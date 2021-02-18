<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Common\Constant\EntityType;

/**
 * The class mapping recipe data to generic entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<RecipeData, GenericEntity>
 */
class RecipeDataToGenericEntityMapper extends TranslationServiceAwareMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return RecipeData::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return GenericEntity::class;
    }

    /**
     * @param RecipeData $source
     * @param GenericEntity $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = EntityType::RECIPE;
        $destination->name = $source->getName();

        $this->addToTranslationService($destination);
    }
}
