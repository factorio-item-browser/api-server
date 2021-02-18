<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\Machine as ClientMachine;
use FactorioItemBrowser\Api\Database\Entity\Machine as DatabaseMachine;

/**
 * The class able to map database machines to client ones.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<DatabaseMachine, ClientMachine>
 */
class DatabaseMachineToClientMachineMapper extends TranslationServiceAwareMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return DatabaseMachine::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ClientMachine::class;
    }

    /**
     * @param DatabaseMachine $source
     * @param ClientMachine $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->name = $source->getName();
        $destination->craftingSpeed = $source->getCraftingSpeed();
        $destination->numberOfItemSlots = $source->getNumberOfItemSlots();
        $destination->numberOfFluidInputSlots = $source->getNumberOfFluidInputSlots();
        $destination->numberOfFluidOutputSlots = $source->getNumberOfFluidOutputSlots();
        $destination->numberOfModuleSlots = $source->getNumberOfModuleSlots();
        $destination->energyUsage = $source->getEnergyUsage();
        $destination->energyUsageUnit = $source->getEnergyUsageUnit();

        $this->addToTranslationService($destination);
    }
}
