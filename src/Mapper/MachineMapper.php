<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\Machine as ClientMachine;
use FactorioItemBrowser\Api\Database\Entity\Machine as DatabaseMachine;

/**
 * The class able to map machines.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineMapper extends AbstractMapper
{
    /**
     * Maps the database machine to a client machine.
     * @param DatabaseMachine $databaseMachine
     * @param ClientMachine $clientMachine
     * @return ClientMachine
     */
    public function mapMachine(DatabaseMachine $databaseMachine, ClientMachine $clientMachine): ClientMachine
    {
        $clientMachine->setName($databaseMachine->getName())
                      ->setCraftingSpeed($databaseMachine->getCraftingSpeed())
                      ->setNumberOfItemSlots($databaseMachine->getNumberOfItemSlots())
                      ->setNumberOfFluidInputSlots($databaseMachine->getNumberOfFluidInputSlots())
                      ->setNumberOfFluidOutputSlots($databaseMachine->getNumberOfFluidOutputSlots())
                      ->setNumberOfModuleSlots($databaseMachine->getNumberOfModuleSlots())
                      ->setEnergyUsage($databaseMachine->getEnergyUsage())
                      ->setEnergyUsageUnit($databaseMachine->getEnergyUsageUnit());

        $this->translationService->addEntityToTranslate($clientMachine);
        return $clientMachine;
    }
}
