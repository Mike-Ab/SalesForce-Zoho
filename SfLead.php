<?php

/**
 * Field mapper of Salesforce Lead Structer
 *
 * @author Moe
 */
class SfLead {

    public $sfLead;
    private $adwordsMapper;

    public function generateInsertArray ($leadInfoArray)
    {
        $lead = [];
        $lead['FirstName']          = $leadInfoArray['fname'];
        $lead['LastName']           = $leadInfoArray['lname'];
        //$lead['MobilePhone']        = $leadInfoArray['mobile'];  /// ????
        $lead['Phone']        = $leadInfoArray['mobile'];
        $lead['Email']              = $leadInfoArray['email'];
        $lead['Description']        = $leadInfoArray['desc'];
        $lead['State']              = $leadInfoArray['state'];
        $lead['Faculty__c']         = $leadInfoArray['faculty'];
        $lead['Country']            = $leadInfoArray['country'];
        $lead['Heard_About_Us__c']  = $leadInfoArray['heardaboutus'];
        $lead['Referrer__c']        = $leadInfoArray['refcode']; // ??
        $lead['Using_Voucher__c']   = $leadInfoArray['voucher'];

        // $zc_gad = $leadInfoArray['zc_gad']; // ??

        $lead['Google_Click_ID__c'] = $leadInfoArray['gclickid'];
        $lead['GA_Search_Keywords__c'] = $leadInfoArray['keyword'];

        // $lead['Using_Voucher__c'] = $leadInfoArray['experience']; // ??
        $lead['Australian_or_Overseas_Experience__c'] = $leadInfoArray['experience_place'];
        $lead['Other_Qualifications__c'] = $leadInfoArray['has_quals'];
        $lead['Other_Qualifications_Age__c'] = $leadInfoArray['quals_age'];
        $lead['Qualification_Demanded__c'] = $leadInfoArray['qual_demanded'];
        $lead['Using_Voucher__c'] = $leadInfoArray['source'];
        $lead['Reason_For_Qualification__c'] = $leadInfoArray['reason'];

        //$lead['Enquiry_Time__c'] = $leadInfoArray['enquiryTime'];
        $lead['Enquiry_Time__c'] = date ('Y-m-d\TH:i:s+10:00', strtotime($leadInfoArray['enquiryTime']));
        $lead['Faculty__c'] = str_replace ('&', 'and', $faculty);
        $lead['Description'] = str_replace ('&', 'and', $desc);
        $lead['Qualification_Demanded__c'] = str_replace ('&', 'and', $demanded_qual);
        $lead['Other_Qualifications__c'] = str_replace ('&', 'and', $has_quals);

        $lead['LeadSource'] = $leadInfoArray['source'];


        $lead['LeadSource'] = isset($_SESSION['lead_source'])? $_SESSION['lead_source'] : 'Online Advertising' ;
        if ($reasonToQualify != ''){
            $lead['Description'] .= " They want to get a qualification to $reasonToQualify.";
        }
        $source = isset ($leadInfoArray['source']) ? $leadInfoArray['source'] : '';
        if (isset ($source) ) {
            require_once ('common_includes/AdwordsMapper.php');
            $this->adwordsMapper = new AdwordsMapper;
            $lead['Specific_Source__c'] = $this->adwordsMapper->getParam($source);
        }else {
            $lead['Specific_Source__c'] = 'Website Direct';
        }
        $lead['Company'] = 'Unknown'; // ???? why is that manditory ?
        return $lead;
   }

   public function addAttachment ($leadID, $attachmentUrl, $sfObject = false)
   {
        if (!$sfObject){
           require_once 'SalesfroceApi.php';
           $sfObject = new SalesforceApi;
        }
        $parts = explode ('/', $attachmentUrl);
        $rev = array_reverse  ($parts);
        $fileBaseName = $rev[0];
        $filebase = dirname(__FILE__).'/tmp/';
        if (!is_file ($filebase.$fileBaseName) && !file_exists ($filebase.$fileBaseName)){
            $file = fopen ($filebase.$fileBaseName , 'w');
            $x = file_get_contents ($attachmentUrl);
            fwrite ($file, $x);
            fclose ($file);
        }
        $record = [
            'Body' => base64_encode((file_get_contents($attachmentUrl))),
            'Name' => $fileBaseName,
            'ParentId' => $leadID
        ];
        try {
            $attachmentId = $sfObject->createRecord($record, 'Attachment');
            return $attachmentId;
        } catch (Exception $ex) {
            var_dump($ex->getMessage());
        }
   }
}
