<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Auth;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandler;
use PHPUnit\Framework\TestCase;
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
     * Tests the createInputFilter method.
     * @covers ::createInputFilter
     */
    public function testCreateInputFilter()
    {
        $expectedFilters = [
            'agent',
            'accessKey',
            'enabledModNames'
        ];

        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new AuthHandler('abc', ['def' => 'ghi'], $modService);

        $result = $this->invokeMethod($handler, 'createInputFilter');
        $this->assertInstanceOf(InputFilter::class, $result);
        /* @var InputFilter $result */
        foreach ($expectedFilters as $filter) {
            $this->assertInstanceOf(InputInterface::class, $result->get($filter));
        }
    }

    /**
     * Tests the createToken method.
     * @covers ::createToken
     */
    public function testCreateToken()
    {
        $agent = 'abc';
        $enabledModCombinationIds = [42, 1337];
        $allowImport = true;

        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new AuthHandler('abc', ['def' => 'ghi'], $modService);
        $this->injectProperty($handler, 'authorizationKey', 'def');
        
        $result = $this->invokeMethod($handler, 'createToken', $agent, $enabledModCombinationIds, $allowImport);
        $this->assertInternalType('string', $result);
    }
}
