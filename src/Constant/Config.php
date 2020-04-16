<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Constant;

/**
 * The interface holding some default config values which will never change.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface Config
{
    /**
     * The default locale.
     */
    public const DEFAULT_LOCALE = 'en';

    /**
     * The default combination id to use as fallback.
     */
    public const DEFAULT_COMBINATION_ID = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
}
