<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Entity;

use DateTime;
use FactorioItemBrowser\Api\Server\Database\Entity\CachedSearchResult;
use PHPUnit\Framework\TestCase;

/**
 * The PHUnit test of the CachedSearchResult class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Entity\CachedSearchResult
 */
class CachedSearchResultTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $cachedSearchResult = new CachedSearchResult('12ab34cd');

        $this->assertSame('12ab34cd', $cachedSearchResult->getHash());
        $this->assertSame('', $cachedSearchResult->getResultData());
        $this->assertInstanceOf(DateTime::class, $cachedSearchResult->getLastSearchTime());
    }

    /**
     * Tests setting and getting the hash.
     * @covers ::getHash
     * @covers ::setHash
     */
    public function testSetAndGetHash()
    {
        $cachedSearchResult = new CachedSearchResult('ab12cd34');

        $hash = '12ab34cd';
        $this->assertSame($cachedSearchResult, $cachedSearchResult->setHash($hash));
        $this->assertSame($hash, $cachedSearchResult->getHash());
    }

    /**
     * Tests setting and getting the resultData.
     * @covers ::getResultData
     * @covers ::setResultData
     */
    public function testSetAndGetResultData()
    {
        $cachedSearchResult = new CachedSearchResult('ab12cd34');

        $resultData = 'abc';
        $this->assertSame($cachedSearchResult, $cachedSearchResult->setResultData($resultData));
        $this->assertSame($resultData, $cachedSearchResult->getResultData());
    }

    /**
     * Tests setting and getting the lastSearchTime.
     * @covers ::getLastSearchTime
     * @covers ::setLastSearchTime
     */
    public function testSetAndGetLastSearchTime()
    {
        $cachedSearchResult = new CachedSearchResult('ab12cd34');

        $lastSearchTime = new DateTime('2038-01-19 03:14:07');
        $this->assertSame($cachedSearchResult, $cachedSearchResult->setLastSearchTime($lastSearchTime));
        $this->assertSame($lastSearchTime, $cachedSearchResult->getLastSearchTime());
    }
}
