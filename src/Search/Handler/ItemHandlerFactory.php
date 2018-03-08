<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the item handler.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemHandlerFactory implements FactoryInterface
{
    /**
     * Creates the item handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ItemHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ItemService $itemService */
        $itemService = $container->get(ItemService::class);

        return new ItemHandler($itemService);
    }
}