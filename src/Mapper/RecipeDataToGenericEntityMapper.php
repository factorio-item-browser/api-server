<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Common\Constant\EntityType;

/**
 * The class mapping recipe data to generic entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDataToGenericEntityMapper extends TranslationServiceAwareMapper implements StaticMapperInterface
{
    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return RecipeData::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return GenericEntity::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param RecipeData $recipeData
     * @param GenericEntity $genericEntity
     */
    public function map($recipeData, $genericEntity): void
    {
        $genericEntity->setType(EntityType::RECIPE)
                      ->setName($recipeData->getName());

        $this->addToTranslationService($genericEntity);
    }
}
