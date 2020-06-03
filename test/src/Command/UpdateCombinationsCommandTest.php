<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use Exception;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Command\UpdateCombinationsCommand;
use FactorioItemBrowser\Api\Server\Console\Console;
use FactorioItemBrowser\Api\Server\Constant\CommandName;
use FactorioItemBrowser\Api\Server\Entity\CombinationUpdate;
use FactorioItemBrowser\Api\Server\Service\CombinationUpdateService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The PHPUnit test of the UpdateCombinationsCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Command\UpdateCombinationsCommand
 */
class UpdateCombinationsCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked combination repository.
     * @var CombinationRepository&MockObject
     */
    protected $combinationRepository;

    /**
     * The mocked combination update service.
     * @var CombinationUpdateService&MockObject
     */
    protected $combinationUpdateService;

    /**
     * The mocked console.
     * @var Console&MockObject
     */
    protected $console;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->combinationRepository = $this->createMock(CombinationRepository::class);
        $this->combinationUpdateService = $this->createMock(CombinationUpdateService::class);
        $this->console = $this->createMock(Console::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $lastUsageInterval = 'abc';
        $maxNumberOfUpdates = 42;

        $command = new UpdateCombinationsCommand(
            $this->combinationRepository,
            $this->combinationUpdateService,
            $this->console,
            $lastUsageInterval,
            $maxNumberOfUpdates,
        );

        $this->assertSame($this->combinationRepository, $this->extractProperty($command, 'combinationRepository'));
        $this->assertSame(
            $this->combinationUpdateService,
            $this->extractProperty($command, 'combinationUpdateService')
        );
        $this->assertSame($this->console, $this->extractProperty($command, 'console'));
        $this->assertSame($lastUsageInterval, $this->extractProperty($command, 'lastUsageInterval'));
        $this->assertSame($maxNumberOfUpdates, $this->extractProperty($command, 'maxNumberOfUpdates'));
    }

    /**
     * Tests the configure method.
     * @throws ReflectionException
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        /* @var UpdateCombinationsCommand&MockObject $command */
        $command = $this->getMockBuilder(UpdateCombinationsCommand::class)
                        ->onlyMethods(['setName', 'setDescription'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $command->expects($this->once())
                ->method('setName')
                ->with($this->identicalTo(CommandName::UPDATE_COMBINATIONS));
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
        $lastUsageInterval = '-1 week';
        $maxNumberOfUpdates = 2;

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $combination1 = $this->createMock(Combination::class);
        $combination2 = $this->createMock(Combination::class);
        $combination3 = $this->createMock(Combination::class);
        $combination4 = $this->createMock(Combination::class);
        $combination5 = $this->createMock(Combination::class);
        $combinations = [$combination1, $combination2, $combination3, $combination4, $combination5];

        $combinationUpdate1 = $this->createMock(CombinationUpdate::class);
        $combinationUpdate2 = $this->createMock(CombinationUpdate::class);
        $combinationUpdate3 = $this->createMock(CombinationUpdate::class);
        $combinationUpdate4 = $this->createMock(CombinationUpdate::class);
        $combinationUpdates = [$combinationUpdate1, $combinationUpdate2, $combinationUpdate3, $combinationUpdate4];
        $filteredCombinationUpdates = [$combinationUpdate1, $combinationUpdate2, $combinationUpdate4];
        $sortedCombinationUpdates = [$combinationUpdate1, $combinationUpdate4, $combinationUpdate2];
        $triggeredCombinationUpdates = [$combinationUpdate1, $combinationUpdate4];

        $this->combinationRepository->expects($this->once())
                                    ->method('findByLastUsageTime')
                                    ->with($this->isInstanceOf(DateTime::class))
                                    ->willReturn($combinations);

        $this->combinationUpdateService->expects($this->exactly(5))
                                       ->method('checkCombination')
                                       ->withConsecutive(
                                           [$this->identicalTo($combination1)],
                                           [$this->identicalTo($combination2)],
                                           [$this->identicalTo($combination3)],
                                           [$this->identicalTo($combination4)],
                                           [$this->identicalTo($combination5)],
                                       )
                                       ->willReturnOnConsecutiveCalls(
                                           $combinationUpdate1,
                                           $combinationUpdate2,
                                           null,
                                           $combinationUpdate3,
                                           $combinationUpdate4,
                                       );
        $this->combinationUpdateService->expects($this->once())
                                       ->method('requestExportStatus')
                                       ->with($this->identicalTo($combinationUpdates));
        $this->combinationUpdateService->expects($this->once())
                                       ->method('filter')
                                       ->with($this->identicalTo($combinationUpdates))
                                       ->willReturn($filteredCombinationUpdates);
        $this->combinationUpdateService->expects($this->once())
                                       ->method('sort')
                                       ->with($this->identicalTo($filteredCombinationUpdates))
                                       ->willReturn($sortedCombinationUpdates);
        $this->combinationUpdateService->expects($this->once())
                                       ->method('triggerExports')
                                       ->with($this->identicalTo($triggeredCombinationUpdates));

        $this->console->expects($this->any())
                      ->method('writeAction')
                      ->with($this->isType('string'));
        $this->console->expects($this->any())
                      ->method('writeMessage')
                      ->with($this->isType('string'));

        $command = new UpdateCombinationsCommand(
            $this->combinationRepository,
            $this->combinationUpdateService,
            $this->console,
            $lastUsageInterval,
            $maxNumberOfUpdates,
        );
        $result = $this->invokeMethod($command, 'execute', $input, $output);

        $this->assertSame(0, $result);
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecuteWithException(): void
    {
        $lastUsageInterval = '-1 week';
        $maxNumberOfUpdates = 2;

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        /* @var Exception&MockObject $exception */
        $exception = $this->createMock(Exception::class);

        $this->combinationRepository->expects($this->once())
                                    ->method('findByLastUsageTime')
                                    ->with($this->isInstanceOf(DateTime::class))
                                    ->willThrowException($exception);

        $this->console->expects($this->any())
                      ->method('writeAction')
                      ->with($this->isType('string'));
        $this->console->expects($this->any())
                      ->method('writeMessage')
                      ->with($this->isType('string'));
        $this->console->expects($this->any())
                      ->method('writeException')
                      ->with($this->identicalTo($exception));

        $command = new UpdateCombinationsCommand(
            $this->combinationRepository,
            $this->combinationUpdateService,
            $this->console,
            $lastUsageInterval,
            $maxNumberOfUpdates,
        );
        $result = $this->invokeMethod($command, 'execute', $input, $output);

        $this->assertSame(1, $result);
    }
}
