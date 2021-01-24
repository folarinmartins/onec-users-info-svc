<?php
namespace controller;

use contract\Controller;
use helper\Utility;
use contract\Event;
use Exception;

class CronController extends Controller{
	/** @return void  */
	public static function zombieBTXJob(){
		global $BTCTransaction;
		global $BTCAddress;
		global $Transition;
		global $admin;
		
		$LAPSE = time();

		if($PID = CronController::register($JOB = CRON_JOB_ZOMBIE_BTX)){
			$log = Utility::startLog('Transaction Queue Mgr');
			$query = "t1 WHERE (SELECT staten from ".$Transition->getTable()." t2 WHERE t2.stateful=t1.id ORDER BY t2.id DESC LIMIT 1 ) IN ('".STATE_DEFAULT."','".STATE_PENDING."','".STATE_APPROVED."') AND `balance0_unit` IN ('".CURRENCY_BTC."') AND `address` IS NOT NULL AND t1.created_at < DATE_SUB(CURRENT_TIMESTAMP,INTERVAL ".$admin->getPref(CONFIG_PLAF_BTC_ADDRESS_RECYCLE_INTERVAL_MINS,BTC_ADDRESS_RECYCLE_INTERVAL_MINS)." MINUTE)";
			$transactions = $BTCTransaction->getAdvanced('t1.id',$query);
			Utility::log("Found ".count($transactions)." Zombie transactions");
			foreach($transactions as $i=>$transaction){
				$txLog = Utility::startLog("Checking transaction $i of " .count($transactions)." id:".$transaction['id']);
				$btcTransaction = $BTCTransaction->getInstance($transaction['id']);
				$btcTransaction->setState(STATE_ABORTED,$admin,'Zombie TX Aborted');
				$btcAddress = $BTCAddress->getInstance($btcTransaction->getProperty('address'));
				if(($btcAddress->getID() && $btcAddress->getCache())){
					$btcAddress->setState(STATE_DEFAULT,$admin,'Address Reclaimed from Zombie TX');
				}
				Event::trigger($btcTransaction,ACTION_ZOMBIE_TRANSACTION_ABORTED,QUANTIFIER_ALL,null,null);
				Utility::getLog($txLog);
				if(time()-$LAPSE > 60){
					$LAPSE = time();
					if(!CronController::canContinue($JOB,$PID))
						return;
				}
			}
			Utility::getLog($log);
			CronController::done($JOB,$PID);
		}
	}
	/** @return void  */
	public static function btxJob(){
		global $BTCTransaction;
		global $BTCAddress;
		global $Transition;
		global $Entry;
		global $admin;
		
		$LAPSE = time();

		if($PID = CronController::register($JOB = CRON_JOB_BTX)){
			$log = Utility::startLog('BTX Queue Mgr');
			$query = "t1 WHERE (SELECT staten from ".$Transition->getTable()." t2 WHERE t2.stateful=t1.id ORDER BY t2.id DESC LIMIT 1 ) IN ('".STATE_DEFAULT."','".STATE_PENDING."','".STATE_APPROVED."') AND `balance0_unit` IN ('".CURRENCY_BTC."') AND `address` IS NOT NULL";
			$transactions = $BTCTransaction->getAdvanced('t1.id',$query,true);
			ul("Found ".count($transactions)." BTX transactions");
			foreach($transactions as $i=>$transaction){
				$txLog = Utility::startLog("Checking transaction $i of " .count($transactions)." id:".$transaction['id']);
				$btcTransaction = $BTCTransaction->getInstance($transaction['id']);
				// ul($btcTransaction->getCache());
				ul("Now in transaction $i of " .count($transactions)." id:".$transaction['id'].' at state: '.$btcTransaction->getStateInstance()->getProperty('name'));
				$btcAddress = $BTCAddress->getInstance($btcTransaction->getProperty('address'));
				ul('Transaction used address: '.$btcAddress->getProperty('address').' with ID:'.$btcAddress->getID().' currently at state: '.$btcAddress->getStateInstance()->getProperty('name') );

				if(!($btcAddress->getID() && $btcAddress->getCache()))
					continue;
				if($btcAddress->getState()!=STATE_PENDING)
					continue;

				try{
					ul('State of transaction: '.$btcTransaction->getState());
					switch($btcTransaction->getState()){
						case STATE_PENDING:{
							ul('Transaction is currently pending...');
							$balanceQTY = APIController::getBTCAddressBalance($btcAddress);
							$totalCrypto = bcdiv($btcTransaction->getProperty('fee_number')+$btcTransaction->getProperty('value_number'),$btcTransaction->getProperty('rate_number'),8);
							if($balanceQTY->getNumber() >= $btcTransaction->getProperty('balance0_number')+$totalCrypto){
								ul('YES, now approving transaction');
								$btcTransaction->setState(STATE_APPROVED,$admin,'Transaction Initiated');
							}else{
								ul('Sorry Pending transacted funds less than expected...'.($btcTransaction->getProperty('balance0_number').' + '.$totalCrypto));
							}
						}break;
						case STATE_APPROVED:{
							ul('Transaction is currently approved...');
							$balanceQTY = APIController::getConfirmedBTCAddressBalance($btcAddress);
							ul('just got address balance...');
							ul($balanceQTY);
							$totalCrypto = ($btcTransaction->getProperty('fee_number')+$btcTransaction->getProperty('value_number'))/$btcTransaction->getProperty('rate_number');

							if($balanceQTY->getNumber() >= $btcTransaction->getProperty('balance0_number')+$totalCrypto){
								ul('YES transaction passed');
								$revenueBTC = $balanceQTY->getNumber()-$btcTransaction->getProperty('balance0_number');
								$revenueFIAT = $btcTransaction->getProperty('rate_number')*$revenueBTC;
								ul("Revenue BTC: $revenueBTC, Revenue FIAT:$revenueFIAT, rate:".$btcTransaction->getProperty('rate').' unit:'.$btcTransaction->getProperty('rate_unit'));
								switch($btcTransaction->getProperty('type')){
									case TX_TYPE_TOPUP:{
										$btcTransaction->updateMap([
											'value_number'=>$revenueFIAT,
											'cleared_number'=>$revenueBTC,
											'cleared_unit'=>$balanceQTY->getUnit()
											]
										);
										$balanceEntry = $Entry->insertMap([
											'name'=>'BTC Account Topup',
											'description'=>'BTC Account Topup',
											'party'=>$admin->getID(),
											'coparty'=>$btcTransaction->getProperty('user'),
											'type'=>ACCOUNT_ENTRY_TYPE_IN,
											'account'=>ACCOUNT_BALANCE,
											'transaction'=>$btcTransaction->getID(),
											'value_number'=>bcsub($revenueFIAT,$btcTransaction->getProperty('fee_number'),2),
											'value_unit'=>$btcTransaction->getProperty('value_unit'),
										]);
										$balanceEntry2 = $Entry->insertMap([
											'name'=>'Account Topup BTC IN',
											'description'=>'Account Topup BTC IN',
											'party'=>$admin->getID(),
											'coparty'=>$admin->getID(),
											'type'=>ACCOUNT_ENTRY_TYPE_IN,
											'account'=>ACCOUNT_BALANCE,
											'transaction'=>$btcTransaction->getID(),
											'value_number'=>$revenueBTC,
											'value_unit'=>$balanceQTY->getUnit(),
										]);
										
										if($balanceEntry){
											Event::trigger($balanceEntry,ACTION_ACCOUNT_TOPUP);
										}else{
											Utility::log('BTC Topup verified but not booked','BTC Topup verified but not booked');
										}										
										if($balanceEntry2){
											Event::trigger($balanceEntry2,ACTION_ACCOUNT_TOPUP);
										}else{
											Utility::log('BTC Topup verifed but BTC Account not booked'.$btcTransaction->getID(),'BTC Topup verifed but BTC Account not booked'.$btcTransaction->getID());
										}
									}break;
									case TX_TYPE_MERCHANT:{
										$balanceEntry = $Entry->insertMap([
											'name'=>'Merchant Payment Verified',
											'description'=>'Merchant Payment Verified',
											'party'=>$admin->getID(),
											'coparty'=>$btcTransaction->getProperty('user'),
											'type'=>ACCOUNT_ENTRY_TYPE_IN,
											'account'=>ACCOUNT_BALANCE,
											'transaction'=>$btcTransaction->getID(),
											'value_number'=>$revenueFIAT-$btcTransaction->getProperty('fee_number'),
											'value_unit'=>$btcTransaction->getProperty('value_unit'),
										]);
										$inflowEntry = $Entry->insertMap([
											'name'=>'BTC Inflow via Merchant Sale',
											'description'=>'BTC Inflow via Merchant Sale',
											'party'=>$admin->getID(),
											'coparty'=>$admin->getID(),
											'type'=>ACCOUNT_ENTRY_TYPE_IN,
											'account'=>ACCOUNT_BALANCE,
											'transaction'=>$btcTransaction->getID(),
											'value_number'=>$revenueBTC,
											'value_unit'=>$balanceQTY->getUnit(),
										]);
										$salesEntry = $Entry->insertMap([
											'name'=>'Merchant BTC IN',
											'description'=>'Merchant BTC IN',
											'party'=>$admin->getID(),
											'coparty'=>$btcTransaction->getProperty('user'),
											'type'=>ACCOUNT_ENTRY_TYPE_IN,
											'account'=>ACCOUNT_SALES,
											'transaction'=>$btcTransaction->getID(),
											'value_number'=>$btcTransaction->getProperty('value_number'),
											'value_unit'=>$btcTransaction->getProperty('value_unit'),
										]);
										$commissionEntry = $Entry->insertMap([
											'name'=>'Merchant Commission Remittance',
											'description'=>'Merchant Commission Remittance',
											'party'=>$admin->getID(),
											'coparty'=>$admin->getID(),
											'type'=>ACCOUNT_ENTRY_TYPE_IN,
											'account'=>ACCOUNT_COMMISSION,
											'transaction'=>$btcTransaction->getID(),
											'value_number'=>$revenueFIAT-$balanceEntry->getProperty('value_number'),
											'value_unit'=>$balanceEntry->getProperty('value_unit'),
										]);
										if($commissionEntry){
											Event::trigger($commissionEntry,ACTION_COMMISSION_REMITTED);
										}else{
											Utility::log('BTC payment verifed but commission not booked TX-ID:'.$btcTransaction->getID(),'BTC payment verifed but commission not booked TX-ID:'.$btcTransaction->getID());
										}
										if($inflowEntry){
											Event::trigger($inflowEntry,ACTION_ACCOUNT_TOPUP);
										}else{
											Utility::log('BTC payment verifed but BTC Account not booked'.$btcTransaction->getID(),'BTC payment verifed but BTC Account not booked'.$btcTransaction->getID());
										}
										if($balanceEntry){
											Event::trigger($balanceEntry,ACTION_MERCHANT_PAID);
										}else{
											Utility::log('BTC payment verified but not booked TX-ID:'.$btcTransaction->getID(),'BTC payment verified but not booked TX-ID:'.$btcTransaction->getID());
										}
									}break;
								}
								$btcAddress->setState(STATE_DEFAULT,$admin,'Recycling BTC Address');
								$btcTransaction->setState(STATE_COMPLETED,$admin,'BTC Payment Verified');
							}else{
								ul('Sorry transacted funds less than expected...'.($btcTransaction->getProperty('balance0_number')+$totalCrypto));
								ul($balanceQTY->getNumber() .'>='. $btcTransaction->getProperty('balance0_number').'+'.$totalCrypto.' = '.($btcTransaction->getProperty('balance0_number')+$totalCrypto));
							}
						}break;
						default:{
							ul('Transaction in an unrecognized state');
						}
					}
				}catch(Exception $e){
					ul("Checking transaction $i throws exception ".$e->getMessage());
				}
				Utility::getLog($txLog);
				if(time()-$LAPSE > 60){
					$LAPSE = time();
					if(!CronController::canContinue($JOB,$PID))
						return;
				}
			}
			Utility::getLog($log);
			CronController::done($JOB,$PID);
		}else{
			ul('couldnt register  cron job');
		}
	}

