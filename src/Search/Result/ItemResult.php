<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Result;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemResult extends AbstractResult
{
    /**
     * The ID of the entity.
     * @var int
     */
    protected $id = 0;

    /**
     * The type of the entity.
     * @var string
     */
    protected $type = '';

    /**
     * Sets the ID of the entity.
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the ID of the entity.
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets the type of the entity.
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type of the search result.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Merges the specified result into the current one.
     * @param AbstractResult $result
     * @return $this
     */
    public function merge(AbstractResult $result)
    {
        parent::merge($result);
        if ($result instanceof ItemResult && $this->id === 0) {
            $this->id = $result->getId();
        }
        return $this;
    }
}