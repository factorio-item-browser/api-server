<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Response;

use Interop\Container\ContainerInterface;
use Zend\Log\LoggerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the error response generator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ErrorResponseGeneratorFactory implements FactoryInterface
{
    /**
     * Creates the error response generator.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ErrorResponseGenerator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var LoggerInterface $logger */
        $logger = $container->get('logger.factorio-item-browser');

        return new ErrorResponseGenerator($logger);
    }
}