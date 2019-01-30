<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\InvalidAuthorizationTokenException;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

/**
 * The PHPUnit test of the AuthorizationService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\AuthorizationService
 */
class AuthorizationServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $authorizationKey = 'abc';

        $service = new AuthorizationService($authorizationKey);

        $this->assertSame($authorizationKey, $this->extractProperty($service, 'authorizationKey'));
    }

    /**
     * Tests the createToken method.
     * @covers ::createToken
     */
    public function testCreateToken(): void
    {
        $agent = new Agent();
        $agent->setName('abc');
        $enabledModCombinationIds = [42, 1337];

        $expectedResult = new AuthorizationToken();
        $expectedResult->setAgentName('abc')
                       ->setEnabledModCombinationIds([42, 1337]);

        $service = new AuthorizationService('foo');
        $result = $service->createToken($agent, $enabledModCombinationIds);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the serializeToken method.
     * @covers ::serializeToken
     */
    public function testSerializeToken(): void
    {
        $authorizationKey = 'abc';
        $token = (new AuthorizationToken())->setAgentName('def');
        $tokenData = [
            'exp' => 2147483647,
            'agt' => 'def',
            'mds' => [42, 1337]
        ];
        $expectedResult = trim((string) file_get_contents(__DIR__ . '/../../asset/token/valid.txt'));

        /* @var AuthorizationService&MockObject $service */
        $service = $this->getMockBuilder(AuthorizationService::class)
                        ->setMethods(['getTokenData'])
                        ->setConstructorArgs([$authorizationKey])
                        ->getMock();
        $service->expects($this->once())
                ->method('getTokenData')
                ->with($this->identicalTo($token))
                ->willReturn($tokenData);

        $result = $service->serializeToken($token);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getTokenData method.
     * @throws ReflectionException
     * @covers ::getTokenData
     */
    public function testGetTokenData(): void
    {
        $agent = 'abc';
        $enabledModCombinationIds = [42, 1337];

        $token = new AuthorizationToken();
        $token->setAgentName($agent)
              ->setEnabledModCombinationIds($enabledModCombinationIds);

        $service = new AuthorizationService('foo');
        $result = $this->invokeMethod($service, 'getTokenData', $token);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('exp', $result);
        $this->assertArrayHasKey('agt', $result);
        $this->assertArrayHasKey('mds', $result);
        $this->assertIsInt($result['exp']);
        $this->assertSame($agent, $result['agt']);
        $this->assertSame($enabledModCombinationIds, $result['mds']);
    }

    /**
     * Provides the data for the deserializeToken test.
     * @return array
     */
    public function provideDeserializeToken(): array
    {
        $token1 = (object) ['exp' => 2147483647, 'agt' => 'def', 'mds' => [42, 1337]];
        $token2 = (object) ['exp' => 2147483647, 'agt' => 'def', 'mds' => ['42', '1337']];
        $expectedToken = new AuthorizationToken();
        $expectedToken->setAgentName('def')
                      ->setEnabledModCombinationIds([42, 1337]);

        return [
            [$token1, $expectedToken],
            [$token2, $expectedToken],
        ];
    }

    /**
     * Tests the deserializeToken method.
     * @covers ::deserializeToken
     * @dataProvider provideDeserializeToken
     * @param stdClass $token
     * @param AuthorizationToken $expectedResult
     * @throws InvalidAuthorizationTokenException
     */
    public function testDeserializeToken(stdClass $token, AuthorizationToken $expectedResult): void
    {
        $serializedToken = 'abc';

        /* @var AuthorizationService&MockObject $service */
        $service = $this->getMockBuilder(AuthorizationService::class)
                        ->setMethods(['decodeSerializedToken'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $service->expects($this->once())
                ->method('decodeSerializedToken')
                ->with($this->identicalTo($serializedToken))
                ->willReturn($token);

        $result = $service->deserializeToken($serializedToken);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the decodeSerializedToken test.
     * @return array
     */
    public function provideDecodeSerializedToken(): array
    {
        return [
            [ // Valid token
                file_get_contents(__DIR__ . '/../../asset/token/valid.txt'),
                false,
                (object) ['exp' => 2147483647, 'agt' => 'def', 'mds' => [42, 1337]],
            ],
            [ // Expired token
                file_get_contents(__DIR__ . '/../../asset/token/expired.txt'),
                true,
                null,
            ],
            [ // Invalid signature
                file_get_contents(__DIR__ . '/../../asset/token/invalidSignature.txt'),
                true,
                null,
            ]
        ];
    }

    /**
     * Tests the decodeSerializedToken method.
     * @param string $serializedToken
     * @param bool $expectException
     * @param stdClass|null $expectedResult
     * @throws ReflectionException
     * @covers ::decodeSerializedToken
     * @dataProvider provideDecodeSerializedToken
     */
    public function testDecodeSerializedToken(
        string $serializedToken,
        bool $expectException,
        ?stdClass $expectedResult
    ): void {
        $authorizationKey = 'abc';

        if ($expectException) {
            $this->expectException(InvalidAuthorizationTokenException::class);
        }

        $service = new AuthorizationService($authorizationKey);
        $result = $this->invokeMethod($service, 'decodeSerializedToken', $serializedToken);

        $this->assertEquals($expectedResult, $result);
    }
}
