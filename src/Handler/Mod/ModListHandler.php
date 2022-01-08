<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Api\Client\Transfer\Mod as ClientMod;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler of the /mod/list request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModListHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly MapperManagerInterface $mapperManager,
        private readonly ModRepository $modRepository
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ModListRequest $clientRequest */
        $clientRequest = $request->getParsedBody();
        $mods = $this->modRepository->findByCombinationId(Uuid::fromString($clientRequest->combinationId));

        $response = new ModListResponse();
        $response->mods = array_map(function (DatabaseMod $mod): ClientMod {
            return $this->mapperManager->map($mod, new ClientMod());
        }, $mods);

        return new ClientResponse($response);
    }
}
