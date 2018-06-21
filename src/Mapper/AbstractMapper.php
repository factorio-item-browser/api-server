<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;

/**
 * The abstract class of the mappers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractMapper
{
    /**
     * The database translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Initializes the mapper.
     * @param TranslationService $translationService
     */
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }
}