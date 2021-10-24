# Changelog

## 3.1.6 - 2021-10-24

### Fixed

- Invalid routes returning wrong status code of 500 instead of the intended 400.
- Missing tracking values on errors.

## 3.1.5 - 2021-09-26

### Added

- Tracking of request-related data as a first test.

## 3.1.4 - 2021-07-09

### Added

- Command for auto-updating combinations under certain conditions.

## 3.1.3 - 2021-07-06

### Added

- Updating the `lastUsageTime` column in the Combination table with each request.
- Verification of the combination id in the paths, returning status code 404 if the combination does not exist.

## 3.1.2 - 2021-05-24

### Changed

- PHP version from 7.4 to 8.0.

## 3.1.1 - 2021-05-14

### Fixed

- Fixed translations not always using the English as fallback.

## 3.1.0 - 2021-02-23

### Changed

- Reduced responses in `item/list`, `item/random` and `search/query`: If `numberOfRecipesPerResult` is set to 0 in the
  request, the response will no longer contain the keys `recipes` and `totalNumberOfRecipes` for the entries, for a 
  performance increase.

## 3.0.1 - 2021-02-22

### Changed

- Updated dependency `factorio-item-browser/api-database` to version 3.5.0.

## 3.0.0 - 2021-02-18

### Changed

- **[BC Break]** All requests require the full combination id in the URL. E.g. vanilla electronic circle becomes
  `/2f4a45fa-a509-a9d1-aae6-ffcf984a7a76/item/electronic-circuit`. Use the new Combination API to get the id of a 
  combination.
- **[BC Break]** Replaced `/auth` request and its JWT token with an API key for all requests.
- Search cache is now stored in a binary format instead of a readable string.

### Removed

- Requests `/combination/status`, `/combination/validate` and `/combination/trigger`. Those are now handled by the
  Combination API itself.

## 2.3.0 - 2020-11-01

### Added

- New endpoint `/combination/validate` to validate a combination against the Factorio Mod Portal.
- Parameters `last-usage` and `max-updates` to the `update-combinations` command to overwrite default values from the config.

## 2.2.0 - 2020-06-03

### Added

- Command `update-combinations` to look for combinations needing an update.

### Changed

- Charset of all tables to utf8mb4 to actually be UTF-8.
- All identifying columns' collation to utf8mb4_bin to make them case-sensitive, as they are in the game.

### Removed

- Support for PHP 7.3. The project must now run with at least PHP 7.4.

## 2.1.1 - 2020-05-03

### Changed

- Demo agent is no longer allowed to create exports.

### Fixed

- Missing CORS headers.

## 2.1.0 - 2020-05-02

### Added

- CORS headers.
- New endpoints `/item/list` and `/recipe/list` to get a full list of items or recipes respectively.

### Changed

- Allow "base" mod to be absent in `/auth` request as of FFF #343.
- Indirect dependency `dasprid/container-interop-doctrine` to `roave/psr-container-doctrine`.

## 2.0.0 - 2020-04-16

### Added

- Attribute `size` to the generic icon response.
- Command for clearing the caches from out-dated entries.
- Doctrine Migrations for managing the database structure.
- New endpoints `/combination/status` and `/combination/export` to trigger new exports and check their status.

### Changed

- Full refactoring of the API server.
- Extracted files for the database access to separate library `factorio-item-browser/api-database`.
- Extracted search logic to separate library `factorio-item-browser/api-search`.
- Extracted import logic to separate project `factorio-item-browser/api-import`. The API server now only reads from the 
  database (with the exception of the CachedSearchResult table).
- Changed library `factorio-item-browser/client` to latest version.
- Use client request and response entities and its serializer to parse requests and build responses.
- Machine preferred in sorting from "player" to "character" as of in-game change.
- Dependencies from Zend to Laminas.

### Removed 

- API: Removed `parameters` field from the error response when request validation fails. Error responses now
  always have only the message.
- Agent name from the `auth` request. The access key is now enough.
- No longer supported endpoint `mod/meta`.

## 1.1.0 - 2018-07-22

### Added

- Request `recipe/machines` providing all machines able to craft the specified recipe.
- `X-Version` and `X-Runtime` to all response headers.

### Fixed

- Conflicting icons between mods.
- Inconsistent numbers of recipes.

### Changed

- Expensive recipes are now returned attached to their normal version instead of a separate recipe. 
  This changes the responses of `/item/ingredient`, `/item/product`, `/item/random`, `/recipe/details` and 
  `/search/query` requests. 
- Changed format of error responses.

### Removed

- Removed `meta` node from all responses.

## 1.0.1 - 2018-04-14

### Fixed

- Empty search actually returned a result without any data.
- Wrong number of enabled mods.
- Generic details partially ignored enabled mods for items.
- Mods matching the search query generating a search result without data.

## 1.0.0 - 2018-04-06

- Initial release of the API server.
