<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Database\Data\MachineData;
use FactorioItemBrowser\Common\Constant\EntityType;

/**
 * The class mapping machine data to generic entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineDataToGenericEntityMapper extends TranslationServiceAwareMapper implements StaticMapperInterface
{
    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return MachineData::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return GenericEntity::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param MachineData $machineData
     * @param GenericEntity $genericEntity
     */
    public function map($machineData, $genericEntity): void
    {
        $genericEntity->setType(EntityType::MACHINE)
                      ->setName($machineData->getName());

        $this->addToTranslationService($genericEntity);
    }
}
