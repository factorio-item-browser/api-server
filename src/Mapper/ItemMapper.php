<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;

/**
 * The class able to map items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemMapper extends TranslationServiceAwareMapper implements DynamicMapperInterface
{
    /**
     * Returns whether the mapper supports the combination of source and destination object.
     * @param object $source
     * @param object $destination
     * @return bool
     */
    public function supports($source, $destination): bool
    {
        return $source instanceof DatabaseItem && $destination instanceof GenericEntity;
    }

    /**
     * Maps the source object to the destination one.
     * @param DatabaseItem $databaseItem
     * @param GenericEntity $clientItem
     */
    public function map($databaseItem, $clientItem): void
    {
        $clientItem->setType($databaseItem->getType())
                   ->setName($databaseItem->getName());

        $this->addToTranslationService($clientItem);
    }
}
