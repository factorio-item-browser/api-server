<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Request\Item\ItemProductRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemProductResponse;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use FactorioItemBrowser\Api\Server\Handler\Item\ItemProductHandler;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the ItemProductHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Item\ItemProductHandler
 */
class ItemProductHandlerTest extends TestCase
{
    use ReflectionTrait;

    /** @var ItemRepository&MockObject */
    private ItemRepository $itemRepository;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var RecipeService&MockObject */
    private RecipeService $recipeService;

    protected function setUp(): void
    {
        $this->itemRepository = $this->createMock(ItemRepository::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeService = $this->createMock(RecipeService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ItemProductHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): ItemProductHandler
    {
        return $this->getMockBuilder(ItemProductHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->itemRepository,
                        $this->mapperManager,
                        $this->recipeService,
                    ])
                    ->getMock();
    }

    /**
     * @throws ServerException
     */
    public function testHandle(): void
    {
        $clientRequest = new ItemProductRequest();
        $clientRequest->combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $clientRequest->type = 'abc';
        $clientRequest->name = 'def';
        $clientRequest->numberOfResults = 21;
        $clientRequest->indexOfFirstResult = 42;

        $item = $this->createMock(Item::class);
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $mappedItem = $this->createMock(GenericEntityWithRecipes::class);

        $expectedPayload = new ItemProductResponse();
        $expectedPayload->item = $mappedItem;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($clientRequest);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithProducts')
                            ->with(
                                $this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')),
                                $this->identicalTo([$item])
                            )
                            ->willReturn($recipeData);

        $instance = $this->createInstance(['fetchItem', 'createItem']);
        $instance->expects($this->once())
                 ->method('fetchItem')
                 ->with(
                     $this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')),
                     $this->identicalTo('abc'),
                     $this->identicalTo('def'),
                 )
                 ->willReturn($item);
        $instance->expects($this->once())
                 ->method('createItem')
                 ->with(
                     $this->identicalTo($item),
                     $this->identicalTo($recipeData),
                     $this->identicalTo(21),
                     $this->identicalTo(42),
                 )
                 ->willReturn($mappedItem);
        $result = $instance->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $result);
        /* @var ClientResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }
}
