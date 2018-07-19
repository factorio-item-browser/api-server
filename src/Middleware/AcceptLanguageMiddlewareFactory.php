<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the accept language middleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AcceptLanguageMiddlewareFactory implements FactoryInterface
{
    /**
     * Creates the accept language middleware.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return AcceptLanguageMiddleware
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new AcceptLanguageMiddleware($translationService);
    }
}
