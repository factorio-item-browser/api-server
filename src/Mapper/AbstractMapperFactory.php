<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The abstract factory of the mappers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AbstractMapperFactory implements FactoryInterface
{
    /**
     * Creates the mapper class.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return AbstractMapper
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new $requestedName($translationService);
    }
}