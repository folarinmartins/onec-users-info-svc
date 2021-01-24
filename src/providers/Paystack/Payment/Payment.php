<?php

namespace Paystack\Payment;

use \Paystack\Paystack;
use \Paystack\Exception\CredentialsError;
use \Paystack\Exception\ParameterError;

use contract\Response;
use contract\Request;
use helper\Utility;
use model\Model;

class Payment {
    private $identifier = null;
    private $main_password = null;
    private $second_password = null;
    private ?Paystack $Paystack = null;

    public function __construct(Paystack $Paystack) {
        $this->Paystack = $Paystack;
    }

    public function credentials($id, $pw1, $pw2=null) {
        $this->identifier = $id;
        $this->main_password = $pw1;
        if(!is_null($pw2)) {
            $this->second_password = $pw2;
        }
    }

    private function _checkCredentials() {
		return true;
    }
    private function reqParams($extras=array()) {
        $ret = array('password'=>$this->main_password);
        if(!is_null($this->second_password)) {
            $ret['second_password'] = $this->second_password;
        }

        return array_merge($ret, $extras);
    }
    private function url($resource) {
		return "transaction/" . $resource;
    }
    private function get($resource, $params=array()) {
        $this->_checkCredentials();
        return $this->Paystack->get($this->url($resource), $this->reqParams($params));
    }
    private function call($resource, $params=array()) {
        $this->_checkCredentials();
        return $this->Paystack->post($this->url($resource), $this->reqParams($params));
    }
    public function getIdentifier() {
        return $this->identifier;
    }



    /**
	 * @desc Create New HD Account
	 * @desc Endpoint: https://api.paystack.co/transaction/verify/:reference
	 * @param string reference
     * @return Model
     * @throws CredentialsError
     */
    public function verify(string $reference):?Model{
		global $BTCTransaction;
		global $Currency;
		if($ret = $this->get('verify/'.$reference)){
			if($ret['data']['status']=='success'){
				$btcTransaction = $BTCTransaction->getInstance(substr($ret['data']['reference'],0,13));
				if($btcTransaction->getID()){
					$currencies = $Currency->get('symbol',$ret['data']['currency']);
					$currency = $currencies[0]['id']??$ret['data']['currency'];
					$btcTransaction->updateMap([
						'hash'=> $ret['data']['reference'],
						'fee_number'=> bcdiv($ret['data']['fees'],100,2),
						'fee_unit'=> $currency,
						'cleared_number'=> bcdiv($ret['data']['amount']-$ret['data']['fees'],100,2),
						'cleared_unit'=> $currency,
					]);
					return $btcTransaction;
				}
			}
		}
		return null;
		/*
		2020-11-21 18:22:44.337784
		Array
		(
			[status] => 1
			[message] => Verification successful
			[data] => Array
				(
					[id] => 893126485
					[domain] => test
					[status] => success
					[reference] => 5fb94cd84820f423816505
					[amount] => 330000
					[message] =>
					[gateway_response] => Successful
					[paid_at] => 2020-11-21T17:22:39.000Z
					[created_at] => 2020-11-21T17:22:33.000Z
					[channel] => card
					[currency] => NGN
					[ip_address] => 129.205.113.248
					[metadata] => Array
						(
							[referrer] => http://localhost/app.blockstale.com/login
						)

					[log] => Array
						(
							[start_time] => 1605979354
							[time_spent] => 6
							[attempts] => 1
							[errors] => 0
							[success] => 1
							[mobile] =>
							[input] => Array
								(
								)

							[history] => Array
								(
									[0] => Array
										(
											[type] => action
											[message] => Attempted to pay with card
											[time] => 4
										)

									[1] => Array
										(
											[type] => success
											[message] => Successfully paid with card
											[time] => 6
										)

								)

						)

					[fees] => 14950
					[fees_split] =>
					[authorization] => Array
						(
							[authorization_code] => AUTH_e71v3hf52v
							[bin] => 408408
							[last4] => 4081
							[exp_month] => 12
							[exp_year] => 2020
							[channel] => card
							[card_type] => visa
							[bank] => TEST BANK
							[country_code] => NG
							[brand] => visa
							[reusable] => 1
							[signature] => SIG_XOsj8EET4apmWlG7fd2a
							[account_name] =>
							[receiver_bank_account_number] =>
							[receiver_bank] =>
						)

					[customer] => Array
						(
							[id] => 34128691
							[first_name] =>
							[last_name] =>
							[email] => folarinjmartins@gmail.com
							[customer_code] => CUS_ukt3axtop971sbc
							[phone] =>
							[metadata] =>
							[risk_action] => default
							[international_format_phone] =>
						)

					[plan] =>
					[split] => Array
						(
						)

					[order_id] =>
					[paidAt] => 2020-11-21T17:22:39.000Z
					[createdAt] => 2020-11-21T17:22:33.000Z
					[requested_amount] => 330000
					[pos_transaction_data] =>
					[transaction_date] => 2020-11-21T17:22:33.000Z
					[plan_object] => Array
						(
						)

					[subaccount] => Array
						(
						)

				)

			)
		*/
	}
}