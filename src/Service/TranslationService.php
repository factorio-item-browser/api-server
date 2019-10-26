<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Database\Entity\Translation;
use FactorioItemBrowser\Api\Database\Repository\TranslationRepository;
use FactorioItemBrowser\Api\Server\Constant\Config;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait;
use FactorioItemBrowser\Common\Constant\EntityType;

/**
 * The service handling the translations of client entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationService
{
    use TypeAndNameFromEntityExtractorTrait;

    /**
     * The repository of the translations.
     * @var TranslationRepository
     */
    protected $translationRepository;

    /**
     * The entities to be translated.
     * @var array|GenericEntity[]
     */
    protected $entities = [];

    /**
     * Initializes the service.
     * @param TranslationRepository $translationRepository
     */
    public function __construct(TranslationRepository $translationRepository)
    {
        $this->translationRepository = $translationRepository;
    }

    /**
     * Adds an entity to be translated.
     * @param GenericEntity $entity
     */
    public function addEntity(GenericEntity $entity): void
    {
        $this->entities[] = $entity;
    }

    /**
     * Translates the entities which have been added to the service.
     * @param AuthorizationToken $authorizationToken
     */
    public function translate(AuthorizationToken $authorizationToken): void
    {
        if (count($this->entities) === 0) {
            return;
        }

        $translations = $this->fetchTranslations($this->entities, $authorizationToken);
        $this->matchTranslationsToEntities($translations, $this->entities);
    }

    /**
     * Fetches the translations to the entities.
     * @param array|GenericEntity[] $entities
     * @param AuthorizationToken $authorizationToken
     * @return array|Translation[]
     */
    protected function fetchTranslations(array $entities, AuthorizationToken $authorizationToken): array
    {
        $translations = $this->translationRepository->findByTypesAndNames(
            $authorizationToken->getCombinationId(),
            $authorizationToken->getLocale(),
            $this->extractTypesAndNames($entities)
        );

        usort($translations, [$this, 'compareTranslations']);
        return $this->prepareTranslations($translations);
    }

    /**
     * Compares the two translations.
     * @param Translation $left
     * @param Translation $right
     * @return int
     */
    protected function compareTranslations(Translation $left, Translation $right): int
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
     * @param Translation $translation
     * @return array
     */
    protected function getSortCriteria(Translation $translation): array
    {
        return [
            $translation->getLocale() !== Config::DEFAULT_LOCALE,
            $translation->getType(),
            $translation->getName(),
        ];
    }

    /**
     * Prepares the translations for matching to the entities.
     * @param array|Translation[] $translations
     * @return array
     */
    protected function prepareTranslations(array $translations): array
    {
        $result = [];
        foreach ($translations as $translation) {
            foreach ($this->getTypesForTranslation($translation) as $type) {
                $result[$this->getTranslationKey($type, $translation->getName())] = $translation;
            }
        }
        return $result;
    }

    /**
     * Returns the types the translation can be applied to.
     * @param Translation $translation
     * @return array|string[]
     */
    protected function getTypesForTranslation(Translation $translation): array
    {
        $result = [$translation->getType()];
        if ($translation->getIsDuplicatedByMachine()) {
            $result[] = EntityType::MACHINE;
        }
        if ($translation->getIsDuplicatedByRecipe()) {
            $result[] = EntityType::RECIPE;
        }
        return array_values(array_unique($result));
    }

    /**
     * Matches the translations to the entities.
     * @param array|Translation[] $translations
     * @param array|GenericEntity[] $entities
     */
    protected function matchTranslationsToEntities(array $translations, array $entities): void
    {
        foreach ($entities as $entity) {
            $translationKey = $this->getTranslationKey($entity->getType(), $entity->getName());
            if (isset($translations[$translationKey])) {
                $translation = $translations[$translationKey];
                $entity->setLabel($translation->getValue())
                       ->setDescription($translation->getDescription());
            }
        }
    }

    /**
     * Returns the key to match the translation.
     * @param string $type
     * @param string $name
     * @return string
     */
    protected function getTranslationKey(string $type, string $name): string
    {
        return "{$type}|{$name}";
    }
}
