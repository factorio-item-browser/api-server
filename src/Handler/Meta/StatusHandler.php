<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Meta;

use FactorioItemBrowser\Api\Client\Response\Meta\StatusResponse;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for the status request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class StatusHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Combination $combination */
        $combination = $request->getAttribute(Combination::class);

        $response = new StatusResponse();
        $response->dataVersion = 1;
        $response->importTime = $combination->getImportTime();
        return new ClientResponse($response);
    }
}
