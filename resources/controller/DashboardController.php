<?php
	namespace controller;
	use contract\Controller;
use contract\Event;
use view\Page;
	use contract\Message;
use helper\Utility;

class DashboardController extends Controller{
		static function show(){
			global $response;
			global $user;
			if(SessionController::isLoggedIn($user)){
				$text = ['page.title'=>'Dashboard'];
				$variables = ['text'=>$text];
				$page = Page::getInstance($variables,'dashboard');
				$response->setPage($page);
			}else{
				$text = ['page.title'=>'Welcome'];
				$variables = ['text'=>$text];
				$page = Page::getInstance($variables,'welcome');
				$response->setPage($page);
			}
		}
		static function listPayout(){
			global $response;
			$text = ['page.title'=>'Payouts'];
			$variables = ['text'=>$text];
			$page = Page::getInstance($variables,'payout.list');
			$response->setPage($page);
		}
		static function readPayout(){
			global $response;
			$text = ['page.title'=>'Payout Request'];
			$variables = ['text'=>$text];
			$page = Page::getInstance($variables,'payout.read');
			$response->setPage($page);
		}
		static function updatePayout(){
			global $request;
			global $response;
			global $State;
			global $Entry;
			global $admin;
			global $User;
			global $user;
			global $BTCTransaction;
			if(SessionController::verifyCSRF()){
				if($request->getParam('state')){
					$btcTransaction = $BTCTransaction->getInstance($request->getVariable('id'));
					if($btcTransaction->getState()==$request->getParam('state')){
						$response->addMessage(new Message('Invalid update', 'Invalid update', THEME_INFO));
						$response->setStatusCode(422);
					}else{
						$user1 = $User->getInstance($btcTransaction->getProperty('user'));
						$btcTransaction->setState($request->getParam('state'),$user,$request->getParam('comment'));
						if($btcTransaction->getState()==STATE_APPROVED){
							$entry = $Entry->insertMap([
								'name'=>'CR:Payout Deduction',
								'description'=>'CR:Payout Deduction',
								'party'=>$admin->getID(),
								'coparty'=>$user1->getID(),
								'type'=>ACCOUNT_ENTRY_TYPE_OUT,
								'account'=>ACCOUNT_BALANCE,
								'transaction'=>$btcTransaction->getID(),
								'value_number'=>bcsub($btcTransaction->getProperty('cleared_number'),0,2),
								'value_unit'=>$btcTransaction->getProperty('cleared_unit'),
							]);
							$balanceQty = AccountController::doBalance($admin,$user1,$btcTransaction->getProperty('cleared_unit'),ACCOUNT_BALANCE);
							Event::trigger($entry,ACTION_PAYOUT_DEDUCTED);
						}
						
						if($btcTransaction->getState()==STATE_COMPLETED){
							$entry = $Entry->insertMap([
								'name'=>'DR:Withdrawal Commission',
								'description'=>'DR:Withdrawal Commission',
								'party'=>$admin->getID(),
								'coparty'=>$user1->getID(),
								'type'=>ACCOUNT_ENTRY_TYPE_IN,
								'account'=>ACCOUNT_COMMISSION,
								'transaction'=>$btcTransaction->getID(),
								'value_number'=>$btcTransaction->getProperty('fee_number'),
								'value_unit'=>$btcTransaction->getProperty('fee_unit'),
							]);
							$balanceQty = AccountController::doBalance($admin,$user1,$btcTransaction->getProperty('cleared_unit'),ACCOUNT_COMMISSION);
							Event::trigger($entry,ACTION_COMMISSION_REMITTED);
						}
						Event::trigger($btcTransaction,ACTION_PAYOUT_REQUEST_UPDATED);
						$response->addMessage(new Message('Payout Updated', 'Payout Updated', THEME_INFO));
						$response->addPayload('state',$State->getInstance($request->getParam('state'))->getCache());
					}
				}else{
					$response->addMessage(new Message('Invalid update', 'Invalid update', THEME_INFO));
					$response->setStatusCode(401);
				}
			}
		}
		static function showKYC(){
			global $response;
			$text = ['page.title'=>'Welcome'];
			$variables = ['text'=>$text];
			$page = Page::getInstance($variables,'kyc');
			$response->setPage($page);
		}
		/** @return void  */
		static function updateKYC(){
			global $request;
			global $response;
			global $File;
			global $User;
			global $user;
			global $Business;
			global $BankAccount;
			if(SessionController::verifyCSRF()){
				if($request->getParam('ac-comment') && $request->getParam('ac-state') && $request->getParam('ac-id') && $request->getVariable('id')){
					$account = $BankAccount->getInstance($request->getParam('ac-id'));

					$account->setState($request->getParam('ac-state'),$user,$request->getParam('ac-comment'));
					$response->addMessage(new Message('KYC Account Status Updated', 'KYC Account Status Updated', THEME_INFO));
				}else
				if($request->getParam('bp-comment') && $request->getParam('bp-state') && $request->getVariable('id')){
					$business = $Business->getInstance($request->getVariable('id'));

					$business->setState($request->getParam('bp-state'),$user,$request->getParam('bp-comment'));
					$response->addMessage(new Message('KYC Business Profile Updated', 'KYC Business Profile Updated', THEME_INFO));
				}else
				if($request->getParam('id-comment') && $request->getParam('id-state') && $request->getVariable('id')){
					$business = $Business->getInstance($request->getVariable('id'));
					$user1 = $User->getInstance($business->getProperty('user'));

					$File->setState($request->getParam('id-state'),$user,$request->getParam('id-comment'),$File->getInstance($user1->getProperty('id_file')));
					$response->addMessage(new Message('KYC User ID Updated', 'KYC User ID Updated', THEME_INFO));
				}else
				if(false && $request->getParam('comment') && $request->getParam('state') && $request->getVariable('id')){
					$business = $Business->getInstance($request->getVariable('id'));
					$user = $User->getInstance($business->getProperty('user'));
					$accounts = $BankAccount->get('user',$user->getProperty('id'));
					$account = $BankAccount->getInstance($accounts[0]['id']);

					$business->setState($request->getParam('state'),$user,$request->getParam('comment').':Business');
					$account->setState($request->getParam('state'),$user,$request->getParam('comment').':Account');
					$File->setState($request->getParam('state'),$user,$request->getParam('comment').':ID',$File->getInstance($user->getProperty('id_file')));
					$response->addMessage(new Message('KYC Request Updated', 'KYC Rrequest Updated', THEME_INFO));
				}else{
					$response->addMessage(new Message('Invalid update', 'Invalid update', THEME_INFO));
					$response->setStatusCode(401);
				}
			}
		}
		static function listKYC(){
			global $response;
			$text = ['page.title'=>'KYC Approval'];
			$variables = ['text'=>$text];
			$page = Page::getInstance($variables,'kyc-list');
			$response->setPage($page);
		}
		static function readKYC(){
			global $response;
			$text = ['page.title'=>'KYC Approval'];
			$variables = ['text'=>$text];
			$page = Page::getInstance($variables,'kyc-read');
			$response->setPage($page);
		}
	}
?>