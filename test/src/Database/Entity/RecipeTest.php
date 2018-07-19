<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FactorioItemBrowser\Api\Server\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Server\Database\Entity\Item;
use FactorioItemBrowser\Api\Server\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Database\Entity\RecipeIngredient;
use FactorioItemBrowser\Api\Server\Database\Entity\RecipeProduct;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Recipe class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Entity\Recipe
 */
class RecipeTest extends TestCase
{
    /**
     * Tests the constructing
     * @covers ::__construct
     * @covers ::getIngredients()
     * @covers ::getModCombinations
     * @covers ::getProducts()
     */
    public function testConstruct()
    {
        $name = 'abc';
        $mode = 'def';
        $craftingCategory = new CraftingCategory('ghi');

        $recipe = new Recipe($name, $mode, $craftingCategory);
        $this->assertSame(null, $recipe->getId());
        $this->assertInstanceOf(ArrayCollection::class, $recipe->getModCombinations());
        $this->assertSame($name, $recipe->getName());
        $this->assertSame($mode, $recipe->getMode());
        $this->assertSame(0., $recipe->getCraftingTime());
        $this->assertSame($craftingCategory, $recipe->getCraftingCategory());
        $this->assertInstanceOf(ArrayCollection::class, $recipe->getIngredients());
        $this->assertInstanceOf(ArrayCollection::class, $recipe->getProducts());
    }

    /**
     * Tests setting and getting the id.
     * @covers ::getId
     * @covers ::setId
     */
    public function testSetAndGetId()
    {
        $recipe = new Recipe('foo', 'bar', new CraftingCategory('baz'));

        $id = 42;
        $this->assertSame($recipe, $recipe->setId($id));
        $this->assertSame($id, $recipe->getId());
    }

    /**
     * Tests setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName()
    {
        $recipe = new Recipe('foo', 'bar', new CraftingCategory('baz'));

        $name = 'abc';
        $this->assertSame($recipe, $recipe->setName($name));
        $this->assertSame($name, $recipe->getName());
    }

    /**
     * Tests setting and getting the mode.
     * @covers ::getMode
     * @covers ::setMode
     */
    public function testSetAndGetMode()
    {
        $recipe = new Recipe('foo', 'bar', new CraftingCategory('baz'));

        $mode = 'abc';
        $this->assertSame($recipe, $recipe->setMode($mode));
        $this->assertSame($mode, $recipe->getMode());
    }

    /**
     * Tests setting and getting the craftingTime.
     * @covers ::getCraftingTime
     * @covers ::setCraftingTime
     */
    public function testSetAndGetCraftingTime()
    {
        $recipe = new Recipe('foo', 'bar', new CraftingCategory('baz'));

        $craftingTime = 13.37;
        $this->assertSame($recipe, $recipe->setCraftingTime($craftingTime));
        $this->assertSame($craftingTime, $recipe->getCraftingTime());
    }

    /**
     * Tests setting and getting the craftingCategory.
     * @covers ::getCraftingCategory
     * @covers ::setCraftingCategory
     */
    public function testSetAndGetCraftingCategory()
    {
        $recipe = new Recipe('foo', 'bar', new CraftingCategory('baz'));

        $craftingCategory = new CraftingCategory('abc');
        $this->assertSame($recipe, $recipe->setCraftingCategory($craftingCategory));
        $this->assertSame($craftingCategory, $recipe->getCraftingCategory());
    }

    /**
     * Tests the getOrderedIngredients method.
     * @covers ::getOrderedIngredients
     */
    public function testGetOrderedIngredients()
    {
        $recipe = new Recipe('abc', 'def', new CraftingCategory('baz'));

        $ingredient1 = new RecipeIngredient($recipe, new Item('ghi', 'jkl'));
        $ingredient1->setOrder(42);
        $ingredient2 = new RecipeIngredient($recipe, new Item('mno', 'pqr'));
        $ingredient2->setOrder(21);

        $recipe->getIngredients()->add($ingredient1);
        $recipe->getIngredients()->add($ingredient2);

        $result = $recipe->getOrderedIngredients();
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame([1 => $ingredient2, 0 => $ingredient1], $result->toArray());
    }

    /**
     * Tests the getOrderedProducts method.
     * @covers ::getOrderedProducts
     */
    public function testGetOrderedProducts()
    {
        $recipe = new Recipe('abc', 'def', new CraftingCategory('baz'));

        $product1 = new RecipeProduct($recipe, new Item('ghi', 'jkl'));
        $product1->setOrder(42);
        $product2 = new RecipeProduct($recipe, new Item('mno', 'pqr'));
        $product2->setOrder(21);

        $recipe->getProducts()->add($product1);
        $recipe->getProducts()->add($product2);

        $result = $recipe->getOrderedProducts();
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame([1 => $product2, 0 => $product1], $result->toArray());
    }
}
