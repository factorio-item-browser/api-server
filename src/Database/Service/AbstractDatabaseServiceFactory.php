<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The abstract factory for the database service classes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AbstractDatabaseServiceFactory implements FactoryInterface
{
    /**
     * Creates the service instance.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return AbstractDatabaseService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);

        return new $requestedName($entityManager);
    }
}