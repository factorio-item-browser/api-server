-- Creates the database tables for the API servers in an empty database.

-- Mod related tables
CREATE TABLE `Mod` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The internal id of the mod.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The name of the mod.',
  `author` TEXT NOT NULL COMMENT 'The author of the mod.',
  `currentVersion` VARCHAR(16) NOT NULL COMMENT 'The current version of the mod that has been imported.',
  `order` INT(10) UNSIGNED NOT NULL COMMENT 'The order position of the mod, 1 being the base mod.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_name` (`name`)
)
COMMENT='The table holding the imported mods.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `ModDependency` (
  `modId` INT(10) UNSIGNED NOT NULL COMMENT 'The id of the mod with the dependency.',
  `requiredModId` INT(10) UNSIGNED NOT NULL COMMENT 'The id of the required mod.',
  `requiredVersion` VARCHAR(16) NOT NULL COMMENT 'The required version of the mod.',
  `type` ENUM('mandatory','optional') NOT NULL COMMENT 'The type of the dependency.',
  PRIMARY KEY (`modId`, `requiredModId`),
  INDEX `idx_modId` (`modId`),
  INDEX `idx_requiredModId` (`requiredModId`),
  CONSTRAINT `fk_MD_modId` FOREIGN KEY (`modId`) REFERENCES `Mod` (`id`),
  CONSTRAINT `fk_MD_requiredModId` FOREIGN KEY (`requiredModId`) REFERENCES `Mod` (`id`)
)
COMMENT='The table holding the dependencies between the mods.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `ModCombination` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The id of the mod combination.',
  `modId` INT(10) UNSIGNED NOT NULL COMMENT 'The id of the main mod.',
  `optionalModIds` TEXT NOT NULL COMMENT 'The list of the ids of the loaded optional mods.',
  `name` TEXT NOT NULL COMMENT 'The name of the mod combination.',
  `order` INT(10) UNSIGNED NOT NULL COMMENT 'The order of the mod combination.',
  PRIMARY KEY (`id`),
  INDEX `idx_modId` (`modId`),
  CONSTRAINT `fk_MC_modId` FOREIGN KEY (`modId`) REFERENCES `Mod` (`id`)
)
COMMENT='The table holding the combinations of optional mods manipulating the data.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


-- Item related tables
CREATE TABLE `Item` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The internal id of the item.',
  `type` ENUM('item','fluid') NOT NULL COMMENT 'The type of the item.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The unique name of the item.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uq_type_name` (`type`, `name`)
)
COMMENT='The table holding the items.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `ModCombinationXItem` (
  `modCombinationId` INT(10) UNSIGNED NOT NULL COMMENT 'The id of the referenced mod combination.',
  `itemId` INT(10) UNSIGNED NOT NULL COMMENT 'The id of the referenced item.',
  PRIMARY KEY (`modCombinationId`, `itemId`),
  INDEX `idx_modId` (`modCombinationId`),
  INDEX `idx_itemId` (`itemId`),
  CONSTRAINT `fk_MCxI_itemId` FOREIGN KEY (`itemId`) REFERENCES `Item` (`id`),
  CONSTRAINT `fk_MCxI_modCombinationId` FOREIGN KEY (`modCombinationId`) REFERENCES `ModCombination` (`id`)
)
COMMENT='The reference table between mod combinations and items.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


