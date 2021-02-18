<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Transfer\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Common\Constant\EntityType;

/**
 * The class able to map database items to generic entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements DynamicMapperInterface<DatabaseRecipe, GenericEntity>
 */
class DatabaseRecipeToGenericEntityMapper extends TranslationServiceAwareMapper implements DynamicMapperInterface
{
    public function supports(object $source, object $destination): bool
    {
        return $source instanceof DatabaseRecipe
            && $destination instanceof GenericEntity
            && !$destination instanceof ClientRecipe;
    }

    /**
     * @param DatabaseRecipe $source
     * @param GenericEntity $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = EntityType::RECIPE;
        $destination->name = $source->getName();

        $this->addToTranslationService($destination);
    }
}
