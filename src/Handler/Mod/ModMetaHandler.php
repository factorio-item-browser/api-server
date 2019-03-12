<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use FactorioItemBrowser\Api\Client\Request\Mod\ModMetaRequest;
use FactorioItemBrowser\Api\Client\Request\RequestInterface;
use FactorioItemBrowser\Api\Client\Response\Mod\ModMetaResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;

/**
 * The handler of the /mod/meta request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModMetaHandler extends AbstractRequestHandler
{
    /**
     * The database service of the mods.
     * @var ModService
     */
    protected $modService;

    /**
     * Initializes the handler.
     * @param ModService $modService
     */
    public function __construct(ModService $modService)
    {
        $this->modService = $modService;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return ModMetaRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function handleRequest(RequestInterface $request): ResponseInterface
    {
        $response = new ModMetaResponse();
        $response->setNumberOfAvailableMods($this->modService->getNumberOfAvailableMods())
                 ->setNumberOfEnabledMods($this->modService->getNumberOfEnabledMods());
        return $response;
    }
}
