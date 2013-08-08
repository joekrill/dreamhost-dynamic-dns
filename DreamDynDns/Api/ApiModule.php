<?php

namespace DreamDynDns\Api;

/**
 * Class Module
 * @package DreamhostDynDns\Api
 */
abstract class ApiModule
{
    /**
     * @var String The Dreamhost API Key (Generate on at {@link https://panel.dreamhost.com/index.cgi?tree=home.api}).
     */
    private $apiKey;

    /**
     * @var string a unique prefix to pass along with API requests.
     */
    public $uniquePrefix = '';

    /**
     * @var String The Dreamhost API base URL.
     */
    public $baseUrl = 'https://api.dreamhost.com/';

    /** @var string How to make API requests ('POST', 'GET', etc) */
    public $requestMethod = 'POST';

    /**
     * @param $apiKey string The API key to use.
     * @param string $uniquePrefix A unique prefix to use for request IDs.
     */
    public function __construct($apiKey, $uniquePrefix='')
    {
        $this->setApiKey($apiKey);

        if(isset($uniquePrefix)) {
            $this->uniquePrefix = $uniquePrefix;
        }
    }

    /**
     * Sets the API key.
     *
     * @param $apiKey string The new API key.
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Makes a generic API request.
     *
     * @param string $cmd The name of the command
     * @param array $params Additional parameters to send with the request.
     * @return Result The result of the request.
     */
    protected function request($cmd, $params=array())
    {
        return new Result(HttpHelper::request($this->baseUrl, $this->requestMethod,
            array_merge(
                array(
                    'cmd' => $cmd,
                    'key' => $this->apiKey,
                    'format' => 'php',
                    'unique_id' => uniqid($this->uniquePrefix, true)
                ),
                $params
            )
        ));
    }

}