<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * The entity representing the ModCombination database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @ORM\Entity(repositoryClass="FactorioItemBrowser\Api\Server\Database\Repository\ModCombinationRepository")
 * @ORM\Table(name="ModCombination")
 */
class ModCombination
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     *
     * The id of the mod combination.
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mod", inversedBy="combinations")
     * @ORM\JoinColumn(name="modId", referencedColumnName="id")
     *
     * The main mod.
     * @var Mod|null
     */
    protected $mod;

    /**
     * @ORM\Column(name="optionalModIds", type="flags")
     *
     * The list of the loaded optional mods.
     * @var array|int[]
     */
    protected $optionalModIds = [];

    /**
     * @ORM\Column(name="name")
     *
     * The name of the mod combination.
     * @var string
     */
    protected $name = '';

    /**
     * @ORM\Column(name="flags", type="flags")
     *
     * The flags of the mod combination.
     * @var array|string[]
     */
    protected $flags = [];

    /**
     * @ORM\Column(name="`order`", type="integer")
     *
     * The order of the mod combination.
     * @var int
     */
    protected $order = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Item", inversedBy="modCombinations")
     * @ORM\JoinTable(name="ModCombinationXItem",
     *     joinColumns={@ORM\JoinColumn(name="modCombinationId", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="itemId", referencedColumnName="id")}
     * )
     *
     * The items added by the mod combination.
     * @var Collection|Item[]
     */
    protected $items;

    /**
     * @ORM\ManyToMany(targetEntity="Recipe", inversedBy="modCombinations")
     * @ORM\JoinTable(name="ModCombinationXRecipe",
     *     joinColumns={@ORM\JoinColumn(name="modCombinationId", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="recipeId", referencedColumnName="id")}
     * )
     *
     * The recipes added by the mod combination.
     * @var Collection|Recipe[]
     */
    protected $recipes;

    /**
     * @ORM\OneToMany(targetEntity="Translation", mappedBy="modCombination")
     *
     * The translations added by the mod combination.
     * @var Collection|Translation[]
     */
    protected $translations;

    /**
     * @ORM\OneToMany(targetEntity="Icon", mappedBy="modCombination")
     *
     * The icons used by the mod combination.
     * @var Collection|Icon[]
     */
    protected $icons;

    /**
     * Initializes the combination.
     * @param Mod $mod
     */
    public function __construct(Mod $mod)
    {
        $this->mod = $mod;

        $this->items = new ArrayCollection();
        $this->recipes = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->icons = new ArrayCollection();
    }

    /**
     * Sets the id of the mod combination.
     * @param int $id
     * @return $this Implementing fluent interface.
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the id of the mod combination.
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets the main mod.
     * @param Mod $mod
     * @return $this Implementing fluent interface.
     */
    public function setMod(Mod $mod)
    {
        $this->mod = $mod;
        return $this;
    }

    /**
     * Returns the main mod.
     * @return Mod|null
     */
    public function getMod(): ?Mod
    {
        return $this->mod;
    }

    /**
     * Sets the list of the loaded optional mods.
     * @param array|int[] $optionalModIds
     * @return $this Implementing fluent interface.
     */
    public function setOptionalModIds(array $optionalModIds)
    {
        $this->optionalModIds = $optionalModIds;
        return $this;
    }

    /**
     * Returns the list of the loaded optional mods.
     * @return array|int[]
     */
    public function getOptionalModIds(): array
    {
        return $this->optionalModIds;
    }

    /**
     * Sets the name of the mod combination.
     * @param string $name
     * @return $this Implementing fluent interface.
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the mod combination.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the flags of the mod combination.
     * @param array|string[] $flags
     * @return $this Implementing fluent interface.
     */
    public function setFlags(array $flags)
    {
        $this->flags = $flags;
        return $this;
    }

    /**
     * Returns whether the specified flag is present.
     * @param string $flagName
     * @return bool
     */
    public function hasFlag(string $flagName): bool
    {
        return isset($this->flags[$flagName]);
    }

    /**
     * Sets the order of the mod combination.
     * @param int $order
     * @return $this Implementing fluent interface.
     */
    public function setOrder(int $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Returns the order of the mod combination.
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * Returns the items added by the mod combination.
     * @return Collection|Item[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Returns the recipes added by the mod combination.
     * @return Collection|Recipe[]
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    /**
     * Returns the translations added by the mod combination.
     * @return Collection|Translation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * Returns the icons used by the mod combination.
     * @return Collection|Icon[]
     */
    public function getIcons(): Collection
    {
        return $this->icons;
    }
}