-- Recipe related tables
CREATE TABLE `Recipe` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The internal id of the recipe.',
  `type` ENUM('normal','expensive') NOT NULL COMMENT 'The type of the recipe.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The name of the recipe.',
  `craftingTime` INT(10) UNSIGNED NOT NULL COMMENT 'The required time in milliseconds to craft the recipe.',
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name`)
)
COMMENT='The recipes to craft the items.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `RecipeIngredient` (
  `recipeId` INT(10) UNSIGNED NOT NULL COMMENT 'The recipe id of the ingredient.',
  `itemId` INT(10) UNSIGNED NOT NULL COMMENT 'The item id of the ingredient.',
  `amount` INT(10) UNSIGNED NOT NULL COMMENT 'The amount required for the recipe.',
  `order` TINYINT(3) UNSIGNED NOT NULL COMMENT 'The order of the ingredient in the recipe.',
  PRIMARY KEY (`recipeId`, `itemId`),
  INDEX `idx_recipeId` (`recipeId`),
  INDEX `idx_itemId` (`itemId`),
  CONSTRAINT `fk_RI_itemId` FOREIGN KEY (`itemId`) REFERENCES `Item` (`id`),
  CONSTRAINT `fk_RI_recipeId` FOREIGN KEY (`recipeId`) REFERENCES `Recipe` (`id`)
)
COMMENT='The table holding the ingredients for the recipes.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `RecipeProduct` (
  `recipeId` INT(10) UNSIGNED NOT NULL COMMENT 'The recipe id of the product.',
  `itemId` INT(10) UNSIGNED NOT NULL COMMENT 'The item id of the product.',
  `amountMin` INT(10) UNSIGNED NOT NULL COMMENT 'The minimal amount of the product in the recipe.',
  `amountMax` INT(10) UNSIGNED NOT NULL COMMENT 'The maximal amount of the product in the recipe.',
  `probability` INT(10) UNSIGNED NOT NULL COMMENT 'The probability of the product in the recipe.',
  `order` TINYINT(3) UNSIGNED NOT NULL COMMENT 'The order of the product in the recipe.',
  PRIMARY KEY (`recipeId`, `itemId`),
  INDEX `idx_recipeId` (`recipeId`),
  INDEX `idx_itemId` (`itemId`),
  CONSTRAINT `fk_RP_itemId` FOREIGN KEY (`itemId`) REFERENCES `Item` (`id`),
  CONSTRAINT `fk_RP_recipeId` FOREIGN KEY (`recipeId`) REFERENCES `Recipe` (`id`)
)
COMMENT='The table holding the products of the recipes.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `ModCombinationXRecipe` (
  `modCombinationId` INT(10) UNSIGNED NOT NULL COMMENT 'The id of the referenced mod combination.',
  `recipeId` INT(10) UNSIGNED NOT NULL COMMENT 'The id of the referenced recipe.',
  PRIMARY KEY (`modCombinationId`, `recipeId`),
  INDEX `idx_modCombinationId` (`modCombinationId`),
  INDEX `idx_recipeId` (`recipeId`),
  CONSTRAINT `fk_MCxR_modCombinationId` FOREIGN KEY (`modCombinationId`) REFERENCES `ModCombination` (`id`),
  CONSTRAINT `fk_MCxR_recipeId` FOREIGN KEY (`recipeId`) REFERENCES `Recipe` (`id`)
)
COMMENT='The reference table between mod combinations and recipes.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


-- Icon related tables
CREATE TABLE `IconFile` (
  `hash` INT(10) UNSIGNED NOT NULL COMMENT 'The hash of the icon file.',
  `image` BLOB NOT NULL COMMENT 'The actual image.',
  PRIMARY KEY (`hash`)
)
COMMENT='The table holding the actual file data of the icons.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `Icon` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The internal id of the icon.',
  `modCombinationId` INT(10) UNSIGNED NOT NULL COMMENT 'The id of the mod combination adding the icon.',
  `iconFileHash` INT(10) UNSIGNED NOT NULL COMMENT 'The hash of the icon file.',
  `type` VARCHAR(32) NOT NULL COMMENT 'The type of the icon\'s prototype.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The name of the icon\'s prototype.',
  PRIMARY KEY (`id`),
  INDEX `idx_modId` (`modCombinationId`),
  INDEX `idx_iconFileHash` (`iconFileHash`),
  INDEX `idx_type_name` (`type`, `name`),
  CONSTRAINT `fk_I_iconFileHash` FOREIGN KEY (`iconFileHash`) REFERENCES `IconFile` (`hash`),
  CONSTRAINT `fk_I_modCombinationId` FOREIGN KEY (`modCombinationId`) REFERENCES `ModCombination` (`id`)
)
COMMENT='The table holding the icons of the items and recipes.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


-- Translation related tables
CREATE TABLE `Translation` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The internal id of the translation.',
  `modCombinationId` INT(10) UNSIGNED NOT NULL COMMENT 'The id of the mod combination adding the translation.',
  `locale` VARCHAR(5) NOT NULL COMMENT 'The locale of the translation.',
  `type` VARCHAR(32) NOT NULL COMMENT 'The type of the translation.',
  `name` VARCHAR(255) NOT NULL COMMENT 'The name of the translation.',
  `value` TEXT NOT NULL COMMENT 'The actual translation.',
  `description` TEXT NOT NULL COMMENT 'The translated description.',
  `isDuplicatedByRecipe` BIT(1) NOT NULL COMMENT 'Whether this translation is duplicated by the recipe.',
  PRIMARY KEY (`id`),
  INDEX `idx_modId` (`modCombinationId`),
  INDEX `idx_locale_type_name` (`locale`, `type`, `name`),
  CONSTRAINT `fk_T_modCombinationId` FOREIGN KEY (`modCombinationId`) REFERENCES `ModCombination` (`id`)
)
COMMENT='The table holding the localized translations of the items and recipes.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


-- Other tables
CREATE TABLE `CachedSearchResult` (
  `hash` INT(10) UNSIGNED NOT NULL COMMENT 'The hash of the search.',
  `resultData` TEXT NOT NULL COMMENT 'The result data of the search.',
  `lastSearchTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'The time when the search result was last used.',
  PRIMARY KEY (`hash`)
)
COMMENT='The table caching the search results.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
