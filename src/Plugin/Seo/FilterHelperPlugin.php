<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Plugin\Seo;

use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\AttributeLandingTweakwise\Model\FilterManager;
use Emico\Tweakwise\Model\Seo\FilterHelper;

class FilterHelperPlugin
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
     * FilterHelperPlugin constructor.
     * @param LandingPageContext $landingPageContext
     * @param FilterManager $filterManager
     */
    public function __construct(LandingPageContext $landingPageContext, FilterManager $filterManager)
    {
        $this->landingPageContext = $landingPageContext;
        $this->filterManager = $filterManager;
    }

    /**
     * @param FilterHelper $helper
     * @param callable $proceed
     * @return bool
     */
    public function aroundShouldPageBeIndexable(FilterHelper $helper, callable $proceed)
    {
        if ($this->landingPageContext->isOnLandingPage() && \count($this->filterManager->getActiveFiltersExcludingLandingPageFilters()) === 0) {
            return true;
        }
        return $proceed();
    }
}