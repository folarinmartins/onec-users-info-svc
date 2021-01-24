<?php
namespace helper;
use model\Model;
use database\RedisWrapper;
use Exception;

abstract class Utility{
	function isMobile():bool{
		return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	}
	static $log;
	public static function startLog(string $desc):string{
		$key = uniqid();
		self::$log[$key]['name'] = $desc;
		self::$log[$key]['time'] = time();
		Utility::log("LOGGING:".self::$log[$key]['name']);
		return $key;
	}
	public static function getLog(string $key, bool $end=true){
		$now = time();
		Utility::log("LOGGING: REPORT:".self::$log[$key]['name'].' DELAY:'.($now-self::$log[$key]['time'])."s");
		if($end)
			unset(self::$log[$key]);
	}
	/** @return string web|cron|cli  */
	static function getInterface():string{
		if(php_sapi_name() !== 'cli')
			return "web";

		if(isset($_SERVER['TERM']))
			return 'cli';		
			
		return "cron";
	}
	static function isCron():bool{
		return php_sapi_name() == 'cli' && !isset($_SERVER['TERM']);
	}
	static function isCLI():bool{
		return php_sapi_name() == 'cli';
	}
	static function isWeb():bool{
		return !(php_sapi_name() == 'cli');
	}
	function getWords(string $sentence, int $count = 10){
		preg_match("/(?:\w+(?:\W+|$)){0,$count}/", $sentence, $matches);
		return $matches[0];
	}
	public static function migrate($value){
		if(Utility::isLogMigrate()){
			$data = ' -- '.date_create()->format('Y-m-d H:i:s.u ').PHP_EOL.print_r($value, true).PHP_EOL;
			file_put_contents(Utility::getLogFile('migrate'), $data, FILE_APPEND);
		}
	}
	public static function log($value){
		$fp = fopen(Utility::getLogFile('event'), 'a');//opens file in append mode.
		fwrite($fp,PHP_EOL.date_create()->format('Y-m-d H:i:s.u ').PHP_EOL.print_r($value, true).PHP_EOL);
		fclose($fp);
	}
	public static function debug($str){
		echo '</br><pre>';
		print_r($str);
		echo '</pre></br>';
	}
	public static function getLogFile(string $key):string{
		global $config;
		return $config['paths']['log'][$key]??'';
	}
	public static function getEnvVariable(string $key){
		global $config;
		return $config['env'][$key]??'';
	}
	public static function addDays($date,$op='+',$denor='1 day'):\DateTime{
		return date_create(date('Y-m-d H:i:s', strtotime($date->format('Y-m-d H:i:s'). " $op $denor")));
	}
	public static function isAfterWorkingDays($dn,$d0,$weekdays=3):bool{
		$deadDT = date(DATE_COOKIE,strtotime(date_format($d0,DATE_COOKIE)."$weekdays weekdays" ));
		return Utility::getFullDifff('d',$deadDT,$dn)>2 && Utility::isAfter($dn,$deadDT);
	}
	public static function isAfter($dn,$d0):bool{
		$dtn = strtotime(date_format($dn,'Y-m-d H:i:s'));
		$dt0 = strtotime(date_format($d0,'Y-m-d H:i:s'));

		$secs = $dtn - $dt0;// == <seconds between the two times>
		// $days = $secs / 86400;
		return ($secs)>0;
	}
	public static function getDateTrichotomy($dd){
		$td = date_create();
		$diff = Utility::getFullDifff('h',$td,$dd);

		if($diff>=0 and $diff<=18 and Utility::isAfter($dd,$td)){
			return 0;
		}else
		if(Utility::isAfter($dd,$td)){
			return 1;
		}else
			return -1;
	}
	public static function getEnv():string{
		global $config;
		return $config['env']['env'];
	}
	public static function getCredential($provider,$key){
		global $config;
		return $config['credential'][$provider][Utility::getEnv()][$key];
	}
	public static function getActiveStates():array{
		return [STATE_PUBLISHED,STATE_OPEN,STATE_PROCESSING,STATE_SUBMITTED,STATE_ENABLED,STATE_PROCESSED,STATE_CHECKED_IN,STATE_DRAFT,STATE_SAVED,STATE_ON,STATE_INVESTIGATING];
	}
	public static function getInactiveStates():array{
		return [STATE_DEFAULT,STATE_CLOSED,STATE_DISABLED,STATE_OFF];
	}
	public static function getURL($index):string{
		global $config;
		return $config['url'][$index];
	}
	public static function getNote($index):string{
		global $config;
		return $config['note'][$index];
	}
	public static function newInstanceLimit():int{
		global $config;
		return $config['env']['instance.new.limit'];
	}
	public static function subtypeDepth():int{
		global $config;
		return $config['env']['subtype.depth'];
	}
	public static function UIDLength():int{
		return strlen(Utility::getUID());
	}
	public static function searchTreeDepth(){
		global $config;
		return $config['env']['search.tree.depth'];
	}
	public static function getK(float $value){
		if($value>1000000){
			return \number_format($value/1000000,1)."m";
		}else
		if($value>1000){
			return \number_format($value/1000,1)."k";
		}
		return \number_format($value);
	}
	public static function payMerchant():Model{
		global $config;
		return Model::getInstanceByID(TYPE_PARTY,$config['env']['pay.merchant'],REALM_KN);
	}

