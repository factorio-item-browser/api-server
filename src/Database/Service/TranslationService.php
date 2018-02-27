<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
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
     * @param bool $useEnabledMods
     * @return $this
     */
    public function translateEntities(bool $useEnabledMods)
    {
        $entities = [];
        $namesByTypes = [];
        foreach ($this->entitiesToTranslate as $entity) {
            $type = $entity->getTranslationType();
            $name = $entity->getName();

            $namesByTypes[$type][] = $name;
            $entities[$type . '|' . $name][] = $entity;
        }

        if (!empty($namesByTypes)) {
            $translations = $this->translationRepository->findAllTranslationsByTypesAndNames(
                $this->currentLocale,
                $namesByTypes,
                $useEnabledMods ? $this->modService->getEnabledModCombinationIds() : []
            );

            usort($translations, function (array $left, array $right): int {
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

            foreach ($translations as $translation) {
                $key = $translation['type'] . '|' . $translation['name'];
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