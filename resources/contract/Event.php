<?php
namespace contract;

use helper\Utility;
use model\Model;
class Event {
    private static $listeners = [];
    /**
     * @param EventListener $listener
     * @param string $op
     * @param string $prop
     * @param string $UID
     * @return void
     */
    public static function listen(EventListener $listener, string $op=QUANTIFIER_ALL, string $prop=QUANTIFIER_ALL, string $UID=QUANTIFIER_ALL) {
		self::$listeners[$UID][$op][$prop][] = $listener;
    }
    public static function unlisten(EventListener $listener, string $op=QUANTIFIER_ALL, string $model) {
        foreach ((self::$listeners[$model][$op]??[]) as $event => $listenerX){
			if($listenerX===$listener){
				unset(self::$listeners[$model][$op][$listenerX]);
			break;
			}
        }
    }
    public static function trigger(Model $model, string $op, string $prop=QUANTIFIER_ALL, string $from=null, string $to=null):bool {
		Utility::log("Event::trigger called OP:$op, PROP:$prop, FROM:$from, TO:$to TYPE:".$model->getType());
		global $user;
		global $admin;
		global $Action;
		if((isset($from) && isset($to) && $from!=$to) || (is_null($from) && is_null($to))){
			if(Utility::isLogActions()){
				$Action->insertMap([
					'user'=>($user->getID()?$user->getID():$admin->getID()),
					'name'=>$op,
					'description'=>"Event::trigger called OP:$op, PROP:$prop, FROM:$from, TO:$to",
				]);
			}
			$ops = array_unique([$op,QUANTIFIER_ALL]);
			$props = array_unique([$prop,QUANTIFIER_ALL]);
			$models = array_unique([$model->getType(),QUANTIFIER_ALL]);
			$targets = [];

			foreach($ops as $i=>$opX){
				foreach($props as $j=>$propX){
					foreach($models as $j=>$modelX){
						foreach(self::$listeners[$modelX][$opX][$propX]??[] as $k=>$listener){
							$targets[] = $listener;
						}
					}					
				}
			}
			foreach(\array_unique($targets,SORT_REGULAR) as $event => $listener){
				if(!$listener->stateChanged($model,$op,$prop,$from,$to))
			break;
			}
		}else{
			Utility::log("DISCARDED::Event::trigger called OP:$op, PROP:$prop, FROM:$from, TO:$to");
		}
		return true;
    }
}
?>