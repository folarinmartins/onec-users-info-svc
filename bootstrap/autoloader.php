<?php
class Autoloader{
    public static function register(){
		spl_autoload_register(function ($class_name) {
			if (file_exists($class_name . '.php')) {
				require_once $class_name . '.php';
				return true;
			}
			return false;
		});
		// PSR-4 Autoloader
		
		spl_autoload_register(function ($class_name) {
			$file = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
			$folder = self::getClassFolder($file);
			if($folder){
				require_once($folder.$file.".php");
				return true;
			}
			return false;
		});
		
		// Search through a list of directories
			spl_autoload_register(function ($class_name) {
				$directorys = array(
					'classes/',
					'classes/otherclasses/',
					'classes2/',
					'module1/classes/'
				);
				foreach($directorys as $directory){
					if(file_exists($directory.$class_name . '.php')){
						require_once($directory.$class_name . '.php');
						//only require the class once, so quit after to save effort (if you got more, then name them something else
						return;
					}
				}
			}); 
		/* 
		*/
		// Search through directory tree
		spl_autoload_register(function ($class_name) {{
			$folder = self::getClassFolder($class_name);	
			if($folder)
				require_once($folder.$class_name.".php");
			// else{
			// 	$folder = self::getClassFolder($class_name,DIRECTORY_SEPARATOR,LIBS_PATH);
			// 	if($folder)
			// 		require_once($folder.$class_name.".php");
			// }			
		}});
    }
	public static function getClassFolder($class_name, $sub = DIRECTORY_SEPARATOR,$baseDir=RESOURCE_PATH) {
		$dir = dir($baseDir.$sub);
		if(file_exists($baseDir.$sub.$class_name.".php"))
			return $baseDir.$sub;

		while(false !== ($folder = $dir->read())) {
			if($folder != "." && $folder != "..") {
				if(is_dir($baseDir.$sub.$folder)) {
					$subFolder = self::getClassFolder($class_name, $sub.$folder."/");

					if($subFolder)
						return $subFolder;
				}
			}
		}
		$dir->close();
		return false;
	}
}
?>