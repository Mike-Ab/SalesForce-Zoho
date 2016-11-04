<?php
if (!class_exists ('Utilities')){
class Utilities {
    public function setParameter($key, $value, $parameter) {
        if ($parameter === "" || strlen($parameter) == 0) {
            $parameter = $key . '=' . $value;
        } else {
            $parameter .= '&' . $key . '=' . $value;
        }
        return $parameter;
    }

    public function sendCurlRequest($url, $parameter) {
        try {
            /* initialize curl handle */
            $ch = curl_init();
            /* set url to send post request */
            curl_setopt($ch, CURLOPT_URL, $url);
            /* allow redirects */
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            /* return a response into a variable */
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            /* times out after 30s */
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            /* set POST method */
            curl_setopt($ch, CURLOPT_POST, 1);
            /* add POST fields parameters */
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
            /* execute the cURL */
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        } catch (Exception $exception) {
            echo 'Exception Message: ' . $exception->getMessage() . '<br/>';
            echo 'Exception Trace: ' . $exception->getTraceAsString();
        }
    }

		
public function read_stream ($var_name, $empty_value = false) 
{
	$ret = (!empty($_POST[$var_name]) && isset ($_POST[$var_name])) ? $_POST[$var_name] : false;
	if (!$ret) $ret =  (!empty($_GET[$var_name]) && isset ($_GET[$var_name])) ? $_GET[$var_name] : false;
	if (!$ret) return $empty_value;
		return $ret;
		
}
		
		public function get_url($function, $module, $response_type = 'xml'){
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
	
 
}// end of class
}
?>