<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use Exception;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\InvalidAuthorizationTokenException;
use Firebase\JWT\JWT;
use Ramsey\Uuid\Uuid;
use stdClass;

/**
 * The service handling the authorization.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AuthorizationService
{
    /**
     * The algorithm to use for the auth token.
     */
    protected const AUTH_TOKEN_ALGORITHM = 'HS256';

    /**
     * The key to use for the authorization.
     * @var string
     */
    protected $authorizationKey;

    /**
     * The lifetime of the authorization token, in seconds.
     * @var int
     */
    protected $authorizationTokenLifetime;

    /**
     * Initializes the helper.
     * @param string $authorizationKey
     * @param int $authorizationTokenLifetime
     */
    public function __construct(string $authorizationKey, int $authorizationTokenLifetime)
    {
        $this->authorizationKey = $authorizationKey;
        $this->authorizationTokenLifetime = $authorizationTokenLifetime;
    }

    /**
     * Encrypts the specified token.
     * @param AuthorizationToken $token
     * @return string
     */
    public function serializeToken(AuthorizationToken $token): string
    {
        return JWT::encode(
            $this->getTokenData($token),
            $this->authorizationKey,
            self::AUTH_TOKEN_ALGORITHM
        );
    }

    /**
     * Returns the token data to use.
     * @param AuthorizationToken $token
     * @return array<mixed>
     */
    protected function getTokenData(AuthorizationToken $token): array
    {
        return [
            'exp' => time() + $this->authorizationTokenLifetime,
            'agt' => $token->getAgentName(),
            'cmb' => $token->getCombinationId()->toString(),
            'mds' => $token->getModNames(),
            'avl' => $token->getIsDataAvailable() ? 1 : 0,
        ];
    }

    /**
     * Decrypts the specified token.
     * @param string $serializedToken
     * @return AuthorizationToken
     * @throws InvalidAuthorizationTokenException
     */
    public function deserializeToken(string $serializedToken): AuthorizationToken
    {
        $rawToken = $this->decodeSerializedToken($serializedToken);

        $result = new AuthorizationToken();
        $result->setAgentName($rawToken->agt)
               ->setCombinationId(Uuid::fromString($rawToken->cmb))
               ->setModNames($rawToken->mds)
               ->setIsDataAvailable($rawToken->avl === 1);

        return $result;
    }

    /**
     * Decodes the serialized token.
     * @param string $serializedToken
     * @return stdClass
     * @throws InvalidAuthorizationTokenException
     */
    protected function decodeSerializedToken(string $serializedToken): stdClass
    {
        try {
            $result = JWT::decode($serializedToken, $this->authorizationKey, [self::AUTH_TOKEN_ALGORITHM]);
        } catch (Exception $e) {
            throw new InvalidAuthorizationTokenException($e);
        }
        return $result;
    }
}
