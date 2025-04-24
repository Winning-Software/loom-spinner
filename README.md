# Loom | Spinner

<div>
<img src="./header.jpg" alt="Loom Spinner CLI Header Image &amp; Logo" style="width: 100%; height: auto;">
</div>

<div>
<!-- Version Badge -->
<img src="https://img.shields.io/badge/Version-1.0.0-blue" alt="Version 1.1.0">
</div>

An environment management application for PHP developers. 

Allows you to spin up minimal, sensibly pre-configured Docker
containers for PHP development, for a fast, consistent and smooth development experience.

Simple commands you can run from anywhere on your system.

# The Lowdown

Create pre-configured Docker environments on a project-by-project basis, tailored specifically for developing PHP 
applications. The default setup gives you an environment with:

- PHP 8.4 (with XDebug + OpCache)
- Nginx
- SQLite3
- NodeJS 23 (Node, NPM + NPX)

Your project is mounted to the PHP container and your projects `public` directory is served via Nginx at 
`http://localhost:<nginx-port>` and your container is accessible via the command line for things like running
unit tests.

**Loom Spinner CLI is currently in the early stages of development and thus only has limited features, such as the
only database option currently being SQLite. More options will be added in subsequent releases.**

# Installation

Pre-Requisites: 

- Composer 
- Docker Desktop/Docker Engine

```shell
composer global require loomlabs/loom-spinner-cli
```

# Usage

Start your (Docker) engines, then:

```shell
cd /path/to/my-project
loom spin:up my-project .
```

Check your new containers ports by inspecting the containers in Docker Desktop or running `docker ps`. 

Your projects public directory is now available at `http://localhost:<nginx-container-port>`. Voila!

## What Else?

Want to stop your containers?

```shell
loom spin:stop my-project
```

Start them again?

```shell
loom spin:start my-project
```

Destroy your containers?

```shell
loom spin:down my-project
```

What's more, `loom` can be configured further with a simple set of configuration options. For a more detailed 
quick-start guide or for more information on using and configuring **Loom Spinner CLI**, please consult the wiki.

Happy spinning! 🧵