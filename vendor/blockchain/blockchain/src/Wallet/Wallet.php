<?php

namespace Blockchain\Wallet;

use \Blockchain\Blockchain;
use \Blockchain\Exception\CredentialsError;
use \Blockchain\Exception\ParameterError;
use contract\Quantity;
use contract\Response;
use contract\Request;
use helper\Utility;
use model\Model;

class Wallet {
    private $identifier;
    private $main_password;
    private $second_password;
    private ?Blockchain $blockchain;

    public function __construct(Blockchain $blockchain,string $main_password=null,string $identifier=null,string $second_password=null) {
        $this->blockchain = $blockchain;
        $this->main_password = $main_password;
        $this->second_password = $second_password;
        $this->identifier = $identifier;
    }

    public function credentials($id, $pw1, $pw2=null) {
        $this->identifier = $id;
        $this->main_password = $pw1;
        if(!is_null($pw2)) {
            $this->second_password = $pw2;
        }
    }

    private function _checkCredentials() {
        if(is_null($this->identifier) || is_null($this->main_password)) {
            throw new CredentialsError('Please enter wallet credentials.');
        }
    }

    private function reqParams($extras=array()) {
        $ret = array('password'=>$this->main_password);
        if(!is_null($this->second_password)) {
            $ret['second_password'] = $this->second_password;
        }

        return array_merge($ret, $extras);
    }

    private function url($resource) {
		return "merchant/" . $this->identifier . "/" . $resource;
    }

    private function call($resource, $params=array()) {
        $this->_checkCredentials();
        return $this->blockchain->post($this->url($resource), $this->reqParams($params));
    }

    public function getIdentifier() {
        return $this->identifier;
    }



    /**
	 * @desc Creating a new Blockchain Wallet
	 * @desc Endpoint: /api/v2/create
	 * @param string password
	 * @param string api_code
	 * @param string priv - private key to import into wallet as first address (optional)
     * @param string label - label to give to the first address generated in the wallet (optional)
     * @param string email - email to associate with the newly created wallet (optional)
     * @return Response
     * @throws CredentialsError
     */
    public function newWallet(string $label='Address 0'):Response{
		$this->_checkCredentials();

		if($ret = $this->blockchain->post('/api/v2/create',array('label'=>$label))){
			$response = new Response(new Request([],'POST'));
			$response->setPayload($ret);
			return $response;
		}
		/*
			Sample Response:

			{
			"guid": "05f290be-dbef-4636-a809-868893c51711",
			"address": "13R9dBgKwBP29JKo11zhfi74YuBsMxJ4qY",
			"label": "Main address"
		} */
	}

    /**
	 * @desc Create New HD Account
	 * @desc Endpoint: /merchant/:guid/accounts/create
	 * @param string password
	 * @param string api_code
     * @param string $label
     * @return Response
     * @throws CredentialsError
     */
    public function newHDAccount(Model $user):Model{
		global $BTCWallet;
		global $BTCAddress;
		if($ret = $this->call('accounts/create', array('label'=>$user->getID().' Account '.uniqid()))){
			$indexResponse = $this->getHDAccount($ret['xpub']);
			$btcWallet = $BTCWallet->insertMap([
				'name'=> $ret['label'],
				'user'=> $user->getID(),
				'indexx'=> $indexResponse->getPayloadValue('index'),
				'provider'=> 'blockchain',
				'archived'=> $ret['archived'],
				'xpriv'=> $ret['xpriv'],
				'xpub'=> $ret['xpub'],
				'balance_number'=> 0.0,
				'balance_unit'=> CURRENCY_BTC,
				'receive_account'=> $ret['cache']['receiveAccount'],
				'change_account'=> $ret['cache']['changeAccount'],
			]);
			$BTCAddress->insertMap(['name'=>'Address 0','wallet'=>$btcWallet->getID(),'address'=>$indexResponse->getPayloadValue('receiveAddress'),'indexx'=>$indexResponse->getPayloadValue('receiveIndex')]);
			return $btcWallet;
		}
		/*
			2020-11-21 03:44:43.169286
			Array
			(
				[label] => 5f9393094831e New Account
				[archived] =>
				[xpriv] => xprv9zSqnwGbwpC2YK7RuBZ5EGsYQ7bKv7wKfz6JgyXwSou1pmJWm5jU6ATvPEjFnCaiNuwHiGpRhVn7MNEUESnUQeRzHddHtY3KiEdV3rRzG1g
				[xpub] => xpub6DSCCSoVnBkKkoBu1D65bQpGx9RpKafB3D1uVMwZ19RzhZdfJd3idxnQEXtKQxjeUXGqFkoNEv82ohXkFmhqgGEPguSbY6TESyurmtN7kXw
				[address_labels] => Array
					(
					)

				[cache] => Array
					(
						[receiveAccount] => xpub6EpGq4ydYkL2ZmoV5RuApUuo6p427sCYaRhLWDmX1RVRBQ6adtRqTmhonyg96peZ5xSB6mmhjh89nZQsy1Npx9ziSDc8BMinozpn4DYLzYv
						[changeAccount] => xpub6EpGq4ydYkL2cDVKfViZz34utPF6ttMzZnYozLv8oyAEMd2k14iYTcDNDXC3J36aopGQzHTcrg4Yfi6xFwBy6vdkn3xCAXXXG4RBEstDFsf
					)
			)
		*/
	}

