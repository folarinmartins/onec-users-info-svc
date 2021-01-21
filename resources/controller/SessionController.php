<?php
	namespace controller;
	use contract\Controller;
	use view\Page;
	use helper\Utility;
	use contract\Message;
	use contract\Event;
	use model\Model;
	use comm\Link;
	use Sonata\GoogleAuthenticator\GoogleAuthenticator;

	class SessionController extends Controller{
		static function fwd(){
			global $request;
			global $Link;
			if($request->getParam('link') && ($uri=Link::getURI($request->getParam('link')))){
				Event::trigger($Link->getInstance($uri),ACTION_VIEW_LINK);
				Page::redirect($uri);
			}else{
				Page::show404();
			}
		}
		static function logout(){
			global $response;
			global $User;
			global $user;
			if(SessionController::isLoggedIn($user)){
				$response->addMessage(new Message('You logged out','Current session securely closed',THEME_INFO));
			}
			SessionController::destroy();
			SessionController::showLogin();
			$user = $User;
		}
		static function showVerify2FA(){
			global $response;
			$text = ['page.title'=>'2FA Code'];
			$variables = ['text'=>$text];
			$page = Page::getInstance($variables,'qr-code-verification');
			$response->setPage($page);
		}
		static function attemptVerify2FA(){
			global $user;
			global $request;
			global $response;
			if(SessionController::verifyCSRF()){
				$g = new GoogleAuthenticator();
				if($user && $request && $request->getParam('e0') && SessionController::check2FACode($user,$request->getParam('e0'))){
					SessionController::doLogin();
					if(true || $request->getParam('remember')) {
						SessionController::setOTPCookie($user);
					}
					DashboardController::show();
				}else{
					$response->addMessage(new Message("Invalid OTP code","Invalid OTP code",THEME_SECONDARY));
					$response->setStatusCode(401);
					$response->setURL('callback',route('2fa'));
					SessionController::showVerify2FA();
				}
			}
		}
		static function showQR(){
			global $response;
			$text = ['page.title'=>'2FA'];
			$variables = ['text'=>$text];
			$page = Page::getInstance($variables,'qr-code');
			$response->setPage($page);
		}
		static function showLogin(){
			global $response;
			global $user;
			if(SessionController::isLoggedIn($user)){
				DashboardController::show();
			}else{
				$text = ['page.title'=>'Sign in'];
				$variables = ['text'=>$text];
				$page = Page::getInstance($variables,'login');
				$response->setPage($page);
			}
		}
		static function attemptLogin(){
			global $request;
			global $response;
			global $User;
			global $user;
			if($users = $User->getGeneric("WHERE (email='".$request->getParam('email')."' OR phone='".$request->getParam('email')."') AND password='".Utility::getHash($request->getParam('password'))."'")){
				if(SessionController::init($theUser = $User->getInstance($users[0]['id']))){
					$user = $theUser;
					if(SessionController::hasValidOTPCookie($user)){
						SessionController::doLogin();
						DashboardController::show();
					}else{
						if($user->getProperty('2fa_secret')&&$user->getProperty('2fa_enabled_at')){
							$response->setURL('callback',route('2fa'));
							SessionController::showVerify2FA();
						}else{
							SessionController::doLogin();
							DashboardController::show();
						}
					}
					Event::trigger($user,ACTION_SIGN_IN,QUANTIFIER_ALL,null,null);
					// $response->addHeader('Location',route('/'));
					// header("Location: ".route('/'));
				}
			}else{
				$response->addMessage(new Message('Credentials not found','Credentials did not match any records, try again',THEME_DANGER));
				SessionController::showLogin();
			}
		}
		static function showRegister(){
			global $response;

			$text = ['page.title'=>'Sign out'];
			$variables = ['text'=>$text];
			$page = Page::getInstance($variables,'register');
			$response->setPage($page);
		}
		static function attemptRegister(){
			global $request;
			global $response;
			global $User;
			global $dbController;

			if($newUser = $User->insertMap(array_merge($request->getParams(),['name'=>ucfirst(substr($request->getParam('email'),0,strpos($request->getParam('email'),'@'))),'email_verification_code'=>Utility::randomToken(),'api_key_live_pk'=>'pkl-'.APIController::getAPIKey(),'api_key_live_sk'=>'skl-'.APIController::getAPIKey(),'api_key_test_pk'=>'pkt-'.APIController::getAPIKey(),'api_key_test_sk'=>'skt-'.APIController::getAPIKey(),]))){
				SessionController::init($newUser);
				SessionController::doLogin();
				DashboardController::show();
				Event::trigger($newUser,ACTION_SIGN_UP,QUANTIFIER_ALL,null,null);
				$response->addMessage(new Message('Verification Mail Sent','A verification email has been sent to your email',THEME_SECONDARY));
			}else{
				$response->addMessage(new Message('Error inserting data',$dbController->getConnection()->error,THEME_DANGER));
				SessionController::showRegister();
			}
		}

		public static function check2FACode(Model $user,string $code):bool{
			$g = new GoogleAuthenticator();
			return $g->checkCode($user->getProperty('2fa_secret'),$code);
		}
		public static function verifyCSRF():bool{
			global $request;
			global $response;
			if(!Utility::isWeb())
				return true;
			if($request && $request->getParam('csrf_token') && $request->getParam('csrf_token')==Utility::getSession('csrf_token')[0]??false){
				return true;
			}else{
				$response->addMessage(new Message("Security token not recognized: ","Security token not recognized: ",THEME_DANGER));
				$response->setStatusCode(401);
				return false;
			}
		}
		public static function init(Model $newUser):bool{
			global $user;
			Utility::saveSession('user',[$newUser->getID()]);
			Utility::saveSession('csrf_token',[Utility::getUID()]);
			$user = $newUser;
			return true;
		}
		public static function doLogin():bool{
			session_regenerate_id();
			Utility::saveSession('ua',[$_SERVER['HTTP_USER_AGENT']]);
			Utility::saveSession('loggedin',[true]);
			return true;
		}
		public static function isLoggedIn(Model $user):bool{
			return (Utility::getSession('loggedin')[0]??false)&& (Utility::getSession('user')[0]??false) && Utility::getSession('user')[0]===$user->getID();
		}
		public static function destroy(){
			Utility::deleteSession('user');
			Utility::deleteSession('loggedin');
			Utility::deleteSession('ua');
		}


		static function setOTPCookie(Model $user):bool{
			$time = floor(time() / (3600 * 24)); // get day number
			//about using the user agent: It's easy to fake it, but it increases the barrier for stealing and reusing cookies nevertheless
			// and it doesn't do any harm (except that it's invalid after a browser upgrade, but that may be even intented)
			$cookie = $time.':'.hash_hmac('sha1', $user->getID().':'.$time.':'.$_SERVER['HTTP_USER_AGENT'], $user->getProperty('2fa_secret'));
			setcookie('otp', $cookie, time() + (30 * 24 * 3600), null, null, null, true);
			return true;
		}
		static function hasValidOTPCookie(Model $user):bool{
			// 0 = tomorrow it is invalid
			$daysUntilInvalid = 8;
			$time = (string) floor((time() / (3600 * 24))); // get day number
			if (isset($_COOKIE['otp'])) {
				list($otpday, $hash) = explode(':', $_COOKIE['otp']);

				if ($otpday >= $time - $daysUntilInvalid && $hash == hash_hmac('sha1', $user->getID().':'.$otpday.':'.$_SERVER['HTTP_USER_AGENT'], $user->getProperty('2fa_secret'))) {
					return true;
				}
			}
			return false;
		}
	}
?>