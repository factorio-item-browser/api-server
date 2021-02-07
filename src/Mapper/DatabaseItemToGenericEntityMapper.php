<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;

/**
 * The class able to map database items to generic entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements DynamicMapperInterface<DatabaseItem, GenericEntity>
 */
class DatabaseItemToGenericEntityMapper extends TranslationServiceAwareMapper implements DynamicMapperInterface
{
    public function supports(object $source, object $destination): bool
    {
        return $source instanceof DatabaseItem && $destination instanceof GenericEntity;
    }

    /**
     * @param DatabaseItem $source
     * @param GenericEntity $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = $source->getType();
        $destination->name = $source->getName();

        $this->addToTranslationService($destination);
    }
}
