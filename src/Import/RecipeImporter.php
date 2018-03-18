<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Server\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Server\Database\Entity\RecipeIngredient as DatabaseRecipeIngredient;
use FactorioItemBrowser\Api\Server\Database\Entity\RecipeProduct as DatabaseRecipeProduct;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;
use FactorioItemBrowser\ExportData\Entity\Recipe as ExportRecipe;

/**
 * The class importing the recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeImporter implements ImporterInterface
{
    /**
     * The entity manager.
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The database service of the items.
     * @var ItemService
     */
    protected $itemService;

    /**
     * The database service of the recipes.
     * @var RecipeService
     */
    protected $recipeService;

    /**
     * The item types and names seen in the export data.
     * @var array|string[][]
     */
    protected $seenItemTypesAndNames = [];

    /**
     * The database items needed by the current import.
     * @var array|DatabaseItem[]
     */
    protected $databaseItems;

    /**
     * Initializes the importer.
     * @param EntityManager $entityManager
     * @param ItemService $itemService
     * @param RecipeService $recipeService
     */
    public function __construct(EntityManager $entityManager, ItemService $itemService, RecipeService $recipeService)
    {
        $this->entityManager = $entityManager;
        $this->itemService = $itemService;
        $this->recipeService = $recipeService;
    }

    /**
     * Imports the mod.
     * @param ExportMod $exportMod
     * @param DatabaseMod $databaseMod
     * @return $this
     */
    public function importMod(ExportMod $exportMod, DatabaseMod $databaseMod)
    {
        return $this;
    }

    /**
     * Imports the combination.
     * @param ExportCombination $exportCombination
     * @param DatabaseCombination $databaseCombination
     * @return $this
     */
    public function importCombination(ExportCombination $exportCombination, DatabaseCombination $databaseCombination)
    {
        $this->seenItemTypesAndNames = [];

        $databaseRecipes = $this->getExistingRecipes($exportCombination);
        $databaseRecipes = $this->addMissingRecipes($exportCombination, $databaseRecipes);
        $this->assignRecipesToCombination($databaseCombination, $databaseRecipes);

        return $this;
    }

    /**
     * Returns the already existing recipes from the database.
     * @param ExportCombination $exportCombination
     * @return array|DatabaseRecipe[]
     */
    protected function getExistingRecipes(ExportCombination $exportCombination): array
    {
        $recipeNames = [];
        foreach ($exportCombination->getData()->getRecipes() as $exportRecipe) {
            if (count($exportRecipe->getIngredients()) > 0 || count($exportRecipe->getProducts()) > 0) {
                $recipeNames[$this->hashExportRecipe($exportRecipe)] = $exportRecipe->getName();
            }
        }

        $groupedDatabaseRecipeIds = $this->recipeService->getIdsByNames(array_values($recipeNames));
        if (count($groupedDatabaseRecipeIds) > 0) {
            $databaseRecipeIds = call_user_func_array('array_merge', $groupedDatabaseRecipeIds);
        } else {
            $databaseRecipeIds = [];
        }

        $result = [];
        foreach ($this->recipeService->getDetailsByIds($databaseRecipeIds) as $databaseRecipe) {
            $exportRecipe = $exportCombination->getData()->getRecipe(
                $databaseRecipe->getName(),
                $databaseRecipe->getMode()
            );
            if ($exportRecipe instanceof ExportRecipe
                && $this->hashExportRecipe($exportRecipe) === $this->hashDatabaseRecipe($databaseRecipe)
            ) {
                $result[$databaseRecipe->getName() . '|' . $databaseRecipe->getMode()] = $databaseRecipe;
            }
        }
        return $result;
    }

    /**
     * Hashes the specified export recipe.
     * @param ExportRecipe $exportRecipe
     * @return string
     */
    protected function hashExportRecipe(ExportRecipe $exportRecipe): string
    {
        $ingredients = [];
        foreach ($exportRecipe->getIngredients() as $ingredient) {
            $ingredients[$ingredient->getOrder()] = [
                $ingredient->getType(),
                $ingredient->getName(),
                $ingredient->getAmount()
            ];
            $this->seenItemTypesAndNames[$ingredient->getType()][$ingredient->getName()] = $ingredient->getName();
        }
        ksort($ingredients);

        $products = [];
        foreach ($exportRecipe->getProducts() as $product) {
            $products[$product->getOrder()] = [
                $product->getType(),
                $product->getName(),
                $product->getAmountMin(),
                $product->getAmountMax(),
                $product->getProbability()
            ];
            $this->seenItemTypesAndNames[$product->getType()][$product->getName()] = $product->getName();
        }
        ksort($products);

        $data = [
            'name' => $exportRecipe->getName(),
            'mode' => $exportRecipe->getMode(),
            'craftingTime' => $exportRecipe->getCraftingTime(),
            'ingredients' => $ingredients,
            'products' => $products
        ];
        return hash('crc32b', json_encode($data));
    }

    /**
     * Hashes the specified database recipe.
     * @param DatabaseRecipe $databaseRecipe
     * @return string
     */
    protected function hashDatabaseRecipe(DatabaseRecipe $databaseRecipe): string
    {
        $ingredients = [];
        foreach ($databaseRecipe->getIngredients() as $ingredient) {
            $ingredients[$ingredient->getOrder()] = [
                $ingredient->getItem()->getType(),
                $ingredient->getItem()->getName(),
                $ingredient->getAmount()
            ];
        }
        ksort($ingredients);

        $products = [];
        foreach ($databaseRecipe->getProducts() as $product) {
            $products[$product->getOrder()] = [
                $product->getItem()->getType(),
                $product->getItem()->getName(),
                $product->getAmountMin(),
                $product->getAmountMax(),
                $product->getProbability()
            ];
        }
        ksort($products);

        $data = [
            'name' => $databaseRecipe->getName(),
            'mode' => $databaseRecipe->getMode(),
            'craftingTime' => $databaseRecipe->getCraftingTime(),
            'ingredients' => $ingredients,
            'products' => $products
        ];
        return hash('crc32b', json_encode($data));
    }

    /**
     * Adds missing recipes to the database.
     * @param ExportCombination $exportCombination
     * @param array|DatabaseRecipe[] $databaseRecipes
     * @return array|DatabaseRecipe[]
     */
    protected function addMissingRecipes(ExportCombination $exportCombination, array $databaseRecipes): array
    {
        $this->databaseItems = [];
        foreach ($this->itemService->getByTypesAndNames($this->seenItemTypesAndNames) as $databaseItem) {
            $this->databaseItems[$databaseItem->getType() . '|' . $databaseItem->getName()] = $databaseItem;
        }

        foreach ($exportCombination->getData()->getRecipes() as $exportRecipe) {
            if (count($exportRecipe->getIngredients()) > 0 || count($exportRecipe->getProducts()) > 0) {
                $key = $exportRecipe->getName() . '|' . $exportRecipe->getMode();
                if (!$databaseRecipes[$key] instanceof DatabaseRecipe) {
                    $databaseRecipes[$key] = $this->persistRecipe($exportRecipe);
                }
            }
        }
        return $databaseRecipes;
    }

    /**
     * Persists the specified recipe into the database.
     * @param ExportRecipe $exportRecipe
     * @return DatabaseRecipe
     */
    protected function persistRecipe(ExportRecipe $exportRecipe): DatabaseRecipe {
        $databaseRecipe = new DatabaseRecipe($exportRecipe->getName(), $exportRecipe->getMode());
        $databaseRecipe->setCraftingTime($exportRecipe->getCraftingTime());
        $this->entityManager->persist($databaseRecipe);

        foreach ($exportRecipe->getIngredients() as $exportIngredient) {
            $item = $this->getItem($exportIngredient->getType(), $exportIngredient->getName());
            $databaseIngredient = new DatabaseRecipeIngredient($databaseRecipe, $item);
            $databaseIngredient->setAmount($exportIngredient->getAmount())
                               ->setOrder($exportIngredient->getOrder());

            $databaseRecipe->getIngredients()->add($databaseIngredient);
            $this->entityManager->persist($databaseIngredient);
        }

        foreach ($exportRecipe->getProducts() as $exportProduct) {
            $item = $this->getItem($exportProduct->getType(), $exportProduct->getName());
            $databaseProduct = new DatabaseRecipeProduct($databaseRecipe, $item);
            $databaseProduct->setAmountMin($exportProduct->getAmountMin())
                            ->setAmountMax($exportProduct->getAmountMax())
                            ->setProbability($exportProduct->getProbability())
                            ->setOrder($exportProduct->getOrder());
            $databaseRecipe->getProducts()->add($databaseProduct);
            $this->entityManager->persist($databaseProduct);
        }
        return $databaseRecipe;
    }

    /**
     * Returns the specified database item.
     * @param string $type
     * @param string $name
     * @return DatabaseItem
     * @throws ApiServerException
     */
    protected function getItem(string $type, string $name): DatabaseItem
    {
        $key = $type . '|' . $name;
        if (!isset($this->databaseItems[$key])) {
            throw new ApiServerException('Unknown item ' . $type . '/' . $name);
        }
        return $this->databaseItems[$key];
    }

    /**
     * Assigns the recipes to the database combination.
     * @param DatabaseCombination $databaseCombination
     * @param array|DatabaseRecipe[] $databaseRecipes
     * @return $this
     */
    protected function assignRecipesToCombination(DatabaseCombination $databaseCombination, array $databaseRecipes)
    {
        foreach ($databaseCombination->getRecipes() as $combinationRecipe) {
            $key = $combinationRecipe->getName() . '|' . $combinationRecipe->getMode();
            if (isset($databaseRecipes[$key])) {
                unset($databaseRecipes[$key]);
            } else {
                $databaseCombination->getRecipes()->removeElement($combinationRecipe);
            }
        }

        foreach ($databaseRecipes as $databaseRecipe) {
            $databaseCombination->getRecipes()->add($databaseRecipe);
        }
        return $this;
    }

    /**
     * Cleans up any no longer needed data.
     * @return $this
     */
    public function clean()
    {
        $this->recipeService->removeOrphans();
        return $this;
    }
}