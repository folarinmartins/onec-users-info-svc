<?php
	declare(strict_types=1);
	require_once realpath(__DIR__.'/../config/config.php');//'/../../config/config.php');
	require_once realpath(__DIR__.'/../autoload.php');
	require_once realpath(__DIR__.'/../../vendor/autoload.php');//. '/../../vendor/autoload.php');


	use comm\Link;
	use database\DBController;
	use helper\Utility;
	use model\Model;
	use contract\Event;
	use contract\Request;
	use contract\Response;
	use controller\AccountController;
	use plaf\ActionListener;
	// use Paystack\Paystack;

	// git remote set-url origin git@github.com:username/repo.git
	// nohup blockchain-wallet-service start --port 3000
	// ps -ef | grep blockchain
	// kill PID
	// systemctl enable blockchain.service

	// grep CRON /var/log/syslog
	// sudo crontab -e
	// */5 * * * *  php -q /home/folarin/Documents/FMInc/code/EE/blockstale/app/cron/btx-monitor.php
	// */7 * * * *  php -q /home/folarin/Documents/FMInc/code/EE/blockstale/app/cron/zombie-btx-monitor.php
	// php -q /home/folarin/Documents/FMInc/code/EE/blockstale/app/cron/btx-monitor.php

	// Autoloader::register();
	$dbController = new DBController();
	// Event::listen(new ActionListener(),QUANTIFIER_ALL,QUANTIFIER_ALL,QUANTIFIER_ALL);

	$User = new Model(MODEL_USER);
	// $File = new Model(MODEL_FILE);
	// $Notification = new Model(MODEL_NOTIFICATION);
	// $State = new Model(MODEL_STATE);
	// $Transition = new Model(MODEL_TRANSITION);
	// $Pref = new Model(MODEL_PREFS);
	// $Business = new Model(MODEL_BUSINESS);
	// $Config = new Model(MODEL_CONFIG);
	// $Bank = new Model(ORG_BANK);
	// $BankAccount = new Model(TX_ACCOUNT);
	// $Currency = new Model(TX_CURRENCY);
	// $GCountry = new Model(GEO_COUNTRY);
	// $GState = new Model(GEO_STATE);
	// $GLGA = new Model(GEO_LGA);
	// $Link = new Model(WEB_LINK);

	// $BTCWallet = new Model(BTC_WALLET);
	// $BTCAddress = new Model(BTC_ADDRESS);
	// $BTCTransaction = new Model(BTC_TRANSACTION);
	// $BTCVoucher = new Model(BTC_VOUCHER);
	// $TransactionType = new Model(TX_TYPE);
	// $FX = new Model(TX_FX);
	// $Account = new Model(ACC_ACCOUNT);
	// $Balance = new Model(ACC_BALANCE);
	// $Entry = new Model(ACC_ENTRY);
	// $Action = new Model(LOG_ACTION);
	// $Cron = new Model(SYS_CRON);
	// $BLOCKCHAIN_V2_API_KEY = Utility::getCredential('blockchain','v2-api');

	// $admin = $User->getInstance(USER_ADMIN);
	// $user = $User->getInstance(Utility::getSession('user')[0]??null);
	// $Blockchain = new \Blockchain\Blockchain(Utility::getCredential('blockchain','api'),Utility::getCredential('blockchain','password'),Utility::getCredential('blockchain','guid'));
	// $Blockchain->setServiceUrl('http://localhost:3000');
	// $Paystack = new Paystack(Utility::getCredential('paystack','sk'));

	$models = [
		// LOG_ACTION=>$Action,
		// TX_FX=>$FX,
		// ACC_ACCOUNT=>$Account,
		// SYS_CRON=>$Cron,
		// ACC_BALANCE=>$Balance,
		// ACC_ENTRY=>$Entry,
		MODEL_USER=>$User,
		// MODEL_FILE=>$File,
		// MODEL_NOTIFICATION=>$Notification,
		// MODEL_STATE=>$State,
		// MODEL_TRANSITION=>$Transition,
		// MODEL_PREFS=>$Pref,
		// MODEL_CONFIG=>$Config,
		// MODEL_BUSINESS=>$Business,
		// ORG_BANK=>$Bank,
		// TX_CURRENCY=>$Currency,
		// BTC_WALLET=>$BTCWallet,
		// BTC_ADDRESS=>$BTCAddress,
		// BTC_TRANSACTION=>$BTCTransaction,
		// BTC_VOUCHER=>$BTCVoucher,
		// TX_ACCOUNT=>$BankAccount,
		// TX_TYPE=>$TransactionType,
		// ORG_BANK=>$Bank,
		// GEO_STATE=>$GState,
		// GEO_COUNTRY=>$GState,
		// GEO_LGA=>$GLGA,
		// WEB_LINK=>$Link,
	];


	$request = new Request([],'',[]);
	$response = new Response($request);
	if(Utility::isWeb()){
		$input = file_get_contents('php://input');
		$tarray = [];
		if($input){
			if($tarray = json_decode($input,true)){
			}else
				parse_str($input,$tarray);
		}
		$request = new Request(array_merge($_REQUEST,$tarray),$_SERVER['REQUEST_METHOD'],$_FILES);
		$response = new Response($request);
	}
	$DIR_ASSETS = 'http://onec.localhost/assets';// Link::getBaseURL()."assets";
	// ul($DIR_ASSETS);
	
	function ul($expr){
		Utility::log($expr);
	}

	/**
	 * @param string $UID
	 * @param string $stateful
	 * @return string
	 */
	function state(string $UID,string $stateful):string{
		global $User;
		global $models;
		return (($models[$UID])?$User->getState($models[$UID]->getInstance($stateful)):'');
	}
	function stateInstance(string $UID,string $stateful):array{
		global $models;
		return $models[$UID]->getStateInstance($models[$UID]->getInstance($stateful))->getCache();
	}
	function transition(string $UID,string $stateful):array{
		global $User;
		global $models;
		return (($models[$UID])?$User->getTransition($models[$UID]->getInstance($stateful),true):'');
	}
	function route(string $index, array $args=[]){
		return Link::getURL($index,$args);
	}
	function smartDate(string $date):string{
		return Utility::smartDate(date_create($date));
	}
	function balance(string $currency=CURRENCY_NAIRA, string $account=ACCOUNT_BALANCE){
		global $admin;
		global $user;
		$balance = AccountController::getBalance($admin,$user,$currency,$account);
		return ['number'=>$balance->getNumber(),'unit'=>$balance->getUnit()];
	}
	function pref(string $entity, string $key,bool $debug=false, $default=''){
		global $Pref;
		if($rows = $Pref->getGeneric("WHERE entity='$entity' AND config='$key' ORDER BY id DESC LIMIT 1",$debug)){
			return (empty($rows[0]['value'])?$default:$rows[0]['value']);
		}
		return $default;
	}
	function ip(){
		return Utility::getClientIP();
	}
	function credential(string $provider,string $key){
		return Utility::getCredential($provider,$key);
	}
	function groupByDate(string $UID, string $filter, bool $debug=false):array{
		global $models;
		return $models[$UID]->groupByDate($filter,$debug);
	}
	function instancesGeneric(string $UID, string $filter, bool $debug=false):array{
		global $models;
		return $models[$UID]->getGeneric($filter,$debug);
	}
	function instancesIDMapped(string $UID, string $haystack=null, string $needle=null):array{
		global $models;
		$ret = [];
		if($haystack && isset($needle))
			$rows = $models[$UID]->getGeneric("WHERE `$haystack`='$needle'",false,'*');
		else
			$rows = $models[$UID]->getAll();

		foreach ($rows as $key => $value)
			$ret[$value['id']] = $value;

		return $ret;
	}
	function instances(string $UID, bool $map=true,string $haystack=null, string $needle=null):array{
		global $models;
		if($map)
			return $models[$UID]->getInstancesMap($haystack,$needle);
		else{
			if($haystack && isset($needle))
				return $models[$UID]->getGeneric("WHERE `$haystack`='$needle'",false,'*');
			else
				return $models[$UID]->getAll();
		}
	}
	function instancesPaginated(string $UID, string $haystack=null, string $needle=null, int $page=0, int $limit=30,bool $debug=false):array{
		global $models;
		if($haystack && isset($needle))
			return $models[$UID]->getPaginated("WHERE `$haystack`='$needle'",'*',$page,$limit,$debug);
		else
			return $models[$UID]->getAll();
	}
	/**
	 * @param string $UID
	 * @param string $key
	 * @param string $value
	 * @return array
	 */
	function fx(string $base, string $quote):array{
		global $models;
		return $models[TX_FX]->getGeneric("WHERE base='$base' AND quote='$quote' ORDER BY created_at DESC LIMIT 1")[0]??[];
	}
	function instance(string $UID,string $key, string $value):array{
		global $models;
		return $models[$UID]->get($key,$value)[0]??[];
	}
	function text($index){
		global $response;
		return $response->getText($index);
	}
	function url($index){
		global $response;
		return $response->getURL($index);
	}
	function request(string $index){
		global $request;
		return $request->getParam($index);
	}
	function param(string $index){
		global $request;
		return $request->getParam($index);
	}
	function variable(string $index){
		global $request;
		return $request->getVariable($index);
	}
	function admin(string $index):string{
		global $admin;
		return $admin->getProperty($index)??'';
	}
	function user(string $index):string{
		global $user;
		return $user->getProperty($index)??'';
	}
	function assets():string{
		global $DIR_ASSETS;
		return $DIR_ASSETS;
	}
	function files(string $id=null):string{
		global $File;
		return Link::getFile($File->getInstance($id));
	}
	function image(string $name):string{
		return Link::getBaseURL()."assets/img/$name";
	}
	function messages():array{
		global $response;
		return $response->getMessageBag();
	}
	function csrf_token():string{
		return Utility::getSession('csrf_token')[0]??'';
	}
	function user_id():string{
		return user('id');
	}
	function session(string $index):string{
		return Utility::getSession($index)[0]??'';
	}
?>

