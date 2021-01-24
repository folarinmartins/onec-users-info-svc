<?php
namespace model;

use contract\Event;
use controller\AccountController;
use Exception;
use helper\Utility;
// use database\DBController;

class Model{
	private string $id;
	private array $cache;
	private string $UID;

	function __construct(string $UID, string $id = null){
		$this->UID = $UID;
		$this->id = $id??'';
		$this->cache = [];
		$this->reloadCache();
	}
	function getInstanceByProperty(string $haystack,string $needle):?Model{
		if($instances = $this->get($haystack,$needle)){
			return new Model($this->getUID(),$instances[0]['id']);
		}
		return null;
	}
	function getInstance(string $id=null):?Model{
		if($id){
			$model = new Model($this->getUID(),$id);
			if($model->getCache())
				return $model;
		}else
			return $this;
			
		return null;
	}
	function getPref(string $key,string $default=null,bool $debug=false):?string{
		global $Pref;
		if($rows = $Pref->getGeneric("WHERE entity='".$this->getID()."' AND config='$key' ORDER BY id DESC LIMIT 1",$debug)){
			return $rows[0]['value']??$default;
		}
		return $default;
	}
	function setPref(string $key,string $value='',bool $debug=false):bool{
		global $Pref;
		if($prefs = $Pref->getGeneric("WHERE `config`='$key' AND `entity`='".$this->getID()."'")){
			if(!$Pref->updateGeneric("SET `value`='value' WHERE `id`='".$prefs[0]['id']."'",$debug)){
				throw new Exception('Could not update preference');
			}
		}else
		if(!$Pref->insertMap(['config' => $key, 'entity' => $this->getID(), 'value' => $value],$debug)){
			throw new Exception('Could not insert preference');
		}
		return true;
	}
	function instanceof(Model $model):bool{
		return $this->getType()===$model->getType();
	}
	function getType():string{
		return $this->UID;
	}
	function getUID():string{
		return $this->UID;
	}
	function getSpecs():array{
		global $config;
		$prop0 = array_merge($config['db']['spec']['_shared'],$config['db']['spec'][$this->getUID()]['properties']);
		$full = $config['db']['spec'][$this->getUID()];
		$full['properties'] = $prop0;
		return $full;
	}
	function getTable():string{
		return $this->getSpecs()['table'];
	}
	function reloadCache(bool $force=false){
		global $config;
		if($this->getID()){
			$this->cache = $this->init($force,$this->getID());
		}
	}
	function getCache(){
		return $this->cache;
	}
	function getID(){
		return $this->id;
	}
	function uncache(string $id=null){
		Utility::uncache($this->getTable(),$id);
	}
	function create($fields,$values,$debug=false):bool{
		global $dbController;
		$query = "INSERT INTO ".$this->getTable()." ($fields) VALUES ($values)";
		return $dbController->runQuery($query,$debug);
	}
	/**
	 * @param string|null $prop
	 * @param string|null $index
	 * @param string|null $format
	 * @return null|Model|void
	 */
	function updateFiles(string $prop=null, string $index=null, string $format=null){
		global $request;
		global $File;
		$specs = $this->getSpecs();
		if($request->getFiles())
		foreach($specs['properties'] as $k=>$spec){
			$fileIO = $request->getFile($k);
			if($fileIO && $fileIO['size'] && !$fileIO['error']){
				$ext = strtolower(pathinfo($fileIO["name"], PATHINFO_EXTENSION));
				$file = $File->insertMap(array_merge($fileIO,['ext'=>$ext,'name'=>$k]));
				$target_dir = UPLOAD_PATH.'/'.$file->getID().'.'.$ext;
				if(file_exists($target_dir))
					unlink($target_dir);
				move_uploaded_file($fileIO["tmp_name"], $target_dir);
				$this->updateMap([$k=>$file->getID()]);
				return $file;
			}
		}
	}
	function updateMap(array $map,string $id=null,$debug=false):int{
		global $dbController;
		if($id = $id??$this->getID()){
			$specs = $this->getSpecs();
			$params = '';
			foreach($specs['properties'] as $k=>$spec){
				if($k=='id')
					continue;
				if(array_key_exists($k,$map)){
					if(is_null($map[$k]))
						$params .= (\strlen($params)>1?',':'')."`$k`=NULL";
					else{
						$value = $map[$k];
						switch($spec['type']){
							case TYPE_BOOLEAN:{
								$value = ($value?'1':'0');
							}break;
							default:{
								$value = addslashes(trim($value));
							}
						}
						if(Utility::in_array(OPTION_HASH,$spec['options'])){
							$value = Utility::getHash($value);
						}
						$params .= (\strlen($params)>1?',':'')."`$k`='$value'";
					}
				}
			}
			$ret = $dbController->updateGeneric($this->getTable(),"SET $params WHERE `id`='$id'",$debug);
			if($ret){
				$this->reloadCache(true);
			}else{
				Utility::log($dbController->getConnection()->error);
				Utility::log($dbController->getQuery());
			}
			return $ret;
		}
		return false;
	}
	function insertMap($map,$debug=false):?Model{
		global $dbController;
		global $Action;
		$model = null;

		$id = Utility::getUID();
		$fields = "id";
		$values = "'$id'";
		$specs = $this->getSpecs();
		foreach($specs['properties'] as $k=>$spec){
			if($k=='id')
				continue;
			if(isset($map[$k])){
				$value = $map[$k];
				switch($spec['type']){
					case TYPE_BOOLEAN:{
						$value = ($value?'1':'0');
					}break;
					default:{
						$value = addslashes(trim($value));
					}
				}
				if(Utility::in_array(OPTION_HASH,$spec['options'])){
					$value = Utility::getHash($value);
				}
				$fields .= ",`$k`";
				$values .= ",'$value'";
			}else{
				if(Utility::in_array(OPTION_REQUIRED,$spec['options'])){
				}else{
					// $fields = $fields.",".$k;
					// $values = $values.",NULL";
				}
			}
		}
		if($this->create($fields,$values,$debug)){
			$model = new Model($this->getUID(),$id);
				Event::trigger($model,OP_C);
		}else{
			Utility::log($dbController->getConnection()->error);
			Utility::log($dbController->getQuery());
		}
		return $model;
	}
	function getProperty($field){
		return stripslashes($this->getCache()["$field"]??'');
	}
	function init(bool $force=false, string $id=QUANTIFIER_ALL){
		global $dbController;
		$type = [];
		if(!$force && Utility::isCache() && ($cache = Utility::getCache($this->getTable(),$id))){
			return $cache;
		}
		if($id && $id!=QUANTIFIER_ALL){
			$types = $dbController->get($this->getTable(),'id',$id);
		}else
			$types = $dbController->getAll($this->getTable());
		$specs = $this->getSpecs();
		foreach($types as $k=>$v){
			foreach($specs['properties'] as $kk=>$spec){
				$type[$v['id']][$kk] = $v[$kk];
			}
			if(Utility::isCache()){
				Utility::cacheGraph($this->getTable(),$id,$type[$v['id']]);
			}
		}
		return $type[$id]??$type;;
	}


