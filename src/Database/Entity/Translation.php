<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * The entity class of the Translation database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @ORM\Entity(repositoryClass="FactorioItemBrowser\Api\Server\Database\Repository\TranslationRepository")
 * @ORM\Table(name="Translation")
 */
class Translation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     *
     * The internal id of the translation.
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ModCombination", inversedBy="translations")
     * @ORM\JoinColumn(name="modCombinationId", referencedColumnName="id")
     *
     * The mod combination providing the translation.
     * @var ModCombination
     */
    protected $modCombination;

    /**
     * @ORM\Column(name="locale")
     *
     * The locale of the translation.
     * @var string
     */
    protected $locale = '';

    /**
     * @ORM\Column(name="type")
     *
     * The type of the translation.
     * @var string
     */
    protected $type = '';

    /**
     * @ORM\Column(name="name")
     *
     * The name of the translation.
     * @var string
     */
    protected $name = '';

    /**
     * @ORM\Column(name="value")
     *
     * The actual translation.
     * @var string
     */
    protected $value = '';

    /**
     * @ORM\Column(name="description")
     *
     * The translated description.
     * @var string
     */
    protected $description = '';

    /**
     * @ORM\Column(name="isDuplicatedByRecipe", type="boolean")
     *
     * Whether this translation is duplicated by the recipe.
     * @var bool
     */
    protected $isDuplicatedByRecipe = false;

    /**
     * @ORM\Column(name="isDuplicatedByMachine", type="boolean")
     *
     * Whether this translation is duplicated by the machine.
     * @var bool
     */
    protected $isDuplicatedByMachine = false;

    /**
     * Sets the internal id of the translation.
     * @param int $id
     * @return $this Implementing fluent interface.
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the internal id of the translation.
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets the mod combination providing the translation.
     * @param ModCombination $modCombination
     * @return $this Implementing fluent interface.
     */
    public function setModCombination(ModCombination $modCombination)
    {
        $this->modCombination = $modCombination;
        return $this;
    }

    /**
     * Returns the mod combination providing the translation.
     * @return ModCombination
     */
    public function getModCombination(): ModCombination
    {
        return $this->modCombination;
    }

    /**
     * Sets the locale of the translation.
     * @param string $locale
     * @return $this Implementing fluent interface.
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns the locale of the translation.
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets the type of the translation.
     * @param string $type
     * @return $this Implementing fluent interface.
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type of the translation.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the name of the translation.
     * @param string $name
     * @return $this Implementing fluent interface.
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the translation.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the value of the translation.
     * @param string $value
     * @return $this Implementing fluent interface.
     */
    public function setValue(string $value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Returns the value of the translation.
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Sets the translated description.
     * @param string $description
     * @return $this Implementing fluent interface.
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Returns the translated description.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets whether this translation is duplicated by the recipe.
     * @param bool $isDuplicatedByRecipe
     * @return $this Implementing fluent interface.
     */
    public function setIsDuplicatedByRecipe(bool $isDuplicatedByRecipe)
    {
        $this->isDuplicatedByRecipe = $isDuplicatedByRecipe;
        return $this;
    }

    /**
     * Returns whether this translation is duplicated by the recipe.
     * @return bool
     */
    public function getIsDuplicatedByRecipe(): bool
    {
        return $this->isDuplicatedByRecipe;
    }

    /**
     * Sets whether this translation is duplicated by the machine.
     * @param bool $isDuplicatedByMachine
     * @return $this Implementing fluent interface.
     */
    public function setIsDuplicatedByMachine(bool $isDuplicatedByMachine)
    {
        $this->isDuplicatedByMachine = $isDuplicatedByMachine;
        return $this;
    }

    /**
     * Returns whether this translation is duplicated by the machine.
     * @return bool
     */
    public function getIsDuplicatedByMachine(): bool
    {
        return $this->isDuplicatedByMachine;
    }
}
