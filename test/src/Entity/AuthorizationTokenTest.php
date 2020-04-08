<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Entity;

use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * The PHPUnit test of the AuthorizationToken class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Entity\AuthorizationToken
 */
class AuthorizationTokenTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $authorizationToken = new AuthorizationToken();

        $this->assertSame('', $authorizationToken->getAgentName());
        $this->assertSame([], $authorizationToken->getModNames());
        $this->assertFalse($authorizationToken->getIsDataAvailable());
        $this->assertSame('', $authorizationToken->getLocale());
    }

    /**
     * Tests setting and getting the agent name.
     * @covers ::getAgentName
     * @covers ::setAgentName
     */
    public function testSetAndGetAgentName(): void
    {
        $agentName = 'abc';
        $authorizationToken = new AuthorizationToken();

        $this->assertSame($authorizationToken, $authorizationToken->setAgentName($agentName));
        $this->assertSame($agentName, $authorizationToken->getAgentName());
    }

    /**
     * Tests the setting and getting the combination id.
     * @covers ::getCombinationId
     * @covers ::setCombinationId
     */
    public function testSetAndGetCombinationId(): void
    {
        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);
        $authorizationToken = new AuthorizationToken();

        $this->assertSame($authorizationToken, $authorizationToken->setCombinationId($combinationId));
        $this->assertSame($combinationId, $authorizationToken->getCombinationId());
    }

    /**
     * Tests the setting and getting the mod names.
     * @covers ::getModNames
     * @covers ::setModNames
     */
    public function testSetAndGetModNames(): void
    {
        $modNames = ['abc', 'def'];
        $authorizationToken = new AuthorizationToken();

        $this->assertSame($authorizationToken, $authorizationToken->setModNames($modNames));
        $this->assertSame($modNames, $authorizationToken->getModNames());
    }

    /**
     * Tests the setting and getting the is data available.
     * @covers ::getIsDataAvailable
     * @covers ::setIsDataAvailable
     */
    public function testSetAndGetIsDataAvailable(): void
    {
        $authorizationToken = new AuthorizationToken();

        $this->assertSame($authorizationToken, $authorizationToken->setIsDataAvailable(true));
        $this->assertTrue($authorizationToken->getIsDataAvailable());
    }

    /**
     * Tests the setting and getting the locale.
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testSetAndGetLocale(): void
    {
        $locale = 'abc';
        $authorizationToken = new AuthorizationToken();

        $this->assertSame($authorizationToken, $authorizationToken->setLocale($locale));
        $this->assertSame($locale, $authorizationToken->getLocale());
    }
}
