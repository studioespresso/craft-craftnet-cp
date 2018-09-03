# Craftnet CP plugin for Craft CMS 3.x

Basic craftnet integration with a CP interface

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require studioespresso/craftnet-cp

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Craftnet CP.


## Configuring Craftnet CP
Create a ``craftnet-cp.php`` file in ``/path/to/project/config`` with an array called ``plugins``, which contains ``handle => label`` for each of your plugins.

    <?php return [
        'plugins' => [
            'plugin-handle' => 'Plugin Label',
        ]
    ];

## Functionality

- Generate one or more licenses for a plugin
- List all sold and generated licenses

Brought to you by [Studio Espresso](https://www.studioespresso.co)
