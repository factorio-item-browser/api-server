# Changelog

## Unreleased

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
