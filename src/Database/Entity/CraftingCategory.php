<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * The entity of the crafting category database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @ORM\Entity(repositoryClass="FactorioItemBrowser\Api\Server\Database\Repository\CraftingCategoryRepository")
 * @ORM\Table(name="CraftingCategory")
 */
class CraftingCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     *
     * The internal id of the crafting category.
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\Column(name="name")
     *
     * The name of the crafting category.
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Machine", mappedBy="craftingCategories")
     *
     * The machines supporting the crafting category.
     * @var Collection|ModCombination[]
     */
    protected $machines;

    /**
     * Initializes the entity.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->machines = new ArrayCollection();
    }

    /**
     * Sets the internal id of the crafting category.
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the internal id of the crafting category.
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets the name of the crafting category.
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the crafting category.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the machines supporting the crafting category.
     * @return Collection|ModCombination[]
     */
    public function getMachines(): Collection
    {
        return $this->machines;
    }
}