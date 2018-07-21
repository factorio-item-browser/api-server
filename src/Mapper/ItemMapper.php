<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Server\Database\Entity\Item as DatabaseItem;

/**
 * The class able to map items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemMapper extends AbstractMapper
{
    /**
     * Maps the database item into the specified client item.
     * @param DatabaseItem $databaseItem
     * @param GenericEntity $clientItem
     * @return GenericEntity
     */
    public function mapItem(DatabaseItem $databaseItem, GenericEntity $clientItem): GenericEntity
    {
        $clientItem->setType($databaseItem->getType())
                   ->setName($databaseItem->getName());

        $this->translationService->addEntityToTranslate($clientItem);
        return $clientItem;
    }
}
