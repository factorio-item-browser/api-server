<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Console;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Console\Console;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The PHPUnit test of the Console class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Console\Console
 */
class ConsoleTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked output.
     * @var OutputInterface&MockObject
     */
    protected $output;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->output = $this->createMock(OutputInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $console = new Console($this->output, true);

        $this->assertSame($this->output, $this->extractProperty($console, 'output'));
        $this->assertTrue($this->extractProperty($console, 'isDebug'));
    }

    /**
     * Tests the writeAction method.
     * @covers ::writeAction
     */
    public function testWriteAction(): void
    {
        $this->output->expects($this->once())
                     ->method('writeln')
                     ->with($this->identicalTo('> abc...'));

        $console = new Console($this->output, true);
        $console->writeAction('abc');
    }

    /**
     * Tests the writeMessage method.
     * @covers ::writeMessage
     */
    public function testWriteMessage(): void
    {
        $this->output->expects($this->once())
                     ->method('writeln')
                     ->with($this->identicalTo('# abc'));

        $console = new Console($this->output, true);
        $console->writeMessage('abc');
    }

    /**
     * Tests the writeException method.
     * @covers ::writeException
     */
    public function testWriteException(): void
    {
        $exception = new ApiServerException('abc');
        $expectedMessages = ['! ApiServerException: abc'];

        /* @var Console&MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->onlyMethods(['createHorizontalLine', 'writeWithDecoration'])
                        ->setConstructorArgs([$this->output, false])
                        ->getMock();
        $console->expects($this->never())
                ->method('createHorizontalLine');
        $console->expects($this->once())
                ->method('writeWithDecoration')
                ->with($this->identicalTo($expectedMessages), $this->identicalTo('red'), $this->identicalTo('bold'));

        $console->writeException($exception);
    }

    /**
     * Tests the writeException method.
     * @covers ::writeException
     */
    public function testWriteExceptionWithDebug(): void
    {
        $exception = new ApiServerException('abc');
        $expectedMessages1 = ['! ApiServerException: abc'];
        $expectedMessages2 = ['---', $exception->getTraceAsString(), '---'];

        /* @var Console&MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->onlyMethods(['createHorizontalLine', 'writeWithDecoration'])
                        ->setConstructorArgs([$this->output, true])
                        ->getMock();
        $console->expects($this->exactly(2))
                ->method('createHorizontalLine')
                ->with('-')
                ->willReturn('---');
        $console->expects($this->exactly(2))
                ->method('writeWithDecoration')
                ->withConsecutive(
                    [$this->identicalTo($expectedMessages1), $this->identicalTo('red'), $this->identicalTo('bold')],
                    [$this->identicalTo($expectedMessages2), $this->identicalTo('red'), $this->identicalTo('')]
                );

        $console->writeException($exception);
    }

    /**
     * Provides the data for the writeWithDecoration test.
     * @return array<mixed>
     */
    public function provideWriteWithDecoration(): array
    {
        return [
            ['foo', 'bar', 'fg=foo;options=bar'],
            ['foo', '', 'fg=foo'],
            ['', 'bar', 'options=bar'],
        ];
    }

    /**
     * Tests the writeWithDecoration method.
     * @param string $color
     * @param string $options
     * @param string $expectedFormatString
     * @throws ReflectionException
     * @covers ::writeWithDecoration
     * @dataProvider provideWriteWithDecoration
     */
    public function testWriteWithDecoration(string $color, string $options, string $expectedFormatString): void
    {
        $messages = ['abc', 'def', 'ghi'];
        $expectedMessages = ["<{$expectedFormatString}>abc", 'def', 'ghi</>'];

        $this->output->expects($this->once())
                     ->method('writeln')
                     ->with($this->identicalTo($expectedMessages));

        $console = new Console($this->output, true);
        $this->invokeMethod($console, 'writeWithDecoration', $messages, $color, $options);
    }

    /**
     * Tests the writeWithDecoration method.
     * @throws ReflectionException
     * @covers ::writeWithDecoration
     */
    public function testWriteWithDecorationWithoutFormats(): void
    {
        $messages = ['abc', 'def', 'ghi'];

        $this->output->expects($this->once())
                     ->method('writeln')
                     ->with($this->identicalTo($messages));

        $console = new Console($this->output, true);
        $this->invokeMethod($console, 'writeWithDecoration', $messages);
    }

    /**
     * Tests the createHorizontalLine method.
     * @throws ReflectionException
     * @covers ::createHorizontalLine
     */
    public function testCreateHorizontalLine(): void
    {
        $character = '-';
        $expectedResult = '--------------------------------------------------------------------------------';

        $console = new Console($this->output, true);
        $result = $this->invokeMethod($console, 'createHorizontalLine', $character);

        $this->assertSame($expectedResult, $result);
    }
}
