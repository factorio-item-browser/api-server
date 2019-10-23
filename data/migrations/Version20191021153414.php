<?php declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial setup of the database.
 */
final class Version20191021153414 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Recipe ( id BINARY(16) NOT NULL COMMENT \'The internal id of the recipe.(DC2Type:uuid_binary)\', name VARCHAR(255) NOT NULL COMMENT \'The name of the recipe.\', mode ENUM(\'normal\', \'expensive\') NOT NULL COMMENT \'The mode of the recipe.(DC2Type:enum_recipe_mode)\', craftingTime INT UNSIGNED NOT NULL COMMENT \'The required time in milliseconds to craft the recipe.\', craftingCategoryId BINARY(16) NOT NULL COMMENT \'The internal id of the crafting category.(DC2Type:uuid_binary)\', INDEX IDX_DD24B401DFFAB95E (craftingCategoryId), INDEX IDX_DD24B4015E237E06 (name), PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the recipes to craft the items.\'');
        $this->addSql('CREATE TABLE RecipeIngredient ( recipeId BINARY(16) NOT NULL COMMENT \'The internal id of the recipe.(DC2Type:uuid_binary)\', `order` TINYINT UNSIGNED NOT NULL COMMENT \'The order of the ingredient in the recipe.(DC2Type:tinyint)\', itemId BINARY(16) NOT NULL COMMENT \'The internal id of the item.(DC2Type:uuid_binary)\', amount INT UNSIGNED NOT NULL COMMENT \'The amount required for the recipe.\', INDEX IDX_69908F266DCBA54 (recipeId), INDEX IDX_69908F26B5F8B771 (itemId), PRIMARY KEY(recipeId, `order`) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the ingredients for the recipes.\'');
        $this->addSql('CREATE TABLE `Mod` ( id BINARY(16) NOT NULL COMMENT \'The internal id of the mod.(DC2Type:uuid_binary)\', name VARCHAR(255) NOT NULL COMMENT \'The name of the mod.\', version VARCHAR(16) NOT NULL COMMENT \'The version of the mod.\', author VARCHAR(255) NOT NULL COMMENT \'The author of the mod.\', UNIQUE INDEX UNIQ_2FB915A85E237E06BF1CD3C3 (name, version), PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the mods.\'');
        $this->addSql('CREATE TABLE CachedSearchResult ( combinationId BINARY(16) NOT NULL COMMENT \'The id of the combination used for the search.(DC2Type:uuid_binary)\', locale VARCHAR(5) NOT NULL COMMENT \'The locale used for the search.\', searchHash BINARY(16) NOT NULL COMMENT \'The hash of the search.(DC2Type:uuid_binary)\', searchQuery TEXT NOT NULL COMMENT \'The raw query string of the search.\', resultData LONGTEXT NOT NULL COMMENT \'The result data of the search.\', lastSearchTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'The time when the search result was last used.\', PRIMARY KEY( combinationId, locale, searchHash ) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table caching the search results.\'');
        $this->addSql('CREATE TABLE Translation ( id BINARY(16) NOT NULL COMMENT \'The internal id of the translation.(DC2Type:uuid_binary)\', locale VARCHAR(5) NOT NULL COMMENT \'The locale of the translation.\', type ENUM( \'mod\', \'item\', \'fluid\', \'machine\', \'recipe\' ) NOT NULL COMMENT \'The type of the translation.(DC2Type:enum_entity_type)\', name VARCHAR(255) NOT NULL COMMENT \'The name of the translation.\', value TEXT NOT NULL COMMENT \'The actual translation.\', description TEXT NOT NULL COMMENT \'The translated description.\', isDuplicatedByRecipe TINYINT(1) NOT NULL COMMENT \'Whether this translation is duplicated by the recipe.\', isDuplicatedByMachine TINYINT(1) NOT NULL COMMENT \'Whether this translation is duplicated by the machine.\', INDEX IDX_32F5CAB84180C6988CDE57295E237E06 (locale, type, name), PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the localized translations of the items and recipes etc.\'');
        $this->addSql('CREATE TABLE CraftingCategory ( id BINARY(16) NOT NULL COMMENT \'The internal id of the crafting category.(DC2Type:uuid_binary)\', name VARCHAR(255) NOT NULL COMMENT \'The name of the crafting category.\', UNIQUE INDEX UNIQ_9BF267DA5E237E06 (name), PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the crafting categories.\'');
        $this->addSql('CREATE TABLE Machine ( id BINARY(16) NOT NULL COMMENT \'The internal id of the machine.(DC2Type:uuid_binary)\', name VARCHAR(255) NOT NULL COMMENT \'The name of the machine.\', craftingSpeed INT UNSIGNED NOT NULL COMMENT \'The crafting speed of the machine.\', numberOfItemSlots TINYINT UNSIGNED NOT NULL COMMENT \'The number of item slots available in the machine, or 255 for unlimited.(DC2Type:tinyint)\', numberOfFluidInputSlots TINYINT UNSIGNED NOT NULL COMMENT \'The number of fluid input slots available in the machine.(DC2Type:tinyint)\', numberOfFluidOutputSlots TINYINT UNSIGNED NOT NULL COMMENT \'The number of fluid output slots available in the machine.(DC2Type:tinyint)\', numberOfModuleSlots TINYINT UNSIGNED NOT NULL COMMENT \'The number of module slots available in the machine.(DC2Type:tinyint)\', energyUsage INT UNSIGNED NOT NULL COMMENT \'The energy usage of the machine.\', energyUsageUnit ENUM( \'W\', \'kW\', \'MW\', \'GW\', \'TW\', \'PW\', \'EW\', \'ZW\', \'YW\' ) NOT NULL COMMENT \'The unit of the energy usage.(DC2Type:enum_energy_usage_unit)\', INDEX IDX_DAB8E6185E237E06 (name), PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the crafting machines of the recipes.\'');
        $this->addSql('CREATE TABLE MachineXCraftingCategory ( machineId BINARY(16) NOT NULL COMMENT \'The internal id of the machine.(DC2Type:uuid_binary)\', craftingCategoryId BINARY(16) NOT NULL COMMENT \'The internal id of the crafting category.(DC2Type:uuid_binary)\', INDEX IDX_EE2BD258633EC4FD (machineId), INDEX IDX_EE2BD258DFFAB95E (craftingCategoryId), PRIMARY KEY(machineId, craftingCategoryId) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Combination ( id BINARY(16) NOT NULL COMMENT \'The internal id of the combination.(DC2Type:uuid_binary)\', importTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'The time when the combination was imported.\', lastUsageTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'The time when the combination was last used by a visitor.\', PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the combinations of mods.\'');
        $this->addSql('CREATE TABLE CombinationXMod ( combinationId BINARY(16) NOT NULL COMMENT \'The internal id of the combination.(DC2Type:uuid_binary)\', modId BINARY(16) NOT NULL COMMENT \'The internal id of the mod.(DC2Type:uuid_binary)\', INDEX IDX_C3D0611AFE40C4A7 (combinationId), INDEX IDX_C3D0611AE07F9145 (modId), PRIMARY KEY(combinationId, modId) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CombinationXItem ( combinationId BINARY(16) NOT NULL COMMENT \'The internal id of the combination.(DC2Type:uuid_binary)\', itemId BINARY(16) NOT NULL COMMENT \'The internal id of the item.(DC2Type:uuid_binary)\', INDEX IDX_9AAA31F4FE40C4A7 (combinationId), INDEX IDX_9AAA31F4B5F8B771 (itemId), PRIMARY KEY(combinationId, itemId) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CombinationXRecipe ( combinationId BINARY(16) NOT NULL COMMENT \'The internal id of the combination.(DC2Type:uuid_binary)\', recipeId BINARY(16) NOT NULL COMMENT \'The internal id of the recipe.(DC2Type:uuid_binary)\', INDEX IDX_64C3FB9DFE40C4A7 (combinationId), INDEX IDX_64C3FB9D6DCBA54 (recipeId), PRIMARY KEY(combinationId, recipeId) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CombinationXMachine ( combinationId BINARY(16) NOT NULL COMMENT \'The internal id of the combination.(DC2Type:uuid_binary)\', machineId BINARY(16) NOT NULL COMMENT \'The internal id of the machine.(DC2Type:uuid_binary)\', INDEX IDX_23B8DE38FE40C4A7 (combinationId), INDEX IDX_23B8DE38633EC4FD (machineId), PRIMARY KEY(combinationId, machineId) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CombinationXTranslation ( combinationId BINARY(16) NOT NULL COMMENT \'The internal id of the combination.(DC2Type:uuid_binary)\', translationId BINARY(16) NOT NULL COMMENT \'The internal id of the translation.(DC2Type:uuid_binary)\', INDEX IDX_7CDE9B16FE40C4A7 (combinationId), INDEX IDX_7CDE9B16CE6E35EC (translationId), PRIMARY KEY(combinationId, translationId) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Item ( id BINARY(16) NOT NULL COMMENT \'The internal id of the item.(DC2Type:uuid_binary)\', type ENUM(\'item\', \'fluid\') NOT NULL COMMENT \'The type of the item.(DC2Type:enum_item_type)\', name VARCHAR(255) NOT NULL COMMENT \'The unique name of the item.\', UNIQUE INDEX UNIQ_BF298A208CDE57295E237E06 (type, name), PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the items.\'');
        $this->addSql('CREATE TABLE Icon ( combinationId BINARY(16) NOT NULL COMMENT \'The internal id of the combination.(DC2Type:uuid_binary)\', type ENUM( \'mod\', \'item\', \'fluid\', \'machine\', \'recipe\' ) NOT NULL COMMENT \'The type of the icon\'\'s prototype.(DC2Type:enum_entity_type)\', name VARCHAR(255) NOT NULL COMMENT \'The name of the icon\'\'s prototype.\', imageId BINARY(16) NOT NULL COMMENT \'The internal id of the image.(DC2Type:uuid_binary)\', INDEX IDX_C5A686E5FE40C4A7 (combinationId), INDEX IDX_C5A686E510F3034D (imageId), PRIMARY KEY(combinationId, type, name) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the icons of the items and recipes.\'');
        $this->addSql('CREATE TABLE RecipeProduct ( recipeId BINARY(16) NOT NULL COMMENT \'The internal id of the recipe.(DC2Type:uuid_binary)\', `order` TINYINT UNSIGNED NOT NULL COMMENT \'The order of the ingredient in the recipe.(DC2Type:tinyint)\', itemId BINARY(16) NOT NULL COMMENT \'The internal id of the item.(DC2Type:uuid_binary)\', amountMin INT UNSIGNED NOT NULL COMMENT \'The minimal amount of the product in the recipe.\', amountMax INT UNSIGNED NOT NULL COMMENT \'The maximal amount of the product in the recipe.\', probability INT UNSIGNED NOT NULL COMMENT \'The probability of the product in the recipe.\', INDEX IDX_DBA48F666DCBA54 (recipeId), INDEX IDX_DBA48F66B5F8B771 (itemId), PRIMARY KEY(recipeId, `order`) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the products for the recipes.\'');
        $this->addSql('CREATE TABLE IconImage ( id BINARY(16) NOT NULL COMMENT \'The internal id of the image.(DC2Type:uuid_binary)\', contents LONGBLOB NOT NULL COMMENT \'The contents of the image.\', size SMALLINT UNSIGNED NOT NULL COMMENT \'The size of the image.\', PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'The table holding the icon image data.\'');
        $this->addSql('ALTER TABLE Recipe ADD CONSTRAINT FK_DD24B401DFFAB95E FOREIGN KEY (craftingCategoryId) REFERENCES CraftingCategory (id)');
        $this->addSql('ALTER TABLE RecipeIngredient ADD CONSTRAINT FK_69908F266DCBA54 FOREIGN KEY (recipeId) REFERENCES Recipe (id)');
        $this->addSql('ALTER TABLE RecipeIngredient ADD CONSTRAINT FK_69908F26B5F8B771 FOREIGN KEY (itemId) REFERENCES Item (id)');
        $this->addSql('ALTER TABLE MachineXCraftingCategory ADD CONSTRAINT FK_EE2BD258633EC4FD FOREIGN KEY (machineId) REFERENCES Machine (id)');
        $this->addSql('ALTER TABLE MachineXCraftingCategory ADD CONSTRAINT FK_EE2BD258DFFAB95E FOREIGN KEY (craftingCategoryId) REFERENCES CraftingCategory (id)');
        $this->addSql('ALTER TABLE CombinationXMod ADD CONSTRAINT FK_C3D0611AFE40C4A7 FOREIGN KEY (combinationId) REFERENCES Combination (id)');
        $this->addSql('ALTER TABLE CombinationXMod ADD CONSTRAINT FK_C3D0611AE07F9145 FOREIGN KEY (modId) REFERENCES `Mod` (id)');
        $this->addSql('ALTER TABLE CombinationXItem ADD CONSTRAINT FK_9AAA31F4FE40C4A7 FOREIGN KEY (combinationId) REFERENCES Combination (id)');
        $this->addSql('ALTER TABLE CombinationXItem ADD CONSTRAINT FK_9AAA31F4B5F8B771 FOREIGN KEY (itemId) REFERENCES Item (id)');
        $this->addSql('ALTER TABLE CombinationXRecipe ADD CONSTRAINT FK_64C3FB9DFE40C4A7 FOREIGN KEY (combinationId) REFERENCES Combination (id)');
        $this->addSql('ALTER TABLE CombinationXRecipe ADD CONSTRAINT FK_64C3FB9D6DCBA54 FOREIGN KEY (recipeId) REFERENCES Recipe (id)');
        $this->addSql('ALTER TABLE CombinationXMachine ADD CONSTRAINT FK_23B8DE38FE40C4A7 FOREIGN KEY (combinationId) REFERENCES Combination (id)');
        $this->addSql('ALTER TABLE CombinationXMachine ADD CONSTRAINT FK_23B8DE38633EC4FD FOREIGN KEY (machineId) REFERENCES Machine (id)');
        $this->addSql('ALTER TABLE CombinationXTranslation ADD CONSTRAINT FK_7CDE9B16FE40C4A7 FOREIGN KEY (combinationId) REFERENCES Combination (id)');
        $this->addSql('ALTER TABLE CombinationXTranslation ADD CONSTRAINT FK_7CDE9B16CE6E35EC FOREIGN KEY (translationId) REFERENCES Translation (id)');
        $this->addSql('ALTER TABLE Icon ADD CONSTRAINT FK_C5A686E5FE40C4A7 FOREIGN KEY (combinationId) REFERENCES Combination (id)');
        $this->addSql('ALTER TABLE Icon ADD CONSTRAINT FK_C5A686E510F3034D FOREIGN KEY (imageId) REFERENCES IconImage (id)');
        $this->addSql('ALTER TABLE RecipeProduct ADD CONSTRAINT FK_DBA48F666DCBA54 FOREIGN KEY (recipeId) REFERENCES Recipe (id)');
        $this->addSql('ALTER TABLE RecipeProduct ADD CONSTRAINT FK_DBA48F66B5F8B771 FOREIGN KEY (itemId) REFERENCES Item (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE RecipeIngredient DROP FOREIGN KEY FK_69908F266DCBA54');
        $this->addSql('ALTER TABLE CombinationXRecipe DROP FOREIGN KEY FK_64C3FB9D6DCBA54');
        $this->addSql('ALTER TABLE RecipeProduct DROP FOREIGN KEY FK_DBA48F666DCBA54');
        $this->addSql('ALTER TABLE CombinationXMod DROP FOREIGN KEY FK_C3D0611AE07F9145');
        $this->addSql('ALTER TABLE CombinationXTranslation DROP FOREIGN KEY FK_7CDE9B16CE6E35EC');
        $this->addSql('ALTER TABLE Recipe DROP FOREIGN KEY FK_DD24B401DFFAB95E');
        $this->addSql('ALTER TABLE MachineXCraftingCategory DROP FOREIGN KEY FK_EE2BD258DFFAB95E');
        $this->addSql('ALTER TABLE MachineXCraftingCategory DROP FOREIGN KEY FK_EE2BD258633EC4FD');
        $this->addSql('ALTER TABLE CombinationXMachine DROP FOREIGN KEY FK_23B8DE38633EC4FD');
        $this->addSql('ALTER TABLE CombinationXMod DROP FOREIGN KEY FK_C3D0611AFE40C4A7');
        $this->addSql('ALTER TABLE CombinationXItem DROP FOREIGN KEY FK_9AAA31F4FE40C4A7');
        $this->addSql('ALTER TABLE CombinationXRecipe DROP FOREIGN KEY FK_64C3FB9DFE40C4A7');
        $this->addSql('ALTER TABLE CombinationXMachine DROP FOREIGN KEY FK_23B8DE38FE40C4A7');
        $this->addSql('ALTER TABLE CombinationXTranslation DROP FOREIGN KEY FK_7CDE9B16FE40C4A7');
        $this->addSql('ALTER TABLE Icon DROP FOREIGN KEY FK_C5A686E5FE40C4A7');
        $this->addSql('ALTER TABLE RecipeIngredient DROP FOREIGN KEY FK_69908F26B5F8B771');
        $this->addSql('ALTER TABLE CombinationXItem DROP FOREIGN KEY FK_9AAA31F4B5F8B771');
        $this->addSql('ALTER TABLE RecipeProduct DROP FOREIGN KEY FK_DBA48F66B5F8B771');
        $this->addSql('ALTER TABLE Icon DROP FOREIGN KEY FK_C5A686E510F3034D');
        $this->addSql('DROP TABLE Recipe');
        $this->addSql('DROP TABLE RecipeIngredient');
        $this->addSql('DROP TABLE `Mod`');
        $this->addSql('DROP TABLE CachedSearchResult');
        $this->addSql('DROP TABLE Translation');
        $this->addSql('DROP TABLE CraftingCategory');
        $this->addSql('DROP TABLE Machine');
        $this->addSql('DROP TABLE MachineXCraftingCategory');
        $this->addSql('DROP TABLE Combination');
        $this->addSql('DROP TABLE CombinationXMod');
        $this->addSql('DROP TABLE CombinationXItem');
        $this->addSql('DROP TABLE CombinationXRecipe');
        $this->addSql('DROP TABLE CombinationXMachine');
        $this->addSql('DROP TABLE CombinationXTranslation');
        $this->addSql('DROP TABLE Item');
        $this->addSql('DROP TABLE Icon');
        $this->addSql('DROP TABLE RecipeProduct');
        $this->addSql('DROP TABLE IconImage');
    }
}
