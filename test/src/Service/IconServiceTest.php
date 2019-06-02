<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Database\Data\IconData;
use FactorioItemBrowser\Api\Database\Entity\IconFile;
use FactorioItemBrowser\Api\Database\Filter\DataFilter;
use FactorioItemBrowser\Api\Database\Repository\IconFileRepository;
use FactorioItemBrowser\Api\Database\Repository\IconRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Server\Service\IconService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\IconService
 */
class IconServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked data filter.
     * @var DataFilter&MockObject
     */
    protected $dataFilter;

    /**
     * The mocked icon file repository.
     * @var IconFileRepository&MockObject
     */
    protected $iconFileRepository;

    /**
     * The mocked icon repository.
     * @var IconRepository&MockObject
     */
    protected $iconRepository;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dataFilter = $this->createMock(DataFilter::class);
        $this->iconFileRepository = $this->createMock(IconFileRepository::class);
        $this->iconRepository = $this->createMock(IconRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new IconService($this->dataFilter, $this->iconFileRepository, $this->iconRepository);

        $this->assertSame($this->dataFilter, $this->extractProperty($service, 'dataFilter'));
        $this->assertSame($this->iconFileRepository, $this->extractProperty($service, 'iconFileRepository'));
        $this->assertSame($this->iconRepository, $this->extractProperty($service, 'iconRepository'));
    }

    /**
     * Tests the injectAuthorizationToken method.
     * @throws ReflectionException
     * @covers ::injectAuthorizationToken
     */
    public function testInjectAuthorizationToken(): void
    {
        $enabledModCombinationIds = [42, 1337];

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getEnabledModCombinationIds')
                           ->willReturn($enabledModCombinationIds);

        $service = new IconService($this->dataFilter, $this->iconFileRepository, $this->iconRepository);
        $service->injectAuthorizationToken($authorizationToken);

        $this->assertSame($enabledModCombinationIds, $this->extractProperty($service, 'enabledModCombinationIds'));
    }

    /**
     * Tests the getHashesByTypesAndNames method.
     * @throws ReflectionException
     * @covers ::getHashesByTypesAndNames
     */
    public function testGetHashesByTypesAndNames(): void
    {
        $enabledModCombinationIds = [42, 1337];
        $namesByTypesArray = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];

        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
        $namesByTypes->expects($this->once())
                     ->method('toArray')
                     ->willReturn($namesByTypesArray);

        /* @var IconData&MockObject $iconData1 */
        $iconData1 = $this->createMock(IconData::class);
        $iconData1->expects($this->once())
                  ->method('getHash')
                  ->willReturn('abc');

        /* @var IconData&MockObject $iconData2 */
        $iconData2 = $this->createMock(IconData::class);
        $iconData2->expects($this->once())
                  ->method('getHash')
                  ->willReturn('def');

        /* @var IconData&MockObject $iconData3 */
        $iconData3 = $this->createMock(IconData::class);
        $iconData3->expects($this->once())
                  ->method('getHash')
                  ->willReturn('abc');


        $iconData = [$iconData1, $iconData2, $iconData3];
        $expectedResult = ['abc', 'def'];

        $this->iconRepository->expects($this->once())
                             ->method('findDataByTypesAndNames')
                             ->with(
                                 $this->identicalTo($namesByTypesArray),
                                 $this->identicalTo($enabledModCombinationIds)
                             )
                             ->willReturn($iconData);

        $service = new IconService($this->dataFilter, $this->iconFileRepository, $this->iconRepository);
        $this->injectProperty($service, 'enabledModCombinationIds', $enabledModCombinationIds);

        $result = $service->getHashesByTypesAndNames($namesByTypes);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getTypesAndNamesByHashes method.
     * @throws ReflectionException
     * @covers ::getTypesAndNamesByHashes
     */
    public function testGetTypesAndNamesByHashes(): void
    {
        /* @var IconData&MockObject $iconData1 */
        $iconData1 = $this->createMock(IconData::class);
        $iconData1->expects($this->once())
                  ->method('getType')
                  ->willReturn('abc');
        $iconData1->expects($this->once())
                  ->method('getName')
                  ->willReturn('def');
        
        /* @var IconData&MockObject $iconData2 */
        $iconData2 = $this->createMock(IconData::class);
        $iconData2->expects($this->once())
                  ->method('getType')
                  ->willReturn('ghi');
        $iconData2->expects($this->once())
                  ->method('getName')
                  ->willReturn('jkl');

        $hashes = ['mno', 'pqr'];
        $iconData = [$iconData1, $iconData2];
        
        $expectedResult = new NamesByTypes();
        $expectedResult->addName('abc', 'def')
                       ->addName('ghi', 'jkl');
        
        /* @var IconService&MockObject $iconService */
        $iconService = $this->getMockBuilder(IconService::class)
                            ->setMethods(['getIconDataByHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $iconService->expects($this->once())
                    ->method('getIconDataByHashes')
                    ->with($this->identicalTo($hashes))
                    ->willReturn($iconData);

        $result = $iconService->getTypesAndNamesByHashes($hashes);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getIconDataByHashes method.
     * @throws ReflectionException
     * @covers ::getIconDataByHashes
     */
    public function testGetIconDataByHashes(): void
    {
        $hashes = ['abc', 'def'];
        $enabledModCombinationIds = [42, 1337];

        $iconData = [
            $this->createMock(IconData::class),
            $this->createMock(IconData::class),
        ];
        $filteredIconData = [
            $this->createMock(IconData::class),
            $this->createMock(IconData::class),
        ];
        
        $this->iconRepository->expects($this->once())
                             ->method('findDataByHashes')
                             ->with($this->identicalTo($hashes), $this->identicalTo($enabledModCombinationIds))
                             ->willReturn($iconData);

        $this->dataFilter->expects($this->once())
                         ->method('filter')
                         ->with($this->identicalTo($iconData))
                         ->willReturn($filteredIconData);

        $service = new IconService($this->dataFilter, $this->iconFileRepository, $this->iconRepository);
        $this->injectProperty($service, 'enabledModCombinationIds', $enabledModCombinationIds);

        $result = $service->getIconDataByHashes($hashes);

        $this->assertEquals($filteredIconData, $result);
    }

    /**
     * Tests the getIconFilesByHashes method.
     * @throws ReflectionException
     * @covers ::getIconFilesByHashes
     */
    public function testGetIconFilesByHashes(): void
    {
        $hashes = ['abc', 'def'];
        $iconFiles = [
            $this->createMock(IconFile::class),
            $this->createMock(IconFile::class),
        ];
        
        $this->iconFileRepository->expects($this->once())
                                 ->method('findByHashes')
                                 ->with($this->identicalTo($hashes))
                                 ->willReturn($iconFiles);

        $service = new IconService($this->dataFilter, $this->iconFileRepository, $this->iconRepository);
        $result = $service->getIconFilesByHashes($hashes);

        $this->assertSame($iconFiles, $result);
    }
}
