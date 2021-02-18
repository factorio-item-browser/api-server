<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\Mod as ClientMod;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;

/**
 * The class able to map database mods to client ones.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<DatabaseMod, ClientMod>
 */
class DatabaseModToClientModMapper extends TranslationServiceAwareMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return DatabaseMod::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ClientMod::class;
    }

    /**
     * @param DatabaseMod $source
     * @param ClientMod $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->name = $source->getName();
        $destination->author = $source->getAuthor();
        $destination->version = $source->getVersion();

        $this->addToTranslationService($destination);
    }
}
