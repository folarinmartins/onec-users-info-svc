<?php
	namespace controller;
	use contract\Controller;
	use helper\Utility;
	use view\Page;

	class SettingsController extends Controller{
		static function show(){
			global $response;
			$text = ['page.title'=>'Settings'];
			$variables = ['text'=>$text];
			$page = Page::getInstance($variables,'settings');
			$response->setPage($page);
		}
	}
?>