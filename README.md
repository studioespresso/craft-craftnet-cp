# Craftnet CP plugin for Craft CMS 3.x

Basic craftnet integration with a CP interface. 

Note that I made this mostly for myself and that future development will take place if and when I need it. Issues and PR's will be handled on a best-effort basis.

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

You can manage the following settings in your `craftnet-cp.php` file:

- username (optional, also available as setting in CP)
- token (optional, also available as setting in CP)
- plugins (required, contains `handle => label` for each of your plugins.)
- displayNotes (optional, displays notes below plugin license info)

    <?php return [
        'username' => 'you@myawesomeplugins.com',
        'token' => '5tmukfu4x2ld8xm1619oJy8klw17fvDsXsDDft8nk
        'plugins' => [
            'plugin-handle' => 'Plugin Label',
        ],
        'displayNotes' => true
    ];

## Functionality

- Generate one or more licenses for a plugin
- List all sold and generated licenses

Brought to you by [Studio Espresso](https://www.studioespresso.co)
