<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod as ClientMod;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;

/**
 * The handler of the /mod/list request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModListHandler extends AbstractRequestHandler
{
    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * The mod repository.
     * @var ModRepository
     */
    protected $modRepository;

    /**
     * Initializes the auth handler.
     * @param MapperManagerInterface $mapperManager
     * @param ModRepository $modRepository
     */
    public function __construct(
        MapperManagerInterface $mapperManager,
        ModRepository $modRepository
    ) {
        $this->mapperManager = $mapperManager;
        $this->modRepository = $modRepository;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return ModListRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param ModListRequest $request
     * @return ResponseInterface
     * @throws MapperException
     */
    protected function handleRequest($request): ResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();
        $mods = $this->modRepository->findByCombinationId($authorizationToken->getCombinationId());

        $response = new ModListResponse();
        foreach ($mods as $databaseMod) {
            $response->addMod($this->createClientMod($databaseMod));
        }
        return $response;
    }

    /**
     * Creates the client mod entity from the database mod.
     * @param DatabaseMod $databaseMod
     * @return ClientMod
     * @throws MapperException
     */
    protected function createClientMod(DatabaseMod $databaseMod): ClientMod
    {
        $result = new ClientMod();
        $this->mapperManager->map($databaseMod, $result);
        return $result;
    }
}
