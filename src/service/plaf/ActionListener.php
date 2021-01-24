<?php
namespace plaf;
use comm\Link;
use comm\Comm;
use model\Model;
use contract\EventListener;
use controller\AccountController;
use helper\Utility;

class ActionListener implements EventListener{
	public static function stateChanged(Model $model, string $op, string $prop=null, string $from=null, string $to=null):bool{
		global $User;
		global $Entry;
		global $BTCTransaction;
		global $Transition;
		global $Action;
		global $admin;
		Utility::log("ActionListener::stateChanged: OP:$op, PROP:$prop, fROM:$from, TO:$to ON:".$model->getID()." TYPE:".$model->getType());
		switch($op){
			case OP_C:{
				if($model->instanceof($Action))
					return true;
				if($model->instanceof($Entry)){
					AccountController::doBalance($User->getInstance($model->getProperty('party')),$User->getInstance($model->getProperty('coparty')),$model->getProperty('value_unit'),$model->getProperty('account'));
				}					
				if(!$model->instanceof($Transition))
					$model->setState(STATE_DEFAULT,$admin,'C:Operation');
			}break;
			case ACTION_ZOMBIE_TRANSACTION_ABORTED:{
				if($model->instanceof($BTCTransaction)){
					Comm::sendNotification($model,NOTIFICATION_TRANSACTION_ABORTED,[MEDIUM_EMAIL,MEDIUM_PUSH]);
				}
			}break;
			case ACTION_PAYOUT_REQUEST_UPDATED:{
				if($model->instanceof($BTCTransaction)){
					Comm::sendNotification($model,NOTIFICATION_PAYOUT_REQUESTED,[MEDIUM_EMAIL,MEDIUM_PUSH]);
				}
			}break;
			case ACTION_PAYOUT_REQUESTED:{
				if($model->instanceof($BTCTransaction)){
					Comm::sendNotification($model,NOTIFICATION_PAYOUT_REQUESTED,[MEDIUM_EMAIL,MEDIUM_PUSH]);
					Comm::sendNotification($model,NOTIFICATION_PAYOUT_REQUESTED_ADMIN,[MEDIUM_EMAIL,MEDIUM_PUSH]);
				}
			}break;
			case ACTION_SIGN_IN:{
				if($model->instanceof($User) && $model->getPref(CONFIG_SIGN_IN_ALERT)){
					Comm::sendNotification($model,NOTIFICATION_ACCOUNT_SIGN_IN,[MEDIUM_EMAIL]);
				}
			}break;
			case ACTION_SIGN_UP:{
				if($model->instanceof($User)){
					Comm::sendNotification($model,NOTIFICATION_EMAIL_VERIFICATION,[MEDIUM_EMAIL]);
				}
			}break;
			case ACTION_ACCOUNT_VERIFICATION:{
				if($model->instanceof($User)){
					Comm::sendNotification($model,NOTIFICATION_ACCOUNT_WELCOME,[MEDIUM_EMAIL]);
				}
			}break;
			case ACTION_COMMISSION_REMITTED:{
				if($model->instanceof($Entry)){
					Comm::sendNotification($model,NOTIFICATION_COMMISSION_REMITTED,[MEDIUM_EMAIL]);
				}
			}break;
			case ACTION_MERCHANT_PAID:
			case ACTION_ACCOUNT_TOPUP:{
				if($model->instanceof($Entry) && $User->getInstance($model->getProperty('coparty'))->getPref(CONFIG_NOTIFICATION)){
					Comm::sendNotification($model,NOTIFICATION_CREDIT_ALERT,[MEDIUM_EMAIL,MEDIUM_SMS,MEDIUM_PUSH]);
				}
			}break;
			case ACTION_TRANSFER_COMPLETED:
			case ACTION_PAYOUT_DEDUCTED:
			case ACTION_VOUCHER_ISSUE:{
				if($model->instanceof($Entry) && $User->getInstance($model->getProperty('coparty'))->getPref(CONFIG_NOTIFICATION)){
					Comm::sendNotification($model,NOTIFICATION_DEBIT_ALERT,[MEDIUM_EMAIL,MEDIUM_SMS,MEDIUM_PUSH]);
				}
			}break;
			case ACTION_VIEW_LINK:
			case ACTION_VIEW_NOTIFICATION:{
			}break;
			case ACTION_SHARE_CART:{
			}break;
		}
		return true;
	}
}
?>