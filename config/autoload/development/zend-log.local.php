<?php

declare(strict_types=1);

/**
 * The configuration of the Zend log.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use Zend\Log\Logger;
use Zend\Log\LoggerInterface;
use Zend\Log\Writer\Stream;

return [
    'log' => [
        LoggerInterface::class . ' $errorLogger' => [
            'writers' => [
                [
                    'name' => Stream::class,
                    'priority' => Logger::ERR,
                    'options' => [
                        'stream' => 'php://stderr',
                    ],
                ],
            ],
        ],
    ],
];
