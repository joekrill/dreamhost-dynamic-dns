<?php

namespace DreamDynDns\Api;


/**
 * Class Result
 * @package DreamhostDynDns\Api
 *
 * Represents the result of making a Dreamhost API call.
 * See @link http://wiki.dreamhost.com/API
 *
 * @property bool successful gets whether the request was successful.
 *
 */
class Result
{
    /**
     * @var bool indicates whether the call was successful.
     */
    private $_successful=false;

    /**
     * @var string an optional error message when $_successful is false.
     */
    private $_error=null;

    /**
     * @var object any additional data returned.
     */
    public $data=null;

    /**
     * @param string $response the response received from the API call.
     */
    public function __construct($response)
    {
        if(empty($response)) {
            $this->error = "Empty response received";
        } else {
            $result = unserialize($response);

            if($result === false) {
                $this->error = "Unexpected response format";
            } elseif(!is_array($result)) {
                $this->error = "Unexpected response object";
            } else {
                $this->_successful = (isset($result['result']) && $result['result'] == 'success');
            }

            if(isset($result['data'])) {
                $this->data = $result['data'];
            }
        }
    }

    public function __get($name)
    {
        if($name == 'successful') {
            return $this->_successful;
        } elseif($name == 'error') {
            return !$this->_successful ? $this->_error  : false;
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    public function __set($name, $value)
    {
        if($name == 'successful') {
            $this->_successful = $value;
        } elseif($name == 'error') {
            $this->_error = $value;
            $this->_successful = empty($value);
        }
    }
}