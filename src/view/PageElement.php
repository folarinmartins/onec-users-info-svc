<?php
	namespace view;
	use helper\Utility;

	class PageElement{
		private string $templatesPath = '';
		protected array $variables = [];
		protected string $interface = '';
		private string $contentFile = '';
		private bool $enabled;

		function __construct(array $variables, string $contentFile, string $templatesPath=null, string $interface){
			$this->templatesPath = $templatesPath?$templatesPath:TEMPLATES_PATH;
			$this->contentFile = $contentFile?$contentFile:'';
			$this->variables = $variables;
			$this->interface = $interface;
			$this->enabled = true;
		}
		function getTemplatePath(){
			return $this->templatesPath;
		}
		function getContentFile(){
			return $this->contentFile;
		}
		function setContentFile(string $contentFile){
			return $this->contentFile = $contentFile;
		}
		function getContent(){
			return $this->getTemplatePath() . "/".$this->getInterface()."/". $this->getContentFile();
		}
		function getVariables(){
			return $this->variables;
		}
		function setVariables(array $variables){
			$this->variables = $variables;
		}
		function getInterface(){
			return $this->interface;
		}
		function setInterface(array $interface){
			$this->interface = $interface;
		}
		function setVariable(string $index, $value){
			$this->variables[$index] = $value;
		}
		function render():string{
			$ret = '';
			if($this->canRender()){
				foreach($this->getVariables() as $key => $value) {
					if (strlen($key) > 0) {
						${$key} = $value;
					}
				}				
				require($this->getContent());
			}
			return '';
		}
		function canRender(){
			return file_exists($this->getContent());
		}
	}