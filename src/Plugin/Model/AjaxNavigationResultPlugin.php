<?php

namespace Emico\AttributeLandingTweakwise\Plugin\Model;

use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\AttributeLandingTweakwise\Model\FilterManager;
use Emico\Tweakwise\Model\AjaxNavigationResult;
use Emico\Tweakwise\Model\Catalog\Layer\Url;
use Emico\Tweakwise\Model\Catalog\Layer\Url\UrlModel;
use Emico\Tweakwise\Model\Catalog\Layer\Url\Strategy\PathSlugStrategy;
use Magento\Framework\App\Request\Http as MagentoHttpRequest;

class AjaxNavigationResultPlugin
{
    /**
     * @var MagentoHttpRequest
     */
    private $request;

    /**
     * @var LandingPageContext
     */
    private $landingPageContext;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var UrlModel
     */
    private $urlModel;

    public function __construct(
        MagentoHttpRequest $request,
        LandingPageContext $landingPageContext,
        FilterManager $filterManager,
        Url $url,
        UrlModel $urlModel
    ) {
        $this->request = $request;
        $this->landingPageContext = $landingPageContext;
        $this->filterManager = $filterManager;
        $this->urlModel = $urlModel;
        $this->url = $url;
    }

    public function aroundGetResponseUrl(AjaxNavigationResult $ajaxNavigationResult, callable $proceed) {
       $type = $this->request->getParam('__tw_ajax_type');

        if ($type === 'landingpage' && $this->landingPageContext->getLandingPage()) {
            $filters = $this->filterManager->getActiveFiltersExcludingLandingPageFilters();
            $url = $this->url->getFilterUrl($filters);

            return $url;
       }

       return $proceed();
    }
}
