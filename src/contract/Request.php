<?php
	namespace contract;
	class Request{
		private array $variables;
		private array $params;
		private array $files;
		private string $method;
		private array $contexts;
		function __construct(array $params, string $method, array $files=[]){
			$this->params = $params;
			$this->files = $files;
			$this->method = $method;
			$this->contexts = $_SERVER;
		}		
		public function getParams():array{
			return $this->params;
		}
		public function setParams(array $params){
			$this->params = $params;
		}
		public function getParam(string $index){
			return $this->params[$index]??'';
		}
		public function setParam(string $index, $value){
			$this->params[$index]=$value;
		}
		public function getContexts():array{
			return $this->contexts;
		}
		public function setContexts(array $contexts){
			$this->contexts = $contexts;
		}
		public function getContext(string $index){
			return $this->contexts[$index]??'';
		}
		public function setContext(string $index, $value){
			$this->contexts[$index]=$value;
		}
		public function getFiles():array{
			return $this->files;
		}
		public function setFiles(array $files){
			$this->files = $files;
		}
		public function getFile(string $index):array{
			return $this->files[$index]??[];
		}
		public function getVariables():array{
			return $this->variables;
		}
		public function setVariables(array $variables){
			$this->variables = $variables;
		}
		public function getVariable(string $index):?string{
			return $this->variables[$index]??'';
		}
		public function getMethod():string{
			return $this->method;
		}
		public function setMethod(string $method){
			$this->method = $method;
		}		
	}
?>