<?php
defined("TABLE_DEL_INCLUSIVE")
	or define("TABLE_DEL_INCLUSIVE", '-1');
defined("TABLE_DEL_EXCLUDE")
	or define("TABLE_DEL_EXCLUSIVE", '0');
defined("TABLE_DEL_EXCLUSIVE")
	or define("TABLE_DEL_DELETED", '1');
	
defined("OP_C")
	or define("OP_C", 1);
defined("OP_R")
	or define("OP_R", 2);
defined("OP_U")
	or define("OP_U", 4);
defined("OP_D")
	or define("OP_D", 8);
defined("OP_L")
	or define("OP_L", 16);
defined("OP_X")
	or define("OP_X", 32);
defined("_OPS")
	or define("_OPS", [OP_C,OP_R,OP_U,OP_D,OP_L,OP_X]);
	
defined("ENV_PRODUCTION")
	or define("ENV_PRODUCTION", '795fbe15');
defined("ENV_STAGE")
	or define("ENV_STAGE", 'f6f2f789');
defined("ENV_DEV")
	or define("ENV_DEV", '8d57aa0c');
defined("_ENVS")
	or define("_ENVS", [ENV_DEV,ENV_STAGE,ENV_PRODUCTION]);

defined("QUANTIFIER_ALL")
	or define("QUANTIFIER_ALL", '*');

defined("CACHE_SESSION")
	or define("CACHE_SESSION", '5f3c8045bad74');
defined("CACHE_REDIS")
	or define("CACHE_REDIS", '5f3c80504de51');
defined("CACHE_MEMCACHED")
	or define("CACHE_MEMCACHED", '5f3c805ac40a5');

defined("COMPANY_PAYSTACK")
	or define("COMPANY_PAYSTACK", '5e89e3c235bcd');
defined("COMPANY_FLUTTERWAVE")
	or define("COMPANY_FLUTTERWAVE", '5ebd296686251');
	
	
define("TYPE_STRING", 'TYPE_STRING');
define("TYPE_BOOLEAN", 'TYPE_BOOLEAN');
define("TYPE_TIMESTAMP", 'TYPE_TIMESTAMP');
define("TYPE_ID", 'TYPE_ID');
define("TYPE_PHONE", 'TYPE_PHONE');
define("TYPE_INT", 'TYPE_INT');
define("TYPE_REAL", 'TYPE_REAL');


define("MODEL_USER", 'user');
define("MODEL_FILE", 'file');
define("MODEL_NOTIFICATION", 'notification');
define("MODEL_STATE", 'state');
define("MODEL_CONFIG", 'config');
define("MODEL_TRANSITION", 'transition');
define("MODEL_PREFS", 'prefs');
define("MODEL_BUSINESS", 'business');
define("BTC_WALLET", 'btc-wallet');
define("BTC_ADDRESS", 'btc-address');
define("BTC_TRANSACTION", 'btc-tx');
define("BTC_VOUCHER", 'btc-voucher');
define("GEO_LGA", 'glga');
define("GEO_STATE", 'gstate');
define("GEO_COUNTRY", 'gcountry');
define("ORG_BANK", 'banks');
define("TX_CURRENCY", 'currency');
define("TX_ACCOUNT", 'account');
define("TX_FX", 'tx-fx');
define("WEB_LINK", 'web-link');
define("TX_TYPE", 'tx-type');
define("ACC_ACCOUNT", 'acc-account');
define("ACC_BALANCE", 'acc-balance');
define("ACC_ENTRY", 'acc-entry');
define("LOG_ACTION", 'log-action');
define("SYS_CRON", 'sys-cron');

define("SATOSHI", 100000000);
define("GAP_LIMIT", 20);
define("GAP_LIMIT_THRESHOLD", 19);

define("MAX_CRON_RUN_TIME", 5);

define("VOUCHER_VALIDITY_DAYS", 9);
define("VOUCHER_MIN_NGN", 1000);
define("NETWORK_FEE_NGN", 300);
define("BYTES_PER_TX", 374);
define("VOUCHER_COMMISSION_PERC", 5);
define("MERCHANT_COMMISSION_PERC", 1.5);
define("VOUCHER_DIGITS", 16);
define("BTC_ADDRESS_RECYCLE_INTERVAL_MINS", 60);
define("BTC_BLOCK_CONFIRMATIONS", 3);
define("VOUCHER_NETWORK_FEES_NUMBER", 150);

