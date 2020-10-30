<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Translation;
use FactorioItemBrowser\Api\Database\Repository\TranslationRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use FactorioItemBrowser\Common\Constant\EntityType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the TranslationService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\TranslationService
 */
class TranslationServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked translation repository.
     * @var TranslationRepository&MockObject
     */
    protected $translationRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translationRepository = $this->createMock(TranslationRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new TranslationService($this->translationRepository);

        $this->assertSame($this->translationRepository, $this->extractProperty($service, 'translationRepository'));
        $this->assertSame([], $this->extractProperty($service, 'entities'));
    }

    /**
     * Tests the addEntity method.
     * @throws ReflectionException
     * @covers ::addEntity
     */
    public function testAddEntity(): void
    {
        /* @var GenericEntity&MockObject $entity1 */
        $entity1 = $this->createMock(GenericEntity::class);
        /* @var GenericEntity&MockObject $entity2 */
        $entity2 = $this->createMock(GenericEntity::class);
        /* @var GenericEntity&MockObject $entity3 */
        $entity3 = $this->createMock(GenericEntity::class);

        $entities = [$entity1, $entity2];
        $expectedEntities = [$entity1, $entity2, $entity3];

        $service = new TranslationService($this->translationRepository);
        $this->injectProperty($service, 'entities', $entities);

        $service->addEntity($entity3);

        $this->assertSame($expectedEntities, $this->extractProperty($service, 'entities'));
    }

    /**
     * Tests the translate method.
     * @throws ReflectionException
     * @covers ::translate
     */
    public function testTranslate(): void
    {
        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);

        $entities = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];
        $translations =  [
            $this->createMock(Translation::class),
            $this->createMock(Translation::class),
        ];

        /* @var TranslationService&MockObject $service */
        $service = $this->getMockBuilder(TranslationService::class)
                        ->onlyMethods(['fetchTranslations', 'matchTranslationsToEntities'])
                        ->setConstructorArgs([$this->translationRepository])
                        ->getMock();
        $service->expects($this->once())
                ->method('fetchTranslations')
                ->with($this->identicalTo($entities), $this->identicalTo($authorizationToken))
                ->willReturn($translations);
        $service->expects($this->once())
                ->method('matchTranslationsToEntities')
                ->with($this->identicalTo($translations), $this->identicalTo($entities));
        $this->injectProperty($service, 'entities', $entities);

        $service->translate($authorizationToken);
    }

    /**
     * Tests the translate method.
     * @throws ReflectionException
     * @covers ::translate
     */
    public function testTranslateWithoutEntities(): void
    {
        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);

        /* @var TranslationService&MockObject $service */
        $service = $this->getMockBuilder(TranslationService::class)
                        ->onlyMethods(['fetchTranslations', 'matchTranslationsToEntities'])
                        ->setConstructorArgs([$this->translationRepository])
                        ->getMock();
        $service->expects($this->never())
                ->method('fetchTranslations');
        $service->expects($this->never())
                ->method('matchTranslationsToEntities');
        $this->injectProperty($service, 'entities', []);

        $service->translate($authorizationToken);
    }

    /**
     * Tests the fetchTranslations method.
     * @throws ReflectionException
     * @covers ::fetchTranslations
     */
    public function testFetchTranslations(): void
    {
        $locale = 'abc';

        $entities = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];
        $translations = [
            $this->createMock(Translation::class),
            $this->createMock(Translation::class),
        ];
        $preparedTranslations = [
            'def' => $this->createMock(Translation::class),
            'ghi' => $this->createMock(Translation::class),
        ];

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);
        $authorizationToken->expects($this->once())
                           ->method('getLocale')
                           ->willReturn($locale);

        $this->translationRepository->expects($this->once())
                                    ->method('findByTypesAndNames')
                                    ->with(
                                        $this->identicalTo($combinationId),
                                        $this->identicalTo($locale),
                                        $this->identicalTo($namesByTypes)
                                    )
                                    ->willReturn($translations);

        /* @var TranslationService&MockObject $service */
        $service = $this->getMockBuilder(TranslationService::class)
                        ->onlyMethods(['extractTypesAndNames', 'compareTranslations', 'prepareTranslations'])
                        ->setConstructorArgs([$this->translationRepository])
                        ->getMock();
        $service->expects($this->once())
                ->method('extractTypesAndNames')
                ->with($this->identicalTo($entities))
                ->willReturn($namesByTypes);
        $service->expects($this->any())
                ->method('compareTranslations')
                ->with($this->isInstanceOf(Translation::class), $this->isInstanceOf(Translation::class))
                ->willReturn(-1);
        $service->expects($this->once())
                ->method('prepareTranslations')
                ->with($this->equalTo($translations))
                ->willReturn($preparedTranslations);

        $result = $this->invokeMethod($service, 'fetchTranslations', $entities, $authorizationToken);

        $this->assertSame($preparedTranslations, $result);
    }

    /**
     * Provides the data for the compareTranslations test.
     * @return array<mixed>
     */
    public function provideCompareTranslations(): array
    {
        return [
            [
                ['abc', 'def'],
                ['def', 'abc'],
                -1,
            ],
            [
                ['abc', 'def'],
                ['abc', 'ghi'],
                -1,
            ],
            [
                ['abc', 'def'],
                ['abc', 'def'],
                0,
            ],
            [
                ['def', 'ghi'],
                ['abc', 'ghi'],
                1,
            ],
            [
                ['def', 'ghi'],
                ['def', 'abc'],
                1,
            ],
        ];
    }

    /**
     * Tests the compareTranslations method.
     * @param array<mixed> $leftCriteria
     * @param array<mixed> $rightCriteria
     * @param int $expectedResult
     * @throws ReflectionException
     * @covers ::compareTranslations
     * @dataProvider provideCompareTranslations
     */
    public function testCompareTranslations(array $leftCriteria, array $rightCriteria, int $expectedResult): void
    {
        /* @var Translation&MockObject $left */
        $left = $this->createMock(Translation::class);
        /* @var Translation&MockObject $right */
        $right = $this->createMock(Translation::class);

        /* @var TranslationService&MockObject $service */
        $service = $this->getMockBuilder(TranslationService::class)
                        ->onlyMethods(['getSortCriteria'])
                        ->setConstructorArgs([$this->translationRepository])
                        ->getMock();
        $service->expects($this->exactly(2))
                ->method('getSortCriteria')
                ->withConsecutive(
                    [$this->identicalTo($left)],
                    [$this->identicalTo($right)]
                )
                ->willReturnOnConsecutiveCalls(
                    $leftCriteria,
                    $rightCriteria
                );

        $result = $this->invokeMethod($service, 'compareTranslations', $left, $right);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSortCriteria method.
     * @throws ReflectionException
     * @covers ::getSortCriteria
     */
    public function testGetSortCriteria(): void
    {
        $locale = 'abc';
        $type = 'def';
        $name = 'ghi';
        $expectedResult = [true, 'def', 'ghi'];

        /* @var Translation&MockObject $translation */
        $translation = $this->createMock(Translation::class);
        $translation->expects($this->once())
                    ->method('getLocale')
                    ->willReturn($locale);
        $translation->expects($this->once())
                    ->method('getType')
                    ->willReturn($type);
        $translation->expects($this->once())
                    ->method('getName')
                    ->willReturn($name);

        $service = new TranslationService($this->translationRepository);
        $result = $this->invokeMethod($service, 'getSortCriteria', $translation);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the prepareTranslations method.
     * @throws ReflectionException
     * @covers ::prepareTranslations
     */
    public function testPrepareTranslations(): void
    {
        /* @var Translation&MockObject $translation1 */
        $translation1 = $this->createMock(Translation::class);
        $translation1->expects($this->any())
                     ->method('getName')
                     ->willReturn('abc');

        /* @var Translation&MockObject $translation2 */
        $translation2 = $this->createMock(Translation::class);
        $translation2->expects($this->any())
                     ->method('getName')
                     ->willReturn('def');

        $translations = [$translation1, $translation2];
        $expectedResult = [
            'pqr' => $translation1,
            'stu' => $translation1,
            'vwx' => $translation2,
        ];

        /* @var TranslationService&MockObject $service */
        $service = $this->getMockBuilder(TranslationService::class)
                        ->onlyMethods(['getTypesForTranslation', 'getTranslationKey'])
                        ->setConstructorArgs([$this->translationRepository])
                        ->getMock();
        $service->expects($this->exactly(2))
                ->method('getTypesForTranslation')
                ->withConsecutive(
                    [$this->identicalTo($translation1)],
                    [$this->identicalTo($translation2)]
                )
                ->willReturnOnConsecutiveCalls(
                    ['ghi', 'jkl'],
                    ['mno']
                );
        $service->expects($this->exactly(3))
                ->method('getTranslationKey')
                ->withConsecutive(
                    [$this->identicalTo('ghi'), $this->identicalTo('abc')],
                    [$this->identicalTo('jkl'), $this->identicalTo('abc')],
                    [$this->identicalTo('mno'), $this->identicalTo('def')]
                )
                ->willReturnOnConsecutiveCalls(
                    'pqr',
                    'stu',
                    'vwx'
                );

        $result = $this->invokeMethod($service, 'prepareTranslations', $translations);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the getTypesForTranslation test.
     * @return array<mixed>
     */
    public function provideGetTypesForTranslation(): array
    {
        // Generic translations
        $translation1 = new Translation();
        $translation1->setType('abc')
                     ->setIsDuplicatedByMachine(true)
                     ->setIsDuplicatedByRecipe(true);
        $result1 = ['abc', EntityType::MACHINE, EntityType::RECIPE];

        $translation2 = new Translation();
        $translation2->setType('def')
                     ->setIsDuplicatedByMachine(true)
                     ->setIsDuplicatedByRecipe(false);
        $result2 = ['def', EntityType::MACHINE];

        $translation3 = new Translation();
        $translation3->setType('ghi')
                     ->setIsDuplicatedByMachine(false)
                     ->setIsDuplicatedByRecipe(true);
        $result3 = ['ghi', EntityType::RECIPE];

        $translation4 = new Translation();
        $translation4->setType('jkl')
                     ->setIsDuplicatedByMachine(false)
                     ->setIsDuplicatedByRecipe(false);
        $result4 = ['jkl'];

        // Machine-only translation
        $translation5 = new Translation();
        $translation5->setType(EntityType::MACHINE)
                     ->setIsDuplicatedByMachine(false)
                     ->setIsDuplicatedByRecipe(false);
        $result5 = [EntityType::MACHINE];

        // Recipe-only translation
        $translation6 = new Translation();
        $translation6->setType(EntityType::RECIPE)
                     ->setIsDuplicatedByMachine(false)
                     ->setIsDuplicatedByRecipe(false);
        $result6 = [EntityType::RECIPE];

        return [
            [$translation1, $result1],
            [$translation2, $result2],
            [$translation3, $result3],
            [$translation4, $result4],
            [$translation5, $result5],
            [$translation6, $result6],
        ];
    }

    /**
     * Tests the getTypesForTranslation method.
     * @param Translation $translation
     * @param array|string[] $expectedResult
     * @throws ReflectionException
     * @covers ::getTypesForTranslation
     * @dataProvider provideGetTypesForTranslation
     */
    public function testGetTypesForTranslation(Translation $translation, array $expectedResult): void
    {
        $service = new TranslationService($this->translationRepository);
        $result = $this->invokeMethod($service, 'getTypesForTranslation', $translation);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the matchTranslationsToEntities method.
     * @throws ReflectionException
     * @covers ::matchTranslationsToEntities
     */
    public function testMatchTranslationsToEntities(): void
    {
        /* @var Translation&MockObject $translation1 */
        $translation1 = $this->createMock(Translation::class);
        $translation1->expects($this->once())
                     ->method('getValue')
                     ->willReturn('ghi');
        $translation1->expects($this->once())
                     ->method('getDescription')
                     ->willReturn('jkl');

        /* @var Translation&MockObject $translation1 */
        $translation2 = $this->createMock(Translation::class);
        $translation2->expects($this->never())
                     ->method('getValue');
        $translation2->expects($this->never())
                     ->method('getDescription');

        $translations = [
            'stu' => $translation1,
            'foo' => $translation2,
        ];

        /* @var GenericEntity&MockObject $entity1 */
        $entity1 = $this->createMock(GenericEntity::class);
        $entity1->expects($this->once())
                ->method('getType')
                ->willReturn('abc');
        $entity1->expects($this->once())
                ->method('getName')
                ->willReturn('def');
        $entity1->expects($this->once())
                ->method('setLabel')
                ->with($this->identicalTo('ghi'))
                ->willReturnSelf();
        $entity1->expects($this->once())
                ->method('setDescription')
                ->with($this->identicalTo('jkl'));

        /* @var GenericEntity&MockObject $entity2 */
        $entity2 = $this->createMock(GenericEntity::class);
        $entity2->expects($this->once())
                ->method('getType')
                ->willReturn('mno');
        $entity2->expects($this->once())
                ->method('getName')
                ->willReturn('pqr');
        $entity2->expects($this->never())
                ->method('setLabel');
        $entity2->expects($this->never())
                ->method('setDescription');

        $entities = [$entity1, $entity2];

        /* @var TranslationService&MockObject $service */
        $service = $this->getMockBuilder(TranslationService::class)
                        ->onlyMethods(['getTranslationKey'])
                        ->setConstructorArgs([$this->translationRepository])
                        ->getMock();
        $service->expects($this->exactly(2))
                ->method('getTranslationKey')
                ->withConsecutive(
                    [$this->identicalTo('abc'), $this->identicalTo('def')],
                    [$this->identicalTo('mno'), $this->identicalTo('pqr')]
                )
                ->willReturnOnConsecutiveCalls(
                    'stu',
                    'vwx'
                );

        $this->invokeMethod($service, 'matchTranslationsToEntities', $translations, $entities);
    }

    /**
     * Tests the getTranslationKey method.
     * @throws ReflectionException
     * @covers ::getTranslationKey
     */
    public function testGetTranslationKey(): void
    {
        $type = 'abc';
        $name = 'def';
        $expectedResult = 'abc|def';

        $service = new TranslationService($this->translationRepository);
        $result = $this->invokeMethod($service, 'getTranslationKey', $type, $name);

        $this->assertSame($expectedResult, $result);
    }
}
