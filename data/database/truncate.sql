-- Truncates ALL database tables. DO NOT EXECUTE UNLESS YOU WANT TO THROW ALL DATA AWAY!

SET foreign_key_checks = 0;
  TRUNCATE `CachedSearchResult`;
  TRUNCATE `CraftingCategory`;
  TRUNCATE `Icon`;
  TRUNCATE `IconFile`;
  TRUNCATE `Item`;
  TRUNCATE `Machine`;
  TRUNCATE `MachineXCraftingCategory`;
  TRUNCATE `Mod`;
  TRUNCATE `ModCombination`;
  TRUNCATE `ModCombinationXItem`;
  TRUNCATE `ModCombinationXMachine`;
  TRUNCATE `ModCombinationXRecipe`;
  TRUNCATE `ModDependency`;
  TRUNCATE `Recipe`;
  TRUNCATE `RecipeIngredient`;
  TRUNCATE `RecipeProduct`;
  TRUNCATE `Translation`;
SET foreign_key_checks = 1;