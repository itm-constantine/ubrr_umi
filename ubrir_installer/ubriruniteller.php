<?php

if (isset($_POST['SIGN'])) {
				$sign = strtoupper(md5(md5($_POST['SHOP_ID']).'&'.md5($_POST["ORDER_ID"]).'&'.md5($_POST['STATE'])));
				if ($_POST['SIGN'] == $sign) {
					switch ($_POST['STATE']) {
						case 'paid':
							header('Location: http://'.$_SERVER['HTTP_HOST'].'/emarket/gateway/'.$_POST["ORDER_ID"].'/?SIGN='.$_POST["SIGN"].'&SHOP_ID='.$_POST['SHOP_ID'].'&STATE='.$_POST['STATE']);				
	 					  break;
					  }
			    }
			} 
			
?>