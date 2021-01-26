<?php

namespace Blockchain\Explorer;

use \Blockchain\Blockchain;
use Blockchain\Exception\Error;
use \Blockchain\Exception\FormatError;
use Blockchain\Exception\HttpError;
use Exception;
use helper\Utility;

class Explorer {
	private Blockchain $blockchain;
    public function __construct(Blockchain $blockchain) {
        $this->blockchain = $blockchain;
    }

	/* ADDRESS LOOK UPS */	
    // getreceivedbyaddress/Address - Get the total number of bitcoins received by an address (in satoshi). Multiple addresses separated by | Do not use to process payments without the confirmations parameter
    // Add the parameters start_time and end_time to restrict received by to a specific time period. Provided times should be a unix timestamp in milliseconds. Multiple addresses separated by |
    // getsentbyaddress/Address - Get the total number of bitcoins send by an address (in satoshi). Multiple addresses separated by | Do not use to process payments without the confirmations parameter
    // addressbalance/Address - Get the balance of an address (in satoshi). Multiple addresses separated by | Do not use to process payments without the confirmations parameter
    // addressfirstseen/Address - Timestamp of the block an address was first confirmed in.

    public function getReceivedByAddress(array $addresses,int $confs = 3):float{
		$ret = $this->blockchain->get("q/getreceivedbyaddress/".implode('|',$addresses),['confirmations'=>$confs]);
		return \Blockchain\Conversion\Conversion::BTC_int2str($ret);
	}
    public function getSentByAddress(array $addresses,int $confs = 3):float{
		$ret = $this->blockchain->get("q/getsentbyaddress/".implode('|',$addresses),['confirmations'=>$confs]);
		return \Blockchain\Conversion\Conversion::BTC_int2str($ret);
	}

	/**
	 * @param array $addresses 
	 * @param int $confs 
	 * @return float 
	 * @throws HttpError 
	 * @throws Error 
	 */
	public function getAddressBalance(array $addresses,int $confs = 3):float{
		$ret = $this->blockchain->get("q/addressbalance/".implode('|',$addresses),['confirmations'=>$confs]);
		return \Blockchain\Conversion\Conversion::BTC_int2str($ret);
	}
    public function getAddressFirstSeen(string $address,int $confs = 3):?\DateTime{
		$ret = $this->blockchain->get("q/addressfirstseen/$address",['confirmations'=>$confs]);
		return new \DateTime('@' . $ret);
	}
	
	/* TRANSACTION LOOKUPS */
    // txtotalbtcoutput/TxHash - Get total output value of a transaction (in satoshi)
    // txtotalbtcinput/TxHash - Get total input value of a transaction (in satoshi)
    // txfee/TxHash - Get fee included in a transaction (in satoshi)
    // txresult/TxHash/Address - Calculate the result of a transaction sent or received to Address. Multiple addresses separated by |

    public function getTotalBTCOutput(string $txHash):float{
		$ret = $ret = $this->blockchain->get("q/txtotalbtcoutput/$txHash",[]);
		return \Blockchain\Conversion\Conversion::BTC_int2str($ret);
	}
    public function getTotalBTCInput(string $txHash):float{
		$ret = $this->blockchain->get("q/txtotalbtcinput/$txHash",[]);
		return \Blockchain\Conversion\Conversion::BTC_int2str($ret);
	}
    public function getTransactionFee(string $txHash):float{
		$ret = $this->blockchain->get("q/txfee/$txHash",[]);
		return \Blockchain\Conversion\Conversion::BTC_int2str($ret);
	}
    public function getTransactionResult(string $txHash, array $addresses):float{
		$ret = $this->blockchain->get("q/txresult/$txHash/".implode('|',$addresses),[]);
		return \Blockchain\Conversion\Conversion::BTC_int2str($ret);
	}
	
	/* TOOLS */
	
	/**
	 * @desc addresstohash/Address - Converts a bitcoin address to a hash 160
	 */
    public function addressToHash(string $address):string{
		$ret = $this->blockchain->get("q/addresstohash/$address/",[]);
		return $ret;
	}
	
	/**
	 * @desc hashtoaddress/Hash - Converts a hash 160 to a bitcoin address
	 */
    public function hashToAddress(string $hash):string{
		$ret = $this->blockchain->get("q/hashtoaddress/$hash/",[]);
		return $ret;
	}
	
