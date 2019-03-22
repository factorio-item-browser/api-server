<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Database\Data\TranslationData;
use FactorioItemBrowser\Api\Database\Repository\TranslationRepository;
use FactorioItemBrowser\Api\Server\Constant\Config;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Common\Constant\EntityType;

/**
 * The service class of the translation database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationService
{
    /**
     * The repository of the translations.
     * @var TranslationRepository
     */
    protected $translationRepository;

    /**
     * The entities which need to be translated.
     * @var array|GenericEntity[]
     */
    protected $entitiesToTranslate = [];

    /**
     * Initializes the service.
     * @param TranslationRepository $translationRepository
     */
    public function __construct(TranslationRepository $translationRepository)
    {
        $this->translationRepository = $translationRepository;
    }

    /**
     * Adds an entity to be translated at a later point.
     * @param GenericEntity $entity
     */
    public function addEntityToTranslate(GenericEntity $entity): void
    {
        $this->entitiesToTranslate[] = $entity;
    }

    /**
     * Translates the entities which have been added to the service.
     * @param AuthorizationToken $authorizationToken
     */
    public function translateEntities(AuthorizationToken $authorizationToken): void
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
            $authorizationToken->getLocale(),
            $namesByTypes,
            $authorizationToken->getEnabledModCombinationIds()
        );
        $translations = $this->sortTranslationData($translations);
        $this->matchTranslationDataToEntities($entities, $translations);
    }

    /**
     * Sorts the translation data so that translation with higher priority come later in the array.
     * @param array|TranslationData[] $translationData
     * @return array|TranslationData[]
     */
    protected function sortTranslationData(array $translationData): array
    {
        usort($translationData, [$this, 'compareTranslations']);
        return $translationData;
    }

    /**
     * Compares the two translations.
     * @param TranslationData $left
     * @param TranslationData $right
     * @return int
     */
    protected function compareTranslations(TranslationData $left, TranslationData $right): int
    {
        $leftCriteria = $this->getSortCriteria($left);
        $rightCriteria = $this->getSortCriteria($right);

        $result = 0;
        while ($result === 0 && count($leftCriteria) > 0) {
            $result = array_shift($leftCriteria) <=> array_shift($rightCriteria);
        }
        return $result;
    }

    /**
     * Returns the criteria to sort the translation.
     * @param TranslationData $translation
     * @return array
     */
    protected function getSortCriteria(TranslationData $translation): array
    {
        return [
            $translation->getLocale() !== Config::DEFAULT_LOCALE,
            $translation->getType(),
            $translation->getOrder(),
            $translation->getName(),
        ];
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
