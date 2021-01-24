<?php
	// chdir(realpath(__DIR__));
	// echo 'got to h20 '.realpath(__DIR__.'/../src/bootstrap/boot.php');
	require_once realpath(__DIR__.'/../src/bootstrap/boot.php');
	use helper\Utility;
	use view\Page;
	use comm\Link;
	use contract\Message;

	$basedir = Link::getBaseDir(false);

	$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use ($basedir){
		$r->addGroup("/users", function (FastRoute\RouteCollector $r) {
			$r->addRoute('GET', "[/]", 'UserController@getAll');
			$r->addRoute('GET', "/{id:\w{13}}", 'UserController@getById');
			$r->addRoute('POST', "[/]", 'UserController@create');
			$r->addRoute('PUT', "/{id:\w{13}}", 'UserController@update');
			$r->addRoute('DELETE', "/{id:\w{13}}", 'UserController@delete');
		});
	});

	// Fetch method and URI from somewhere
	$httpMethod = $_SERVER['REQUEST_METHOD'];
	$uri = $_SERVER['REQUEST_URI'];

	// Strip query string (?foo=bar) and decode URI
	if(false !== $pos = strpos($uri, '?')){
		$uri = substr($uri, 0, $pos);
	}
	$uri = rawurldecode($uri);
	
	$ret = [
		'response'=>0,
		'status'=>0,
		'message'=>'',
		'data'=>[]
	];
	
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
		break;
		case FastRoute\Dispatcher::NOT_FOUND:
			$response->setStatusCode(400);
			$response->addMessage(new Message('Error','Path not found on this server'));
		break;
		case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
			$response->setStatusCode(405);
			$response->addMessage(new Message('Error','Method not allowed'));
		break;
	}
	
	
	$ret['response'] = $response->getStatusCode();
	$ret['status'] = ($response->getStatusCode()==200?'success':'error');
	foreach($response->getMessageBag() as $i=>$message){
		$ret['message'] .= $message->getMessage();
	}
	foreach($response->getHeader() as $i=>$header){
		header("$i:$header");
	}
	foreach($response->getPayload() as $i=>$payload){
		$ret['data'][$i] = $payload;
	}
	
	if($page = $response->getPage()){
		$page->render();
	}else
	if($ret){
		header('Content-Type: application/json; charset=utf-8');
		echo \json_encode($ret);	
	}
		
	http_response_code($response->getStatusCode());