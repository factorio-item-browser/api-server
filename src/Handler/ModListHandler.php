<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * The handler of the /mod/list request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModListHandler implements RequestHandlerInterface
{
    /**
     * The database mod service.
     * @var ModService
     */
    protected $modService;

    /**
     * Initializes the auth handler.
     * @param ModService $modService
     */
    public function __construct(ModService $modService)
    {
        $this->modService = $modService;
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $enabledModNames = $this->modService->getEnabledModNames();

        $preparedMods = [];
        foreach ($this->modService->getAllMods() as $mod) {
            $preparedMods[] = [
                'name' => $mod->getName(),
                'author' => $mod->getAuthor(),
                'version' => $mod->getCurrentVersion(),
                'isEnabled' => in_array($mod->getName(), $enabledModNames)
            ];
        }

        return new JsonResponse([
            'mods' => $preparedMods
        ]);
    }
}