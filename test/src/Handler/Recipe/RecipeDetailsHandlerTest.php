<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Recipe;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeDetailsHandler;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the RecipeDetailsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeDetailsHandler
 */
class RecipeDetailsHandlerTest extends TestCase
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
     * @return RecipeDetailsHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeDetailsHandler
    {
        return $this->getMockBuilder(RecipeDetailsHandler::class)
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
        $clientRequest = new RecipeDetailsRequest();
        $clientRequest->combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $clientRequest->names = ['abc', 'def'];

        $recipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];
        $recipeData = $this->createMock(RecipeDataCollection::class);

        $mappedRecipeData = new GenericEntityWithRecipes();
        $mappedRecipeData->recipes = $recipes;

        $expectedPayload = new RecipeDetailsResponse();
        $expectedPayload->recipes = $recipes;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($clientRequest);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithNames')
                            ->with(
                                $this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')),
                                $this->equalTo(['abc', 'def'])
                            )
                            ->willReturn($recipeData);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with(
                                $this->identicalTo($recipeData),
                                $this->isInstanceOf(GenericEntityWithRecipes::class),
                            )
                            ->willReturn($mappedRecipeData);

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $result);
        /* @var ClientResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }
}
