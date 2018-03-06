<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use BluePsyduck\Common\Data\DataContainer;

/**
 * The handler of the /generic/icon request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class GenericIconHandler extends AbstractGenericHandler
{

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $namesByTypes = $this->getEntityNamesByType($requestData);

        return [
            'icons' => [],
        ];
    }
}