	/**
	 * @param Model|null $stateful
	 * @param bool $transition
	 * @return string
	 */
	function getState(Model $stateful=null):string{
		global $Transition;
		$stateful = ($stateful??$this);
		if($states = $Transition->getGeneric("WHERE `stateful`='".$stateful->getID()."' ORDER BY `id` DESC LIMIT 1")){
			return $states[0]['staten'];
		}
		return '';
	}
	function getStateInstance(Model $stateful=null,bool $debug=false):?Model{
		global $Transition;
		global $State;
		$stateful = ($stateful??$this);
		if($states = $Transition->getGeneric("WHERE `stateful`='".$stateful->getID()."' ORDER BY `id` DESC LIMIT 1",$debug)){
			return $State->getInstance($states[0]['staten']);
		}
		return null;
	}
	function getTransition(Model $stateful=null):array{
		global $Transition;
		$stateful = ($stateful??$this);
		if($states = $Transition->getGeneric("WHERE `stateful`='".$stateful->getID()."' ORDER BY `id` DESC LIMIT 1")){
			return $states[0];
		}
		return [];
	}
	function getTransitions(Model $stateful=null,bool $transition=false):array{
		global $Transition;
		$stateful = ($stateful??$this);
		if($states = $Transition->getGeneric("WHERE `stateful`='".$stateful->getID()."' ORDER BY `id` DESC")){
			if($transition)
				return $states;
			else
				return Utility::linearize($states,'staten');
		}
		return [];
	}
	function setState(string $state,Model $user,string $description,Model $stateful=null):?MOdel{
		$stateful = ($stateful??$this);
		$state0 = ($this->getState($stateful)??STATE_DEFAULT);
		return $this->advance($state0,$state,$user,$description,$stateful);
	}
	function advance(string $state0, string $staten, Model $user, string $description, Model $stateful=null):?MOdel{
		global $Transition;
		$stateful = ($stateful??$this);
		return $Transition->insertMap(['stateful'=>$stateful->getID(),'user'=>$user->getID(),'description'=>$description,'state0'=>$state0,'staten'=>$staten]);
	}

