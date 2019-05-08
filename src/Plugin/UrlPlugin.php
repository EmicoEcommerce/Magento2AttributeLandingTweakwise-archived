<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

namespace Emico\AttributeLandingTweakwise\Plugin;


use Closure;
use Emico\AttributeLanding\Model\UrlFinder;
use Emico\Tweakwise\Model\Catalog\Layer\Filter\Item;
use Emico\Tweakwise\Model\Catalog\Layer\Url;
use Magento\Catalog\Model\Layer\Resolver;

class UrlPlugin
{
    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var UrlFinder
     */
    private $urlFinder;

    /**
     * UrlPlugin constructor.
     * @param Resolver $layerResolver
     * @param UrlFinder $urlFinder
     */
    public function __construct(Resolver $layerResolver, UrlFinder $urlFinder)
    {
        $this->layerResolver = $layerResolver;
        $this->urlFinder = $urlFinder;
    }

    /**
     * @param Url $subject
     * @param Closure $proceed
     * @param Item $filterItem
     * @return mixed|string
     */
    public function aroundGetSelectFilter(Url $subject, Closure $proceed, Item $filterItem)
    {
        $layer = $this->layerResolver->get();
        $filters = $layer->getState()->getFilters();
        $filters[] = $filterItem;
        if ($url = $this->urlFinder->findUrlByFilters($filters, $layer->getCurrentCategory()->getEntityId())) {
            return '/' . $url;
        }

        return $proceed($filterItem);
    }
}