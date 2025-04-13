# Loom | Spinner

An environment management application for PHP developers.

# Dev Notes

**Argument priority:**

- Those passed explicitly in the CLI commands
- Any set within `{projectDirectory}/spinner.yaml`
- Fall back to `/config/spinner.yaml`

# Commands

## Command: `spin:up`

Creates a new PHP development environment and mounts your project files.

### Arguments

> #### Argument: name 
>
> **Required?** ✅
> 
> The name of your Docker containers. Your containers will spin up with the name {name}-{service}-1 i.e.
> 
> `spinner spin:up name=test path=/path`
> 
> Results in containers named `test-php-1` and `test-nginx-1`

> #### Argument: path
> 
> **Required?** ✅
> 
> The **absolute path** on your system to the project you want to create containers for.

### Options

> #### Option: --php
> 
> **Required?** ❌
> 
> Defines the PHP version that your container will use. You can omit this flag and set the PHP version inside your
> projects `spinner.yaml` file. Otherwise, will use the default value found in `config/spinner.yaml`

> #### Option: --node
> 
> **Required?** ❌
> 
> Set which version of Node to install in your container. Is ignored if the `--disable-node` flag is
> passed, or if Node is disabled in your projects `spinner.yaml` file. Equivalent to setting `options.environment.node.version = x`
> in your projects Spinner config.

> #### Option: --disable-node
> 
> **Required?** ❌
> 
> Disables Node for your environment, so it isn't included in your PHP container. Equivalent to setting `options.environment.node.enabled = false`
> in your Spinner config.

> #### Option: --disable-server
> 
> **Required?** ❌
> 
> Does not install a webserver (so no Nginx). Useful if you just need a PHP container to run 
> unit tests or something.