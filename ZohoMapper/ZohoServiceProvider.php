<?php

namespace ZohoMapper;

require __DIR__.'/vendor/autoload.php';
/**
 * Description of ZohoServiceProvider
 *
 * @author mohammada
 */
class ZohoServiceProvider 
{
    /**
     * Generate the API endpoing needed for the certain API function
     * 
     * @param string $function
     * @param string $module
     * @param type $response_type
     * @return string the URL for the API call to be made
     */
    public static function generateURL($function, $module, $response_type = 'json')
    {
        switch ($function){
            case 'get':
            $url = 'https://crm.zoho.com/crm/private/'.$response_type.'/'.$module.'/getRecords';
            break;
            case 'delete':
            $url = 'https://crm.zoho.com/crm/private/'.$response_type.'/'.$module.'/deleteRecords';
            break;
            case 'insert':
            $url = 'https://crm.zoho.com/crm/private/'.$response_type.'/'.$module.'/insertRecords';
            break;
            case 'update':
                $url = 'https://crm.zoho.com/crm/private/'.$response_type.'/'.$module.'/updateRecords';
            break;
        }
        return $url;
    }
    
    /**
     * Generate the XML from accociated array with the keys/values 
     * 
     * @param array $data
     * @param string $recordType
     * @return string
     */
    public static function generateXML(array $data, $recordType)
    {
        $xml = '';
        $index = 0;
        $xml .= "<$recordType>";
        $xml .= "<row no='".++$index."'>";
        foreach ($data as $key => $val){
            $xml .= "<FL val='$key'><![CDATA[$val]]></FL>";
        }
        $xml .= "</row>";
        $xml .= "</$recordType>";
        return $xml;
    }
}
