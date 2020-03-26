<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use DateTime;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\Api\Server\Mapper\ExportJobMapper;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\DetailsResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The PHPUnit test of the ExportJobMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Mapper\ExportJobMapper
 */
class ExportJobMapperTest extends TestCase
{
    /**
     * Provides the data for the supports test.
     * @return array<mixed>
     */
    public function provideSupports(): array
    {
        return [
            [new Job(), new ExportJob(), true],
            [new DetailsResponse(), new ExportJob(), true],

            [new stdClass(), new ExportJob(), false],
            [new Job(), new stdClass(), false],
        ];
    }

    /**
     * Tests the supports method.
     * @param object $source
     * @param object $destination
     * @param bool $expectedResult
     * @covers ::supports
     * @dataProvider provideSupports
     */
    public function testSupports(object $source, object $destination, bool $expectedResult): void
    {
        $mapper = new ExportJobMapper();
        $result = $mapper->supports($source, $destination);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        /* @var DateTime&MockObject $creationTime */
        $creationTime = $this->createMock(DateTime::class);
        /* @var DateTime&MockObject $exportTime */
        $exportTime = $this->createMock(DateTime::class);
        /* @var DateTime&MockObject $importTime */
        $importTime = $this->createMock(DateTime::class);

        $source = new Job();
        $source->setStatus('abc')
               ->setCreationTime($creationTime)
               ->setExportTime($exportTime)
               ->setImportTime($importTime)
               ->setErrorMessage('def');

        $expectedDestination = new ExportJob();
        $expectedDestination->setStatus('abc')
                            ->setCreationTime($creationTime)
                            ->setExportTime($exportTime)
                            ->setImportTime($importTime)
                            ->setErrorMessage('def');

        $destination = new ExportJob();

        $mapper = new ExportJobMapper();
        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
