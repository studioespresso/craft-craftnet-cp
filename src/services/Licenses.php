<?php

namespace studioespresso\craftnetcp\services;

use barrelstrength\craftnetphp\CraftnetClient;
use craft\base\Component;
use Craft;
use studioespresso\craftnetcp\CraftnetCp;
use studioespresso\craftnetcp\models\License;

class Licenses extends Component
{
    public function createLicense(License $license)
    {
        if ($license->count <= 0) {
            return false;
        }

        // Client info
        $username = CraftnetCp::$plugin->getSettings()->username;
        $apiKey = CraftnetCp::$plugin->getSettings()->token;
        $client = new CraftnetClient($username, $apiKey);

        // Generate Licenses
        for ($x = 1; $x <= $license->count; $x++) {
            // @todo - potential bug, response will be overwritten each loop
            $response = $client->pluginLicenses->create([
                'edition' => $license->edition,
                'plugin' => $license->plugin,
                'email' => $license->email,
                'notes' => $license->notes,
                'expirable' => $license->expirable ? true : false,
                'privateNotes' => $license->privateNotes
            ]);
        }

        return $response;
    }
}
