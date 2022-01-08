<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Command;

use FactorioItemBrowser\Api\Search\SearchCacheClearInterface;
use FactorioItemBrowser\Api\Server\Constant\CommandName;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The command for cleaning the caches.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CleanCacheCommand extends Command
{
    public function __construct(
        private readonly SearchCacheClearInterface $searchCacheClear,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName(CommandName::CLEAN_CACHE);
        $this->setDescription('Cleans the caches from already out-dated entries.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->searchCacheClear->clearExpiredResults();
        return 0;
    }
}
