<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * The entity of the machine database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @ORM\Entity(repositoryClass="FactorioItemBrowser\Api\Server\Database\Repository\MachineRepository")
 * @ORM\Table(name="Machine")
 */
class Machine
{
    /**
     * The number of slots used to represent unlimited.
     */
    private const UNLIMITED_ITEM_SLOTS = 255;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     *
     * The internal id of the machine.
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\Column(name="name")
     *
     * The name of the machine.
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="ModCombination", mappedBy="machines")
     *
     * The mod combinations which are adding the machine.
     * @var Collection|ModCombination[]
     */
    protected $modCombinations;

    /**
     * @ORM\ManyToMany(targetEntity="CraftingCategory", inversedBy="machines")
     * @ORM\JoinTable(name="MachineXCraftingCategory",
     *     joinColumns={@ORM\JoinColumn(name="machineId", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="craftingCategoryId", referencedColumnName="id")}
     * )
     *
     * The crafting categories supported by the machine.
     * @var Collection|CraftingCategory[]
     */
    protected $craftingCategories;

    /**
     * @ORM\Column(name="craftingSpeed", type="integer")
     *
     * The crafting speed of the machine.
     * @var int
     */
    protected $craftingSpeed = 1;

    /**
     * @ORM\Column(name="numberOfItemSlots", type="integer")
     *
     * The number of item slots available in the machine.
     * @var int
     */
    protected $numberOfItemSlots = 0;

    /**
     * @ORM\Column(name="numberOfFluidInputSlots", type="integer")
     *
     * The number of fluid input slots available in the machine.
     * @var int
     */
    protected $numberOfFluidInputSlots = 0;

    /**
     * @ORM\Column(name="numberOfFluidOutputSlots", type="integer")
     *
     * The number of fluid output slots available in the machine.
     * @var int
     */
    protected $numberOfFluidOutputSlots = 0;

    /**
     * @ORM\Column(name="numberOfModuleSlots", type="integer")
     *
     * The number of module slots available in the machine.
     * @var int
     */
    protected $numberOfModuleSlots = 0;

    /**
     * @ORM\Column(name="energyUsage", type="integer")
     *
     * The energy usage of the machine, in watt.
     * @var int
     */
    protected $energyUsage = 0;

    /**
     * Initializes the entity.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->modCombinations = new ArrayCollection();
        $this->craftingCategories = new ArrayCollection();
    }

    /**
     * Sets the internal id of the machine.
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the internal id of the machine.
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets the name of the machine.
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the machine.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the mod combinations which are adding the machine.
     * @return Collection|ModCombination[]
     */
    public function getModCombinations(): Collection
    {
        return $this->modCombinations;
    }

    /**
     * Returns the crafting categories supported by the machine.
     * @return Collection|CraftingCategory[]
     */
    public function getCraftingCategories(): Collection
    {
        return $this->craftingCategories;
    }

    /**
     * Sets the crafting speed of the machine.
     * @param float $craftingSpeed
     * @return $this
     */
    public function setCraftingSpeed(float $craftingSpeed)
    {
        $this->craftingSpeed = intval($craftingSpeed * 1000);
        return $this;
    }

    /**
     * Returns the crafting speed of the machine.
     * @return float
     */
    public function getCraftingSpeed(): float
    {
        return $this->craftingSpeed / 1000;
    }

    /**
     * Sets the number of item slots available in the machine, or -1 if unlimited.
     * @param int $numberOfItemSlots
     * @return $this
     */
    public function setNumberOfItemSlots(int $numberOfItemSlots)
    {
        $this->numberOfItemSlots = ($numberOfItemSlots === -1) ? self::UNLIMITED_ITEM_SLOTS : $numberOfItemSlots;
        return $this;
    }

    /**
     * Returns the number of item slots available in the machine, or -1 if unlimited.
     * @return int
     */
    public function getNumberOfItemSlots(): int
    {
        return ($this->numberOfItemSlots === self::UNLIMITED_ITEM_SLOTS) ? -1 : $this->numberOfItemSlots;
    }

    /**
     * Sets the number of fluid input slots available in the machine.
     * @param int $numberOfFluidInputSlots
     * @return $this
     */
    public function setNumberOfFluidInputSlots(int $numberOfFluidInputSlots)
    {
        $this->numberOfFluidInputSlots = $numberOfFluidInputSlots;
        return $this;
    }

    /**
     * Returns the number of fluid input slots available in the machine.
     * @return int
     */
    public function getNumberOfFluidInputSlots(): int
    {
        return $this->numberOfFluidInputSlots;
    }

    /**
     * Sets the number of fluid output slots available in the machine.
     * @param int $numberOfFluidOutputSlots
     * @return $this
     */
    public function setNumberOfFluidOutputSlots(int $numberOfFluidOutputSlots)
    {
        $this->numberOfFluidOutputSlots = $numberOfFluidOutputSlots;
        return $this;
    }

    /**
     * Returns the number of fluid output slots available in the machine.
     * @return int
     */
    public function getNumberOfFluidOutputSlots(): int
    {
        return $this->numberOfFluidOutputSlots;
    }

    /**
     * Sets the number of module slots available in the machine.
     * @param int $numberOfModuleSlots
     * @return $this
     */
    public function setNumberOfModuleSlots(int $numberOfModuleSlots)
    {
        $this->numberOfModuleSlots = $numberOfModuleSlots;
        return $this;
    }

    /**
     * Returns the number of module slots available in the machine.
     * @return int
     */
    public function getNumberOfModuleSlots(): int
    {
        return $this->numberOfModuleSlots;
    }

    /**
     * Sets the energy usage of the machine, in watt.
     * @param int $energyUsage
     * @return $this
     */
    public function setEnergyUsage(int $energyUsage)
    {
        $this->energyUsage = $energyUsage;
        return $this;
    }

    /**
     * Returns the energy usage of the machine, in watt.
     * @return int
     */
    public function getEnergyUsage(): int
    {
        return $this->energyUsage;
    }
}