	public static function destroySession(){
		Utility::uncache('session',QUANTIFIER_ALL,Utility::sessionCachePolicy());
	}
	public static function deleteSession(string $property, bool $persist=false){
		Utility::uncache('session',$property,CACHE_SESSION);
	}
	public static function saveSession(string $property, array $values, bool $persist=false){
		Utility::cacheGraph('session',$property,$values,Utility::sessionCachePolicy());
	}
	public static function getSession(string $property):array{
		return Utility::getCache('session',$property,Utility::sessionCachePolicy());
	}
	public static function currentSession():Model{
		return Model::getInstanceByID(TYPE_WEB_SESSION,Utility::getSession(PROPERTY_ID)[0]);
	}

	public static function currentParty():Model{
		return Model::getInstanceByID(TYPE_PARTY,Utility::getSession(PROPERTY_PARTY_COUNTER)[0]);
	}
	public static function currentCompany():Model{
		return Model::getInstanceByID(TYPE_PARTY,Utility::getSession(PROPERTY_PARTY)[0],REALM_KN);
	}
	public static function defaultCurrency():Model{
		return Model::getInstanceByID(TYPE_CURRENCY,Utility::getSession(PROPERTY_UNIT)[0],REALM_KN);
	}
	public static function maskEmail(string $email,int $minFill = 4){
		return preg_replace_callback(
				'/^(.)(.*?)([^@]?)(?=@[^@]+$)/u',
				function ($m) use ($minFill) {
					return $m[1]
							. str_repeat("*", max($minFill, mb_strlen($m[2], 'UTF-8')))
							. ($m[3] ?: $m[1]);
				},
				$email
			);
	}
	public static function getOp($op='r'):string{
		global $config;
		return $config['env']['op'][$op];
	}
	public static function getNamePattern():string{
		global $config;
		return $config['note']['pattern.username'];
	}
	public static function getPhonePattern():string{
		global $config;
		return $config['note']['pattern.phone'];
	}
	public static function getSamplePhone():string{
		global $config;
		return $config['note']['sample.phone'];
	}
	public static function getPasswordPattern():string{
		global $config;
		return $config['note']['pattern.password'];
	}
	public static function getSamplePassword():string{
		global $config;
		return $config['note']['sample.password'];
	}
	public static function getSampleName():string{
		global $config;
		return $config['note']['sample.username'];
	}
	public static function instanceDepth():int{
		global $config;
		return $config['env']['instance.depth'];
	}
	public static function graphDepth():int{
		global $config;
		return $config['env']['graph.depth'];
	}
	public static function cachePolicy():string{
		global $config;
		return $config['env']['cache.policy'];
	}
	public static function sessionCachePolicy():string{
		global $config;
		return $config['env']['cache.policy.session'];
	}
	public static function isCache():bool{
		global $config;
		return $config['env']['cache'];
	}
	public static function cache(string $type, string $id, Model $model, string $policy=null, string $domain='app.blockstale.com'){
		Utility::cacheGraph($type,$id,$model->getCache(),$policy,$domain);
	}
	public static function cacheGraph(string $type, string $id, array $array, string $policy=null, string $domain='app.blockstale.com'){
		$policy = $policy?$policy:Utility::cachePolicy();
		switch($policy){
			case CACHE_REDIS:{
				RedisWrapper::getRedis()->set("$domain:$type:$id",serialize($array));
			}break;
			case CACHE_SESSION:{
				$_SESSION['cache'][$type][$id] = $array;
			}break;
		}
	}
	public static function uncache(string $type, string $id, string $policy=null, string $domain='app.blockstale.com'){
		$policy = $policy?$policy:Utility::cachePolicy();
		switch($policy){
			case CACHE_REDIS:{
				if(RedisWrapper::getRedis()->exists("$domain:$type:$id"))
					return RedisWrapper::getRedis()->del("$type:$id");
			}break;
			case CACHE_SESSION:{
				if(isset($_SESSION['cache'][$type][$id]))
					unset($_SESSION['cache'][$type][$id]);
				else
				if($id == QUANTIFIER_ALL){
					unset($_SESSION['cache']['type']);
				}
			}break;
		}
	}
	public static function getCache(string $type, string $id, string $policy=null, string $domain='app.blockstale.com'):array{
		$policy = $policy?$policy:Utility::cachePolicy();
		switch($policy){
			case CACHE_REDIS:{
				$redis = RedisWrapper::getRedis();
				if($id==QUANTIFIER_ALL){
					$ret = [];
					$keys = $redis->keys("$domain:$type:*");
					foreach($keys as $k=>$key){
						if($redis->exists("$key"))
							$ret[\substr($key,\strrpos($key,":")+1)] = unserialize($redis->get("$key"));
					}
					return $ret;
				}else
				if($redis->exists("$domain:$type:$id"))
					return unserialize($redis->get("$domain:$type:$id"));
				else
					return [];
			}break;
			case CACHE_SESSION:{
				if($type==QUANTIFIER_ALL && isset($_SESSION['cache'])){
					return $_SESSION['cache'];
				}else
				if($id==QUANTIFIER_ALL && isset($_SESSION['cache'][$type])){
					return $_SESSION['cache'][$type];
				}else
				if(isset($_SESSION['cache'][$type][$id])){
					return $_SESSION['cache'][$type][$id];
				}else
					return [];
			}break;
		}
		return [];
	}
	public static function isProduction():bool{
		global $config;
		return Utility::getEnv() == 'production';
	}
	public static function isLog():bool{
		global $config;
		return $config['env']['log'];
	}
	public static function isLogActions():bool{
		global $config;
		return $config['env']['log.actions'];
	}
	public static function isLogMigrate():bool{
		global $config;
		return $config['env']['log.migration'];
	}
	public static function isTransform():bool{
		global $config;
		return $config['env']['log.transformation'];
	}
	public static function tokenize(string $str){
		return strtolower(str_replace(' ','_',trim($str)));
	}
	public static function println(string $str){
		echo "<br>$str";
	}
	public static function getKey(int $length=3):string{
		if(function_exists('random_bytes')) {
			return bin2hex(random_bytes($length));
		}
		if(function_exists('mcrypt_create_iv')) {
			return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
		}
		if(function_exists('openssl_random_pseudo_bytes')) {
			return bin2hex(openssl_random_pseudo_bytes($length));
		}
	}
	public static function getUID():string{
		return uniqid();
	}
	public static function getComCode():string{
		return md5(uniqid(rand()));
	}
	public static function getClientIP() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
	public static function getClientDevice():string{
		global $request;
		preg_match("/iPhone|Android|iPad|iPod|webOS|Linux|Windows|FireFox|Opera|Chrome/", $request->getContext('HTTP_USER_AGENT'), $matches);
		$os = current($matches);
		// switch($os){
		// case 'iPhone': /*do something...*/ break;
		// case 'Android': /*do something...*/ break;
		// case 'iPad': /*do something...*/ break;
		// case 'iPod': /*do something...*/ break;
		// case 'webOS': /*do something...*/ break;
		// }

		return $os;
		/*
			echo $_SERVER['HTTP_USER_AGENT'] . "\n\n";

			$browser = get_browser(null, true);
			print_r($browser);

			The above example will output something similar to:

			Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7) Gecko/20040803 Firefox/0.9.3

			Array
			(
				[browser_name_regex] => ^mozilla/5\.0 (windows; .; windows nt 5\.1; .*rv:.*) gecko/.* firefox/0\.9.*$
				[browser_name_pattern] => Mozilla/5.0 (Windows; ?; Windows NT 5.1; *rv:*) Gecko/* Firefox/0.9*
				[parent] => Firefox 0.9
				[platform] => WinXP
				[browser] => Firefox
				[version] => 0.9
				[majorver] => 0
				[minorver] => 9
				[cssversion] => 2
				[frames] => 1
				[iframes] => 1
				[tables] => 1
				[cookies] => 1
				[backgroundsounds] =>
				[vbscript] =>
				[javascript] => 1
				[javaapplets] => 1
				[activexcontrols] =>
				[cdf] =>
				[aol] =>
				[beta] => 1
				[win16] =>
				[crawler] =>
				[stripper] =>
				[wap] =>
				[netclr] =>
			)

			Notes ¶

				Note:

				In order for this to work, your browscap configuration setting in php.ini must point to the correct location of the browscap.ini file on your system.

				browscap.ini is not bundled with PHP, but you may find an up-to-date » php_browscap.ini file here.

				While browscap.ini contains information on many browsers, it relies on user updates to keep the database current. The format of the file is fairly self-explanatory.

			*/
	}
	/**
	 * @param string $endpoint
	 * @param array $default
	 * @return array
	 * @throws Exception
	 */
	public static function curl(string $endpoint, array $default=null,$jsondecode=true,array $curlOpt=[]):array{
		$ch = curl_init($endpoint);

        curl_setopt($ch, CURLOPT_USERAGENT, 'Onechurch-PHP/1.0');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); //CURLOPT_CONNECTTIMEOUT - The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
		curl_setopt($ch, CURLOPT_TIMEOUT, 400); //CURLOPT_TIMEOUT - The maximum number of seconds to allow cURL functions to execute.
		foreach($curlOpt as $key=>$value){
			curl_setopt($ch, $key, $value);
		}
		$json = curl_exec($ch);
		// $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		// if($http_status !== 200){}
		$curlError = curl_error($ch);
		curl_close($ch);
		
