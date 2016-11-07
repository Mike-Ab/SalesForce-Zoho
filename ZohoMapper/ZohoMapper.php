<?php
namespace ZohoMapper;

require __DIR__ .'/vendor/autoload.php';
require __DIR__ . '/catch-zoho-mapper-autoload.php';

use \GuzzleHttp\Client;
use \ZohoMapper\ZohoServiceProvider as Zoho;

class ZohoMapper
{
    private $token;
    
    public function __construct($token) {
        if (!$token) {
            throw new \Exception('Missing Auth Token from Zoho');
        }
        $this->token = $token;
    }
    
    public function insertRecord ($record, $type, $isApproval = false)
    {
        $http = new Client(['verify' => false]);
        $url = Zoho::generateURL('insert', $type);
        $options = [
            'scope'     => 'crmapi',
            'authtoken' => $this->token,
            'newFormat' => 1,
            'wfTrigger' => 'true',
            'xmlData'   => Zoho::generateXML($record, $type)
        ];
        if ($isApproval) {
            $options['isApproval'] = 'true';
        }
        $attempt = $http->request('POST', $url, [
            'form_params' => $options
            ]);
        echo ($attempt->getBody());
    }
    
}

$zoho = new \ZohoMapper\ZohoMapper('ff5196138d9b9112b7fe675a9c6025d0');
$lead = [
    'SMOWNERID'     => '696292000002259143',
    'Email'         => 'it'.time().'@testOnlytest.com.au',
    'First Name'    => 'Guzzlle',
    'Last Name'     => 'testttttt',
    'Mobile'        => '04404040404',
    'Description'   => 'This is a junk lead'
];
$zoho->insertRecord($lead, 'Leads');

/*
 * $parameter = $utilObj->setParameter("scope", $this->SCOPE, $parameter);
            $parameter = $utilObj->setParameter("authtoken", $this->AUTHTOKEN, $parameter);
            $parameter = $utilObj->setParameter("newFormat", 1 , $parameter);
            //$parameter = $utilObj->setParameter("isApproval",'true',$parameter);
            $parameter = $utilObj->setParameter("xmlData", $xml_data , $parameter);
 *  <FL val="SMOWNERID">696292000002259143</FL>
                            <FL val="Email">'.$email.'</FL>
                            <FL val="First Name"><![CDATA['.$fname.']]></FL>
                            <FL val="Last Name"><![CDATA['.$lname.']]></FL>
                            <FL val="Mobile"><![CDATA['.$mobile.']]></FL>
                            <FL val="Description">'.$desc.'</FL>	
 */