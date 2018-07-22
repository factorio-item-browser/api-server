# Changelog

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