	/**
	 * @desc hashpubkey/Pubkey - Converts a public key to a hash 160
	 */
    public function pkToHash(string $publicKey):string{
		$ret = $this->blockchain->get("q/hashpubkey/$publicKey/",[]);
		return $ret;
	}
	
	/**
	 * @desc addrpubkey/Pubkey - Converts a public key to an Address
	 */	
    public function pkToAddress(string $publicKey):string{
		$ret = $this->blockchain->get("q/addrpubkey/$publicKey/",[]);
		return $ret;
	}
	
	/**
	 * @desc pubkeyaddr/Address - Converts an address to public key (if available)
	 */	
    public function addressToPK(string $address):string{
		$ret = $this->blockchain->get("q/pubkeyaddr/$address/",[]);
		return $ret;
	}
	
	
	// MISC
    /**
	 * @desc rejected - Lookup the reason why the provided tx or block hash was rejected (if any)
     * @param string $hash 
     * @return string 
     * @throws HttpError 
     * @throws Error 
     */
    public function rejectedReason(string $hash):int{
		$ret = $this->blockchain->get("q/rejected/$hash");
		return $ret;
	}
	
    /**
	 * @desc unconfirmedcount - Number of pending unconfirmed transactions
     * @param string $address 
     * @return string 
     * @throws HttpError 
     * @throws Error 
     */
    public function unconfirmedTXCount():int{
		$ret = $this->blockchain->get("q/unconfirmedcount");
		return $ret;
	}
	
    /**
	 * @desc hashrate - Estimated network hash rate in gigahash
     * @param string $address 
     * @return string 
     * @throws HttpError 
     * @throws Error 
     */
    public function getHashRate():float{
		$ret = $this->blockchain->get("q/hashrate");
		return $ret;
	}
	
    /**
	 * @desc 24hrbtcsent - Number of btc sent in the last 24 hours (in satoshi)
     * @param string $address 
     * @return string 
     * @throws HttpError 
     * @throws Error 
     */
    public function get24HRBTCSent():int{
		$ret = $this->blockchain->get("q/24hrbtcsent");
		return \Blockchain\Conversion\Conversion::BTC_int2str($ret);
	}
	
    /**
	 * @desc 24hrtransactioncount - Number of transactions in the past 24 hours
     * @param string $address 
     * @return string 
     * @throws HttpError 
     * @throws Error 
     */
    public function get24HRTXCount():int{
		$ret = $this->blockchain->get("q/24hrtransactioncount");
		return $ret;
	}
	
    /**
	 * @desc 24hrprice - 24 hour weighted price from the largest exchanges, in USD
     * @param string $address 
     * @return string 
     * @throws HttpError 
     * @throws Error 
     */
    public function get24HRPrice():float{
		$ret = $this->blockchain->get("q/24hrprice");
		return $ret;
	}
	
    /**
	 * @desc marketcap - USD market cap (based on 24 hour weighted price)
     * @param string $address 
     * @return string 
     * @throws HttpError 
     * @throws Error 
     */
    public function getMarketCap():float{
		$ret = $this->blockchain->get("q/marketcap");
		return $ret;
	}

	
	
	/* LEGACY */
	
    public function getBlock($hash) {
        return new Block($this->blockchain->get('rawblock/' . $hash, array('format'=>'json')));
    }

    public function getBlocksAtHeight($height) {
        if(!is_integer($height)) {
            throw new FormatError('Block height must be iteger.');
        }
        $blocks = array();
        $json = $this->blockchain->get('block-height/' . $height, array('format'=>'json'));
        if(array_key_exists('blocks', $json)) {
            foreach ($json['blocks'] as $block) {
                $blocks[] = new Block($block);
            }
        }

        return $blocks;
    }

    public function getBlockByIndex($index) {
        trigger_error("getBlockByIndex is deprecated. Please use getBlock (by hash) whenever possible.", E_USER_DEPRECATED);
        if(!is_integer($index)) {
            throw new FormatError('Block index must be iteger.');
        }
        return new Block($this->blockchain->get('block-index/' . $index, array('format'=>'json')));
    }

    public function getTransaction($hash) {
        return new Transaction($this->blockchain->get('rawtx/' . $hash, array('format'=>'json')));
    }

