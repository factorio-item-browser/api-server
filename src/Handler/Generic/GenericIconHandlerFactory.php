<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the generic icon handler.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class GenericIconHandlerFactory implements FactoryInterface
{
    /**
     * Creates the generic icon handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return GenericIconHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new GenericIconHandler();
    }
}