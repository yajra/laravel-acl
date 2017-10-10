# Changelog

## v3.3.1 - 2017-10-10
- Compare hasRole using slug. #16
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
