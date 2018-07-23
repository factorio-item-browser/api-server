<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search;

/**
 * The class holding the different parts of the query.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchQuery
{
    /**
     * The raw query string.
     * @var string
     */
    protected $queryString = '';

    /**
     * The keywords of the query.
     * @var array|string[]
     */
    protected $keywords = [];

    /**
     * Initializes the search query.
     * @param string $queryString
     */
    public function __construct(string $queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * Returns the raw query string.
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->queryString;
    }

    /**
     * Adds a keyword to the query.
     * @param string $keyword
     * @return $this
     */
    public function addKeyword(string $keyword)
    {
        $this->keywords[] = $keyword;
        return $this;
    }

    /**
     * Returns the keywords of the query.
     * @return array|string[]
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * Unifies the query.
     * @return $this
     */
    public function unify()
    {
        sort($this->keywords);
        return $this;
    }

    /**
     * Returns a hash representing the query.
     * @return int
     */
    public function getHash(): int
    {
        return crc32((string) json_encode([
            'keywords' => $this->keywords
        ]));
    }
}
