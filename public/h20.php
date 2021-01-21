<?php
	require_once '../bootstrap/boot.php';
	use view\Page;
	use helper\Utility;
	use comm\Link;

	$basedir = Link::getBaseDir(false);

	$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use ($basedir){
		$r->addGroup("/$basedir", function (FastRoute\RouteCollector $r) {
			$r->addRoute('GET', "/[welcome]", 'DashboardController@show');
			$r->addRoute('GET', "/login", 'SessionController@showLogin');
			$r->addRoute('POST', "/login", 'SessionController@attemptLogin');

			$r->addRoute('POST', "/users", 'UserController@store');
			$r->addRoute(['PUT','POST'], "/users/{id:\w{13}}", 'UserController@update');

			$r->addRoute('GET', "/logout", 'SessionController@logout');
			$r->addRoute('GET', "/settings", 'SettingsController@show');

			$r->addRoute('GET', "/register", 'SessionController@showRegister');
			$r->addRoute('POST', "/register", 'SessionController@attemptRegister');

			$r->addRoute('PUT', "/verify-email/{id:\w{13}}", 'UserController@initVerifyEmail');
			$r->addRoute('GET', "/verify-email", 'UserController@attemptVerifyEmail');

			$r->addRoute('PUT', "/verify-kyc/{id:\w{13}}", 'UserController@attemptVerifyKYC');

			$r->addRoute('GET', "/kyc", 'DashboardController@showKYC');
			$r->addRoute('GET', "/kycs", 'DashboardController@listKYC');
			$r->addRoute('GET', "/kycs/{id:\w{13}}", 'DashboardController@readKYC');
			$r->addRoute('PUT', "/kycs/{id:\w{13}}", 'DashboardController@updateKYC');
			
			$r->addRoute('GET', "/payouts", 'DashboardController@listPayout');
			$r->addRoute('GET', "/payouts/{id:\w{13}}", 'DashboardController@readPayout');
			$r->addRoute('PUT', "/payouts/{id:\w{13}}", 'DashboardController@updatePayout');

			$r->addRoute('GET', "/verify-phone", 'UserController@initVerifyPhone');
			$r->addRoute('POST', "/verify-phone", 'UserController@attemptVerifyPhone');

			$r->addRoute('POST', "/init-2fa", 'UserController@init2FA');

			$r->addRoute('GET', "/2fa", 'SessionController@showVerify2FA');
			$r->addRoute('POST', "/2fa", 'SessionController@attemptVerify2FA');

			$r->addRoute('PUT', "/reset-2fa/{id:\w{13}}", 'UserController@initReset2FA');
			$r->addRoute('GET', "/reset-2fa", 'UserController@attemptReset2FA');

			$r->addRoute('GET', "/forgot", 'UserController@showForgot');
			$r->addRoute('POST', "/forgot", 'UserController@attemptForgot');

			$r->addRoute('GET', "/reset", 'UserController@showReset');
			$r->addRoute('POST', "/reset", 'UserController@attemptReset');


			$r->addRoute('GET', "/init-recv-crypto", 'BTCController@initReceivePayment');
			$r->addRoute('GET', "/ping-btx/{id:\w{13}}", 'BTCController@pingBTX');
			$r->addRoute('GET', "/hooks-btx[/{id:\w{13}}]", 'BTCController@hookBTX');
			$r->addRoute('GET', "/payout-crypto", 'BTCController@send');
			
			$r->addRoute('GET', "/init-topup-crypto", 'BTCController@initTopup');
			$r->addRoute('GET', "/init-topup-fiat", 'FIATController@initTopup');
			
			$r->addRoute('GET', "/init-recv-fiat", 'FIATController@initReceivePayment');
			$r->addRoute('GET', "/ping-ftx/{id:\w{13,}}", 'FIATController@pingFTX');
			
			$r->addRoute('GET', "/init-issue-vtx", 'VoucherController@initVTX');
			$r->addRoute('GET', "/issue-vtx", 'VoucherController@issueVTX');
			$r->addRoute('GET', "/redeem[/{code:\d{16}}]", 'VoucherController@showRedeem');
			$r->addRoute('POST', "/redeem", 'VoucherController@redeemVTX');
			
			$r->addRoute('GET', "/fwd", 'SessionController@fwd');
			
			$r->addRoute('GET', "/vendor", 'UserController@showVendor');
			$r->addRoute(['PUT','POST'], "/vendor/{id:\w{13}}", 'UserController@updateVendor');
			
			$r->addRoute('GET', "/merchant", 'UserController@showMerchant');
			$r->addRoute(['PUT','POST'], "/merchant/{id:\w{13}}", 'UserController@updateMerchant');
			$r->addRoute('GET', "/withdraw", 'FIATController@initWithdraw');
			
			$r->addRoute('GET', "/transactions", 'UserController@showTransactions');
			$r->addRoute('GET', "/transactions/{id:\w{13}}", 'UserController@showTransaction');
			$r->addRoute('GET', "/contact", 'UserController@showContact');
			$r->addRoute('GET', "/support", 'UserController@showContact');
			$r->addRoute('GET', "/faq", 'UserController@showFAQ');
			$r->addRoute('GET', "/about", 'UserController@showAbout');
			$r->addRoute('GET', "/platform", 'UserController@showPlatform');
			$r->addRoute(['PUT','POST'], "/platform/{id:\w{13}}", 'UserController@updatePlatform');
		});
		/*
			// $r->addRoute('GET', '/users', 'get_all_users_handler');
			// {id} must be a number (\d+)
			// $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
			// The /{title} suffix is optional
			// $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
			$r->addGroup('/admin', function (FastRoute\RouteCollector $r) {
				// $r->addRoute('GET', '/do-something', 'handler');
				// $r->addRoute('GET', '/do-another-thing', 'handler');
				// $r->addRoute('GET', '/do-something-else', 'handler');
			});
			$r->addGroup('/admin', function (FastRoute\RouteCollector $r) {
				// $r->addRoute('GET', '/do-something', 'handler');
				// $r->addRoute('GET', '/do-another-thing', 'handler');
				// $r->addRoute('GET', '/do-something-else', 'handler');
			});

			// Matches /user/42, but not /user/xyz
			// $r->addRoute('GET', '/user/{id:\d+}', 'handler');

			// Matches /user/foobar, but not /user/foo/bar
			// $r->addRoute('GET', '/user/{name}', 'handler');

			// Matches /user/foo/bar as well
			// $r->addRoute('GET', '/user/{name:.+}', 'handler');

			// Furthermore parts of the route enclosed in [...] are considered optional, so that /foo[bar] will match both /foo and /foobar. Optional parts are only supported in a trailing position, not in the middle of a route.

			// This route
			// $r->addRoute('GET', '/user/{id:\d+}[/{name}]', 'handler');
			// Is equivalent to these two routes
			// $r->addRoute('GET', '/user/{id:\d+}', 'handler');
			// $r->addRoute('GET', '/user/{id:\d+}/{name}', 'handler');

			// Multiple nested optional parts are possible as well
			// $r->addRoute('GET', '/user[/{id:\d+}[/{name}]]', 'handler');

			// This route is NOT valid, because optional parts can only occur at the end
			// $r->addRoute('GET', '/user[/{id:\d+}]/{name}', 'handler');

			// $r->get('/get-route', 'get_handler');
			// $r->post('/post-route', 'post_handler');

			// Is equivalent to:

			// $r->addRoute('GET', '/get-route', 'get_handler');
			// $r->addRoute('POST', '/post-route', 'post_handler');

			// route groups

		*/
	});

	// Fetch method and URI from somewhere
	$httpMethod = $_SERVER['REQUEST_METHOD'];
	$uri = $_SERVER['REQUEST_URI'];

	// Strip query string (?foo=bar) and decode URI
	if(false !== $pos = strpos($uri, '?')){
		$uri = substr($uri, 0, $pos);
	}
	$uri = rawurldecode($uri);

	$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
	switch ($routeInfo[0]){
		case FastRoute\Dispatcher::FOUND:
			$handler = $routeInfo[1];
			$vars = $routeInfo[2];
			$request->setVariables($vars);
			$tokens = explode('@',$handler);
			if($tokens[1]??false){
				call_user_func_array('controller\\'.$tokens[0].'::'.$tokens[1],[]);
			}else{
				$tokens[0]::index();
			}
			http_response_code($response->getStatusCode());
			foreach($response->getHeader() as $i=>$header){
				header("$i:$header");
			}
			// Utility::log($response->getHeader());
			if($page = $response->getPage()){
				$page->render();
			}else{
				$ret = [];
				if($response->getMessageBag()){
					$ret = ['message'=>''];
					foreach($response->getMessageBag() as $i=>$message){
						$ret['message'] .= $message->getMessage();
					}
				}
				foreach($response->getPayload() as $i=>$payload){
					$ret[$i] = $payload;
				}
				if($ret)
					echo \json_encode($ret);
			}
		break;
		case FastRoute\Dispatcher::NOT_FOUND:
			Utility::log('NOT_FOUND');
			Utility::log($_SERVER['REQUEST_URI']);
			http_response_code(400);
			Page::show404();
		break;
		case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
			$allowedMethods = $routeInfo[1];
			Utility::log('METHOD_NOT_ALLOWED');
			Utility::log($_SERVER['REQUEST_URI']);
			Utility::log($_SERVER['REQUEST_METHOD']);
			http_response_code(405);
			Page::showErrorPage('405');
		break;
	}