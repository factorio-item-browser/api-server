<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

/**
 * The factory of the cached search result service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CachedSearchResultServiceFactory
{
    /**
     * Creates the cached search result service instance.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return CachedSearchResultService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        /* @var ModService $modService */
        $modService = $container->get(ModService::class);
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new CachedSearchResultService($entityManager, $modService, $translationService);
    }
}
