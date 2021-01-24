<?php
include_once 'refs.php';

define("APP_ROOT", realpath('../../'));
define("RESOURCE_PATH", realpath(APP_ROOT.'/src'));
define("BOOTSTRAP_PATH", realpath(RESOURCE_PATH.'/bootstrap'));
define("CONFIG_PATH", realpath(RESOURCE_PATH.'/config'));
define("STORAGE_PATH", realpath(APP_ROOT.'/storage'));
define("TEST_PATH", realpath(APP_ROOT.'/tests'));
define("LIBS_PATH", realpath(APP_ROOT.'/vendor'));
define("WEB_ROOT", realpath(APP_ROOT.'/public'));

define("TEMPLATES_PATH", realpath(RESOURCE_PATH.'/ui'));


define("LOG_PATH", realpath(STORAGE_PATH . '/logs'));
define("SESSION_PATH", STORAGE_PATH.'/session');
define("UPLOAD_PATH", STORAGE_PATH.'/public/uploads');
define("UPLOAD_PATH_SYM", '/uploads');

$config = array(
	"version" => '2.1.9',
	"build" => '5e39492f',
	'company'=>[
		'name'=>'OneChurch',
		'phone'=>'+234-(806)-628-8220',
		'email'=>'info@onech',
		'address'=>'1 OneChurch Place, 104233, LOS, NG',
		'url'=>'www.onech',
		'slogan'=>'bringing cryptocurrency to you',
		'icon'=>'logo.png'
	],
    "db" => array(
        ENV_PRODUCTION => array(
            "dbname" => "one_user_info",
            "username" => "mysql_dba",
            "password" => "7~6%{ipu[n1w",
            "host" => "localhost"
        ),
        ENV_DEV => array(
            "dbname" => "one_user_info",
            "username" => "mysql_dba",
            "password" => "7~6%{ipu[n1w",
            "host" => "localhost"
		),
        ENV_STAGE => array(
            "dbname" => "one_user_info",
            "username" => "mysql_dba",
            "password" => "7~6%{ipu[n1w",
            "host" => "localhost"
		),
		'spec' => [
			'_shared' => [
				'id'=>[
					'options'=>[OPTION_UNIQUE],
					'type'=>TYPE_ID
				],
				'name'=>[
					'options'=>[],
					'type'=>TYPE_STRING
				],
				'description'=>[
					'options'=>[],
					'type'=>TYPE_STRING
				],
				'created_at'=>[
					'options'=>[],
					'type'=>TYPE_TIMESTAMP
				],
				'updated_at'=>[
					'options'=>[],
					'type'=>TYPE_TIMESTAMP
				],
				'del'=>[
					'options'=>[],
					'type'=>TYPE_BOOLEAN
				],
				'dkey'=>[
					'options'=>[],
					'type'=>TYPE_ID
				],
			],
			MODEL_NOTIFICATION => [
				'table'=>'mod_notifications',
				'caption'=>'name',
				'properties'=>[
					'medium'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
					'type'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
					'user'=>[
						'options'=>[],
						'type'=>TYPE_ID
					],
					'html'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
					'text'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
				]
			],
			MODEL_CONFIG => [
				'table'=>'mod_configs',
				'caption'=>'name',
				'properties'=>[
					'type'=>[
						'options'=>[],
						'type'=>TYPE_ID
					],
				]
			],
			SYS_CRON => [
				'table'=>'sys_cron',
				'caption'=>'name',
				'properties'=>[
					'runner'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
					'locked_at'=>[
						'options'=>[],
						'type'=>TYPE_TIMESTAMP
					],
					'released_at'=>[
						'options'=>[],
						'type'=>TYPE_TIMESTAMP
					],
				]
			],
			TX_TYPE => [
				'table'=>'tx_types',
				'caption'=>'name',
				'properties'=>[]
			],
			TX_FX => [
				'table'=>'tx_fxs',
				'caption'=>'name',
				'properties'=>[
					'base'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'quote'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'buy'=>[
						'options'=>[],
						'type'=>TYPE_REAL
					],
					'sell'=>[
						'options'=>[],
						'type'=>TYPE_REAL
					],
				]
			],
			WEB_LINK => [
				'table'=>'web_links',
				'caption'=>'name',
				'properties'=>[
					'uri'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
				]
			],
			LOG_ACTION => [
				'table'=>'log_actions',
				'caption'=>'name',
				'properties'=>[
					'user'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[MODEL_USER],
					],
				]
			],
			ACC_ACCOUNT => [
				'table'=>'acc_accounts',
				'caption'=>'name',
				'properties'=>[
					'type'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
				]
			],
			ACC_ENTRY => [
				'table'=>'acc_entries',
				'caption'=>'name',
				'properties'=>[
					'party'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[MODEL_USER],
					],
					'coparty'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[MODEL_USER],
					],
					'type'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
					'account'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[ACC_ACCOUNT],
					],
					'transaction'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[BTC_TRANSACTION],
					],
					'value_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL
					],
					'value_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'consumed_at'=>[
						'options'=>[],
						'type'=>TYPE_TIMESTAMP,
					],
				]
			],
			ACC_BALANCE => [
				'table'=>'acc_balances',
				'caption'=>'name',
				'properties'=>[
					'party'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[MODEL_USER],
					],
					'coparty'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[MODEL_USER],
					],
					'account'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[ACC_ACCOUNT],
					],
					'value_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL
					],
					'value_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
				]
			],
			MODEL_STATE => [
				'table'=>'mod_states',
				'caption'=>'name',
				'properties'=>[
					'pre'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
					'post'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
					'gerund'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
					'theme'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
				]
			],
			MODEL_FILE => [
				'table'=>'mod_files',
				'caption'=>'name',
				'properties'=>[
					'type'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
					'size'=>[
						'options'=>[],
						'type'=>TYPE_INT
					],
					'ext'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					]
				]
			],
			MODEL_USER => [
				'table'=>'mod_users',
				'caption'=>'name',
				'properties'=>[
					'phone'=>[
						'options'=>[OPTION_UNIQUE],
						'type'=>TYPE_PHONE
					],
					'email'=>[
						'options'=>[OPTION_UNIQUE],
						'type'=>TYPE_STRING
					],
					'password'=>[
						'options'=>[OPTION_HASH],
						'type'=>TYPE_STRING
					]
				]
			],
			MODEL_TRANSITION => [
				'table'=>'mod_transitions',
				'caption'=>'name',
				'properties'=>[
					'stateful'=>[
						'options'=>[],
						'fk'=>[QUANTIFIER_ALL],
						'type'=>TYPE_ID
					],
					'user'=>[
						'options'=>[],
						'fk'=>[MODEL_USER],
						'type'=>TYPE_ID
					],
					'state0'=>[
						'options'=>[],
						'fk'=>[MODEL_STATE],
						'type'=>TYPE_ID
					],
					'staten'=>[
						'options'=>[],
						'fk'=>[MODEL_STATE],
						'type'=>TYPE_ID
					],
				]
			],
			MODEL_PREFS => [
				'table'=>'mod_prefs',
				'caption'=>'name',
				'properties'=>[
					'config'=>[
						'options'=>[],
						'fk'=>[MODEL_CONFIG],
						'type'=>TYPE_ID
					],
					'entity'=>[
						'options'=>[],
						'fk'=>[MODEL_USER],
						'type'=>TYPE_ID
					],
					'value'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					]
				]
			],
			MODEL_BUSINESS => [
				'table'=>'mod_businesses',
				'caption'=>'name',
				'properties'=>[
					'address'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
					'user'=>[
						'options'=>[],
						'fk'=>[MODEL_USER],
						'type'=>TYPE_ID
					],
					'state'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
						'fk'=>[GEO_STATE],
					]
				]
			],
			BTC_WALLET => [
				'table'=>'btc_wallets',
				'caption'=>'name',
				'properties'=>[
					'xpriv'=>[
						'options'=>[OPTION_UNIQUE],
						'type'=>TYPE_STRING
					],
					'xpub'=>[
						'options'=>[OPTION_UNIQUE],
						'type'=>TYPE_STRING
					],
					'receive_account'=>[
						'options'=>[OPTION_UNIQUE],
						'type'=>TYPE_STRING
					],
					'change_account'=>[
						'options'=>[OPTION_UNIQUE],
						'type'=>TYPE_STRING
					],
					'indexx'=>[
						'options'=>[],
						'type'=>TYPE_INT
					],
					'archived'=>[
						'options'=>[],
						'type'=>TYPE_BOOLEAN
					],
					'provider'=>[
						'options'=>[],
						'fk'=>['ORG_COMPANY'],
						'type'=>TYPE_ID
					],
					'user'=>[
						'options'=>[],
						'fk'=>[MODEL_USER],
						'type'=>TYPE_ID
					],
					'balance_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'balance_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					]
				]
			],
			BTC_ADDRESS => [
				'table'=>'btc_addresses',
				'caption'=>'name',
				'properties'=>[
					'wallet'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[BTC_WALLET],
					],
					'address'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
					'indexx'=>[
						'options'=>[],
						'type'=>TYPE_INT,
					],
					'balance_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'balance_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					]
				]
			],
			BTC_VOUCHER => [
				'table'=>'btc_vouchers',
				'caption'=>'name',
				'properties'=>[
					'code'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
					'user'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[MODEL_USER],
					],
					'validity_days'=>[
						'options'=>[],
						'type'=>TYPE_INT,
					],
					'value0_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'value0_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'value_fiat_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'value_fiat_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'value_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'value_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'commission_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'commission_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'commission_admin_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'commission_admin_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'commission_vendor_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'commission_vendor_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'network_fee_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'network_fee_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'cleared_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'cleared_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'rate_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'rate_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
				]
			],
			BTC_TRANSACTION => [
				'table'=>'btc_transactions',
				'caption'=>'name',
				'properties'=>[
					'hash'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
					'user'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[MODEL_USER],
					],
					'type'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_TYPE],
					],
					'tto'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
					'address'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[BTC_ADDRESS],
					],
					'confs'=>[
						'options'=>[],
						'type'=>TYPE_INT,
					],
					'value_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'value_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'fee_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'fee_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'cleared_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'cleared_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'rate_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'rate_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'balance0_number'=>[
						'options'=>[],
						'type'=>TYPE_REAL,
					],
					'balance0_unit'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
				]
			],
			GEO_LGA => [
				'table'=>'geo_lgas',
				'caption'=>'name',
				'properties'=>[
					'country'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[GEO_STATE],
					]
				]
			],
			GEO_STATE => [
				'table'=>'geo_states',
				'caption'=>'name',
				'properties'=>[
					'country'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[GEO_COUNTRY],
					]
				]
			],
			GEO_COUNTRY => [
				'table'=>'geo_countries',
				'caption'=>'name',
				'properties'=>[]
			],
			ORG_BANK => [
				'table'=>'org_banks',
				'caption'=>'name',
				'properties'=>[
					'code'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
					'slug'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
					'longcode'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
					'gateway'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
				]
			],
			TX_CURRENCY => [
				'table'=>'tx_currencies',
				'caption'=>'name',
				'properties'=>[
					'crypto'=>[
						'options'=>[],
						'type'=>TYPE_BOOLEAN
					],
					'symbol'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
					'symbol_char'=>[
						'options'=>[],
						'type'=>TYPE_STRING
					],
				]
			],
			TX_ACCOUNT => [
				'table'=>'tx_accounts',
				'caption'=>'name',
				'properties'=>[
					'user'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[MODEL_USER],
					],
					'number'=>[
						'options'=>[],
						'type'=>TYPE_STRING,
					],
					'currency'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[TX_CURRENCY],
					],
					'bank'=>[
						'options'=>[],
						'type'=>TYPE_ID,
						'fk'=>[ORG_BANK],
					],
				]
			],
		]
    ),
    "credential" => array(
        'paystack' => array(
            ENV_PRODUCTION => array(
				"sk" => "sk_live_ce7293e08a210a09149e85160e786b2eb97eef30",
				"pk" => "pk_live_f4cd81411b612e0ac159883777334bbb724c88ff"
			),
            ENV_DEV => array(
				"sk" => "sk_test_9fc7b0ca7555df2e79508254d0be277faf34bb2b",
				"pk" => "pk_test_bfebd9f285860a27e83bfb8e4f8b8a2c6d480166"
			),
            ENV_STAGE => array(
				"sk" => "sk_test_9fc7b0ca7555df2e79508254d0be277faf34bb2b",
				"pk" => "pk_test_bfebd9f285860a27e83bfb8e4f8b8a2c6d480166"
			)
		),
        COMPANY_FLUTTERWAVE => array(
            ENV_PRODUCTION => array(
				"sk" => "FLWSECK-db3ccd11d5f07df935626ffaafd8fe5e-X",
				"pk" => "FLWPUBK-988338199b99dc6beaf62018c0482158-X",
				"ek" => "db3ccd11d5f0fd2e35eb8089",
				"hash" => "2c5082e2-7435-45de-82db-07eee3bc4640"
			),
            ENV_DEV => array(
				"sk" => "FLWSECK_TEST-e21c05117c7fce8bc8af432bc72aea6a-X",
				"pk" => "FLWPUBK_TEST-6fc29046b9ef73685a375048206cb79e-X",
				"ek" => "FLWSECK_TESTbb8c6ac9d00a",
				"hash" => "2c5082e2-7435-45de-82db-07eee3bc4640"
			),
            ENV_STAGE => array(
				"sk" => "FLWSECK_TEST-e21c05117c7fce8bc8af432bc72aea6a-X",
				"pk" => "FLWPUBK_TEST-6fc29046b9ef73685a375048206cb79e-X",
				"ek" => "FLWSECK_TESTbb8c6ac9d00a",
				"hash" => "2c5082e2-7435-45de-82db-07eee3bc4640"
			)
		),
        "smschamp" => [
            ENV_PRODUCTION => [
				"ek" => "038ab64648f242625d1b269289345d79" //https://api.sendchamp.com/api/v1/sms/send
			],
            ENV_DEV => [
				"ek" => 'sendchamp_live_$2y$10$WX1v/YBavujmNGfAX4OGS.vkeQ9j0EYX9mD8dY/6/XScRtdd.sEPm' //https://sandbox-api.sendchamp.com/api/v1/sms/send
			],
            ENV_STAGE => [
				"ek" => 'sendchamp_live_$2y$10$WX1v/YBavujmNGfAX4OGS.vkeQ9j0EYX9mD8dY/6/XScRtdd.sEPm' //https://sandbox-api.sendchamp.com/api/v1/sms/send
			]
		],
        "ipinfo" => [
            ENV_PRODUCTION => [
				"api" => "845afe241b0def" ,//https://ipinfo.io?token=845afe241b0def
			],
            ENV_DEV => [
				"api" => "845afe241b0def" //
			],
            ENV_STAGE => [
				"api" => "845afe241b0def" //
			]
		],
        "ipstack" => [
            ENV_PRODUCTION => [
				"api" => "038ab64648f242625d1b269289345d79" //http://api.ipstack.com/197.211.61.143?access_key=038ab64648f242625d1b269289345d79
			],
            ENV_DEV => [
				"api" => "038ab64648f242625d1b269289345d79" //http://api.ipstack.com/197.211.61.143?access_key=038ab64648f242625d1b269289345d79
			],
            ENV_STAGE => [
				"api" => "038ab64648f242625d1b269289345d79" //http://api.ipstack.com/197.211.61.143?access_key=038ab64648f242625d1b269289345d79
			]
		],
        "at" => [
            ENV_PRODUCTION => [
				"api" => "249bd03e73718afb70062bd80e86d1640419f4c10bddf7f4e08c655efe7471bd",
				"url" => "https://api.africastalking.com/version1/messaging",
				"shortcode" => "20050",
				"alphanumeric" => "OneChurch",
			],
            ENV_DEV => [
				"api" => "249bd03e73718afb70062bd80e86d1640419f4c10bddf7f4e08c655efe7471bd",
				"shortcode" => "20050",
				"url" => "https://api.africastalking.com/version1/messaging",
				// "url" => "https://api.sandbox.africastalking.com/version1/messaging",
				"alphanumeric" => "OneChurch",
			],
            ENV_STAGE => [
				"api" => "249bd03e73718afb70062bd80e86d1640419f4c10bddf7f4e08c655efe7471bd",
				"shortcode" => "20050",
				"url" => "https://api.africastalking.com/version1/messaging",
				// "url" => "https://api.sandbox.africastalking.com/version1/messaging",
				"alphanumeric" => "OneChurch",
			]
		],
        "blockchain" => [
            ENV_PRODUCTION => [
				"xpub" => "xpub6DSCCSoVnBkJy7XdzHbkPpsz2GgTEprZfj3yuJaqurPDWNhbvL37qctHbXvYK14Qmfi81DvTfSdjpJpy5k1xqerT2H6sfF8oyUN7LH6tRAr",
				"guid" => "e6ad340f-2f92-4724-8b55-d1d0ef14aba6",
				"password" => "y84v4KJcABJqRuR",
				"api" => "1770d5d9-bcea-4d28-ad21-6cbd5be018a8",
				"v2-api" => "1770d5d9-bcea-4d28-ad21-6cbd5be018a8",
				// "accounts" => ['5fb30b045045c'],
				// "addresses" => ['5fb30ab1d490f','5fb30af885c7f'],
			],
            ENV_DEV => [
				"xpub" => "xpub6DSCCSoVnBkK1puxHW7EHCNxp2zqewEoQ3mjQHaFu9k84aevd2JVUdp71MpFB8Dt5d7qcmyeFJZz2QD1Xvc5heuZQK3qxtobvRFdhpaRwyJ",
				"guid" => "e6ad340f-2f92-4724-8b55-d1d0ef14aba6",
				"password" => "y84v4KJcABJqRuR",
				"api" => "1770d5d9-bcea-4d28-ad21-6cbd5be018a8",
				"v2-api" => "1770d5d9-bcea-4d28-ad21-6cbd5be018a8",
				// "accounts" => ['5fb30b045045c'],
				// "addresses" => ['5fb30ab1d490f','5fb30af885c7f'],
			],
            ENV_STAGE => [
				"xpub" => "xpub6DSCCSoVnBkK1puxHW7EHCNxp2zqewEoQ3mjQHaFu9k84aevd2JVUdp71MpFB8Dt5d7qcmyeFJZz2QD1Xvc5heuZQK3qxtobvRFdhpaRwyJ",
				"guid" => "e6ad340f-2f92-4724-8b55-d1d0ef14aba6",
				"password" => "y84v4KJcABJqRuR",
				"api" => "1770d5d9-bcea-4d28-ad21-6cbd5be018a8",
				"v2-api" => "1770d5d9-bcea-4d28-ad21-6cbd5be018a8",
				// "accounts" => ['5fb30b045045c'],
				// "addresses" => ['5fb30ab1d490f','5fb30af885c7f'],
			]
		],
    ),
    "url" => array(
		'host' =>[
			ENV_PRODUCTION => "users.onec.com",
			ENV_DEV => 'onech.localhost',
			ENV_STAGE => "users.onec.com",	
		],
		'basedir' =>[
			ENV_PRODUCTION => "",
			ENV_DEV => '',
			ENV_STAGE => "",	
		],
		ENV_PRODUCTION => "",
		ENV_DEV => '',
		ENV_STAGE => "",
		"/" => "",
		"home" => "",
		"login" => "login",
		"register" => "register",
		"logout" => "logout",
		"verify-kyc" => "verify-kyc",
		"kycs" => "kycs",
		"verify-email" => "verify-email",
		"verify-phone" => "verify-phone",
		"init-2fa" => "init-2fa",
		"2fa" => "2fa",
		"reset-2fa" => "reset-2fa",
		"reset" => "reset",
		"forgot" => "forgot",
		"settings" => "settings",
		"pref" => "pref",
		"users" => "users",
		"comm" => "comm",
		"activate" => "activate",
		"support" => "support",
		"dashboard" => "index",
		"image" => "assets/img",
		"image.avatar" => "assets/img/avatar.png",
		"dummy.image" => "assets/img/avatar.png",
		"vendor" => "vendor",
		"merchant" => "merchant",



		"redeem" => "redeem",
		"transactions" => "transactions",
		"about" => "about",
		"platform" => "platform",
		"contact" => "contact",
		"faq" => "faq",
		"withdraw" => "withdraw",
		"payouts" => "payouts",
		"init-issue-vtx" => "init-issue-vtx",
		"issue-vtx" => "issue-vtx",
		"init-receive-payment" => "init-receive-payment",
		"init-recv-fiat" => "init-recv-fiat",
		"init-recv-crypto" => "init-recv-crypto",
		"ping-btx" => "ping-btx",
		"ping-ftx" => "ping-ftx",
		"hooks-btx" => "hooks-btx",
		"payout-crypto" => "payout-crypto",
		"init-topup-crypto" => "init-topup-crypto",
		"init-topup-fiat" => "init-topup-fiat",
		"fwd" => "fwd",
		// "dummy.image" => "assets/img/sample/avatar/avatar1.jpg"
	),
	"email" => array(
		"email.admin" => "admin@onech.com",
		"email.admin.password" => "7~6%{ipu[n1w"
	),
    "paths" => array(
        "resources" => RESOURCE_PATH,
        "images" => array(
            "content" => $_SERVER["DOCUMENT_ROOT"] . "/images/content",
            "layout" => $_SERVER["DOCUMENT_ROOT"] . "/images/layout"
		),
        "log" => array(
            "migrate" => LOG_PATH . "/migrate.log",
            "event" => LOG_PATH . "/event.log"
        )
	),
	"env" => array(
		"env" => ENV_DEV,
		"password.default" => "mypassword",
		"op" => array(
			"c"=>OP_C,
			"r"=>OP_R,
			"u"=>OP_U,
			"d"=>OP_D,
			"l"=>OP_L
		),
		'log' => FALSE,
		'log.actions' => false,
		'log.migration' => false,
		'tx-fee' => 0.01,
		'cache' => FALSE,
		'cache.policy' => CACHE_REDIS,
		'cache.policy.session' => CACHE_SESSION,
		'activation.required' => !TRUE,
		'pay.merchant' => COMPANY_PAYSTACK,
		'time.point.format' => "m/d/Y H:i:s",
		'regex.decimal' => "\d+([.]\d+)?",
		"regex.username" => "^(['-.]?[a-zA-Z]+['-.]?[a-zA-z]*)([ ]+['-.]?[a-zA-Z]+['-.]?[a-zA-z]*)*",
		"regex.password" => "\w{6,25}",
		'regex.email' =>  "^(([\w]|[^@]+@\w+[.]\w{2,4}){1}){1}$",
		'regex.iserid' =>   "^(([\w]|[^@]+@\w+[.]\w{2,4}){1}|\d{11}){1}$"
	),
	"note" => array(
		"welcome" => "We at OneChurch are super excited to liberate your financial options.",
		"account.activation" => "Please click the button provided to activate your account. If you didn't begin this process simply ignore this message",
		"password.reset" => "Please click the button below to reset your password. Or, ignore this message if you didn't make this request",
		"pattern.phone" => "\d{11}",
		"sample.phone" => "eg. 08012345678",
		"pattern.username" => "^(['-.]?[a-zA-Z]+['-.]?[a-zA-z]*)([ ]+['-.]?[a-zA-Z]+['-.]?[a-zA-z]*)*",
		"pattern.password" => "\w{6,25}",
		"sample.username" => "eg. Rhoda",
		"sample.password" => "6 or more characters"
	),
);

define("DB_HOST", $config['db'][$config['env']['env']]['host']);
define("DB_USERNAME", $config['db'][$config['env']['env']]['username']);
define("DB_PASSWORD", $config['db'][$config['env']['env']]['password']);
define("DB_DBNAME", $config['db'][$config['env']['env']]['dbname']);

/*
    Error reporting.
*/
	ini_set("error_reporting", "true");
	ini_set('display_errors', 1);
	//set session cookie to be read only via http and not by JavaScript
	// ini_set('session.cookie_httponly', '1');
	error_reporting(E_ALL);
	error_reporting(E_ALL ^ E_WARNING);
	date_default_timezone_set("Africa/Lagos");
	ini_set('max_execution_time', 300); //300 seconds = 5 minutes
	set_time_limit(300);

/*
    Session
*/
if(session_status()!=PHP_SESSION_ACTIVE){
	ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7);
	ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 7);
	ini_set('session.save_path', SESSION_PATH);
	session_start();
}
?>