    /**
	 * @desc Get Single HD Account
	 * @desc Endpoint: /merchant/:guid/accounts/:xpub_or_index
	 * @param string password
	 * @param string api_code
     * @return Response
     * @throws CredentialsError
     */
    public function getHDAccount(string $xpub_or_index):Response{
		if($ret = $this->call("accounts/$xpub_or_index")){
			$response = new Response(new Request([],'POST'));
			$response->setPayload($ret);
			$response->addPayload('balance',\Blockchain\Conversion\Conversion::BTC_int2str($ret['balance']));
			return $response;
		}
		/* 2020-11-21 03:44:43.169363
		Array
		(
			[balance] =>
			[label] => 5f9393094831e New Account
			[index] => 18
			[archived] =>
			[extendedPublicKey] => xpub6DSCCSoVnBkKkoBu1D65bQpGx9RpKafB3D1uVMwZ19RzhZdfJd3idxnQEXtKQxjeUXGqFkoNEv82ohXkFmhqgGEPguSbY6TESyurmtN7kXw
			[extendedPrivateKey] => xprv9zSqnwGbwpC2YK7RuBZ5EGsYQ7bKv7wKfz6JgyXwSou1pmJWm5jU6ATvPEjFnCaiNuwHiGpRhVn7MNEUESnUQeRzHddHtY3KiEdV3rRzG1g
			[receiveIndex] => 0
			[lastUsedReceiveIndex] =>
			[receiveAddress] => 12wK5ZxbSQfqREPhJDBZ6KacePpRTpiiw7
		) */
	}

    /**
	 * @desc List HD xPubs
	 * @desc Endpoint: /merchant/:guid/accounts/xpubs
	 * @param string password
	 * @param string api_code
     * @return Response
     * @throws CredentialsError
     */
    public function listHDXPubs():Response{
		if($ret = $this->call('accounts/xpubs')){
			$response = new Response(new Request([],'POST'));
			$response->setPayload($ret);
			return $response;
		}
	}
    /**
	 * @desc List Active HD Accounts
	 * @desc Endpoint: Endpoint: /merchant/:guid/accounts
	 * @param string password
	 * @param string api_code
     * @return Response
     * @throws CredentialsError
     */
    public function listHDAccounts():Response{
		if($ret = $this->call('accounts')){
			$resp = [];
			foreach($ret as $i=>$account){
				$resp[$i] = $account;
				$resp[$i]['balance'] = \Blockchain\Conversion\Conversion::BTC_int2str($account['balance']);
			}
			$response = new Response(new Request([],'POST'));
			$response->setPayload($resp);
			return $response;
		}
	}
    /**
	 * @desc Get HD Account Receiving Address
	 * @Endpoint: /merchant/:guid/accounts/:xpub_or_index/receiveAddress
	 * @param string password
	 * @param string api_code
     * @return Response
     * @throws CredentialsError
     */
    public function getReceivingAddress(string $xpub_or_index):Response{
		if($ret = $this->call("accounts/$xpub_or_index/receiveAddress")){
			$response = new Response(new Request([],'POST'));
			$response->setPayload($ret);
			return $response;
		}
	}

    /**
	 * @desc Check HD Account Balance
	 * @Endpoint: /merchant/:guid/accounts/:xpub_or_index/balance
	 * @param string password
	 * @param string api_code
     * @return Response
     * @throws CredentialsError
     */
    public function getHDAccountBalance(string $xpub_or_index):Quantity{
		if($ret = $this->call("accounts/$xpub_or_index/balance")){
			Utility::log($ret);
			return new Quantity(\Blockchain\Conversion\Conversion::BTC_int2str($ret['balance']),CURRENCY_BTC);
		}	
	}

