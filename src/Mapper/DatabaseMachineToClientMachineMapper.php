<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\Machine as ClientMachine;
use FactorioItemBrowser\Api\Database\Entity\Machine as DatabaseMachine;

/**
 * The class able to map database machines to client ones.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DatabaseMachineToClientMachineMapper extends TranslationServiceAwareMapper implements StaticMapperInterface
{
    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return DatabaseMachine::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return ClientMachine::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param DatabaseMachine $databaseMachine
     * @param ClientMachine $clientMachine
     */
    public function map($databaseMachine, $clientMachine): void
    {
        $clientMachine->setName($databaseMachine->getName())
                      ->setCraftingSpeed($databaseMachine->getCraftingSpeed())
                      ->setNumberOfItemSlots($databaseMachine->getNumberOfItemSlots())
                      ->setNumberOfFluidInputSlots($databaseMachine->getNumberOfFluidInputSlots())
                      ->setNumberOfFluidOutputSlots($databaseMachine->getNumberOfFluidOutputSlots())
                      ->setNumberOfModuleSlots($databaseMachine->getNumberOfModuleSlots())
                      ->setEnergyUsage($databaseMachine->getEnergyUsage())
                      ->setEnergyUsageUnit($databaseMachine->getEnergyUsageUnit());

        $this->addToTranslationService($clientMachine);
    }
}
