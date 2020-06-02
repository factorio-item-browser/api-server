<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Console;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * The wrapper class for the actual console.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Console
{
    protected OutputInterface $output;
    protected bool $isDebug;

    public function __construct(OutputInterface $output, bool $isDebug)
    {
        $this->output = $output;
        $this->isDebug = $isDebug;
    }

    public function writeAction(string $action): void
    {
        $this->output->writeln('> ' . $action . '...');
    }

    public function writeMessage(string $message): void
    {
        $this->output->writeln('# ' . $message);
    }

    public function writeException(Throwable $e): void
    {
        $this->writeWithDecoration([
            sprintf('! %s: %s', substr((string) strrchr(get_class($e), '\\'), 1), $e->getMessage()),
        ], 'red', 'bold');

        if ($this->isDebug) {
            $this->writeWithDecoration([
                $this->createHorizontalLine('-'),
                $e->getTraceAsString(),
                $this->createHorizontalLine('-'),
            ], 'red');
        }
    }

    /**
     * @param array|string[] $messages
     * @param string $color
     * @param string $options
     */
    protected function writeWithDecoration(array $messages, string $color = '', string $options = ''): void
    {
        $messages = array_values(array_map([OutputFormatter::class, 'escape'], $messages));

        $formats = [];
        if ($color !== '') {
            $formats[] = "fg={$color}";
        }
        if ($options !== '') {
            $formats[] = "options={$options}";
        }
        $formatString = implode(';', $formats);
        if ($formatString !== '') {
            $messages[0] = "<{$formatString}>{$messages[0]}";
            $messages[count($messages) - 1] .= '</>';
        }

        $this->output->writeln($messages);
    }

    protected function createHorizontalLine(string $character): string
    {
        return str_pad('', 80, $character);
    }
}
