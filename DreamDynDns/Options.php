<?php

namespace DreamDynDns;

class Options
{
    /** @var array $config The initial configuration provided. */
    private $config = array();

    /** @var array $paramNames The parameter names that can be provided for each request. */
    private $paramNames = array();

    /** @var array $params The actual parameter values. */
    private $params = array();

    /** @var array $paramSources The ultimate source of a given parameter value ('default', 'auto', 'get' or 'post') */
    private $paramSources = array();

    /** @var array $allowedDomains An array of allowed domains. */
    private $allowedDomains = array();

    /** @var bool $allowGet True to allow GET parameter. Otherwise only POST parameter are accepted. */
    private $allowGet = true;

    /** @var string $password A password that must be supplied to make updates. */
    private $password = null;

    /**
     * @param array $config The configuration array to initialize the configuration from.
     */
    public function __construct($config=array())
    {
        $this->config = $config;
        $this->paramNames = isset($config['paramNames']) && is_array($config['paramNames']) ? $config['paramNames'] : array();
        $this->params = isset($config['defaults']) && is_array($config['defaults']) ? $config['defaults'] : array($config['defaults']);
        // All parameters come from 'default' at this point.
        $this->paramSources = array_fill_keys(array_keys($this->params), 'default');
        $this->allowedDomains = isset($config['allowedDomains']) && is_array($config['allowedDomains']) ? $config['allowedDomains'] : array();
        $this->allowGet = isset($config['allowGet']) ? $config['allowGet'] : true;
        $this->password = isset($config['password']) ? $config['password'] : null;

        // if we're allowing GET parameters, we'll extract any URL parameters provided.
        if($this->allowGet) {
            $this->extractParams($_GET, 'get');
        }

        // Extract and POST parameters provided.
        $this->extractParams($_POST, 'post');

        // If we haven't been given an IP address, try to get ir from $_SERVER['REMOTE_ADDR'].
        if(!Isset($this->params['ip']) || empty($this->params['ip'])) {
            $this->params['ip'] = $_SERVER['REMOTE_ADDR'];
            $this->paramSources['ip'] = 'auto';
        }
    }

    public function checkPassword()
    {
        if(!empty($this->password)) {
            return $this->password == $this->getParam('password');
        } else {
            return true;
        }
    }

    /**
     * Extracts parameters from an array of provided parameters. They must match the names
     * provided in the initial paramNames config value.
     *
     * @param array $from The source array.
     * @param string $source The name of the source.
     */
    private function extractParams($from, $source)
    {
        foreach(array_intersect_key($from, $this->paramNames) as $key => $value) {
            $name = $this->paramNames[$key];
            $this->params[$name] = $value;
            $this->paramSources[$name] = $source;
        }
    }

    /**
     * Gets the value of a given parameter.
     *
     * @param string $param The parameter name
     * @param null $default The default value to return if not set.
     * @return null The value of the parameter.
     */
    public function getParam($param, $default=null)
    {
        return isset($this->params[$param]) ? $this->params[$param] : $default;
    }

    /**
     * Gets the source of a given parameter name.
     *
     * @param string $param The name of the parameter.
     * @return null The source of the parameter ('default', 'auto', 'get', or 'post')
     */
    public function getParamSource($param)
    {
        return isset($this->paramSources[$param]) ? $this->paramSources[$param] : null;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    public function isAllowedDomain($domain)
    {
        foreach($this->allowedDomains as $allowed) {
            //echo '[checking '.$domain.' against '.$allowed.']';

            if(is_callable($allowed) && $allowed($domain)) {
                return true;
            } else{
                $allowed = strtolower($allowed);
                if(strpos($allowed,'.') === 0 && substr_compare($domain, $allowed, -strlen($allowed), strlen($allowed)) === 0) {
                    return true;
                } elseif(strtolower($allowed) == $domain) {
                    return true;
                }
            }
        }

        return false;
    }
}