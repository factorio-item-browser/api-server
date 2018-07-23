<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Client\Entity\EntityInterface;
use FactorioItemBrowser\Api\Server\Exception\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\InputFilter\InputFilter;

/**
 * The abstract class of the request handlers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractRequestHandler implements RequestHandlerInterface
{
    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = $this->createInputFilter();
        $inputFilter->setData((array) $request->getParsedBody());
        if (!$inputFilter->isValid()) {
            throw new ValidationException($inputFilter->getMessages());
        }

        $responseData = $this->handleRequest(new DataContainer($inputFilter->getValues()));
        return new JsonResponse($this->convertDataToArray($responseData));
    }

    /**
     * Creates the input filter to use to verify the request.
     * @return InputFilter
     */
    abstract protected function createInputFilter(): InputFilter;

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     */
    abstract protected function handleRequest(DataContainer $requestData): array;

    /**
     * Converts the response data to an array.
     * @param array $data
     * @return array
     */
    protected function convertDataToArray(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->convertDataToArray($value);
            } elseif ($value instanceof EntityInterface) {
                $result[$key] = $value->writeData();
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
