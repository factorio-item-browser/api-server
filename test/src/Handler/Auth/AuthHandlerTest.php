<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Auth;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandler;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputInterface;

/**
 * The PHPUnit test of the AuthHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandler
 */
class AuthHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $agents = ['def' => 'ghi'];

        /* @var AuthorizationService|MockObject $authorizationService */
        $authorizationService = $this->createMock(AuthorizationService::class);
        /* @var ModService|MockObject $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new AuthHandler($authorizationService, $agents, $modService);
        $this->assertSame($authorizationService, $this->extractProperty($handler, 'authorizationService'));
        $this->assertSame($agents, $this->extractProperty($handler, 'agents'));
        $this->assertSame($modService, $this->extractProperty($handler, 'modService'));
    }

    /**
     * Tests the createInputFilter method.
     * @throws ReflectionException
     * @covers ::createInputFilter
     */
    public function testCreateInputFilter(): void
    {
        $expectedFilters = [
            'agent',
            'accessKey',
            'enabledModNames'
        ];

        /* @var AuthorizationService|MockObject $authorizationService */
        $authorizationService = $this->createMock(AuthorizationService::class);
        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new AuthHandler($authorizationService, ['def' => 'ghi'], $modService);
        $result = $this->invokeMethod($handler, 'createInputFilter');

        /* @var InputFilter $result */
        foreach ($expectedFilters as $filter) {
            $this->assertInstanceOf(InputInterface::class, $result->get($filter));
        }
    }
}
