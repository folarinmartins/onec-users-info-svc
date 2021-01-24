<?php
namespace controller;

use Blockchain\Conversion\Conversion;
use contract\Controller;
use helper\Utility;
use model\Model;
use contract\Quantity;
use Exception;

class APIController extends Controller{
	/**
	 * @param Model $btcAddress
	 * @return Quantity
	 * @throws Exception
	 */
	static function getConfirmedBTCAddressBalance(Model $btcAddress):Quantity{
		global $Blockchain;
		global $admin;
		$curlOpt = [CURLOPT_CONNECTTIMEOUT=>10];
		ul('About looking for confirmed balance');
		$confs = $admin->getPref(CONFIG_PLAF_BLOCK_CONFIRMATIONS,BTC_BLOCK_CONFIRMATIONS);
		try{
			ul('https://www.bitgo.com/api/v1/address/'.$btcAddress->getProperty('address').'...');
			$balJSON = Utility::curl('https://www.bitgo.com/api/v1/address/'.$btcAddress->getProperty('address'),null,true,$curlOpt);
			ul($balJSON);
			if(isset($balJSON['balance']) && !empty($balJSON['address']))
				return new Quantity(Conversion::BTC_int2str($balJSON['confirmedBalance']),CURRENCY_BTC);
			/*{
				[balance] => 23396
				[confirmedBalance] => 18155
				[unconfirmedSends] => 0
				[unconfirmedReceives] => 5241
				[spendableBalance] => 18155
				[sent] => 0
				[received] => 23396
				[address] => 17hCWCsWPm8VgDJRducz3DMh217Wyh2tAe
			}
			*/
		}catch(Exception $e){
			ul('https://www.bitgo.com/api/v1/address/'.$btcAddress->getProperty('address').' failed '.$e->getMessage());
		}

		try{
			ul('V2 API '.$btcAddress->getProperty('address').'...');
			$bal = $Blockchain->Explorer->getAddressBalance([$btcAddress->getProperty('address')],$confs);
			ul($bal);
			return new Quantity($bal,CURRENCY_BTC);
		}catch(Exception $e){
			ul('V2 '.$btcAddress->getProperty('address').' failed '.$e->getMessage());
		}


		try{
			ul('https://api.blockcypher.com/v1/btc/main/addrs/'.$btcAddress->getProperty('address').'..., retypin...');
			ul('https://api.blockcypher.com/v1/btc/main/addrs/'.$btcAddress->getProperty('address')."?confirmations=$confs");
			// https://api.blockcypher.com/v1/btc/main/addrs/1DEP8i3QJCsomS4BSMY2RpU1upv62aGvhD?confirmations=6
			$balJSON = Utility::curl('https://api.blockcypher.com/v1/btc/main/addrs/'.$btcAddress->getProperty('address')."?confirmations=$confs",null,true,$curlOpt);
			ul($balJSON);
			if(isset($balJSON['total_received']) && !empty($balJSON['address']))
				return new Quantity(Conversion::BTC_int2str($balJSON['final_balance']),CURRENCY_BTC);
			/*{
			"address": "1DEP8i3QJCsomS4BSMY2RpU1upv62aGvhD",
			"total_received": 4654010,
			"total_sent": 0,
			"balance": 4654010,
			"unconfirmed_balance": 0,
			"final_balance": 4654010,
			"n_tx": 16,
			"unconfirmed_n_tx": 0,
			"final_n_tx": 16,
			"txrefs": [
				{
				"tx_hash": "2684c187196bb5dc22cafb8f54825f2499fbf3912472a93c7f293a624e599977",
				"block_height": 651522,
				"tx_input_n": -1,
				"tx_output_n": 0,
				"value": 18682,
				"ref_balance": 4654010,
				"spent": false,
				"confirmations": 9404,
				"confirmed": "2020-10-06T13:15:30Z",
				"double_spend": false
				},
				{
				"tx_hash": "012954b7f9ed7a6ddc65cc697fdd98cddb5ff32f8c7e5d764d761f29620103a7",
				"block_height": 648648,
				"tx_input_n": -1,
				"tx_output_n": 0,
				"value": 18263,
				"ref_balance": 4635328,
				"spent": false,
				"confirmations": 12278,
				"confirmed": "2020-09-17T00:04:44Z",
				"double_spend": false
				}
			],
			"tx_url": "https://api.blockcypher.com/v1/btc/main/txs/"
			}

			*/
		}catch(Exception $e){
			ul('https://api.blockcypher.com/v1/btc/main/addrs/'.$btcAddress->getProperty('address')."?confirmations=$confs".' failed '.$e->getMessage());
		}

		throw new Exception('Could not get balance');
	}
	static function getBTCAddressBalance(Model $btcAddress):Quantity{
		$curlOpt = [CURLOPT_CONNECTTIMEOUT=>10];
		try{
			ul('https://www.bitgo.com/api/v1/address/'.$btcAddress->getProperty('address').'...');
			$balJSON = Utility::curl('https://www.bitgo.com/api/v1/address/'.$btcAddress->getProperty('address'),null,true,$curlOpt);
			ul($balJSON);
			if(isset($balJSON['balance']) && !empty($balJSON['address']))
				return new Quantity(Conversion::BTC_int2str($balJSON['balance']),CURRENCY_BTC);
			/*{
				[balance] => 23396
				[confirmedBalance] => 18155
				[unconfirmedSends] => 0
				[unconfirmedReceives] => 5241
				[spendableBalance] => 18155
				[sent] => 0
				[received] => 23396
				[address] => 17hCWCsWPm8VgDJRducz3DMh217Wyh2tAe
			}
			*/
		}catch(Exception $e){
			ul('https://www.bitgo.com/api/v1/address/'.$btcAddress->getProperty('address').' failed '.$e->getMessage());
		}

		try{
			if($page = Utility::curl("https://www.blockchain.com/btc/address/".$btcAddress->getProperty('address'),null,false,$curlOpt)){
				preg_match("/Final Balance.+\b(\d[.]\d{8}\b) BTC/U", $page[0], $matches);
				if(is_float($matches[1]))
					return new Quantity($matches[1],CURRENCY_BTC);
			}
		}catch(Exception $e){}

		try{
			ul('https://chain.api.btc.com/v3/address/'.$btcAddress->getProperty('address').'...');
			$balJSON = Utility::curl('https://chain.api.btc.com/v3/address/'.$btcAddress->getProperty('address'),null,true,$curlOpt);
			ul($balJSON);
			if(isset($balJSON['data']) && $balJSON['status']=='success')
				return new Quantity(Conversion::BTC_int2str($balJSON['data']['balance']),CURRENCY_BTC);
			/*{"data":
				{
					"address":"1KMxB3wwijm8r2MSGoAyGZA7k6RcFqcYsg",
					"received":16382,
					"sent":0,
					"balance":16382,
					"tx_count":8,
					"unconfirmed_tx_count":0,
					"unconfirmed_received":0,
					"unconfirmed_sent":0,
					"unspent_tx_count":8
				},
				"err_code":0,
				"err_no":0,
				"message":"success",
				"status":"success"
			}

			*/
		}catch(Exception $e){
			ul('https://chain.api.btc.com/v3/address/'.$btcAddress->getProperty('address').' failed '.$e->getMessage());
		}


		try{
			ul('https://api.blockcypher.com/v1/btc/main/addrs/'.$btcAddress->getProperty('address').'...');
			$balJSON = Utility::curl('https://api.blockcypher.com/v1/btc/main/addrs/'.$btcAddress->getProperty('address').'/balance');
			ul($balJSON);
			if(isset($balJSON['total_received']) && !empty($balJSON['address']))
				return new Quantity(Conversion::BTC_int2str($balJSON['final_balance']),CURRENCY_BTC);
			/*{
				"address": "1DEP8i3QJCsomS4BSMY2RpU1upv62aGvhD",
				"total_received": 4654010,
				"total_sent": 0,
				"balance": 4654010,
				"unconfirmed_balance": 0,
				"final_balance": 4654010,
				"n_tx": 16,
				"unconfirmed_n_tx": 0,
				"final_n_tx": 16
				}
			*/
		}catch(Exception $e){
			ul('https://api.blockcypher.com/v1/btc/main/addrs/'.$btcAddress->getProperty('address').' failed');
		}


		try{
			ul('https://api-r.bitcoinchain.com/v1/address/'.$btcAddress->getProperty('address').'...');
			$balJSON = Utility::curl('https://api-r.bitcoinchain.com/v1/address/'.$btcAddress->getProperty('address'));
			ul($balJSON);
			if(isset($balJSON['balance']) && !empty($balJSON['address']))
				return new Quantity($balJSON['balance'],CURRENCY_BTC);
			/*[
				{
					"address": "1Chain4asCYNnLVbvG6pgCLGBrtzh4Lx4b",
					"balance": 0.01921908,
					"hash_160": "80562b9db5f4e95fce228aeab3336032f2b76ce7",
					"total_rec": 0.01921908,
					"transactions": 2,
					"unconfirmed_transactions_count": "0"
				}
			] */
		}catch(Exception $e){
			ul('https://api-r.bitcoinchain.com/v1/address/'.$btcAddress->getProperty('address').' failed '.$e->getMessage());
		}

		throw new Exception('Could not get balance');
	}

