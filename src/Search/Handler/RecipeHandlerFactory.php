<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the recipe handler.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeHandlerFactory implements FactoryInterface
{
    /**
     * Creates the recipe handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return RecipeHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var RecipeService $recipeService */
        $recipeService = $container->get(RecipeService::class);

        return new RecipeHandler($recipeService);
    }
}
