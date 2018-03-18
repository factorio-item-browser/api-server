<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Server\Database\Entity\Translation as DatabaseTranslation;
use FactorioItemBrowser\ExportData\Entity\Item as ExportItem;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;
use FactorioItemBrowser\ExportData\Entity\Recipe as ExportRecipe;

/**
 * The class importing the translation.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationImporter implements ImporterInterface
{
    /**
     * The Doctrine entity manager.
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The translation of the mod meta.
     * @var array|DatabaseTranslation[]
     */
    protected $modTranslations = [];

    /**
     * The database translations.
     * @var array|DatabaseTranslation[]
     */
    protected $databaseTranslations;

    /**
     * Initializes the importer.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Imports the mod.
     * @param ExportMod $exportMod
     * @param DatabaseMod $databaseMod
     * @return $this
     */
    public function importMod(ExportMod $exportMod, DatabaseMod $databaseMod)
    {
        foreach ($exportMod->getTitles()->getTranslations() as $locale => $title) {
            $key = $locale . '|mod|' . $exportMod->getName();
            $translation = $this->createDatabaseTranslation($locale, 'mod', $exportMod->getName());
            $translation->setValue($title);
            $this->modTranslations[$key] = $translation;
        }
        foreach ($exportMod->getDescriptions()->getTranslations() as $locale => $description) {
            $key = $locale . '|mod|' . $exportMod->getName();
            if (!isset($this->modTranslations[$key])) {
                $this->modTranslations[$key] = $this->createDatabaseTranslation($locale, 'mod', $exportMod->getName());
            }
            $this->modTranslations[$key]->setDescription($description);
        }
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
        if (count($exportCombination->getLoadedOptionalModNames()) === 0) {
            $this->databaseTranslations = $this->modTranslations;
        } else {
            $this->databaseTranslations = [];
        }

        foreach ($exportCombination->getData()->getItems() as $exportItem) {
            $this->processItem($exportItem);
        }
        foreach ($exportCombination->getData()->getRecipes() as $exportRecipe) {
            $this->processRecipe($exportRecipe);
        }

        $this->assignTranslationsToCombination($databaseCombination, $this->databaseTranslations);
        return $this;
    }

    /**
     * Processes the specified item.
     * @param ExportItem $exportItem
     * @return $this
     */
    protected function processItem(ExportItem $exportItem)
    {
        foreach ($exportItem->getLabels()->getTranslations() as $locale => $label) {
            $translation = $this->getDatabaseTranslation($locale, $exportItem->getType(), $exportItem->getName());
            $translation->setValue($label)
                        ->setIsDuplicatedByRecipe($exportItem->getProvidesRecipeLocalisation());
        }
        foreach ($exportItem->getDescriptions()->getTranslations() as $locale => $description) {
            $translation = $this->getDatabaseTranslation($locale, $exportItem->getType(), $exportItem->getName());
            $translation->setDescription($description)
                        ->setIsDuplicatedByRecipe($exportItem->getProvidesRecipeLocalisation());
        }
        return $this;
    }

    /**
     * Processes the specified recipe.
     * @param ExportRecipe $exportRecipe
     * @return $this
     */
    protected function processRecipe(ExportRecipe $exportRecipe)
    {
        foreach ($exportRecipe->getLabels()->getTranslations() as $locale => $label) {
            $translation = $this->getDatabaseTranslation($locale, EntityType::RECIPE, $exportRecipe->getName());
            $translation->setValue($label);
        }
        foreach ($exportRecipe->getDescriptions()->getTranslations() as $locale => $description) {
            $translation = $this->getDatabaseTranslation($locale, EntityType::RECIPE, $exportRecipe->getName());
            $translation->setDescription($description);
        }
        return $this;
    }

    /**
     * Returns the specified database translation entity.
     * @param string $locale
     * @param string $type
     * @param string $name
     * @return DatabaseTranslation
     */
    protected function getDatabaseTranslation(string $locale, string $type, string $name): DatabaseTranslation
    {
        $key = $locale . '|' . $type . '|' . $name;
        if (!isset($this->databaseTranslations[$key])) {
            $this->databaseTranslations[$key] = $this->createDatabaseTranslation($locale, $type, $name);
        }
        return $this->databaseTranslations[$key];
    }

    /**
     * Creates a database translation entity..
     * @param string $locale
     * @param string $type
     * @param string $name
     * @return DatabaseTranslation
     */
    protected function createDatabaseTranslation(string $locale, string $type, string $name): DatabaseTranslation
    {
        $translation = new DatabaseTranslation();
        $translation->setLocale($locale)
                    ->setType($type)
                    ->setName($name);
        return $translation;
    }

    /**
     * Assigns the translation to the combination.
     * @param DatabaseCombination $databaseCombination
     * @param array|DatabaseTranslation[] $translations
     * @return $this
     */
    protected function assignTranslationsToCombination(DatabaseCombination $databaseCombination, array $translations)
    {
        /* @var array|DatabaseTranslation[] $combinationTranslations */
        $combinationTranslations = [];
        foreach ($databaseCombination->getTranslations() as $translation) {
            $key = $translation->getLocale() . '|' . $translation->getType() . '|' . $translation->getName();
            $combinationTranslations[$key] = $translation;
        }

        foreach ($translations as $key => $translation) {
            if (isset($combinationTranslations[$key])) {
                $combinationTranslation = $combinationTranslations[$key];
                $combinationTranslation->setValue($translation->getValue())
                                       ->setDescription($translation->getDescription())
                                       ->setIsDuplicatedByRecipe($translation->getIsDuplicatedByRecipe());
                unset($combinationTranslations[$key]);
            } else {
                $translation->setModCombination($databaseCombination);
                $databaseCombination->getTranslations()->add($translation);
                $this->entityManager->persist($translation);
            }
        }

        foreach ($combinationTranslations as $translation) {
            $databaseCombination->getTranslations()->removeElement($translation);
            $this->entityManager->remove($translation);
        }
        return $this;
    }

    /**
     * Cleans up any no longer needed data.
     * @return $this
     */
    public function clean()
    {
        return $this;
    }
}