	static function done(string $job,string $pid){
		global $Cron;
		if(CronController::getRunner($job)===$pid){
			$Cron->updateMap(['released_at'=>date_create()->format('Y-m-d H:i:s')],$job);
		}
	}
	static function getRunner(string $job):?string{
		global $Cron;
		global $admin;
		if($runners = $Cron->getGeneric("WHERE `id`='$job' AND `released_at` IS NULL AND CURRENT_TIMESTAMP < DATE_ADD(locked_at,INTERVAL ".$admin->getPref(CONFIG_PLAF_MAX_CRON_RUN_TIME,MAX_CRON_RUN_TIME)." MINUTE) ORDER BY `id` DESC LIMIT 1")){
			return $runners[0]['runner'];
		}
		return null;
	}
	static function register(string $job):?string{
		global $Cron;		
		if(!SessionController::verifyCSRF()){
			throw new \Exception("Unknown Invocation Source");
		}
			
		$pid = uniqid();
		if(CronController::isFree($job) && $Cron->updateMap(['runner'=>$pid,'locked_at'=>date_create()->format('Y-m-d H:i:s'),'released_at'=>null],$job)){
			return $pid;
		}
		return null;
	}
	static function canContinue(string $job,string $runner):bool{
		global $Cron;
		if(CronController::getRunner($job)===$runner){
			$Cron->updateMap(['locked_at'=>date_create()->format('Y-m-d H:i:s'),'released_at'=>null],$job);
			return true;
		}
		return false;
	}
	static function isFree(string $job):bool{
		return is_null(CronController::getRunner($job));
	}
}