    /**
	 * @desc Archive HD Account
	 * @Endpoint: /merchant/:guid/accounts/:xpub_or_index/archive
	 * @param string password
	 * @param string api_code
     * @return Response
     * @throws CredentialsError
     */
    public function archiveHDAccount(string $xpub_or_index):Response{
		if($ret = $this->call("accounts/$xpub_or_index/archive")){
			$response = new Response(new Request([],'POST'));
			$response->setPayload($ret);
			return $response;
		}
	}
    /**
	 * @desc Unarchive HD Account
	 * @Endpoint: /merchant/:guid/accounts/:xpub_or_index/unarchive
	 * @param string password
	 * @param string api_code
     * @return Response
     * @throws CredentialsError
     */
    public function unarchiveHDAccount(string $xpub_or_index):Response{
		if($ret = $this->call("accounts/$xpub_or_index/unarchive")){
			$response = new Response(new Request([],'POST'));
			$response->setPayload($ret);
			return $response;
		}
	}



	/* Make Payment
		Endpoint: /merchant/:guid/payment
		Query Parameters:
		to - bitcoin address to send to (required)
		amount - amount in satoshi to send (required)
		password - main wallet password (required)
		second_password - second wallet password (required, only if second password is enabled)
		api_code - blockchain.info wallet api code (optional)
		from - bitcoin address or account index to send from (optional)
		fee - specify transaction fee in satoshi
		fee_per_byte - specify transaction fee-per-byte in satoshi 
	*/
    /**
	 * Endpoint: /merchant/:guid/payment
     * @param mixed $to_address
     * @param mixed $amount
     * @param mixed|null $from_address
     * @param mixed|null $fee
     * @param mixed|null $fee_per_byte
     * @return Response
     * @throws ParameterError
     * @throws CredentialsError
     */
    public function send(string $to_address, float $amount, string $from_address, Model $user, float $fee=null, float $fee_per_byte=null):?Model{
		global $BTCTransaction;
		global $BTCWallet;
		if(!isset($amount))
            throw new ParameterError("Amount required.");
		
		Utility::log("In send to:$to_address, amount:$amount from:$from_address, fee:$fee, fee_per_byte=$fee_per_byte");
		
        $params = array(
            'to'=>$to_address,
            'amount'=>\Blockchain\Conversion\Conversion::BTC_float2int($amount)
		);
        if(!is_null($from_address))
            $params['from'] = $from_address;
        if(!is_null($fee))
            $params['fee'] = \Blockchain\Conversion\Conversion::BTC_float2int($fee);
        // if(!is_null($fee_per_byte))
            $params['fee_per_byte'] = $fee_per_byte;

		Utility::log($params);
			
		// return null;
		if(($ret = $this->call('payment', $params)) && ($ret['success']??false)){
			Utility::log($ret);
			$toAddress = $BTCWallet->get('xpub',$ret['from'][0]);
			$btcTransaction = $BTCTransaction->insertMap([
				'name'=>'BTC Outbound Transfer',
				'desc'=>'from:'.$ret['from'][0].' to:'.$ret['to'][0].' fee: '.$ret['fee'],
				'type'=>TX_TYPE_TRANSFER,
				'hash'=>$ret['txid'],
				'tto'=>$ret['to'][0],
				'address'=>$toAddress[0]['id']??substr($ret['from'][0],0,13),
				'user'=>$user->getID(),
				'value_number'=>\Blockchain\Conversion\Conversion::BTC_int2str($ret['amounts'][0]),
				'value_unit'=>CURRENCY_BTC,
				'fee_number'=>\Blockchain\Conversion\Conversion::BTC_int2str($ret['fee']),
				'fee_unit'=>CURRENCY_BTC,
				'cleared_number'=>\Blockchain\Conversion\Conversion::BTC_int2str($ret['amounts'][0]+$ret['fee']),
				'cleared_unit'=>CURRENCY_BTC,
			],true);
			$btcTransaction->setState(STATE_APPROVED,$user,'BTC Outbound Transaction');
			return $btcTransaction;
		}
		/*
			(
				[to] => Array
					(
						[0] => 1KMxB3wwijm8r2MSGoAyGZA7k6RcFqcYsg
					)

				[amounts] => Array
					(
						[0] => 1082
					)

				[from] => Array
					(
						[0] => xpub6DSCCSoVnBkJy7XdzHbkPpsz2GgTEprZfj3yuJaqurPDWNhbvL37qctHbXvYK14Qmfi81DvTfSdjpJpy5k1xqerT2H6sfF8oyUN7LH6tRAr
					)

				[fee] => 2260
				[txid] => 30cc4070a18193103f772fad37de02ad65d8c927988ec650693a51c5e31621d0
				[tx_hash] => 30cc4070a18193103f772fad37de02ad65d8c927988ec650693a51c5e31621d0
				[message] => Payment Sent
				[success] => 1
				[warning] => Setting a fee_per_byte value below 50 satoshi/byte is not recommended, and may lead to long confirmation times
			)		  
		  */
    }
    /**
     * @param mixed $recipients
     * @param mixed|null $from_address
     * @param mixed|null $fee
     * @return PaymentResponse
     * @throws CredentialsError
     */
    public function sendMany($recipients, string $from_address=null, float $fee=null, float $fee_per_byte=null,Model $user):array{
		global $BTCTransaction;
		global $BTCAddress;
        $R = array();
        // Construct JSON by hand, preserving the full value of amounts
        foreach ($recipients as $address => $amount) {
            $R[] = '"' . $address . '":' . \Blockchain\Conversion\Conversion::BTC_float2int($amount);
        }
        $json = '{' . implode(',', $R) . '}';

        $params = array(
            'recipients'=>$json
        );
        if(!is_null($from_address))
            $params['from'] = $from_address;
        if(!is_null($fee))
            $params['fee'] = \Blockchain\Conversion\Conversion::BTC_float2int($fee);
		if(!is_null($fee_per_byte))
		$params['fee_per_byte'] = \Blockchain\Conversion\Conversion::BTC_float2int($fee_per_byte);

		$rets = [];
		if($ret = $this->call('sendmany', $params)){
			for($i=0;$i<count($ret['to']);$i++){
				$fromAddress = $BTCAddress->get('address',$ret['from'][0]);
				$feeProp = bcdiv(\Blockchain\Conversion\Conversion::BTC_int2str($ret['fee']),count($ret['to']),8);
				$btcTransaction = $BTCTransaction->insertMap([
					'name'=>'BTC Outbound Transfer',
					'desc'=>'from:'.$ret['from'][0].' to:'.$ret['to'][$i].' fee: '.$ret['fee'].' feeProp:'.$feeProp,
					'type'=>TX_TYPE_PAYOUT,
					'hash'=>$ret['txid'],
					'tto'=>$ret['to'][$i],
					'address'=>$fromAddress[0]['id']??substr($ret['from'][0],0,13),
					'user'=>$user->getID(),
					'value_number'=>\Blockchain\Conversion\Conversion::BTC_int2str($ret['amounts'][$i]),
					'value_unit'=>CURRENCY_BTC,
					'fee_number'=>$feeProp,
					'fee_unit'=>CURRENCY_BTC,
					'cleared_number'=>bcsub(\Blockchain\Conversion\Conversion::BTC_int2str($ret['amounts'][$i]),$feeProp,8),
					'cleared_unit'=>CURRENCY_BTC,
				],true);
				$btcTransaction->setState(STATE_APPROVED,$user,'BTC Outbound Transaction');
				$rets[] = $btcTransaction;
			}
			return $rets;
		}
		/* {
			"to" : ["1A8JiWcwvpY7tAopUkSnGuEYHmzGYfZPiq", "18fyqiZzndTxdVo7g9ouRogB4uFj86JJiy"],
			"from": ["17p49XUC2fw4Fn53WjZqYAm4APKqhNPEkY"],
			"amounts": [16000, 5400030],
			"fee": 2000,
			"txid": "f322d01ad784e5deeb25464a5781c3b20971c1863679ca506e702e3e33c18e9c",
			"success": true
		  } */
    }



