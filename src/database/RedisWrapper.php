<?php
	namespace database;
	/* 
		https://github.com/phpredis/phpredis	
	*/

	class RedisWrapper{
		private static $redisObj = null;
		private function __construct($server="localhost",$port=6379){			
			
		}
		public static function getRedis():\Redis{
			if(self::$redisObj === null){
				self::$redisObj = new \Redis();
				self::$redisObj->connect('localhost');
				if(!self::$redisObj){
					throw new \RedisException("Redis client cannot be instantiated");
				}
			}			
			return self::$redisObj;
		}
		function get($key):?string{
			try{
				if($redisObj->exists($key))
					return $redisObj->get($key);
			}catch( Exception $e ){
				return null;
			}
			return [];
		}
		function set(string $key,string $value):bool{
			try{
				$redisObj->set($key,$value);
			}catch( Exception $e ){
				return false;
			}
			return true;
		}
		function del($key):bool{
			try{
				$redisObj->del($key);
			}catch( Exception $e ){
				return false;
			}
			return true;
		}
	}
?>