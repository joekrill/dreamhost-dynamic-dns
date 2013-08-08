<?php
/**
 * User: joe
 * Date: 8/8/13
 * Time: 1:32 PM
 */

namespace DreamDynDns;


class DnsRecord {

    public $record;
    public $type;
    public $value;
    public $existing=false;
    public $added=false;

    public function __construct($record, $type, $value, $existing=false, $added=false)
    {
        $this->record = $record;
        $this->type = $type;
        $this->value = $value;
        $this->existing = $existing;
        $this->added = $added;
    }
}