        if($curlError) {
			if(!is_null($default))
				return $default;
			else
				throw new Exception("Couldn't fetch resource", 1);
        }else{
			if($jsondecode){
				return json_decode($json,true);
			}else
				return [$json];
		}
		
		return $default;
	}
	public static function getIPInfo():array{
		return Utility::curl("https://ipinfo.io?token=".Utility::getCredential('ipinfo','api'),['ip'=>'Unknown','city'=>'Unknown','region'=>'Unknown','country'=>'N/A','loc'=>'N/A','org'=>'N/A','timezone'=>'N/A']);
		/* {
			"ip": "129.205.113.251",
			"city": "Nchia",
			"region": "Rivers",
			"country": "NG",
			"loc": "4.7931,7.1206",
			"org": "AS37148 Globacom Limited",
			"timezone": "Africa/Lagos"
		  } */
	}
	public static function startsWith(string $haystack, string $needle):bool{
		 $length = strlen($needle);
		 return (substr($haystack, 0, $length) === $needle);
	}
	public static function endsWith(string $haystack, string $needle):bool{
		$length = strlen($needle);
		return $length === 0 ||
		(substr($haystack, -$length) === $needle);
	}
	public static function linearize(array $array, string $column):array{
		if(count($array)>0 && isset($array[0][$column]))
			return array_column($array,$column);
		return [];
	}
	public static function arraySyntax(array $array, string $delimiter='"', string $key=null, array $enclose=['','']){
		$ret = $enclose[0];
		$i=0;
		foreach($array as $k=>$v){
			$ret .= (($i>0)?',':'');

			if($key)
				$ret .= $delimiter.$v[$key].$delimiter;
			else
			if(is_array($v))
				$ret .= $delimiter.$v[0].$delimiter;
			else
				$ret .= $delimiter.$v.$delimiter;
			++$i;
		}
		return $ret.$enclose[1];
	}
	public static function concat(array $array, string $delim=',', string $quote="'"):string{
		$concat = "";
		foreach($array as $k1=>$v1){
			$concat .= (($k1!=0)?$delim:((strlen($concat)>0)?$delim:''))."$quote".$v1."$quote";
		}
		if(strlen($concat)==0)
			$concat = $quote.$quote;
		return $concat;
	}
	public static function in_array(string $needle, array $haystack):bool{
		return in_array(strtolower($needle), array_map('strtolower', $haystack));
	}
	public static function subset(array $subset, array $superset):bool{
		return Utility::intersect($subset,$superset) == $subset;
	}
	public static function intersect(array $subset, array $superset):array{
		return array_intersect(array_map('strtolower', $subset), array_map('strtolower', $superset));
	}
	public static function setEquals(array $subset, array $superset):bool{
		return Utility::subset($subset,$superset) && Utility::subset($superset,$subset);
	}
	public static function prettify(string $title, string $content):string{
		return sprintf("%20s: ", "$title").$content;
	}
	public static function parseDate(Model $Model):?\DateTime{
		$dd = null;
		switch($Model->getType()){
			case TYPE_TIME_PERIOD:{
				$dd = \date_create("".Model::getInstance(TYPE_TIME_POINT,$Model->getPropertyValue(PROPERTY_FROM)[0]));
			}break;
			case TYPE_TIME_POINT:{
				$dd = \date_create("".$Model);
			}break;
		}
		return $dd;
	}
	public static function dateToString($date){
		return $date->format(DATE_RSS);
	}
	public static function simpleDate($date){
		return $date->format('d M, y');
	}
	public static function smartDays($date){
		$today = date_create();
		$ddiff = Utility::getFullDifff('d',$date,$today);
		if(date_format($date,"Y")==date_format($today,"Y")){
			if(date_format($date,"m")==date_format($today,"m")){
				if($ddiff<=7){
					if(date_format($today,"j")-date_format($date,"j")<=1){
						if(date_format($today,"j")-date_format($date,"j")==1)
							return "Yesterday";
						else
							return "Today";
					}else
						return date_format($date,"l, jS");
				}else
					return date_format($date,"l, jS");
			}else
				return date_format($date,"l, jS F");
		}else
			return date_format($date,"jS F, 'y");
	}
	public static function smartDate($date,$sortable=false){
		if($sortable)
			return date_format($date,"Y/m/d H:i:s");
		$today = date_create();
		$idiff = number_format(Utility::getFullDifff('i',$date,$today));
		$ddiff = Utility::getFullDifff('d',$date,$today);
		if(date_format($date,"Y")==date_format($today,"Y")){
			if(date_format($date,"m")==date_format($today,"m")){
				if($ddiff<=7){
					if(date_format($today,"j")-date_format($date,"j")<=1){
						if(date_format($today,"j")-date_format($date,"j")==1)
							return "Yesterday, ".date_format($date,"h:i A");
						else
						if($idiff<=1)
							return "Just Now";
						else
						if($idiff<=59)
							return "$idiff mins ago";
						else
							return "Today, ".date_format($date,"h:i A");
					}else
						return date_format($date,"l jS, h:i A");
				}else
					return date_format($date,"l jS, h:i A");
			}else
			if(date_format($date,"W")==date_format($today,"W"))
				return date_format($date,"l, jS, h:i A");
			else
				return date_format($date,"l, jS F, h:i A");
		}else
			return date_format($date,"jS F, 'y");
	}
	public static function smartTime($offset,$sortable=false){
		$hh = floor($offset / 3600);
		$mm = floor(($offset / 60) % 60);
		$ss = $offset % 60;
		printf("%02d:%02d:%02d", $hh, $mm, $ss);
	}
	public static function getFullDifff($str_interval, $dt_menor, $dt_maior, $relative=false,$debug=false):float{
		if(is_string($dt_menor)) $dt_menor = date_create( $dt_menor);
		if(is_string($dt_maior)) $dt_maior = date_create( $dt_maior);

		$diff = date_diff( $dt_menor, $dt_maior, !$relative);
		if($debug)
			Utility::debug($diff);

		switch( $str_interval){
			case "y":
				$total = $diff->y + $diff->m / 12 + $diff->d / 365.25;
				break;
			case "m":
				$total= $diff->y * 12 + $diff->m + $diff->d/30 + $diff->h / 24;
				break;
			case "d":
				$total = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h/24 + $diff->i / 60;
				break;
			case "h":
				$total = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i/60;
				break;
			case "i":
				$total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
				break;
			case "s":
				$total = ((($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i)*60 + $diff->s;
				break;
		   }
		if( $diff->invert)
			return -1 * $total;
		else
			return $total;
	}
	public static function parseMoney(float $str):string{
		return number_format($str,2,".",",");
		// return "&#8358;".number_format($str,2,".",",");
	}
	public static function parseFloat(float $str, int $decimal=0, string $thousandSeparator=''):string{
		return number_format($str,$decimal,".","$thousandSeparator");
	}
	function hashPassword(string $pwd):string{
		return password_hash($pwd);
	}
	function verifyPassword(string $pwd, string $hash):bool{
		return password_verify($pwd,$hash);
	}
	public static function getHash(string $payload):string{
		return md5($payload);
	}
	public static function characterize(string $str, string $character, int $chunk) {
		return implode($character, str_split($str, $chunk));
	}
	public static function randomToken(int $length = 32){
		if(!isset($length) || $length <= 8 ){
		  $length = 32;
		}
		if (function_exists('random_bytes')) {
			return bin2hex(random_bytes($length));
		}
		if (function_exists('mcrypt_create_iv')) {
			return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
		}
		if (function_exists('openssl_random_pseudo_bytes')) {
			return bin2hex(openssl_random_pseudo_bytes($length));
		}
	}
	public static function getRelativePath(string $from, string $to):string{
		// some compatibility fixes for Windows paths
		$from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
		$to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
		$from = str_replace('\\', '/', $from);
		$to   = str_replace('\\', '/', $to);

		$from     = explode('/', $from);
		$to       = explode('/', $to);
		$relPath  = $to;

		foreach($from as $depth => $dir) {
			// find first non-matching dir
			if($dir === $to[$depth]) {
				// ignore this directory
				array_shift($relPath);
			} else {
				// get number of remaining dirs to $from
				$remaining = count($from) - $depth;
				if($remaining > 1) {
					// add traversals up to first matching dir
					$padLength = (count($relPath) + $remaining - 1) * -1;
					$relPath = array_pad($relPath, $padLength, '..');
					break;
				} else {
					$relPath[0] = './' . $relPath[0];
				}
			}
		}
		return implode('/', $relPath);
	}
}
?>