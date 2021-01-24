<?php
namespace contract;
use model\Model;

interface EventListener{
	public static function stateChanged(Model $model, string $op, string $prop=null, string $from=null, string $to=null):bool;
}
?>