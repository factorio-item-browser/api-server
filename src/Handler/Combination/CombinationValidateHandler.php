<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Combination;

use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationValidateRequest;
use FactorioItemBrowser\Api\Client\Request\RequestInterface as ClientRequestInterface;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationValidateResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface as ClientResponseInterface;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\CombinationValidationService;

/**
 * The handler of the combination validation request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationValidateHandler extends AbstractRequestHandler
{
    /**
     * The combination validation service.
     * @var CombinationValidationService
     */
    protected $combinationValidationService;

    /**
     * Initializes the handler.
     * @param CombinationValidationService $combinationValidationService
     */
    public function __construct(CombinationValidationService $combinationValidationService)
    {
        $this->combinationValidationService = $combinationValidationService;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return CombinationValidateRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param ClientRequestInterface $clientRequest
     * @return ClientResponseInterface
     * @throws ApiServerException
     * @throws ClientException
     */
    protected function handleRequest($clientRequest): ClientResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();
        $validatedMods = $this->combinationValidationService->validate($authorizationToken->getModNames());

        $response = new CombinationValidateResponse();
        $response->setValidatedMods($validatedMods)
                 ->setIsValid($this->combinationValidationService->areModsValid($validatedMods));

        return $response;
    }
}
