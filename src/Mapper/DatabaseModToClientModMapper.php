<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod as ClientMod;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;

/**
 * The class able to map database mods to client ones.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DatabaseModToClientModMapper extends TranslationServiceAwareMapper implements StaticMapperInterface
{
    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return DatabaseMod::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return ClientMod::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param DatabaseMod $databaseMod
     * @param ClientMod $clientMod
     */
    public function map($databaseMod, $clientMod): void
    {
        $clientMod->setName($databaseMod->getName())
                  ->setAuthor($databaseMod->getAuthor())
                  ->setVersion($databaseMod->getCurrentVersion());

        $this->addToTranslationService($clientMod);
    }
}
