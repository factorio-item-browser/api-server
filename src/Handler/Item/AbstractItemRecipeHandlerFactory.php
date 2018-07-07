<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Mapper\ItemMapper;
use FactorioItemBrowser\Api\Server\Mapper\RecipeMapper;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The abstract factory of the item recipe handlers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AbstractItemRecipeHandlerFactory implements FactoryInterface
{
    /**
     * Creates the item recipe handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return AbstractItemRecipeHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ItemMapper $itemMapper */
        $itemMapper = $container->get(ItemMapper::class);
        /* @var ItemService $itemService */
        $itemService = $container->get(ItemService::class);
        /* @var RecipeMapper $recipeMapper */
        $recipeMapper = $container->get(RecipeMapper::class);
        /* @var RecipeService $recipeService */
        $recipeService = $container->get(RecipeService::class);
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new $requestedName($itemMapper, $itemService, $recipeMapper, $recipeService, $translationService);
    }
}
