<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Traits;

use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;

/**
 * The trait for extracting the names grouped by type from entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
trait TypeAndNameFromEntityExtractorTrait
{
    /**
     * Extracts the names of the entities, grouped by their types.
     * @param array|Entity[] $entities
     * @return NamesByTypes
     */
    protected function extractTypesAndNames(array $entities): NamesByTypes
    {
        $result = new NamesByTypes();
        foreach ($entities as $entity) {
            $result->addName($entity->getType(), $entity->getName());
        }
        return $result;
    }
}
