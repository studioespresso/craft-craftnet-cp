<?php
/**
 * Craftnet CP plugin for Craft CMS 3.x
 *
 * Basic craft net integration with a CP interface
 *
 * @link      https://www.studioespresso.co
 * @copyright Copyright (c) 2018 Studio Espresso
 */

namespace studioespresso\craftnetcp\controllers;

use barrelstrength\craftnetphp\CraftnetClient;
use studioespresso\craftnetcp\CraftnetCp;

use Craft;
use craft\web\Controller;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Studio Espresso
 * @package   CraftnetCp
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Public Methods
    // =========================================================================
    public function actionGenerate()
    {
        $handle = Craft::$app->request->getRequiredBodyParam('handle');
        $email = Craft::$app->request->getRequiredBodyParam('email');
        $edition = Craft::$app->request->getBodyParam('edition') ? Craft::$app->request->getBodyParam('edition') : 'standard';
        $notes = Craft::$app->request->getBodyParam('notes');
        $privateNotes = Craft::$app->request->getBodyParam('privateNotes');
        $count = Craft::$app->request->getBodyParam('count');

        $username = CraftnetCp::$plugin->getSettings()->username;
        $apiKey = CraftnetCp::$plugin->getSettings()->token;
        $client = new CraftnetClient($username, $apiKey);

        for ($x = 1; $x <= $count; $x++) {
            $response = $client->pluginLicenses->create([
                'edition' => $edition,
                'plugin' => $handle,
                'email' => $email,
                'notes' => $notes,
                'privateNotes' => $privateNotes
            ]);
        }

        return;
    }

    /**
     * @return mixed
     */
    public function actionList()
    {
        $username = CraftnetCp::$plugin->getSettings()->username;
        $apiKey = CraftnetCp::$plugin->getSettings()->token;
        $client = new CraftnetClient($username, $apiKey);

        $response = $client->pluginLicenses->get();

        $pluginLicenses = $response->getBody()->getContents();

        $results = json_decode($pluginLicenses);
        return $this->renderTemplate('craftnet-cp/list', ['data' => $results]);


    }
}
