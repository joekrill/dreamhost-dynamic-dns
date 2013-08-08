<?php

namespace DreamDynDns;


use DreamDynDns\Api\Dns;

/**
 * Class Response
 * @package DreamDynDns
 *
 * This is the response object we return for any requests.
 */
class Response
{
    /**
     * @var bool True if we were successful; otherwise, false.
     */
    public $successful = true;

    /**
     * @var string An optional information message. If successful == false this is usually the error message.
     */
    public $message;

    /**
     * @var array Additional warnings that may have been raised.
     */
    public $warnings=array();

    /**
     * @var string The IP address provided/extracted
     */
    public $ipAddress = null;

    /**
     * @var string The domain to update.
     */
    public $domains = array();

    /**
     * @var string The record types to update.
     */
    public $recordTypes = null;


    /**
     * @var array An array of records to be added.
     */
    public $records = array();

    /**
     * @var array Any records that were removed.
     */
    public $removed = array();

    public function __construct(Options $config)
    {
        if(!$config->checkPassword()) {
            header('HTTP/1.1 401 Unauthorized');
            exit(json_encode("Unauthorized"));
        } else {
            $this->ip = $config->getParam('ip');
            $this->recordTypes = array_filter(explode(',',$config->getParam('recordType')));

            foreach(array_filter(explode(',',strtolower($config->getParam('domain')))) as $domain) {
                if($config->isAllowedDomain($domain)) {
                    $this->domains[] = $domain;
                }
            }

            // Make sure we've been provided with the basic necessary data.
            if(empty($this->ip)) {
                $this->setError('No IP address specified or found.');
            } elseif(filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) == false) {
                $this->setError("Invalid IPv4 address specified '$this->ip' (source: ".$config->getParamSource('ip').')');
            } elseif(empty($this->domains)) {
                $this->setError('No domains specified.');
            } elseif(empty($this->recordTypes)) {
                $this->setError('No record types specified.');
            }
            // Determine what records we are going to need.
            foreach($this->recordTypes as $recordType) {
                foreach($this->domains as $domain) {
                    $this->records[] = new DnsRecord($domain, $recordType, $this->ip);
                }
            }
        }
    }

    /**
     * Sets the response as being unsuccessful with a given message.
     *
     * @param string  $message The error message.
     */
    public function setError($message)
    {
        $this->message = $message;
        $this->successful = false;
    }

    public function setDryRun($isDryRun)
    {
        if($isDryRun) {
            $this->dryRun = true;
            $this->log = array();
            $this->out('Running in Dry Run mode');
        } else {
            unset($this->log);
            unset($this->dryRun);
        }
    }

    public function isDryRun()
    {
        return isset($this->dryRun) && $this->dryRun;
    }

    public function out($line)
    {
        if(isset($this->log)) {
            $this->log[] = date(DATE_W3C).' '.$line;
        }
    }
}