<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Combination;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\ValidatedMod;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationValidateRequest;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationValidateResponse;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Combination\CombinationValidateHandler;
use FactorioItemBrowser\Api\Server\Service\CombinationValidationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the CombinationValidateHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Combination\CombinationValidateHandler
 */
class CombinationValidateHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked combination validation service.
     * @var CombinationValidationService&MockObject
     */
    protected $combinationValidationService;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->combinationValidationService = $this->createMock(CombinationValidationService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new CombinationValidateHandler($this->combinationValidationService);

        $this->assertSame(
            $this->combinationValidationService,
            $this->extractProperty($handler, 'combinationValidationService'),
        );
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = CombinationValidateRequest::class;

        $handler = new CombinationValidateHandler($this->combinationValidationService);
        $result = $this->invokeMethod($handler, 'getExpectedRequestClass');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the handleRequest method.
     * @covers ::handleRequest
     * @throws ReflectionException
     */
    public function testHandleRequest(): void
    {
        $modNames = ['abc', 'def'];

        $authorizationToken = new AuthorizationToken();
        $authorizationToken->setModNames($modNames);

        $validatedMod1 = new ValidatedMod();
        $validatedMod1->setName('abc');
        $validatedMod2 = new ValidatedMod();
        $validatedMod2->setName('def');

        $validatedMods = [
            'abc' => $validatedMod1,
            'def' => $validatedMod2,
        ];

        $request = $this->createMock(CombinationValidateRequest::class);

        $expectedResult = new CombinationValidateResponse();
        $expectedResult->setValidatedMods($validatedMods)
                       ->setIsValid(true);

        $this->combinationValidationService->expects($this->once())
                                           ->method('validate')
                                           ->with($this->identicalTo($modNames))
                                           ->willReturn($validatedMods);
        $this->combinationValidationService->expects($this->once())
                                           ->method('areModsValid')
                                           ->with($this->identicalTo($validatedMods))
                                           ->willReturn(true);

        $handler = $this->getMockBuilder(CombinationValidateHandler::class)
                        ->onlyMethods(['getAuthorizationToken'])
                        ->setConstructorArgs([$this->combinationValidationService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertEquals($expectedResult, $result);
    }
}
