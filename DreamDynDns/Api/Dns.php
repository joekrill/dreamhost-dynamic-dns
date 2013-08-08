<?php
namespace DreamDynDns\Api;

/**
 * Class Dns
 * @package DreamDynDns\Api
 *  Encapsultes requests related to Dreamhost DNS API calls.
 */
class Dns extends ApiModule
{
    /**
     * Finds a list of avialable DNS records matching the given criteria.
     *
     * @param string|array $record A record or array of records to filter on.
     * @param string|array $type A record type, or array of types to filter on (A, AAAA, CNAME, MX, etc...).
     * @param string|array $value A record value or array of record values to filter on.
     * @return Result The result of the API call.
     */
    public function find($record=null, $type=null, $value=null)
    {
        $result = $this->request('dns-list_records');

        if($result->successful) {
            // Filtering is done locally. Dreamhost doesn't provide this in their API yet. The
            // call simply returns everything. So we filter on our own.
            if(!empty($result->data) && is_array($result->data)) {
                $result->data = array_filter($result->data, function($resultRecord) use ($record, $type, $value) {
                        //echo $resultRecord['record'];
                        return
                            ( empty($record) || $resultRecord['record'] == $record || (is_array($record) && in_array($resultRecord['record'], $record))) &&
                            ( empty($type) || $resultRecord['type'] == $type || (is_array($type) && in_array($resultRecord['type'], $type))) &&
                            ( empty($value) || $resultRecord['value'] == $value || (is_array($value) && in_array($resultRecord['value'], $value)));
                    });
            }
        }

        return $result;
    }

    /**
     * Adds a DNS record.
     *
     * @param string $record The record to add.
     * @param string $type The record type to add (A, AAAA, CNAME, MX, etc...).
     * @param string $value The value to give the record.
     * @param string $comment An optional comment to associate with the record.
     * @return Result The result of the API call.
     */
    public function add($record, $type, $value, $comment=null)
    {
        return $this->request('dns-add_record', array(
            'record' => $record,
            'type' => $type,
            'value' => $value,
            'comment' => $comment
        ));
    }

    /**
     * Removes a DNS record.
     *
     * @param string $record The record to remove.
     * @param string $type The record type to remove (A, AAAA, CNAME, MX, etc...).
     * @param string $value The value to record to remove.
     * @return Result
     */
    public function remove($record, $type, $value)
    {
        return $this->request('dns-remove_record', array(
            'record' => $record,
            'type' => $type,
            'value' => $value
        ));
    }
}