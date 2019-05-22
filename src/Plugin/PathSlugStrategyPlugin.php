<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Plugin;


use Closure;
use Emico\AttributeLanding\Api\Data\LandingPageInterface;
use Emico\AttributeLanding\Model\FilterHider\FilterHiderInterface;
use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\AttributeLandingTweakwise\Model\FilterManager;
use Emico\Tweakwise\Model\Catalog\Layer\Filter\Item;
use Emico\Tweakwise\Model\Catalog\Layer\Url\Strategy\PathSlugStrategy;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Zend\Http\Request as HttpRequest;

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
     * @param LandingPageContext $landingPageContext
     * @param FilterManager $filterManager
     */
    public function __construct(LandingPageContext $landingPageContext, FilterManager $filterManager)
    {
        $this->landingPageContext = $landingPageContext;
        $this->filterManager = $filterManager;
    }

    /**
     * @param PathSlugStrategy $pathSlugStrategy
     * @param Closure $proceed
     * @param HttpRequest $request
     * @param Item $item
     * @return string
     */
    public function aroundGetAttributeSelectUrl(PathSlugStrategy $pathSlugStrategy, Closure $proceed, HttpRequest $request, Item $item)
    {
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
     * @param HttpRequest $request
     * @param Item $item
     * @return string
     */
    public function aroundGetAttributeRemoveUrl(PathSlugStrategy $pathSlugStrategy, Closure $proceed, HttpRequest $request, Item $item)
    {
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
}