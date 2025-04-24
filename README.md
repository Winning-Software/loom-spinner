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
> The name of your Docker containers. Your containers will spin up with the name {name}-{service} i.e.
> 
> `spinner spin:up name=test path=/path`
> 
> Results in containers named `test-php` and `test-nginx`

> #### Argument: path
> 
> **Required?** ✅
> 
> The **absolute path** on your system to the project you want to create containers for.

### Options

> #### Option: --php
> 
> Defines the PHP version that your container will use. You can omit this flag and set the PHP version inside your
> projects `spinner.yaml` file. Otherwise, will use the default value found in `config/spinner.yaml`

> #### Option: --node
> 
> Set which version of Node to install in your container. Equivalent to setting `options.environment.node.version: x` 
> in your projects Spinner config.

> #### Option: --database
> 
> Set which database driver to use with your environment. The default (and currently only) database driver is `sqlite3`. 
> Accepted values are currently `sqlite` and `sqlite3`. If using the default, SQLite is installed inside your PHP 
> container and a mapping created to the `/sqlite` directory in your project. This argument is ignored if 
> `--disabled-database` is set.

> #### Option: --disable-database
>
> Does not install a database with your environment. Equivalent to setting `options.environment.database.enabled: false` 
> in your Spinner config.

> #### Option: --disable-server
> 
> Does not install a webserver (so no Nginx). Useful if you just need a PHP container to run unit tests or something.

> #### Option: --disable-xdebug
> 
> Do not install XDebug in your environment. Equivalent to setting `options.environment.php.xdebug = false` in your 
> projects Spinner config.

## Command: `spin:down`

Destroys a named environment; containers and Spinner config.

> #### Argument: name
>
> **Required?** ✅
>
> The name of the containers and associated config that you wish to delete. Use the same name you used when you
> span up your containers with the `spin:up` command.
> 
> `spinner spin:down name=project-name`

## XDebug Setup

These instructions are for PHPStorm. I don't know about working with other IDE's, although they
should be fairly universal.

### Server Settings

- `File` -> `Settings`
- `PHP` -> `Servers` -> `+`
- Give your "server" a name and use the values shown below.

| Setting                 | Value                                                                                                          |
|-------------------------|----------------------------------------------------------------------------------------------------------------|
| Host                    | 127.0.0.1                                                                                                      |
| Port                    | **Local** port **PHP** container is running on. i.e. if your docker container shows 52033:9003, use **52033**. |
| Debugger                | Xdebug                                                                                                         |
| Use path mappings?      | ✅                                                                                                              |
| File/Directory          | Select **your project root**                                                                                   |
| Absolute path on server | /var/www/html                                                                                                  |

### Remote Debugger

- `Run` -> `Edit Configurations`
- `+` -> `PHP Remote Debug`
- Give it a name
- Use the server you created before
- Use **SPINNER** as IDE key

Make sure you start listening when you want to debug by clicking the little bug icon in the 
top right (by default) in PHPStorm.