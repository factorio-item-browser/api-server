<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\Mod as ClientMod;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;

/**
 * The class able to map mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModMapper
{
    /**
     * Maps the database mod to a client mod.
     * @param DatabaseMod $databaseMod
     * @param TranslationService $translationService
     * @return ClientMod
     */
    static public function mapDatabaseItemToClientItem(
        DatabaseMod $databaseMod,
        TranslationService $translationService
    ): ClientMod
    {
        $clientMod = new ClientMod();
        $clientMod
            ->setName($databaseMod->getName())
            ->setAuthor($databaseMod->getAuthor())
            ->setVersion($databaseMod->getCurrentVersion());

        $translationService->addEntityToTranslate($clientMod);
        return $clientMod;
    }
}