<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Plugin;

use Closure;
use Emico\AttributeLanding\Api\Data\FilterInterface;
use Emico\AttributeLanding\Model\Config;
use Emico\AttributeLanding\Model\Filter;
use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\AttributeLanding\Model\UrlFinder;
use Emico\AttributeLandingTweakwise\Model\FilterManager;
use Emico\Tweakwise\Model\Catalog\Layer\Filter\Item;
use Emico\Tweakwise\Model\Catalog\Layer\NavigationContext\CurrentContext;
use Emico\Tweakwise\Model\Catalog\Layer\Url;
use Emico\Tweakwise\Model\Client\Request\ProductSearchRequest;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;

class UrlPlugin
{
    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var LandingPageContext
     */
    private $landingPageContext;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var Config
     */
    private $config;
    /**
     * @var CurrentContext
     */
    private $context;

    /**
     * UrlPlugin constructor.
     * @param Resolver $layerResolver
     * @param LandingPageContext $landingPageContext
     * @param FilterManager $filterManager
     * @param Config $config
     * @param CurrentContext $context
     */
    public function __construct(
        Resolver $layerResolver,
        LandingPageContext $landingPageContext,
        FilterManager $filterManager,
        Config $config,
        CurrentContext $context
    ) {
        $this->layerResolver = $layerResolver;
        $this->landingPageContext = $landingPageContext;
        $this->filterManager = $filterManager;
        $this->config = $config;
        $this->context = $context;
    }

    /**
     * @param Url $subject
     * @param Closure $proceed
     * @param Item $filterItem
     * @return mixed|string
     */
    public function aroundGetSelectFilter(Url $subject, Closure $proceed, Item $filterItem)
    {
        if (!$this->config->isCrossLinkEnabled() || $this->context->getRequest() instanceof ProductSearchRequest) {
            return $proceed($filterItem);
        }

        if ($url = $this->filterManager->findLandingPageUrlForFilterItem($filterItem)) {
            return '/' . $url;
        }

        return $proceed($filterItem);
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
        if (preg_match('|' . $landingPage->getUrlRewriteRequestPath() . '(.*)|', $removeUrl, $matches)) {
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
