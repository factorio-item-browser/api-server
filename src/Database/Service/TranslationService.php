<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Database\Data\TranslationData;
use FactorioItemBrowser\Api\Database\Repository\TranslationRepository;
use FactorioItemBrowser\Common\Constant\EntityType;

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
     * @var array|GenericEntity[]
     */
    protected $entitiesToTranslate = [];

    /**
     * TranslationService constructor.
     * @param ModService $modService
     * @param TranslationRepository $translationRepository
     */
    public function __construct(ModService $modService, TranslationRepository $translationRepository)
    {
        parent::__construct($modService);

        $this->translationRepository = $translationRepository;
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
     * Returns the locale to be used for translations.
     * @return string
     */
    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }

    /**
     * Adds an entity to be translated at a later point.
     * @param GenericEntity $entity
     * @return $this
     */
    public function addEntityToTranslate(GenericEntity $entity)
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

        $translations = $this->translationRepository->findDataByTypesAndNames(
            $this->currentLocale,
            $namesByTypes,
            $this->modService->getEnabledModCombinationIds()
        );
        $translations = $this->sortTranslationData($translations);
        $this->matchTranslationDataToEntities($entities, $translations);
        return $this;
    }

    /**
     * Sorts the translation data so that translation with higher priority come later in the array.
     * @param array|TranslationData[] $translationData
     * @return array|TranslationData[]
     */
    protected function sortTranslationData(array $translationData): array
    {
        usort($translationData, function (TranslationData $left, TranslationData $right): int {
            $result = ($left->getLocale() !== 'en') <=> ($right->getLocale() !== 'en');
            if ($result === 0) {
                $result = $left->getType() <=> $right->getType();
                if ($result === 0) {
                    $result = $left->getOrder() <=> $right->getOrder();
                    if ($result === 0) {
                        $result = $left->getName() <=> $right->getName();
                    }
                }
            }
            return $result;
        });
        return $translationData;
    }

    /**
     * Matches the translation data to the entities.
     * @param array|GenericEntity[][] $entities
     * @param array|TranslationData[] $translationData
     * @return $this
     */
    protected function matchTranslationDataToEntities(array $entities, array $translationData)
    {
        foreach ($translationData as $translation) {
            $keys = [$translation->getType() . '|' . $translation->getName()];
            if ($translation->getIsDuplicatedByRecipe()) {
                $keys[] = EntityType::RECIPE . '|' . $translation->getName();
            }
            if ($translation->getIsDuplicatedByMachine()) {
                $keys[] = EntityType::MACHINE . '|' . $translation->getName();
            }

            foreach ($keys as $key) {
                foreach ($entities[$key] ?? [] as $entity) {
                    if (strlen($translation->getValue()) > 0) {
                        $entity->setLabel($translation->getValue());
                    }
                    if (strlen($translation->getDescription()) > 0) {
                        $entity->setDescription($translation->getDescription());
                    }
                }
            }
        }
        return $this;
    }
}
