<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Command\TriggerCombinationUpdatesCommand;
use FactorioItemBrowser\Api\Server\Constant\CommandName;
use FactorioItemBrowser\Api\Server\Exception\RejectedCombinationUpdateException;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use FactorioItemBrowser\Api\Server\Service\CombinationUpdateService;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Common\Constant\Defaults;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The PHPUnit test of the TriggerCombinationUpdatesCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Command\TriggerCombinationUpdatesCommand
 */
class TriggerCombinationUpdatesCommandTest extends TestCase
{
    use ReflectionTrait;

    /** @var CombinationRepository&MockObject */
    private CombinationRepository $combinationRepository;
    /** @var CombinationUpdateService&MockObject */
    private CombinationUpdateService $combinationUpdateService;
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->combinationRepository = $this->createMock(CombinationRepository::class);
        $this->combinationUpdateService = $this->createMock(CombinationUpdateService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return TriggerCombinationUpdatesCommand&MockObject
     */
    private function createInstance(array $mockedMethods = []): TriggerCombinationUpdatesCommand
    {
        return $this->getMockBuilder(TriggerCombinationUpdatesCommand::class)
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->combinationRepository,
                        $this->combinationUpdateService,
                        $this->entityManager,
                    ])
                    ->getMock();
    }

    /**
     * @throws ReflectionException
     */
    public function testConfigure(): void
    {
        $instance = $this->createInstance(['setName', 'setDescription', 'addOption']);
        $instance->expects($this->once())
                 ->method('setName')
                 ->with($this->identicalTo(CommandName::TRIGGER_COMBINATION_UPDATES));
        $instance->expects($this->once())
                 ->method('setDescription')
                 ->with($this->isType('string'));
        $instance->expects($this->exactly(4))
                 ->method('addOption')
                 ->withConsecutive(
                     [
                         $this->identicalTo('last-usage'),
                         $this->identicalTo(''),
                         $this->identicalTo(InputOption::VALUE_REQUIRED),
                         $this->isType('string'),
                     ],
                     [
                         $this->identicalTo('update-check'),
                         $this->identicalTo(''),
                         $this->identicalTo(InputOption::VALUE_REQUIRED),
                         $this->isType('string'),
                     ],
                     [
                         $this->identicalTo('limit'),
                         $this->identicalTo(''),
                         $this->identicalTo(InputOption::VALUE_REQUIRED),
                         $this->isType('string'),
                     ],
                     [
                         $this->identicalTo('dry-run'),
                         $this->identicalTo(''),
                         $this->identicalTo(InputOption::VALUE_NONE),
                         $this->isType('string'),
                     ],
                 );

        $this->invokeMethod($instance, 'configure');
    }

    /**
     * @throws ReflectionException
     */
    public function testExecute(): void
    {
        $lastUsage = 'P30D';
        $updateCheck = 'P1D';
        $limit = 8;
        $dryRun = false;
        $factorioVersion = '1.2.3';
        $expectedResult = 0;
        $updateHash1 = Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1');
        $exception2 = new RejectedCombinationUpdateException('test exception');

        $input = $this->createMock(InputInterface::class);
        $input->expects($this->exactly(4))
              ->method('getOption')
              ->withConsecutive(
                  [$this->identicalTo('last-usage')],
                  [$this->identicalTo('update-check')],
                  [$this->identicalTo('limit')],
                  [$this->identicalTo('dry-run')],
              )
              ->willReturnOnConsecutiveCalls(
                  $lastUsage,
                  $updateCheck,
                  $limit,
                  $dryRun,
              );

        $output = $this->createMock(OutputInterface::class);

        $combination1 = $this->createMock(Combination::class);
        $combination1->expects($this->once())
                     ->method('setLastUpdateCheckTime')
                     ->with($this->isInstanceOf(DateTime::class))
                     ->willReturnSelf();
        $combination1->expects($this->once())
                     ->method('setLastUpdateHash')
                     ->with($this->identicalTo($updateHash1))
                     ->willReturnSelf();
        $combination2 = $this->createMock(Combination::class);
        $combination2->expects($this->once())
                     ->method('setLastUpdateCheckTime')
                     ->with($this->isInstanceOf(DateTime::class))
                     ->willReturnSelf();
        $combination2->expects($this->never())
                     ->method('setLastUpdateHash');

        $this->combinationRepository->expects($this->once())
                                    ->method('findPossibleCombinationsForUpdate')
                                    ->with(
                                        $this->isInstanceOf(DateTime::class),
                                        $this->isInstanceOf(DateTime::class),
                                        $this->identicalTo($limit),
                                    )
                                    ->willReturn([$combination1, $combination2]);

        $this->combinationUpdateService->expects($this->exactly(2))
                                       ->method('checkCombination')
                                       ->withConsecutive(
                                           [$this->identicalTo($combination1), $this->identicalTo($factorioVersion)],
                                           [$this->identicalTo($combination2), $this->identicalTo($factorioVersion)],
                                       )
                                       ->willReturnOnConsecutiveCalls(
                                           new FulfilledPromise($updateHash1),
                                           new RejectedPromise($exception2),
                                       );
        $this->combinationUpdateService->expects($this->once())
                                       ->method('triggerUpdate')
                                       ->with($this->identicalTo($combination1));

        $this->entityManager->expects($this->exactly(2))
                            ->method('persist')
                            ->withConsecutive(
                                [$this->identicalTo($combination1)],
                                [$this->identicalTo($combination2)],
                            );
        $this->entityManager->expects($this->once())
                            ->method('flush');

        $instance = $this->createInstance(['detectFactorioVersion']);
        $instance->expects($this->once())
                 ->method('detectFactorioVersion')
                 ->willReturn($factorioVersion);

        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testExecuteWithDryRun(): void
    {
        $lastUsage = 'P30D';
        $updateCheck = 'P1D';
        $limit = 8;
        $dryRun = true;
        $factorioVersion = '1.2.3';
        $expectedResult = 0;
        $updateHash1 = Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1');
        $exception2 = new RejectedCombinationUpdateException('test exception');

        $input = $this->createMock(InputInterface::class);
        $input->expects($this->exactly(4))
              ->method('getOption')
              ->withConsecutive(
                  [$this->identicalTo('last-usage')],
                  [$this->identicalTo('update-check')],
                  [$this->identicalTo('limit')],
                  [$this->identicalTo('dry-run')],
              )
              ->willReturnOnConsecutiveCalls(
                  $lastUsage,
                  $updateCheck,
                  $limit,
                  $dryRun,
              );

        $output = $this->createMock(OutputInterface::class);

        $combination1 = $this->createMock(Combination::class);
        $combination1->expects($this->never())
                     ->method('setLastUpdateCheckTime');
        $combination1->expects($this->never())
                     ->method('setLastUpdateHash');
        $combination2 = $this->createMock(Combination::class);
        $combination2->expects($this->never())
                     ->method('setLastUpdateCheckTime');
        $combination2->expects($this->never())
                     ->method('setLastUpdateHash');

        $this->combinationRepository->expects($this->once())
                                    ->method('findPossibleCombinationsForUpdate')
                                    ->with(
                                        $this->isInstanceOf(DateTime::class),
                                        $this->isInstanceOf(DateTime::class),
                                        $this->identicalTo($limit),
                                    )
                                    ->willReturn([$combination1, $combination2]);

        $this->combinationUpdateService->expects($this->exactly(2))
                                       ->method('checkCombination')
                                       ->withConsecutive(
                                           [$this->identicalTo($combination1), $this->identicalTo($factorioVersion)],
                                           [$this->identicalTo($combination2), $this->identicalTo($factorioVersion)],
                                       )
                                       ->willReturnOnConsecutiveCalls(
                                           new FulfilledPromise($updateHash1),
                                           new RejectedPromise($exception2),
                                       );
        $this->combinationUpdateService->expects($this->never())
                                       ->method('triggerUpdate');

        $this->entityManager->expects($this->never())
                            ->method('persist');
        $this->entityManager->expects($this->never())
                            ->method('flush');

        $instance = $this->createInstance(['detectFactorioVersion']);
        $instance->expects($this->once())
                 ->method('detectFactorioVersion')
                 ->willReturn($factorioVersion);

        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testDetectFactorioVersion(): void
    {
        $expectedResult = '2.3.4';

        $mod1 = new Mod();
        $mod1->setName('foo')
             ->setVersion('1.2.3');
        $mod2 = new Mod();
        $mod2->setName(Constant::MOD_NAME_BASE)
             ->setVersion('2.3.4');

        $combination = new Combination();
        $combination->getMods()->add($mod1);
        $combination->getMods()->add($mod2);

        $this->combinationRepository->expects($this->once())
                                    ->method('findById')
                                    ->with($this->equalTo(Uuid::fromString(Defaults::COMBINATION_ID)))
                                    ->willReturn($combination);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'detectFactorioVersion');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testDetectFactorioVersionWithoutCombination(): void
    {
        $this->combinationRepository->expects($this->once())
                                    ->method('findById')
                                    ->with($this->equalTo(Uuid::fromString(Defaults::COMBINATION_ID)))
                                    ->willReturn(null);

        $this->expectException(ServerException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'detectFactorioVersion');
    }

    /**
     * @throws ReflectionException
     */
    public function testDetectFactorioVersionWithoutBaseMod(): void
    {
        $mod1 = new Mod();
        $mod1->setName('foo')
             ->setVersion('1.2.3');
        $mod2 = new Mod();
        $mod2->setName('bar')
             ->setVersion('2.3.4');

        $combination = new Combination();
        $combination->getMods()->add($mod1);
        $combination->getMods()->add($mod2);

        $this->combinationRepository->expects($this->once())
                                    ->method('findById')
                                    ->with($this->equalTo(Uuid::fromString(Defaults::COMBINATION_ID)))
                                    ->willReturn($combination);

        $this->expectException(ServerException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'detectFactorioVersion');
    }
}
