<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Exception\RejectedCombinationUpdateException;
use FactorioItemBrowser\Api\Server\Service\CombinationUpdateService;
use FactorioItemBrowser\CombinationApi\Client\ClientInterface;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobPriority;
use FactorioItemBrowser\CombinationApi\Client\Exception\ClientException;
use FactorioItemBrowser\CombinationApi\Client\Request\Combination\ValidateRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\CreateRequest;
use FactorioItemBrowser\CombinationApi\Client\Response\Combination\ValidateResponse;
use FactorioItemBrowser\CombinationApi\Client\Transfer\ValidatedMod;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The PHPUnit test of the CombinationUpdateService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Service\CombinationUpdateService
 */
class CombinationUpdateServiceTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $combinationApiClient;

    protected function setUp(): void
    {
        $this->combinationApiClient = $this->createMock(ClientInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return CombinationUpdateService&MockObject
     */
    private function createInstance(array $mockedMethods = []): CombinationUpdateService
    {
        return $this->getMockBuilder(CombinationUpdateService::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->combinationApiClient,
                    ])
                    ->getMock();
    }

    public function testCheckCombination(): void
    {
        $combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $factorioVersion = '1.2.3';
        $expectedUpdateHash = '7190ca13-cac2-eda3-af7a-007873a64c5b';

        $mod1 = new Mod();
        $mod1->setName('abc')
             ->setVersion('2.3.4');
        $mod2 = new Mod();
        $mod2->setName('def')
             ->setVersion('3.4.5');
        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId));
        $combination->getMods()->add($mod1);
        $combination->getMods()->add($mod2);

        $expectedRequest = new ValidateRequest();
        $expectedRequest->combinationId = $combinationId;
        $expectedRequest->factorioVersion = $factorioVersion;

        $validatedMod1 = new ValidatedMod();
        $validatedMod1->name = 'abc';
        $validatedMod1->version = '2.3.4';
        $validatedMod2 = new ValidatedMod();
        $validatedMod2->name = 'def';
        $validatedMod2->version = '3.5.7';
        $response = new ValidateResponse();
        $response->isValid = true;
        $response->mods = [$validatedMod1, $validatedMod2];

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willReturn(new FulfilledPromise($response));

        $instance = $this->createInstance();
        $result = $instance->checkCombination($combination, $factorioVersion)->wait();

        $this->assertInstanceOf(UuidInterface::class, $result);
        /** @var UuidInterface $result */
        $this->assertSame($expectedUpdateHash, $result->toString());
    }

    public function testCheckCombinationWithClientException(): void
    {
        $combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $factorioVersion = '1.2.3';

        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId));

        $expectedRequest = new ValidateRequest();
        $expectedRequest->combinationId = $combinationId;
        $expectedRequest->factorioVersion = $factorioVersion;


        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willThrowException($this->createMock(ClientException::class));

        $this->expectException(ClientException::class);

        $instance = $this->createInstance();
        $instance->checkCombination($combination, $factorioVersion)->wait();
    }

    public function testCheckCombinationWithInvalidCombination(): void
    {
        $combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $factorioVersion = '1.2.3';

        $mod = new Mod();
        $mod->setName('abc')
             ->setVersion('2.3.4');
        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId));
        $combination->getMods()->add($mod);

        $expectedRequest = new ValidateRequest();
        $expectedRequest->combinationId = $combinationId;
        $expectedRequest->factorioVersion = $factorioVersion;

        $response = new ValidateResponse();
        $response->isValid = false;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willReturn(new FulfilledPromise($response));

        $this->expectException(RejectedCombinationUpdateException::class);

        $instance = $this->createInstance();
        $instance->checkCombination($combination, $factorioVersion)->wait();
    }

    public function testCheckCombinationWithNoModUpdates(): void
    {
        $combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $factorioVersion = '1.2.3';

        $mod1 = new Mod();
        $mod1->setName('abc')
             ->setVersion('2.3.4');
        $mod2 = new Mod();
        $mod2->setName('def')
             ->setVersion('3.4.5');
        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId));
        $combination->getMods()->add($mod1);
        $combination->getMods()->add($mod2);

        $expectedRequest = new ValidateRequest();
        $expectedRequest->combinationId = $combinationId;
        $expectedRequest->factorioVersion = $factorioVersion;

        $validatedMod1 = new ValidatedMod();
        $validatedMod1->name = 'abc';
        $validatedMod1->version = '2.3.4';
        $response = new ValidateResponse();
        $response->isValid = true;
        $response->mods = [$validatedMod1];

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willReturn(new FulfilledPromise($response));

        $this->expectException(RejectedCombinationUpdateException::class);

        $instance = $this->createInstance();
        $instance->checkCombination($combination, $factorioVersion)->wait();
    }

    public function testCheckCombinationWithIdenticalUpdateHash(): void
    {
        $combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $factorioVersion = '1.2.3';
        $updateHash = '7190ca13-cac2-eda3-af7a-007873a64c5b';

        $mod1 = new Mod();
        $mod1->setName('abc')
             ->setVersion('2.3.4');
        $mod2 = new Mod();
        $mod2->setName('def')
             ->setVersion('3.4.5');
        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId))
                    ->setLastUpdateHash(Uuid::fromString($updateHash));
        $combination->getMods()->add($mod1);
        $combination->getMods()->add($mod2);

        $expectedRequest = new ValidateRequest();
        $expectedRequest->combinationId = $combinationId;
        $expectedRequest->factorioVersion = $factorioVersion;

        $validatedMod1 = new ValidatedMod();
        $validatedMod1->name = 'abc';
        $validatedMod1->version = '2.3.4';
        $validatedMod2 = new ValidatedMod();
        $validatedMod2->name = 'def';
        $validatedMod2->version = '3.5.7';
        $response = new ValidateResponse();
        $response->isValid = true;
        $response->mods = [$validatedMod1, $validatedMod2];

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willReturn(new FulfilledPromise($response));

        $this->expectException(RejectedCombinationUpdateException::class);

        $instance = $this->createInstance();
        $instance->checkCombination($combination, $factorioVersion)->wait();
    }

    public function testTriggerUpdate(): void
    {
        $combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';

        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId));

        $expectedRequest = new CreateRequest();
        $expectedRequest->combinationId = $combinationId;
        $expectedRequest->priority = JobPriority::AUTO_UPDATE;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest));

        $instance = $this->createInstance();
        $instance->triggerUpdate($combination);
    }

    public function testTriggerUpdateWithException(): void
    {
        $combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';

        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId));

        $expectedRequest = new CreateRequest();
        $expectedRequest->combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $expectedRequest->priority = JobPriority::AUTO_UPDATE;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willThrowException($this->createMock(ClientException::class));

        $instance = $this->createInstance();
        $instance->triggerUpdate($combination);
    }
}
