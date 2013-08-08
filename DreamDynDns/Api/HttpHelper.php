<?php

namespace DreamDynDns\Api;

/**
 * Class HttpHelper
 * @package DreamhostDynDns\Api
 *
 * This is a helper class for making HTTP requests.
 * It  uses CURL if available, otherwise it falls back to using file_get_contents.
 */
class HttpHelper
{
    /** @var bool Allows CURL to make SSL requests when there are no root certs on the machine!  */
    private static $verifySsl = false;

    /**
     * Makes an HTTP request.
     *
     * @param $url string URL to request
     * @param string $method The request method ('GET', 'POST', 'PUT', 'DELETE', etc)
     * @param null $data Any additional data to include in the payload.
     * @param null $headers Additional headers to send.
     * @param int $timeout The request timeout in milliseconds
     * @return bool|string The response string, or false if there was an error.
     */
    public static function request($url, $method='GET', $data=null, $headers=null, $timeout=3000)
    {
        if ( function_exists('curl_init') ) {
            return HttpHelper::request_curl($url, $method, $data, $headers, $timeout);
        } else {
            return HttpHelper::request_file_get_contents($url, $method, $data, $headers, $timeout);
        }
    }

    /**
     * Makes an HTTP request using file_get_contents.
     *
     * @param $url string the URL to request
     * @param string $method The request method ('GET', 'POST', 'PUT', 'DELETE', etc)
     * @param null $data Any additional data to include in the payload.
     * @param null $headers Additional headers to send.
     * @param int $timeout The request timeout in milliseconds
     * @return bool|string The response string, or false if there was an error.
     */
    public static function request_file_get_contents($url, $method='GET', $data=null, $headers=null, $timeout=300)
    {
        $opts =	array(
            'method'  => $method,
            'timeout' => $timeout,
        );

        if (!empty($headers)) {
            $h = array();
            while (list($header, $headerValue) = each($headers)) {
                array_push($h, $header . ': ' . $headerValue);
            }

            $opts['header'] = implode("\r\n",$h);
        }

        if(!empty($data)) {
            if(strtolower($method) == 'post') {
                $opts['content'] = http_build_query($data);
            } else {
                $url = HttpHelper::appendToUrl($url, $data);
            }
        }

        $context = stream_context_create(array('http' => $opts));
        return file_get_contents($url, false, $context);
    }


    /**
     * Makes an HTTP request using CURL.
     *
     * @param $url string the URL to request
     * @param string $method The request method ('GET', 'POST', 'PUT', 'DELETE', etc)
     * @param null $data Any additional data to include in the payload.
     * @param null $headers Additional headers to send.
     * @param int $timeout The request timeout in milliseconds
     * @return bool|string The response string, or false if there was an error.
     */
    public static function request_curl($url, $method='GET', $data=null, $headers=null, $timeout=300)
    {
        $cn = curl_init();

        if(!empty($data)) {
            if(strtolower($method) == 'post' || strtolower($method) == 'put') {
                curl_setopt($cn, CURLOPT_POSTFIELDS, http_build_query($data));
            } else {
                $url = HttpHelper::appendToUrl($url, $data);
            }
        }

        curl_setopt($cn, CURLOPT_URL, $url);
        curl_setopt($cn, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($cn, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($cn, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($cn, CURLOPT_ENCODING, 'gzip,deflate');

        if(!self::$verifySsl) {
            curl_setopt($cn, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($cn, CURLOPT_SSL_VERIFYPEER, 0);
        }

        if (!empty($headers)) {
            $h = array();
            while (list($header, $headerValue) = each($headers)) {
                array_push($h, $header . ': ' . $headerValue);
            }

            curl_setopt($cn, CURLOPT_HTTPHEADER, $h);
        }


        try {
            ob_start();
            if(curl_exec($cn)) {
                return ob_get_clean();
            } else {
                ob_end_clean();
                return false;
            }
        } catch(\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Appends a query string to an existing URL.
     *
     * @param $url string The URL.
     * @param $queryString string The query string to append.
     * @return string The resulting query string.
     */
    public static function appendToUrl($url, $queryString)
    {
        if(is_array($queryString)) {
            $queryString = http_build_query($queryString);
        }
        $separator = (parse_url($url, PHP_URL_QUERY) == NULL) ? '?' : '&';
        return $url.$separator.$queryString;
    }
}