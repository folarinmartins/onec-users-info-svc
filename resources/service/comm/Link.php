<?php declare(strict_types=1);
namespace comm;
use model\Model;
use helper\Utility;
Class Link{
	public static function getProtocol($appendSlash=true):string{
		if(Utility::isCLI()){
			$http = 'http';
		}else
			$http = "".(($_SERVER['HTTPS']??false) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
		return $http.($appendSlash?"://":'');
	}
	public static function getHost($appendSlash=true):string{
		global $config;
		if(Utility::isCLI()){
			return $config['url']['host'][Utility::getEnv()].($appendSlash?"/":'');
		}
		return $_SERVER['HTTP_HOST'].($appendSlash?"/":'');
	}
	public static function getBaseDir($appendSlash=true):string{
		global $config;
		$ret = $config['url']['basedir'][Utility::getEnv()];
		if($ret && $appendSlash){
			return $ret.'/';
		}
		return $ret;
	}
	public static function getBaseURL($appendSlash=true):string{
		$burl = 'localhost';
		if(false && Utility::isCache() && ($burl = Utility::getCache('config','base-url')[0]??'')){
		}else{
			$burl = Link::getProtocol().Link::getHost().Link::getBaseDir($appendSlash);
			if(Utility::isCache())
				Utility::cacheGraph('config','base-url',[$burl]);
		}
		return $burl;
	}	

	public static function getLink($uri,$redirect=false):string{
		return '';
		global $Link;
		$links = $Link->getGeneric('uri',$uri);		
		if(!$links){
			$link = $Link->insertMap(['uri'=>$uri]);
		}else{
			$link = $Link->getInstance($links[0]['id']);
		}
		return Link::encode($link->getID(),$redirect);
	}
	public static function getDummyLink(){
		return Link::get404();
	}
	public static function get404(){
		return Link::getLink("404");
	}
	public static function get505(){
		return Link::getLink("505");
	}
	public static function getURL($index, array $params=[]):string{
		$ret = Link::getBaseURL().Utility::getURL($index);
		foreach($params as $k=>$v){
			if(!\is_array($v)){
				$ret .= ($k==0?'?':'&')."e$k=".$v;
			}
		}
		return $ret;
	}
	public static function getB64File(Model $file):string{
		return $file->getProperty('type').';base64,'.base64_encode(file_get_contents((Link::getFile($file,false))));
		// if($fileProxy->getRealm()==REALM_KN || $fileProxy->definesProperty([PROPERTY_FORMAT,REALM_KN]))
		// 	return 'data:'.$fileProxy->getPropertyValue([[PROPERTY_FORMAT,REALM_KN],[PROPERTY_MIME,REALM_KN]])[0].';base64,'.base64_encode(file_get_contents(UPLOAD_PATH.'/'.$fileProxy->getID().'.'.$fileProxy->getPropertyValue(PROPERTY_EXTENSION)[0]));
		// else
		// 	return 'data:'.$fileProxy->getPropertyValue([[PROPERTY_FORMAT,REALM_OP],[PROPERTY_MIME,REALM_KN]])[0].';base64,'.base64_encode(file_get_contents(UPLOAD_PATH.'/'.$fileProxy->getID().'.'.$fileProxy->getPropertyValue(PROPERTY_EXTENSION)[0]));
	}
	public static function getFile(Model $file, bool $public=true):string{
		if($file && $file->getID() && $file->getType()==MODEL_FILE){
			$filename = $file->getID().'.'.$file->getProperty('ext');
			if(file_exists(UPLOAD_PATH.'/'.$filename)){
				if($public){
					return Link::getBaseURL(false).UPLOAD_PATH_SYM.'/'.$filename;
				}else{
					return UPLOAD_PATH.'/'.$filename;
				}
			}
			else
				return Link::getURL('dummy.image');
		}else{
			return Link::getURL('dummy.image');
		}
	}
	public static function getURI($link):string{
		global $Link;
		return $Link->getInstance($link)->getProperty('uri');
	}
	public static function isAuth(string $link):bool{
		return true;
		// return TypeProxy::getInstance(TYPE_LINE,$link)->getPropertyValue(PROPERTY_AUTH)[0]??false;
	}
	public static function encode($link,$redirect=false){
		if($redirect)
			return 'PROPERTY_REDIRECT'.'='.$link;
		else
			return Link::getURL('fwd').'?link='.$link;
	}
}