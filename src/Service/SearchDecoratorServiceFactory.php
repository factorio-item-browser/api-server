<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Server\SearchDecorator\ItemDecorator;
use FactorioItemBrowser\Api\Server\SearchDecorator\RecipeDecorator;
use FactorioItemBrowser\Api\Server\SearchDecorator\SearchDecoratorInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchDecoratorServiceFactory implements FactoryInterface
{
    protected const ALIASES = [
        ItemDecorator::class,
        RecipeDecorator::class,
    ];

    /**
     * Creates the service.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return SearchDecoratorService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SearchDecoratorService($this->createSearchDecorators($container, self::ALIASES));
    }

    /**
     * Creates the fetchers to use.
     * @param ContainerInterface $container
     * @param array|string[] $aliases
     * @return array|SearchDecoratorInterface[]
     */
    protected function createSearchDecorators(ContainerInterface $container, array $aliases): array
    {
        $result = [];
        foreach ($aliases as $alias) {
            $result[] = $container->get($alias);
        }
        return $result;
    }
}
