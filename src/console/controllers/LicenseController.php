<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.github.io/license/
 */

namespace studioespresso\craftnetcp\console\controllers;

use Craft;
use craft\helpers\Console;
use craft\helpers\FileHelper;
use studioespresso\craftnetcp\CraftnetCp;
use studioespresso\craftnetcp\models\License;
use yii\console\Controller;
use yii\console\ExitCode;

class LicenseController extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'generate-license';

    public $email;
    public $plugin;
    public $edition;
    public $expirable;
    public $notes;
    public $privateNotes;
    public $count = 1;

    public $file;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);

        if ($actionID === 'generate') {
            $options[] = 'email';
            $options[] = 'plugin';
            $options[] = 'edition';
            $options[] = 'expirable';
            $options[] = 'notes';
            $options[] = 'privateNotes';
            $options[] = 'count';
        }

        if ($actionID === 'import-csv') {
            $options[] = 'file';
        }

        return $options;
    }

    /**
     * Generate one or more licenses
     */
    public function actionGenerate()
    {
        if (!$this->email) {
            $message = Craft::t("craftnet-cp", "Email attribute required");
            $this->stdout($message);
            return ExitCode::DATAERR;
        }

        if (!$this->plugin) {
            $message = Craft::t("craftnet-cp", "Plugin Handle attribute required");
            $this->stdout($message);
            return ExitCode::DATAERR;
        }

        $license = new License();
        $license->email = $this->email;
        $license->plugin = $this->plugin;
        $license->edition = $this->edition ?? 'standard';
        $license->expirable = $this->expirable ?? false;
        $license->notes = $this->notes ?? null;
        $license->privateNotes = $this->privateNotes ?? null;
        $license->privateNotes = $this->count;

        $this->stdout(PHP_EOL.
            "-- ".Craft::t('craftnet-cp', 'NEW LICENSE')." ----------------------------".PHP_EOL.
            Craft::t('craftnet-cp', "Email: ").$license->email.PHP_EOL.
            Craft::t('craftnet-cp', "Plugin: ").$license->plugin.PHP_EOL.
            Craft::t('craftnet-cp', "Edition: ").$license->edition.PHP_EOL.
            Craft::t('craftnet-cp', "Expirable: ").($license->expirable ? 'Yes' : 'No').PHP_EOL.
            Craft::t('craftnet-cp', "Notes: ").$license->notes.PHP_EOL.
            Craft::t('craftnet-cp', "Private Notes: ").$license->privateNotes.PHP_EOL.
            "-------------------------------------------"
            .PHP_EOL, Console::FG_GREY);

        if (!$this->confirm(Craft::t('craftnet-cp', 'Generate {count} License(s)?', [
            'count' => $this->count
        ]))) {
            $this->stdout(Craft::t('craftnet-cp', 'License generation cancelled.'), Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $this->stdout(Craft::t('craftnet-cp', 'Generating license via Craftnet API ... ').PHP_EOL, Console::FG_YELLOW);

        // Create the license(s)
        if (!CraftnetCp::$plugin->licenses->createLicense($license)) {
            $this->stdout(Craft::t('craftnet-cp', 'Unable to create one or more licenses.').PHP_EOL, Console::FG_RED);
            return ExitCode::DATAERR;
        }

        $this->stdout(Craft::t('craftnet-cp', 'License(s) created.').PHP_EOL, Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Import a CSV file of licenses
     */
    public function actionImportCsv()
    {
        $rows = array_map('str_getcsv', file($this->file));

        if (count($rows) <= 0) {
            $this->stdout(Craft::t('craftnet-cp', 'No rows found in CSV file.'), Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $licenses = [];

        // Grab the header row and remove it
        $headerRows = $rows[0];
        array_shift($rows);

        // Update the array to be an associative array and update the keys to use the header row values
        array_walk($rows, function(&$value, $key) use (&$licenses, $headerRows) {

            $license = new License();

            // Dynamically build the License model from our header row and data rows
            foreach ($value as $index => $attribute) {
                $license->{$headerRows[$index]} = $attribute;
            }

            $licenses[] = $license;
        });
        
        foreach ($licenses as $license)
        {
            // Create the license(s)
            if (!CraftnetCp::$plugin->licenses->createLicense($license)) {
                $this->stdout(Craft::t('craftnet-cp', 'Unable to create ' . $license->count . ' ' . $license->plugin . ' license(s) for ' . $license->email . ' ').PHP_EOL, Console::FG_RED);
            }

            $this->stdout(Craft::t('craftnet-cp', 'Created ' . $license->count . ' ' . $license->plugin . ' license(s) for ' . $license->email . ' ').PHP_EOL, Console::FG_GREEN);
        }

        return ExitCode::OK;
    }
}
