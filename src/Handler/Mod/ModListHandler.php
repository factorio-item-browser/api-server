<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Mapper\ModMapper;
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
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $enabledModNames = $this->modService->getEnabledModNames();
        $mods = [];
        foreach ($this->modService->getAllMods() as $databaseMod) {
            $clientMod = ModMapper::mapDatabaseItemToClientItem($databaseMod, $this->translationService);
            $clientMod->setIsEnabled(in_array($databaseMod->getName(), $enabledModNames));
            $mods[] = $clientMod;
        }
        $this->translationService->translateEntities();

        return [
            'mods' => $mods
        ];
    }
}