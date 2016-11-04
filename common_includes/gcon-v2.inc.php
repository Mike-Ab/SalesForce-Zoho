<?php
//////////////////////////////////////////////////////////////////////
//////////////////////// DB Connection class /////////////////////////
//////////////////////////////////////////////////////////////////////
if (!class_exists ('GCONNECT_V2')) {
class GCONNECT_V2 {

	protected $db_host = 'localhost'; // 'mysql-5.gqaustralia.com.au';
	protected $db_username = 'root'; 
	protected $db_pass = '';
	protected $db_name ;	
	protected $EstablishConnection = false;
	public $connected = false;

    function __construct ($database_name = 'leads_log_db')
	{
		$this->db_name = $database_name;
	}


		public function EstablishCon($a,$b,$c, $d){
			$this->EstablishConnection = mysqli_connect("$a", "$b", "$c", "$d");
			if (mysqli_connect_errno())  {
			  echo "Failed to connect to MySQL: " . mysqli_connect_error();
			}
			return $this->EstablishConnection;
			}		

		public function connect ($database = false){
			if ($database === false) $database = $this->db_name;
			$this->connected = $this->EstablishCon($this->db_host,$this->db_username,$this->db_pass, $database);
			if ($this->connected){
				return $this->connected;
				}else{
					return false;
				}
			}
		
		public function DC(){
			mysqli_close($this->connected);
			$this->connected = false;
			}	

		
		public function sanitise($string, $database = false){
				$con = $this->connect($database);
				$return = mysqli_real_escape_string($con, $string);
				$this->DC();
			return $return;
		}
		
	/**
	 * Execute an SQL Query
	 * @param $sql
	 * @param $error_message
	 * @param $database
	 */
	 
	public function execute ( $sql, $error_message = '', $database = false )	
		{
			$con = $this->connect( $database );
			$qry = mysqli_query ( $con, $sql )  or die ( $error_message. ' : '.mysqli_error( $con ) );
			$this->DC();
			return $qry;
		}

	/**
	 * Execute an SQL Query
	 * @param $sql
	 * @param $error_message
	 * @param $database
	 */
	 
	public function executeReturnId ( $sql, $error_message = '', $database = false )	
		{
			$con = $this->connect( $database );
			$qry = mysqli_query ( $con, $sql )  or die ( $error_message. ' : '.mysqli_error( $con ) );
			return mysqli_insert_id( $con );
		}


	/**
	 * Get associated array of an SQL query result
	 * @param $sql_resource
	 */

	public function get_assoc ( $sql_resource , $single_result = false )
		{
			if ( mysqli_num_rows($sql_resource) != 0){
			if ($single_result) return ( mysqli_fetch_assoc( $sql_resource ) );
				while ( $x = mysqli_fetch_assoc( $sql_resource ) ){
						$array[] = $x;		
					}
				return $array;		
				}
			else return NULL;
		}
	
	
	public function fetch_result ( $sql, $single_result = false, $error_message = '' , $database = false)
	{
		return $this->get_assoc($this->execute($sql, $error_message, $database), $single_result);
	}
	
	} // end of class
}