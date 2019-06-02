<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use FactorioItemBrowser\Api\Server\SearchDecorator\SearchDecoratorInterface;
use FactorioItemBrowser\Api\Server\Service\SearchDecoratorService;
use FactorioItemBrowser\Api\Server\Service\SearchDecoratorServiceFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the SearchDecoratorServiceFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\SearchDecoratorServiceFactory
 */
class SearchDecoratorServiceFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $aliases = ['abc', 'def'];
        
        $config = [
            ConfigKey::PROJECT => [
                ConfigKey::API_SERVER => [
                    ConfigKey::SEARCH_DECORATORS => $aliases,
                ],
            ],
        ];
        
        $searchDecorators = [
            $this->createMock(SearchDecoratorInterface::class),
            $this->createMock(SearchDecoratorInterface::class),
        ];

        $expectedResult = new SearchDecoratorService($searchDecorators);
        
        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('config'))
                  ->willReturn($config);
        
        /* @var SearchDecoratorServiceFactory&MockObject $factory */
        $factory = $this->getMockBuilder(SearchDecoratorServiceFactory::class)
                        ->setMethods(['createSearchDecorators'])
                        ->getMock();
        $factory->expects($this->once())
                ->method('createSearchDecorators')
                ->with($this->identicalTo($container), $this->identicalTo($aliases))
                ->willReturn($searchDecorators);

        $result = $factory($container, SearchDecoratorService::class);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createSearchDecorators method.
     * @throws ReflectionException
     * @covers ::createSearchDecorators
     */
    public function testCreateSearchDecorators(): void
    {
        $aliases = ['abc', 'def'];

        /* @var SearchDecoratorInterface&MockObject $searchDecorator1 */
        $searchDecorator1 = $this->createMock(SearchDecoratorInterface::class);
        /* @var SearchDecoratorInterface&MockObject $searchDecorator2 */
        $searchDecorator2 = $this->createMock(SearchDecoratorInterface::class);

        $expectedResult = [$searchDecorator1, $searchDecorator2];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [$this->identicalTo('abc')],
                      [$this->identicalTo('def')]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $searchDecorator1,
                      $searchDecorator2
                  );

        $factory = new SearchDecoratorServiceFactory();
        $result = $this->invokeMethod($factory, 'createSearchDecorators', $container, $aliases);

        $this->assertEquals($expectedResult, $result);
    }
}