define("ACCOUNT_BALANCE", '5fbef58b6c4ad');
define("ACCOUNT_SALES", '5fccfdcd11a77');
define("ACCOUNT_COMMISSION", '5fccfdcd11ab1');
define("ACCOUNT_PAYOUT", '5fccfdcd11abd');


define("CRON_JOB_BTX", '5fd4fec989668');
define("CRON_JOB_ZOMBIE_BTX", '5fd4fec989669');


define("ACCOUNT_TYPE_REVENUE", 'REVENUE');
define("ACCOUNT_TYPE_EXPENSE", 'EXPENSE');
define("ACCOUNT_ENTRY_TYPE_IN", 'IN');
define("ACCOUNT_ENTRY_TYPE_OUT", 'OUT');

define("USER_ADMIN", '5fb3e4a6d0d4a');
define("CURRENCY_NAIRA", '5fa881054ec78');
define("CURRENCY_BTC", '5faaa0d20ad74');
define("CURRENCY_USD", '5fa881054ec79');

define("OPTION_HASH", 'OPTION_HASH');
define("OPTION_REQUIRED", 'OPTION_REQUIRED');
define("OPTION_UNIQUE", 'OPTION_UNIQUE');
define("OPTION_LIST", 'OPTION_LIST');

define("THEME_INFO", 'info');
define("THEME_PRIMARY", 'primary');
define("THEME_WARNING", 'warning');
define("THEME_DANGER", 'danger');
define("THEME_SUCCESS", 'success');
define("THEME_SECONDARY", 'secondary');


define("MEDIUM_SMS", '5f0be05bed71c');
define("MEDIUM_EMAIL", '5f0be05e335cc');
define("MEDIUM_PUSH", '5f0be0606d7f7');

define("STATE_DEFAULT", '5fa69b6727f2d');
define("STATE_PENDING", '5fa69b7311d80');
define("STATE_APPROVED", '5fa69b746a796');
define("STATE_DECLINED", '5fa69b8521b41');
define("STATE_COMPLETED", '5fce82ca23fe6');
define("STATE_ABORTED", '5fd3d84937e62');

define("TX_TYPE_TOPUP", '5fba6ff1a4abf');
define("TX_TYPE_MERCHANT", '5fba6ff267277');
define("TX_TYPE_PAYOUT", '5fba6ff327c8e');
define("TX_TYPE_VOUCHER_PURCHASE", '5fc01668bfde4');
define("TX_TYPE_REVERT", '5fba7050638a9');
define("TX_TYPE_TRANSFER", '5fba6ff327c8f');


define("CONFIG_NOTIFICATION", '5fb9b517344e5');
define("CONFIG_NEWSLETTER", '5fb9b51cc7e3f');
define("CONFIG_SIGN_IN_ALERT", '5fb9b51d700f9');

define("CONFIG_LIVE_FX_RATES", '5fc2eb3b722d1');
define("CONFIG_USER_PAYS_TOP_UP_FEES", '5fc2eb3b7231b');
define("CONFIG_PRIORITY_NETWORK_FEE", '5fc318ff9afce');

define("CONFIG_VENDOR_VOUCHER_LESS_COMMISSION", '5fc2eb3b72334');
define("CONFIG_VENDOR_VOUCHER_LESS_NETWORK_FEE", '5fc2eb3b7234d');

define("CONFIG_PLAF_VOUCHER_COMMISSION", '5fc3903fb77fb');
define("CONFIG_PLAF_VENDORS_COMMISSION", '5fcf890a33d9d');
define("CONFIG_PLAF_ALLOW_FLEXIBLE_VENDORS_COMMISSION", '5fcf890a33dd7');
define("CONFIG_VENDOR_VOUCHER_COMMISSION", '5fcf890a33de1');
define("CONFIG_PLAF_VOUCHER_VALIDITY", '5fc3903fb77ga');
define("CONFIG_PLAF_BTC_ADDRESS_RECYCLE_INTERVAL_MINS", '5fd3d3716ce3b');
define("CONFIG_PLAF_MAX_CRON_RUN_TIME", '5fd500f679183');

