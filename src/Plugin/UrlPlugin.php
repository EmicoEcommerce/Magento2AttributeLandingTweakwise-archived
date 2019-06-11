<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Plugin;


use Closure;
use Emico\AttributeLanding\Api\Data\LandingPageInterface;
use Emico\AttributeLanding\Model\Filter;
use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\AttributeLanding\Model\UrlFinder;
use Emico\AttributeLandingTweakwise\Model\FilterManager;
use Emico\Tweakwise\Model\Catalog\Layer\Filter\Item;
use Emico\Tweakwise\Model\Catalog\Layer\Url;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;

class UrlPlugin
{
    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var UrlFinder
     */
    private $urlFinder;

    /**
     * @var LandingPageContext
     */
    private $landingPageContext;
    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * UrlPlugin constructor.
     * @param Resolver $layerResolver
     * @param UrlFinder $urlFinder
     * @param LandingPageContext $landingPageContext
     */
    public function __construct(Resolver $layerResolver, UrlFinder $urlFinder, LandingPageContext $landingPageContext, FilterManager $filterManager)
    {
        $this->layerResolver = $layerResolver;
        $this->urlFinder = $urlFinder;
        $this->landingPageContext = $landingPageContext;
        $this->filterManager = $filterManager;
    }

    /**
     * @param Url $subject
     * @param Closure $proceed
     * @param Item $filterItem
     * @return mixed|string
     */
    public function aroundGetSelectFilter(Url $subject, Closure $proceed, Item $filterItem)
    {
        $layer = $this->getLayer();
        $filters = $this->filterManager->getAllActiveFilters();

        $filters[] = $filterItem;
        $filters = array_map(
            function (Item $item) {
                return new Filter(
                    $item->getFilter()->getUrlKey(),
                    $item->getAttribute()->getTitle()
                );
            },
            $filters
        );

        $attributeLandingFilters = $this->getLandingsPageFilters();
        $filters = array_unique(
            array_merge($filters, $attributeLandingFilters),
            SORT_REGULAR
        );

        if ($url = $this->urlFinder->findUrlByFilters($filters, $layer->getCurrentCategory()->getEntityId())) {
            return '/' . $url;
        }

        return $proceed($filterItem);
    }

    /**
     * @return Filter[]
     */
    public function getLandingsPageFilters()
    {
        if (!$landingsPage = $this->landingPageContext->getLandingPage()) {
            return [];
        }

        return $landingsPage->getFilters();
    }

    /**
     * @param Url $subject
     * @param Closure $proceed
     * @param Item $filterItem
     * @return mixed|string
     * 
     * When "hide_selected_filters" is disabled we need to apply some extra logic for the removal of filters.
     * If the user removes a filter which is part of the landingPage predefined filters we need to go to the category page instead of the landingpage
     */
    public function aroundGetRemoveFilter(Url $subject, Closure $proceed, Item $filterItem)
    {
        $landingPage = $this->landingPageContext->getLandingPage();

        $removeUrl = $proceed($filterItem);

        // We are not on a landing page, no need to do anything special
        if ($landingPage === null || $landingPage->getHideSelectedFilters()) {
            return $removeUrl;
        }

        // The filter you want to remove is not set as predefined filter on the landingpage, we can safely stay on the landingpage
        if (!$this->filterManager->isFilterAvailableOnLandingPage($landingPage, $filterItem)) {
            return $removeUrl;
        }

        // Capture the filter part of the URL and rebuild the URL to from {landingPage}/{filters} to {category}/{filters}
        if (preg_match('|' . $landingPage->getUrlPath() . '(.*)|', $removeUrl, $matches)) {
            $category = $this->getLayer()->getCurrentCategory();
            $categoryUrl = $category->getUrl();
            $removeUrl = $categoryUrl . $matches[1];
        }

        return $removeUrl;
    }

    /**
     * @param Url $subject
     * @param Closure $proceed
     * @param array $activeFilterItems
     * @return mixed|string
     */
    public function aroundGetClearUrl(Url $subject, Closure $proceed, array $activeFilterItems)
    {
        $landingPage = $this->landingPageContext->getLandingPage();

        // We are not on a landing page, no need to do anything special
        if ($landingPage === null || $landingPage->getHideSelectedFilters()) {
            return $proceed($activeFilterItems);
        }

        $category = $this->getLayer()->getCurrentCategory();
        return $category->getUrl();
    }

    /**
     * @return Layer
     */
    protected function getLayer(): Layer
    {
        return $this->layerResolver->get();
    }
}