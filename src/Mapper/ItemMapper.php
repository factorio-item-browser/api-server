<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\Item as ClientItem;
use FactorioItemBrowser\Api\Server\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;

/**
 * The class able to map items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemMapper
{
    /**
     * Maps the database item to a client item.
     * @param DatabaseItem $databaseItem
     * @param TranslationService $translationService
     * @return ClientItem
     */
    static public function mapDatabaseItemToClientItem(
        DatabaseItem $databaseItem,
        TranslationService $translationService
    ): ClientItem
    {
        $clientItem = new ClientItem();
        $clientItem
            ->setType($databaseItem->getType())
            ->setName($databaseItem->getName());

        $translationService->addEntityToTranslate($clientItem);
        return $clientItem;
    }
}