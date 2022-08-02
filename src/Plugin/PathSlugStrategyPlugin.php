<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Plugin;

use Closure;
use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\AttributeLandingTweakwise\Model\FilterManager;
use Emico\Tweakwise\Model\Catalog\Layer\Filter\Item;
use Emico\Tweakwise\Model\Catalog\Layer\Url\Strategy\PathSlugStrategy;
use Emico\Tweakwise\Model\Catalog\Layer\UrlFactory;
use Magento\Framework\App\Request\Http as MagentoHttpRequest;

class PathSlugStrategyPlugin
{
    /**
     * @var LandingPageContext
     */
    private $landingPageContext;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * @param LandingPageContext $landingPageContext
     * @param FilterManager $filterManager
     * @param UrlFactory $urlFactory
     * @param Url $magentoUrl
     */
    public function __construct(
        LandingPageContext $landingPageContext,
        FilterManager $filterManager,
        UrlFactory $urlFactory
    ) {
        $this->landingPageContext = $landingPageContext;
        $this->filterManager = $filterManager;
        $this->urlFactory = $urlFactory;
    }

    /**
     * @param PathSlugStrategy $pathSlugStrategy
     * @param Closure $proceed
     * @param MagentoHttpRequest $request
     * @param Item $item
     * @return string
     */
    public function aroundGetAttributeSelectUrl(
        PathSlugStrategy $pathSlugStrategy,
        Closure $proceed,
        MagentoHttpRequest $request,
        Item $item
    ) {
        $landingPage = $this->landingPageContext->getLandingPage();
        if ($landingPage === null || $landingPage->getHideSelectedFilters()) {
            return $proceed($request, $item);
        }

        $filters = $this->filterManager->getActiveFiltersExcludingLandingPageFilters();
        $filters[] = $item;
        return $pathSlugStrategy->buildFilterUrl($request, $filters);
    }

    /**
     * @param PathSlugStrategy $pathSlugStrategy
     * @param Closure $proceed
     * @param MagentoHttpRequest $request
     * @param Item $item
     * @return string
     */
    public function aroundGetSliderUrl(
        PathSlugStrategy $pathSlugStrategy,
        Closure $proceed,
        MagentoHttpRequest $request,
        Item $item
    ) {
        $landingPage = $this->landingPageContext->getLandingPage();
        if ($landingPage === null || $landingPage->getHideSelectedFilters()) {
            return $proceed($request, $item);
        }

        $filters = $this->filterManager->getActiveFiltersExcludingLandingPageFilters();
        foreach ($filters as $key => $activeItem) {
            if ($activeItem->getFilter()->getUrlKey() === $item->getFilter()->getUrlKey()) {
                unset($filters[$key]);
            }
        }

        $attribute = clone $item->getAttribute();
        $attribute->setValue('title', '{{from}}-{{to}}');
        $filters[] = new Item($item->getFilter(), $attribute, $this->urlFactory->create());

        return $pathSlugStrategy->buildFilterUrl($request, $filters);
    }

    /**
     * @param PathSlugStrategy $pathSlugStrategy
     * @param Closure $proceed
     * @param MagentoHttpRequest $request
     * @param Item $item
     * @return string
     */
    public function aroundGetAttributeRemoveUrl(
        PathSlugStrategy $pathSlugStrategy,
        Closure $proceed,
        MagentoHttpRequest $request,
        Item $item
    ) {
        $landingPage = $this->landingPageContext->getLandingPage();
        if ($landingPage === null || $landingPage->getHideSelectedFilters()) {
            return $proceed($request, $item);
        }

        if (!$this->filterManager->isFilterAvailableOnLandingPage($landingPage, $item)) {
            $filters = $this->filterManager->getActiveFiltersExcludingLandingPageFilters();
        } else {
            $filters = $this->filterManager->getAllActiveFilters();
        }
        foreach ($filters as $key => $activeItem) {
            if ($activeItem === $item) {
                unset($filters[$key]);
            }
        }
        return $pathSlugStrategy->buildFilterUrl($request, $filters);
    }

    /**
     * Adds landingpage filters to category select url
     *
     * @param PathSlugStrategy $original
     * @param string $result
     * @return string
     */
    public function afterGetCategoryFilterSelectUrl(
        PathSlugStrategy $original,
        string $result
    ): string {
        $landingPage = $this->landingPageContext->getLandingPage();
        if ($landingPage === null) {
            return $result;
        }

        // If $landingPage->getHideSelectedFilters() === false then the landingpage filters are already in the url
        if (!$landingPage->getHideSelectedFilters()) {
            return $result;
        }

        $landingsPageFilters = $this->filterManager->getLandingsPageFilters();
        if (empty($landingsPageFilters)) {
            return $result;
        }

        $filters = [];
        foreach ($landingsPageFilters as $filter) {
            $filters[] = $filter->getFacet();
            $filters[] = strtolower($filter->getValue());
        }

        return sprintf(
            '%s/%s',
            rtrim($result, '/'),
            ltrim(implode('/', $filters), '/')
        );
    }

    /**
     * @param MagentoHttpRequest $request
     * @return string
     */
    public function afterGetOriginalUrl(PathSlugStrategy $original, string $result, MagentoHttpRequest $request): string
    {
        $landingPage = $this->landingPageContext->getLandingPage();
        if ($landingPage === null) {
            return $result;
        }

        if ($twOriginalUrl = $request->getParam('__tw_original_url')) {
            // This seems ugly, perhaps there is another way?
            $query = [];
            // Add page and sort
            $page = $request->getParam('p');
            $sort = $request->getParam('product_list_order');
            $limit = $request->getParam('product_list_limit');
            $mode = $request->getParam('product_list_mode');

            if ($page &&
                (int) $page > 1
            ) {
                $query['p'] = $page;
            }

            if ($sort) {
                $query['product_list_order'] = $sort;
            }
            if ($limit) {
                $query['product_list_limit'] = $limit;
            }
            if ($mode) {
                $query['product_list_mode'] = $mode;
            }

            return filter_var(
                $original->magentoUrl->getDirectUrl($twOriginalUrl, ['_query' => $query]),
                FILTER_SANITIZE_URL
            );
        }

        return $result;
    }
}
