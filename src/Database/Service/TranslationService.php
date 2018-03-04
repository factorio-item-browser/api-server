<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Client\Entity\TranslatedEntityInterface;
use FactorioItemBrowser\Api\Server\Database\Entity\Translation;
use FactorioItemBrowser\Api\Server\Database\Repository\TranslationRepository;

/**
 * The service class of the translation database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationService extends AbstractModsAwareService
{
    /**
     * The repository of the translations.
     * @var TranslationRepository
     */
    protected $translationRepository;

    /**
     * The locale to be used for translations.
     * @var string
     */
    protected $currentLocale = 'en';

    /**
     * The entities which need to be translated.
     * @var array|TranslatedEntityInterface[]
     */
    protected $entitiesToTranslate = [];

    /**
     * Initializes the repositories needed by the service.
     * @param EntityManager $entityManager
     * @return $this
     */
    protected function initializeRepositories(EntityManager $entityManager)
    {
        $this->translationRepository = $entityManager->getRepository(Translation::class);
        return $this;
    }

    /**
     * Sets the locale to be used for translations.
     * @param string $currentLocale
     * @return $this Implementing fluent interface.
     */
    public function setCurrentLocale(string $currentLocale)
    {
        $this->currentLocale = $currentLocale;
        return $this;
    }

    /**
     * Adds an entity to be translated at a later point.
     * @param TranslatedEntityInterface $entity
     * @return $this
     */
    public function addEntityToTranslate(TranslatedEntityInterface $entity)
    {
        $this->entitiesToTranslate[] = $entity;
        return $this;
    }

    /**
     * Translates the entities which have been added to the service.
     * @return $this
     */
    public function translateEntities()
    {
        $entities = [];
        $namesByTypes = [];
        foreach ($this->entitiesToTranslate as $entity) {
            $type = $entity->getType();
            $name = $entity->getName();

            $namesByTypes[$type][] = $name;
            $entities[$type . '|' . $name][] = $entity;
        }

        if (!empty($namesByTypes)) {
            $translations = $this->translationRepository->findAllTranslationsByTypesAndNames(
                $this->currentLocale,
                $namesByTypes,
                $this->modService->getEnabledModCombinationIds()
            );
            $translations = $this->sortTranslationData($translations);
            $this->matchTranslationDataToEntities($entities, $translations);
        }
        return $this;
    }

    /**
     * Sorts the translation data so that translation with higher priority come later in the array.
     * @param array $translationData
     * @return array
     */
    protected function sortTranslationData(array $translationData): array
    {
        usort($translationData, function (array $left, array $right): int {
            $result = ($left['locale'] !== 'en') <=> ($right['locale'] !== 'en');
            if ($result === 0) {
                $result = $left['type'] <=> $right['type'];
                if ($result === 0) {
                    $result = $left['order'] <=> $right['order'];
                    if ($result === 0) {
                        $result = $left['name'] <=> $right['name'];
                    }
                }
            }
            return $result;
        });
        return $translationData;
    }

    /**
     * Matches the translation data to the entities.
     * @param array|TranslatedEntityInterface[] $entities
     * @param array $translationData
     * @return $this
     */
    protected function matchTranslationDataToEntities(array $entities, array $translationData)
    {
        foreach ($translationData as $translation) {
            $keys = [$translation['type'] . '|' . $translation['name']];
            if ($translation['isDuplicatedByRecipe']) {
                $keys[] = EntityType::RECIPE . '|' . $translation['name'];
            }

            foreach ($keys as $key) {
                foreach ($entities[$key] ?? [] as $entity) {
                    /* @var TranslatedEntityInterface $entity */
                    if (strlen($translation['value']) > 0) {
                        $entity->setLabel($translation['value']);
                    }
                    if (strlen($translation['description']) > 0) {
                        $entity->setDescription($translation['description']);
                    }
                }
            }
        }
        return $this;
    }
}