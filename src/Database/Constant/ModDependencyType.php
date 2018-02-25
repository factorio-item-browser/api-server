<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Constant;

/**
 * The types of the mod dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDependencyType
{
    /**
     * The required mod is mandatory.
     */
    const MANDATORY = 'mandatory';

    /**
     * The required mod is optional.
     */
    const OPTIONAL = 'optional';
}