	public static function reloadBlockchainWallets(){
		global $Blockchain;
		global $BTCWallet;
		global $admin;

		foreach($Blockchain->Wallet->listHDAccounts()->getPayload() as $i=>$wallet){
			Utility::log($wallet);
			$BTCWallet->insertMap([
				'user'=>$admin->getID(),
				'name'=>$wallet['label'],
				'balance_number'=>$wallet['balance'],
				'balance_unit'=>CURRENCY_BTC,
				'indexx'=>$wallet['index'],
				'xpub'=>$wallet['extendedPublicKey'],
				'xpriv'=>$wallet['extendedPrivateKey'],
				'archived'=>$wallet['archived'],
				'receive_account'=> uniqid(),
				'change_account'=> uniqid(),
			],true);
		}
	}
	static function initBanks(){
		global $Bank;
		try{
			$banks = Utility::curl('https://api.paystack.co/bank',[]);
			foreach($banks['data'] as $i=>$bank){
				$Bank->insertMap([
					'name'=>$bank['name'],'slug'=>$bank['slug'],'code'=>$bank['code'],'longcode'=>$bank['longcode'],'gateway'=>$bank['gateway']??'']
				);
			}
		}catch(\Exception $e){
			Utility::log($e->getMessage());
		}
	}
	static function getAPIKey():string{
		return Utility::characterize(Utility::randomToken(20),'-',8);
	}
	static function getVoucherCode():string{
		global $admin;
		$digits = pref($admin->getID(),CONFIG_VOUCHER_DIGITS)??VOUCHER_DIGITS;
		return random_int(10000000,99999999).random_int(10000000,99999999);
	}
	static function getMerchantCommission():float{
		global $admin;
		return $admin->getPref(CONFIG_MERCHANTS_SALE_COMMISSION,MERCHANT_COMMISSION_PERC);
	}
	static function getVoucherAdminCommission():float{
		global $admin;
		return $admin->getPref(CONFIG_PLAF_VOUCHER_COMMISSION,VOUCHER_COMMISSION_PERC);
	}
	static function getVoucherVendorCommission():float{
		global $admin;
		global $user;
		if($admin->getPref(CONFIG_PLAF_ALLOW_FLEXIBLE_VENDORS_COMMISSION))
			return $user->getPref(CONFIG_VENDOR_VOUCHER_COMMISSION,$admin->getPref(CONFIG_PLAF_VENDORS_COMMISSION,0));
		else
			return $admin->getPref(CONFIG_PLAF_VENDORS_COMMISSION,0);
	}
	static function getVoucherCommission():float{
		return APIController::getVoucherAdminCommission()+APIController::getVoucherVendorCommission();
	}
	static function getPref(Model $user, string $pref, string $default=''):?string{
		return $user->getPref($pref,$default);
	}
	static function getWithdrawalFee(Model $currency):Quantity{
		global $admin;
		global $Currency;
		global $admin;
		$vminUnit = $admin->getPref(CONFIG_PAYOUT_FEE_UNIT,CURRENCY_NAIRA);
		$vminNumber = $admin->getPref(CONFIG_PAYOUT_FEE_NUMBER,50);
		if($currency->getID()==$vminUnit)
			return new Quantity($vminNumber,$vminUnit);
		else
			return new Quantity($vminNumber*APIController::getFX($Currency->getInstance($vminUnit),$currency),$currency->getID());
	}
	static function getVoucherMinimum(Model $currency):Quantity{
		global $admin;
		global $Currency;
		$vminUnit = pref($admin->getID(),CONFIG_VOUCHER_MINIMUM_UNIT)??CURRENCY_NAIRA;
		$vminNumber = pref($admin->getID(),CONFIG_VOUCHER_MINIMUM_NUMBER)??VOUCHER_MIN_NGN;
		if($currency->getID()==$vminUnit)
			return new Quantity($vminNumber,$vminUnit);
		else
			return new Quantity($vminNumber*APIController::getFX($Currency->getInstance($vminUnit),$currency),$currency->getID());
	}
	static function getNetworkFee(Model $currency):Quantity{
		global $admin;
		if(pref($admin->getID(),CONFIG_BLOCKCHAIN_LIVE_NETWORK_FEES)){
			$ch = curl_init("https://bitcoinfees.earn.com/api/v1/fees/recommended");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$json = curl_exec($ch);
			$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if($http_status !== 200){
				throw new Exception("Couldn't fetch live network fees", 1);
			}else{
				if(pref($admin->getID(),CONFIG_PRIORITY_NETWORK_FEE)){
					return APIController::convert(new Quantity(pref($admin->getID(),CONFIG_BLOCKCHAIN_BYTE_PER_TX,false,BYTES_PER_TX)*bcdiv(json_decode($json,true)['fastestFee'],SATOSHI,8),CURRENCY_BTC),$currency);
				}else{
					return APIController::convert(new Quantity(pref($admin->getID(),CONFIG_BLOCKCHAIN_BYTE_PER_TX,false,BYTES_PER_TX)*bcdiv(json_decode($json,true)['halfHourFee'],SATOSHI,8),CURRENCY_BTC),$currency);
				}
			}
		}else{
			return APIController::convert(new Quantity(pref($admin->getID(),CONFIG_VOUCHER_NETWORK_FEES_NUMBER)??NETWORK_FEE_NGN,pref($admin->getID(),CONFIG_VOUCHER_NETWORK_FEES_UNIT)??CURRENCY_NAIRA),$currency);
		}
		// {"fastestFee":82,"halfHourFee":80,"hourFee":56}
		// fastestFee: The lowest fee (in satoshis per byte) that will currently result in the fastest transaction confirmations (usually 0 to 1 block delay).
		// halfHourFee: The lowest fee (in satoshis per byte) that will confirm transactions within half an hour (with 90% probability).
		// hourFee: The lowest fee (in satoshis per byte) that will confirm transactions within an hour (with 90% probability).
	}
	static function convert(Quantity $qty,Model $currency,bool $sell=true):Quantity{
		global $Currency;
		if($qty->getUnit()==$currency->getID()){
			return new Quantity($qty->getNumber(),$currency->getID());
		}else{
			$base = $Currency->getInstance($qty->getUnit());
			$usd = $Currency->getInstance(CURRENCY_USD);
			$cryptos = Utility::linearize($Currency->get('crypto',1),'id');
			if(Utility::in_array($base->getID(),$cryptos)){
				if(Utility::in_array($currency->getID(),$cryptos)){
					return new Quantity($qty->getNumber()*self::getFX($base,$usd,$sell)/self::getFX($currency,$usd,$sell),$currency->getID());
				}else{
					return new Quantity($qty->getNumber()*self::getFX($base,$usd,$sell)*self::getFX($usd,$currency,$sell),$currency->getID());
				}
			}
		}
	}
	static function getVoucherValidity():int{
		global $admin;
		$pref = pref($admin->getID(),CONFIG_PLAF_VOUCHER_VALIDITY);
		return ($pref??VOUCHER_VALIDITY_DAYS);
	}
	static function btcSpotPrice(Model $quote):float{
		global $Currency;
		return APIController::getFX($Currency->getInstance(CURRENCY_BTC),$quote);
	}
	/**
	 * @param Model $base
	 * @param Model $quote
	 * @param bool $sell
	 * @return float
	 * @throws Exception
	 */
	static function getFX(Model $base,Model $quote,bool $sell=true):float{
		global $Pref;
		global $FX;
		global $admin;
		if($base->getID()==$quote->getID())
			return 1;

		$pref = $Pref->getGeneric("WHERE entity='".$admin->getID()."' AND config='".CONFIG_LIVE_FX_RATES."' ORDER BY created_at DESC LIMIT 1");
		if($pref && !$pref[0]['value'] && ($fx = $FX->getGeneric("WHERE base='".$base->getID()."' AND quote='".$quote->getID()."' ORDER BY created_at DESC LIMIT 1"))){
			return $fx[0][$sell?'sell':'buy'];
		}else{
			$json = Utility::curl("https://api.coinbase.com/v2/exchange-rates?currency=".$base->getProperty('symbol'));
			return $json['data']['rates'][strtoupper($quote->getProperty('symbol'))]??'';
		}
	}
}