<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Entity;

use FactorioItemBrowser\Api\Server\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination;
use FactorioItemBrowser\Api\Server\Database\Entity\Translation;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Translation class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Entity\Translation
 */
class TranslationTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct()
    {
        $translation = new Translation();

        $this->assertNull($translation->getId());
//        $this->assertNull($translation->getModCombination()); @todo Fix missing relation
        $this->assertSame('', $translation->getLocale());
        $this->assertSame('', $translation->getType());
        $this->assertSame('', $translation->getName());
        $this->assertSame('', $translation->getValue());
        $this->assertSame('', $translation->getDescription());
        $this->assertFalse($translation->getIsDuplicatedByRecipe());
        $this->assertFalse($translation->getIsDuplicatedByMachine());
    }

    /**
     * Tests setting and getting the id.
     * @covers ::getId
     * @covers ::setId
     */
    public function testSetAndGetId()
    {
        $translation = new Translation();

        $id = 42;
        $this->assertSame($translation, $translation->setId($id));
        $this->assertSame($id, $translation->getId());
    }

    /**
     * Tests setting and getting the modCombination.
     * @covers ::getModCombination
     * @covers ::setModCombination
     */
    public function testSetAndGetModCombination()
    {
        $translation = new Translation();

        $modCombination = new ModCombination(new Mod('foo'));
        $this->assertSame($translation, $translation->setModCombination($modCombination));
        $this->assertSame($modCombination, $translation->getModCombination());
    }

    /**
     * Tests setting and getting the locale.
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testSetAndGetLocale()
    {
        $translation = new Translation();

        $locale = 'abc';
        $this->assertSame($translation, $translation->setLocale($locale));
        $this->assertSame($locale, $translation->getLocale());
    }

    /**
     * Tests setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType()
    {
        $translation = new Translation();

        $type = 'abc';
        $this->assertSame($translation, $translation->setType($type));
        $this->assertSame($type, $translation->getType());
    }

    /**
     * Tests setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName()
    {
        $translation = new Translation();

        $name = 'abc';
        $this->assertSame($translation, $translation->setName($name));
        $this->assertSame($name, $translation->getName());
    }

    /**
     * Tests setting and getting the value.
     * @covers ::getValue
     * @covers ::setValue
     */
    public function testSetAndGetValue()
    {
        $translation = new Translation();

        $value = 'abc';
        $this->assertSame($translation, $translation->setValue($value));
        $this->assertSame($value, $translation->getValue());
    }

    /**
     * Tests setting and getting the description.
     * @covers ::getDescription
     * @covers ::setDescription
     */
    public function testSetAndGetDescription()
    {
        $translation = new Translation();

        $description = 'abc';
        $this->assertSame($translation, $translation->setDescription($description));
        $this->assertSame($description, $translation->getDescription());
    }

    /**
     * Tests setting and getting the isDuplicatedByRecipe.
     * @covers ::getIsDuplicatedByRecipe
     * @covers ::setIsDuplicatedByRecipe
     */
    public function testSetAndGetIsDuplicatedByRecipe()
    {
        $translation = new Translation();

        $isDuplicatedByRecipe = true;
        $this->assertSame($translation, $translation->setIsDuplicatedByRecipe($isDuplicatedByRecipe));
        $this->assertTrue($translation->getIsDuplicatedByRecipe());
    }

    /**
     * Tests setting and getting the isDuplicatedByMachine.
     * @covers ::getIsDuplicatedByMachine
     * @covers ::setIsDuplicatedByMachine
     */
    public function testSetAndGetIsDuplicatedByMachine()
    {
        $translation = new Translation();

        $isDuplicatedByMachine = true;
        $this->assertSame($translation, $translation->setIsDuplicatedByMachine($isDuplicatedByMachine));
        $this->assertTrue($translation->getIsDuplicatedByMachine());
    }
}
