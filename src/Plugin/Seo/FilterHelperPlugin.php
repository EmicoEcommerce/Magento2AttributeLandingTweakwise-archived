<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Plugin\Seo;

use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\AttributeLandingTweakwise\Model\FilterManager;
use Emico\Tweakwise\Model\Catalog\Layer\Filter\Item;
use Emico\Tweakwise\Model\Seo\FilterHelper;
use Emico\AttributeLanding\Model\UrlFinder;

class FilterHelperPlugin
{
    /**
     * @var LandingPageContext
     */
    protected $landingPageContext;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * FilterHelperPlugin constructor.
     * @param LandingPageContext $landingPageContext
     * @param FilterManager $filterManager
     */
    public function __construct(
        LandingPageContext $landingPageContext,
        FilterManager $filterManager
    ) {
        $this->landingPageContext = $landingPageContext;
        $this->filterManager = $filterManager;
    }

    /**
     * @param FilterHelper $helper
     * @param callable $proceed
     * @return bool
     */
    public function aroundShouldPageBeIndexable(FilterHelper $helper, callable $proceed): bool
    {
        if ($this->landingPageContext->isOnLandingPage() &&
            \count($this->filterManager->getActiveFiltersExcludingLandingPageFilters()) === 0
        ) {
            return true;
        }
        return $proceed();
    }

    /**
     * @param FilterHelper $helper
     * @param callable $proceed
     * @return Item[]
     */
    public function aroundGetActiveFilterItems(FilterHelper $helper, callable $proceed): array
    {
        if ($this->landingPageContext->isOnLandingPage()) {
            return $this->filterManager->getActiveFiltersExcludingLandingPageFilters();
        }
        return $proceed();
    }

    /**
     * @param FilterHelper $helper
     * @param bool $result
     * @param Item $item
     * @return bool|string|null
     */
    public function afterShouldFilterBeIndexable(FilterHelper $helper, bool $result, Item $item)
    {
        return $result || $this->filterManager->findLandingPageUrlForFilterItem($item);
    }
}
