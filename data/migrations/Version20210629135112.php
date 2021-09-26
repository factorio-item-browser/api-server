<?php

// phpcs:ignoreFile

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210629135112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added update related columns to the Combination table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOT
            ALTER TABLE Combination 
                ADD lastUpdateCheckTime TIMESTAMP NULL DEFAULT NULL COMMENT 'The last time this combination was checked for an update.(DC2Type:timestamp)', 
                ADD lastUpdateHash BINARY(16) NULL DEFAULT NULL COMMENT 'The hash representing the mod versions used when the combination was last updated.(DC2Type:uuid_binary)'
        EOT);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<EOT
            ALTER TABLE Combination 
                DROP lastUpdateCheckTime,
                DROP lastUpdateHash
        EOT);
    }
}
