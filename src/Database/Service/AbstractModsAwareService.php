<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Data\DataInterface;
use FactorioItemBrowser\Api\Database\Helper\DataHelper;

/**
 * The abstract service being aware of the mod service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractModsAwareService extends AbstractDatabaseService
{
    /**
     * The mod database service.
     * @var ModService
     */
    protected $modService;

    /**
     * Initializes the service.
     * @param EntityManager $entityManager
     * @param ModService $modService
     */
    public function __construct(EntityManager $entityManager, ModService $modService)
    {
        parent::__construct($entityManager);
        $this->modService = $modService;
    }

    /**
     * Filters the data using the order column.
     * @param array|DataInterface[] $data
     * @return array|DataInterface[]
     */
    protected function filterData(array $data): array
    {
        $dataHelper = new DataHelper();
        return $dataHelper->filter($data);
    }
}
