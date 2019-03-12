<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod as ClientMod;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Request\RequestInterface;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
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
     * The database mod service.
     * @var ModService
     */
    protected $modService;

    /**
     * Initializes the auth handler.
     * @param MapperManagerInterface $mapperManager
     * @param ModService $modService
     */
    public function __construct(
        MapperManagerInterface $mapperManager,
        ModService $modService
    ) {
        $this->mapperManager = $mapperManager;
        $this->modService = $modService;
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
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws MapperException
     */
    protected function handleRequest(RequestInterface $request): ResponseInterface
    {
        $response = new ModListResponse();

        $enabledModNames = $this->modService->getEnabledModNames();
        foreach ($this->modService->getAllMods() as $databaseMod) {
            $isEnabled = in_array($databaseMod->getName(), $enabledModNames, true);
            $response->addMod($this->createClientMod($databaseMod, $isEnabled));
        }

        return $response;
    }

    /**
     * Creates the client mod entity from the database mod.
     * @param DatabaseMod $databaseMod
     * @param bool $isEnabled
     * @return ClientMod
     * @throws MapperException
     */
    protected function createClientMod(DatabaseMod $databaseMod, bool $isEnabled): ClientMod
    {
        $result = new ClientMod();
        $this->mapperManager->map($databaseMod, $result);
        $result->setIsEnabled($isEnabled);
        return $result;
    }
}
