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
use studioespresso\craftnetcp\models\License;

/**
 * License Controller
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
class LicenseController extends Controller
{
    public function actionIndex()
    {
        $plugins = CraftnetCp::$plugin->getSettings()->plugins;

        return $this->renderTemplate('craftnet-cp/index', [
            'plugins' => $plugins
        ]);
    }

    // Public Methods
    // =========================================================================
    public function actionGenerate()
    {
        // License Info
        $license = new License();
        $license->plugin = Craft::$app->request->getRequiredBodyParam('handle');
        $license->email = Craft::$app->request->getRequiredBodyParam('email');
        $license->edition = Craft::$app->request->getBodyParam('edition') ? Craft::$app->request->getBodyParam('edition') : 'standard';
        $license->expirable = Craft::$app->request->getBodyParam('expirable');
        $license->notes = Craft::$app->request->getBodyParam('notes');
        $license->privateNotes = Craft::$app->request->getBodyParam('privateNotes');
        $license->count = Craft::$app->request->getBodyParam('count');

        if (!CraftnetCp::$plugin->licenses->createLicense($license)) {
            $errorMessage = Craft::t('craftnet-cp', 'Unable to generate license(s)');
            Craft::$app->getSession()->setError($errorMessage);
            Craft::error($errorMessage);

            $username = CraftnetCp::$plugin->getSettings()->username;
            $apiKey = CraftnetCp::$plugin->getSettings()->token;
            $client = $this->_getCraftnetClient($username, $apiKey);

            for ($x = 1; $x <= $count; $x++) {
                $response = $client->pluginLicenses->create([
                    'edition' => $edition,
                    'plugin' => $handle,
                    'email' => $email,
                    'notes' => $notes,
                    'expirable' => $expirable ? true : false,
                    'privateNotes' => $privateNotes
                ]);
            }

            Craft::$app->getSession()->setNotice('License(s) generated');
            return $this->redirectToPostedUrl();
        }
    }

    /**
     * @return mixed
     */
    public function actionList(int $page = 1)
    {
        if ($requestedPage = Craft::$app->request->getBodyParam('page')) {
            $page = (int)$requestedPage;
        }

        $username = CraftnetCp::$plugin->getSettings()->username;
        $apiKey = CraftnetCp::$plugin->getSettings()->token;
        $client = $this->_getCraftnetClient($username, $apiKey);

        $response = $client->pluginLicenses->get([
            'page' => $page
        ]);

        $pluginLicenses = $response->getBody()->getContents();

        $results = json_decode($pluginLicenses);

        return $this->renderTemplate('craftnet-cp/list', [
            'data' => $results,
            'page' => $page,
        ]);
    }

    /**
     * Gets a configured instance of the Craftnet API Client
     *
     * @return CraftnetClient
     */
    private function _getCraftnetClient($username, $apiKey)
    {
        $isCraft31 = version_compare(Craft::$app->getVersion(), '3.1', '>=');

        if ($isCraft31) {
            return new CraftnetClient(
                Craft::parseEnv($username),
                Craft::parseEnv($apiKey)
            );
        }

        return new CraftnetClient($username, $apiKey);
    }
}
