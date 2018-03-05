<?php

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * The entity of the icon database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @ORM\Entity
 * @ORM\Table(name="Icon")
 */
class Icon
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     *
     * The internal id of the icon.
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ModCombination")
     * @ORM\JoinColumn(name="modCombinationId", referencedColumnName="id")
     *
     * The mod combination adding the icon.
     * @var ModCombination
     */
    protected $modCombination;

    /**
     * @ORM\ManyToOne(targetEntity="IconFile")
     * @ORM\JoinColumn(name="iconFileId", referencedColumnName="id")
     *
     * The file of the icon.
     * @var IconFile
     */
    protected $file;

    /**
     * @ORM\Column(name="layerHash")
     *
     * The hash value of the icon layers.
     * @var string
     */
    protected $layerHash = '';

    /**
     * @ORM\Column(name="type")
     *
     * The type of the icon's prototype.
     * @var string
     */
    protected $type = '';

    /**
     * @ORM\Column(name="name")
     *
     * The name of the icons's prototype.
     * @var string
     */
    protected $name = '';

    /**
     * Initializes the entity.
     * @param ModCombination $modCombination
     * @param IconFile $iconFile
     */
    public function __construct(ModCombination $modCombination, IconFile $iconFile)
    {
        $this->modCombination = $modCombination;
        $this->file = $iconFile;
    }

    /**
     * Sets the internal id of the icon.
     * @param int $id
     * @return $this Implementing fluent interface.
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the internal id of the icon.
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets the mod combination adding the icon.
     * @param ModCombination $modCombination
     * @return $this Implementing fluent interface.
     */
    public function setModCombination(ModCombination $modCombination)
    {
        $this->modCombination = $modCombination;
        return $this;
    }

    /**
     * Returns the mod combination adding the icon.
     * @return ModCombination
     */
    public function getModCombination(): ModCombination
    {
        return $this->modCombination;
    }

    /**
     * Sets the file of the icon.
     * @param IconFile $file
     * @return $this Implementing fluent interface.
     */
    public function setFile(IconFile $file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Returns the file of the icon.
     * @return IconFile
     */
    public function getFile(): IconFile
    {
        return $this->file;
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
     * Sets the type of the icon's prototype.
     * @param string $type
     * @return $this Implementing fluent interface.
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type of the icon's prototype.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the name of the icons's prototype.
     * @param string $name
     * @return $this Implementing fluent interface.
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the icons's prototype.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}