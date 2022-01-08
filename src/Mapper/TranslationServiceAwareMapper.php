<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Server\Service\TranslationService;

/**
 * The abstract class of the mappers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class TranslationServiceAwareMapper
{
    public function __construct(
        private readonly TranslationService $translationService,
    ) {
    }

    /**
     * Adds the specified entity to the translation service.
     */
    protected function addToTranslationService(GenericEntity $entity): void
    {
        $this->translationService->addEntity($entity);
    }
}
