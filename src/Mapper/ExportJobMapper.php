<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;

/**
 * The mapper for the export jobs.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportJobMapper implements DynamicMapperInterface
{
    /**
     * Returns whether the mapper supports the combination of source and destination object.
     * @param object $source
     * @param object $destination
     * @return bool
     */
    public function supports($source, $destination): bool
    {
        return $source instanceof Job && $destination instanceof ExportJob;
    }

    /**
     * Maps the source object to the destination one.
     * @param Job $source
     * @param ExportJob $destination
     */
    public function map($source, $destination): void
    {
        $destination->setStatus($source->getStatus())
                    ->setCreationTime($source->getCreationTime())
                    ->setExportTime($source->getExportTime())
                    ->setImportTime($source->getImportTime())
                    ->setErrorMessage($source->getErrorMessage());
    }
}
