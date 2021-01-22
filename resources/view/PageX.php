<?php
	namespace view;
	use model\TypeProxy;
	use model\ProxyType;
	use helper\Utility;
	use web\ShoppingCart;
	use comm\Link;
	use acm\ACM;
	
	class PageX extends Page{
		function __construct(TypeProxy $pageProxy, array $variables, string $templatesPath=null){
			$variables['text']['page.title'] = $pageProxy->getPropertyValue(PROPERTY_NAME)[0];
			$variables['text']['page.desc'] = $pageProxy->getPropertyValue(PROPERTY_DEFINITION)[0];		
			parent::__construct($variables,$pageProxy->getPropertyValue(PROPERTY_TEMPLATE)[0].'.php',$templatesPath);
			// $this->init();
		}
		// public function init(){
		// }
		static function show(TypeProxy $pageProxy, array $variables, string $templatesPath=null){
			$pageX = new PageX($pageProxy,$variables,$templatesPath);
			$pageX->render();
		}
		// static function getInstance(TypeProxy $pageProxy, array $variables, string $templatesPath=null):PageX{
		// 	return new PageX($pageProxy,$variables,$templatesPath);
		// }
	}
?>