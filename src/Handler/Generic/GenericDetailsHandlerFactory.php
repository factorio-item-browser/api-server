<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the generic details handler.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class GenericDetailsHandlerFactory implements FactoryInterface
{
    /**
     * Creates the generic details handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return GenericDetailsHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var RecipeService $recipeService */
        $recipeService = $container->get(RecipeService::class);
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new GenericDetailsHandler($recipeService, $translationService);
    }
}