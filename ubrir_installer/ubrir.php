<?php
include(dirname(__FILE__)."/ubrir_files/UbrirClass.php");
class ubrirPayment extends payment {

    public function validate() {
        return true;
    }

    public function process($template = null) {
		$this->order->order();
        $currency = strtoupper(mainConfiguration::getInstance()->get('system', 'default-currency'));
        $amount = number_format($this->order->getActualPrice(), 2, '.', '');
        $orderId = $this->order->getId();
        $mnt_ubrir_id = $this->object->mnt_ubrir_id;
        $mnt_secret_key = $this->object->mnt_secret_key;
        $mnt_uni_id = $this->object->mnt_uni_id;
        $mnt_uni_login = $this->object->mnt_uni_login;
		$mnt_uni_pass = $this->object->mnt_uni_pass;
		$mnt_uni_emp = $this->object->mnt_uni_emp;
		$mnt_two = $this->object->mnt_two;
		
		$cmsController = cmsController::getInstance();
		$answerUrl = 'http://'.$cmsController->getCurrentDomain()->getHost() . "/emarket/gateway/".$this->order->getId().'/';
		$failUrl = 	'http://'.$cmsController->getCurrentDomain()->getHost() .	'/emarket/purchase/result/fail/';		 
		$successUrl = 	'http://'.$cmsController->getCurrentDomain()->getHost() .	'/emarket/purchase/result/successful/';		
					if(is_array($mnt_ubrir_id)) $mnt_ubrir_id = $mnt_ubrir_id[0];
					if(is_array($mnt_secret_key)) $mnt_secret_key = $mnt_secret_key[0];	 
		$bankHandler = new Ubrir(array(				        // инициализируем объект операции в TWPG
							'shopId' => $mnt_ubrir_id, 
							'order_id' => $orderId, 
							'sert' => $mnt_secret_key, 
							'amount' => $amount,
							'approve_url' => $answerUrl,
							'cancel_url' => $failUrl,
							'decline_url' => $failUrl,
							));                    
		$response_order = $bankHandler->prepare_to_pay();
        
		$new_order_twpg = l_mysql_query('INSERT INTO `umi_twpg` (`umi_id`, `twpg_id`, `session_id`) VALUES ("'.$orderId.'", "'.$response_order->OrderID[0].'", "'.$response_order->SessionID[0].'")');
		
        $param = array();
    		if(is_array($mnt_uni_id)) $mnt_uni_id = $mnt_uni_id[0];
    		//if(is_array($mnt_uni_login)) 
    		$mnt_uni_login = 'maevs@ubrr.ru';
    		if(is_array($mnt_uni_pass)) $mnt_uni_pass = $mnt_uni_pass[0];
    		//var_dump($mnt_uni_login); die;
		$param['sign'] = strtoupper(md5(md5($mnt_uni_id).'&'.md5($mnt_uni_login).'&'.md5($mnt_uni_pass).'&'.md5($orderId).'&'.md5($amount)));
        $param['twpg_url'] = $response_order->URL[0].'?orderid='.$response_order->OrderID[0].'&sessionid='.$response_order->SessionID[0];
        $param['uni_id'] = $mnt_uni_id;
        $param['uni_login'] = $mnt_uni_login;
        $param['amount'] = $amount;
        $param['order_id'] = $orderId;
		$param['urlno'] = $failUrl;
		$param['urlok'] = $successUrl;
		if($mnt_two == 1) $param['uni_submit'] = ' <INPUT TYPE="button" onclick="document.forms.uniteller.submit()" value="Оплатить MasterCard">';
		else $param['uni_submit'] = '';
		
		
        $this->order->setPaymentStatus('initialized');
        list($templateString) = def_module::loadTemplates("tpls/emarket/payment/ubrir/ubrir.tpl", "form_block"); 
        return def_module::parseTemplate($templateString, $param);
    }

    public function poll() {
	
		if (isset($_POST["xmlmsg"])) {
		
		 $rooturl = cmsController::getInstance()->getCurrentDomain()->getHost();
			
		  $xml_string = base64_decode($_POST["xmlmsg"]);
		  $parse_it = simplexml_load_string($xml_string);
		   
		  if ($parse_it->OrderStatus[0]=="APPROVED") {
		  
			$new_order_twpg = l_mysql_query('SELECT * FROM `umi_twpg` WHERE umi_id = '.$this->order->getId().' ORDER BY id DESC LIMIT 1');
			$orderyeah = @mysql_fetch_assoc($new_order_twpg);
			
			$mnt_ubrir_id = $this->object->mnt_ubrir_id;
			$mnt_secret_key = $this->object->mnt_secret_key;
			if(is_array($mnt_ubrir_id)) $mnt_ubrir_id = $mnt_ubrir_id[0];
			if(is_array($mnt_secret_key)) $mnt_secret_key = $mnt_secret_key[0];	 
					
			$bankHandler = new Ubrir(array(																											 // инициализируем объект операции в TWPG
								'shopId' => $mnt_ubrir_id, 
								'order_id' => $this->order->getId(), 
								'sert' => $mnt_secret_key,
								'twpg_order_id' => $orderyeah['twpg_id'], 
								'twpg_session_id' => $orderyeah['session_id']
								));
			if($bankHandler->check_status("APPROVED")) {
			$this->order->payment_document_num = $orderyeah['twpg_id'];
			$this->order->setPaymentStatus("accepted");
			return '<h2>Заказ успешно оплачен</h2>';
		  }
		 
		};
	};
	
	
	if (isset($_GET['SIGN'])) {
				$sign = strtoupper(md5(md5($_GET['SHOP_ID']).'&'.md5($this->order->getId()).'&'.md5($_GET['STATE'])));
				if ($_GET['SIGN'] == $sign) {
					switch ($_GET['STATE']) {
						case 'paid':
							$this->order->setPaymentStatus("accepted");		
	 					  break;
					  }
			    }
	};  
	
	
	if (isset($_GET['reverse'])) {
							if(!permissionsCollection::getInstance()->isSv()) exit('Нужно быть супервайзером');
							$this->order->setOrderStatus("rejected");		
							$this->order->setPaymentStatus("rejected");	
							return '<h2>Реверс успешно выполнен</h2><p><a href="/ubrir_orders.php">Вернуться</a></p>';  
			    }; 
	
  
  }

}

;

?>