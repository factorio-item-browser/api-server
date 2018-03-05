<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Search;

use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
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
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new SearchQueryHandler($translationService);
    }
}