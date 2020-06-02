<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Command;

use BluePsyduck\MapperManager\Exception\MapperException;
use DateTime;
use Exception;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Console\Console;
use FactorioItemBrowser\Api\Server\Constant\CommandName;
use FactorioItemBrowser\Api\Server\Service\CombinationUpdateService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The command for checking for combinations which most likely need an update.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateCombinationsCommand extends Command
{
    protected CombinationRepository $combinationRepository;
    protected CombinationUpdateService $combinationUpdateService;
    protected Console $console;
    protected string $lastUsageInterval;
    protected int $maxNumberOfUpdates;

    public function __construct(
        CombinationRepository $combinationRepository,
        CombinationUpdateService $combinationUpdateService,
        Console $console,
        string $lastUsageInterval,
        int $maxNumberOfUpdates
    ) {
        parent::__construct();

        $this->combinationRepository = $combinationRepository;
        $this->combinationUpdateService = $combinationUpdateService;
        $this->console = $console;
        $this->lastUsageInterval = $lastUsageInterval;
        $this->maxNumberOfUpdates = $maxNumberOfUpdates;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName(CommandName::UPDATE_COMBINATIONS);
        $this->setDescription('Checks if any combinations need an update.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->console->writeAction(
                sprintf('Fetching combinations with usage within %s', $this->lastUsageInterval)
            );
            $combinations = $this->combinationRepository->findByLastUsageTime(new DateTime($this->lastUsageInterval));
            $this->console->writeMessage(sprintf('Found %d combinations of interest.', count($combinations)));

            $this->console->writeAction('Fetching meta data from mod portal');
            $combinationUpdates = [];
            foreach ($combinations as $combination) {
                $combinationUpdate = $this->combinationUpdateService->checkCombination($combination);
                if ($combinationUpdate !== null) {
                    $combinationUpdates[] = $combinationUpdate;
                }
            }
            $this->console->writeMessage(
                sprintf('Found %d combinations requiring an update.', count($combinationUpdates))
            );

            $this->console->writeAction('Requesting export status');
            $this->combinationUpdateService->requestExportStatus($combinationUpdates);

            $combinationUpdates = $this->combinationUpdateService->filter($combinationUpdates);
            $this->console->writeMessage(
                sprintf('Kept %d combinations to trigger an update for.', count($combinationUpdates))
            );

            $combinationUpdates = $this->combinationUpdateService->sort($combinationUpdates);
            $combinationUpdates = array_slice($combinationUpdates, 0, $this->maxNumberOfUpdates);

            $this->console->writeAction(sprintf('Triggering %d exports', count($combinationUpdates)));
            $this->combinationUpdateService->triggerExports($combinationUpdates);

            return 0;
        } catch (Exception | MapperException $e) {
            $this->console->writeException($e);
            return 1;
        }
    }
}
