<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Search\SearchCacheClearInterface;
use FactorioItemBrowser\Api\Server\Command\CleanCacheCommand;
use FactorioItemBrowser\Api\Server\Constant\CommandName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The PHPUnit test of the CleanCacheCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Command\CleanCacheCommand
 */
class CleanCacheCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked search cache clear.
     * @var SearchCacheClearInterface&MockObject
     */
    protected $searchCacheClear;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->searchCacheClear = $this->createMock(SearchCacheClearInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $command = new CleanCacheCommand($this->searchCacheClear);

        $this->assertSame($this->searchCacheClear, $this->extractProperty($command, 'searchCacheClear'));
    }

    /**
     * Tests the configure method.
     * @throws ReflectionException
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        /* @var CleanCacheCommand&MockObject $command */
        $command = $this->getMockBuilder(CleanCacheCommand::class)
                        ->onlyMethods(['setName', 'setDescription'])
                        ->setConstructorArgs([$this->searchCacheClear])
                        ->getMock();
        $command->expects($this->once())
                ->method('setName')
                ->with($this->identicalTo(CommandName::CLEAN_CACHE));
        $command->expects($this->once())
                ->method('setDescription')
                ->with($this->isType('string'));

        $this->invokeMethod($command, 'configure');
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        /* @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        /* @var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);

        $this->searchCacheClear->expects($this->once())
                               ->method('clearExpiredResults');

        $command = new CleanCacheCommand($this->searchCacheClear);
        $result = $this->invokeMethod($command, 'execute', $input, $output);

        $this->assertSame(0, $result);
    }
}
