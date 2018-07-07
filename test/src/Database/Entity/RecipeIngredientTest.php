<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Entity;

use FactorioItemBrowser\Api\Server\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Server\Database\Entity\Item;
use FactorioItemBrowser\Api\Server\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Database\Entity\RecipeIngredient;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeIngredient class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Entity\RecipeIngredient
 */
class RecipeIngredientTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $recipe = new Recipe('abc', 'def', new CraftingCategory('ghi'));
        $item = new Item('jkl', 'mno');
        $recipeIngredient = new RecipeIngredient($recipe, $item);

        $this->assertSame($recipe, $recipeIngredient->getRecipe());
        $this->assertSame($item, $recipeIngredient->getItem());
        $this->assertSame(0., $recipeIngredient->getAmount());
        $this->assertSame(0, $recipeIngredient->getOrder());
    }

    /**
     * Tests setting and getting the recipe.
     * @covers ::getRecipe
     * @covers ::setRecipe
     */
    public function testSetAndGetRecipe()
    {
        $recipeIngredient = new RecipeIngredient(
            new Recipe('foo', 'bar', new CraftingCategory('baz')),
            new Item('bar', 'foo')
        );

        $recipe = new Recipe('abc', 'def', new CraftingCategory('ghi'));
        $this->assertSame($recipeIngredient, $recipeIngredient->setRecipe($recipe));
        $this->assertSame($recipe, $recipeIngredient->getRecipe());
    }

    /**
     * Tests setting and getting the item.
     * @covers ::getItem
     * @covers ::setItem
     */
    public function testSetAndGetItem()
    {
        $recipeIngredient = new RecipeIngredient(
            new Recipe('foo', 'bar', new CraftingCategory('baz')),
            new Item('bar', 'foo')
        );

        $item = new Item('abc', 'def');
        $this->assertSame($recipeIngredient, $recipeIngredient->setItem($item));
        $this->assertSame($item, $recipeIngredient->getItem());
    }

    /**
     * Tests setting and getting the amount.
     * @covers ::getAmount
     * @covers ::setAmount
     */
    public function testSetAndGetAmount()
    {
        $recipeIngredient = new RecipeIngredient(
            new Recipe('foo', 'bar', new CraftingCategory('baz')),
            new Item('bar', 'foo')
        );

        $amount = 13.37;
        $this->assertSame($recipeIngredient, $recipeIngredient->setAmount($amount));
        $this->assertSame($amount, $recipeIngredient->getAmount());
    }

    /**
     * Tests setting and getting the order.
     * @covers ::getOrder
     * @covers ::setOrder
     */
    public function testSetAndGetOrder()
    {
        $recipeIngredient = new RecipeIngredient(
            new Recipe('foo', 'bar', new CraftingCategory('baz')),
            new Item('bar', 'foo')
        );

        $order = 42;
        $this->assertSame($recipeIngredient, $recipeIngredient->setOrder($order));
        $this->assertSame($order, $recipeIngredient->getOrder());
    }
}
