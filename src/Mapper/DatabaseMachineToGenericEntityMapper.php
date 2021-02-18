<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Database\Entity\Machine;
use FactorioItemBrowser\Common\Constant\EntityType;

/**
 * The class mapping database machines to generic entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<Machine, GenericEntity>
 */
class DatabaseMachineToGenericEntityMapper extends TranslationServiceAwareMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return Machine::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return GenericEntity::class;
    }

    /**
     * @param Machine $source
     * @param GenericEntity $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = EntityType::MACHINE;
        $destination->name = $source->getName();

        $this->addToTranslationService($destination);
    }
}
