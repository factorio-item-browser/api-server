<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\Machine as ClientMachine;
use FactorioItemBrowser\Api\Server\Database\Entity\Machine as DatabaseMachine;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;

/**
 * The class able to map machines.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineMapper
{
    /**
     * Maps the database machine to a client machine.
     * @param DatabaseMachine $databaseMachine
     * @param TranslationService $translationService
     * @return ClientMachine
     */
    static public function mapDatabaseMachineToClientMachine(
        DatabaseMachine $databaseMachine,
        TranslationService $translationService
    ): ClientMachine
    {
        $clientMachine = new ClientMachine();
        $clientMachine->setName($databaseMachine->getName())
                      ->setCraftingSpeed($databaseMachine->getCraftingSpeed())
                      ->setNumberOfIngredientSlots($databaseMachine->getNumberOfIngredientSlots())
                      ->setNumberOfModuleSlots($databaseMachine->getNumberOfModuleSlots())
                      ->setEnergyUsage($databaseMachine->getEnergyUsage());

        $translationService->addEntityToTranslate($clientMachine);
        return $clientMachine;
    }
}