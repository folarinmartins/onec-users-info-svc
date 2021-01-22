<?php
namespace view;

class Email extends PageElement{
	function __construct(array $variables,string $contentFile='404.php', string $templatesPath=null, string $interface='mail'){
		parent::__construct($variables,'mail.'.$contentFile.'.php',$templatesPath,$interface);
		$this->init($variables);
	}
	function init(){
		global $config;
		$vars = $this->getVariables();

		$vars['text']['company.name'] = $config['company']['name'];
		$vars['text']['company.slogan'] = $config['company']['slogan'];
		$vars['text']['company.address'] = $config['company.address'];
		$vars['text']['company.phone'] = $config['company']['phone'];
		$vars['text']['company.email'] = $config['company']['email'];
		$vars['text']['company.icon'] = $config['company']['icon'];
		$vars['url']['company.url'] = $config['company']['url'];
		$vars['text']['app.version'] = $config['version'];
		$vars['text']['app.build'] = $config['build'];
		$vars['url']['mail.images'] = image('mail');
		$vars['url']['support'] = route('support');

		$this->setVariables($vars);
	}
	static function getInstance($variables,$contentFile,$templatePath=null):Email{
		return new Email($variables,$contentFile,$templatePath,'mail');
	}
	function render():string{
		ob_start(null,null,PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);
		parent::render();
		$xmail = ob_get_clean();
		// ob_end_clean();
		return $xmail;
	}
}