	public function groupByDate(string $filter, bool $debug=false):array{
		$rows = $this->getGeneric($filter,$debug);
		$ret = [];
		foreach($rows as $i=>$row){
			$ret[Utility::smartDays(date_create($row['created_at']))][] = $row;
		}
		return $ret;
	}
	public function getInstancesMap(string $haystack=null, string $needle=null):array{
		$options = [];
		$spcc = $this->getSpecs();
		$query = '';
		if($haystack && $needle)
			$query = "WHERE `$haystack`='$needle' ".$query;
		$types = $this->getGeneric("$query ORDER BY ".$spcc['caption']);
		foreach($types as $kkk=>$type){
			$options[$type['id']] = $type[$spcc['caption']];
		}
		return $options;
	}
	function get($haystack,$needle,$debug=false,$skipCache=false):array{
		if($haystack=='id' && !$skipCache && Utility::isCache()){
			$type = $this->getTable();
			if($type && ($cache = Utility::getCache($type,$needle))){
				return [$cache];
			}
		}
		$filter = "WHERE $haystack='$needle'";
		return $this->getGeneric($filter,$debug);
	}
	function getAllAtState(string $state, string $haystack=null, string $needle=null, int $page=1, int $max=50,$debug=false):array{
		global $Transition;
		if($haystack && $needle)
			$query = " t1 WHERE (SELECT staten from ".$Transition->getTable()." t2 WHERE t2.stateful=t1.id ORDER BY t2.id DESC LIMIT 1 ) = '$state' AND `$haystack`='$needle'";
		else
			$query = " t1 WHERE (SELECT staten from ".$Transition->getTable()." t2 WHERE t2.stateful=t1.id ORDER BY t2.id DESC LIMIT 1 ) = '$state'";
		return $this->getAdvanced('t1.id',$query,$debug);
	}
	function getAll($debug=false,$skipCache=false){
		if(!$skipCache && Utility::isCache()){
			if($cache = Utility::getCache($this->getTable(),QUANTIFIER_ALL)){
				$ret = [];
				foreach($cache as $k=>$v)
					$ret[] = $v;
				return $ret;
			}else{
				$this->init(true,QUANTIFIER_ALL);
			}
		}
		return $this->getGeneric("WHERE true",$debug);
	}
	function getGeneric($filter="",$debug=false,$cols='*',$skipCache=false){
		global $dbController;
		return $dbController->getGeneric($this->getTable(),$filter,$cols,$debug,$skipCache);
	}
	function getPaginated($filter="",$cols='*',int $page=0, int $limit=100,$debug=false){
		global $dbController;
		return $dbController->getPaginated($this->getTable(),$filter,$cols,$page,$limit,$debug);
	}
	function getAdvanced($cols,$filter="",$debug=false){
		return $this->getGeneric($filter,$debug,$cols);
	}
	function updateProperty(string $field, $value, bool $debug=false){
		$ret = null;
		if(\is_array($value))
			$ret = $this->update($field,$value[0],"id",$this->id,$debug);
		else
			$ret = $this->update($field,$value,"id",$this->id,$debug);
		if(Utility::isCache()){
			if($ret && $field=='del' && (($value[0]??false)||$value))
				$this->uncache($this->id);
			else
				$this->init(true,$this->getID());
		}
		return $ret;
	}
	function update($field,$value,$haystack,$needle,$debug=false){
		if(\is_array($value))
			$value = trim($value[0]);
		else
			$value = trim($value);
		$query = "SET $field='".addslashes($value)."' WHERE $haystack='$needle'";
		return $this->updateGeneric($query,$debug);
	}
	function updateGeneric($filter,$debug=false){
		global $dbController;
		$ret = $dbController->updateGeneric($this->getTable(),$filter,$debug);
		if($ret);
			$this->reloadCache(true);
		return $ret;
	}
	function deleteAll(){
		$this->delete('1','1');
	}
	function delete($haystack,$needle,$debug=false,$soft=false){
		$query = " WHERE $haystack='$needle'";
		return $this->deleteGeneric($query,$debug,$soft);
	}
	function deleteGeneric($filter,$debug=false,$soft=false){
		global $dbController;
		return $dbController->deleteGeneric($this->getTable(),$filter,$debug,$soft);
	}
	function hasProperty($field){
		return !empty($this->getProperty("$field"));
	}
	function getFieldMeta(){ //TODO:Get DB field/column meta
		global $dbController;
		$mysql_data_type_hash = array(
			1=>'tinyint',
			2=>'smallint',
			3=>'int',
			4=>'float',
			5=>'double',
			7=>'timestamp',
			8=>'bigint',
			9=>'mediumint',
			10=>'date',
			11=>'time',
			12=>'datetime',
			13=>'year',
			16=>'bit',
			//252 is currently mapped to all text and blob types (MySQL 5.0.51a)
			253=>'varchar',
			254=>'char',
			246=>'decimal'
		);
		// According to
		// dev.mysql.com/sources/doxygen/mysql-5.1/mysql__com_8h-source.html
		// the flag bits are:

		// NOT_NULL_FLAG          1         /* Field can't be NULL */
		// PRI_KEY_FLAG           2         /* Field is part of a primary key */
		// UNIQUE_KEY_FLAG        4         /* Field is part of a unique key */
		// MULTIPLE_KEY_FLAG      8         /* Field is part of a key */
		// BLOB_FLAG             16         /* Field is a blob */
		// UNSIGNED_FLAG         32         /* Field is unsigned */
		// ZEROFILL_FLAG         64         /* Field is zerofill */
		// BINARY_FLAG          128         /* Field is binary   */
		// ENUM_FLAG            256         /* field is an enum */
		// AUTO_INCREMENT_FLAG  512         /* field is a autoincrement field */
		// TIMESTAMP_FLAG      1024         /* Field is a timestamp */
		$query = "SELECT Name, SurfaceArea from Country ORDER BY Name LIMIT 5";

		if ($result = $dbController->runQuery($query)) {

			/* Get field information for all columns */
			$finfo = $result->fetch_fields();

			foreach ($finfo as $val) {
				printf("Name:      %s\n",   $val->name);
				printf("Table:     %s\n",   $val->table);
				printf("Max. Len:  %d\n",   $val->max_length);
				printf("Length:    %d\n",   $val->length);
				printf("charsetnr: %d\n",   $val->charsetnr);
				printf("Flags:     %d\n",   $val->flags);
				printf("Type:      %d\n\n", $val->type);
			}
			$result->free();
		}
	}
}
?>
