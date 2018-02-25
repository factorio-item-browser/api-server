<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * The entity class of the Mod database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @ORM\Entity(repositoryClass="FactorioItemBrowser\Api\Server\Database\Repository\ModRepository")
 * @ORM\Table(name="`Mod`")
 */
class Mod
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     *
     * The internal id of the mod.
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="name", unique=true)
     *
     * The name of the mod.
     * @var string
     */
    protected $name = '';

    /**
     * @ORM\Column(name="author")
     *
     * The author of the mod.
     * @var string
     */
    protected $author = '';

    /**
     * @ORM\Column(name="currentVersion")
     *
     * The current version of the mod that has been imported.
     * @var string
     */
    protected $currentVersion = '';

    /**
     * @ORM\Column(name="`order`", type="integer")
     *
     * The order position of the mod, 1 being the base mod.
     * @var int
     */
    protected $order = 0;

    /**
     * @ORM\OneToMany(targetEntity="ModDependency", mappedBy="mod")
     *
     * The dependencies of the mod.
     * @var Collection|ModDependency[]
     */
    protected $dependencies;

    /**
     * @ORM\OneToMany(targetEntity="ModCombination", mappedBy="mod")
     *
     * The combinations this mod is the main mod of.
     * @var Collection|ModCombination[]
     */
    protected $combinations;

    /**
     * Initializes the entity.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        $this->combinations = new ArrayCollection();
        $this->dependencies = new ArrayCollection();
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
     * Sets the name of the mod.
     * @param string $name
     * @return $this Implementing fluent interface.
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the mod.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the author of the mod.
     * @param string $author
     * @return $this Implementing fluent interface.
     */
    public function setAuthor(string $author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Returns the author of the mod.
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Sets the current version of the mod that has been imported.
     * @param string $currentVersion
     * @return $this Implementing fluent interface.
     */
    public function setCurrentVersion(string $currentVersion)
    {
        $this->currentVersion = $currentVersion;
        return $this;
    }

    /**
     * Returns the current version of the mod that has been imported.
     * @return string
     */
    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    /**
     * Sets the order position of the mod, 1 being the base mod.
     * @param int $order
     * @return $this Implementing fluent interface.
     */
    public function setOrder(int $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Returns the order position of the mod, 1 being the base mod.
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * Returns the dependencies of the mod.
     * @return Collection|ModDependency[]
     */
    public function getDependencies(): Collection
    {
        return $this->dependencies;
    }

    /**
     * Returns the combinations this mod is the main mod of.
     * @return Collection|ModCombination[]
     */
    public function getCombinations(): Collection
    {
        return $this->combinations;
    }
}