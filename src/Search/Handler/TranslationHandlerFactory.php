<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the translation handler.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationHandlerFactory implements FactoryInterface
{
    /**
     * Creates the translation handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return TranslationHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new TranslationHandler($translationService);
    }
}
