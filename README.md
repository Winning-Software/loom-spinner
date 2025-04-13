# Loom | Spinner

An environment management application for PHP developers.

## Dev Notes

**Argument priority:**

- Those passed explicitly in the CLI commands
- Any set within `{projectDirectory}/spinner.yaml`
- Fall back to `/config/spinner.yaml`

## Commands

### `spin:up`

#### Arguments

- `name` - **Required**: The name for your Docker containers.
- `path` - **Required**: The **absolute path** to your project root directory.
- `php` - **Optional**: If passed, sets the PHP version used by your container. Can be overridden 
by creating a `spinner.yaml` file in your project root directory and defining the key `options.environment.php.version`

#### Options

- `disable-node` - **Optional**: Disables Node. Can also define the key `options.environment.node.enabled` in your 
`spinner.yaml` file.
- `disable-server` - **Optional:** Does not install a web server.

## Example Usage

`spinner spin:up name=test path=/abs/path/to/project`

`spinner spin:up test /abs/path/to/project 8.4 --disable-server --disable-node`