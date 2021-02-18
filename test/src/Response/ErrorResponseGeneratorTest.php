<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Response;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use FactorioItemBrowser\Api\Server\Response\ErrorResponseGenerator;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the ErrorResponseGenerator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Response\ErrorResponseGenerator
 */
class ErrorResponseGeneratorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked logger.
     * @var LoggerInterface&MockObject
     */
    protected $logger;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $generator = new ErrorResponseGenerator($this->logger, true);

        $this->assertSame($this->logger, $this->extractProperty($generator, 'logger'));
        $this->assertTrue($this->extractProperty($generator, 'isDebug'));
    }

    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $exception = new Exception();
        $expectedStatusCode = 500;
        $expectedMessage = 'Internal server error.';
        $responseError = [
            'abc' => 'def',
        ];
        $expectedPayload = [
            'error' => [
                'abc' => 'def',
            ],
        ];

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var ErrorResponseGenerator&MockObject $generator */
        $generator = $this->getMockBuilder(ErrorResponseGenerator::class)
                          ->onlyMethods(['logException', 'createResponseError'])
                          ->setConstructorArgs([$this->logger, true])
                          ->getMock();
        $generator->expects($this->once())
                  ->method('logException')
                  ->with($this->identicalTo($expectedStatusCode), $this->identicalTo($exception));
        $generator->expects($this->once())
                  ->method('createResponseError')
                  ->with($this->identicalTo($expectedMessage), $this->identicalTo($exception))
                  ->willReturn($responseError);

        $result = $generator($exception, $request, $response);

        $this->assertInstanceOf(JsonResponse::class, $result);
        /* @var JsonResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }

    /**
     * Tests the invoking with an ApiServerException.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvokeWithApiServerException(): void
    {
        $exception = new ServerException('foo', 123);
        $expectedStatusCode = 123;
        $expectedMessage = 'foo';
        $responseError = [
            'abc' => 'def',
        ];
        $expectedPayload = [
            'error' => [
                'abc' => 'def',
            ],
        ];

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var ErrorResponseGenerator&MockObject $generator */
        $generator = $this->getMockBuilder(ErrorResponseGenerator::class)
                          ->onlyMethods(['logException', 'createResponseError'])
                          ->setConstructorArgs([$this->logger, true])
                          ->getMock();
        $generator->expects($this->once())
                  ->method('logException')
                  ->with($this->identicalTo($expectedStatusCode), $this->identicalTo($exception));
        $generator->expects($this->once())
                  ->method('createResponseError')
                  ->with($this->identicalTo($expectedMessage), $this->identicalTo($exception))
                  ->willReturn($responseError);

        $result = $generator($exception, $request, $response);

        $this->assertInstanceOf(JsonResponse::class, $result);
        /* @var JsonResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }

    /**
     * Tests the logException method.
     * @throws ReflectionException
     * @covers ::logException
     */
    public function testLogException(): void
    {
        $statusCode = 500;
        /* @var Exception&MockObject $exception */
        $exception = $this->createMock(Exception::class);

        $this->logger->expects($this->once())
                     ->method('crit')
                     ->with($this->identicalTo($exception));

        $generator = new ErrorResponseGenerator($this->logger, true);
        $this->invokeMethod($generator, 'logException', $statusCode, $exception);
    }

    /**
     * Tests the logException method with a statusCode outside the logged range.
     * @throws ReflectionException
     * @covers ::logException
     */
    public function testLogExceptionWithInvalidStatusCode(): void
    {
        $statusCode = 400;
        /* @var Exception&MockObject $exception */
        $exception = $this->createMock(Exception::class);

        $this->logger->expects($this->never())
                     ->method('crit');

        $generator = new ErrorResponseGenerator($this->logger, true);
        $this->invokeMethod($generator, 'logException', $statusCode, $exception);
    }

    /**
     * Tests the createResponseError method with debug mode.
     * @throws ReflectionException
     * @covers ::createResponseError
     */
    public function testCreateResponseErrorWithDebug(): void
    {
        $message = 'abc';
        $exceptionMessage = 'def';
        $exception = new Exception($exceptionMessage);

        $generator = new ErrorResponseGenerator($this->logger, true);
        $result = $this->invokeMethod($generator, 'createResponseError', $message, $exception);

        $this->assertArrayHasKey('message', $result);
        $this->assertSame($exceptionMessage, $result['message']);
        $this->assertArrayHasKey('backtrace', $result);
        $this->assertIsArray($result['backtrace']);
    }

    /**
     * Tests the createResponseError method without debug mode.
     * @throws ReflectionException
     * @covers ::createResponseError
     */
    public function testCreateResponseErrorWithoutDebug(): void
    {
        $message = 'abc';
        $exceptionMessage = 'def';
        $exception = new Exception($exceptionMessage);
        $expectedResult = [
            'message' => 'abc',
        ];

        $generator = new ErrorResponseGenerator($this->logger, false);
        $result = $this->invokeMethod($generator, 'createResponseError', $message, $exception);

        $this->assertEquals($expectedResult, $result);
    }
}
