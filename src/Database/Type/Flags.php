<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Traversable;

/**
 * The Doctrine type to use flags in a column (mapped to the SET type of MySQL).
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Flags extends Type
{
    /**
     * Returns the SQL declaration snippet for a field of this type.
     * @param array $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform The currently used database platform.
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'TEXT'; // We are not using this feature.
    }

    /**
     * Returns the name of this type.
     * @return string
     */
    public function getName()
    {
        return 'flags';
    }

    /**
     * Converts the PHP value to its database representation.
     * @param array $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $flags = [];
        if (is_array($value)) {
            $flags = $value;
        } elseif ($value instanceof Traversable) {
            $flags = iterator_to_array($value);
        }
        return implode(',', $flags);
    }

    /**
     * Converts the database value to its PHP representation.
     * @param string $value
     * @param AbstractPlatform $platform
     * @return array
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $result = [];
        if (!empty($value)) {
            $flags = explode(',', $value);
            $result = array_combine($flags, $flags);
        }
        return $result;
    }
}