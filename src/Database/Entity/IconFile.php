<?php

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * The entity of the icon file database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @ORM\Entity
 * @ORM\Table(name="IconFile")
 */
class IconFile
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     *
     * The internal id of the icon file.
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\Column(name="layerHash")
     *
     * The hash value of the icon layers.
     * @var string
     */
    protected $layerHash;

    /**
     * @ORM\Column(name="image", type="blob")
     *
     * The actual image data.
     * @var resource
     */
    protected $image;

    /**
     * Initializes the entity.
     * @param string $layerHash
     */
    public function __construct(string $layerHash)
    {
        $this->layerHash = $layerHash;
    }

    /**
     * Sets the internal id of the icon file.
     * @param int $id
     * @return $this Implementing fluent interface.
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the internal id of the icon file.
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets the hash value of the icon layers.
     * @param string $layerHash
     * @return $this Implementing fluent interface.
     */
    public function setLayerHash(string $layerHash)
    {
        $this->layerHash = $layerHash;
        return $this;
    }

    /**
     * Returns the hash value of the icon layers.
     * @return string
     */
    public function getLayerHash(): string
    {
        return $this->layerHash;
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