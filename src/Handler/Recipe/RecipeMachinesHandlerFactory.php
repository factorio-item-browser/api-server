<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use FactorioItemBrowser\Api\Server\Database\Service\MachineService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Mapper\MachineMapper;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the recipe machines handler.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMachinesHandlerFactory implements FactoryInterface
{
    /**
     * Creates the recipe machines handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return RecipeMachinesHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var MachineMapper $machineMapper */
        $machineMapper = $container->get(MachineMapper::class);
        /* @var MachineService $machineService */
        $machineService = $container->get(MachineService::class);
        /* @var RecipeService $recipeService */
        $recipeService = $container->get(RecipeService::class);
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new RecipeMachinesHandler($machineMapper, $machineService, $recipeService, $translationService);
    }
}