# Changelog

## v3.3 - 2017-01-10 passionatedreamer19@gmail.com http://facebook.com/muhsaeedparacha
- Added permissions directly on Users in addition to being on roles.
- While checking for user permissions, the package checks via two ways $user->permissions() and $user->roles()->permissions
- use HasRoleAndPermission trait in User Model instead of HasRole
- Changed HasRole Trait to subtract functionality for User Model, most of that functionality has been copied to HasRoleAndPermission Trait

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
