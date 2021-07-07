<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Command;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Constant\CommandName;
use FactorioItemBrowser\Api\Server\Service\CombinationUpdateService;
use GuzzleHttp\Promise\Utils;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The command checking if any recently used combination may require an update of its data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TriggerCombinationUpdatesCommand extends Command
{
    private CombinationRepository $combinationRepository;
    private CombinationUpdateService $combinationUpdateService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        CombinationRepository $combinationRepository,
        CombinationUpdateService $combinationUpdateService,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct();

        $this->combinationRepository = $combinationRepository;
        $this->combinationUpdateService = $combinationUpdateService;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName(CommandName::TRIGGER_COMBINATION_UPDATES);
        $this->setDescription('Checks for combinations requiring an update and triggers them.');

        $this->addOption(
            'last-usage',
            '',
            InputOption::VALUE_REQUIRED,
            'The interval of the earliest last usage time the combination must have.',
        );
        $this->addOption(
            'update-check',
            '',
            InputOption::VALUE_REQUIRED,
            'The interval of the latest update check the combination must have.',
        );
        $this->addOption(
            'limit',
            '',
            InputOption::VALUE_REQUIRED,
            'The maximal number of combinations to check for updates.',
        );
        $this->addOption(
            'dry-run',
            '',
            InputOption::VALUE_NONE,
            'Only do a dry-run without actually triggering updates.',
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $intervalLastUsage = new DateInterval(strval($input->getOption('last-usage')));
        $intervalUpdateCheck = new DateInterval(strval($input->getOption('update-check')));
        $limit = intval($input->getOption('limit'));
        $dryRun = boolval($input->getOption('dry-run'));

        $combinations = $this->combinationRepository->findPossibleCombinationsForUpdate(
            (new DateTime('now'))->sub($intervalLastUsage),
            (new DateTime('now'))->sub($intervalUpdateCheck),
            $limit,
        );

        $promises = [];
        foreach ($combinations as $combination) {
            if (!$dryRun) {
                $combination->setLastUpdateCheckTime(new DateTime('now'));
                $this->entityManager->persist($combination);
            }

            $promise = $this->combinationUpdateService->checkCombination($combination)->then(
                function (UuidInterface $updateHash) use ($combination, $dryRun, $output): void {
                    if (!$dryRun) {
                        $combination->setLastUpdateHash($updateHash);
                        $this->combinationUpdateService->triggerUpdate($combination);
                    }
                    $output->writeln(sprintf('%s: Requires update.', $combination->getId()->toString()));
                },
                function (Exception $exception) use ($combination, $output): void {
                    $output->writeln(
                        sprintf('%s: No update: %s', $combination->getId()->toString(), $exception->getMessage()),
                    );
                },
            );

            $promises[] = $promise;
        }
        Utils::settle($promises)->wait();

        if (!$dryRun) {
            $this->entityManager->flush();
        }
        return 0;
    }
}
