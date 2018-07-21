<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use Zend\InputFilter\InputFilter;

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
        return [
            'numberOfAvailableMods' => $this->modService->getNumberOfAvailableMods(),
            'numberOfEnabledMods' => $this->modService->getNumberOfEnabledMods()
        ];
    }
}
