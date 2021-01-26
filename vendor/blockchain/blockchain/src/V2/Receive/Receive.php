<?php

namespace Blockchain\V2\Receive;

use Blockchain\Blockchain;
use Blockchain\Exception\Error;
use Blockchain\Exception\HttpError;

use DateTime;
use DateTimeZone;
use contract\Response;
use contract\Request;
use helper\Utility;
use model\Model;

/**
 * The V2 Receive API client.
 * @author George Robinson <george.robinson@blockchain.com>
 */
class Receive{
    /**
     * @var string
     */
    const URL = 'https://api.blockchain.info/v2/receive';
	// Endpoint: /merchant/:guid/accounts/:xpub_or_index/receiveAddress
    /**
     * @var resource
     */
    private $ch;
    private $api;

    /**
     * Instantiates a receive API client.
     *
     * @param resource $ch The cURL resource.
     */
    public function __construct($ch,string $api){
        $this->ch = $ch;
        $this->api = $api;
    }
    /**
     * Generates a receive adddress.
     *
     * @param string $key The API key.
     * @param string $xpub The public key.
     * @param string $callback The callback URL.
     * @param int    $gap_limit How many unused addresses are allowed.
     * @return Response
     * @throws \Blockchain\Exception\Error
     * @throws \Blockchain\Exception\HttpError
     */
    public function generate(Model $btcWallet, $callback, $gap_limit = null):Model{
		global $BTCAddress;
		
		$key = $this->api;
		$xpub = $btcWallet->getProperty('xpub');
		
        $p = compact('key', 'xpub', 'callback');
        if(!is_null($gap_limit))
            $p['gap_limit'] = $gap_limit;
        $q = http_build_query($p);

        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_URL, static::URL.'?'.$q);

        if (($resp = curl_exec($this->ch)) === false) {
            throw new HttpError(curl_error($this->ch));
        }

        if (($data = json_decode($resp, true)) === NULL) {
            throw new Error("Unable to decode JSON response from Blockchain: $resp");
        }

        $info = curl_getinfo($this->ch);

