<?php
namespace controller;

use contract\Controller;
use view\Page;
use helper\Utility;
use model\Model;
use contract\Quantity;
use Exception;

// https://api.paystack.co/bank
class AccountController extends Controller{
	static function getBalance(Model $party, Model $coparty, string $currency=CURRENCY_NAIRA, string $account=ACCOUNT_BALANCE):Quantity{
		global $Balance;
		$balance = $Balance->getGeneric("WHERE party='".$party->getID()."' AND coparty='".$coparty->getID()."' AND account='$account' AND value_unit='$currency' ORDER BY created_at DESC LIMIT 1");
		return new Quantity($balance[0]['value_number']??0,$currency);
	}
	static function shouldDoBalance(Model $party, Model $coparty, string $currency=CURRENCY_NAIRA, string $account=ACCOUNT_BALANCE):bool{
		global $Entry;
		$in_sum = $Entry->getAdvanced("SUM(value_number) as in_sum","WHERE party='".$party->getID()."' AND coparty='".$coparty->getID()."' AND type='".ACCOUNT_ENTRY_TYPE_IN."' AND account='$account' AND value_unit='$currency' AND consumed_at IS NULL");
		$out_sum = $Entry->getAdvanced("SUM(value_number) as in_sum","WHERE party='".$party->getID()."' AND coparty='".$coparty->getID()."' AND type='".ACCOUNT_ENTRY_TYPE_OUT."' AND account='$account' AND value_unit='$currency' AND consumed_at IS NULL");
		return ($in_sum[0]['in_sum']??false) || ($out_sum[0]['out_sum']??false);
	}
	static function doBalance(Model $party, Model $coparty, string $currency=CURRENCY_NAIRA, string $account=ACCOUNT_BALANCE):Quantity{
		global $Entry;
		global $Balance;

		$balanceQty = AccountController::getBalance($party,$coparty,$currency,$account);
		$in_sum = $Entry->getAdvanced("SUM(value_number) as in_sum","WHERE party='".$party->getID()."' AND coparty='".$coparty->getID()."' AND type='".ACCOUNT_ENTRY_TYPE_IN."' AND account='$account' AND value_unit='$currency' AND consumed_at IS NULL");
		$out_sum = $Entry->getAdvanced("SUM(value_number) as out_sum","WHERE party='".$party->getID()."' AND coparty='".$coparty->getID()."' AND type='".ACCOUNT_ENTRY_TYPE_OUT."' AND account='$account' AND value_unit='$currency' AND consumed_at IS NULL");

		if(($in_sum[0]['in_sum']??false) || ($out_sum[0]['out_sum']??false)){
			$balanceNum = $balanceQty->getNumber() + $in_sum[0]['in_sum'] - ($out_sum[0]['out_sum']);
			$balance = $Balance->insertMap([
				'name'=>'Balance Update',
				'description'=>'Balance Update',
				'party'=>$party->getID(),
				'coparty'=>$coparty->getID(),
				'account'=>$account,
				'value_number'=>$balanceNum,
				'value_unit'=>$currency,
			]);
			$Entry->updateGeneric("SET consumed_at=now() WHERE party='".$party->getID()."' AND coparty='".$coparty->getID()."' AND type IN ('".ACCOUNT_ENTRY_TYPE_IN."','".ACCOUNT_ENTRY_TYPE_OUT."') AND account='$account' AND value_unit='$currency' AND consumed_at IS NULL");
			$balanceQty = new Quantity($balance->getProperty('value_number'),$balance->getProperty('value_unit'));
		}
		return $balanceQty;
	}
}