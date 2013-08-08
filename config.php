<?php
/**
 * This is the configuration file for the DreamDynDns scripts.
 *
 * At the very least, you'll need to populate 'allowedDomains' and 'apiKey'.
 */
return array(
    // Set default here. See 'paramNames' for how to
    // override or set these on each request.
    'defaults' => array(
        'apiKey' => 'MY_DREAMHOST_API_KEY', // Put your Dreamhost API Key here!!! (get one {@link https://panel.dreamhost.com/index.cgi?tree=home.api})
        'recordType' => 'A', // Multiple types can be specified with a comma delimited list.
        //'domain' => 'example.com',
        //'force' => true, // This would make the update regardless of whether it's necessary, every time.
        //'appId' => 'myAppId' // Optional Application ID used when making API requests.
        //'ip' => '127.0.0.1' // This kind of defeats the point of this whole script, but it's possible!
    ),

    'password' => 'mysupersecretpassword', // Set a password that must be specified to make an update.

    /** An array of allowed domains.
     *
     * This can included:
     *   - Explicit domain names
     *   - Explicit domain names preceeded by a period, which will allow any subdomains as well.
     *   - A function that takes a single parameter and validates it as an acceptable domain name.
     */
    'allowedDomains' => array('example.com'),
    //'allowedDomains' => array('.example.com'), // Allow example.com and any sub-domain of example.com
    //'allowedDomains' => array('example.com'), // Allow ONLY example.com
    //'allowedDomains' => array('home.example.com', 'example.net), // Allow ONLY home.example.com AND example.net
    //'allowDomains' => array(function($domain) { return true; }), // Allow ALL domains.

    /** True to allow GET requests. Otherwise
     * only POST requests will work. */
    'allowGet' => true,
    //'allowGet' => false, // Requests must be made using POST. Or, all parameters must be specified in 'defaults', IP can be obtained using $_SERVER['REQUEST_ADDR'] if not specified.

    /**
     * An array of parameters that can be specified on each request.
     * Each unique parameter name (key) should be mapped to a valid
     * parameter name:
     *      apiKey
     *      ip
     *      domain
     *      force
     *
     * An array of parameters mapped to their actual
     * parameter names. This allow parameter names to be overridden
     * easily. To disable the ability to set a parameter on each request,
     * set it to empty (null, false, or '').
     *
     * For instance, to allow providing ?myip=127.0.0.1 in your request
     * instead of ?ip=127.0.0.1, you could change the paramNames 'ip'
     * value this way:
     *      'ip' => 'myip'
     */
    'paramNames' => array(
        'ip' => 'ip', // Comment this out to force IP addresses to be obtained using using $_SERVER['REMOTE_ADDR']
        'domain' => 'domain',
        //'apiKey' => 'apiKey', // Comment out to not allow apiKey specified per-request.
        'force' => 'force',
        'recordType' => 'recordType',
        'password' => 'password',
        //'rectype' => 'recordType', // This will allow ?rectype=AAAA or similar in the request.
    )
);