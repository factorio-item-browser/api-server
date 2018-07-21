<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search;

use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Mapper\RecipeMapper;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the search decorator.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchDecoratorFactory implements FactoryInterface
{
    /**
     * Creates the search decorator.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return SearchDecorator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ItemService $itemService */
        $itemService = $container->get(ItemService::class);
        /* @var RecipeMapper $recipeMapper */
        $recipeMapper = $container->get(RecipeMapper::class);
        /* @var RecipeService $recipeService */
        $recipeService = $container->get(RecipeService::class);
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new SearchDecorator($itemService, $recipeMapper, $recipeService, $translationService);
    }
}
