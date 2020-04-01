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
use Emico\Tweakwise\Model\Client\Type\FacetType\SettingsType;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;

class FilterManager
{
    /**
     * @var array
     */
    protected $activeFilters;

    /**
     * @var array
     */
    protected $activeFiltersExcludingLandingPageFilters;

    /**
     * @var Resolver
     */
    protected $layerResolver;

    /**
     * @var FilterHiderInterface
     */
    protected $filterHider;

    /**
     * @var LandingPageContext
     */
    protected $landingPageContext;

    /**
     * FilterManager constructor.
     * @param Resolver $layerResolver
     * @param FilterHiderInterface $filterHider
     * @param LandingPageContext $landingPageContext
     */
    public function __construct(Resolver $layerResolver, FilterHiderInterface $filterHider, LandingPageContext $landingPageContext)
    {
        $this->layerResolver = $layerResolver;
        $this->filterHider = $filterHider;
        $this->landingPageContext = $landingPageContext;
    }

    /**
     * @return Item[]
     */
    public function getActiveFiltersExcludingLandingPageFilters(): array
    {
        if ($this->activeFiltersExcludingLandingPageFilters === null) {
            $filters = $this->getAllActiveFilters();
            $landingPage = $this->landingPageContext->getLandingPage();
            if ($landingPage === null) {
                return $filters;
            }
            /** @var string|int $index  */
            foreach ($filters as $index => $filterItem) {
                /** @var Item $filterItem */
                if ($this->filterHider->shouldHideFilter($landingPage, $filterItem->getFilter(), $filterItem)) {
                    unset($filters[$index]);
                }
            }
            $this->activeFiltersExcludingLandingPageFilters = $filters;
        }
        return $this->activeFiltersExcludingLandingPageFilters;
    }

    /**
     * @return array|Item[]
     */
    public function getAllActiveFilters(): array
    {
        if ($this->activeFilters !== null) {
            return $this->activeFilters;
        }

        $filterItems = $this->getLayer()->getState()->getFilters();
        if (!\is_array($filterItems)) {
            return [];
        }
        // Do not consider category as active
        $filterItems = \array_filter($filterItems, function (Item $filter) {
            $source = $filter
                ->getFilter()
                ->getFacet()
                ->getFacetSettings()
                ->getSource();
            return $source !== SettingsType::SOURCE_CATEGORY;
        });
        $this->activeFilters = $filterItems;
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
