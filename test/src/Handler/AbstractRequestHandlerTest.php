<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\EntityInterface;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
use Zend\InputFilter\InputFilter;

/**
 * The PHPUnit test of the AbstractRequestHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler
 */
class AbstractRequestHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Provides the data for the handle test.
     * @return array
     */
    public function provideProcess(): array
    {
        return [
            [true, false],
            [false, true]
        ];
    }

    /**
     * Tests the handle method.
     * @param bool $isValid
     * @param bool $expectException
     * @covers ::handle
     * @dataProvider provideProcess
     */
    public function testHandle(bool $isValid, bool $expectException)
    {
        if ($expectException) {
            $this->expectException(ApiServerException::class);
        }

        $parsedBody = ['abc' => 'def'];
        $values = ['ghi' => 'jkl'];
        $messages = ['mno' => 'pqr'];
        $responseData = ['stu' => 'vwx'];
        $convertedResponseData = ['uts' => 'xwv'];

        /* @var ServerRequest|MockObject $request */
        $request = $this->getMockBuilder(ServerRequest::class)
                        ->setMethods(['getParsedBody'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($parsedBody);

        /* @var InputFilter|MockObject $inputFilter */
        $inputFilter = $this->getMockBuilder(InputFilter::class)
                            ->setMethods(['setData', 'isValid', 'getMessages', 'getValues'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $inputFilter->expects($this->once())
                    ->method('setData')
                    ->with($parsedBody);
        $inputFilter->expects($this->once())
                    ->method('isValid')
                    ->willReturn($isValid);
        $inputFilter->expects($expectException ? $this->once() : $this->never())
                    ->method('getMessages')
                    ->willReturn($messages);
        $inputFilter->expects($expectException ? $this->never() : $this->once())
                    ->method('getValues')
                    ->willReturn($values);

        /* @var AbstractRequestHandler|MockObject $handler */
        $handler = $this->getMockBuilder(AbstractRequestHandler::class)
                        ->setMethods(['createInputFilter', 'handleRequest', 'convertDataToArray'])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('createInputFilter')
                ->willReturn($inputFilter);
        $handler->expects($expectException ? $this->never() : $this->once())
                ->method('handleRequest')
                ->with($this->callback(function ($param) use ($values) {
                    $this->assertInstanceOf(DataContainer::class, $param);
                    /* @var DataContainer $param */
                    $this->assertSame($values, $param->getData());
                    return true;
                }))
                ->willReturn($responseData);
        $handler->expects($expectException ? $this->never() : $this->once())
                ->method('convertDataToArray')
                ->with($responseData)
                ->willReturn($convertedResponseData);

        $response = $handler->handle($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        /* @var JsonResponse $response */
        $this->assertSame($convertedResponseData, $response->getPayload());
    }

    /**
     * Tests the convertDataToArray method.
     * @covers ::convertDataToArray
     */
    public function testConvertDataToArray()
    {
        /* @var EntityInterface|MockObject $entity */
        $entity = $this->getMockBuilder(EntityInterface::class)
                       ->setMethods(['writeData'])
                       ->getMockForAbstractClass();
        $entity->expects($this->exactly(2))
               ->method('writeData')
               ->willReturn(['foo' => 'bar']);

        $data = [
            'abc' => 'def',
            'ghi' => [
                'jkl' => 'mno',
                'pqr' => $entity,
            ],
            'stu' => $entity
        ];
        $expectedResult = [
            'abc' => 'def',
            'ghi' => [
                'jkl' => 'mno',
                'pqr' => [
                    'foo' => 'bar'
                ]
            ],
            'stu' => [
                'foo' => 'bar'
            ]
        ];

        /* @var AbstractRequestHandler $handler */
        $handler = $this->createMock(AbstractRequestHandler::class);
        $result = $this->invokeMethod($handler, 'convertDataToArray', $data);
        $this->assertSame($expectedResult, $result);
    }
}
