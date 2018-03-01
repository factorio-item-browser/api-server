<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use FactorioItemBrowser\Api\Client\Entity\Mod as ClientMod;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
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
     * The database translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Initializes the auth handler.
     * @param ModService $modService
     * @param TranslationService $translationService
     */
    public function __construct(ModService $modService, TranslationService $translationService)
    {
        $this->modService = $modService;
        $this->translationService = $translationService;
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $enabledModNames = $this->modService->getEnabledModNames();
        $mods = [];
        foreach ($this->modService->getAllMods() as $databaseMod) {
            $clientMod = new ClientMod();
            $clientMod->setName($databaseMod->getName())
                      ->setAuthor($databaseMod->getAuthor())
                      ->setVersion($databaseMod->getCurrentVersion())
                      ->setIsEnabled(in_array($databaseMod->getName(), $enabledModNames));

            $this->translationService->addEntityToTranslate($clientMod);
            $mods[] = $clientMod;
        }
        $this->translationService->translateEntities(false);

        /* @var ClientMod[] $mods */
        foreach ($mods as $index => $mod) {
            $mods[$index] = $mod->writeData();
        }
        return new JsonResponse([
            'mods' => $mods
        ]);
    }
}