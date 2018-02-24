<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Error;

use Interop\Container\ContainerInterface;
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
        /* @var MessageLogger $messageLogger */
        $messageLogger = $container->get(MessageLogger::class);

        return new ErrorResponseGenerator($messageLogger);
    }
}