<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search;

/**
 * The class parsing a query string to a search query instance.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchQueryParser
{
    /**
     * Parses the specified query string.
     * @param string $queryString
     * @return SearchQuery
     */
    public function parse(string $queryString): SearchQuery
    {
        $result = new SearchQuery($queryString);
        foreach (explode(' ', $queryString) as $keyword) {
            $keyword = strtolower(trim($keyword));
            if (strlen($keyword) >= 2) {
                $result->addKeyword($keyword);
            }
        }

        $result->unify();
        return $result;
    }
}
