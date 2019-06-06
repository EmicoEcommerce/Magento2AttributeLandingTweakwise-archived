<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Model;

use Emico\AttributeLanding\Api\Data\LandingPageInterface;
use Emico\AttributeLanding\Model\Filter;
use Emico\AttributeLanding\Model\FilterHider\FilterHiderInterface;
use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\Tweakwise\Model\Catalog\Layer\Filter\Item;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;

class FilterManager
{
    /**
     * @var array
     */
    private $activeFilters;

    /**
     * @var array
     */
    private $activeFiltersExludingLandingPageFilters;

    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var FilterHiderInterface
     */
    private $filterHider;

    /**
     * @var LandingPageContext
     */
    private $landingPageContext;

    /**
     * FilterManager constructor.
     * @param Resolver $layerResolver
     * @param FilterHiderInterface $filterHider
     */
    public function __construct(Resolver $layerResolver, FilterHiderInterface $filterHider, LandingPageContext $landingPageContext)
    {
        $this->layerResolver = $layerResolver;
        $this->filterHider = $filterHider;
        $this->landingPageContext = $landingPageContext;
    }

    /**
     * @return array
     */
    public function getActiveFiltersExcludingLandingPageFilters(): array
    {
        if ($this->activeFiltersExludingLandingPageFilters === null) {
            $filters = $this->getAllActiveFilters();
            $landingPage = $this->landingPageContext->getLandingPage();
            if ($landingPage === null) {
                return $filters;
            }

            foreach ($filters as $index => $filterItem) {
                /** @var Item $filterItem */
                if ($this->filterHider->shouldHideFilter($landingPage, $filterItem->getFilter(), $filterItem)) {
                    unset($filters[$index]);
                }
            }
            $this->activeFiltersExludingLandingPageFilters = $filters;
        }
        return $this->activeFiltersExludingLandingPageFilters;
    }

    /**
     * @return array|Item[]
     */
    public function getAllActiveFilters(): array
    {
        if ($this->activeFilters !== null) {
            return $this->activeFilters;
        }

        $filters = $this->getLayer()->getState()->getFilters();
        if (!\is_array($filters)) {
            return [];
        }
        $this->activeFilters = $filters;
        return $this->activeFilters;
    }

    /**
     * @return Layer
     */
    protected function getLayer(): Layer
    {
        return $this->layerResolver->get();
    }

    /**
     * @param LandingPageInterface $landingPage
     * @param Item $filterItem
     * @return bool
     */
    public function isFilterAvailableOnLandingPage(LandingPageInterface $landingPage, Item $filterItem): bool
    {
        foreach ($landingPage->getFilters() as $landingPageFilter) {
            if (
                $filterItem->getAttribute()->getTitle() === $landingPageFilter->getValue() &&
                $filterItem->getFilter()->getUrlKey() === $landingPageFilter->getFacet()
            ) {
                return true;
            }
        }
        return false;
    }
}