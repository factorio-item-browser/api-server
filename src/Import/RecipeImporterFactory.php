<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use Interop\Container\ContainerInterface;

/**
 * The factory of the recipe importer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeImporterFactory
{
    /**
     * Creates the recipe importer.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return RecipeImporter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        /* @var ItemService $itemService */
        $itemService = $container->get(ItemService::class);
        /* @var RecipeService $recipeService */
        $recipeService = $container->get(RecipeService::class);

        return new RecipeImporter($entityManager, $itemService, $recipeService);
    }
}