define("CONFIG_VOUCHER_DIGITS", '5fc3aac0a29bf');
define("CONFIG_VOUCHER_MINIMUM_NUMBER", '5fc5735a726c4');
define("CONFIG_VOUCHER_MINIMUM_UNIT", '5fc5735a726d0');
define("CONFIG_VOUCHER_NETWORK_FEES_NUMBER", '5fc5849a1aa95');
define("CONFIG_VOUCHER_NETWORK_FEES_UNIT", '5fc5849a1aad4');
define("CONFIG_BLOCKCHAIN_LIVE_NETWORK_FEES", '5fc5849a1aade');
define("CONFIG_BLOCKCHAIN_BYTE_PER_TX", '5fc58971a70c2');
define("CONFIG_BLOCKCHAIN_SATOSHI_PER_BYTE", '5fc58971a70e6');
define("CONFIG_VOUCHER_ALPHANUMERIC", '5fc3aac0a29f1');
define("CONFIG_MERCHANT_LIVE", '5fc76cf5523bd');
define("CONFIG_MERCHANTS_SALE_MINIMUM_NUMBER", '5fc7f0e3d0f94');
define("CONFIG_MERCHANTS_SALE_MINIMUM_UNIT", '5fc7f0e3d0fbf');
define("CONFIG_MERCHANTS_SALE_COMMISSION", '5fc7f0e3d0fdb');
define("CONFIG_MERCHANTS_CHARGE_COMMISSION_ON_SALE", '5fc7f0e3d0feb');

define("CONFIG_DEFAULT_CURRENCY", '5fc59ea93044a');
define("CONFIG_DEFAULT_CURRENCY_FLEXIBLE", '5fc59ea930487');
define("CONFIG_CENTRAL_CRYPTO_WALLET", '5fc7ad49156a5');
define("CONFIG_PLAF_BLOCK_CONFIRMATIONS", '5fc83b4c34d88');
define("CONFIG_PAYOUT_FEE_NUMBER", '5fcac4538bfc7');
define("CONFIG_PAYOUT_FEE_UNIT", '5fcac4538c006');
define("CONFIG_CHARGE_WITHDRAWAL_FEE_ON_BALANCE", '5fcbf88a4a08f');



// ('5fcf890a33d9d', 'Vendor Commission', 'Vendor Commission', NULL, '0', NULL, current_timestamp(), current_timestamp()), 
// ('5fcf890a33dd7', 'Allow Flexible Vendor Commission', 'Allow Flexible Vendor Commission', NULL, '0', NULL, current_timestamp(), current_timestamp()), 
// ('5fcf890a33de1', 'Vendor\'s Commission', 'Vendor\'s Commission', NULL, '0', NULL, current_timestamp(), current_timestamp());


