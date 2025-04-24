# Changelog

All notable changes to this project will be documented in this file.

## [1.0.2] - 2025-04-24
### Added
- Added information on `spin:down` command to README.

### Changed
- Minor documentation tweak to include link to Wiki on Packagist.

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