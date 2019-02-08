<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod as ClientMod;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use Zend\InputFilter\InputFilter;

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
     * The database translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Initializes the auth handler.
     * @param MapperManagerInterface $mapperManager
     * @param ModService $modService
     * @param TranslationService $translationService
     */
    public function __construct(
        MapperManagerInterface $mapperManager,
        ModService $modService,
        TranslationService $translationService
    ) {
        $this->mapperManager = $mapperManager;
        $this->modService = $modService;
        $this->translationService = $translationService;
    }

    /**
     * Creates the input filter to use to verify the request.
     * @return InputFilter
     */
    protected function createInputFilter(): InputFilter
    {
        return new InputFilter();
    }

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     * @throws MapperException
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $enabledModNames = $this->modService->getEnabledModNames();
        $clientMods = [];
        foreach ($this->modService->getAllMods() as $databaseMod) {
            $clientMod = new ClientMod();
            $this->mapperManager->map($databaseMod, $clientMod);
            $clientMod->setIsEnabled(in_array($databaseMod->getName(), $enabledModNames, true));

            $clientMods[] = $clientMod;
        }

        $this->translationService->translateEntities();
        return [
            'mods' => $clientMods
        ];
    }
}