        if($info['http_code'] == 200){			
			$btcAddress = $BTCAddress->insertMap([
					'name'=>'New Public Address',
					'wallet'=>$btcWallet->getID(),
					'address'=>$data['address'],
					'indexx'=>$data['index']
				]
			);			
			return $btcAddress;
            // return new ReceiveResponse($data['address'], $data['index'], $data['callback']);
        }
        throw new Error(implode(', ', $data));
	}
	// "message" : "Internal handlers error"
    public function balanceUpdate($address,$xpub,$callback,$onNotification='KEEP',$confs=3, $op = 'ALL'){
		$key = $this->api;

		$p = compact('key', 'xpub', 'callback','address','onNotification','confs','op');
		$p2 = json_encode($p);
        // $q = http_build_query($p);
		// https://api.blockchain.info/v2/receive/balance_update
        curl_setopt($this->ch, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_URL, 'https://api.blockchain.info/v2/receive/balance_update');
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: text/plain',
			'Content-Length: ' . strlen($p2),)
		);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS,http_build_query($p));


        if (($resp = curl_exec($this->ch)) === false) {
            throw new HttpError(curl_error($this->ch));
        }

		Utility::log('resp...');
		Utility::log($resp);
		// Utility::log(static::URL.'/balance_update?'.$q);
        if (($data = json_decode($resp, true)) === NULL) {
            throw new Error("Unable to decode balanceUpdate JSON response from Blockchain: $resp");
        }

		$info = curl_getinfo($this->ch);
		Utility::log('info...');
		Utility::log($info);

        if ($info['http_code'] == 200) {
			$response = new Response(new Request($p,'GET'));
			$response->setPayload($data);
			return $response;
			/* {
				"id" : 70,
				"addr" : "183qrMGHzMstARRh2rVoRepAd919sGgMHb",
				"op" : "RECEIVE",
				"confs" : 5,
				"callback" : "https://mystore.com?invoice_id=123",
				"onNotification" : "KEEP"
			} */
        }
        throw new Error(implode(', ', $data));
		/*
			https://api.blockchain.info/v2/receive/balance_update

			address - The address you would like to monitor
			callback - The callback URL to be notified when a payment is received.
			key - Your blockchain.info receive payments v2 api key. Request an API key.
			onNotification - The request notification behaviour ('KEEP' | 'DELETE).
			confs - Optional (Default 3). The number of confirmations the transaction needs to have before a notification is sent.
			op - Optional (Default 'ALL'). The operation type you would like to receive notifications for ('SPEND' | 'RECEIVE' | 'ALL').

			Monitor an address for every received payment with 5 confirmations:

			curl -H "Content-Type: text/plain" --data '{"key":"[your-key-here]","addr":"183qrMGHzMstARRh2rVoRepAd919sGgMHb","callback":"https://mystore.com?invoice_id=123","onNotification":"KEEP", "op":"RECEIVE", "confs": 5}' https://api.blockchain.info/v2/receive/balance_update

			Response: 200 OK, application/json

			{
			"id" : 70,
			"addr" : "183qrMGHzMstARRh2rVoRepAd919sGgMHb",
			"op" : "RECEIVE",
			"confs" : 5,
			"callback" : "https://mystore.com?invoice_id=123",
			"onNotification" : "KEEP"
			}

			Please note, the callback url is limited to 255 characters in length.

			When a payment is received by a generated address, or by an address monitored by a balance update request, blockchain.info will notify the callback URL you specify. For balance update callbacks and additional notification will be sent once the transaction reaches the specified number of confirmations.

			transaction_hash - The payment transaction hash.
			address - The destination bitcoin address (part of your xPub account).
			confirmations - The number of confirmations of this transaction.
			value - The value of the payment received (in satoshi, so divide by 100,000,000 to get the value in BTC).
			{custom parameter} - Any parameters included in the callback URL will be passed back to the callback URL in the notification. You can use this functionality to include parameters in your callback URL like invoice_id or customer_id to track which payments are associated with which of your customers.
 		*/
    }
    public function deleteBalanceUpdate($id,$key):Response{
		$key = $this->api;
		// curl -X DELETE "https://api.blockchain.info/v2/receive/balance_update/70?key=[your-key-here]"
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($this->ch, CURLOPT_URL, static::URL."/balance_update/$id?key=$key");

        if (($resp = curl_exec($this->ch)) === false) {
            throw new HttpError(curl_error($this->ch));
        }

        if (($data = json_decode($resp, true)) === NULL) {
            throw new Error("Unable to decode JSON response from Blockchain: $resp");
        }

        $info = curl_getinfo($this->ch);

        if ($info['http_code'] == 200) {
			$response = new Response(new Request(['id'=>$id],'DELETE'));
			$response->setPayload($data);
			return $response;
			/* {
				 "deleted": true }
			 */
        }
		throw new Error(implode(', ', $data));
		/*
			The id in the response can be used to delete the request:

			curl -X DELETE "https://api.blockchain.info/v2/receive/balance_update/70?key=[your-key-here]"

			Response: 200 OK, application/json

			{ "deleted": true }
		*/
	}
    /**
    * Get the index gap bewteen the last address
    * paid to and the last address generated
    *
    * @param string $key The API key.
    * @param string $xpub The public key.
    * @return int The address gap.
    * @throws \Blockchain\Exception\Error
    * @throws \Blockchain\Exception\HttpError
    */
    public function checkAddressGap($xpub):Response{
		$key = $this->api;
		// curl "https://api.blockchain.info/v2/receive/checkgap?xpub=[yourxpubhere]]&key=[yourkeyhere]"
        $p = compact('key', 'xpub');
        $q = http_build_query($p);

        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_URL, static::URL.'/checkgap?'.$q);

        if (($resp = curl_exec($this->ch)) === false) {
            throw new HttpError(curl_error($this->ch));
        }

        if (($data = json_decode($resp, true)) === NULL) {
            throw new Error("Unable to decode JSON response from Blockchain: $resp");
        }

        $info = curl_getinfo($this->ch);

        if ($info['http_code'] == 200) {
			$response = new Response(new Request($p,'GET'));
			$response->setPayload($data);
			return $response;
            // return $data['gap'];
        }
        throw new Error(implode(', ', $data));
    }
    /**
     * Gets the callback logs.
     *
     * @param string $key The API key.
     * @param string $callback The callback URL.
     * @return \Blockchain\V2\Receive\CallbackLogEntry[]
     * @throws \Blochchain\Exception\Error
     * @throws \Blockchain\Exception\HttpError
     */
    public function callbackLogs($callback){
		$key = $this->api;
        $p = compact('key', 'callback');
        $q = http_build_query($p);

        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_URL, static::URL.'/callback_log?'.$q);

        if (($resp = curl_exec($this->ch)) === false) {
            throw new HttpError(curl_error($this->ch));
        }

        if (($data = json_decode($resp, true)) === NULL) {
            throw new Error("Unable to decode JSON response from Blockchain: $resp");
        }

        $info = curl_getinfo($this->ch);

        if ($info['http_code'] == 200) {
            return array_map([$this, 'createCallbackLogEntry'], (array) $data);
        }

        throw new Error(implode(', ', $data));
    }
    /**
     * Creates a callback log entry.
     *
     * @param string[mixed] $data
     * @return \Blockchain\V2\Receive\CallbackLogEntry
     */
    private function createCallbackLogEntry($data){
        return new CallbackLogEntry($data['callback'], new DateTime($data['called_at']), $data['raw_response'], $data['response_code']);
    }
}
