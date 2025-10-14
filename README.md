<p>
<img src="https://forge.winningsoftware.co.uk/LoomLabs/loom-spinner-cli/media/branch/main/header.jpg" alt="Loom Spinner CLI Header Image & Logo" style="width: 100%; height: auto;">
</p>

# Loom Spinner CLI

<p>
<!-- Version Badge -->
<img src="https://img.shields.io/badge/Version-2.0.0-blue" alt="Version 2.0.0">
<!-- License Badge -->
<img src="https://img.shields.io/badge/License-GPL--3.0--or--later-40adbc" alt="License GPL-3.0-or-later">
</p>

A streamlined environment management tool for PHP developers.

Loom Spinner makes it easy to launch minimal, thoughtfully pre-configured Docker containers for PHP development; helping 
you enjoy a fast, consistent, and hassle-free workflow.

Run simple commands from anywhere on your system to manage your environments.

# At a Glance

Effortlessly create custom Docker environments for each of your PHP projects. Out of the box, Loom Spinner provides:

- **PHP 8.4** (includes XDebug & OpCache)
- **Nginx**
- **MySQL 9.3**
- **NodeJS 23** (Node, NPM, & NPX)

Your project directory is automatically mounted to the PHP container, and the `public` directory is served via Nginx at 
`http://localhost:<nginx-port>`. Access the container directly from your terminal to execute unit tests or other 
commands, all within an isolated environment.

# Installation

**Requirements:**
- Composer
- Docker Desktop or Docker Engine

To install globally, run:

```shell
composer global require cloudbase/loom-spinner
```

# Usage

Start Docker, then launch your project environment:

```shell
cd /path/to/my-project
loom spin:up my-project .
```

Check which ports your containers are using via Docker Desktop or by running `docker ps`.

Once running, your project's public directory is accessible at `http://localhost:<nginx-container-port>`—you're ready to go!

## Managing Your Environment

To stop your containers:

```shell
loom spin:stop my-project
```

To start them again:

```shell
loom spin:start my-project
```

To attach to your PHP container:

```shell
loom spin:attach my-project
```

To remove them completely:

```shell
loom spin:down my-project
```

To list all of your _Spinner_ managed environments:

```shell
loom env:list
```

Loom Spinner can be further customized with a set of simple configuration options.

Happy spinning! 🧵