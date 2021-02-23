<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\SearchDecorator;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;
use FactorioItemBrowser\Api\Server\SearchDecorator\AbstractEntityDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the AbstractEntityDecorator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\SearchDecorator\AbstractEntityDecorator
 */
class AbstractEntityDecoratorTest extends TestCase
{
    use ReflectionTrait;

    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    private int $numberOfRecipesPerResult = 42;

    protected function setUp(): void
    {
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return AbstractEntityDecorator<ResultInterface>&MockObject
     */
    private function createInstance(array $mockedMethods = []): AbstractEntityDecorator
    {
        $instance = $this->getMockBuilder(AbstractEntityDecorator::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->setConstructorArgs([
                             $this->mapperManager,
                         ])
                         ->getMockForAbstractClass();
        $instance->initialize($this->numberOfRecipesPerResult);
        return $instance;
    }

    /**
     * @throws ReflectionException
     */
    public function testAddAnnouncedId(): void
    {
        $id1 = Uuid::fromString('1435b65c-b98b-4ba5-94e3-0fd9ff016fec');
        $id2 = Uuid::fromString('2c007cf8-ff80-417b-a245-9ba6a8625035');

        $instance = $this->createInstance();
        $this->assertSame([], $this->extractProperty($instance, 'announcedIds'));

        $this->invokeMethod($instance, 'addAnnouncedId', $id1);
        $this->assertSame([
            '1435b65c-b98b-4ba5-94e3-0fd9ff016fec' => $id1,
        ], $this->extractProperty($instance, 'announcedIds'));

        $this->invokeMethod($instance, 'addAnnouncedId', null);
        $this->assertSame([
            '1435b65c-b98b-4ba5-94e3-0fd9ff016fec' => $id1,
        ], $this->extractProperty($instance, 'announcedIds'));

        $this->invokeMethod($instance, 'addAnnouncedId', $id2);
        $this->assertSame([
            '1435b65c-b98b-4ba5-94e3-0fd9ff016fec' => $id1,
            '2c007cf8-ff80-417b-a245-9ba6a8625035' => $id2,
        ], $this->extractProperty($instance, 'announcedIds'));

        $this->invokeMethod($instance, 'addAnnouncedId', $id2);
        $this->assertSame([
            '1435b65c-b98b-4ba5-94e3-0fd9ff016fec' => $id1,
            '2c007cf8-ff80-417b-a245-9ba6a8625035' => $id2,
        ], $this->extractProperty($instance, 'announcedIds'));
    }

    /**
     * @throws ReflectionException
     */
    public function testPrepare(): void
    {
        $announcedIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];
        $entities = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        $instance = $this->createInstance(['fetchDatabaseEntities']);
        $instance->expects($this->once())
                 ->method('fetchDatabaseEntities')
                 ->with($this->equalTo($announcedIds))
                 ->willReturn($entities);
        $this->injectProperty($instance, 'announcedIds', $announcedIds);

        $instance->prepare();

        $this->assertSame($this->extractProperty($instance, 'databaseEntities'), $entities);
    }

    public function testDecorateWithRecipes(): void
    {
        $searchResult = $this->createMock(ResultInterface::class);
        $id = $this->createMock(UuidInterface::class);
        $entity = $this->createMock(GenericEntityWithRecipes::class);

        $instance = $this->createInstance(['getIdFromResult', 'mapEntityWithId', 'hydrateRecipes']);
        $instance->expects($this->once())
                 ->method('getIdFromResult')
                 ->with($this->identicalTo($searchResult))
                 ->willReturn($id);
        $instance->expects($this->once())
                 ->method('mapEntityWithId')
                 ->with($this->identicalTo($id), $this->isInstanceOf(GenericEntityWithRecipes::class))
                 ->willReturn($entity);
        $instance->expects($this->once())
                 ->method('hydrateRecipes')
                 ->with($this->identicalTo($searchResult), $this->identicalTo($entity));

        $result = $instance->decorate($searchResult);

        $this->assertSame($entity, $result);
    }

    public function testDecorateWithoutRecipes(): void
    {
        $searchResult = $this->createMock(ResultInterface::class);
        $id = $this->createMock(UuidInterface::class);
        $entity = $this->createMock(GenericEntity::class);

        $this->numberOfRecipesPerResult = 0;

        $instance = $this->createInstance(['getIdFromResult', 'mapEntityWithId', 'hydrateRecipes']);
        $instance->expects($this->once())
                 ->method('getIdFromResult')
                 ->with($this->identicalTo($searchResult))
                 ->willReturn($id);
        $instance->expects($this->once())
                 ->method('mapEntityWithId')
                 ->with($this->identicalTo($id), $this->isInstanceOf(GenericEntity::class))
                 ->willReturn($entity);
        $instance->expects($this->never())
                 ->method('hydrateRecipes');

        $result = $instance->decorate($searchResult);

        $this->assertSame($entity, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideMapEntityWithId(): array
    {
        $entity1 = $this->createMock(Item::class);
        $entity2 = $this->createMock(Item::class);
        $entities = [
            '1435b65c-b98b-4ba5-94e3-0fd9ff016fec' => $entity1,
            '2c007cf8-ff80-417b-a245-9ba6a8625035' => $entity2,
        ];

        return [
            [$entities, Uuid::fromString('1435b65c-b98b-4ba5-94e3-0fd9ff016fec'), $entity1, false],
            [$entities, Uuid::fromString('fb2e552f-3e94-4297-8050-e434fd79d42f'), null, true],
            [$entities, null, null, true],
        ];
    }

    /**
     * @dataProvider provideMapEntityWithId
     * @param array<string, object> $entities
     * @param UuidInterface|null $id
     * @param object|null $expectedEntity
     * @param bool $expectNull
     * @throws ReflectionException
     */
    public function testMapEntityWithId(
        array $entities,
        ?UuidInterface $id,
        ?object $expectedEntity,
        bool $expectNull
    ): void {
        $destination = $this->createMock(GenericEntity::class);
        $mappedDestination = $this->createMock(GenericEntity::class);

        $this->mapperManager->expects($expectedEntity === null ? $this->never() : $this->once())
                            ->method('map')
                            ->with($this->identicalTo($expectedEntity), $this->identicalTo($destination))
                            ->willReturn($mappedDestination);

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'databaseEntities', $entities);

        $result = $this->invokeMethod($instance, 'mapEntityWithId', $id, $destination);

        if ($expectNull) {
            $this->assertNull($result);
        } else {
            $this->assertSame($mappedDestination, $result);
        }
    }
}
