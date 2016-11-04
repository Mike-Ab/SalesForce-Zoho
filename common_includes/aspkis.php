<?php

require_once("infusionsoft_api.php");



function send_request($fn, $ln, $email, $mobile, $domain, $lead_source){
	global $infusionsoft;
	
	$infusionsoft = new Infusionsoft_4578( 'nd220', '8a3160b5f73ba3a14b2ed3eb434b1581390f8d6109b616fb514bb839d9d378eb' );
	
	$addfields = array('FirstName'=>$fn, 'LastName'=>$ln, 'Email'=>$email, 'Phone1'=>$mobile,  'Website'=>$domain, 'LeadSource'=>$lead_source);
	$id = $infusionsoft->data('add','Contact',$addfields);
		
	$result =$infusionsoft->contact('addToGroup',$id, 220); //tag “S8”
	
	$optin =$infusionsoft->APIEmail('optIn',$email, 'Opted in on website');
	
	echo "$id  optin=$optin";
}

function handle_request(){
	if(isset($_POST['Email']) || isset($_GET['Email'])){
		$fn = $_REQUEST['FirstName'];
		$ln = $_REQUEST['LastName'];
		$email = $_REQUEST['Email'];
		$mobile = $_REQUEST['Mobile'];
		$domain = $_REQUEST['Domain'];
		$lead_source = "New FSR V2";
		
		send_request($fn, $ln, $email, $mobile, $domain, $lead_source);
		
		
	}else{
		echo "Invalid Request";
	}
}

handle_request();
exit;

?>

