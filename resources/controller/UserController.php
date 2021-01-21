<?php
namespace controller;

use contract\Controller;
use view\Page;
use helper\Utility;
use contract\Message;
use comm\Comm;
use comm\Link;
use contract\Event;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

class UserController extends Controller{	
	/** @return void  */
	static function attemptVerifyKYC(){
		global $request;
		global $response;
		global $user;
		global $Business;
		global $BankAccount;
		global $File;
		if(SessionController::verifyCSRF()){
			if(($business = $Business->get('user', $request->getVariable('id'))[0]??false) && $business['name'] && $business['address'] && $business['state']){
				if(($account = $BankAccount->get('user',$request->getVariable('id'))[0]??false) && $account['name'] && $account['number'] && $account['currency'] && ['bank']){
					if($user->getProperty('id_type') && $user->getProperty('id_file')){
						$Business->setState(STATE_PENDING,$user,'KYC Approval Requested by user',$Business->getInstance($business['id']));
						$BankAccount->setState(STATE_PENDING,$user,'Account Approval Requested by user',$BankAccount->getInstance($account['id']));
						$File->setState(STATE_PENDING,$user,'ID Approval Requested by user',$File->getInstance($user->getProperty('id_file')));
						$response->addMessage(new Message('Business profile submitted for verification', 'Business profile submitted for verification', THEME_INFO));
					}else{
						$response->addMessage(new Message('Please upload a valid ID', 'Please upload a valid ID', THEME_DANGER));
						$response->setStatusCode(401);
					}
				}else{
					$response->addMessage(new Message('Please submit a business profile', 'Please submit a business profile', THEME_DANGER));
					$response->setStatusCode(401);
				}	
			}else{
				$response->addMessage(new Message('Please update your account details', 'Please update your account details', THEME_DANGER));
				$response->setStatusCode(401);
			}			
		}
	}
	static function showPlatform(){
		global $response;
		$text = ['page.title' => 'Platform'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'platform');
		$response->setPage($page);
	}
	static function showFAQ(){
		global $response;
		$text = ['page.title' => 'FAQ'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'faq');
		$response->setPage($page);
	}
	static function showAbout(){
		global $response;
		$text = ['page.title' => 'About Us'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'about');
		$response->setPage($page);
	}
	static function showContact(){
		global $response;
		$text = ['page.title' => 'Contact Us'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'contact');
		$response->setPage($page);
	}
	static function showTransaction(){
		global $response;
		$text = ['page.title' => 'Transaction'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'transaction');
		$response->setPage($page);
	}
	static function showTransactions(){
		global $response;
		$text = ['page.title' => 'Transactions'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'transactions');
		$response->setPage($page);
	}
	static function showMerchant(){
		global $response;
		$text = ['page.title' => 'Merchants'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'merchant');
		$response->setPage($page);
	}
	static function showVendor(){
		global $response;
		$text = ['page.title' => 'Vendors'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'vendor');
		$response->setPage($page);
	}
	static function updatePlatform(){
		global $request;
		global $response;
		global $Pref;
		global $FX;
		if(SessionController::verifyCSRF()){
			if($request->getParam('base')){
				if($fxs = $FX->getGeneric("WHERE `base`='".$request->getParam('base')."' AND `quote`='".$request->getParam('quote')."'")){
					if($FX->updateGeneric("SET `buy`='".$request->getParam('buy')."', `sell`='".$request->getParam('sell')."' WHERE `id`='".$fxs[0]['id']."'")){
						$response->addMessage(new Message('FX Rate Updated', 'FX Rate Updated', THEME_SECONDARY));
					}else{
						$response->addMessage(new Message('FX Rate Exists', 'FX Rate Exists', THEME_SECONDARY));
						$response->setStatusCode(400);
					}					
				}else
				if($FX->insertMap(['base' => $request->getParam('base'), 'quote' => $request->getParam('quote'), 'buy' => $request->getParam('buy'), 'sell' => $request->getParam('sell')]))
					$response->addMessage(new Message('FX Rate defined', 'FX Rate defined', THEME_SECONDARY));
				else{
					$response->addMessage(new Message('FX Rate not updated', 'FX Rate not updated', THEME_SECONDARY));
					$response->setStatusCode(400);
				}
			}else
			if($request->getParam('conf')){
				if($prefs = $Pref->getGeneric("WHERE `config`='".$request->getParam('conf')."' AND `entity`='".$request->getVariable('id')."'")){
					if($Pref->updateGeneric("SET `value`='" . $request->getParam('value') . "' WHERE `id`='".$prefs[0]['id']."'")){
						$response->addMessage(new Message('Preference updated', 'Preference updated', THEME_SECONDARY));
					}else{
						$response->addMessage(new Message('Preference value exists', 'Preference value exists', THEME_SECONDARY));
						$response->setStatusCode(400);
					}					
				}else
				if($Pref->insertMap(['config' => $request->getParam('conf'), 'entity' => $request->getVariable('id'), 'value' => $request->getParam('value')]))
					$response->addMessage(new Message('Preference defined', 'Preference defined', THEME_SECONDARY));
				else{
					$response->addMessage(new Message('Preference not updated', 'Preference not updated', THEME_SECONDARY));
					$response->setStatusCode(400);
				}
			}else
			if($request->getParam('confs')){
				foreach($request->getParam('confs') as $i=>$conf){
					if($prefs = $Pref->getGeneric("WHERE `config`='$i' AND `entity`='".$request->getVariable('id')."'")){
						$Pref->updateGeneric("SET `value`='$conf' WHERE `id`='".$prefs[0]['id']."'");
					}else{
						$Pref->insertMap(['config' => $i, 'entity' => $request->getVariable('id'), 'value' => $conf]);
					}
				}
				$response->addMessage(new Message('Preferences Updated', 'Preferences Updated', THEME_SECONDARY));
			}else{
				$response->addMessage(new Message('Invalid update', 'Invalid update', THEME_INFO));
				$response->setStatusCode(401);
			}
		}
	}
	static function updateMerchant(){
		global $request;
		global $response;
		global $Pref;
		global $FX;
		if(SessionController::verifyCSRF()){
			if($request->getParam('conf')){
				if($prefs = $Pref->getGeneric("WHERE `config`='".$request->getParam('conf')."' AND `entity`='".$request->getVariable('id')."'")){
					if($Pref->updateGeneric("SET `value`='" . $request->getParam('value') . "' WHERE `id`='".$prefs[0]['id']."'")){
						$response->addMessage(new Message('Preference updated', 'Preference updated', THEME_SECONDARY));
					}else{
						$response->addMessage(new Message('Preference value exists', 'Preference value exists', THEME_SECONDARY));
						$response->setStatusCode(400);
					}					
				}else
				if($Pref->insertMap(['config' => $request->getParam('conf'), 'entity' => $request->getVariable('id'), 'value' => $request->getParam('value')]))
					$response->addMessage(new Message('Preference defined', 'Preference defined', THEME_SECONDARY));
				else{
					$response->addMessage(new Message('Preference not updated', 'Preference not updated', THEME_SECONDARY));
					$response->setStatusCode(400);
				}
			}else
			if($request->getParam('confs')){
				foreach($request->getParam('confs') as $i=>$conf){
					if($prefs = $Pref->getGeneric("WHERE `config`='$i' AND `entity`='".$request->getVariable('id')."'")){
						$Pref->updateGeneric("SET `value`='$conf' WHERE `id`='".$prefs[0]['id']."'");
					}else{
						$Pref->insertMap(['config' => $i, 'entity' => $request->getVariable('id'), 'value' => $conf]);
					}
				}
				$response->addMessage(new Message('Preferences Updated', 'Preferences Updated', THEME_SECONDARY));
			}else{
				$response->addMessage(new Message('Invalid update', 'Invalid update', THEME_INFO));
				$response->setStatusCode(401);
			}
		}
	}
	static function updateVendor(){
		global $request;
		global $response;
		global $user;
		global $Pref;
		global $File;
		global $Business;
		global $BankAccount;
		if(SessionController::verifyCSRF()){
			if($request->getParam('bp-name')){
				if($buss = $Business->get('user', $request->getVariable('id'))){
					$business = $Business->getInstance($buss[0]['id']);
					if($business->updateMap(['name' => $request->getParam('bp-name'), 'address' => $request->getParam('bp-address'), 'state' => $request->getParam('bp-state')])){
						$business->setState(STATE_PENDING,$user,' ');
						$response->addMessage(new Message('Business Profile Updated', 'Business Profile Updated', THEME_INFO));
					}else{
						$response->addMessage(new Message('Values already exist', 'Values already exist', THEME_SECONDARY));
						$response->setStatusCode(400);
					}
				}else
				if($business = $Business->insertMap(['name'=>$request->getParam('bp-name'), 'user'=>$request->getVariable('id'), 'address'=>$request->getParam('bp-address'), 'state'=>$request->getParam('bp-state')])){
					$business->setState(STATE_PENDING,$user,' ');
					$response->addMessage(new Message('Business profile defined', 'Business profile defined', THEME_SECONDARY));
				}else{
					$response->addMessage(new Message('Business profile not updated', 'Business profile not updated', THEME_SECONDARY));
					$response->setStatusCode(400);
				}
			}else
			if($request->getFile('id_file') && $request->getParam('bp-id-type') && $user->updateFiles() && $user->updateMap(['id_type' => $request->getParam('bp-id-type')])){
				$File->setState(STATE_PENDING,$user,'',$File->getInstance($user->getProperty('id_file')));
				$response->addMessage(new Message('ID updated successfully', 'ID updated successfully', THEME_INFO));
			}else
			if($request->getParam('acc-number')){
				if($acct = $BankAccount->get('user', $request->getVariable('id'))){
					$account = $BankAccount->getInstance($acct[0]['id']);
					if($account->updateMap(['name' => $request->getParam('acc-name'), 'number' => $request->getParam('acc-number'), 'user' => $request->getVariable('id'), 'bank' => $request->getParam('acc-bank'), 'currency' => $request->getParam('acc-currency')])){
						$account->setState(STATE_PENDING,$user,' ');
						$response->addMessage(new Message('Account details updated', "Account profile updated", THEME_SECONDARY));
					}else{
						$response->addMessage(new Message('Duplicated values detected', "Duplicated values detected", THEME_SECONDARY));
						$response->setStatusCode(400);
					}
				}else
				if($account = $BankAccount->insertMap(['name' => $request->getParam('acc-name'), 'number' => $request->getParam('acc-number'), 'user' => $request->getVariable('id'), 'bank' => $request->getParam('acc-bank'), 'currency' => $request->getParam('acc-currency')])){
					$account->setState(STATE_PENDING,$user,' ');	
					$response->addMessage(new Message('Account details defined', 'Account details defined', THEME_SECONDARY));
				}else{
					$response->addMessage(new Message('Account details not updated', 'Account details not updated', THEME_SECONDARY));
					$response->setStatusCode(400);
				}
			}else
			if($request->getParam('confs')){
				foreach($request->getParam('confs') as $i=>$conf){
					if($prefs = $Pref->getGeneric("WHERE `config`='$i' AND `entity`='".$request->getVariable('id')."'")){
						$Pref->updateGeneric("SET `value`='$conf' WHERE `id`='".$prefs[0]['id']."'");
					}else{
						$Pref->insertMap(['config' => $i, 'entity' => $request->getVariable('id'), 'value' => $conf]);
					}
				}
				$response->addMessage(new Message('Preferences Updated', 'Preferences Updated', THEME_SECONDARY));
			}else
			if($request->getParam('conf')){
				if($prefs = $Pref->getGeneric("WHERE `config`='".$request->getParam('conf')."' AND `entity`='".$request->getVariable('id')."'")){
					if($Pref->updateGeneric("SET `value`='" . $request->getParam('value') . "' WHERE `id`='".$prefs[0]['id']."'")){
						$response->addMessage(new Message('Preference updated', 'Preference updated', THEME_SECONDARY));
					}else{
						$response->addMessage(new Message('Preference value exists', 'Preference value exists', THEME_SECONDARY));
						$response->setStatusCode(400);
					}					
				}else
				if($Pref->insertMap(['config' => $request->getParam('conf'), 'entity' => $request->getVariable('id'), 'value' => $request->getParam('value')]))
					$response->addMessage(new Message('Preference defined', 'Preference defined', THEME_SECONDARY));
				else{
					$response->addMessage(new Message('Preference not updated', 'Preference not updated', THEME_SECONDARY));
					$response->setStatusCode(400);
				}
			}else
			if($request->getParam('password') && Utility::getHash($request->getParam('passworda')) != user('password')){
				$response->addMessage(new Message('Unauthorized operation: Incorrect password', 'Unauthorized operation: Incorrect password', THEME_WARNING));
				$response->setStatusCode(401);
			}else
			if($request->getFile('avatar') && $user->updateFiles()){
				$response->addMessage(new Message('Avatar updated successfully', 'Avatar updated successfully', THEME_INFO));
				$response->addPayload('avatar', Link::getFile($File->getInstance($user->getProperty('avatar'))));
			}else
			if($user->updateMap($request->getParams(), $request->getVariable('id'))){
				$response->addMessage(new Message('Update successful', 'Update successful', THEME_INFO));
			}else{
				$response->addMessage(new Message('Invalid update', 'Invalid update', THEME_INFO));
				$response->setStatusCode(401);
			}
		}
	}
	static function update(){
		global $request;
		global $response;
		global $user;
		global $Pref;
		global $File;
		global $Business;
		global $BankAccount;
		if(SessionController::verifyCSRF()){
			if($request->getParam('bp-name')){
				if($buss = $Business->get('user', $request->getVariable('id'))){
					$business = $Business->getInstance($buss[0]['id']);
					if($business->updateMap(['name' => $request->getParam('bp-name'), 'address' => $request->getParam('bp-address'), 'state' => $request->getParam('bp-state')])){
						$business->setState(STATE_PENDING,$user,' ');
						$response->addMessage(new Message('Business Profile Updated', 'Business Profile Updated', THEME_INFO));
					}else{
						$response->addMessage(new Message('Values already exist', 'Values already exist', THEME_SECONDARY));
						$response->setStatusCode(400);
					}
				}else
				if($business = $Business->insertMap(['name'=>$request->getParam('bp-name'), 'user'=>$request->getVariable('id'), 'address'=>$request->getParam('bp-address'), 'state'=>$request->getParam('bp-state')])){
					$business->setState(STATE_PENDING,$user,' ');
					$response->addMessage(new Message('Business profile defined', 'Business profile defined', THEME_SECONDARY));
				}else{
					$response->addMessage(new Message('Business profile not updated', 'Business profile not updated', THEME_SECONDARY));
					$response->setStatusCode(400);
				}
			}else
			if($request->getFile('id_file') && $request->getParam('bp-id-type') && $user->updateFiles() && $user->updateMap(['id_type' => $request->getParam('bp-id-type')])){
				$File->setState(STATE_PENDING,$user,'',$File->getInstance($user->getProperty('id_file')));
				$response->addMessage(new Message('ID updated successfully', 'ID updated successfully', THEME_INFO));
			}else
			if($request->getParam('acc-number')){
				if($acct = $BankAccount->get('user', $request->getVariable('id'))){
					$account = $BankAccount->getInstance($acct[0]['id']);
					if($account->updateMap(['name' => $request->getParam('acc-name'), 'number' => $request->getParam('acc-number'), 'user' => $request->getVariable('id'), 'bank' => $request->getParam('acc-bank'), 'currency' => $request->getParam('acc-currency')])){
						$account->setState(STATE_PENDING,$user,' ');
						$response->addMessage(new Message('Account details updated', "Account profile updated", THEME_SECONDARY));
					}else{
						$response->addMessage(new Message('Duplicated values detected', "Duplicated values detected", THEME_SECONDARY));
						$response->setStatusCode(400);
					}
				}else
				if($account = $BankAccount->insertMap(['name' => $request->getParam('acc-name'), 'number' => $request->getParam('acc-number'), 'user' => $request->getVariable('id'), 'bank' => $request->getParam('acc-bank'), 'currency' => $request->getParam('acc-currency')])){
					$account->setState(STATE_PENDING,$user,' ');	
					$response->addMessage(new Message('Account details defined', 'Account details defined', THEME_SECONDARY));
				}else{
					$response->addMessage(new Message('Account details not updated', 'Account details not updated', THEME_SECONDARY));
					$response->setStatusCode(400);
				}
			}else
			if($request->getParam('conf')){
				if($prefs = $Pref->getGeneric("WHERE `config`='".$request->getParam('conf')."' AND `entity`='".$request->getVariable('id')."'")){
					if($Pref->updateGeneric("SET `value`='" . $request->getParam('value') . "' WHERE `id`='".$prefs[0]['id']."'")){
						$response->addMessage(new Message('Preference updated', 'Preference updated', THEME_SECONDARY));
					}else{
						$response->addMessage(new Message('Preference value exists', 'Preference value exists', THEME_SECONDARY));
						$response->setStatusCode(400);
					}					
				}else
				if($Pref->insertMap(['config' => $request->getParam('conf'), 'entity' => $request->getVariable('id'), 'value' => $request->getParam('value')]))
					$response->addMessage(new Message('Preference defined', 'Preference defined', THEME_SECONDARY));
				else{
					$response->addMessage(new Message('Preference not updated', 'Preference not updated', THEME_SECONDARY));
					$response->setStatusCode(400);
				}
			}else
			if($request->getParam('password') && Utility::getHash($request->getParam('passworda')) != user('password')){
				$response->addMessage(new Message('Unauthorized operation: Incorrect password', 'Unauthorized operation: Incorrect password', THEME_WARNING));
				$response->setStatusCode(401);
			}else
			if($request->getFile('avatar') && $user->updateFiles()){
				$response->addMessage(new Message('Avatar updated successfully', 'Avatar updated successfully', THEME_INFO));
				$response->addPayload('avatar', Link::getFile($File->getInstance($user->getProperty('avatar'))));
			}else
			if($user->updateMap($request->getParams(), $request->getVariable('id'))){
				$response->addMessage(new Message('Update successful', 'Update successful', THEME_INFO));
			}else{
				$response->addMessage(new Message('Invalid update', 'Invalid update', THEME_INFO));
				$response->setStatusCode(401);
			}
		}
	}
	static function showForgot(){
		global $response;
		$text = ['page.title' => 'Forgot password'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'forgot');
		$response->setPage($page);
	}
	static function showQR(){
		global $response;
		$text = ['page.title' => '2FA'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'qr-code');
		$response->setPage($page);
	}
	static function showVerifyPhone(){
		global $response;
		$text = ['page.title' => 'SMS Verification'];
		$variables = ['text' => $text];
		$page = Page::getInstance($variables, 'sms-verification');
		$response->setPage($page);
	}
	static function init2FA(){
		global $request;
		global $response;
		global $user;
		if(SessionController::verifyCSRF()){
			if($request->getParam('password') && Utility::getHash($request->getParam('password')) === $user->getProperty('password')){
				if(!$user->getProperty('2fa_enabled_at')){
					$g = new GoogleAuthenticator();
					$secret = $g->generateSecret();
					if($user->updateMap(['2fa_secret' => $secret, '2fa_enabled_at' => date_create()->format('Y-m-d H:i:s')])){
						if($url = GoogleQrUrl::generate($user->getProperty('email'), $secret, 'Blockstale', 220)){
							$response->setURL('qr', $url);
							return UserController::showQR();
						}else{
							$response->addMessage(new Message("Couldn't generate QR Code URL", "Couldn't generate QR Code URL", THEME_SECONDARY));
							$response->setStatusCode(501);
							SettingsController::show();
						}
					}else{
						$response->addMessage(new Message('2FA Secret Failed', '2FA Secret Failed', THEME_SECONDARY));
						$response->setStatusCode(501);
						SettingsController::show();
					}
				}
			}else{
				$response->addMessage(new Message("Password doesn't match", "Password doesn't match", THEME_SECONDARY));
				$response->setStatusCode(401);
				SettingsController::show();
			}
		}
	}
	static function initReset2FA(){
		global $request;
		global $response;
		global $user;
		if(SessionController::verifyCSRF()){
			if($request->getParam('password') && Utility::getHash($request->getParam('password')) === $user->getProperty('password')){
				if($request->getParam('otp') && SessionController::check2FACode($user, $request->getParam('otp'))){
					if($user->updateMap(['2fa_reset_code' => Utility::randomToken()])){
						if(Comm::sendNotification($user, NOTIFICATION_2FA_RESET, [MEDIUM_EMAIL])){
							$mask = Utility::maskEmail($user->getProperty('email', 3));
							$response->addMessage(new Message("2FA Reset verification link sent to $mask", "A Reset verification link has been sent to $mask", THEME_INFO));
						}else{
							$response->addMessage(new Message('Error seding verification email', 'Error seding verification email, try again', THEME_WARNING));
							$response->setStatusCode(500);
						}
					}else{
						$response->addMessage(new Message('Could not update reset code', 'Verification link could not be set, please try again', THEME_WARNING));
						$response->setStatusCode(500);
					}
				}else{
					$response->addMessage(new Message("Incorrect or expired OTP", "Incorrect or expired OTP", THEME_SECONDARY));
					$response->setStatusCode(401);
				}
			}else{
				$response->addMessage(new Message("Password does not match", "Password does not match", THEME_SECONDARY));
				$response->setStatusCode(401);
			}
		}
	}
	static function attemptReset2FA(){
		global $request;
		global $response;
		global $User;
		global $user;
		if($user && $request->getParam('e0') && $users = $User->get('2fa_reset_code', $request->getParam('e0'))){
			if($users && $users[0]['id'] === $user->getID() && $user->updateMap(['2fa_reset_code' => null, '2fa_enabled_at' => null, '2fa_secret' => null], null)){
				$response->addMessage(new Message('2FA is deactivated', '2FA reset was successful', THEME_SECONDARY));
			}else{
				$response->addMessage(new Message('Request not completed', 'Request not verified, please verify on the same device', THEME_SECONDARY));
				$response->setStatusCode(401);
			}
		}else{
			$response->addMessage(new Message('Request not verified', 'Request not verified', THEME_DANGER));
			$response->setStatusCode(401);
		}
		SessionController::showLogin();
	}
	static function initVerifyPhone(){
		global $response;
		global $user;
		if(SessionController::verifyCSRF()){
			if($user->updateMap(['phone_verification_code' => rand(1000, 9999)],null)){
				if(Comm::sendNotification($user, NOTIFICATION_PHONE_VERIFICATION, [MEDIUM_SMS])){
					$phone = $user->getProperty('phone');
					$mask = '+' . \substr($phone, 0, 4) . '***' . substr($phone, -1, 3);
					$response->addMessage(new Message("Verification SMS sent to $mask", "A verification SMS has been sent to $mask", THEME_SECONDARY));
					UserController::showVerifyPhone();
				}else{
					$response->addMessage(new Message('Error seding verification SMS', 'Error seding verification SMS, try again', THEME_SECONDARY));
					SettingsController::show();
					$response->setStatusCode(500);
				}
			}else{
				$response->addMessage(new Message('Could not update verification code', 'Verification code could not be updated, please try again', THEME_SECONDARY));
				$response->setStatusCode(500);
				SettingsController::show();
			}
		}
	}
	static function attemptVerifyPhone(){
		global $request;
		global $response;
		global $user;
		if(SessionController::verifyCSRF()){
			if($user->getProperty('phone_verification_code') === $request->getParam('e0')){
				if($user->getProperty('phone_verified_at')){
					$response->addMessage(new Message('Your phone has been verified', 'Your phone has been verified', THEME_SECONDARY));
				}else
					if($user->updateMap(['phone_verified_at' => date_create()->format('Y-m-d H:i:s')])){
					$response->addMessage(new Message('Phone verification was successful', 'Phone verification was successful', THEME_SUCCESS));
				}else{
					$response->addMessage(new Message('Phone could not be verified', 'Phone verification failed, please try again later', THEME_SECONDARY));
				}
			}else{
				$response->addMessage(new Message('Incorrect OTP code', 'Incorrect OTP code', THEME_SECONDARY));
				UserController::showVerifyPhone();
				$response->setStatusCode(401);
				return;
			}
		}
		SettingsController::show();
	}
	static function initVerifyEmail(){
		global $response;
		global $user;
		if(SessionController::verifyCSRF()){
			if($user->updateMap(['email_verification_code' => Utility::randomToken()])){
				if(Comm::sendNotification($user, NOTIFICATION_EMAIL_VERIFICATION, [MEDIUM_EMAIL])){
					$mask = Utility::maskEmail($user->getProperty('email', 3));
					$response->addMessage(new Message("Email verification link sent to $mask", "A verification link has been sent to $mask", THEME_INFO));
				}else{
					$response->addMessage(new Message('Error seding verification email', 'Error seding verification email, try again', THEME_WARNING));
					$response->setStatusCode(500);
				}
			}else{
				$response->addMessage(new Message('Could not update verification link', 'Verification link could not be set, please try again', THEME_WARNING));
				$response->setStatusCode(500);
			}
		}
	}
	static function attemptVerifyEmail(){
		global $request;
		global $response;
		global $User;
		if($request->getParam('e0') && $users = $User->get('email_verification_code', $request->getParam('e0'))){
			if($users && $users[0]['email_verified_at']){
				$response->addMessage(new Message('Your account has been verified', 'Your email has been previously verified', THEME_INFO));
			}else
			if($users && $User->updateMap(['email_verified_at' => date_create()->format('Y-m-d H:i:s')], $users[0]['id'])){
				Event::trigger($User->getInstance($users[0]['id']),ACTION_ACCOUNT_VERIFICATION,QUANTIFIER_ALL,null,null);
				$response->addMessage(new Message('Email verification was successful', 'Email verification was successful', THEME_SUCCESS));
			}else{
				$response->addMessage(new Message('Email could not be verified', 'Email verification failed, please try again later', THEME_WARNING));
			}
		}else{
			$response->addMessage(new Message('Account not found', 'Credentials did not match any records, try again', THEME_DANGER));
			$response->setStatusCode(401);
		}
		SessionController::showLogin();
	}
	static function attemptForgot(){
		global $request;
		global $response;
		global $User;
		if($users = $User->getGeneric("WHERE (email='" . $request->getParam('email') . "' OR phone='" . $request->getParam('email') . "')")){
			if($User->updateMap(['reset_code' => Utility::randomToken()], $users[0]['id'])){
				if(Comm::sendNotification($User->getInstance($users[0]['id']), NOTIFICATION_PASSWORD_RESET, [MEDIUM_EMAIL])){
					$response->addMessage(new Message('Reset link sent to you', 'A verification link has been sent to your email address', THEME_INFO));
				}
			}else{
				$response->addMessage(new Message('Could not update reset link', 'Reset link could not be set, please try again later', THEME_WARNING));
			}
		}else{
			$response->addMessage(new Message('Account not found', 'Credentials did not match any records, try again', THEME_DANGER));
			$response->setStatusCode(401);
		}
		UserController::showForgot();
	}
	static function showReset(){
		global $request;
		global $response;
		global $User;
		if($User->get('reset_code', $request->getParam('e0'))){
			$text = ['page.title' => 'Reset password'];
			$variables = ['text' => $text];
			$page = Page::getInstance($variables, 'reset');
			$response->setPage($page);
		}else{
			$response->addMessage(new Message('Invalid request', 'Reset token invalid, please try again', THEME_WARNING));
			$response->setStatusCode(401);
			UserController::showForgot();
		}
	}
	static function attemptReset(){
		global $request;
		global $response;
		global $User;
		if($request->getParam('e0') && $request->getParam('password') && $users = $User->get('reset_code', $request->getParam('e0'))){
			if($users && $User->updateMap(['reset_code' => '', 'password' => $request->getParam('password')], $users[0]['id'])){
				$response->addMessage(new Message('Password reset was successful', 'Password reset was successful, now sign in', THEME_SUCCESS));
				SessionController::showLogin();
			}else{
				$response->addMessage(new Message('Could not update password', 'Password reset failed, please try again later', THEME_WARNING));
				UserController::showForgot();
			}
		}else{
			$response->addMessage(new Message('Account not found', 'Credentials did not match any records, try again', THEME_DANGER));
			$response->setStatusCode(401);
			UserController::showForgot();
		}
	}
}