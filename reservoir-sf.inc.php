<?php
require ('utilities-class.inc.php');
require ('gcon-v2.inc.php');
require ('SalesforceApi.php');
require ('SfLead.php');
$utilObj = new Utilities;
class Reservoir
{
    public $socket;
    public $sf;
    public $utl;
    public $sfLead;

    public function __construct ()
    {
            $this->socket = new GCONNECT_V2('gqaustra_leads_log');
            $this->sf = new SalesforceApi;
            $this->utl = new Utilities;
            $this->sfLead = new SfLead;
    }

    public function loadSettings ()
    {
            return ($this->socket->fetch_result("SELECT * FROM reservoir_settings WHERE id = 1", true));
    }

    public function startReservoir ($push = true)
    {
            $this->socket->execute("UPDATE reservoir_settings SET is_on = 'yes' WHERE id = 1");
            if ($push){
                    $this->pushAllRecords();
            }
    }

    public function stopReservoir ()
    {
            $this->socket->execute("UPDATE reservoir_settings SET is_on = 'no' WHERE id = 1");
    }

    public function isReservoirOn ()
    {
            $test = $this->socket->fetch_result("SELECT is_on FROM reservoir_settings WHERE id = 1", true);
            if ($test['is_on'] == "yes") return true;
            else return false;
    }

    public function getQueued ()
    {
            $sql = "SELECT id FROM raw_lead WHERE is_synced = 0";
            $count = $this->socket->fetch_result($sql);
            if (!isset ($count)) return 0;
            return count ($count);
    }


    public function pushAllRecords ()
    {
            $records = $this->socket->fetch_result("select * from raw_lead where is_synced = 0 ORDER BY created_on ASC");
            if (isset ($records) && count ($records) > 0){
                    foreach ($records as $record) {
                            $this->pushToZoho($record);
                            sleep(1);
                    }
            }
    }


    public function pushOneRecord ()
    {
            $record = $this->socket->fetch_result("select * from raw_lead where is_synced = 0 AND status = 0 ORDER BY created_on ASC LIMIT 1", true);
            if (isset ($record)){
                    $id = $record['id'];
                    // $this->socket->execute("UPDATE raw_lead SET status = 1 WHERE id = $id");
                    $push = $this->pushToZoho($record);
                    return $push;
            }
            return false;

    }


    public function pushToZoho( $record )
    {
      $info_array = unserialize($record['data']);
      $recordId = $this->insert_lead_in_crm($info_array);
      if ($recordId) {
              $this->socket->execute("update raw_lead set is_synced = 1, status = 2 where id = ". $record['id']);
              // $this->zoho->IFS_submit($info_array);
              $attachment = $record['resume'];
              if ($attachment && $attachment != '') {
                      $this->sfLead->addAttachment($recordId, $attachment, $this->sf);
              }
              return $recordId;
            }else {
                    $this->socket->execute("INSERT INTO failed_raw_lead VALUES (".$record['id'].", '".$record['data']."', ".$record['is_synced'].", '".$record['resume']."', '".$record['id']."')");
                    $this->socket->execute("DELETE FROM raw_lead WHERE id = ". $record['id']);
                    return false;
            }
    }

    public function insert_lead_in_crm ($infoArray)
    {
        $insert = $this->sfLead->generateInsertArray($infoArray);
        try {
            $leadId = $this->sf->createRecord($insert, 'Lead');
            return ($leadId);
            } catch (Exception $ex) {
            echo ($ex->getMessage());
            return false;
        }
    }

}// end of class
