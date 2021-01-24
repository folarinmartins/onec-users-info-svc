<?php
	namespace view;
	// use model\TypeProxy;
	use helper\Utility;
	// use web\ShoppingCart;
	use comm\Link;
	// use acm\ACM;
	// use model\ProxyType;
	// use model\TypeDAO;

	class Page extends PageElement{
		function __construct(array $variables,string $contentFile='404.php', string $templatesPath=null, string $interface='app'){
			parent::__construct($variables,'page.'.$contentFile.'.php',$templatesPath, $interface);
			$this->init();
		}
		function init(){
			global $config;
			$variables = $this->getVariables();			
			/* 
				if($userProxy->hasProperty(PROPERTY_ICON))
					$variables['url']['user.dp'] = '';//Link::getFile(TypeProxy::getInstanceByID(TYPE_FILE,$userProxy->getPropertyID(PROPERTY_ICON)[0]));
				else
					$variables['url']['user.dp'] = 'assets/img/a0.jpg';

				if($userProxy){
					$variables['text']['user.name'] = $userProxy->getPropertyValue(PROPERTY_NAME)[0]??'';
					$variables['text']['user.email'] = $userProxy->getPropertyValue(PROPERTY_EMAIL)[0]??'';

				}else{
					$variables['text']['user.name'] = 'User';
					$variables['text']['user.email'] = '';
				}
 			*/
			$variables['text']['op.c'] = OP_C;
			$variables['text']['op.r'] = OP_R;
			$variables['text']['op.u'] = OP_U;
			$variables['text']['op.d'] = OP_D;
			$variables['text']['op.l'] = OP_L;
			$variables['root'] = Link::getBaseURL();
			 
			if(!isset($variables['text']['page.title']))
				$variables['text']['page.title'] = 'Welcome!';
			if(!isset($variables['text']['company.name']))
				$variables['text']['company.name'] = $config['company']['name'];
			/* 
				if(!isset($vars['url']['page.hero']))
					$vars['url']['page.hero'] = '.';
				if(!isset($vars['text']['page.desc']))
					$vars['text']['page.desc'] = 'Welcome to '.$vars['text']['company.name'];
				if(!isset($vars['text']['alert.show']))
					$vars['text']['alert.show'] = 'show';
				if(!isset($vars['text']['alert.theme']))
					$vars['text']['alert.theme'] = 'info';
				if(!isset($vars['text']['alert.icon']))
					$vars['text']['alert.icon'] = 'info';
				if(!isset($vars['url']['login']))
					$vars['url']['login'] = Link::getURL('login');;
				if(!isset($vars['url']['logout']))
					$vars['url']['logout'] = Link::getURL('logout');;
				if(!isset($vars['url']['support']))
					$vars['url']['support'] = Link::getURL('support');;
				if(!isset($vars['url']['services']))
					$vars['url']['services'] = Link::getURL('services');;
				if(!isset($vars['url']['settings']))
					$vars['url']['settings'] = Link::getURL('settings');;
				if(!isset($vars['url']['3pa']))
					$vars['url']['3pa'] = Link::getURL('3pa');;
				if(!isset($vars['url']['3pa.r']))
					$vars['url']['3pa.r'] = Link::getURL('3pa').'?e0='.OP_R;;
				if(!isset($vars['url']['3pa.l']))
					$vars['url']['3pa.l'] = Link::getURL('3pa').'?e0='.OP_L;;
				if(!isset($vars['url']['3pa.x']))
					$vars['url']['3pa.x'] = Link::getURL('3pa').'?e0='.OP_X;;
				if(!isset($vars['url']['shop']))
					$vars['url']['shop'] = Link::getURL('shop');
				if(!isset($vars['url']['shop.c']))
					$vars['url']['shop.c'] = Link::getURL('shop').'?e0='.TYPE_PACKAGE.'&e1='.OP_C;
				if(!isset($vars['url']['shop.l']))
					$vars['url']['shop.l'] = Link::getURL('shop').'?e0='.TYPE_PACKAGE.'&e1='.OP_L;
				if(!isset($vars['url']['delivery.l']))
					$vars['url']['delivery.l'] = Link::getURL('logistics').'?e0='.TYPE_DELIVERY.'&e1='.OP_L;
				if(!isset($vars['url']['product.l']))
					$vars['url']['product.l'] = Link::getURL('logistics').'?e0='.TYPE_PRODUCT.'&e1='.OP_L;
				if(!isset($vars['url']['commodity.l']))
					$vars['url']['commodity.l'] = Link::getURL('logistics').'?e0='.TYPE_COMMODITY.'&e1='.OP_L;
				if(!isset($vars['url']['so.l']))
					$vars['url']['so.l'] = Link::getURL('logistics').'?e0='.TYPE_ORDER_SALES.'&e1='.OP_L;
				if(!isset($vars['url']['mrp']))
					$vars['url']['mrp'] = Link::getURL('mrp').'?e0='.TYPE_ORDER_SALES.'&e1='.PROPERTY_CONFIGURATION;
				if(!isset($vars['url']['mrp.so.l']))
					$vars['url']['mrp.so.l'] = Link::getURL('mrp').'?e0='.TYPE_ORDER_SALES.'&e1='.OP_L;
				if(!isset($vars['url']['mrp.rfq.l']))
					$vars['url']['mrp.rfq.l'] = Link::getURL('logistics').'?e0='.TYPE_RFQ.'&e1='.OP_L;
				if(!isset($vars['url']['mrp.rfq.c']))
					$vars['url']['mrp.rfq.c'] = Link::getURL('logistics').'?e0='.TYPE_RFQ.'&e1='.OP_C;
				if(!isset($vars['url']['mrp.quotation.l']))
					$vars['url']['mrp.quotation.l'] = Link::getURL('logistics').'?e0='.TYPE_QUOTATION.'&e1='.OP_L;
				if(!isset($vars['url']['mrp.vinvoice.l']))
					$vars['url']['mrp.vinvoice.l'] = Link::getURL('logistics').'?e0='.TYPE_INVOICE_VENDORS.'&e1='.OP_L;
				if(!isset($vars['url']['mrp.gr.l']))
					$vars['url']['mrp.gr.l'] = Link::getURL('logistics').'?e0='.TYPE_GOODS_RECEIPT.'&e1='.OP_L;
				if(!isset($vars['url']['mrp.po.l']))
					$vars['url']['mrp.po.l'] = Link::getURL('logistics').'?e0='.TYPE_ORDER_PURCHASE.'&e1='.OP_L;
				if(!isset($vars['url']['mrp.pro.l']))
					$vars['url']['mrp.pro.l'] = Link::getURL('logistics').'?e0='.TYPE_ORDER_PRODUCTION.'&e1='.OP_L;
				if(!isset($vars['url']['mrp.prs.l']))
					$vars['url']['mrp.prs.l'] = Link::getURL('logistics').'?e0='.TYPE_PRODUCTION_SCHEDULE.'&e1='.OP_L;
				if(!isset($vars['url']['mrp.sro.l']))
					$vars['url']['mrp.sro.l'] = Link::getURL('logistics').'?e0='.TYPE_ORDER_PROCESSING.'&e1='.OP_L;
				if(!isset($vars['url']['mrp.srs.l']))
					$vars['url']['mrp.srs.l'] = Link::getURL('logistics').'?e0='.TYPE_PROCESSING_SCHEDULE.'&e1='.OP_L;
				if(!isset($vars['url']['payment.l']))
					$vars['url']['payment.l'] = Link::getURL('logistics').'?e0='.TYPE_ORDER_PAY.'&e1='.OP_L;
				if(!isset($vars['url']['invoice.l']))
					$vars['url']['invoice.l'] = Link::getURL('logistics').'?e0='.TYPE_INVOICE_SALES.'&e1='.OP_L;
				if(!isset($vars['url']['entry.l']))
					$vars['url']['entry.l'] = Link::getURL('logistics').'?e0='.TYPE_ENTRY.'&e1='.OP_L;
				if(!isset($vars['url']['transaction.l']))
					$vars['url']['transaction.l'] = Link::getURL('logistics').'?e0='.TYPE_TRANSACTION.'&e1='.OP_L;
				if(!isset($vars['url']['support.l']))
					$vars['url']['support.l'] = Link::getURL('logistics').'?e0='.TYPE_ISSUE.'&e1='.OP_L;
				if(!isset($vars['url']['notification.l']))
					$vars['url']['notification.l'] = Link::getURL('logistics').'?e0='.TYPE_NOTIFICATION.'&e1='.OP_L;
				if(!isset($vars['url']['link.l']))
					$vars['url']['link.l'] = Link::getURL('logistics').'?e0='.TYPE_WEB_LINK.'&e1='.OP_L;
				if(!isset($vars['url']['weblog.l']))
					$vars['url']['weblog.l'] = Link::getURL('logistics').'?e0='.TYPE_WEB_LOG.'&e1='.OP_L;
				if(!isset($vars['url']['checkout']))
					$vars['url']['checkout'] = Link::getURL('checkout');
				if(!isset($vars['url']['gigs']))
					$vars['url']['gigs'] = Link::getURL('gigs');

				if(!isset($vars['text']['alert.message'])){
					$vars['text']['alert.message'] = '<strong>Yo guacamole!</strong> You should check in on some of those fields below.';
					$vars['text']['alert.show'] = 'hidden';
					$vars['text']['alert.theme'] = 'info';
					$vars['text']['alert.icon'] = 'info';
				}
			 */
			$this->setVariables($variables);
		}
		static function getInstance(array $variables, string $contentFile, string $templatePath=null, string $interface='app'):Page{
			return new Page($variables,$contentFile,$templatePath,$interface);
		}
		public static function redirect(string $url, bool $force=true){
			if(!$force || getallheaders()['X-PJAX']??false){
				header("X-PJAX-URL: $url");
			}else{
				header("Location: $url");
				exit();
			}
		}
		public static function show404(){
			Page::showErrorPage('404');
		}
		public static function show505(){
			Page::showErrorPage('505');
		}
		public static function showErrorPage(string $code='404'){
			$errorPage = new Page(['text'=>['page.title'=>"Error $code"]],"$code",null);
			$errorPage->render();
		}
		public static function getRoles():string{
			$retPX = ACM::getRoles(Utility::currentParty(),Utility::currentCompany());
			if($retPX->getProperty(PROPERTY_LINE)??false){
				$role = $retPX->getProperty(PROPERTY_LINE)[0][PROPERTY_NAME];
				if(count($retPX->getProperty(PROPERTY_LINE))>1){
					$role .= ' +'.(count($retPX->getProperty(PROPERTY_LINE))-1);
				}
				return $role;
			}else{
				return TypeDAO::getType(Utility::currentParty()->getType());
			}
		}
		public static function getMenu(TypeProxy $companyProxy, TypeProxy $userProxy):ProxyType{
			$retPX = ProxyType::getInstance(TYPE_MENU);
			$menus = TypeProxy::getInstanceMap(TYPE_MENU,null,REALM_KN);
			foreach($menus as $i=>$menu){
				// Utility::log("Menu $menu=>$i");
				$ret = [];
				$pages = TypeProxy::getInstanceMap(TYPE_PAGE,null,REALM_KN,[[PROPERTY_MENU,$i],[PROPERTY_STATE,STATE_ENABLED]]);
				// $anchors = TypeProxy::getInstanceMap(TYPE_ANCHOR,null,REALM_KN,[[PROPERTY_MENU,$i],[PROPERTY_STATE,STATE_ENABLED]]);
				$ret[PROPERTY_NAME] = $menu;
				$ret[PROPERTY_ID] = $i;
				$index = 0;
				foreach ($pages as $j => $page){
					$pageProxy = TypeProxy::getInstanceByID(TYPE_PAGE,$j,REALM_KN);
					if(ACM::canAccessPage($userProxy,$companyProxy,$pageProxy)){
						$ret[PROPERTY_ANCHOR][$index][PROPERTY_NAME] = $pageProxy->getPropertyValue([PROPERTY_NAME])[0];
						$ret[PROPERTY_ANCHOR][$index][PROPERTY_DEFINITION] = $pageProxy->getPropertyValue([PROPERTY_DEFINITION])[0];
						$ret[PROPERTY_ANCHOR][$index][PROPERTY_THEME] = $pageProxy->getPropertyValue(PROPERTY_THEME)[0]??'';
						$ret[PROPERTY_ANCHOR][$index][PROPERTY_SYMBOL] = $pageProxy->getPropertyValue(PROPERTY_ICON_FE)[0]??'';
						$ret[PROPERTY_ANCHOR][$index][PROPERTY_URI] = Link::getBaseURL().$pageProxy->getPropertyValue([PROPERTY_URI])[0]??'';
						++$index;
					}else{
						// Utility::log("Cant't access page ".$anchorProxy->getPropertyValue([PROPERTY_PAGE,PROPERTY_NAME])[0]);
					}
				}
				$retPX->appendProperty(PROPERTY_MENU,$ret);
			}
			return $retPX;
		}
	}
?>