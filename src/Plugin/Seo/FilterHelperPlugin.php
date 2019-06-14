<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Plugin\Seo;

use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\Tweakwise\Model\Seo\FilterHelper;

class FilterHelperPlugin
{
    /**
     * @var LandingPageContext
     */
    private $landingPageContext;

    /**
     * FilterHelperPlugin constructor.
     * @param LandingPageContext $landingPageContext
     */
    public function __construct(LandingPageContext $landingPageContext)
    {
        $this->landingPageContext = $landingPageContext;
    }

    /**
     * @param FilterHelper $helper
     * @param callable $proceed
     * @return bool
     */
    public function aroundShouldPageBeIndexable(FilterHelper $helper, callable $proceed)
    {
        if ($this->landingPageContext->isOnLandingPage()) {
            return true;
        }
        return $proceed();
    }
}