<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Response;

use Exception;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Response\ErrorResponseGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Log\LoggerInterface;

/**
 * The PHPUnit test of the ErrorResponseGenerator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Response\ErrorResponseGenerator
 */
class ErrorResponseGeneratorTest extends TestCase
{
    /**
     * Provides the data for the invoke test.
     * @return array
     */
    public function provideInvoke(): array
    {
        $exception = new Exception('abc');

        return [
            [
                new ApiServerException('abc', 418),
                false,
                null,
                [
                    'error' => [
                        'message' => 'abc'
                    ]
                ],
                418
            ],
            [
                (new ApiServerException('abc', 418))->addParameter('def', 'ghi'),
                true,
                null,
                [
                    'error' => [
                        'message' => 'abc',
                        'parameters' => [[
                            'name' => 'def',
                            'message' => 'ghi'
                        ]]
                    ]
                ],
                418
            ],
            [
                $exception,
                false,
                null,
                [
                    'error' => [
                        'message' => 'An unexpected error occurred.'
                    ]
                ],
                500
            ],
            [
                $exception,
                true,
                $exception,
                [
                    'error' => [
                        'message' => 'An unexpected error occurred.'
                    ]
                ],
                500
            ],
        ];
    }

    /**
     * Tests the invoke method.
     * @param Exception $exception
     * @param bool $useLogger
     * @param mixed $expectedLog
     * @param array $expectedPayload
     * @param int $expectedStatusCode
     * @covers ::__construct
     * @covers ::__invoke
     * @dataProvider provideInvoke
     */
    public function testInvoke(
        Exception $exception,
        bool $useLogger,
        $expectedLog,
        array $expectedPayload,
        int $expectedStatusCode
    ) {
        /* @var ServerRequest $request */
        $request = $this->createMock(ServerRequest::class);

        /* @var ResponseInterface|MockObject $response */
        $response = $this->getMockBuilder(ResponseInterface::class)
                         ->setMethods(['getHeaders'])
                         ->disableOriginalConstructor()
                         ->getMockForAbstractClass();
        $response->expects($this->once())
                 ->method('getHeaders')
                 ->willReturn(['foo' => 'bar']);

        if ($useLogger) {
            /* @var LoggerInterface|MockObject $logger */
            $logger = $this->getMockBuilder(LoggerInterface::class)
                           ->setMethods(['crit'])
                           ->getMockForAbstractClass();
            $logger->expects($expectedLog === null ? $this->never() : $this->once())
                   ->method('crit')
                   ->with($expectedLog);
        } else {
            $logger = null;
        }

        $generator = new ErrorResponseGenerator($logger);

        /* @var JsonResponse $result */
        $result = $generator($exception, $request, $response);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertSame($expectedPayload, $result->getPayload());
        $this->assertSame($expectedStatusCode, $result->getStatusCode());
        $this->assertSame('bar', $result->getHeaderLine('foo'));
    }
}
