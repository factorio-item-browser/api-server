<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Entity;

use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use PHPUnit\Framework\TestCase;

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
     * Tests setting and getting the enabled mod combination ids.
     * @covers ::getModNames
     * @covers ::setModNames
     */
    public function testSetAndGetEnabledModCombinationIds(): void
    {
        $enabledModCombinationIds = [42, 1337];
        $authorizationToken = new AuthorizationToken();

        $this->assertSame(
            $authorizationToken,
            $authorizationToken->setModNames($enabledModCombinationIds)
        );
        $this->assertSame($enabledModCombinationIds, $authorizationToken->getModNames());
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