    public function enableHD():Response{
		if($ret = $this->call('enableHD')){
			$response = new Response(new Request([],'POST'));
			$response->setPayload($ret);
			return $response;
		}
	}
	
    public function getBalance():float{
		if($ret = $this->call('balance')){
			return \Blockchain\Conversion\Conversion::BTC_int2str($ret['balance']);
		}
	}
	/**
	 * @deprecated 
	 * */
    public function getAddressBalance($address):float {
		if($ret = $this->call('address_balance', array('address'=>$address))){
			return \Blockchain\Conversion\Conversion::BTC_int2str($ret['balance']);
		}
		return 0;
    }

    public function getAddresses() {
        $json = $this->call('list');
        $addresses = array();
        foreach ($json['addresses'] as $address) {
            $addresses[] = new WalletAddress($address);
        }
        return $addresses;
    }

    public function getNewAddress($label=null) {
        $params = array();
        if(!is_null($label)) {
            $params['label'] = $label;
        }
        return new WalletAddress($this->call('new_address', $params));
    }

    public function archiveAddress($address) {
        $json = $this->call('archive_address', array('address'=>$address));
        if(array_key_exists('archived', $json)) {
            if($json['archived'] == $address) {
                return true;
            }
        }
        return false;
    }

    public function unarchiveAddress($address) {
        $json = $this->call('unarchive_address', array('address'=>$address));
        if(array_key_exists('active', $json)) {
            if($json['active'] == $address) {
                return true;
            }
        }
        return false;
    }

}