# DreamDynDns

DreamDynDns is a script and set of classes to allow you to update your Dreamhost DNS entries dynamically. That is, use Dreamhost as a DynamicDNS provider.

## Features
* Highly customizable, but easily configurable.
* Password protectable.
* Update multiple domain names and/or record types at once.
* Customizable domain white-list

##Installation
Deploy files to your web server (not the one you'll be updating the IP address for!)

`git clone https://github.com/joekrill/dreamhost-dynamic-dns.git /var/www/dyndns`

Modify the config.php to suit your needs. At the very least:

* Update the 'allowedDomains' configuration options to allow the domain(s) you want to be able update.
* Change the 'password' to the password you want to use, or leave empty to disable.
* Set 'defaults'=>'apiKey' value to the API key you [generate at Dreamhost](https://panel.dreamhost.com/index.cgi?tree=home.api). You probably wwant to limit this API key to only DNS related calls.

## Usage

Once installed, you can update your DNS by visiting a URL like this (assuming you've installed the script on www.example.com and you're updating the DNS for the domain mydomain.com):

> http://www.example.com/dyndns/index.php?ip=203.0.113.42&domain=mydomain.com

For your Tomato-powered router, choose "Custom URL" and enter something like this:

> http://www.example.com/dyndns/index.php?ip=@IP&domain=mydomain.com

## Configuration

The config.php file should return an array of configuration options.  The following configuration sections are supported:

### defaults
This is an array of key => value pairs of default values that will be used. These can optionally be overriden on each request (if a name is provided in the 'paramNames' section):

* __apiKey__ -  your dreamhost API key. Get one [here](https://panel.dreamhost.com/index.cgi?tree=home.api). It's a good idea to limit this to only DNS-related API calls. 
* __recordType__ - the default record type to update. This can be a comma delimited list of multiple types ('A,MX').
* __domain__ - the domain to update. 
* __force__ - when the DNS record already exists with the specified IP address, it normally is left as is and not updated. if this is set to true, it will force the DNS records to be removed and readded.
* __appId__ - Dreamhost API requests can be accompanied by a unique ID. If provided, this will prefix that unique ID.
* __ip__ - the IP address to update the records with. It doesn't really make sense to provide a default here. But if you really want to, the option is available.

### password
If this is set, this password will be required as a parameter on each request.

### allowedDomains
This is a whitelist of domains whose DNS records will be allowed to be modified. This should be an array, and each value of the array can be specified in the following ways:

* An explicit domain name. i.e. 'example.com'. This allows only example.com domain records to be modified.
* A gloval domain name. Prefix the domain name with a period. i.e. '.example.com'. This will allow example.com and any subdomains under example.com to be modified.
* A function. If function is supplied, it will be called with a single parameter: the domkain name to validate, and should return true if the domain name is acceptable, or false if it is not.
 
### allowGet
If this is set to `false` any parameters MUST be passed using a POST request. URL parameters will be ignored.

### paramNames
This allows you to define the parameter names for passing values on each request. It is an array where each key is the parameter name to accept, and it's value is the configuration option it will apply to (see the defaults section above).

For example, if you wanted to be able to pass a URL where theIP address is specified with a URL parameter of `myIpAddress` instead of simply `ip`, you could change it's parameter name like this:


    paramNames' => array(
        'ipAddress' => 'ip',
        'domain' => 'domain'
    );

To disable allowing specific parameters to be specified in each request, simply omit them here!

## License

MIT