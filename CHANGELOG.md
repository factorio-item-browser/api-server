# Changelog

## 1.2.0 - Unreleased

### Changed

- Full refactoring of the API server.
- Extracted files for the database access to separate library `factorio-item-browser/api-database`.
- Extracted search logic to separate library `factorio-item-browser/api-search`.
- Extracted import logic to separate project `factorio-item-browser/api-import`. The API server now only reads from the 
  database (with the exception of the CachedSearchResult table).
- Changed library `factorio-item-browser/client` to latest version 2.0.
- Use client request and response entities and its serializer to parse requests and build responses.

### Removed 

- API: Removed `parameters` field from the error response when request validation fails. Error responses now
  always have only the message.

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