define("NOTIFICATION_PASSWORD_RESET", 'NOTIFICATION_PASSWORD_RESET');
define("NOTIFICATION_ACCOUNT_SIGN_IN", 'NOTIFICATION_ACCOUNT_SIGN_IN');
define("NOTIFICATION_ACCOUNT_WELCOME", 'NOTIFICATION_ACCOUNT_WELCOME');
define("NOTIFICATION_COMMISSION_REMITTED", 'NOTIFICATION_COMMISSION_REMITTED');
define("NOTIFICATION_ACCOUNT_CODE", 'NOTIFICATION_ACCOUNT_CODE');
define("NOTIFICATION_ACCOUNT_MESSAGE", 'NOTIFICATION_ACCOUNT_MESSAGE');	
define("NOTIFICATION_EMAIL_VERIFICATION", 'NOTIFICATION_EMAIL_VERIFICATION');
define("NOTIFICATION_2FA_RESET", 'NOTIFICATION_2FA_RESET');
define("NOTIFICATION_PHONE_VERIFICATION", 'NOTIFICATION_PHONE_VERIFICATION');
define("NOTIFICATION_ACCOUNT_VERIFICATION", 'NOTIFICATION_ACCOUNT_VERIFICATION');
define("NOTIFICATION_ANNOUNCEMENT", 'NOTIFICATION_ANNOUNCEMENT');	
define("NOTIFICATION_SERVICE_ANNOUNCEMENT", 'NOTIFICATION_SERVICE_ANNOUNCEMENT');
define("NOTIFICATION_SERVICE_NOTIFICATION", 'NOTIFICATION_SERVICE_NOTIFICATION');
define("NOTIFICATION_SERVICE_UPDATE", 'NOTIFICATION_SERVICE_UPDATE');
define("NOTIFICATION_SERVICE_REVIEW", 'NOTIFICATION_SERVICE_REVIEW');	
define("NOTIFICATION_STORE_WELCOME", 'NOTIFICATION_STORE_WELCOME');
define("NOTIFICATION_PAYMENT_CONFIRMATION", 'NOTIFICATION_PAYMENT_CONFIRMATION');
define("NOTIFICATION_TRANSACTION_ALERT", 'NOTIFICATION_TRANSACTION_ALERT');
define("NOTIFICATION_CREDIT_ALERT", 'NOTIFICATION_CREDIT_ALERT');
define("NOTIFICATION_DEBIT_ALERT", 'NOTIFICATION_DEBIT_ALERT');
define("NOTIFICATION_ORDER_CONFIRMATION", 'NOTIFICATION_ORDER_CONFIRMATION');
define("NOTIFICATION_DOC_UPDATE", 'NOTIFICATION_DOC_UPDATE');
define("NOTIFICATION_BID_CONFIRMED", 'NOTIFICATION_BID_CONFIRMED');
define("NOTIFICATION_BID_DECLINED", 'NOTIFICATION_BID_DECLINED');
define("NOTIFICATION_BID_OFFER_ACCEPTED", 'NOTIFICATION_BID_OFFER_ACCEPTED');
define("NOTIFICATION_ISSUE_RECEIVED", 'NOTIFICATION_ISSUE_RECEIVED');
define("NOTIFICATION_ISSUE_RESOLVED", 'NOTIFICATION_ISSUE_RESOLVED');
define("NOTIFICATION_SHARE_CART", 'NOTIFICATION_SHARE_CART');
define("NOTIFICATION_PAYOUT_REQUESTED_ADMIN", 'NOTIFICATION_PAYOUT_REQUESTED_ADMIN');
define("NOTIFICATION_PAYOUT_REQUESTED", 'NOTIFICATION_PAYOUT_REQUESTED');
define("NOTIFICATION_TRANSACTION_ABORTED", 'NOTIFICATION_TRANSACTION_ABORTED');


define("ACTION_SIGN_IN", 'ACTION_SIGN_IN');
define("ACTION_ACCOUNT_VERIFICATION", 'ACTION_ACCOUNT_VERIFICATION');
define("ACTION_SIGN_UP", 'ACTION_SIGN_UP');
define("ACTION_SIGN_OUT", 'ACTION_SIGN_OUT');
define("ACTION_SHARE_CART", 'ACTION_SHARE_CART');
define("ACTION_VIEW_NOTIFICATION", 'ACTION_VIEW_NOTIFICATION');
define("ACTION_VIEW_LINK", 'ACTION_VIEW_LINK');
define("ACTION_ACCOUNT_TOPUP", 'ACTION_ACCOUNT_TOPUP');
define("ACTION_VOUCHER_ISSUE", 'ACTION_VOUCHER_ISSUE');
define("ACTION_MERCHANT_PAID", 'ACTION_MERCHANT_PAID');
define("ACTION_ZOMBIE_TRANSACTION_ABORTED", 'ACTION_ZOMBIE_TRANSACTION_ABORTED');
define("ACTION_PAYOUT_REQUESTED", 'ACTION_PAYOUT_REQUESTED');
define("ACTION_PAYOUT_REQUEST_UPDATED", 'ACTION_PAYOUT_REQUEST_UPDATED');
define("ACTION_PAYOUT_DEDUCTED", 'ACTION_PAYOUT_DEDUCTED');
define("ACTION_COMMISSION_REMITTED", 'ACTION_COMMISSION_REMITTED');
define("ACTION_TRANSFER_COMPLETED", 'ACTION_TRANSFER_COMPLETED');
	

define("OPERATION_C", '5e3f1a4fbf037');
define("OPERATION_R", '5e3f1a4ff1ced');
define("OPERATION_U", '5e3f1a5013235');
define("OPERATION_D", '5e3f1a5033d92');
define("OPERATION_L", '5e3f1a504a414');
	
?>