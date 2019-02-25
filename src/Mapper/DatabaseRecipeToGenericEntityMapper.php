<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Entity\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;

/**
 * The class able to map database items to generic entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DatabaseRecipeToGenericEntityMapper extends TranslationServiceAwareMapper implements DynamicMapperInterface
{
    /**
     * Returns whether the mapper supports the combination of source and destination object.
     * @param object $source
     * @param object $destination
     * @return bool
     */
    public function supports($source, $destination): bool
    {
        return $source instanceof DatabaseRecipe
            && $destination instanceof GenericEntity
            && !$destination instanceof ClientRecipe;
    }

    /**
     * Maps the source object to the destination one.
     * @param DatabaseRecipe $databaseRecipe
     * @param GenericEntity $genericEntity
     */
    public function map($databaseRecipe, $genericEntity): void
    {
        $genericEntity->setType(EntityType::RECIPE)
                      ->setName($databaseRecipe->getName());

        $this->addToTranslationService($genericEntity);
    }
}
