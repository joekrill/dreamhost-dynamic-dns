<?php
// Set whether to run in debug mode, which provides more information when there is an error.
defined('DDNS_DEBUG') || define('DDNS_DEBUG', true);

// We'll always return JSON
header('Content-Type: application/json');

// An autoloader function to load our additional classes as needed.
spl_autoload_register(function ($class) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
    if(is_readable($file)) {
        require_once($file);
    }
});

// This will handle any unexpected errors that may occur.
set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
        $response = new stdClass();
        $response->successful = false;
        $response->message = $errstr;


        if(DDNS_DEBUG) {
            $response->error = new stdClass();
            $response->error->num = $errno;
            $response->error->file = $errfile;
            $response->error->line = $errline;
            $response->error->context= $errcontext;
        }

        exit(json_encode($response));
    }
);

// Load the main configuration.
$config = new DreamDynDns\Options(require('config.php'));

// This will be the object we return as a response.
// It contains a `records` property which is an array
// of the DNS records we want based on the config and request parameters.
$response = new DreamDynDns\Response($config);

// The response object will make sure we've been provided with enough information to continue. If not,
// The `successful` property will be false and there's no reason to continue.
if($response->successful) {
    // $dns handles service calls to the Dreamhost DNS API
    $dns = new DreamDynDns\Api\Dns($config->getParam('apiKey'), $config->getParam('appId', 'DreamhostDDns'));
 
    // If $force is true, we'll send an IP address update even if it's not needed (that is, if the
    // current record is already set to that IP address)
    $force = $config->getParam('force');
 
    // Get all the records that match the domains and record types we want to update.
    $findResult = $dns->find($response->domains,$response->recordTypes);

    if($findResult->successful) {
       // Any records that we need to remove will be put here.
       $toBeRemoved = array();

        // Figure out which records we'll need to remove and which are existing.
        foreach($findResult->data as $foundRecord) {
            $match = false;
 
            // Look at each existing record (`$foundRecord`) and see if it matches
            // any of the records that we want (`$response->records`)
            foreach($response->records as $record) {
                if($record->record == $foundRecord['record'] &&
                    $record->type == $foundRecord['type'] &&
                    $record->value == $foundRecord['value']) {

                    // One of our required records matches this existing record.
                    $match = true;
                    
                    // Indicate this record already exists as a DNS entry
                    $record->existing = true;
                }
            }

            // Record does not contain any data we want, or we're forcing an
            // update -- either way it should be removed form the DNS server.
            if(!$match || $force) {
                $toBeRemoved[] = $foundRecord;
            }
        }

        // Now those recods that weren't a match (or all of them if $force === true)
        foreach($toBeRemoved as $record) {
            $removeResult = $dns->remove($record['record'], $record['type'], $record['value']);
            if($removeResult->successful) {
                $response->removed[] = $record;
            } else {
                // Something went wrong trying to remove the record. We won't fail, but
                // we'll create a warning message.
                $warning = new stdClass();
                $warning->type = 'Record not removed';
                $warning->message = $removeResult->data;
                $warning->data = $record;
                $response->warnings[] = $warning;
            }
        }

        // Finaly add the records we want, which aren't existing records (unless $force === true, in which case we
        // add all records.
        foreach($response->records as $record) {
            if($force || !$record->existing) {
                $addResult = $dns->add($record->record, $record->type, $record->value, 'DreamhostDynDns @ '.date(DATE_W3C));

                if($addResult->successful) {
                    $record->added = true;
                } else {
                    $response->setError('DNS Record not added');
                    $warning = new stdClass();
                    $warning->type = 'Record not added';
                    $warning->message = $addResult->data;
                    $warning->data = $record;
                    $response->warnings[] = $warning;
                }
            }
        }
    } else {
        $response->setError('Unable to retrieve existing DNS entries:'.$findResult->error);
    }
}

// Lastly, output eh the response in JSON format.
exit(json_encode($response));
