<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use FactorioItemBrowser\Api\Client\Request\Mod\ModMetaRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModMetaResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;
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
     * The mod repository.
     * @var ModRepository
     */
    protected $modRepository;

    /**
     * Initializes the handler.
     * @param ModRepository $modRepository
     */
    public function __construct(ModRepository $modRepository)
    {
        $this->modRepository = $modRepository;
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
     * @param ModMetaRequest $request
     * @return ResponseInterface
     */
    protected function handleRequest($request): ResponseInterface
    {
        $enabledModCombinationIds = $this->getAuthorizationToken()->getEnabledModCombinationIds();

        $response = new ModMetaResponse();
        $response->setNumberOfAvailableMods($this->modRepository->count())
                 ->setNumberOfEnabledMods($this->modRepository->count($enabledModCombinationIds));
        return $response;
    }
}
