<?php

/**
 * The config file used by Doctrine's CLI tools.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Doctrine\Migrations\DependencyFactory;
use Psr\Container\ContainerInterface;

/* @var ContainerInterface $container */
$container = require(__DIR__  . '/container.php');

return $container->get(DependencyFactory::class);
