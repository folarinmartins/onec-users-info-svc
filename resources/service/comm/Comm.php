<?php
namespace comm;

use controller\AccountController;
use model\Model;
use helper\Utility;
use view\Email;
Class Comm{
	public static function sendNotification(Model $model, string $notificationType, array $media=[MEDIUM_EMAIL], $refProxy=null, $refPX=null):bool{
		global $User;
		global $Notification;
		global $Account;
		global $Entry;
		global $Currency;
		global $File;
		global $BTCTransaction;
		global $admin;
		
		$variables = [];
		$notification = null;
		switch($notificationType){
			case NOTIFICATION_2FA_RESET:{
				if($model->instanceof($User)){
					$text['email.title'] = 'Verify 2FA Reset';
					$text['title'] = 'Verify 2FA Reset';
					$text['title.sub'] = 'Email verification needed to reset 2FA';
					$text['user.name'] = $model->getProperty('name');
					$text['email'] = $model->getProperty('email');
					$text['body'] = 'You requested a 2FA reset on your account. Please click the button below to verify your request.';
					$url['link.url'] = Link::getURL('reset-2fa',[$model->getProperty('2fa_reset_code')]);
					$url['link.text'] = 'Verify 2FA Reset';
					$text['text'] = $text['body'].' '.$url['link.url'];

					$notification = $Notification->insertMap([
							'type'=>$notificationType,
							'name'=>$text['email.title'],
							'medium'=>$media[0],
							'user'=>$model->getID(),
							'text'=>$text['email.title'].' '.$text['body'],
							'html'=>'',
						]
					);
					$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
					$variables['url'] = $url;
					$variables['text'] = $text;
					$email = Email::getInstance($variables,'account.password.reset');
					$html = $email->render();
					$notification->updateMap(['html'=>$html]);
				}
			}break;
			case NOTIFICATION_PASSWORD_RESET:{
				if($model->instanceof($User)){
					$text['email.title'] = 'Account Password Reset';
					$text['title'] = 'Forgot Your Password?';
					$text['title.sub'] = 'A password reset was activated by you';
					$text['user.name'] = $model->getProperty('name');
					$text['email'] = $model->getProperty('email');
					$text['body'] = Utility::getNote('password.reset');
					$url['link.url'] = Link::getURL('reset',[$model->getProperty('reset_code')]);;
					$url['link.text'] = 'Reset My Password';
					$text['text'] = $text['body'].' '.$url['link.url'];

					$notification = $Notification->insertMap([
							'type'=>$notificationType,
							'name'=>$text['email.title'],
							'medium'=>$media[0],
							'user'=>$model->getID(),
							'text'=>$text['email.title'].' '.$text['body'],
							'html'=>'',
						]
					);
					$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
					$variables['url'] = $url;
					$variables['text'] = $text;
					$email = Email::getInstance($variables,'account.password.reset');
					$html = $email->render();
					$notification->updateMap(['html'=>$html]);
				}
			}break;
			case NOTIFICATION_EMAIL_VERIFICATION:{
				if($model->instanceof($User)){
					$text['email.title'] = 'Account Verification';
					$text['title'] = 'Verify E-mail Address';
					$text['title.sub'] = 'Email verification needed to activate your new account';
					$text['user.name'] = $model->getProperty('name');
					$text['code.name'] = 'Email Address';
					$text['email'] = $model->getProperty('email');
					$text['body'] = Utility::getNote('account.activation');
					$url['link.url'] = Link::getURL('verify-email',[$model->getProperty('email_verification_code')]);
					$url['link.text'] = 'Verify My E-mail Address';
					$text['text'] = $text['body'].' '.$url['link.url'];

					$notification = $Notification->insertMap([
							'type'=>$notificationType,
							'name'=>$text['email.title'],
							'medium'=>$media[0],
							'user'=>$model->getID(),
							'text'=>$text['email.title'].' '.$text['body'],
							'html'=>'',
						]
					);
					$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
					$variables['url'] = $url;
					$variables['text'] = $text;
					$email = Email::getInstance($variables,'account.verification');
					$html = $email->render();
					$notification->updateMap(['html'=>$html]);
				}
			}break;
			case NOTIFICATION_ACCOUNT_WELCOME:{
				if($model->instanceof($User)){
					$text['email.title'] = 'Welcome to Blockstale';
					$text['title'] = 'Congratulations '.$model->getProperty('name').'!';
					$text['title.sub'] = 'You have finished setting up your new account';
					$text['email'] = $model->getProperty('email');
					$text['body'] = "We at Blockstale welcome you to the Crypto Currency Revolution, your journey to financial freedom begins here.";
					$url['link.url'] = Link::getURL('/');
					$url['link.text'] = 'Continue to Dashboard';
					$text['text'] = $text['body'].' '.$url['link.url'];
					
					$notification = $Notification->insertMap([
							'type'=>$notificationType,
							'name'=>$text['email.title'],
							'medium'=>$media[0],
							'user'=>$model->getID(),
							'text'=>$text['email.title'].' '.$text['body'],
							'html'=>'',
						]
					);
					$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
					$variables['url'] = $url;
					$variables['text'] = $text;
					$email = Email::getInstance($variables,'account.welcome');
					$html = $email->render();
					$notification->updateMap(['html'=>$html]);
				}
			}break;
			case NOTIFICATION_PHONE_VERIFICATION:{
				if($model->instanceof($User)){
					$text['email.title'] = 'SMS Verification';
					$text['title'] = 'Verify Phone Number';
					$text['title.sub'] = 'Phone verification needed to elevate your privilege';
					$text['user.name'] = $model->getProperty('name');
					$text['email'] = $model->getProperty('email');
					$text['body'] = "Your Blockstale verification code is: ".$model->getProperty('phone_verification_code');
					$url['link.url'] = Link::getURL('verify-phone',[$model->getProperty('phone_verification_code')]);
					$url['link.text'] = $text['body'];
					$text['text'] = $text['body'];

					$notification = $Notification->insertMap([
							'type'=>$notificationType,
							'name'=>$text['email.title'],
							'medium'=>$media[0],
							'user'=>$model->getID(),
							'text'=>$text['body'],
							'html'=>'',
						]
					);
					if(Utility::in_array(MEDIUM_EMAIL,$media)){
						$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
						$variables['url'] = $url;
						$variables['text'] = $text;
						$email = Email::getInstance($variables,'account.password.reset');
						$html = $email->render();
						$notification->updateMap(['html'=>$html]);
					}
				}
			}break;
			case NOTIFICATION_ACCOUNT_SIGN_IN:{
				if($model->instanceof($User)){
					$device = Utility::getClientDevice();
					$ipInfo = Utility::getIPInfo();
					$text['email.title'] = 'Account New Sign-in';
					$text['title'] = 'New Sign-in from '.$device;
					$text['title.sub'] = 'An authentication activity just succeeded on your account';
					$text['body'] = "Your Account was just signed in to from ".$ipInfo['region']." via ISP ".$ipInfo['org']." with IP:".$ipInfo['ip'].". You're getting this email to make sure that it was you";
					$text['body.sub'] = "You received this email to let you know about important changes to your Account, Services and Subscriptions";
					$text['os'] = '';// ' ['.$ipInfo['ip'];
					$text['device'] = $device;
					$text['ip'] = $ipInfo['city'].', '.$ipInfo['region'].' '.$ipInfo['country'].' >>'.$ipInfo['loc'];
					$text['timestamp'] = date_create()->format(DATE_COOKIE);
					$text['info.title'] = 'Information';
					$text['info.body'] = $text['body'];
					$url['link.url'] = Link::getLink(Link::getURL('contact'));
					$url['link.text'] = 'Block Device';
					$text['text'] = $text['body'];


					$notification = $Notification->insertMap([
							'type'=>$notificationType,
							'name'=>$text['email.title'],
							'medium'=>$media[0],
							'user'=>$model->getID(),
							'text'=>$text['body'],
							'html'=>''
					]);

					if(Utility::in_array(MEDIUM_EMAIL,$media)){
						$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
						$variables['url'] = $url;
						$variables['text'] = $text;
						$email = Email::getInstance($variables,'account.new.signin');
						$html = $email->render();
						$notification->updateMap(['html'=>$html]);
					}
				}
			}break;
			case NOTIFICATION_COMMISSION_REMITTED:{
				if($model->instanceof($Entry)){
					$user = $admin;// $User->getInstance($model->getProperty('coparty'));
					$text['email.title'] = 'Credit Alert';
					$text['title'] = 'Credit Alert';
					$text['title.sub'] = 'Your Blockstale account has been funded';
					$text['user.name'] = $user->getProperty('name');
					$text['email'] = $user->getProperty('email');
					$text['information.title'] = 'Remark';
					$text['information.desc'] = $model->getProperty('description');
					$text['body'] = $text['information.desc'];
					$text['account.title'] = $Account->getInstance($model->getProperty('account'))->getProperty('name');

					$text['account.value.desc'][0] = 'Credit';
					$text['account.value.symbol'][0] = $Currency->getInstance($model->getProperty('value_unit'))->getProperty('symbol');

					$balanceQTY = AccountController::getBalance($User->getInstance($model->getProperty('party')),$user,$model->getProperty('value_unit'),$model->getProperty('account'));
					$text['account.value.desc'][1] = 'Balance';
					$text['account.value.symbol'][1] = $text['account.value.symbol'][0];

					$cryptos = Utility::linearize($Currency->get('crypto','1'),'id');
					if(Utility::in_array($model->getProperty('value_unit'),$cryptos)){
						$text['account.value.number'][0] = sprintf('%.8f', $model->getProperty('value_number'));
						$text['account.value.number'][1] = sprintf('%.8f', $balanceQTY->getNumber());
					}else{
						$text['account.value.number'][0] = sprintf('%.2f', $model->getProperty('value_number'));
						$text['account.value.number'][1] = sprintf('%.2f', $balanceQTY->getNumber());
					}
					$text['text'] = $text['title'].'['.$text['account.title'].'] Amt:'.number_format($text['account.value.number'][0],2).' Bal:'.$text['account.value.number'][1];

					$notification = $Notification->insertMap([
						'type'=>$notificationType,
						'name'=>$text['email.title'],
						'medium'=>$media[0],
						'user'=>$model->getProperty('coparty'),
						'text'=>$text['body'],
						'html'=>''
					]);

					if(Utility::in_array(MEDIUM_EMAIL,$media)){
						$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
						$variables['url'] = $url;
						$variables['text'] = $text;
						$email = Email::getInstance($variables,'transaction');
						$html = $email->render();
						$notification->updateMap(['html'=>$html]);
					}
				}
			}break;
			case NOTIFICATION_CREDIT_ALERT:{
				if($model->instanceof($Entry)){
					$user = $User->getInstance($model->getProperty('coparty'));
					$text['email.title'] = 'Credit Alert';
					$text['title'] = 'Credit Alert';
					$text['title.sub'] = 'Your Blockstale account has been funded';
					$text['user.name'] = $user->getProperty('name');
					$text['email'] = $user->getProperty('email');
					$text['information.title'] = 'Remark';
					$text['information.desc'] = $model->getProperty('description');
					$text['body'] = $text['information.desc'];
					$text['account.title'] = $Account->getInstance($model->getProperty('account'))->getProperty('name');

					$text['account.value.desc'][0] = 'Credit';
					$text['account.value.symbol'][0] = $Currency->getInstance($model->getProperty('value_unit'))->getProperty('symbol');

					$balanceQTY = AccountController::getBalance($User->getInstance($model->getProperty('party')),$user,$model->getProperty('value_unit'),$model->getProperty('account'));
					$text['account.value.desc'][1] = 'Balance';
					$text['account.value.symbol'][1] = $text['account.value.symbol'][0];

					$cryptos = Utility::linearize($Currency->get('crypto','1'),'id');
					if(Utility::in_array($model->getProperty('value_unit'),$cryptos)){
						$text['account.value.number'][0] = sprintf('%.8f', $model->getProperty('value_number'));
						$text['account.value.number'][1] = sprintf('%.8f', $balanceQTY->getNumber());
					}else{
						$text['account.value.number'][0] = sprintf('%.2f', $model->getProperty('value_number'));
						$text['account.value.number'][1] = sprintf('%.2f', $balanceQTY->getNumber());
					}
					$text['text'] = $text['title'].'['.$text['account.title'].'] Amt:'.number_format($text['account.value.number'][0],2).' Bal:'.$text['account.value.number'][1];

					$notification = $Notification->insertMap([
						'type'=>$notificationType,
						'name'=>$text['email.title'],
						'medium'=>$media[0],
						'user'=>$model->getProperty('coparty'),
						'text'=>$text['body'],
						'html'=>''
					]);

					if(Utility::in_array(MEDIUM_EMAIL,$media)){
						$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
						$variables['url'] = $url;
						$variables['text'] = $text;
						$email = Email::getInstance($variables,'transaction');
						$html = $email->render();
						$notification->updateMap(['html'=>$html]);
					}
				}
			}break;
			case NOTIFICATION_DEBIT_ALERT:{
				if($model->instanceof($Entry)){
					$user = $User->getInstance($model->getProperty('coparty'));
					$text['email.title'] = 'Debit Alert';
					$text['title'] = 'Debit Alert';
					$text['title.sub'] = 'Your Blockstale account has been debited';
					$text['user.name'] = $user->getProperty('name');
					$text['email'] = $user->getProperty('email');
					$text['information.title'] = 'Remark';
					$text['information.desc'] = $model->getProperty('description');//"Please keep you account details safe and secure. We won't ask you for them at any time";
					$text['body'] = $text['information.desc'];
					$text['account.title'] = $Account->getInstance($model->getProperty('account'))->getProperty('name');

					$text['account.value.desc'][0] = 'Debit';
					$text['account.value.symbol'][0] = $Currency->getInstance($model->getProperty('value_unit'))->getProperty('symbol');

					$balanceQTY = AccountController::getBalance($User->getInstance($model->getProperty('party')),$user,$model->getProperty('value_unit'),$model->getProperty('account'));
					ul($balanceQTY);
					$text['account.value.desc'][1] = 'Balance';
					$text['account.value.symbol'][1] = $text['account.value.symbol'][0];

					$cryptos = Utility::linearize($Currency->get('crypto','1'),'id');
					if(Utility::in_array($model->getProperty('value_unit'),$cryptos)){
						$text['account.value.number'][0] = sprintf('%.8f', $model->getProperty('value_number'));
						$text['account.value.number'][1] = sprintf('%.8f', $balanceQTY->getNumber());
					}else{
						$text['account.value.number'][0] = sprintf('%.2f', $model->getProperty('value_number'));
						$text['account.value.number'][1] = sprintf('%.2f', $balanceQTY->getNumber());
					}
					$text['text'] = $text['title'].'['.$text['account.title'].'] Amt:'.number_format($text['account.value.number'][0],2).' Bal:'.$text['account.value.number'][1];

					$notification = $Notification->insertMap([
						'type'=>$notificationType,
						'name'=>$text['email.title'],
						'medium'=>$media[0],
						'user'=>$model->getProperty('coparty'),
						'text'=>$text['body'],
						'html'=>''
					]);

					if(Utility::in_array(MEDIUM_EMAIL,$media)){
						$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
						$variables['url'] = $url;
						$variables['text'] = $text;
						$email = Email::getInstance($variables,'transaction');
						$html = $email->render();
						$notification->updateMap(['html'=>$html]);
					}
				}
			}break;
			case NOTIFICATION_TRANSACTION_ABORTED:{//TODO:
				if($model->instanceof($BTCTransaction)){
					$user = $User->getInstance($model->getProperty('user'));					
					$text['email.title'] = 'Zomie Transaction Aborted';
					$text['title'] = 'Payout';
					$text['title.sub'] = 'Zomie Transaction Aborted';

					$text['user.image'] = Link::getFile($File->getInstance($admin->getProperty('avatar')));

					$text['user.name'] = '';
					$text['doc.state'] = $model->getStateInstance()->getProperty('name');
					$text['doc.date'] = Utility::simpleDate(date_create($model->getProperty('updated_at')));

					$text['recp.name'] = $user->getProperty('name');
					$text['body'] = 'You are receiving this message as an update to your recent payout request';
					$url['link.url'] = route('support');
					$url['link.text'] = 'Contact Support';
					$url['link.url.1'] = route('transactions').'/'.$model->getID();
					$url['link.text.1'] = 'View Request';
					// $text['text'] = $text['body'].' '.$url['link.url.1'];


					$notification = $Notification->insertMap([
						'type'=>$notificationType,
						'name'=>$text['email.title'],
						'medium'=>$media[0],
						'user'=>$model->getProperty('user'),
						'text'=>$text['body'],
						'html'=>''
					]);

					if(Utility::in_array(MEDIUM_EMAIL,$media)){
						$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
						$variables['url'] = $url;
						$variables['text'] = $text;
						$email = Email::getInstance($variables,'account.message');
						$html = $email->render();
						$notification->updateMap(['html'=>$html]);
					}
				}
			}break;
			case NOTIFICATION_PAYOUT_REQUESTED:{
				if($model->instanceof($BTCTransaction)){
					$user = $User->getInstance($model->getProperty('user'));					
					$text['email.title'] = 'Payout Request Update';
					$text['title'] = 'Payout';
					$text['title.sub'] = 'Payout Request Update';

					$text['user.image'] = Link::getFile($File->getInstance($admin->getProperty('avatar')));

					$text['user.name'] = 'Payout Request';
					$text['doc.state'] = $model->getStateInstance()->getProperty('name');
					$text['doc.date'] = Utility::simpleDate(date_create($model->getProperty('updated_at')));

					$text['recp.name'] = $user->getProperty('name');
					$text['body'] = 'You are receiving this message as an update to your recent payout request';
					$url['link.url'] = route('support');
					$url['link.text'] = 'Contact Support';
					$url['link.url.1'] = route('transactions').'/'.$model->getID();
					$url['link.text.1'] = 'View Request';
					// $text['text'] = $text['body'].' '.$url['link.url.1'];


					$notification = $Notification->insertMap([
						'type'=>$notificationType,
						'name'=>$text['email.title'],
						'medium'=>$media[0],
						'user'=>$model->getProperty('user'),
						'text'=>$text['body'],
						'html'=>''
					]);

					if(Utility::in_array(MEDIUM_EMAIL,$media)){
						$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
						$variables['url'] = $url;
						$variables['text'] = $text;
						$email = Email::getInstance($variables,'account.message');
						$html = $email->render();
						$notification->updateMap(['html'=>$html]);
					}
				}
			}break;
			case NOTIFICATION_PAYOUT_REQUESTED_ADMIN:{
				if($model->instanceof($BTCTransaction)){
					$user = $User->getInstance($model->getProperty('user'));
					$currency = $Currency->getInstance($model->getProperty('value_unit'));
					
					$text['email.title'] = 'Payout Request';
					$text['title'] = 'Payout Request';
					$text['title.sub'] = 'Merchant Payout Request';
					
					$dp = ($currency->getProperty('crypto')?8:2);
					$text['user.image'] = Link::getFile($File->getInstance($admin->getProperty('avatar')));

					$text['user.name'] = 'Payout Request';
					$text['doc.state'] = $model->getStateInstance()->getProperty('name');
					$text['doc.date'] = Utility::simpleDate(date_create($model->getProperty('created_at')));

					$text['recp.name'] = 'Admin';
					$text['body'] = 'A merchant ('.$user->getProperty('name').') has just requested a payout to the tune of '.bcadd($model->getProperty('value_number'),0,$dp).' '.$currency->getProperty('symbol').'. Please act soon.';
					$url['link.url'] = route('login');
					$url['link.text'] = 'Log In';
					$url['link.url.1'] = route('withdraw').'/'.$model->getID();
					$url['link.text.1'] = 'View Request';
					// $text['text'] = $text['body'].' '.$url['link.url.1'];


					$notification = $Notification->insertMap([
						'type'=>$notificationType,
						'name'=>$text['email.title'],
						'medium'=>$media[0],
						'user'=>$admin->getID(),
						'text'=>$text['body'],
						'html'=>''
					]);

					if(Utility::in_array(MEDIUM_EMAIL,$media)){
						$url['email.link'] = Link::getURL('comm').'/'.$notification->getID();
						$variables['url'] = $url;
						$variables['text'] = $text;
						$email = Email::getInstance($variables,'account.message');
						$html = $email->render();
						$notification->updateMap(['html'=>$html]);
					}
				}
			}break;
			
			case NOTIFICATION_ISSUE_RESOLVED:{
				Utility::log("made it to NOTIFICATION_ISSUE_RESOLVED id:".$refProxy->getID().' type:'.$refProxy->getType());
				if($refProxy && TypeProxy::isA($refProxy->getType(),TYPE_ISSUE)){
					Utility::log("made it to NOTIFICATION_ISSUE_RESOLVED-2 id:".$refProxy->getID().' type:'.$refProxy->getType());
					$senderProxy = TypeProxy::getInstanceByID(TYPE_PARTY,$refProxy->getPropertyValue(PROPERTY_PARTY)[0]);
					$text['email.title'] = 'Ticket Notification';
					$text['title'] = TypeDAO::getType($refProxy->getType());
					$text['title.sub'] = 'You ticket is now resolved '.$senderProxy->getPropertyValue(PROPERTY_NAME)[0];

					$fileProxy = TypeProxy::getInstanceByID(TYPE_FILE,$senderProxy->getPropertyValue([PROPERTY_ICON])[0]??'',REALM_KN);
					if($fileProxy)
						$text['user.image'] = Link::getFile($fileProxy);
					else
						$text['user.image'] = Link::getFile(TypeProxy::getInstance(TYPE_TYPE));

					$text['user.name'] = $senderProxy->getPropertyValue([PROPERTY_NAME])[0];
					$text['doc.state'] = $refProxy->getPropertyValue([PROPERTY_STATE,PROPERTY_NAME])[0];
					$text['doc.date'] = Utility::simpleDate(date_create($refProxy->getDAObject()->getProperty('updated_at')));

					$text['recp.name'] = $model->getProperty('name');
					$text['body'] = "This is to notify you that you ticket is now resolved by our operatives. Please review and if you need to get back to us just reply this message. Cheers!";
					$url['link.url'] = "mailto:".$senderProxy->getPropertyValue([PROPERTY_EMAIL])[0]."?subject=RE:".$text['title'].' '.$refProxy->getID();
					$url['link.text'] = 'Reply to Message';
					$url['link.url.1'] = Link::getLink(Link::getURL('track',[$refProxy->getID()]));
					$url['link.text.1'] = 'Track this Ticket';
					$text['text'] = $text['body'].' '.$url['link.url.1'];

					$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
					$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
					$variables['url'] = $url;
					$variables['text'] = $text;

					$email = Email::getInstance($variables,'account.message');
					$html = $email->render(); unset($email);
					Utility::log("now printing email content of NOTIFICATION_ISSUE_RESOLVED id:".$notificationProxy->getID());
					Utility::log($html);
					$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$html);
				}
			}break;
			case NOTIFICATION_ISSUE_RECEIVED:{
				Utility::log("made it to NOTIFICATION_ISSUE_RECEIVED id:".$refProxy->getID().' type:'.$refProxy->getType());
				if($refProxy && TypeProxy::isA($refProxy->getType(),TYPE_ISSUE)){
					Utility::log("made it to NOTIFICATION_ISSUE_RECEIVED-2 id:".$refProxy->getID().' type:'.$refProxy->getType());
					$senderProxy = TypeProxy::getInstanceByID(TYPE_PARTY,$refProxy->getPropertyValue(PROPERTY_PARTY)[0]);
					$text['email.title'] = 'Ticket Notification';
					$text['title'] = TypeDAO::getType($refProxy->getType());
					$text['title.sub'] = 'You opened a new ticket with '.$senderProxy->getPropertyValue(PROPERTY_NAME)[0];

					$fileProxy = TypeProxy::getInstanceByID(TYPE_FILE,$senderProxy->getPropertyValue([PROPERTY_ICON])[0]??'',REALM_KN);
					if($fileProxy)
						$text['user.image'] = Link::getFile($fileProxy);
					else
						$text['user.image'] = Link::getFile(TypeProxy::getInstance(TYPE_TYPE));

					$text['user.name'] = $senderProxy->getPropertyValue([PROPERTY_NAME])[0];
					$text['doc.state'] = $refProxy->getPropertyValue([PROPERTY_STATE,PROPERTY_NAME])[0];
					$text['doc.date'] = Utility::simpleDate(date_create($refProxy->getDAObject()->getProperty('updated_at')));

					$text['recp.name'] = $model->getProperty('name');
					$text['body'] = "Thanks for dropping us a line. Your entry is now being reviewed by our operatives. We'll keep you updated as events unfold. Cheers!";
					$url['link.url'] = "mailto:".$senderProxy->getPropertyValue([PROPERTY_EMAIL])[0]."?subject=RE:".$text['title'].' '.$refProxy->getID();
					$url['link.text'] = 'Reply to Message';
					$url['link.url.1'] = Link::getLink(Link::getURL('track',[$refProxy->getID()]));
					$url['link.text.1'] = 'Track this Ticket';
					$text['text'] = $text['body'].' '.$url['link.url.1'];

					$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
					$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
					$variables['url'] = $url;
					$variables['text'] = $text;

					$email = Email::getInstance($variables,'account.message');
					$html = $email->render(); unset($email);
					Utility::log("now printing email content of NOTIFICATION_ISSUE_RECEIVED id:".$notificationProxy->getID());
					Utility::log($html);
					$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$html);
				}
			}break;
			case NOTIFICATION_SERVICE_NOTIFICATION:{
				Utility::log("made it to NOTIFICATION_SERVICE_NOTIFICATION id:".$refProxy->getID().' type:'.$refProxy->getType());
				if($refProxy && TypeProxy::isA($refProxy->getType(),TYPE_SERVICE_BID)){
					Utility::log("made it to NOTIFICATION_SERVICE_NOTIFICATION-2 id:".$refProxy->getID().' type:'.$refProxy->getType());
					$senderProxy = TypeProxy::getInstanceByID(TYPE_PARTY,$refProxy->getPropertyValue(PROPERTY_PARTY)[0]);
					$offeringProxy = TypeProxy::getInstanceByID(TYPE_OFFERING,$refPX->getProperty(PROPERTY_REFERENCE)[0]);
					$offeringText = (TypeProxy::isA($offeringProxy->getType(),TYPE_PRODUCT)?'Product':'Service');
					$text['email.title'] = "$offeringText Notification";
					$text['title'] = "$offeringText Notification";
					$text['title.sub'] = "$offeringText notification from ".$senderProxy->getPropertyValue(PROPERTY_NAME)[0];

					$fileProxy = TypeProxy::getInstanceByID(TYPE_FILE,$senderProxy->getPropertyValue([PROPERTY_ICON])[0]??'',REALM_KN);
					if($fileProxy)
						$text['user.image'] = Link::getFile($fileProxy);
					else
						$text['user.image'] = Link::getFile(TypeProxy::getInstance(TYPE_TYPE));

					$text['user.name'] = $refProxy->getPropertyValue([PROPERTY_AUTHOR,PROPERTY_NAME])[0];
					$text['doc.state'] =  $offeringProxy->getPropertyValue([PROPERTY_STATE,PROPERTY_NAME])[0];
					$text['doc.date'] = Utility::simpleDate(date_create());

					$text['recp.name'] = $model->getProperty('name');
					$text['body'] = "I am the Pilot assigned to your ".$offeringProxy->getPropertyValue([PROPERTY_NAME,REALM_KN])[0].". I will be coming around between ".TypeProxy::getInstance($refProxy->getProperty(PROPERTY_ETA)[0]['type'],$refProxy->getPropertyValue(PROPERTY_ETA)[0],REALM_OP).", please be available to receive your order. Feel free to reach me at ".$refProxy->getPropertyValue([PROPERTY_AUTHOR,PROPERTY_PHONE])[0];
					$url['link.text'] = "Track this $offeringText";
					$url['link.url'] = Link::getLink(Link::getURL('track').'?e0='.$offeringProxy->getID());
					$text['text'] = $text['body'].' '.$url['link.url'];

					$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
					$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
					$variables['url'] = $url;
					$variables['text'] = $text;

					$email = Email::getInstance($variables,'service.update');
					$html = $email->render(); unset($email);
					Utility::log("now printing email content of NOTIFICATION_SERVICE_NOTIFICATION id:".$notificationProxy->getID());
					Utility::log($html);
					$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$html);
				}
			}break;
			case NOTIFICATION_SERVICE_UPDATE:{
				Utility::log("made it to NOTIFICATION_SERVICE_UPDATE id:".$refProxy->getID().' type:'.$refProxy->getType());
				if($refProxy && TypeProxy::isA($refProxy->getType(),TYPE_OFFERING)){
					Utility::log("made it to NOTIFICATION_SERVICE_UPDATE-2 id:".$refProxy->getID().' type:'.$refProxy->getType());
					$senderProxy = TypeProxy::getInstanceByID(TYPE_PARTY,$refProxy->getPropertyValue(PROPERTY_PARTY)[0]);
					$offeringText = (TypeProxy::isA($refProxy->getType(),TYPE_PRODUCT)?'Product':'Service');
					$text['email.title'] = "$offeringText Update";
					$text['title'] = TypeDAO::getType($refProxy->getType());
					$text['title.sub'] = "$offeringText update from ".$senderProxy->getPropertyValue(PROPERTY_NAME)[0];

					$fileProxy = TypeProxy::getInstanceByID(TYPE_FILE,$senderProxy->getPropertyValue([PROPERTY_ICON])[0]??'',REALM_KN);
					if($fileProxy)
						$text['user.image'] = Link::getFile($fileProxy);
					else
						$text['user.image'] = Link::getFile(TypeProxy::getInstance(TYPE_TYPE));

					$text['user.name'] = $refProxy->getPropertyValue([PROPERTY_SYMBOL,REALM_KN])[0];
					$text['doc.state'] = $refProxy->getPropertyValue([PROPERTY_STATE,PROPERTY_NAME])[0];
					$text['doc.date'] = Utility::simpleDate(date_create());

					$text['recp.name'] = $model->getProperty('name');
					$text['body'] = "This is to notify you that your $offeringText order with ".$senderProxy->getPropertyValue(PROPERTY_NAME)[0].' has just changed state in the servicing line. Follow the links below to keep minute-by-minute track of your order. To opt out of these messages please visit your communication preferences online.';
					$url['link.text'] = "Track this $offeringText";
					$url['link.url'] = Link::getLink(Link::getURL('track').'?e0='.$refProxy->getID());
					$text['text'] = $text['body'].' '.$url['link.url'];

					$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
					$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
					$variables['url'] = $url;
					$variables['text'] = $text;

					$email = Email::getInstance($variables,'service.update');
					$html = $email->render(); unset($email);
					Utility::log("now printing email content of NOTIFICATION_SERVICE_UPDATE id:".$notificationProxy->getID());
					Utility::log($html);
					$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$html);
				}
			}break;
			case NOTIFICATION_BID_OFFER_ACCEPTED:{
				Utility::log("made it to NOTIFICATION_BID_OFFER_ACCEPTED id:".$refProxy->getID().' type:'.$refProxy->getType());
				if($refProxy && TypeProxy::isA($refProxy->getType(),TYPE_DOCUMENT)){
					Utility::log("made it to NOTIFICATION_BID_OFFER_ACCEPTED-2 id:".$refProxy->getID().' type:'.$refProxy->getType());
					$senderProxy = TypeProxy::getInstanceByID(TYPE_PARTY,$refProxy->getPropertyValue(PROPERTY_PARTY)[0]);
					$text['email.title'] = 'Service Materials';
					$text['title'] = TypeDAO::getType($refProxy->getType());
					$text['title.sub'] = 'Thanks for accepting the offer';

					$fileProxy = TypeProxy::getInstanceByID(TYPE_FILE,$senderProxy->getPropertyValue([PROPERTY_ICON])[0]??'',REALM_KN);
					if($fileProxy)
						$text['user.image'] = Link::getFile($fileProxy);
					else
						$text['user.image'] = Link::getFile(TypeProxy::getInstance(TYPE_TYPE));

					$text['user.name'] = $refProxy->getPropertyValue([PROPERTY_REFERENCE,PROPERTY_AUTHOR,PROPERTY_NAME])[0];
					$text['doc.state'] = $refProxy->getPropertyValue([PROPERTY_STATE,PROPERTY_NAME])[0];
					$text['doc.date'] = Utility::simpleDate(date_create());

					$text['recp.name'] = $model->getProperty('name');
					$text['body'] = "Be informed that the recipients have been informed of your servicing. Please follow the link below to access the service gazette";
					$url['link.url'] = Link::getLink(Link::getURL('logistics').'?e0='.TYPE_BOM.'&e1='.OP_X.'&e2='.$refProxy->getID());
					$url['link.text'] = 'View Gazette';
					$text['text'] = $text['body'].' '.$url['link.url'];

					$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
					$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
					$variables['url'] = $url;
					$variables['text'] = $text;

					$email = Email::getInstance($variables,'service.update');
					$html = $email->render(); unset($email);
					Utility::log("now printing email content of NOTIFICATION_BID_OFFER_ACCEPTED id:".$notificationProxy->getID());
					Utility::log($html);
					$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$html);
				}
			}break;
			case NOTIFICATION_BID_CONFIRMED:{
				Utility::log("made it to NOTIFICATION_BID_CONFIRMED id:".$refProxy->getID().' type:'.$refProxy->getType());
				if($refProxy && TypeProxy::isA($refProxy->getType(),TYPE_DOCUMENT)){
					Utility::log("made it to NOTIFICATION_BID_CONFIRMED-2 id:".$refProxy->getID().' type:'.$refProxy->getType());
					$senderProxy = TypeProxy::getInstanceByID(TYPE_PARTY,$refProxy->getPropertyValue(PROPERTY_PARTY)[0]);
					$text['email.title'] = 'Contract Update';
					$text['title'] = TypeDAO::getType($refProxy->getType());
					$text['title.sub'] = 'Congratulations, your contract bid was confirmed';

					$fileProxy = TypeProxy::getInstanceByID(TYPE_FILE,$senderProxy->getPropertyValue([PROPERTY_ICON])[0]??'',REALM_KN);
					if($fileProxy)
						$text['user.image'] = Link::getFile($fileProxy);
					else
						$text['user.image'] = Link::getFile(TypeProxy::getInstance(TYPE_TYPE));

					$text['user.name'] = $refProxy->getPropertyValue([PROPERTY_REFERENCE,PROPERTY_AUTHOR,PROPERTY_NAME])[0];
					$text['doc.state'] = $refProxy->getPropertyValue([PROPERTY_STATE,PROPERTY_NAME])[0];
					$text['doc.date'] = Utility::simpleDate(date_create());

					$text['recp.name'] = $model->getProperty('name');
					$text['body'] = "Congratulations your service bid was accepted. Please follow the link to accept the offer. By accepting the offer, you agree to ".$senderProxy->getPropertyValue(PROPERTY_NAME)[0]." Terms and Policy for service delivery. Please note that while the offer is YET to be accepted by you, we reserve the right to award it to someone else without prior notification. Good luck!";
					$url['link.url'] = Link::getLink(Link::getURL('logistics').'?e0='.$refProxy->getType().'&e1='.OP_R.'&e2='.$refProxy->getID());
					$url['link.text'] = 'Accept Offer';
					$text['text'] = $text['body'].' '.$url['link.url'];

					$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
					$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
					$variables['url'] = $url;
					$variables['text'] = $text;

					$email = Email::getInstance($variables,'service.update');
					$html = $email->render(); unset($email);
					Utility::log("now printing email content of NOTIFICATION_BID_CONFIRMED id:".$notificationProxy->getID());
					Utility::log($html);
					$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$html);
				}
			}break;
			case NOTIFICATION_BID_DECLINED:{
				Utility::log("made it to NOTIFICATION_BID_DECLINED id:".$refProxy->getID().' type:'.$refProxy->getType());
				if($refProxy && TypeProxy::isA($refProxy->getType(),TYPE_DOCUMENT)){
					Utility::log("made it to NOTIFICATION_BID_DECLINED-2 id:".$refProxy->getID().' type:'.$refProxy->getType());
					$senderProxy = TypeProxy::getInstanceByID(TYPE_PARTY,$refProxy->getPropertyValue(PROPERTY_PARTY)[0]);
					$text['email.title'] = 'Contract Update';
					$text['title'] = TypeDAO::getType($refProxy->getType());
					$text['title.sub'] = 'Sorry, your contract bid was declined';

					$fileProxy = TypeProxy::getInstanceByID(TYPE_FILE,$senderProxy->getPropertyValue([PROPERTY_ICON])[0]??'',REALM_KN);
					if($fileProxy)
						$text['user.image'] = Link::getFile($fileProxy);
					else
						$text['user.image'] = Link::getFile(TypeProxy::getInstance(TYPE_TYPE));

					$text['user.name'] = $refProxy->getPropertyValue([PROPERTY_REFERENCE,PROPERTY_AUTHOR,PROPERTY_NAME])[0];
					$text['doc.state'] = $refProxy->getPropertyValue([PROPERTY_STATE,PROPERTY_NAME])[0];
					$text['doc.date'] = Utility::simpleDate(date_create());

					$text['recp.name'] = $model->getProperty('name');
					$text['body'] = "Sorry, due to popular demand your service bid was declined. Thank you for your interest in service delivery with ".$senderProxy->getPropertyValue(PROPERTY_NAME)[0].", the next offer may be yours. Goodluck!";
					$url['link.url'] = "mailto:".$refProxy->getPropertyValue([PROPERTY_AUTHOR,PROPERTY_EMAIL])[0]."?subject=RE:".$text['title'].' '.$refProxy->getID();
					$url['link.text'] = 'Reply to Message';
					$url['link.url.1'] = Link::getLink(Link::getURL('logistics').'?e0='.TYPE_PROCESSING_SCHEDULE.'&e1='.OP_L);
					$url['link.text.1'] = 'Search Offers';
					$text['text'] = $text['body'].' '.$url['link.url.1'];

					$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
					$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
					$variables['url'] = $url;
					$variables['text'] = $text;

					$email = Email::getInstance($variables,'account.message');
					$html = $email->render(); unset($email);
					Utility::log("now printing email content of NOTIFICATION_BID_DECLINED id:".$notificationProxy->getID());
					Utility::log($html);
					$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$html);
				}
			}break;
			case NOTIFICATION_DOC_UPDATE:{
				Utility::log("made it to NOTIFICATION_DOC_UPDATE id:".$refProxy->getID().' type:'.$refProxy->getType());
				if($refProxy && TypeProxy::isA($refProxy->getType(),TYPE_DOCUMENT)){
					Utility::log("made it to NOTIFICATION_DOC_UPDATE-2 id:".$refProxy->getID().' type:'.$refProxy->getType());
					$senderProxy = TypeProxy::getInstanceByID(TYPE_PARTY,$refProxy->getPropertyValue(PROPERTY_PARTY)[0]);
					$text['email.title'] = 'Document Update';
					$text['title'] = TypeDAO::getType($refProxy->getType());
					$text['title.sub'] = 'Document action requested from '.$senderProxy->getPropertyValue(PROPERTY_NAME)[0];

					$fileProxy = TypeProxy::getInstanceByID(TYPE_FILE,$senderProxy->getPropertyValue([PROPERTY_ICON])[0]??'',REALM_KN);
					if($fileProxy)
						$text['user.image'] = Link::getFile($fileProxy);
					else
						$text['user.image'] = Link::getFile(TypeProxy::getInstance(TYPE_TYPE));

					$text['user.name'] = $refProxy->getPropertyValue([PROPERTY_AUTHOR,PROPERTY_NAME])[0];
					$text['doc.state'] = $refProxy->getPropertyValue([PROPERTY_STATE,PROPERTY_NAME])[0];
					$text['doc.date'] = Utility::simpleDate(date_create($refProxy->getDAObject()->getProperty('updated_at')));

					$text['recp.name'] = $model->getProperty('name');
					$text['body'] = 'You are receiving this message because a business document raised at '.$senderProxy->getPropertyValue(PROPERTY_NAME)[0].' requires your action. Please follow the links given to act as appropriate';
					$url['link.url'] = "mailto:".$refProxy->getPropertyValue([PROPERTY_AUTHOR,PROPERTY_EMAIL])[0]."?subject=RE:".$text['title'].' '.$refProxy->getID();
					$url['link.text'] = 'Reply to Message';
					$url['link.url.1'] = Link::getLink(Link::getURL('logistics').'?e0='.$refProxy->getType().'&e1='.OP_R.'&e2='.$refProxy->getID());
					$url['link.text.1'] = 'View Document';
					$text['text'] = $text['body'].' '.$url['link.url.1'];

					$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
					$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
					$variables['url'] = $url;
					$variables['text'] = $text;

					$email = Email::getInstance($variables,'account.message');
					$html = $email->render(); unset($email);
					Utility::log("now printing email content of NOTIFICATION_DOC_UPDATE id:".$notificationProxy->getID());
					Utility::log($html);
					$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$html);
				}
			}break;
			case NOTIFICATION_ORDER_CONFIRMATION:{
				Utility::log("made it to NOTIFICATION_ORDER_CONFIRMATION id:".$refProxy->getID().' type:'.$refProxy->getType());
				if($refProxy && TypeProxy::isA($refProxy->getType(),TYPE_INVOICE_SALES)){
					Utility::log("made it to NOTIFICATION_ORDER_CONFIRMATION-2 id:".$refProxy->getID().' type:'.$refProxy->getType());
					$text['email.title'] = 'Order Confirmation';
					$text['title'] = 'Order Confirmation';
					$text['title.sub'] = "Thanks, your order is now being processed";
					$text['user.name'] = $model->getProperty('name');
					$text['email'] = $model->getProperty('email');
					$text['body'] = 'You are receiving this message because you placed a service order on '.Utility::currentCompany()->getPropertyValue(PROPERTY_NAME)[0];
					$url['link.url'] = Link::getLink(Link::getURL('invoice').'?e0='.OP_R.'&e1='.$refProxy->getID());
					$url['link.text'] = 'Download Invoice';
					$text['text'] = $text['body'].' '.$url['link.url'];

					foreach($refProxy->getPropertyValue([PROPERTY_LINE]) as $i=>$offering){
						$offeringProxy = TypeProxy::getInstanceByID(TYPE_OFFERING,$offering,REALM_OP);
						$text['invoice.line'][$i]['name'] = $offeringProxy->getPropertyValue([PROPERTY_SYMBOL,REALM_KN])[0];
						$text['invoice.line'][$i]['desc'] = $offeringProxy->getPropertyValue([PROPERTY_NAME,REALM_KN])[0];
						$text['invoice.line'][$i]['unit'] = $offeringProxy->getPropertyValue([PROPERTY_UNIT])[0];
						$uvPX = Offering::getUnitValue($offeringProxy);
						$text['invoice.line'][$i]['value.number'] = $uvPX->getProperty(PROPERTY_NUMBER)[0];
						$text['invoice.line'][$i]['value.symbol'] = $uvPX->getProperty(PROPERTY_SYMBOL)[0];
						$fileProxy = TypeProxy::getInstanceByID(TYPE_FILE,$offeringProxy->getPropertyValue([PROPERTY_ICON,REALM_KN])[0]??'',REALM_KN);
						if($fileProxy)
							$text['invoice.line'][$i]['image.url'] = Link::getFile($fileProxy);
						else
							$text['invoice.line'][$i]['image.url'] = Link::getFile(TypeProxy::getInstance(TYPE_TYPE));
						$text['invoice.line'][$i]['link.text'] = 'Track this Item';
						$text['invoice.line'][$i]['link.url'] = Link::getLink(Link::getURL('track').'?e0='.$offeringProxy->getID());
						$text['invoice.line'][$i]['sku'] = $offeringProxy->getPropertyValue([PROPERTY_STATE,PROPERTY_NAME])[0];
					}

					$text['invoice.total.name'][0] = 'Subtotal';
					$text['invoice.total.value.number'][0] = $refProxy->getPropertyValue([PROPERTY_VALUE,PROPERTY_NUMBER])[0];
					$text['invoice.total.value.symbol'][0] = $refProxy->getPropertyValue([PROPERTY_VALUE,PROPERTY_UNIT,PROPERTY_SYMBOL])[0];

					$text['invoice.total.name'][1] = 'VAT';
					$text['invoice.total.value.number'][1] = 0;
					$text['invoice.total.value.symbol'][1] = $text['invoice.total.value.symbol'][0];

					$text['invoice.total.name'][2] = 'Total Due';
					$text['invoice.total.value.number'][2] = $text['invoice.total.value.number'][0];
					$text['invoice.total.value.symbol'][2] = $text['invoice.total.value.symbol'][0];

					$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
					$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
					$variables['url'] = $url;
					$variables['text'] = $text;

					$email = Email::getInstance($variables,'order.confirmation.4');
					$html = $email->render(); unset($email);
					Utility::log("now printing email content of NOTIFICATION_ORDER_CONFIRMATION id:".$notificationProxy->getID());
					Utility::log($html);
					$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$html);
				}
			}break;
			case NOTIFICATION_PAYMENT_CONFIRMATION:{
				Utility::log("made it to NOTIFICATION_PAYMENT_CONFIRMATION id:".$refProxy->getID().' type:'.$refProxy->getType());
				if($refProxy && TypeProxy::isA($refProxy->getType(),TYPE_ORDER_PAY)){
					Utility::log("made it to NOTIFICATION_PAYMENT_CONFIRMATION-2 id:".$refProxy->getID().' type:'.$refProxy->getType());
					$text['email.title'] = 'Payment Successful';
					$text['title'] = 'Payment Successful';
					$text['title.sub'] = "Thanks, your payment has been received";
					$text['user.name'] = $model->getProperty('name');
					$text['email'] = $model->getProperty('email');
					$text['body'] = 'You are receiving this message because you made a payment transaction on '.Utility::currentCompany()->getPropertyValue(PROPERTY_NAME)[0];
					$url['link.url'] = Link::getLink(Link::getURL('invoice').'?e0='.OP_R.'&e1='.$refProxy->getPropertyValue([PROPERTY_INVOICE])[0]);
					$url['link.text'] = 'Download Receipt';
					$text['text'] = $text['body'].' '.$url['link.url'];

					$text['invoice.title'] = 'Invoice #'.substr($refProxy->getPropertyValue(PROPERTY_INVOICE)[0],0,5);
					$text['invoice.title.sub'] = 'Authorized by '.$refProxy->getPropertyValue([PROPERTY_PARTY,PROPERTY_NAME])[0];
					$text['invoice.value.desc'] = 'Total Pay';
					$text['invoice.value.number'] = $refProxy->getPropertyValue([PROPERTY_VALUE,PROPERTY_NUMBER])[0];
					$text['invoice.value.symbol'] = $refProxy->getPropertyValue([PROPERTY_VALUE,PROPERTY_UNIT,PROPERTY_SYMBOL])[0];

					$text['invoice.biller.header'][0] = 'Biller Information';
					$text['invoice.biller.header'][1] = 'Clearing Information';

					$text['invoice.biller.desc'][0] = $refProxy->getPropertyValue([PROPERTY_PARTY_COUNTER,PROPERTY_EMAIL])[0]??'N/A';
					$text['invoice.biller.desc'][1] = $refProxy->getPropertyValue([PROPERTY_PARTY,PROPERTY_EMAIL])[0]??'N/A';

					foreach($refProxy->getPropertyValue([PROPERTY_INVOICE,PROPERTY_LINE]) as $i=>$offering){
						$offeringProxy = TypeProxy::getInstanceByID(TYPE_OFFERING,$offering,REALM_OP);
						$text['invoice.line'][$i]['name'] = $offeringProxy->getPropertyValue([PROPERTY_SYMBOL,REALM_KN])[0];
						$text['invoice.line'][$i]['desc'] = $offeringProxy->getPropertyValue([PROPERTY_NAME,REALM_KN])[0];
						$text['invoice.line'][$i]['unit'] = $offeringProxy->getPropertyValue([PROPERTY_UNIT])[0];
						$text['invoice.line'][$i]['value.number'] = Offering::getUnitValue($offeringProxy)->getProperty(PROPERTY_NUMBER)[0];
						$text['invoice.line'][$i]['value.symbol'] = $offeringProxy->getPropertyValue([PROPERTY_VALUE,PROPERTY_UNIT,PROPERTY_SYMBOL])[0];
					}

					$text['invoice.total.name'][0] = 'Subtotal';
					$text['invoice.total.value.number'][0] = $refProxy->getPropertyValue([PROPERTY_INVOICE,PROPERTY_VALUE,PROPERTY_NUMBER])[0];
					$text['invoice.total.value.symbol'][0] = $refProxy->getPropertyValue([PROPERTY_INVOICE,PROPERTY_VALUE,PROPERTY_UNIT,PROPERTY_SYMBOL])[0];

					$text['invoice.total.name'][1] = 'VAT';
					$text['invoice.total.value.number'][1] = 0;
					$text['invoice.total.value.symbol'][1] = $text['invoice.total.value.symbol'][0];

					$text['invoice.total.name'][2] = 'Total Due';
					$text['invoice.total.value.number'][2] = $text['invoice.total.value.number'][0];
					$text['invoice.total.value.symbol'][2] = $text['invoice.total.value.symbol'][0];

					$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
					$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
					$variables['url'] = $url;
					$variables['text'] = $text;

					$email = Email::getInstance($variables,'invoice.3');
					$html = $email->render(); unset($email);
					Utility::log("now printing email content of NOTIFICATION_PAYMENT_CONFIRMATION id:".$notificationProxy->getID());
					Utility::log($html);
					$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$html);
				}
			}break;
			case NOTIFICATION_ACCOUNT_VERIFICATION:{
				$text['email.title'] = 'Account Verification';
				$text['title'] = 'Verify E-mail Address';
				$text['title.sub'] = 'Email verification needed to activate your new account';
				$text['user.name'] = $model->getProperty('name');
				$text['email'] = $recipientProxy->getPropertyValue(PROPERTY_EMAIL)[0]??Utility::currentCompany()->getPropertyValue(PROPERTY_EMAIL)[0];
				$text['body'] = Utility::getNote('account.activation');
				$url['link.url'] = $link = Link::getLink(Link::getURL('activate').'?q='.$recipientProxy->getPropertyValue(PROPERTY_CONFIRMATION_CODE)[0]);
				$url['link.text'] = 'Verify My E-mail Address';
				$text['text'] = $text['body'].' '.$url['link.url'];

				$notificationProxy = Comm::getNotification($notificationType,$text['email.title'],$media,'',$text['text'],$recipientProxy);
				$url['email.link'] = Link::getLink(Link::getURL('comm',[$notificationProxy->getID()]));
				$variables['url'] = $url;
				$variables['text'] = $text;

				$email = Email::getInstance($variables,'account.verification');
				$notificationProxy->updateProperty([PROPERTY_HTML,PROPERTY_CONTENT],$email->render());
			}break;
		}
		if($notification){
			foreach($media as $i=>$medium){
				if($medium==MEDIUM_EMAIL){
					try{
						return Mailer::send($notification->getProperty('name'),[$User->getInstance($notification->getProperty('user'))],$notification->getProperty('html'),$notification->getProperty('text'));
					}catch(\Exception $e){}
				}else
				if($medium==MEDIUM_SMS){
					try{
						return Messager::send($notification->getProperty('text'),[$User->getInstance($notification->getProperty('user'))]);
					}catch(\Exception $e){}
				}
			}
		}
		return false;
	}
	public static function getNotification(string $type, string $title, string $medium, string $html, string $text, Model $model){
		return null;
	}
}
?>