<?php
	namespace contract;

	use contract\Message;
	use contract\Request;
	use view\Page;
	
	class Response{
		private Request $request;
		private $page;
		private array $messageBag;
		private array $payload;
		private array $header;
		private array $text;
		private array $url;
		private int $statusCode;
		
		function __construct(Request $request){
			$this->request = $request;
			$this->statusCode = 200;
			$this->messageBag = [];
			$this->payload = [];
			$this->text = [];
			$this->url = [];
			$this->header = [];
			$this->page = null;
		}
		public function getRequest():Request{
			return $this->request;
		}
		public function setRequest(Request $request){
			$this->request = $request;
		}
		public function getPage():?Page{
			return $this->page;
		}
		public function setPage(Page $page){
			$this->page = $page;
		}
		public function getURL(string $index){
			return $this->url[$index]??'';
		}
		public function setURL(string $index,$url){
			$this->url[$index] = $url;
		}
		public function getURLs():array{
			return $this->url;
		}
		public function setURLs(array $url){
			$this->url = $url;
		}
		public function getTexts():array{
			return $this->text;
		}
		public function setTexts(array $text){
			$this->text = $text;
		}
		public function setText(string $index, $text){
			$this->text[$index] = $text;
		}
		public function getText(string $index){
			return $this->text[$index]??'';
		}
		public function getMessageBag():array{
			return $this->messageBag;
		}
		public function setMessageBag(array $messageBag){
			$this->messageBag = $messageBag;
		}
		public function addMessage(Message $message){
			$this->messageBag[] = $message;
		}
		public function getPayload():array{
			return $this->payload;
		}
		public function setPayload(array $payload){
			$this->payload = $payload;
		}
		public function addPayload(string $key, $value){
			$this->payload[$key] = $value;
		}
		public function getHeader():array{
			return $this->header;
		}
		public function setHeader(array $header){
			$this->header = $header;
		}
		public function addHeader(string $key, $value){
			$this->header[$key] = $value;
		}
		public function getPayloadValue(string $key){
			return $this->payload[$key]??'';
		}
		public function getStatusCode():int{
			return $this->statusCode;
		}
		public function setStatusCode(int $statusCode){
			$this->statusCode = $statusCode;
		}
	}
?>