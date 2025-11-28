# Developers guide

This guide will walk you through setting up your development environment, building your project package, enabling remote
installations, and starting your local server for testing.

## Setting up an environment

In your projects root directory go to: `build/dev_env.json`.

1. `release_folder` A location where your package will be saved. **IT MUST BE IN THE ROOT FOLDER OF YOUR PROJECT!**
2. `build_save_format`A format in which your package will be saved ex. `<name>_<version>`. This is pulled from your
   manifest. Supported placeholders are `<name>, <version>, <description>, <author>, <license>`.
3. `address` Remote address of your AWT instance. This is used for quick deployment of your packages.
4. `remote_install_path` Remote path on your AWT instance, where packages will be pushed.
5. `devSecret` A secret key that allows you to "securely" install your package on remote instance.

## How to build your package

### Windows

In your projects root directory simply run `build.bat`.

### Linux

Go to your projects root directory and in terminal type:
`./build.sh`. If it fails in terminal type `sudo chmod +x ./build.sh`. Then run it again with `./build.sh`.

## How to enable remote installation

This setting is intended for development purposes only. It MUST remain FALSE in production environments to ensure
security.

In `awt_config.php` set the following: