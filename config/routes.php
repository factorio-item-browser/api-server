<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->post('/auth', Handler\Auth\AuthHandler::class, 'auth');
    $app->post('/generic/details', Handler\Generic\GenericDetailsHandler::class, 'generic.details');
    $app->post('/mod/list', Handler\Mod\ModListHandler::class, 'mod.list');
    $app->post('/recipe/details', Handler\Recipe\RecipeDetailsHandler::class, 'recipe.details');
};
