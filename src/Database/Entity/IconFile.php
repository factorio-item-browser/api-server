<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * The entity of the icon file database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @ORM\Entity(repositoryClass="FactorioItemBrowser\Api\Server\Database\Repository\IconFileRepository")
 * @ORM\Table(name="IconFile")
 */
class IconFile
{
    /**
     * @ORM\Id
     * @ORM\Column(name="`hash`", type="integer")
     * Note: BINARY(4) would be more appropriate, but Doctrine makes using binary fields in queries complicated.
     *
     * The hash of the icon file.
     * @var int|null
     */
    protected $hash;

    /**
     * @ORM\Column(name="image", type="blob")
     *
     * The actual image data.
     * @var resource
     */
    protected $image;

    /**
     * Initializes the entity.
     * @param int $hash
     */
    public function __construct(int $hash)
    {
        $this->hash = $hash;
    }

    /**
     * Sets the hash of the icon.
     * @param int $hash
     * @return $this Implementing fluent interface.
     */
    public function setHash(int $hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Returns the hash of the icon.
     * @return int
     */
    public function getHash(): int
    {
        return $this->hash;
    }

    /**
     * Sets the actual image data.
     * @param resource|string $image
     * @return $this Implementing fluent interface.
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Returns the actual image data.
     * @return string
     */
    public function getImage(): string
    {
        if (is_resource($this->image)) {
            $this->image = stream_get_contents($this->image);
        }
        return $this->image;
    }
}