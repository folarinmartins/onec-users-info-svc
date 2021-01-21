<?php
namespace controller;

use contract\Controller;
use helper\Utility;
use contract\Message;
use model\Model;
use comm\Link;
use contract\Event;
use Exception;

class BTCController extends Controller{
	/** @return void  */
	static function getUserWallet(Model $user,$forceNew=false):Model{
		global $Blockchain;
		global $BTCWallet;
		if(SessionController::verifyCSRF()){
			if(!$forceNew && ($wallets = $BTCWallet->getAllAtState(STATE_DEFAULT,'user',$user->getID()))){
				return $BTCWallet->getInstance($wallets[0]['id']);
			}else{
				return $Blockchain->Wallet->newHDAccount($user);
			}
		}
	}
	static function getUsableAddress(Model $user, bool $forceNew=false):?Model{
		global $Blockchain;
		global $BTCWallet;
		global $BTCAddress;
		if(SessionController::verifyCSRF()){
			$callbackUrl = 'https://blockstale.com/hooks-btx';
			$wallets = $BTCWallet->getGeneric("WHERE user='".$user->getID()."' AND archived<>'1' ORDER BY indexx ASC");
			foreach($wallets as $i=>$wallet){
				$btcWallet = $BTCWallet->getInstance($wallet['id']);
				$btcAddress = null;
				$xPub = $btcWallet->getProperty('xpub');
				if($forceNew){
					$btcAddress = $Blockchain->ReceiveV2->generate($btcWallet, $callbackUrl, GAP_LIMIT);
				}else
				if($freeAddresses = $BTCAddress->getAllAtState(STATE_DEFAULT,'wallet',$btcWallet->getID())){
					$btcAddress = $BTCAddress->getInstance($freeAddresses[0]['id']);
					// $Blockchain->ReceiveV2->balanceUpdate($btcAddress->getProperty('address'),$xPub,$callbackUrl);
				}else{
					$addresses = $BTCAddress->getGeneric("WHERE wallet='".$btcWallet->getID()."' ORDER BY indexx DESC LIMIT 1");
					if($addresses && $addresses[0]['indexx']>0){
						$gapResponse = $Blockchain->ReceiveV2->checkAddressGap($xPub);
						if($gapResponse->getPayloadValue('gap')<=GAP_LIMIT_THRESHOLD){
							$btcAddress = $Blockchain->ReceiveV2->generate($btcWallet,$callbackUrl, GAP_LIMIT);
						}else{
							continue;
							throw new \Exception('Address threshold reached, please try again later');
							//Generate new addresses here and pre-fill them with petty cash without a callback
						}
					}else{
						$btcAddress = $Blockchain->ReceiveV2->generate($btcWallet, $callbackUrl, GAP_LIMIT);
					}
				}
				return $btcAddress;
			}
			Utility::log('Wow... couldnt find a usable address');
			// return null;
			$btcWallet = BTCController::getUserWallet($user,true);
			return BTCController::getUsableAddress($user);
		}
	}
	static function send(){
		global $user;
		global $admin;
		global $request;
		global $response;
		global $Blockchain;
		global $Entry;
		if(SessionController::verifyCSRF()){
			try{
				ul($request->getParams());
				$btcTransaction = $Blockchain->Wallet->send($request->getParam('address'),$request->getParam('value'),BTCController::getUserWallet($admin)->getProperty('indexx'),$user,null,$admin->getPref(CONFIG_BLOCKCHAIN_SATOSHI_PER_BYTE,null));
				if($btcTransaction->getID()){
					$entry = $Entry->insertMap([
						'name'=>'DR:BTC Payout',
						'description'=>'DR:BTC Payout',
						'party'=>$admin->getID(),
						'coparty'=>$user->getID(),
						'type'=>ACCOUNT_ENTRY_TYPE_OUT,
						'account'=>ACCOUNT_BALANCE,
						'transaction'=>$btcTransaction->getID(),
						'value_number'=>$btcTransaction->getProperty('cleared_number'),
						'value_unit'=>$btcTransaction->getProperty('cleared_unit'),
					]);
					$response->addPayload('explorer','https://www.blockchain.com/btc/tx/'.$btcTransaction->getProperty('hash'));
					if($entry){
						$response->addMessage(new Message('Transfer Completed','Transfer Completed',THEME_SECONDARY));
					}else{
						$response->addMessage(new Message('Transfer Completed, but not booked','Transfer Completed, but not booked',THEME_SECONDARY));
					}
					Event::trigger($entry,ACTION_TRANSFER_COMPLETED);
				}else{
					$response->addMessage(new Message('Voucher Redemption Failed','Voucher Redemption Failed',THEME_SECONDARY));
					$response->setStatusCode(511);
				}
			}catch(\Exception $e){
				Utility::log($e->getMessage());
				$response->setStatusCode(500);
				$response->addMessage(new Message($e->getMessage(),$e->getMessage(),THEME_SECONDARY));
			}
		}
	}
	static function pingBTX(){
		global $request;
		global $response;
		global $admin;
		global $user;
		global $BTCTransaction;
		if(SessionController::verifyCSRF()){
			$btcTransaction = $BTCTransaction->getInstance($request->getVariable('id'));
			switch($btcTransaction->getState()){
				case STATE_PENDING:{
					$response->addPayload('status','pending');
					$response->addMessage(new Message('BTC Payment Pending','BTC Payment Pending',THEME_INFO));
				}break;
				case STATE_APPROVED:{
					$response->addPayload('status','approved');
					$response->addMessage(new Message('BTC Payment Approved','BTC Payment Approved',THEME_INFO));
				}break;
				case STATE_COMPLETED:{
					$balanceQTY = AccountController::getBalance($admin,$user,CURRENCY_NAIRA);
					$balanceQTY2 = AccountController::getBalance($admin,$user,CURRENCY_NAIRA,ACCOUNT_SALES);
					$response->addPayload('status','success');
					$response->addPayload('balance_number',$balanceQTY->getNumber());
					$response->addPayload('balance_unit',$balanceQTY->getUnit());
					$response->addPayload('sales_balance_number',$balanceQTY2->getNumber());
					$response->addPayload('sales_balance_unit',$balanceQTY2->getUnit());
					$response->addMessage(new Message('BTC Payment Successful','BTC Payment Successful',THEME_INFO));
				}break;
				case STATE_ABORTED:{
					$response->addPayload('status','aborted');
					$response->addMessage(new Message('BTC payment aborted','BTC payment aborted',THEME_INFO));
				}break;
				case STATE_DECLINED:{
					$response->addPayload('status','declined');
					$response->addMessage(new Message('BTC payment declined','BTC payment declined',THEME_INFO));
				}break;
				default:{
					$response->addPayload('status','undefined');
					$response->addMessage(new Message('Transaction state unrecognized','Transaction state unrecognized',THEME_INFO));
				}
			}
		}
	}
	static function hookBTX(){
		global $request;
		global $admin;
		global $BTCTransaction;
		if(true || SessionController::verifyCSRF()){
			$btcTransaction = $BTCTransaction->getInstance($request->getVariable('id'));
			if($request->getParam('confirmations')){
				$btcTransaction->setState(STATE_APPROVED,$admin,'Transaction verified by provider');
			}
			echo "*ok*";
			/*
				transaction_hash - The payment transaction hash.
				address - The destination bitcoin address (part of your xPub account).
				confirmations - The number of confirmations of this transaction.
				value - The value of the payment received (in satoshi, so divide by 100,000,000 to get the value in BTC).
				{custom parameter} - Any parameters included in the callback URL will be passed back to the callback URL in the notification. You can use this functionality to include parameters in your callback URL like invoice_id or customer_id to track which payments are associated with which of your customers.
			*/
		}
	}
	static function initReceivePayment(){
		global $request;
		global $response;
		global $Currency;
		global $BTCTransaction;
		global $user;
		global $admin;
		if(SessionController::verifyCSRF()){
			try{
				$crypto = $Currency->getInstance($request->getParam('currency'));
				$fiat = $Currency->getInstance($request->getParam('value-unit'));
				$usd = $Currency->getInstance(CURRENCY_USD);
				$rate = APIController::getFX($crypto,$usd,false)*APIController::getFX($usd,$fiat,false);
				$fee = number_format($request->getParam('value')*APIController::getMerchantCommission()/100,2,'.','');
				$valueFiat = $request->getParam('value');

				$totalFiat = $valueFiat;
				if($user->getPref(CONFIG_MERCHANTS_CHARGE_COMMISSION_ON_SALE,true)){
					$totalFiat += $fee;
				}else{
					$totalFiat += $fee;
				}

				$cryptoValue = number_format($valueFiat/$rate,8,'.','');
				$totalCrypto = number_format($totalFiat/$rate,8,'.','');

				$btcTransaction = $BTCTransaction->insertMap([
					'name'=>'Merchant BTC Payment',
					'description'=>$request->getParam('desc'),
					'user'=>$user->getID(),
					'type'=>TX_TYPE_MERCHANT,
					'address'=>'NULL',
					'value_number'=>bcadd($request->getParam('value'),0,2),
					'value_unit'=>$fiat->getID(),
					'fee_number'=>$fee,
					'fee_unit'=>$fiat->getID(),
					'rate_number'=>$rate,
					'rate_unit'=>$crypto->getID(),
					'cleared_number'=>$totalFiat,
					'cleared_unit'=>$fiat->getID(),
				]);

				if($btcAddress = BTCController::getUsableAddress($admin)){
					$balanceQTY = APIController::getBTCAddressBalance($btcAddress);

					$btcTransaction->updateMap(['address'=>$btcAddress->getID(),'balance0_number'=>$balanceQTY->getNumber(),'balance0_unit'=>$balanceQTY->getUnit()]);
					$btcAddress->setState(STATE_PENDING,$user,'BTC address engaged');
					$btcTransaction->setState(STATE_PENDING,$user,'New BTC transaction initiated');
					$response->addPayload('address',$btcAddress->getProperty('address'));
					$response->addPayload('desc',$btcTransaction->getProperty('description'));
					$response->addPayload('value-crypto', bcadd($cryptoValue,0,8));
					$response->addPayload('value-fiat',bcadd($btcTransaction->getProperty('value_number'),0,2));
					$response->addPayload('fee-crypto',number_format($fee/$btcTransaction->getProperty('rate_number'),8,'.',''));
					$response->addPayload('fee-fiat',bcadd($fee,0,2));
					$response->addPayload('total-crypto',$totalCrypto);
					$response->addPayload('total-fiat',bcadd($totalFiat,0,2));
					$response->addPayload('symbol-crypto',$crypto->getCache());
					$response->addPayload('symbol-fiat',$fiat->getCache());
					$response->addPayload('endpoint',Link::getBaseURL(false));
					$response->addPayload('txid',$btcTransaction->getID());
					$qrurl = urlencode('bitcoin:'.$btcAddress->getProperty('address')."?&amount=$totalCrypto&message=Blockstale Payments, LLC");
					$response->addPayload('qr',"https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=$qrurl");
					$response->addPayload('fiat/crypto',1/$btcTransaction->getProperty('rate_number'));
					$response->addPayload('crypto/fiat',$btcTransaction->getProperty('rate_number'));
				}else{
					$response->addMessage(new Message('Error initializing transaction 2','Error initializing transaction 2',THEME_SECONDARY));
				}
			}catch(Exception $e){
				Utility::log($e->getMessage());
				$response->addMessage(new Message($e->getMessage(),$e->getMessage(),THEME_SECONDARY));
				$response->setStatusCode(500);
			}
			// Utility::log($response->getPayload());
		}
	}
	static function initTopup(){
		global $request;
		global $response;
		global $BTCTransaction;
		global $Currency;
		global $user;
		global $admin;
		if(SessionController::verifyCSRF()){
			try{
				$crypto = $Currency->getInstance($request->getParam('currency'));
				$usd = $Currency->getInstance(CURRENCY_USD);
				$fiat = $Currency->getInstance(CURRENCY_NAIRA);

				$fx = APIController::getFX($crypto,$usd,false);
				$fx1 = APIController::getFX($usd,$fiat,false);
				$rate = $fx*$fx1;
				$fee = bcmul(0,0,2);
				$totalFiat = bcadd($fee,0,2);
				$totalCrypto = bcmul($totalFiat,0,8);

				$btcTransaction = $BTCTransaction->insertMap([
					'name'=>'Inbound BTC Transaction',
					'description'=>'BTC Balance Top Up',
					'user'=>$user->getID(),
					'type'=>TX_TYPE_TOPUP,
					'value_number'=>bcadd(10,0,2),
					'value_unit'=>$fiat->getID(),
					'fee_number'=>$fee,
					'fee_unit'=>$fiat->getID(),
					'rate_number'=>$rate,
					'rate_unit'=>$crypto->getID(),
					'cleared_number'=>bcmul(0,0,0),
					'cleared_unit'=>$crypto->getID(),
				]);

				if($btcAddress = BTCController::getUsableAddress($admin)){
					$balanceQTY = APIController::getBTCAddressBalance($btcAddress);
					$btcTransaction->updateMap(['address'=>$btcAddress->getID(),'balance0_number'=>$balanceQTY->getNumber(),'balance0_unit'=>$balanceQTY->getUnit()]);
					$btcAddress->setState(STATE_PENDING,$user,'BTC address engaged');
					$btcTransaction->setState(STATE_PENDING,$user,'New BTC transaction initiated');
					$response->addPayload('address',$btcAddress->getProperty('address'));
					$response->addPayload('desc',$btcTransaction->getProperty('description'));
					$response->addPayload('value-crypto', bcmul(0,0,8));
					$response->addPayload('value-fiat',0);
					$response->addPayload('fee-crypto',bcmul($fee,0,8));
					$response->addPayload('fee-fiat',$fee);
					$response->addPayload('total-crypto',$totalCrypto);
					$response->addPayload('total-fiat',$totalFiat);
					$response->addPayload('symbol-crypto',$crypto->getCache());
					$response->addPayload('symbol-fiat',$fiat->getCache());
					$response->addPayload('symbol-fiat-0',$usd->getCache());
					$response->addPayload('endpoint',Link::getBaseURL(false));
					$response->addPayload('txid',$btcTransaction->getID());
					$qrurl = urlencode($btcAddress->getProperty('address'));
					$response->addPayload('qr',"https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=$qrurl");
					$response->addPayload('usd/fiat',bcadd($fx1,0,2));
					$response->addPayload('crypto/usd',bcadd($btcTransaction->getProperty('rate_number'),0,2));
				}else{
					$response->setStatusCode(500);
					$response->addMessage(new Message('Error initializin BTC Topup','Error initializin BTC Topup',THEME_SECONDARY));
				}
			}catch(Exception $e){
				Utility::log($e->getMessage());
				$response->addMessage(new Message($e->getMessage(),$e->getMessage(),THEME_SECONDARY));
				$response->setStatusCode(500);
			}
		}
	}
}