<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * The entity class if the item database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @ORM\Entity(repositoryClass="FactorioItemBrowser\Api\Server\Database\Repository\ItemRepository")
 * @ORM\Table(name="Item")
 */
class Item
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     *
     * The internal id of the item.
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\Column(name="type")
     *
     * The type of the item.
     * @var string
     */
    protected $type = '';

    /**
     * @ORM\Column(name="name")
     *
     * The unique name of the item.
     * @var string
     */
    protected $name = '';

    /**
     * @ORM\ManyToMany(targetEntity="ModCombination", mappedBy="items")
     *
     * The mod combinations which are adding the item.
     * @var Collection|ModCombination[]
     */
    protected $modCombinations;

    /**
     * Initializes the entity.
     * @param string $type
     * @param string $name
     */
    public function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
        $this->modCombinations = new ArrayCollection();
    }

    /**
     * Sets the internal id of the item.
     * @param int $id
     * @return $this Implementing fluent interface.
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the internal id of the item.
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets the type of the item.
     * @param string $type
     * @return $this Implementing fluent interface.
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type of the item.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the unique name of the item.
     * @param string $name
     * @return $this Implementing fluent interface.
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the unique name of the item.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the mod combinations which are adding the item.
     * @return Collection|ModCombination[]
     */
    public function getModCombinations(): Collection
    {
        return $this->modCombinations;
    }
}