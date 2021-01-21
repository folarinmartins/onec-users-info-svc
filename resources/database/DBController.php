<?php
namespace database;

use helper\Utility;
use mysqli_result;

class DBController{
	private $conn;
	const CACHE_KEY = 'query';
	private string $query;
	function __construct(){
		$this->conn = $this->connectDB();
		$this->query = '';
	}
	function connectDB(){
		static $connection;

		if(!isset($connection)){
			$connection = mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_DBNAME);
		}
		if($connection === false){
			return \mysqli_connect_error();
		}
		return $connection;
	}
	function getConnection(){
		return $this->conn;
	}
	/** @return void  */
	function listFields(){
		global $dbController;
		$result = $dbController->getResultSet("SHOW COLUMNS FROM sometable");
		// if (!$result) {
		// 	echo 'Could not run query: ' . 'mysql_error()';
		// 	exit;
		// }
		// if ($dbController->affectedRows() > 0) {
			// while ($row = mysql_fetch_assoc($result)) {
				print_r($result);
			// }
		// }
	}
	/**
	 * @param mixed $query 
	 * @param bool $debug 
	 * @return mysqli_result|bool 
	 */
	function runQuery(string $query,$debug=false){
		$this->query = $query;
		$ret =  mysqli_query($this->conn,$query);
		if($debug){
			Utility::log($query);
			Utility::log($this->affectedRows());
		}
		return $ret;
	}
	function getQuery():string{
		return $this->query;
	}
	function affectedRows(){
		return mysqli_affected_rows($this->conn);
	}
	/**
	 * @param string $query 
	 * @param bool $debug 
	 * @param bool $skipCache 
	 * @return array 
	 */
	function getResultSet(string $query, bool $debug=false, bool $skipCache=true):array{
		$rs = [];
		if(!$skipCache && Utility::isCache() && ($cache = Utility::getCache($this::CACHE_KEY,$query))){
			$rs = $cache;
		}else{
			$result = $this->runQuery($query,$debug);
			$rows = $this->numRows($result);
			if($rows){
				while($row = mysqli_fetch_assoc($result)) {
					$rs[] = $row;
				}
			}
			if(Utility::isCache()){
				// Utility::cacheGraph($this->CACHE_KEY,$query,$rs);
			}
		}
		return $rs;
	}
	function numRows($result){
		if(\is_bool($result))
			return 0;
		else
			return mysqli_num_rows($result);
	}
	function migrate(string $query){
		Utility::migrate($query);
	}
	function create(string $table,$fields,$values,$debug=false){
		$query = "INSERT INTO ".$table." ($fields) VALUES ($values)";
		if($ret = $this->runQuery($query,$debug)){
			$this->migrate($query);
			return $ret;
		}
		return '';
	}
	function update(string $table,$field,$value,$haystack,$needle,$debug=false){
		$value = addslashes(trim($value));
		$query = "SET $field='".$value."' WHERE $haystack='$needle'";
		return $this->updateGeneric($table,$query,$debug);
	}
	function updateAdvanced(string $table, array $cvSet, string $haystack, string $needle, $debug=false){
		$query = "";
		$i=0;
		foreach($cvSet as $key=>$value){
			$query .= ($i==0?"SET":",")." ".$key."='".$value."'";
			$i++;
		}
		$query .= " WHERE $haystack='$needle'";
		return $this->updateGeneric($table,$query,$debug);
	}
	function updateGeneric($table,$query,$debug=false){
		$ret = '';
		$query = "UPDATE $table $query";
		$ret = $this->runQuery($query,$debug);
		$affectedRows = $this->affectedRows();
		if($affectedRows){
			$this->migrate($query);
		}
		return $affectedRows>0?$affectedRows:0;
	}
	function deleteAll(string $table,$debug=false){
		// $filter = "WHERE $haystack='$needle'";
		return $this->delete($table,'1','1',$debug);
	}
	function delete(string $table,string $haystack, string $needle, bool $debug=false, string $dkey=null){
		return $this->updateAdvanced($table,['del'=>'1','dkey'=>$dkey??Utility::getUID()],$haystack,$needle,$debug);
	}
	function deleteGeneric(string $table, string $filter, bool $debug=false){
		$query = "SET del='1' $filter";
		return $this->updateGeneric($table,$query,$debug);
	}
	function getAll(string $table,bool $debug=false,bool $skipCache=true){
		return $this->getGeneric($table,'WHERE true','*',$debug,$skipCache);
	}
	function get(string $table, string $haystack, string $needle, bool $debug=false){
		$filter = "WHERE $haystack='$needle'";
		return $this->getGeneric($table,$filter,'*',$debug);
	}
	function getGeneric(string $table, string $filter="", string $cols=null, bool $debug=false, bool $skipCache=true){
		$cols = $cols?$cols:'*';

		$query = "SELECT $cols from $table $filter";

		return $this->getResultSet($query,$debug,$skipCache);
	}
	function getPaginated(string $table, string $filter="", string $cols='*', int $page=0, int $limit=100, bool $debug=false){
		$cols = ($cols?$cols:'*');

		if(stripos($filter,'LIMIT',0) || stripos($filter,'ORDER BY',0) )
			$query = "SELECT $cols from $table $filter";
		else
			$query = "SELECT $cols from $table $filter ORDER BY created_at DESC LIMIT $page,$limit";

		return $this->getResultSet($query,$debug);
	}
}
?>
