<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Exception\ValidationException;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

/**
 * The handler of the /auth request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AuthHandler implements RequestHandlerInterface
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
        $inputFilter = $this->createInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            throw new ValidationException($inputFilter->getMessages());
        }


        $key = 'wuppdi'; // @todo Read encryption key from config.

        $this->modService->setEnabledCombinationsByModNames($inputFilter->getValue('enabledModNames'));
        $token = [
            'iat' => time(),
            'exp' => time() + 86400,
            'agt' => (string) $inputFilter->getValue('agent'),
            'mds' => $this->modService->getEnabledModCombinationIds()
        ];

        return new JsonResponse([
            'authorizationToken' => JWT::encode($token, $key)
        ]);
    }

    /**
     * Creates the input filter to use for the request.
     * @return InputFilter
     */
    protected function createInputFilter()
    {
        $inputFilter = new InputFilter();
        $inputFilter
            ->add([
                'name' => 'agent',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ])
            ->add([
                'name' => 'accessKey',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ])
            ->add([
                'name' => 'enabledModNames',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ]);
        return $inputFilter;
    }
}