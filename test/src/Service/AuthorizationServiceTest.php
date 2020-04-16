<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\InvalidAuthorizationTokenException;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
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
        $authorizationTokenLifetime = 42;

        $service = new AuthorizationService($authorizationKey, $authorizationTokenLifetime);

        $this->assertSame($authorizationKey, $this->extractProperty($service, 'authorizationKey'));
        $this->assertSame($authorizationTokenLifetime, $this->extractProperty($service, 'authorizationTokenLifetime'));
    }

    /**
     * Tests the serializeToken method.
     * @covers ::serializeToken
     */
    public function testSerializeToken(): void
    {
        $authorizationKey = 'foo';
        $combinationId = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');

        $token = new AuthorizationToken();
        $token->setAgentName('abc')
              ->setCombinationId($combinationId)
              ->setModNames(['def', 'ghi']);
        $tokenData = [
            'exp' => 2147483647,
            'agt' => 'abc',
            'cmb' => '999a23e4-addb-4821-91b5-1adf0971f6f4',
            'mds' => ['def', 'ghi'],
        ];
        $expectedResult = trim((string) file_get_contents(__DIR__ . '/../../asset/token/valid.txt'));

        /* @var AuthorizationService&MockObject $service */
        $service = $this->getMockBuilder(AuthorizationService::class)
                        ->onlyMethods(['getTokenData'])
                        ->setConstructorArgs([$authorizationKey, 42])
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
        $combinationId = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');
        $modNames = ['def', 'ghi'];

        $token = new AuthorizationToken();
        $token->setAgentName($agent)
              ->setCombinationId($combinationId)
              ->setModNames($modNames);

        $service = new AuthorizationService('foo', 42);
        $result = $this->invokeMethod($service, 'getTokenData', $token);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('exp', $result);
        $this->assertArrayHasKey('agt', $result);
        $this->assertArrayHasKey('cmb', $result);
        $this->assertArrayHasKey('mds', $result);
        $this->assertIsInt($result['exp']);
        $this->assertSame($agent, $result['agt']);
        $this->assertSame($combinationId->toString(), $result['cmb']);
        $this->assertSame($modNames, $result['mds']);
    }

    /**
     * Tests the deserializeToken method.
     * @covers ::deserializeToken
     * @throws InvalidAuthorizationTokenException
     */
    public function testDeserializeToken(): void
    {
        $token = (object) [
            'exp' => 2147483647,
            'agt' => 'abc',
            'cmb' => '999a23e4-addb-4821-91b5-1adf0971f6f4',
            'mds' => ['def', 'ghi']
        ];

        $serializedToken = 'abc';
        $expectedResult = new AuthorizationToken();
        $expectedResult->setAgentName('abc')
                      ->setCombinationId(Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4'))
                      ->setModNames(['def', 'ghi']);

        /* @var AuthorizationService&MockObject $service */
        $service = $this->getMockBuilder(AuthorizationService::class)
                        ->onlyMethods(['decodeSerializedToken'])
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
     * @return array<mixed>
     */
    public function provideDecodeSerializedToken(): array
    {
        return [
            [ // Valid token
                file_get_contents(__DIR__ . '/../../asset/token/valid.txt'),
                false,
                (object) [
                    'exp' => 2147483647,
                    'agt' => 'abc',
                    'cmb' => '999a23e4-addb-4821-91b5-1adf0971f6f4',
                    'mds' => ['def', 'ghi']
                ],
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
        $authorizationKey = 'foo';

        if ($expectException) {
            $this->expectException(InvalidAuthorizationTokenException::class);
        }

        $service = new AuthorizationService($authorizationKey, 42);
        $result = $this->invokeMethod($service, 'decodeSerializedToken', $serializedToken);

        $this->assertEquals($expectedResult, $result);
    }
}
