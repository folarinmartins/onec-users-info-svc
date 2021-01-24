<?php
namespace controller;

use contract\Controller;
use view\Page;
use helper\Utility;
use contract\Message;
use comm\Comm;
use model\Model;
use comm\Link;
use contract\Event;
use Exception;
use Paystack\Payment\Payment;

// https://api.paystack.co/bank
class FIATController extends Controller{
	static function payoutFIAT(){
		global $user;
		global $request;
		global $response;
		global $Blockchain;
		if(SessionController::verifyCSRF()){
			try{
				// $btcWallet = FIATController::getUserWallet($user);
				// $sendResponse = $Blockchain->Wallet->send($request->getParam('address'), $request->getParam('value'), $btcWallet->getProperty('indexx'));
				// Utility::log($sendResponse->getPayload());
				// $response->setPayload($sendResponse->getPayload());
				// $response->addMessage(new Message('Transfer Successful','Transfer Successful',THEME_SECONDARY));
			}catch(\Exception $e){
				Utility::log($e->getMessage());
				Utility::log($request->getParams());
				$response->setStatusCode(500);
				$response->addMessage(new Message('Transfer Failed','Transfer Failed',THEME_SECONDARY));
			}
		}
	}
	static function pingFTX(){
		global $request;
		global $response;
		global $Paystack;
		global $Entry;
		global $BTCTransaction;
		global $admin;
		global $user;
		if(SessionController::verifyCSRF()){
			$btcTransaction = $BTCTransaction->getInstance(substr($request->getVariable('id'),0,13));
			switch($btcTransaction->getState()){
				case STATE_APPROVED:{
					$response->addPayload('status','success');
					$response->addMessage(new Message('FIAT Payment already verified','FIAT Payment already verified',THEME_INFO));
				}break;
				case STATE_PENDING:{
					if(($btcTransaction=$Paystack->Payment->verify($request->getVariable('id'))) && $btcTransaction->getID()){
						$entry = $Entry->insertMap([
							'name'=>'FIAT Payment Verified',
							'description'=>'FIAT Payment Verified',
							'party'=>$admin->getID(),
							'coparty'=>$user->getID(),
							'type'=>ACCOUNT_ENTRY_TYPE_IN,
							'account'=>ACCOUNT_BALANCE,
							'transaction'=>$btcTransaction->getID(),
							'value_number'=>$btcTransaction->getProperty('cleared_number'),
							'value_unit'=>$btcTransaction->getProperty('cleared_unit'),
						]);
						if($entry){
							$balance = AccountController::doBalance($admin,$user,$btcTransaction->getProperty('cleared_unit'),ACCOUNT_BALANCE);
							$response->addPayload('balance_number',$balance->getNumber());
							$response->addPayload('balance_unit',$balance->getUnit());
							$btcTransaction->setState(STATE_COMPLETED,$admin,'FIAT Payment Verified');

							Event::trigger($entry,ACTION_ACCOUNT_TOPUP,QUANTIFIER_ALL,null,null);
							$response->addPayload('status','success');
							$response->addMessage(new Message('FIAT payment successsful','FIAT payment successsful',THEME_INFO));
						}else{
							$response->addPayload('status','pending');
							$response->addMessage(new Message('FIAT payment verified but not booked','FIAT payment verified but not booked',THEME_INFO));
							$response->setStatusCode(500);
						}
					}else{
						$response->addPayload('status','pending');
						$response->addMessage(new Message('FIAT payment still pending verification','FIAT payment still pending verification',THEME_INFO));
					}
				}break;
				case STATE_DECLINED:{
					$response->addPayload('status','declined');
					$response->addMessage(new Message('FIAT payment declined','FIAT payment declined',THEME_INFO));
				}break;
				default:{
					$response->addPayload('status','undefined');
					$response->addMessage(new Message('Transaction state unrecognized','Transaction state unrecognized',THEME_INFO));
				}
			}

		}
	}
	static function hookFTX(){
		global $request;
		global $admin;
		global $BTCTransaction;
		global $BTCAddress;
		if(true || SessionController::verifyCSRF()){

		}
	}
	static function initWithdraw(){
		global $request;
		global $response;
		global $BTCTransaction;
		global $user;
		global $admin;
		global $Currency;
		if(SessionController::verifyCSRF()){
			try{
				$feeQty = APIController::getWithdrawalFee($Currency->getInstance($request->getParam('unit')));
				if($admin->getPref(CONFIG_CHARGE_WITHDRAWAL_FEE_ON_BALANCE,true)){
					$totalFiat = bcadd($feeQty->getNumber(),$request->getParam('value'),2);
				}else{
					$totalFiat = bcsub($feeQty->getNumber(),$request->getParam('value'),2);
				}

				$btcTransaction = $BTCTransaction->insertMap([
					'name'=>'Payout Request',
					'description'=>'Payout Request',
					'user'=>$user->getID(),
					'type'=>TX_TYPE_PAYOUT,
					'address'=>$request->getParam('account'),
					'hash'=>$request->getParam('account'),
					'value_number'=>bcadd($request->getParam('value'),0,2),
					'value_unit'=>$request->getParam('unit'),
					'fee_number'=>$feeQty->getNumber(),
					'fee_unit'=>$feeQty->getUnit(),
					'rate_number'=>1,
					'rate_unit'=>$request->getParam('unit'),
					'cleared_number'=>$totalFiat,
					'cleared_unit'=>$request->getParam('unit'),
				]);

				if($btcTransaction){
					$btcTransaction->setState(STATE_PENDING,$user,'New payout process initiated');
					Event::trigger($btcTransaction,ACTION_PAYOUT_REQUESTED,QUANTIFIER_ALL,null,null);
					$response->addMessage(new Message('Payout request processing','Payout request processing',THEME_SECONDARY));
				}else{
					$response->addMessage(new Message('Error initializing withdrawal','Error initializing withdrawal',THEME_SECONDARY));
				}
			}catch(Exception $e){
				Utility::log($e->getMessage());
				$response->addMessage(new Message($e->getMessage(),$e->getMessage(),THEME_SECONDARY));
				$response->setStatusCode(500);
			}
		}
	}
	static function initTopup(){
		global $request;
		global $response;
		global $BTCTransaction;
		global $user;
		if(SessionController::verifyCSRF()){
			try{
				$fee = 0;//bcmul(0,$request->getParam('value'),2);
				$totalFiat = bcadd($fee,$request->getParam('value'),2);
				$btcTransaction = $BTCTransaction->insertMap([
					'name'=>'Inbound FIAT Transaction',
					'description'=>'FIAT Account Top Up',
					'user'=>$user->getID(),
					'type'=>TX_TYPE_TOPUP,
					'address'=>'NULL',
					'value_number'=>bcadd($request->getParam('value'),0,2),
					'value_unit'=>$request->getParam('currency'),
					'fee_number'=>$fee,
					'fee_unit'=>$request->getParam('currency'),
					'rate_number'=>1,
					'rate_unit'=>$request->getParam('currency'),
					'cleared_number'=>bcmul(0,0,0),
					'cleared_unit'=>$request->getParam('currency'),
				]);

				if($btcTransaction){
					$btcTransaction->setState(STATE_PENDING,$user,'New FIAT transaction initiated');
					$response->addPayload('desc',$btcTransaction->getProperty('description'));
					$response->addPayload('total',$totalFiat);
					$response->addPayload('symbol',instance(TX_CURRENCY,'id',$request->getParam('currency')));
					$response->addPayload('txid',$btcTransaction->getID());
				}else{
					$response->addMessage(new Message('Error initializing transaction 2','Error initializing transaction 2',THEME_SECONDARY));
				}
			}catch(Exception $e){
				Utility::log($e->getMessage());
				$response->addMessage(new Message($e->getMessage(),$e->getMessage(),THEME_SECONDARY));
				$response->setStatusCode(500);
			}
		}
	}
	static function initReceivePayment(){
		global $request;
		global $response;
		global $BTCTransaction;
		global $user;
		if(SessionController::verifyCSRF()){
			try{
				$fee = 0;//bcmul(0,$request->getParam('value'),2);
				$totalFiat = bcadd($fee,$request->getParam('value'),2);
				$btcTransaction = $BTCTransaction->insertMap([
					'name'=>'Inbound FIAT Transaction',
					'description'=>$request->getParam('desc'),
					'type'=>TX_TYPE_MERCHANT,
					'user'=>$user->getID(),
					'address'=>'NULL',
					'value_number'=>bcadd($request->getParam('value'),0,2),
					'value_unit'=>$request->getParam('currency'),
					'fee_number'=>$fee,
					'fee_unit'=>$request->getParam('currency'),
					'rate_number'=>1,
					'rate_unit'=>$request->getParam('currency'),
					'cleared_number'=>bcmul(0,0,2),
					'cleared_unit'=>$request->getParam('currency'),
				]);

				if($btcTransaction){
					$btcTransaction->setState(STATE_PENDING,$user,'New FIAT transaction initiated');
					$response->addPayload('desc',$btcTransaction->getProperty('description'));
					$response->addPayload('total',$totalFiat);
					$response->addPayload('symbol',instance(TX_CURRENCY,'id',$request->getParam('currency')));
					$response->addPayload('txid',$btcTransaction->getID());
				}else{
					$response->addMessage(new Message('Error initializing transaction 2','Error initializing transaction 2',THEME_SECONDARY));
				}
			}catch(Exception $e){
				Utility::log($e->getMessage());
				$response->addMessage(new Message($e->getMessage(),$e->getMessage(),THEME_SECONDARY));
				$response->setStatusCode(500);
			}
		}
	}
}