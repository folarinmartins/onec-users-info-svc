<?php
	namespace contract;

	/** @package contract */
	abstract class Controller{
		protected $request;
		static function index(){}
		static function create(){}
		static function store(){}
		static function show(){
			echo 'now showing';
		}
		static function edit(){}
		static function update(){}
		static function destroy(){}
	}
?>