<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of zoho
 * 
 */
 
 require ('s3.inc.php');
 require_once("api/infusionsoft_api.php");
 $infusionsoft = new Infusionsoft_4578( 'nd220', '8a3160b5f73ba3a14b2ed3eb434b1581390f8d6109b616fb514bb839d9d378eb' );
 
 if (!class_exists ('ZOHO')){
class ZOHO {
	
    const VERSION = "1.1.3";
    protected $AUTHTOKEN = "ff5196138d9b9112b7fe675a9c6025d0";
    protected $SCOPE = "crmapi";

    public $adwords;
	
	private $s3;

    ///////////////////////////////////// Lead Entry //////////////////////////////////////////////////

	
	public function __construct ()
	{
		$this->s3 = new GQwebsiteS3;
		
	}


    public function IFS_submit($infoArray)
    {
		$result = $this->send_request($infoArray);
	}





	public function send_request($lead_info_array){
	global $infusionsoft;

	
	$addfields = array( 'FirstName'  =>$lead_info_array['fname'], 
	                    'LastName'   =>$lead_info_array['lname'], 
						'Email'      =>$lead_info_array['email'], 
						'Phone1'     =>$lead_info_array['mobile'],  /* 'Website'=>$domain, */ 
						'LeadSource' =>$lead_info_array['source'],
						'_Industry'   =>$lead_info_array['faculty'],
						'_QualificationDemanded' => $lead_info_array['qual_demanded'],
						'_WhyQualification' => $lead_info_array['reason'],
						'_YearsofExperience' => $lead_info_array['experience'],
						'_AgeGroup'   => '',
						'_Learning'   => '',
						'_StudyLength' => '',						
                        '_HowHeard' => $lead_info_array['heardaboutus']    
						);
						
						
						
	$id = $infusionsoft->data('add','Contact',$addfields);
		
	$result =$infusionsoft->contact('addToGroup',$id, 92); // new lead
	
	$optin =$infusionsoft->APIEmail('optIn',$email, 'Opted in on website');
	
	//	echo "$id  optin=$optin";
	return $id;
}




    public function insert_lead_in_crm($lead_info_array){

            //////////////////////// Prepare the Data ////////////////////////
            $fname 	= $lead_info_array['fname'];
            $lname 	= $lead_info_array['lname'];
            $mobile = $lead_info_array['mobile'];
            $email 	= $lead_info_array['email'];
            $desc 	= $lead_info_array['desc'];
            $state  = $lead_info_array['state'];
            $faculty= $lead_info_array['faculty'];
            $country = $lead_info_array['country'];
            $source_desc = $lead_info_array['heardaboutus']; // new update
            $referral_code = $lead_info_array['refcode']; // new update
            $vouhcer = $lead_info_array['voucher'];

            $zc_gad = $lead_info_array['zc_gad'];

            $gclickid = $lead_info_array['gclickid'];
            $keyword = $lead_info_array['keyword'];

            $experience = $lead_info_array['experience'];
            $experience_place = $lead_info_array['experience_place'];
            $has_quals = $lead_info_array['has_quals'];
            $quals_age = $lead_info_array['quals_age'];
            $demanded_qual = $lead_info_array['qual_demanded'];
            $source = $lead_info_array['source'];
            $heardaboutus = $lead_info_array['heardaboutus'];
			
			$reasonToQualify = $lead_info_array['reason'];
             $enquiryTime = $lead_info_array['enquiryTime'];
			$faculty = str_replace ('&', 'and', $faculty);
			$desc = str_replace ('&', 'and', $desc);
			$demanded_qual = str_replace ('&', 'and', $demanded_qual);
			$has_quals = str_replace ('&', 'and', $has_quals);


              $lead_source = isset($_SESSION['lead_source'])? $_SESSION['lead_source'] : 'Online Advertising' ;
			  if ($reasonToQualify != ''){
				  $desc .= " They want to get a qualification to $reasonToQualify.";
			  }
//            $specifica_source = isset($_SESSION['specific_source']) ? $_SESSION['specific_source'] : NULL ;
//            if ($specifica_source === NULL ) {$specifica_source = isset($_SESSION['in_depth_Source']) ? $_SESSION['in_depth_Source'] : NULL ; $indepth_source = '';}
//            else{
//            $indepth_source = isset($_SESSION['in_depth_Source']) ? $_SESSION['in_depth_Source'] : NULL ;}
            //$source_desc = isset($_SESSION['source_description']) ? $_SESSION['source_description'] : NULL ;
			
            if ($specifica_source == NULL) {
                if (isset ($source) ) {
                    require_once ('common_includes/AdwordsMapper.php');
                    $this->adwordsMapper = new AdwordsMapper;
                    $specifica_source = $this->adwordsMapper->getParam($source);
                }else {
                    $specifica_source = 'Website Direct';
                }
                $source = "";
            }
			
            //////////////////////// PREPARE THE XML //////////////////////////
            $xml_data = '
            <Leads>
            <row no="1">
                            <FL val="SMOWNERID">696292000002259143</FL>
                            <FL val="Email">'.$email.'</FL>
                            <FL val="First Name"><![CDATA['.$fname.']]></FL>
                            <FL val="Last Name"><![CDATA['.$lname.']]></FL>
                            <FL val="Mobile"><![CDATA['.$mobile.']]></FL>
                            <FL val="Description">'.$desc.'</FL>	
                            <FL val="Post Code">'.$state.'</FL>
                            <FL val="Faculty">'.$faculty.'</FL>
                            <FL val="Country">'.$country.'</FL>
                            <FL val="Lead Source">'.$lead_source.'</FL>
                            <FL val="Specific Source">'.$specifica_source.'</FL>
                            <FL val="In-Depth Source">'.$indepth_source.'</FL>
                            <FL val="Source Description">'.$source_desc.'</FL>
                            <FL val="zc_gad">'.$zc_gad.'</FL>				
                            <FL val="GCLID">'.$gclickid.'</FL>		
                            <FL val="Google Click ID">'.$gclickid.'</FL>
                            <FL val="GA Search Keywords">'.$keyword.'</FL>
                            <FL val="Keyword">'.$keyword.'</FL>

                            <FL val="Referral Code">'.$referral_code.'</FL>

                            <FL val="Using Voucher">'.$vouhcer.'</FL>

                            <FL val="The Qualification Name">'.$demanded_qual.'</FL>
                            <FL val="Years of Experience">'.$experience.'</FL>
                            <FL val="Australian or Overseas Experience">'.$experience_place.'</FL>
                            <FL val="Other Qualifications">'.$has_quals.'</FL>
                            <FL val="Other Qualifications Age">'.$quals_age.'</FL>
                            <FL val="Heard About Us">'.$heardaboutus.'</FL>
							<FL val="Enquiry Time">'.$enquiryTime.'</FL>
							<FL val="Reason For Qualification">'.$reasonToQualify.'</FL>



                </row>
            </Leads>
                                    ';
			//var_dump($xml_data);
            $xml_data = str_replace('&', 'and', $xml_data);
            global $utilObj;
            $parameter = "";
            $parameter = $utilObj->setParameter("scope", $this->SCOPE, $parameter);
            $parameter = $utilObj->setParameter("authtoken", $this->AUTHTOKEN, $parameter);
            $parameter = $utilObj->setParameter("newFormat", 1 , $parameter);
            //$parameter = $utilObj->setParameter("isApproval",'true',$parameter);
            $parameter = $utilObj->setParameter("xmlData", $xml_data , $parameter);
            $parameter = $utilObj->setParameter("wfTrigger", 'true' , $parameter);
            /***************** make the call *********/
            $response = $utilObj->sendCurlRequest($this->get_url('ins','Leads','json'), $parameter);
		//	var_dump($response);
            $id_array = json_decode ($response, true);
            $recordId = $id_array["response"]["result"]['recorddetail']['FL'][0]['content'];
            
            if ($id_array["response"]["result"]['message'] == 'Record(s) added successfully'){
                return $recordId;
            }else {
                return false;
            }

    }


            private function get_url($function, $module, $response_type = 'xml'){
            switch ($function){
                            case 'get':
                            $url = 'https://crm.zoho.com/crm/private/'.$response_type.'/'.$module.'/getRecords';
                            break;

                            case 'del':
                            $url = 'https://crm.zoho.com/crm/private/'.$response_type.'/'.$module.'/deleteRecords';
                            break;

                            case 'ins':
                            $url = 'https://crm.zoho.com/crm/private/'.$response_type.'/'.$module.'/insertRecords';
                            break;

                            case 'upd':
                            $url = 'https://crm.zoho.com/crm/private/'.$response_type.'/'.$module.'/updateRecords';
                            break;

                            default :
                            die ("INVALID OPERATION / FUNCTION");
                    }
                    return $url;
            }

            /*
            get the Resume
            =======================================================*/
			
			/**
			 * This function is depricated and now using amazon S3
			 *

            public function save_cv (){

            if (isset($_FILES["cv"]) && !empty($_FILES["cv"])) {
                    $myFile = $_FILES["cv"];

                    if ($myFile["error"] !== UPLOAD_ERR_OK) {
                            echo $myFile["error"];
                            echo "<p>An error occurred.</p>";
                            exit;
                    }

                    // ensure a safe filename
                    $name = preg_replace("/[^A-Z0-9._-]/i", "_", $myFile["name"]);

                    // don't overwrite an existing file
                    $i = 0;
                    $parts = pathinfo($name);
                    while (file_exists(UPLOAD_DIR . $name)) {
                            $i++;
                            $name = $parts["filename"] . "-" . $i . "." . $parts["extension"];
                    }
                    // preserve file from temporary directory
                    $success = move_uploaded_file($myFile["tmp_name"],
                            UPLOAD_DIR . $name);
                    if (!$success) {
                            echo "<p>Unable to save file.</p>";
                            return false;
                    }else {
                    // set proper permissions on the new file
                            chmod(UPLOAD_DIR . $name, 0644);
                            return (UPLOAD_DIR . $name);
                            }
                    }
            }
			
			*/
			// V2 of save_file ()
			
			public function save_cv (){
				
				if (isset($_FILES["cv"]) && !empty($_FILES["cv"])) {
                    $myFile = $_FILES["cv"];
                    if ($myFile["error"] !== UPLOAD_ERR_OK) {
                            echo $myFile["error"];
                            echo "<p>An error occurred uploading your file.</p>";
                            exit;
                    }

                    // ensure a safe filename
                    $name = preg_replace("/[^A-Z0-9._-]/i", "_", $myFile["name"]);
					$return = $this->s3->uploadFile($myFile['name'], $myFile['tmp_name'], $myFile['type']);
					return $return;
					
                    }
			}
			
			/**
			 * Depricated 
			 *
			 *
            public function attach_resume($lead_crm_id, $resume_filename){

                    $recordId = $lead_crm_id;
                    $resume_filename = '@/'.$resume_filename;
                    $url = "https://crm.zoho.com/crm/private/xml/Leads/uploadFile?authtoken=".$this->AUTHTOKEN."&scope=crmapi";
                    $post=array("id"=>$recordId,"content"=>$resume_filename);

                    //================= start curl ===================
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch,CURLOPT_URL,$url);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
                    $result = curl_exec($ch);
                    curl_close($ch);
       
                    }
					*/
					
		public function attach_resume($lead_crm_id, $resume_filename){			
				$parts = explode ('/', $resume_filename);
				$rev = array_reverse  ($parts);
				$fileBaseName = $rev[0];		
				$filebase = dirname(__FILE__).'/tmp/';
                $recordId = $lead_crm_id;				
				if (!is_file ($filebase.$fileBaseName) && !file_exists ($filebase.$fileBaseName)){
					$file = fopen ($filebase.$fileBaseName , 'w');
					$x = file_get_contents ($resume_filename);
					fwrite ($file, $x);
					fclose ($file);					
				}
                $resume_filename = '@/'.$filebase.$fileBaseName;
                $url = "https://crm.zoho.com/crm/private/json/Leads/uploadFile?authtoken=ff5196138d9b9112b7fe675a9c6025d0&scope=crmapi";
				$post=array("id"=>$recordId,"content"=>$resume_filename);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				$result = curl_exec($ch); 
				curl_close($ch);			
				unlink ($filebase.$fileBaseName);       		

			}				
		
} // end of class
 }