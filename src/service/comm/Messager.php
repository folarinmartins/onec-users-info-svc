<?php
namespace comm;

use helper\Utility;
use AfricasTalking\SDK\AfricasTalking;

class Messager{
	public static function send(string $text, array $to, string $from=null,array $cc=[]):bool{
		$username = Utility::getCredential('at','alphanumeric');; // use 'sandbox' for development in the test environment
		$apiKey   = Utility::getCredential('at','api'); // use your sandbox app API key for development in the test environment
		$AT       = new AfricasTalking($username, $apiKey);
		// Get one of the services
		$sms      = $AT->sms();
		$tto = array_map(function($user){
				return '+'.$user->getProperty('phone');
			},$to);
		$result = $sms->send([
			// 'from'      => $username,
			'to'      => implode(',',$tto),
			'message' => $text
		]);
		return true;
		/* (
			[status] => success
			[data] => stdClass Object
				(
					[SMSMessageData] => stdClass Object
						(
							[Message] => Sent to 1/1 Total Cost: NGN 3.2000
							[Recipients] => Array
								(
									[0] => stdClass Object
										(
											[cost] => NGN 3.2000
											[messageId] => ATXid_7c184d41b295f1f17345536387f704df
											[messageParts] => 1
											[number] => +2348066288220
											[status] => Success
											[statusCode] => 101
										)
								)
						)
				)
		) */
	}
}
 ?>
