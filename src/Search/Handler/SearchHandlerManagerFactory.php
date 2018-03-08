<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use Interop\Container\ContainerInterface;

/**
 * The factory of the search manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchHandlerManagerFactory
{
    /**
     * The handler classes to use.
     */
    const HANDLER_CLASSES = [
        ItemHandler::class,
        RecipeHandler::class,
        TranslationHandler::class,
        MissingItemIdHandler::class,
        MissingRecipeIdHandler::class,
    ];

    /**
     * Creates the search manager.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return SearchHandlerManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $handlers = [];
        foreach (self::HANDLER_CLASSES as $handlerClass) {
            $handlers[] = $container->get($handlerClass);
        }

        return new SearchHandlerManager($handlers);
    }
}