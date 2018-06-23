<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\Mod as ClientMod;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod as DatabaseMod;

/**
 * The class able to map mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModMapper extends AbstractMapper
{
    /**
     * Maps the database item into the specified client item.
     * @param DatabaseMod $databaseMod
     * @param ClientMod $clientMod
     * @return ClientMod
     */
    public function mapMod(DatabaseMod $databaseMod, ClientMod $clientMod): ClientMod
    {
        $clientMod->setName($databaseMod->getName())
                  ->setAuthor($databaseMod->getAuthor())
                  ->setVersion($databaseMod->getCurrentVersion());

        $this->translationService->addEntityToTranslate($clientMod);
        return $clientMod;
    }
}