<?php

/**
 * @author : Edwin Jacobs, email: ejacobs@emico.nl.
 * @copyright : Copyright Emico B.V. 2020.
 */
namespace Emico\AttributeLandingTweakwise\Model\FilterFormInputProvider;

use Emico\AttributeLanding\Api\Data\LandingPageInterface;
use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\Tweakwise\Model\Config;
use Emico\Tweakwise\Model\FilterFormInputProvider\FilterFormInputProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;

class LandingPageInputProvider implements FilterFormInputProviderInterface
{
    public const TYPE = 'landingpage';

    /**
     * @var Config
     */
    protected $twConfig;

    /**
     * @var LandingPageContext
     */
    protected $landingPageContext;

    /**
     * @var RequestInterface $request
     */
    protected RequestInterface $request;

    /**
     * LandingPageProvider constructor.
     * @param Config $twConfig
     * @param LandingPageContext $landingPageContext
     */
    public function __construct(
        Config             $twConfig,
        LandingPageContext $landingPageContext,
        RequestInterface   $request
    ) {
        $this->twConfig = $twConfig;
        $this->landingPageContext = $landingPageContext;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     * @throws NotFoundException
     */
    public function getFilterFormInput(): array
    {
        if (!$this->twConfig->isAjaxFilters()) {
            return [];
        }

        $page = $this->getPage();
        if (!$page) {
            throw new NotFoundException(__('landingpage not found'));
        }
        return [
            '__tw_ajax_type' => self::TYPE,
            '__tw_object_id' => $page->getPageId(),
            '__tw_original_url' => $page->getUrlPath(),
            'product_list_order' => $this->request->getParam('product_list_order'),
            'product_list_limit' => $this->request->getParam('product_list_limit'),
            'product_list_mode' => $this->request->getParam('product_list_mode'),
        ];
    }

    /**
     * @return LandingPageInterface
     */
    protected function getPage(): LandingPageInterface
    {
        return $this->landingPageContext->getLandingPage();
    }
}
