<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200603082746 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Converts database to utf8mb4 and collation to _bin.';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(<<<EOT
            ALTER TABLE CachedSearchResult 
                COLLATE='utf8mb4_bin',
                CHANGE locale locale VARCHAR(5) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The locale used for the search.', 
                CHANGE searchQuery searchQuery TEXT COLLATE 'utf8mb4_general_ci' NOT NULL COMMENT 'The raw query string of the search.', 
                CHANGE resultData resultData LONGTEXT COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The result data of the search.'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Combination
                COLLATE='utf8mb4_bin'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CombinationXItem
                COLLATE='utf8mb4_bin'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CombinationXMachine
                COLLATE='utf8mb4_bin'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CombinationXMod
                COLLATE='utf8mb4_bin'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CombinationXRecipe
                COLLATE='utf8mb4_bin'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CombinationXTranslation
                COLLATE='utf8mb4_bin'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CraftingCategory 
                COLLATE='utf8mb4_bin',
                DROP INDEX UNIQ_9BF267DA5E237E06, 
                CHANGE name name VARCHAR(255) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The name of the crafting category.',
                ADD INDEX IDX_9BF267DA5E237E06 (name)
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Icon 
                COLLATE='utf8mb4_bin',
                CHANGE type type ENUM('mod','item','fluid','machine','recipe') COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The type of the icon\'s prototype.(DC2Type:enum_entity_type)',
                CHANGE name name VARCHAR(255) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The name of the icon''s prototype.'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE IconImage 
                COLLATE='utf8mb4_bin'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Item 
                COLLATE='utf8mb4_bin',
                DROP INDEX UNIQ_BF298A208CDE57295E237E06,
                CHANGE type type ENUM('item','fluid') COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The type of the item.(DC2Type:enum_item_type)',
                CHANGE name name VARCHAR(255) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The unique name of the item.',
                ADD INDEX IDX_BF298A208CDE57295E237E06 (type, name)
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Machine 
                COLLATE='utf8mb4_bin',
                CHANGE name name VARCHAR(255) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The name of the machine.'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE MachineXCraftingCategory 
                COLLATE='utf8mb4_bin'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE `Mod` 
                COLLATE='utf8mb4_bin',
                DROP INDEX UNIQ_2FB915A85E237E06BF1CD3C3,
                CHANGE name name VARCHAR(255) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The name of the mod.', 
                CHANGE version version VARCHAR(16) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The version of the mod.', 
                CHANGE author author VARCHAR(255) COLLATE 'utf8mb4_general_ci' NOT NULL COMMENT 'The author of the mod.'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Recipe 
                COLLATE='utf8mb4_bin',
                CHANGE name name VARCHAR(255) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The name of the recipe.',
                CHANGE mode mode ENUM('normal','expensive') COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The mode of the recipe.(DC2Type:enum_recipe_mode)'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE RecipeIngredient
                COLLATE='utf8mb4_bin'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE RecipeProduct
                COLLATE='utf8mb4_bin'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Translation
                COLLATE='utf8mb4_bin',
                CHANGE locale locale VARCHAR(5) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The locale of the translation.', 
                CHANGE type type ENUM('mod','item','fluid','machine','recipe') COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The type of the translation.(DC2Type:enum_entity_type)',
                CHANGE name name VARCHAR(255) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The name of the translation.', 
                CHANGE value value TEXT COLLATE 'utf8mb4_general_ci' NOT NULL COMMENT 'The actual translation.', 
                CHANGE description description TEXT COLLATE 'utf8mb4_general_ci' NOT NULL COMMENT 'The translated description.'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE _Migrations 
                COLLATE='utf8mb4_bin',
                CHANGE version version VARCHAR(14) COLLATE 'utf8mb4_bin' NOT NULL
        EOT);
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(<<<EOT
            ALTER TABLE CachedSearchResult
                COLLATE='utf8_general_ci', 
                CHANGE locale locale VARCHAR(5) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The locale used for the search.', 
                CHANGE searchQuery searchQuery TEXT COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The raw query string of the search.', 
                CHANGE resultData resultData LONGTEXT COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The result data of the search.'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Combination 
                COLLATE='utf8_general_ci'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CombinationXItem
                COLLATE='utf8_general_ci'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CombinationXMachine 
                COLLATE='utf8_general_ci'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CombinationXMod
                COLLATE='utf8_general_ci'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CombinationXRecipe
                COLLATE='utf8_general_ci'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CombinationXTranslation
                COLLATE='utf8_general_ci'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE CraftingCategory 
                COLLATE='utf8_general_ci',
                DROP INDEX IDX_9BF267DA5E237E06, 
                CHANGE name name VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The name of the crafting category.',
                ADD UNIQUE INDEX UNIQ_9BF267DA5E237E06 (name)
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Icon 
                COLLATE='utf8_general_ci',
                CHANGE type type ENUM('mod','item','fluid','machine','recipe') COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The type of the icon\'s prototype.(DC2Type:enum_entity_type)',
                CHANGE name name VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The name of the icon''s prototype.'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE IconImage COLLATE='utf8_general_ci'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Item 
                COLLATE='utf8_general_ci',
                DROP INDEX IDX_BF298A208CDE57295E237E06,
                CHANGE type type ENUM('item','fluid') COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The type of the item.(DC2Type:enum_item_type)',
                CHANGE name name VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The unique name of the item.',
                ADD UNIQUE INDEX UNIQ_BF298A208CDE57295E237E06 (type, name)
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Machine
                COLLATE='utf8_general_ci',
                CHANGE name name VARCHAR(255) COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The name of the machine.',
                CHANGE energyUsageUnit energyUsageUnit ENUM('W','kW','MW','GW','TW','PW','EW','ZW','YW') COLLATE 'utf8mb4_bin' NOT NULL COMMENT 'The unit of the energy usage.(DC2Type:enum_energy_usage_unit)'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE MachineXCraftingCategory 
                COLLATE='utf8_general_ci'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE `Mod` 
                COLLATE='utf8_general_ci',
                CHANGE name name VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The name of the mod.', 
                CHANGE version version VARCHAR(16) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The version of the mod.', 
                CHANGE author author VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The author of the mod.',
                ADD UNIQUE INDEX UNIQ_2FB915A85E237E06BF1CD3C3 (name, version)
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Recipe 
                COLLATE='utf8_general_ci',
                CHANGE name name VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The name of the recipe.',
                CHANGE mode mode ENUM('normal','expensive') COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The mode of the recipe.(DC2Type:enum_recipe_mode)'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE RecipeIngredient 
                COLLATE='utf8_general_ci'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE RecipeProduct 
                COLLATE='utf8_general_ci'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE Translation
                COLLATE='utf8_general_ci',
                CHANGE locale locale VARCHAR(5) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The locale of the translation.', 
                CHANGE type type ENUM('mod','item','fluid','machine','recipe') COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The type of the translation.(DC2Type:enum_entity_type)',
                CHANGE name name VARCHAR(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The name of the translation.', 
                CHANGE value value TEXT COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The actual translation.', 
                CHANGE description description TEXT COLLATE 'utf8_general_ci' NOT NULL COMMENT 'The translated description.'
        EOT);
        $this->addSql(<<<EOT
            ALTER TABLE _Migrations 
                COLLATE='utf8_general_ci',
                CHANGE version version VARCHAR(14) COLLATE 'utf8_general_ci' NOT NULL
        EOT);
    }
}
