# Changelog

All notable changes to this project will be documented in this file.

## [1.4.4] - 2025-06-23
### Fixed
- No longer redirecting static assets to front controller.

## [1.4.3] - 2025-06-08
### Fixed
- Fixed issue with `env:list` command always using the same `.env` config.
- Now displays correct URL for each environment.

## [1.4.2] - 2025-06-06
### Added
- Added a project URL to the output of the `env:list` command.

## [1.4.1] - 2025-06-06
### Added
- Added `--version` command.

### Fixed
- Fixed `env:list` command output when no environments are configured.

## [1.4.0] - 2025-06-03
### Changed
- Replaced emojis in `env:list` command output with coloured text.

## [1.3.1] - 2025-05-21
### Changed
- Update package for re-release as `loomsoftware/loom-spinner`.

## [1.3.0] - 2025-05-18
### Added
- Added Attach Command: `spin:attach <name>`

## [1.2.2] - 2025-05-17
### Fixed
- Fixed warning outputs that are displayed when an environment does not use Nginx.

## [1.2.1] - 2025-05-17
### Added
- Added Server and Database information to the output of `env:list` command.

## [1.2.0] - 2025-05-17
### Added
- Added List Environments Command: `env:list`.
- Added dev dependency: `loomsoftware/badger`.

## [1.1.3] - 2025-05-04
### Fixed
- Fixed a typo causing XDebug installation to fail inside the PHP-FPM container.

## [1.1.2] - 2025-04-26
### Fixed
- Fixed a critical bug where environments would not build correctly if using a SQLite database.

## [1.1.1] - 2025-04-26
### Fixed
- Fixed a critical bug where environments could not be destroyed if using a MySQL database.
- Fixed a critical bug where PDO extensions were not installed in the container, causing MySQL driver errors.

## [1.1.0] - 2025-04-24
### Added
- New database option: MySQL.
- New configuration option: `options.environment.database.rootPassword`.

### Changed
- Changed the default database for new environments to MySQL (from SQLite).

## [1.0.4] - 2025-04-24
### Fixed
- Fixed a critical autoloading issue after the package is globally installed.

## [1.0.3] - 2025-04-24
### Fixed
- Fixed typo in `composer.json` description field.

## [1.0.2] - 2025-04-24
### Added
- Added information on `spin:down` command to README.

### Changed
- Minor documentation tweak to include a link to the Wiki on Packagist.

## [1.0.1] - 2025-04-24
### Changed
- Minor documentation tweak to make the header image display on Packagist.

## [1.0.0] – 2025-04-24
### Added
- Initial public release of **Loom Spinner CLI**.
- Command to spin up a new PHP development environment with Docker (`spin:up`).
- Commands to stop (`spin:stop`), start (`spin:start`), and destroy (`spin:down`) environments.
- Customizable environment configuration via CLI options or `spinner.yaml`.
- Support for PHP (default: 8.4), Nginx, SQLite3, and Node.js (default: 23).
- Xdebug support (enabled by default; can be disabled per environment).
- Automatic port assignment for services.
- Clear documentation for installation, configuration, usage, commands, and debugging.

---

**Note:**  
This is the project’s initial release. More features and database options are planned for future updates.