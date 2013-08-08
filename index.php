<?php
defined('DDNS_DEBUG') || define('DDNS_DEBUG', true);

header('Content-Type: application/json');

spl_autoload_register(function ($class) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
    if(is_readable($file)) {
        require_once($file);
    }
});

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

$config = new DreamDynDns\Options(require('config.php'));
$response = new DreamDynDns\Response($config);

if($response->successful) {
    $dns = new DreamDynDns\Api\Dns($config->getParam('apiKey'), $config->getParam('appId', 'DreamhostDDns'));
    $force = $config->getParam('force');
    $findResult = $dns->find($response->domains,$response->recordTypes);

    if($findResult->successful) {
        $toBeRemoved = array();

        // Figure out which records we'll need to remove and which are existing.
        foreach($findResult->data as $foundRecord) {
            $match = false;
            foreach($response->records as $record) {
                if($record->record == $foundRecord['record'] &&
                    $record->type == $foundRecord['type'] &&
                    $record->value == $foundRecord['value']) {

                    $match = true;
                    $record->existing = true;
                }
            }

            if(!$match || $force) {
                $toBeRemoved[] = $foundRecord;
            }
        }

        foreach($toBeRemoved as $record) {
            $removeResult = $dns->remove($record['record'], $record['type'], $record['value']);
            if($removeResult->successful) {
                $response->removed[] = $record;
            } else {
                $warning = new stdClass();
                $warning->type = 'Record not removed';
                $warning->message = $removeResult->data;
                $warning->data = $record;
                $response->warnings[] = $warning;
            }
        }

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

exit(json_encode($response));
