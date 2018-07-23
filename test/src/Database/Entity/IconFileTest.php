<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Entity;

use BluePsyduck\Common\Test\ReflectionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\Api\Server\Database\Entity\IconFile;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the IconFile class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Entity\IconFile
 */
class IconFileTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getIcons
     */
    public function testConstruct()
    {
        $iconFile = new IconFile('12ab34cd');

        $this->assertSame('12ab34cd', $iconFile->getHash());
        $this->assertSame('', $iconFile->getImage());
        $this->assertInstanceOf(ArrayCollection::class, $iconFile->getIcons());
    }

    /**
     * Tests setting and getting the hash.
     * @covers ::getHash
     * @covers ::setHash
     */
    public function testSetAndGetHash()
    {
        $iconFile = new IconFile('ab12cd34');

        $hash = '12ab34cd';
        $this->assertSame($iconFile, $iconFile->setHash($hash));
        $this->assertSame($hash, $iconFile->getHash());
    }

    /**
     * Provides the data for testSetAndGetImage.
     * @return array
     */
    public function provideSetAndGetImage(): array
    {
        $stream = fopen('php://memory', 'r+');
        $this->assertNotFalse($stream);
        fwrite($stream, 'abc');
        fseek($stream, 0);

        return [
            ['abc', 'abc', 'abc', 'abc'],
            ['abc', 'abc', $stream, 'abc']
        ];
    }

    /**
     * Tests setting and getting the image.
     * @param string $imageToSet
     * @param string $expectedProperty
     * @param mixed $propertyToSet
     * @param string $expectedImage
     * @covers ::getImage
     * @covers ::setImage
     * @dataProvider provideSetAndGetImage
     */
    public function testSetAndGetImage(
        string $imageToSet,
        string $expectedProperty,
        $propertyToSet,
        string $expectedImage
    ) {
        $iconFile = new IconFile('ab12cd34');

        $this->assertSame($iconFile, $iconFile->setImage($imageToSet));
        $this->assertSame($expectedProperty, $this->extractProperty($iconFile, 'image'));

        $this->injectProperty($iconFile, 'image', $propertyToSet);
        $this->assertSame($expectedImage, $iconFile->getImage());
    }
}
