# Changelog

## v4.2.0 - 2020-02-14

- Add scope havingRolesBySlugs.
- Add roles property and method annotations.

## v4.1.3 - 2019-09-28

- Fix FatalErrorException when overriding customPermissionMap property.

## v4.1.2 - 2019-09-28

- Revert to "Implement strict comparison on permission slug."
- Fix for intermittent cache issues.

## v4.1.1 - 2019-09-05
- Refactor model events to RefreshCache trait.
- Fix role comparison not working due to logged in user cached roles.

## v4.1.0 - 2019-09-04
- Add support laravel 6.0. [#31](https://github.com/yajra/laravel-acl/pull/24)

## v4.0.2 - 2019-08-31
- Implement strict comparison on permission slug.
- Fix issue with cache where old permission roles are still being used for comparison.

## v4.0.1 - 2019-08-22
- Resolve cache key from config.

## v4.0.0 - 2019-08-22
- Use bigIncrements to match laravel 5.8 migrations.
- Resolve models from config file.
- Set min support to Laravel 5.8.
- Fix merging of config.
- Add option to configure cache.

## v3.6.1 - 2019-01-08
- Fix eloquent collection.

## v3.6.0 - 2018-01-03
- Add unit tests. [#24](https://github.com/yajra/laravel-acl/pull/24)
- Bump to php 7.0.
- Bump to phpunit 6.0.

## v3.5.0 - 2017-11-21
- Add @role directive. [#22](https://github.com/yajra/laravel-acl/pull/22)
- Deprecate @isRole directive in favor of @role directive.

## v3.4.0 - 2017-11-21
- Add support for multiple role middleware. [#21](https://github.com/yajra/laravel-acl/pull/21)
- Fix #20.

## v3.3.1 - 2017-10-10
- Compare hasRole using slug. [#16](https://github.com/yajra/laravel-acl/pull/16)
- Fix #8.

## v3.3.0 - 2017-07-31
- Fix 3.0 branch alias.
- Add support for Laravel 5.5 auto-package discovery.

## v3.2.1 - 2017-07-07
- Fix publishing of migrations.

## v3.2.0 - 2017-01-05
- Add github templates.
- Add php_cs.
- Update gitattributes.

## v3.1.2 - 2017-01-05
- Removed the unnecessary variadic operator.
- PR #6, credits to @orumad.

## v3.1.1 - 2017-01-04
- Fix the issue with the case of variadic arguments.
- PR #5, credits to @orumad.

## v3.1.0 - 2016-12-22
- Add revokeRoleBySlug method.
- PR #3, credits to @jiwom

## v3.0.1 - 2016-12-01
- Fix permission resource middleware.

## v3.0.0 - 2016-11-18

### Added
- RoleMiddleware
- PermissionMiddleware
- CanAtLeastMiddleware
- Role can ability.
- @hasRole directive
- @isRole directive

### Deprecated
- HasPermission trait getPermissionsSlug renamed to getPermissions.

### Fixed
- Fix doc blocks.
- Fix @canAtLeast directive
- Fix year on license.

### Removed
- Nothing

### Security
- Nothing
