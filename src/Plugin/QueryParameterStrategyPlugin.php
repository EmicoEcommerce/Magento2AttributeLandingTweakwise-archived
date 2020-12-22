<?php

/**
 * @author : Edwin Jacobs, email: ejacobs@emico.nl.
 * @copyright : Copyright Emico B.V. 2020.
 */
namespace Emico\AttributeLandingTweakwise\Plugin;

use Emico\AttributeLanding\Model\LandingPageContext;
use Emico\AttributeLandingTweakwise\Model\FilterManager;
use Emico\Tweakwise\Model\Catalog\Layer\Url\Strategy\QueryParameterStrategy;
use Magento\Framework\Url;

class QueryParameterStrategyPlugin
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
     * @var Url
     */
    private $magentoUrl;

    public function __construct(
        LandingPageContext $landingPageContext,
        FilterManager $filterManager,
        Url $magentoUrl
    ) {
        $this->landingPageContext = $landingPageContext;
        $this->filterManager = $filterManager;
        $this->magentoUrl = $magentoUrl;
    }

    /**
     * Adds landingpage filters to category select url
     *
     * @param QueryParameterStrategy $original
     * @param string $result
     * @return string
     */
    public function afterGetCategoryFilterSelectUrl(
        QueryParameterStrategy $original,
        string $result
    ): string {
        $landingPage = $this->landingPageContext->getLandingPage();
        if ($landingPage === null) {
            return $result;
        }

        // If $landingPage->getHideSelectedFilters() === false then the landingpage filters are already in the url
        if (!$landingPage->getHideSelectedFilters()) {
            return $result;
        }

        $landingsPageFilters = $this->filterManager->getLandingsPageFilters();
        if (empty($landingsPageFilters)) {
            return $result;
        }

        $urlParts = parse_url($result) ?: null;
        if (!$urlParts) {
            return $result;
        }

        $query = [];
        $queryPart = $urlParts['query'] ?? '';
        // Parse the current query parameters as string
        parse_str($queryPart, $query);

        foreach ($landingsPageFilters as $filter) {
            $query[$filter->getFacet()][] = strtolower($filter->getValue());
        }

        return $this->magentoUrl->getDirectUrl(
            ltrim($urlParts['path'], '/'),
            ['_query' => $query]
        );
    }
}
