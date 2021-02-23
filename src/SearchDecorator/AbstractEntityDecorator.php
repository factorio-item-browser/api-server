<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\SearchDecorator;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * The abstract class of the decorators based on database entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @template TResult of ResultInterface
 * @implements SearchDecoratorInterface<TResult>
 */
abstract class AbstractEntityDecorator implements SearchDecoratorInterface
{
    protected MapperManagerInterface $mapperManager;

    protected int $numberOfRecipesPerResult = 0;
    /** @var array<string, UuidInterface> */
    protected array $announcedIds = [];
    /** @var array<string, object> */
    protected array $databaseEntities = [];

    public function __construct(MapperManagerInterface $mapperManager)
    {
        $this->mapperManager = $mapperManager;
    }

    public function initialize(int $numberOfRecipesPerResult): void
    {
        $this->numberOfRecipesPerResult = $numberOfRecipesPerResult;
        $this->announcedIds = [];
        $this->databaseEntities = [];
    }

    /**
     * Adds an id that was announced.
     * @param UuidInterface|null $id
     */
    protected function addAnnouncedId(?UuidInterface $id): void
    {
        if ($id !== null) {
            $this->announcedIds[$id->toString()] = $id;
        }
    }

    public function prepare(): void
    {
        $this->databaseEntities = $this->fetchDatabaseEntities(array_values($this->announcedIds));
    }

    /**
     * Fetches the entities with the announced ids from the database.
     * @param array<UuidInterface> $ids
     * @return array<string, object>
     */
    abstract protected function fetchDatabaseEntities(array $ids): array;

    public function decorate(ResultInterface $searchResult): ?GenericEntity
    {
        $id = $this->getIdFromResult($searchResult);
        $entity = $this->mapEntityWithId(
            $id,
            $this->numberOfRecipesPerResult > 0 ? new GenericEntityWithRecipes() : new GenericEntity(),
        );
        if ($entity instanceof GenericEntityWithRecipes) {
            $this->hydrateRecipes($searchResult, $entity);
        }
        return $entity;
    }

    /**
     * Returns the id from the search result.
     * @param TResult $searchResult
     * @return UuidInterface|null
     */
    abstract protected function getIdFromResult(ResultInterface $searchResult): ?UuidInterface;

    /**
     * Hydrates the recipes into the entity.
     * @param TResult $searchResult
     * @param GenericEntityWithRecipes $entity
     */
    abstract protected function hydrateRecipes(ResultInterface $searchResult, GenericEntityWithRecipes $entity): void;

    /**
     * Maps the entity with the specified id to the specified destination.
     * @template T of object
     * @param UuidInterface|null $id
     * @param T $destination
     * @return T|null
     */
    protected function mapEntityWithId(?UuidInterface $id, object $destination): ?object
    {
        if ($id === null) {
            return null;
        }

        $entity = $this->databaseEntities[$id->toString()] ?? null;
        if ($entity === null) {
            return null;
        }

        return $this->mapperManager->map($entity, $destination);
    }
}
