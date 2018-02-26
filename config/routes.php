<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->post('/auth', Handler\AuthHandler::class, 'auth');
    $app->post('/mod/list', Handler\ModListHandler::class, 'mod.list');
};
