<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use FactorioItemBrowser\Api\Database\Repository\TranslationRepository;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the translation service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationServiceFactory implements FactoryInterface
{
    /**
     * Creates the service instance.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return TranslationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModService $modService */
        $modService = $container->get(ModService::class);
        /* @var TranslationRepository $translationRepository */
        $translationRepository = $container->get(TranslationRepository::class);

        return new TranslationService($modService, $translationRepository);
    }
}
