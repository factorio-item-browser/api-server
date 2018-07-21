<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(name="`hash`")
     *
     * The hash of the icon file.
     * @var string
     */
    protected $hash;

    /**
     * @ORM\Column(name="image", type="blob")
     *
     * The actual image data.
     * @var string|resource
     */
    protected $image = '';

    /**
     * @ORM\OneToMany(targetEntity="Icon", mappedBy="file")
     *
     * The icons using the file.
     * @var Collection|Icon[]
     */
    protected $icons;

    /**
     * Initializes the entity.
     * @param string $hash
     */
    public function __construct(string $hash)
    {
        $this->setHash($hash);
        $this->icons = new ArrayCollection();
    }

    /**
     * Sets the hash of the icon.
     * @param string $hash
     * @return $this Implementing fluent interface.
     */
    public function setHash(string $hash)
    {
        $this->hash = hex2bin($hash);
        return $this;
    }

    /**
     * Returns the hash of the icon.
     * @return string
     */
    public function getHash(): string
    {
        return bin2hex($this->hash);
    }

    /**
     * Sets the actual image data.
     * @param string $image
     * @return $this Implementing fluent interface.
     */
    public function setImage(string $image)
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

    /**
     * Returns the icons using this file.
     * @return Collection|Icon[]
     */
    public function getIcons(): Collection
    {
        return $this->icons;
    }
}
