<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Recipe;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeListRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeListResponse;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeListHandler;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the RecipeListHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeListHandler
 */
class RecipeListHandlerTest extends TestCase
{
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var RecipeService&MockObject */
    private RecipeService $recipeService;

    protected function setUp(): void
    {
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeService = $this->createMock(RecipeService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RecipeListHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeListHandler
    {
        return $this->getMockBuilder(RecipeListHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->mapperManager,
                        $this->recipeService,
                    ])
                    ->getMock();
    }

    public function testHandle(): void
    {
        $clientRequest = new RecipeListRequest();
        $clientRequest->combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $clientRequest->numberOfResults = 21;
        $clientRequest->indexOfFirstResult = 42;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($clientRequest);

        $limitedData = $this->createMock(RecipeDataCollection::class);
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('limitNames')
                   ->with($this->identicalTo(21), $this->identicalTo(42))
                   ->willReturn($limitedData);
        $recipeData->expects($this->once())
                   ->method('countNames')
                   ->willReturn(1337);

        $recipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];

        $mappedRecipes = new GenericEntityWithRecipes();
        $mappedRecipes->recipes = $recipes;

        $expectedPayload = new RecipeListResponse();
        $expectedPayload->recipes = $recipes;
        $expectedPayload->totalNumberOfResults = 1337;

        $this->recipeService->expects($this->once())
                            ->method('getAllData')
                            ->with($this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')))
                            ->willReturn($recipeData);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with(
                                $this->identicalTo($limitedData),
                                $this->isInstanceOf(GenericEntityWithRecipes::class),
                            )
                            ->willReturn($mappedRecipes);

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $result);
        /* @var ClientResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }
}
