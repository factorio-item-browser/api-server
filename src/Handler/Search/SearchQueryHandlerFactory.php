<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Search;

use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Search\Handler\SearchHandlerManager;
use FactorioItemBrowser\Api\Server\Search\SearchDecorator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the search query handler.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchQueryHandlerFactory implements FactoryInterface
{
    /**
     * Creates the search query handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return SearchQueryHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var SearchHandlerManager $searchHandlerManager */
        $searchHandlerManager = $container->get(SearchHandlerManager::class);
        /* @var SearchDecorator $searchDecorator */
        $searchDecorator = $container->get(SearchDecorator::class);
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new SearchQueryHandler($searchHandlerManager, $searchDecorator, $translationService);
    }
}