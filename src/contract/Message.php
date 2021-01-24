<?php
	namespace contract;

	class Message{
		private string $title;
		private string $message;
		private string $theme;
		function __construct(string $title, string $message, string $theme=THEME_INFO){
			$this->title = $title;
			$this->message = $message;
			$this->theme = $theme;
		}
		public function getTitle():string{
			return $this->title;
		}
		public function setTitle(string $title){
			$this->title = $title;
		}
		public function getTheme():string{
			return $this->theme;
		}
		public function setTheme(string $theme){
			$this->theme = $theme;
		}
		public function getMessage():string{
			return $this->message;
		}
		public function setMessage(string $message){
			$this->message = $message;
		}
	}
?>