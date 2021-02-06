<?php

// phpcs:ignoreFile

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210206102029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Changed resultData of CachedSearchResult from TEXT to BLOB.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('TRUNCATE CachedSearchResult');
        $this->addSql(<<<EOT
            ALTER TABLE CachedSearchResult
            CHANGE resultData resultData LONGBLOB NOT NULL COMMENT 'The result data of the search.'
        EOT);
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('TRUNCATE CachedSearchResult');
        $this->addSql(<<<EOT
            ALTER TABLE CachedSearchResult
            CHANGE resultData resultData LONGTEXT NOT NULL COMMENT 'The result data of the search.'
        EOT);
    }
}
