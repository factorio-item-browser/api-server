<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Constant\Config;
use FactorioItemBrowser\Api\Server\Exception\InternalServerException;
use FactorioItemBrowser\Api\Server\Service\CombinationService;
use FactorioItemBrowser\Common\Constant\Constant;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the CombinationService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\CombinationService
 */
class CombinationServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked combination repository.
     * @var CombinationRepository&MockObject
     */
    protected $combinationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->combinationRepository = $this->createMock(CombinationRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new CombinationService($this->combinationRepository);

        $this->assertSame($this->combinationRepository, $this->extractProperty($service, 'combinationRepository'));
    }

    /**
     * Tests the getBaseMod method.
     * @throws InternalServerException
     * @covers ::getBaseMod
     */
    public function testGetBaseMod(): void
    {
        $expectedCombinationId = Uuid::fromString(Config::DEFAULT_COMBINATION_ID);

        $mod = new Mod();
        $mod->setName(Constant::MOD_NAME_BASE);

        $combination = new Combination();
        $combination->getMods()->add($mod);

        $this->combinationRepository->expects($this->once())
                                    ->method('findById')
                                    ->with($this->equalTo($expectedCombinationId))
                                    ->willReturn($combination);

        $service = new CombinationService($this->combinationRepository);
        $result = $service->getBaseMod();

        $this->assertSame($mod, $result);
    }

    /**
     * Tests the getBaseMod method.
     * @throws InternalServerException
     * @throws ReflectionException
     * @covers ::getBaseMod
     */
    public function testGetBaseModWithCachedMod(): void
    {
        $baseMod = $this->createMock(Mod::class);

        $this->combinationRepository->expects($this->never())
                                    ->method('findById');

        $service = new CombinationService($this->combinationRepository);
        $this->injectProperty($service, 'baseMod', $baseMod);

        $result = $service->getBaseMod();

        $this->assertSame($baseMod, $result);
    }

    /**
     * Tests the getBaseMod method.
     * @throws InternalServerException
     * @covers ::getBaseMod
     */
    public function testGetBaseModWithMissingMod(): void
    {
        $expectedCombinationId = Uuid::fromString(Config::DEFAULT_COMBINATION_ID);

        $mod = new Mod();
        $mod->setName('foo');

        $combination = new Combination();
        $combination->getMods()->add($mod);

        $this->combinationRepository->expects($this->once())
                                    ->method('findById')
                                    ->with($this->equalTo($expectedCombinationId))
                                    ->willReturn($combination);

        $this->expectException(InternalServerException::class);

        $service = new CombinationService($this->combinationRepository);
        $service->getBaseMod();
    }
}
