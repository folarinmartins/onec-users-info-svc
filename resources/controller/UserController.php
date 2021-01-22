<?php
namespace controller;

use contract\Controller;
use helper\Utility;
use contract\Message;
use comm\Link;

class UserController extends Controller{	
	static function create(){
		global $request;
		global $response;
		global $User;
		global $dbController;

		// $response->setPayload($request->getParams());
		if($newUser = $User->insertMap($request->getParams())){
			$response->addPayload('id',$newUser->getID());
		}else{
			$response->setStatusCode(422);
			$response->addMessage(new Message('Error inserting data',$dbController->getConnection()->error,THEME_DANGER));
		}
	}
	static function getById(){
		global $request;
		global $response;
		global $User;
		
		if($user = $User->getInstance($request->getVariable('id'))){
			$response->setPayload($user->getCache());			
		}else{
			$response->setStatusCode(404);
			$response->addMessage(new Message('Error','Instance not found in database',THEME_DANGER));
		}
	}
	static function delete(){
		global $request;
		global $response;
		global $User;
		
		if($User->delete('id',$request->getVariable('id'))){
			$response->addMessage(new Message('Success','Entry deleted successfully'));			
		}else{
			$response->setStatusCode(404);
			$response->addMessage(new Message('Error','Instance not deleted from database',THEME_DANGER));
		}
	}
	static function getAll(){
		global $response;
		global $User;
		
		if($users = $User->getAll()){
			$response->setPayload($users);			
		}else{
			$response->setStatusCode(404);
			$response->addMessage(new Message('Error','Instance not found in database',THEME_DANGER));
		}
	}
	static function update(){
		global $request;
		global $response;
		global $user;
		global $File;
		if(SessionController::verifyCSRF()){
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
}