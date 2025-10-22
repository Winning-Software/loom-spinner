# Loom Spinner CLI

<div>
<!-- Version Badge -->
<img src="https://img.shields.io/badge/Version-3.2.1-blue" alt="Version 3.2.1">
<!-- License Badge -->
<img src="https://img.shields.io/badge/License-GPL--3.0--or--later-40adbc" alt="License GPL-3.0-or-later">
</div>

Loom Spinner is a streamlined environment management tool for PHP developers.

It makes launching minimal, pre-configured Docker containers effortless, providing a fast, consistent, and hassle-free 
workflow for your projects.

Run simple commands from anywhere on your system to manage your environments.

> This project is built for Linux and has not been fully tested on Windows or MacOS. Full Windows/MacOS support is 
> planned for a future release.

# At a Glance

Effortlessly create custom Docker environments for each of your PHP projects. Out of the box, Loom Spinner provides:

- **PHP 8.4** (with XDebug & OpCache)
- **Nginx**
- **MySQL 9.3**
- **NodeJS 23** (Node, NPM, & NPX)

Your project directory is automatically mounted into the PHP container, and the `public` directory is served via Nginx at:

```shell
https://{project-name}.app
``` 

You can access the container directly from your terminal to run tests or other commands in an isolated environment.

# Installation

**Requirements:**
- Composer
- Docker Desktop or Docker Engine

To install globally, run:

```shell
composer global require cloudbase/loom-spinner
```

> **Optional HTTPS/SSL Support**
> 
> For prettified `https://{project-name}.app` URLs, install `mkcert` before using Loom Spinner.

Linux example:

```shell
sudo apt install mkcert libnss3-tools
mkcert -install
```

# Quick Start

Spin up your project and add the hosts entry in a single sequence:

```shell
cd /path/to/my-project
loom spin:up my-project .
sudo loom env:hosts:add my-project
```

> ✅ This will create the Docker containers (PHP, Nginx, MySQL) and ensure your system can resolve http://my-project.app 
> for clean URLs.

# Usage

Launch a new environment:

```shell
loom spin:up my-project /path/to/my-project
```

Or from the project directory:

```shell
cd /path/to/my-project
loom spin:up my-project .
```

## Hosts Entry

To access your project via the browser, add an entry to `/etc/hosts`:

```shell
sudo loom env:hosts:add my-project
```

## Database Credentials

Your default database credentials are:

| Username | Password |
|----------|----------|
| root     | docker   |

To see which port your database container is using, run:

```shell
loom env:list
```

You can customise your credentials, see the [Configuration](https://github.com/CloudBaseHQ/loom-spinner/wiki/Configuration) 
section of the documentation for more details.

## Managing Your Environment

| Action                  | Command                       |
|-------------------------|-------------------------------|
| Stop containers         | `loom spin:stop my-project`   |
| Start containers        | `loom spin:start my-project`  |
| Attach to PHP container | `loom spin:attach my-project` |
| Destroy environment     | `loom spin:down my-project`   |
| List all environments   | `loom env:list`               |

---

Happy spinning! 🧵