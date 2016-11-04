<?php

require_once ('soapclient/SforcePartnerClient.php');
require_once ('soapclient/SforceEnterpriseClient.php');
require_once ('soapclient/SforceHeaderOptions.php');

class SalesforceApi
{
    /*
     *
     * These values need to be in a config file
     *
     */
    private $wsdFile    = "src/enterprise.wsdl.xml";
    private $username   = "prasanth.pillai@gqaustralia.com.au.gqsanbox";
    private $password   = ";3CVm~U;2ci-";
    private $token      = "WS3XSZb8kEQpu4WraJmdphlb";
    private $queryReturnKeys = ['done', 'records', 'size'];
    private $sessionID  = false;
    private $userID     = false;

    protected $sf;
    protected $sfConnection = false;


    /**
     * Just initiating the SF object
     */
    public function __construct ()
    {
        $this->sf = new SforceEnterpriseClient;
    }

    /**
     * Creates connection and handles OAuth 2 Authentication
     * @return boolean
     * @throws Exception
     */
    private function createConnection ()
    {
        if ($this->sfConnection) {
            return true;
        }
        try {
        $connection = $this->sf->createConnection($this->wsdFile);
        $login = $this->sf->login($this->username, $this->password.$this->token);
        $this->sessionID = $login->sessionId;
        $this->userID = $login->userId;
        $this->sfConnection = true;
        }  catch (Exception $e){
            $this->sfConnection = false;
            throw $e;
        }
    }

    /**
     * Creates ONE contact
     * @param array $opts associative array of fieldname
     * @return string Created Record ID
     */
    public function createRecord (array $opts, $recordType)
    {
        // example $opts = ['FirstName' => 'Test', 'LastName' => 'Last', 'Email' => 'it2@gqaustralia.com.au', 'MobilePhone' => '0403616626'];
        try {
            $this->createConnection();

            $response = $this->sf->create([(object)($opts)], $recordType);
            if ($response[0]->success == true) {
                return($response[0]->id);
            }else {
                // var_dump($response);
                return null;
                //return($response[0]->errors[0]);
            }
        }  catch (Exception $e){
            throw $e;
        }
    }

    /**
     * Gets contact information
     * @param array $fields fields to be selected
     * @param string $recordID
     * @param string $recordType
     */
    public function getRecordInfo (array $fields , $recordID, $recordType)
    {
        try {
        $this->createConnection();
        $response = $this->sf->retrieve(implode(', ',$fields), $recordType, [$recordID]);
        return (array)($response[0]);
        }  catch (Exception $e){
            throw $e;
        }
    }

    /**
     * Updates ONE Record
     * @param array $updates associative array of fieldname => new value
     * @param string $recordID
     * @param string $recordType Description
     */
    public function updateRecord (array $updates, $recordID, $recordType)
    {
        $updates['Id'] = $recordID;
        try {
        $this->createConnection();
        $response = $this->sf->update([(object)($updates)], $recordType);
        return (array)($response[0]);
        }  catch (Exception $e){
            throw $e;
        }
    }

    /**
     * Execute and SQL query on SF
     * @param string $query
     * @return array
     * @throws Exception includes the not found as well
     */
    public function sfQuery ($query)
    {
        try {
        $this->createConnection();
        $response = (array)$this->sf->query($query);
        foreach ($response as $key => $val){
            if (!in_array($key, $this->queryReturnKeys)){
                unset ($response[$key]);
            }
        }
        /* php v5.6
        return (array_filter((array)$response, function ($filterVal)  {
            return in_array($filterVal, $this->queryReturnKeys);
           }, ARRAY_FILTER_USE_KEY));
        */
        return $response;
        }  catch (Exception $e){
            throw $e;
        }
    }
}

/*
 * create records
$opts = ['FirstName' => 'Test', 'LastName' => 'Last', 'Email' => 'it3@gqaustralia.com.au', 'MobilePhone' => '0403616626'];
$create = $sf->createRecord($opts, 'Contact');
*/
/*
echo '<pre>';
$sf = new SalesforceApi();

$details = $sf->getRecordInfo([
    'FirstName', 'LastName', 'Id', 'Email', 'MobilePhone'
],'003p000000EbJngAAF', 'Contact');
var_dump($details);
$updates = ['FirstName' => 'Updated FirstName'];
$update = $sf->updateContact($updates, '003p000000EbJngAAF');
var_dump($update);
$details = $sf->getRecordInfo([
    'FirstName', 'LastName', 'Id', 'Email', 'MobilePhone'
],'003p000000EbJngAAF', 'Contact');
var_dump($details);
*/
/*
echo '<pre>';
$sf = new SalesforceApi();
$query = "Select opportunityId, product2Id, product2.name from OpportunityLineItem where OpportunityId = '006p0000003lEoy' limit 5";
//$query = "SELECT Id, Name from Enrollment__c where Client__c = '003p000000EbJng'";

$result = $sf->sfQuery($query)['records'];
$queryResult = $result[0];
var_dump($queryResult, $queryResult->Product2->Name);
 *
 */
/*
$data = 'a:22:{s:5:"fname";s:5:"Zivan";s:5:"lname";s:7:"Zivanov";s:5:"email";s:18:"zzivanov@gmail.com";s:6:"mobile";s:10:"0452550286";s:4:"desc";s:114:"{{ Resume Attached }}. Zivan can provide us with the following types of evidence . They are after getting Not Sure";s:5:"state";s:23:"3201 Carrum Downs - VIC";s:7:"faculty";s:37:"Hospitality Events Tourism and Travel";s:7:"country";s:9:"Australia";s:6:"zc_gad";s:0:"";s:7:"refcode";s:0:"";s:8:"gclickid";s:0:"";s:7:"keyword";s:0:"";s:7:"voucher";s:2:"No";s:10:"experience";s:18:"more than 5 years ";s:16:"experience_place";s:31:"in both Australia and Overseas ";s:9:"has_quals";s:3:"yes";s:9:"quals_age";s:16:"2 to 5 years old";s:13:"qual_demanded";s:8:"Not Sure";s:12:"heardaboutus";s:5:"Other";s:6:"source";s:5:"fbrpl";s:11:"enquiryTime";s:19:"2016-06-07 23:35:48";s:6:"reason";s:20:"Personal development";}';
$dataArray = unserialize($data);
$attachmentUrl = 'http://s3-ap-southeast-2.amazonaws.com/gqwebsite/uploads/cv/Zivan_Zivanov.pdf';
require 'SfLead.php';
$sfLead = new SfLead;
$lead = $sfLead->generateInsertArray($dataArray);
$test = $sf->createRecord($lead, 'Lead');

if ($lead) {
    $attachment = $sfLead->addAttachment($test, $attachmentUrl, $sf);
}
 *
 */
