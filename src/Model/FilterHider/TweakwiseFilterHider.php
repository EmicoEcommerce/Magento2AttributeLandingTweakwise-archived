<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Model\FilterHider;

use Emico\AttributeLanding\Api\Data\FilterInterface as LandingPageFilterInterface;
use Emico\AttributeLanding\Api\Data\LandingPageInterface;
use Emico\AttributeLanding\Model\FilterHider\FilterHiderInterface;
use Emico\Tweakwise\Model\Catalog\Layer\Filter as TweakwiseFilter;
use Emico\Tweakwise\Model\Catalog\Layer\Filter\Item as TweakwiseFilterItem;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Filter\Item;

class TweakwiseFilterHider implements FilterHiderInterface
{
    /**
     * @param LandingPageInterface $landingPage
     * @param FilterInterface $filter
     * @param Item|null $filterItem
     * @return bool
     */
    public function shouldHideFilter(
        LandingPageInterface $landingPage,
        FilterInterface $filter,
        Item $filterItem = null
    ): bool
    {
        if (!$filter instanceof TweakwiseFilter) {
            return false;
        }

        if ($filterItem && !$filterItem instanceof TweakwiseFilterItem) {
            return false;
        }

        $facet = $filter->getUrlKey();
        $landingPageFilters = $landingPage->getFilters();

        $landingPageFacets = array_map(
            static function (LandingPageFilterInterface $landingPageFilter) {
                return $landingPageFilter->getFacet();
            },
            $landingPageFilters
        );

        $isLandingPageFilter = in_array($facet, $landingPageFacets, true);
        if (!$filterItem) {
            return $isLandingPageFilter;
        }

        $landingsPageFilterValues = array_map(
            static function (LandingPageFilterInterface $landingPageFilter) {
                return strtolower($landingPageFilter->getValue());
            },
            $landingPageFilters
        );

        return $isLandingPageFilter
            && in_array(
                strtolower($filterItem->getAttribute()->getTitle()),
                $landingsPageFilterValues,
                true
            );
    }
}
