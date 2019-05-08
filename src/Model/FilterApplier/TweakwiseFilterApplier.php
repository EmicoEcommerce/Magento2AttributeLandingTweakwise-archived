<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Model\FilterApplier;


use Emico\AttributeLanding\Api\Data\LandingPageInterface;
use Emico\AttributeLanding\Model\FilterApplier\FilterApplierInterface;
use Emico\Tweakwise\Model\Catalog\Layer\NavigationContext;

class TweakwiseFilterApplier implements FilterApplierInterface
{
    /**
     * @var NavigationContext
     */
    private $navigationContext;

    /**
     * TweakwiseFilterApplier constructor.
     * @param NavigationContext $navigationContext
     */
    public function __construct(NavigationContext $navigationContext)
    {
        $this->navigationContext = $navigationContext;
    }

    /**
     * @param LandingPageInterface $page
     * @return mixed
     */
    public function applyFilters(LandingPageInterface $page)
    {
        $navigationRequest = $this->navigationContext->getRequest();

        $filters = $page->getFilters();

        foreach ($filters as $filter) {
            $navigationRequest->addAttributeFilter($filter->getFacet(), $filter->getValue());
        }
    }

    /**
     * @return bool
     */
    public function canApplyFilters(): bool
    {
        return true;
    }
}