    public function getTransactionByIndex($index) {
        trigger_error("getTransactionByIndex is deprecated. Please use getTransaction (by hash) whenever possible.", E_USER_DEPRECATED);
        return new Transaction($this->blockchain->get('rawtx/' . intval($index), array('format'=>'json')));
    }

    public function getBase58Address($address, $limit=50, $offset=0, $filter=FilterType::RemoveUnspendable) {
        return $this->getAddress($address, $limit, $offset, $filter);
    }

    public function getHash160Address($address, $limit=50, $offset=0, $filter=FilterType::RemoveUnspendable) {
        return $this->getAddress($address, $limit, $offset, $filter);
    }

	/*     
		Get details about a single address, listing up to $limit transactions starting at $offset.
    */
    public function getAddress($address, $limit=50, $offset=0, $filter=FilterType::RemoveUnspendable) {
        $params = array(
            'format'=>'json',
            'limit'=>intval($limit),
            'offset'=>intval($offset),
            'filter'=>intval($filter)
        );
        return new Address($this->blockchain->get('address/' . $address, $params));
    }

    public function getXpub($xpub, $limit=100, $offset=0, $filter=FilterType::RemoveUnspendable) {
        $params = array(
            'format'=>'json',
            'limit'=>intval($limit),
            'offset'=>intval($offset),
            'filter'=>intval($filter),
            'active'=>$xpub
        );
        $resp = $this->blockchain->get('multiaddr?', $params);
        if(array_key_exists('addresses', $resp)) {
            $xpub = new Xpub($resp['addresses'][0]);
            if(array_key_exists('txs', $resp)) {
                foreach ($resp['txs'] as $txn) {
                    $xpub->transactions[] = new Transaction($txn);
                }
            }
        }
        return $xpub;

    }

    public function getMultiAddress($addresses, $limit=100, $offset=0, $filter=FilterType::RemoveUnspendable) {
        if(!is_array($addresses))
            throw new FormatError('Must pass array argument.');

        $params = array(
            'format'=>'json',
            'limit'=>intval($limit),
            'offset'=>intval($offset),
            'filter'=>intval($filter),
            'active'=>implode('|', $addresses)
        );
        return new MultiAddress($this->blockchain->get('multiaddr?', $params));
    }

    /* Get a list of unspent outputs for an array of addresses

    */
    public function getUnspentOutputs($addresses, $confirmations=0, $limit=250) {
        if(!is_array($addresses))
            throw new FormatError('Must pass array argument.');

        $params = array(
            'format'=>'json',
            'limit'=>intval($limit),
            'confirmations'=>intval($confirmations),
            'active'=>implode('|', $addresses)
        );
        $json = $this->blockchain->get('unspent', $params);
        $outputs = Array();
        if(array_key_exists('unspent_outputs', $json)) {
            foreach ($json['unspent_outputs'] as $output) {
                $outputs[] = new UnspentOutput($output);
            }
        }
        return $outputs;
    }

    public function getLatestBlock() {
        return new LatestBlock($this->blockchain->get('latestblock', array('format'=>'json')));
    }

    public function getUnconfirmedTransactions() {
        $json = $this->blockchain->get('unconfirmed-transactions', array('format'=>'json'));
        $txn = array();
        if(array_key_exists('txs', $json)) {
            foreach ($json['txs'] as $tx) {
                $txn[] = new Transaction($tx);
            }
        }
        return $txn;
    }

    /* Get blocks for a specific day, provided UNIX timestamp, in seconds.
    */
    public function getBlocksForDay($unix_time=0) {
        $time_ms = strval($unix_time) . '000';
        return $this->processSimpleBlockJSON($this->blockchain->get('blocks/'.$time_ms, array('format'=>'json')));
    }

    /* Get blocks for a specific mining pool.
    */
    public function getBlocksByPool($pool) {
        return $this->processSimpleBlockJSON($this->blockchain->get('blocks/'.$pool, array('format'=>'json')));
    }

    private function processSimpleBlockJSON($json) {
        $blocks = array();
        if(array_key_exists('blocks', $json)) {
            foreach ($json['blocks'] as $block) {
                $blocks[] = new SimpleBlock($block);
            }
        }
        return $blocks;
    }
}