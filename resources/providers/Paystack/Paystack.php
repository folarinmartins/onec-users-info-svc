<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Short File Description
 *
 * PHP version 7.+
 *
 * @category   aCategory
 * @package    aPackage
 * @subpackage aSubPackage
 * @author     anAuthor
 * @copyright  2014 a Copyright
 * @license    a License
 * @link       http://www.aLink.com
 */

namespace Paystack;

use Error;

use \Paystack\Payment\Payment;

// Check if BCMath module installed
if(!function_exists('bcscale')) {
    throw new Error("BC Math module not installed.");
}

// Check if curl module installed
if(!function_exists('curl_init')) {
    throw new Error("cURL module not installed.");
}

class Paystack {
    // const URL = 'https://blockchain.info/';
    const URL = 'https://api.paystack.co/';

    private $ch;
	// private $api_code;
	public Payment $Payment;
	


    const DEBUG = true;
    public $log = Array();

    public function __construct($api_code) {
        // $this->service_url = null;

        // if(!is_null($api_code)) {
        // $this->api_code = $api_code;
        // }

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Paystack-PHP/1.0');
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $api_code","Cache-Control: no-cache",]);
		

        $this->Payment    = new Payment($this);
        // $this->Explorer  = new Explorer($this);
        // $this->Push      = new Push($this);
        // $this->Rates     = new Rates($this);
        // $this->ReceiveV2 = new ReceiveV2($this->ch);
        // $this->Stats     = new Stats($this);
        // $this->Wallet    = new Wallet($this);
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    public function setTimeout($timeout) {
        curl_setopt($this->ch, CURLOPT_TIMEOUT, intval($timeout));
    }

    public function post($resource, $data=null) {
        $url = Paystack::URL;

        curl_setopt($this->ch, CURLOPT_URL, $url.$resource);
        curl_setopt($this->ch, CURLOPT_POST, true);

        curl_setopt($this->ch, CURLOPT_HTTPHEADER,array("Content-Type: application/x-www-form-urlencoded"));

        // if(!is_null($this->api_code)) {
        //     $data['api_code'] = $this->api_code;
        // }

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $json = $this->_call();

        // throw ApiError if we get an 'error' field in the JSON
        if(array_key_exists('error', $json)) {
            throw new Error($json['error']);
        }
        return $json;
    }

    public function get($resource, $params=array()) {
        $url = Paystack::URL;
        curl_setopt($this->ch, CURLOPT_POST, false);
        $query = http_build_query($params);
        curl_setopt($this->ch, CURLOPT_URL, $url.$resource.'?'.$query);

        return $this->_call();
    }

    private function _call() {
        $t0 = microtime(true);
        $response = curl_exec($this->ch);
        $dt = microtime(true) - $t0;

        if(curl_error($this->ch)) {
            $info = curl_getinfo($this->ch);
            throw new Error("Call to " . $info['url'] . " failed: " . curl_error($this->ch));
        }
        $json = json_decode($response, true);
        if(is_null($json)) {
            // this is possibly a from btc request with a comma separation
            $json = json_decode(str_replace(',', '', $response));
            if (is_null($json))
                throw new Error("Unable to decode JSON response from Paystack: " . $response);
        }

        if(self::DEBUG) {
            $info = curl_getinfo($this->ch);
            $this->log[] = array(
                'curl_info' => $info,
                'elapsed_ms' => round(1000*$dt)
            );
        }

        return $json;
    }
}