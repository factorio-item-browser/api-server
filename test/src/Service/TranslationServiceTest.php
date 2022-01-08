<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Translation;
use FactorioItemBrowser\Api\Database\Repository\TranslationRepository;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use FactorioItemBrowser\Common\Constant\EntityType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the TranslationService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Service\TranslationService
 */
class TranslationServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var TranslationRepository&MockObject */
    private TranslationRepository $translationRepository;

    protected function setUp(): void
    {
        $this->translationRepository = $this->createMock(TranslationRepository::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return TranslationService&MockObject
     */
    private function createInstance(array $mockedMethods = []): TranslationService
    {
        return $this->getMockBuilder(TranslationService::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->translationRepository,
                    ])
                    ->getMock();
    }

    /**
     * @throws ReflectionException
     */
    public function testAddEntity(): void
    {
        $entity1 = $this->createMock(GenericEntity::class);
        $entity2 = $this->createMock(GenericEntity::class);
        $entity3 = $this->createMock(GenericEntity::class);

        $entities = [$entity1, $entity2];
        $expectedEntities = [$entity1, $entity2, $entity3];

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'entities', $entities);

        $instance->addEntity($entity3);

        $this->assertSame($expectedEntities, $this->extractProperty($instance, 'entities'));
    }

    /**
     * @throws ReflectionException
     */
    public function testTranslate(): void
    {
        $locale = 'abc';
        $entities = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];
        $translations =  [
            $this->createMock(Translation::class),
            $this->createMock(Translation::class),
        ];
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');

        $instance = $this->createInstance(['fetchTranslations', 'matchTranslationsToEntities']);
        $instance->expects($this->once())
                 ->method('fetchTranslations')
                 ->with($this->identicalTo($entities), $this->identicalTo($combinationId), $this->identicalTo($locale))
                 ->willReturn($translations);
        $instance->expects($this->once())
                 ->method('matchTranslationsToEntities')
                 ->with($this->identicalTo($translations), $this->identicalTo($entities));
        $this->injectProperty($instance, 'entities', $entities);

        $instance->translate($combinationId, $locale);
    }

    /**
     * @throws ReflectionException
     */
    public function testTranslateWithoutEntities(): void
    {
        $locale = 'abc';
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');

        $instance = $this->createInstance(['fetchTranslations', 'matchTranslationsToEntities']);
        $instance->expects($this->never())
                 ->method('fetchTranslations');
        $instance->expects($this->never())
                 ->method('matchTranslationsToEntities');
        $this->injectProperty($instance, 'entities', []);

        $instance->translate($combinationId, $locale);
    }

    /**
     * @throws ReflectionException
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

        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');
        $namesByTypes = $this->createMock(NamesByTypes::class);

        $this->translationRepository->expects($this->once())
                                    ->method('findByTypesAndNames')
                                    ->with(
                                        $this->identicalTo($combinationId),
                                        $this->identicalTo($locale),
                                        $this->identicalTo($namesByTypes)
                                    )
                                    ->willReturn($translations);

        $instance = $this->createInstance(['extractTypesAndNames', 'compareTranslations', 'prepareTranslations']);
        $instance->expects($this->once())
                 ->method('extractTypesAndNames')
                 ->with($this->identicalTo($entities))
                 ->willReturn($namesByTypes);
        $instance->expects($this->any())
                 ->method('compareTranslations')
                 ->with($this->isInstanceOf(Translation::class), $this->isInstanceOf(Translation::class))
                 ->willReturn(-1);
        $instance->expects($this->once())
                 ->method('prepareTranslations')
                 ->with($this->equalTo($translations))
                 ->willReturn($preparedTranslations);

        $result = $this->invokeMethod($instance, 'fetchTranslations', $entities, $combinationId, $locale);

        $this->assertSame($preparedTranslations, $result);
    }

    /**
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
     * @param array<mixed> $leftCriteria
     * @param array<mixed> $rightCriteria
     * @param int $expectedResult
     * @throws ReflectionException
     * @dataProvider provideCompareTranslations
     */
    public function testCompareTranslations(array $leftCriteria, array $rightCriteria, int $expectedResult): void
    {
        $left = $this->createMock(Translation::class);
        $right = $this->createMock(Translation::class);

        $instance = $this->createInstance(['getSortCriteria']);
        $instance->expects($this->exactly(2))
                 ->method('getSortCriteria')
                 ->withConsecutive(
                     [$this->identicalTo($left)],
                     [$this->identicalTo($right)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $leftCriteria,
                     $rightCriteria
                 );

        $result = $this->invokeMethod($instance, 'compareTranslations', $left, $right);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetSortCriteria(): void
    {
        $locale = 'abc';
        $type = 'def';
        $name = 'ghi';
        $expectedResult = [true, 'def', 'ghi'];

        $translation = new Translation();
        $translation->setLocale($locale)
                    ->setType($type)
                    ->setName($name);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getSortCriteria', $translation);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testPrepareTranslations(): void
    {
        $translation1 = new Translation();
        $translation1->setName('abc');
        $translation2 = new Translation();
        $translation2->setName('def');
        $translation3 = new Translation();
        $translation3->setName('abc');

        $translations = [$translation1, $translation2, $translation3];
        $expectedResult = [
            'pqr' => [$translation1, $translation3],
            'stu' => [$translation1],
            'vwx' => [$translation2],
        ];

        $instance = $this->createInstance(['getTypesForTranslation', 'getTranslationKey']);
        $instance->expects($this->exactly(3))
                 ->method('getTypesForTranslation')
                 ->withConsecutive(
                     [$this->identicalTo($translation1)],
                     [$this->identicalTo($translation2)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     ['ghi', 'jkl'],
                     ['mno'],
                     ['ghi'],
                 );
        $instance->expects($this->exactly(4))
                 ->method('getTranslationKey')
                 ->withConsecutive(
                     [$this->identicalTo('ghi'), $this->identicalTo('abc')],
                     [$this->identicalTo('jkl'), $this->identicalTo('abc')],
                     [$this->identicalTo('mno'), $this->identicalTo('def')],
                     [$this->identicalTo('ghi'), $this->identicalTo('abc')],
                 )
                 ->willReturnOnConsecutiveCalls(
                     'pqr',
                     'stu',
                     'vwx',
                     'pqr',
                 );

        $result = $this->invokeMethod($instance, 'prepareTranslations', $translations);

        $this->assertEquals($expectedResult, $result);
    }

    /**
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
     * @param Translation $translation
     * @param array<string> $expectedResult
     * @throws ReflectionException
     * @dataProvider provideGetTypesForTranslation
     */
    public function testGetTypesForTranslation(Translation $translation, array $expectedResult): void
    {
        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getTypesForTranslation', $translation);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testMatchTranslationsToEntities(): void
    {
        $translation1a = new Translation();
        $translation1a->setValue('ghi')
                      ->setDescription('foo');
        $translation1b = new Translation();
        $translation1b->setDescription('jkl');

        $translation2 = new Translation();

        $translations = [
            'stu' => [$translation1a, $translation1b],
            'foo' => [$translation2],
        ];

        $entity1 = new GenericEntity();
        $entity1->type = 'abc';
        $entity1->name = 'def';

        $expectedEntity1 = new GenericEntity();
        $expectedEntity1->type = 'abc';
        $expectedEntity1->name = 'def';
        $expectedEntity1->label = 'ghi';
        $expectedEntity1->description = 'jkl';

        $entity2 = new GenericEntity();
        $entity2->type = 'mno';
        $entity2->name = 'pqr';

        $expectedEntity2 = new GenericEntity();
        $expectedEntity2->type = 'mno';
        $expectedEntity2->name = 'pqr';

        $entities = [$entity1, $entity2];

        $instance = $this->createInstance(['getTranslationKey']);
        $instance->expects($this->exactly(2))
                 ->method('getTranslationKey')
                 ->withConsecutive(
                     [$this->identicalTo('abc'), $this->identicalTo('def')],
                     [$this->identicalTo('mno'), $this->identicalTo('pqr')]
                 )
                 ->willReturnOnConsecutiveCalls(
                     'stu',
                     'vwx'
                 );

        $this->invokeMethod($instance, 'matchTranslationsToEntities', $translations, $entities);

        $this->assertEquals($expectedEntity1, $entity1);
        $this->assertEquals($expectedEntity2, $entity2);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetTranslationKey(): void
    {
        $type = 'abc';
        $name = 'def';
        $expectedResult = 'abc|def';

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getTranslationKey', $type, $name);

        $this->assertSame($expectedResult, $result);
    }
}
