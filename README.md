# box, by Hyperf

Box is committed to helping improve the programming experience of Hyperf applications, managing the PHP environment and related dependencies, providing the ability to package Hyperf applications as binary programs, and also providing reverse proxy services for managing and deploying Hyperf applications.

## This is still an early experimental version, have fun ~

You could download the latest builded `box` binary file from Github Actions artifact of this project.    
Click [here](https://github.com/hyperf/box/actions) to download ~   

Please notice that box **ONLY** supports for **Swow**, but **NOT** Swoole, so your Hyperf application should created by [hyperf/swow-skeleton](https://github.com/hyperf/swow-skeleton) or other else swow skeleton.

### Usage

#### Install box

##### Mac

```base
wget https://github.com/hyperf/box/releases/download/v0.0.3/box_php8.1_x86_64_macos -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```

##### Linux

```base
wget https://github.com/hyperf/box/releases/download/v0.0.3/box_php8.1_x86_64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```

#### Init Github Access Token

Box needs a Github Access Token to request github api, to retrieve the versions of the package.   

1. [Create Github Access Token](https://github.com/settings/tokens/new)， the `workflow` scope have to be selected.
2. Run `box config set github.access-token <Your Token>` to init the token.
3. Have fun ~

### Commands

- `box get pkg@version` to install the package from remote automatically, `pkg` is the package name, and `version` is the version of package, `box get pkg` means to install the latest version of pkg, for example, run `box get php@8.1` to install the PHP 8.1, run `box get composer` to install the latest composer bin
- `box build-prepare` to get ready for `build` and `build-self` command
- `box build-self` to build the `box` bin itself
- `box build <path>` to build a Hyperf application into a binary file
- `box config list` to dump the config file
- `box config get <key>` to retrieve the value by key from config file
- `box config set <key> <value>` to set value by key into the config file
- `box config unset <key>` to unset the config value by key
- `box config set-php-version <version>` to set the current PHP version of box, available value: 8.0 | 8.1
- `box config get-php-version <version>` to get the current PHP version of box
- `box start -u <upsteamHost:upstreamPort>` to start a proxy HTTP server for the upstream server
- `box php <argument>` to run any PHP command via current PHP version of box
- `box composer <argument>` to run any Composer command via box, the version of the composer bin depends on the last executed `get composer` command
- `box php-cs-fixer <argument>` to run any php-cs-fixer command via box, the version of the composer bin depends on the last executed `get php-cs-fixer` command
- `box version` to dump the current version of the box bin
