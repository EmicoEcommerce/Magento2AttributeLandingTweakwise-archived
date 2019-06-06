<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Model\FilterHider;

use Emico\AttributeLanding\Api\Data\LandingPageInterface;
use Emico\AttributeLanding\Model\FilterHider\FilterHiderInterface;
use Magento\Catalog\Model\Layer\Filter\Item;
use Emico\Tweakwise\Model\Catalog\Layer\Filter\Item as TweakwiseFilterItem;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Emico\Tweakwise\Model\Catalog\Layer\Filter as TweakwiseFilter;

class TweakwiseFilterHider implements FilterHiderInterface
{
    /**
     * @param LandingPageInterface $landingPage
     * @param FilterInterface $filter
     * @param Item|null $filterItem
     * @return bool
     */
    public function shouldHideFilter(LandingPageInterface $landingPage, FilterInterface $filter, Item $filterItem = null): bool
    {
        if (!$filter instanceof TweakwiseFilter) {
            return false;
        }

        if ($filterItem && !$filterItem instanceof TweakwiseFilterItem) {
            return false;
        }

        $facet = $filter->getFacet()->getFacetSettings()->getAttributename();

        foreach ($landingPage->getFilters() as $landingPageFilter) {
            if ($landingPageFilter->getFacet() === $facet) {
                if ($filterItem !== null) {
                    return strtolower($landingPageFilter->getValue()) === strtolower($filterItem->getAttribute()->getTitle());

                }
                return true;
            }
        }
        return false;
    }
}