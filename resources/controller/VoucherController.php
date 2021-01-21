<?php
namespace controller;

use contract\Controller;
use view\Page;
use helper\Utility;
use contract\Message;
use comm\Link;
use contract\Event;
use Exception;

class VoucherController extends Controller{
	static function showRedeem(){
		global $response;
		$text = ['page.title'=>'Redeem'];
		$variables = ['text'=>$text];
		$page = Page::getInstance($variables,'redeem');
		$response->setPage($page);
	}
	static function redeemVTX(){
		global $request;
		global $response;
		global $BTCVoucher;
		global $Blockchain;
		global $Entry;
		global $admin;
		if(SessionController::verifyCSRF()){
			try{
				$vouchers = $BTCVoucher->get('code',str_replace(' ','',$request->getParam('code')));
				if($vouchers){
					$btcVoucher = $BTCVoucher->getInstance($vouchers[0]['id']);
					Utility::log($btcVoucher->getCache());
					Utility::log("Here's the current state of voucher: ".$btcVoucher->getState()." comparing: ");
					if($btcVoucher->getState()==STATE_APPROVED){
						if(Utility::getFullDifff('s',Utility::addDays(date_create(),'+',$btcVoucher->getProperty('validity_days').' days'),date_create($btcVoucher->getProperty('created_at')))>=0){
							$btcTransaction = $Blockchain->Wallet->send($request->getParam('address'),$btcVoucher->getProperty('value_number'),BTCController::getUserWallet($admin)->getProperty('indexx'),$admin,null,$admin->getPref(CONFIG_BLOCKCHAIN_SATOSHI_PER_BYTE,null));
							if($btcTransaction->getID()){
								$btcVoucher->setState(STATE_COMPLETED,$admin,'Voucher cleared');
								$entry = $Entry->insertMap([
									'name'=>'CR:Voucher Redemption',
									'description'=>'CR:Voucher Redemption',
									'party'=>$admin->getID(),
									'coparty'=>$admin->getID(),
									'type'=>ACCOUNT_ENTRY_TYPE_OUT,
									'account'=>ACCOUNT_BALANCE,
									'transaction'=>$btcTransaction->getID(),
									'value_number'=>$btcTransaction->getProperty('cleared_number'),
									'value_unit'=>$btcTransaction->getProperty('cleared_unit'),
								]);
								$response->addPayload('explorer','https://www.blockchain.com/btc/tx/'.$btcTransaction->getProperty('hash'));
								if($entry){
									$response->addMessage(new Message('Voucher Redeemed','Voucher Redeemed',THEME_SECONDARY));
								}else{
									$response->addMessage(new Message('Voucher Redeemed, but not booked','Voucher Redeemed, but not booked',THEME_SECONDARY));
								}
							}else{
								$response->addMessage(new Message('Voucher Redemption Failed','Voucher Redemption Failed',THEME_SECONDARY));
								$response->setStatusCode(511);
							}
						}else{
							$response->setStatusCode(422);
							$response->addMessage(new Message('Expired Voucher','Expired Voucher: ('.Utility::getFullDifff('s',Utility::addDays(date_create(),'+',VOUCHER_VALIDITY_DAYS.' days'),date_create($btcVoucher->getProperty('created_at'))).')',THEME_SECONDARY));
						}
					}else{
						$response->setStatusCode(422);
						switch($btcVoucher->getState()){
							case STATE_DEFAULT:{
								$response->addMessage(new Message('Unapproved Voucher','Unapproved Voucher',THEME_SECONDARY));
							}break;
							case STATE_COMPLETED:{
								$response->addMessage(new Message('Voucher already redeemed','Voucher already redeemed',THEME_SECONDARY));
							}break;
							default:{
								$response->addMessage(new Message('Invalid Voucher State','Invalid Voucher State',THEME_SECONDARY));
							}
						}
					}
				}else{
					$response->setStatusCode(422);
					$response->addMessage(new Message('Invalid voucher code','Invalid voucher code',THEME_SECONDARY));
				}
			}catch(Exception $e){
				Utility::log($e->getMessage());
				$response->addMessage(new Message($e->getMessage(),$e->getMessage(),THEME_SECONDARY));
				$response->setStatusCode(500);
			}
		}
	}
	static function issueVTX(){
		global $request;
		global $response;
		global $BTCVoucher;
		global $BTCTransaction;
		global $Currency;
		global $Entry;
		global $user;
		global $admin;
		if(SessionController::verifyCSRF()){
			try{
				$fiat = $Currency->getInstance(CURRENCY_NAIRA);
				$usd = $Currency->getInstance(CURRENCY_USD);
				$crypto = $Currency->getInstance(CURRENCY_BTC);

				$fiatValue = 0;
				$cryptoValue = 0;
				$commission = 0;
				$networkFee = APIController::getNetworkFee($fiat);

				$rate = APIController::getFX($crypto,$usd)*APIController::getFX($usd,$fiat);
				if(!Utility::in_array($request->getParam('currency'),Utility::linearize($Currency->get('crypto','1'),'id'))){
					$fiatValue = $request->getParam('value');
				}else{
					$fiatValue = $request->getParam('value')*$rate;
				}
				$adminComm = $fiatValue*APIController::getVoucherAdminCommission()/100;
				$vendorComm = $fiatValue*APIController::getVoucherVendorCommission()/100;
				$commission = $adminComm + $vendorComm;
				$networkFee = APIController::getNetworkFee($fiat)->getNumber();

				$totalFiat = $fiatValue;
				if(pref($user->getID(),CONFIG_VENDOR_VOUCHER_LESS_NETWORK_FEE)){
					$fiatValue -= $networkFee;
				}else{
					$totalFiat += $networkFee;
				}
				if(pref($user->getID(),CONFIG_VENDOR_VOUCHER_LESS_COMMISSION)){
					$fiatValue -= $commission;
				}else{
					$totalFiat += $commission;
				}

				$balanceQty = AccountController::getBalance($admin,$user,$fiat->getID(),ACCOUNT_BALANCE);
				if($balanceQty->getNumber() >= $totalFiat){
					$cryptoValue = bcdiv($fiatValue,$rate,8);

					$btcVoucher = $BTCVoucher->insertMap([
						'name'=>'BTC Voucher',
						'description'=>'New BTC Voucher Issue',
						'user'=>$user->getID(),
						'code'=>APIController::getVoucherCode(),
						'validity_days'=>APIController::getVoucherValidity(),
						'value0_number'=>$request->getParam('value'),
						'value0_unit'=>$request->getParam('currency'),
						'value_number'=>$cryptoValue,
						'value_unit'=>$crypto->getID(),
						'value_fiat_number'=>$fiatValue,
						'value_fiat_unit'=>$fiat->getID(),
						'commission_number'=>bcadd($commission,0,2),
						'commission_unit'=>$fiat->getID(),
						'commission_admin_number'=>bcadd($adminComm,0,2),
						'commission_admin_unit'=>$fiat->getID(),
						'commission_vendor_number'=>bcadd($vendorComm,0,2),
						'commission_vendor_unit'=>$fiat->getID(),
						'network_fee_number'=>bcadd($networkFee,0,2),
						'network_fee_unit'=>$fiat->getID(),
						'rate_number'=>$rate,
						'rate_unit'=>$fiat->getID(),
						'cleared_number'=>bcadd($totalFiat,0,2),
						'cleared_unit'=>$fiat->getID(),
					]);

					$btcTransaction = $BTCTransaction->insertMap([
						'name'=>'Voucher Purchase',
						'description'=>'Voucher Purchase',
						'type'=>TX_TYPE_VOUCHER_PURCHASE,
						'hash'=>$btcVoucher->getID(),
						'user'=>$user->getID(),
						'address'=>'NULL',
						'value_number'=>$btcVoucher->getProperty('cleared_number'),
						'value_unit'=>$btcVoucher->getProperty('cleared_unit'),
						'fee_number'=>bcadd($commission+$networkFee,0,2),
						'fee_unit'=>$btcVoucher->getProperty('commission_unit'),
						'rate_number'=>$btcVoucher->getProperty('rate_number'),
						'rate_unit'=>$btcVoucher->getProperty('rate_unit'),
						'cleared_number'=>$btcVoucher->getProperty('cleared_number'),
						'cleared_unit'=>$btcVoucher->getProperty('cleared_unit'),
					]);

					$entry = $Entry->insertMap([
						'name'=>'DR:Voucher Purchase',
						'description'=>'DR:Voucher Purchase',
						'party'=>$admin->getID(),
						'coparty'=>$user->getID(),
						'type'=>ACCOUNT_ENTRY_TYPE_OUT,
						'account'=>ACCOUNT_BALANCE,
						'transaction'=>$btcTransaction->getID(),
						'value_number'=>bcsub($btcTransaction->getProperty('cleared_number'),$vendorComm,2),
						'value_unit'=>$btcTransaction->getProperty('cleared_unit'),
					]);
					$entry2 = $Entry->insertMap([
						'name'=>'DR:Voucher Commission',
						'description'=>'DR:Voucher Commission',
						'party'=>$admin->getID(),
						'coparty'=>$user->getID(),
						'type'=>ACCOUNT_ENTRY_TYPE_IN,
						'account'=>ACCOUNT_COMMISSION,
						'transaction'=>$btcTransaction->getID(),
						'value_number'=>$btcVoucher->getProperty('commission_vendor_number'),
						'value_unit'=>$btcVoucher->getProperty('commission_vendor_unit'),
					]);
					$entry3 = $Entry->insertMap([
						'name'=>'DR:Voucher Commission',
						'description'=>'DR:Voucher Commission',
						'party'=>$admin->getID(),
						'coparty'=>$admin->getID(),
						'type'=>ACCOUNT_ENTRY_TYPE_IN,
						'account'=>ACCOUNT_COMMISSION,
						'transaction'=>$btcTransaction->getID(),
						'value_number'=>$btcVoucher->getProperty('commission_admin_number'),
						'value_unit'=>$btcVoucher->getProperty('commission_admin_unit'),
					]);
					$entry4 = $Entry->insertMap([
						'name'=>'DR:Voucher Sale',
						'description'=>'DR:Voucher Sale',
						'party'=>$admin->getID(),
						'coparty'=>$user->getID(),
						'type'=>ACCOUNT_ENTRY_TYPE_IN,
						'account'=>ACCOUNT_SALES,
						'transaction'=>$btcTransaction->getID(),
						'value_number'=>$btcTransaction->getProperty('cleared_number'),
						'value_unit'=>$btcTransaction->getProperty('cleared_unit'),
					]);

					//TODO: Account for booked vs actual network fees in any period. That is, make the initial predicted value into NETWORK_FEES_PREPAID_ACCOUNT vs NETWORK_FEES_EXPENSES_ACCOUNT
					$balanceQty = AccountController::getBalance($admin,$user,$fiat->getID(),ACCOUNT_BALANCE);
					$balanceQty2 = AccountController::getBalance($admin,$user,$fiat->getID(),ACCOUNT_COMMISSION);
					$balanceQty3 = AccountController::getBalance($admin,$user,$fiat->getID(),ACCOUNT_SALES);

					if($btcVoucher){
						$response->addPayload('currency',$crypto->getCache());
						$response->addPayload('currency-fiat',$fiat->getCache());
						$response->addPayload('txid',$btcVoucher->getID());
						$code = $btcVoucher->getProperty('code');
						$response->addPayload('code',$btcVoucher->getProperty('code'));
						$response->addPayload('code-fmt',substr($code,0,4).' '.substr($code,4,4).' '.substr($code,8,4).' '.substr($code,12,4));
						$response->addPayload('value',$btcVoucher->getProperty('value_number'));
						$response->addPayload('value-fiat',bcadd($totalFiat,0,2));
						$response->addPayload('validity',Utility::addDays(date_create(),'+',VOUCHER_VALIDITY_DAYS.' days')->format('M d, H:i'));

						$response->addPayload('commission_number',$btcVoucher->getProperty('commission_number'));
						$response->addPayload('commission_unit',$btcVoucher->getProperty('commission_unit'));

						$response->addPayload('network_fee_number',$btcVoucher->getProperty('network_fee_number'));
						$response->addPayload('network_fee_unit',$btcVoucher->getProperty('network_fee_unit'));

						$response->addPayload('balance_number',$balanceQty->getNumber());
						$response->addPayload('balance_unit',$balanceQty->getUnit());
						$response->addPayload('commission_balance_number',$balanceQty2->getNumber());
						$response->addPayload('sales_balance_number',$balanceQty3->getNumber());

						if($entry){
							$response->addMessage(new Message('Voucher Issued','Voucher Issued, Ref: '.$btcVoucher->getProperty('code'),THEME_SECONDARY));
							Event::trigger($entry,ACTION_VOUCHER_ISSUE,QUANTIFIER_ALL,null,null);
							Event::trigger($entry3,ACTION_COMMISSION_REMITTED,QUANTIFIER_ALL,null,null);
						}else
							$response->addMessage(new Message('Voucher Issued, but not booked','Voucher Issued, but not booked',THEME_SECONDARY));

						$qrurl = Link::getURL('redeem').'/'.$btcVoucher->getProperty('code');
						$response->addPayload('redeem-url',$qrurl);
						$response->addPayload('qr',"https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=".urlencode($qrurl));
						$btcVoucher->setState(STATE_APPROVED,$user,'Voucher Dispensed');
						$btcTransaction->setState(STATE_APPROVED,$user,'Voucher Dispensed');
					}else{
						$response->setStatusCode(500);
						$response->addMessage(new Message('Error Issuing Voucher','Error Issuing Voucher',THEME_SECONDARY));
					}
				}else{
					$response->setStatusCode(422);
					$response->addMessage(new Message('Insufficient Funds','Insufficient Funds ('.$fiat->getProperty('symbol_char').round($fiatValue+$commission+$networkFee-$balanceQty->getNumber(),2).')',THEME_SECONDARY));
				}
			}catch(Exception $e){
				Utility::log($e->getMessage());
				$response->addMessage(new Message($e->getMessage(),$e->getMessage(),THEME_SECONDARY));
				$response->setStatusCode(500);
			}
		}
	}
	static function initVTX(){
		global $request;
		global $response;
		global $Currency;
		global $admin;
		global $user;
		if(SessionController::verifyCSRF()){
			try{
				$fiat = $Currency->getInstance($request->getParam('currency-fiat'));
				$usd = $Currency->getInstance(CURRENCY_USD);
				$crypto = $Currency->getInstance($request->getParam('currency-crypto'));
				$response->addPayload('crypto/fiat',APIController::getFX($crypto,$usd)*APIController::getFX($usd,$fiat));
				$response->addPayload('balance-fiat',AccountController::getBalance($admin,$user,$request->getParam('currency-fiat'),ACCOUNT_BALANCE)->getNumber());
				$response->addPayload('min-fiat',APIController::getVoucherMinimum($fiat)->getNumber());
				$response->addPayload('network-fee-fiat',APIController::getNetworkFee($fiat)->getNumber());
				$response->addPayload('comm-perc',APIController::getVoucherCommission());

				$response->addPayload('currency-fiat',$fiat->getCache());
				$response->addPayload('currency-crypto',$crypto->getCache());
			}catch(\Exception $e){
				$response->addMessage(new Message($e->getMessage(),$e->getMessage(),THEME_SECONDARY));
				$response->setStatusCode(500);
			}
		}
		// Utility::log($response->getPayload());
	}
}