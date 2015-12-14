<?php
@session_start();

require "classes/modules/emarket/classes/payment/systems/ubrir_files/UbrirClass.php";
include "standalone.php";
if(!permissionsCollection::getInstance()->isSv()) exit('Нужно быть супервайзером');

$new_order_twpg = l_mysql_query('SELECT * FROM `cms3_object_fields` WHERE `name` = "mnt_ubrir_id" ORDER BY id DESC LIMIT 1');
$new_order_twpg2 = l_mysql_query('SELECT * FROM  `cms3_object_content` WHERE  `field_id` = '.@mysql_fetch_assoc($new_order_twpg)['id'].' ORDER BY field_id DESC LIMIT 1');
$twpg_id = @mysql_fetch_assoc($new_order_twpg2)['varchar_val'];

$new_order_twpg3 = l_mysql_query('SELECT * FROM `cms3_object_fields` WHERE `name` = "mnt_secret_key" ORDER BY id DESC LIMIT 1');
$new_order_twpg4 = l_mysql_query('SELECT * FROM  `cms3_object_content` WHERE  `field_id` = '.@mysql_fetch_assoc($new_order_twpg3)['id'].' ORDER BY field_id DESC LIMIT 1');
$twpg_sert = @mysql_fetch_assoc($new_order_twpg4)['varchar_val'];

if(!empty($_POST['task_ubrir']))
	switch ($_POST['task_ubrir']) {
				case '1':
					if(!empty($_POST['shoporderidforstatus'])) {
					$oid = $_POST['shoporderidforstatus']*1;
					$nt = l_mysql_query('SELECT * FROM `umi_twpg` WHERE twpg_id = '.$oid.' ORDER BY id DESC LIMIT 1');
					$ot = @mysql_fetch_assoc($nt);
							$bankHandler = new Ubrir(array(																												 // для статуса
								'shopId' => $twpg_id,
								'order_id' => $oid, 
								'sert' => $twpg_sert,
								'twpg_order_id' => $ot['twpg_id'], 
								'twpg_session_id' => $ot['session_id']
								));
							$out = '<div class="ubr_s">Статус заказа - '.$bankHandler->check_status().'</div>';	
					}
					else $out = "<div class='ubr_f'>Вы не ввели номер заказа</div>";
					break;
					
				case '2':
					if(!empty($_POST['shoporderidforstatus'])) {
						$oid = $_POST['shoporderidforstatus']*1;
					$nt = l_mysql_query('SELECT * FROM `umi_twpg` WHERE twpg_id = '.$oid.' ORDER BY id DESC LIMIT 1');
					$ot = @mysql_fetch_assoc($nt);
							$bankHandler = new Ubrir(array(																												 // для статуса
								'shopId' => $twpg_id,
								'order_id' => $oid, 
								'sert' => $twpg_sert,
								'twpg_order_id' => $ot['twpg_id'], 
								'twpg_session_id' => $ot['session_id']
								));
							$out = '<div class="ubr_s">'.$bankHandler->detailed_status().'</div>';		
					}
					else $out = "<div class='ubr_f'>Вы не ввели номер заказа</div>";
					break;
					
				 case '3':
					if(!empty($_POST['shoporderidforstatus'])) {
						$oid = $_POST['shoporderidforstatus']*1;
						$nt = l_mysql_query('SELECT * FROM `umi_twpg` WHERE twpg_id = '.$oid.' ORDER BY id DESC LIMIT 1');
						$ot = @mysql_fetch_assoc($nt);
								$bankHandler = new Ubrir(array(																												 // для статуса
									'shopId' => $twpg_id,
									'order_id' => $oid, 
									'sert' => $twpg_sert,
									'twpg_order_id' => $ot['twpg_id'], 
									'twpg_session_id' => $ot['session_id']
									));
									$res = $bankHandler->reverse_order();	
									if($res == 'OK') header('Location: http://'.$_SERVER['HTTP_HOST'].'/emarket/gateway/'.$ot['umi_id'].'/?reverse=true');
									else $out = "<div class='ubr_f'>Реверс невозможен</div>";
						}
						else $out = "<div class='ubr_f'>Вы не ввели номер заказа</div>";
					break;
				
				case '4':				
							$bankHandler = new Ubrir(array(																												 // для статуса
								'shopId' => $twpg_id,
								'sert' => $twpg_sert
								));
							$out = '<div class="ubr_s">'.$bankHandler->reconcile().'</div>';                                                                                        
					break;		
					
				case '5':				
							$bankHandler = new Ubrir(array(																												 // для журнала операции
								'shopId' => $twpg_id,
								'sert' => $twpg_sert
								));
							$out = '<div class="ubr_s">'.$bankHandler->extract_journal().'</div>';    
					break;	

				case '6':				
							$new_order_twpg5 = l_mysql_query('SELECT * FROM `cms3_object_fields` WHERE `name` = "mnt_uni_login" ORDER BY id DESC LIMIT 1');
							$new_order_twpg6 = l_mysql_query('SELECT * FROM  `cms3_object_content` WHERE  `field_id` = '.@mysql_fetch_assoc($new_order_twpg5)['id'].' ORDER BY field_id DESC LIMIT 1');
							$uni_login = @mysql_fetch_assoc($new_order_twpg6)['varchar_val'];
							
							$new_order_twpg7 = l_mysql_query('SELECT * FROM `cms3_object_fields` WHERE `name` = "mnt_uni_emp" ORDER BY id DESC LIMIT 1');
							$new_order_twpg8 = l_mysql_query('SELECT * FROM  `cms3_object_content` WHERE  `field_id` = '.@mysql_fetch_assoc($new_order_twpg7)['id'].' ORDER BY field_id DESC LIMIT 1');
							$uni_pass = @mysql_fetch_assoc($new_order_twpg8)['varchar_val'];
							
							if(empty($uni_pass) OR empty($uni_login)) { echo '<div class="ubr_f">Необходимо ввести логин и пароль ЛК для MasterCard</div>';	die; }
							
							$bankHandler = new Ubrir(array(																												 // для журнала Uniteller
								'uni_login' => $uni_login,
								'uni_pass' => $uni_pass,
								));
							$out = '<div class="ubr_s">'.$bankHandler->uni_journal().'</div>';  
					break;	
				case '7':
					if(!empty($_POST['mailsubject'])  AND !empty($_POST['maildesc'])) {					
							$to = 'ibank@ubrr.ru';
							 $subject = htmlspecialchars($_GET['mailsubject'], ENT_QUOTES);
							 $message = 'Отправитель: '.htmlspecialchars($_GET['mailem'], ENT_QUOTES).' | '.htmlspecialchars($_GET['maildesc'], ENT_QUOTES);
							 $headers = 'From: '.$_SERVER["HTTP_HOST"];
							  mail($to, $subject, $message, $headers);
					}     
					break;	
				default:
					break;
			}
			
			
$toprint = '

<div id="callback" style="display: none;">
 <table>
 <tr>
 <h2 onclick="show(this);" style="text-align: center; cursor:pointer;">Обратная связь<span style="margin-left: 20px; font-size: 80%; color: grey;" onclick="jQuery(\'#callback\').toggle();">[X]</span></h2>
 </tr>
 <tr>
         <td>Тема</td>
            <td>
            <select name="subject" id="mailsubject" style="width:150px">
              <option selected disabled>Выберите тему</option>
              <option value="Подключение услуги">Подключение услуги</option>
              <option value="Продление Сертификата">Продление Сертификата</option>
              <option value="Технические вопросы">Технические вопросы</option>
              <option value="Юридические вопросы">Юридические вопросы</option>
			  <option value="Бухгалтерия">Бухгалтерия</option>
              <option value="Другое">Другое</option>
            </select>
            </td>
          </tr>
 <tr>
 <td>Телефон</td>
 <td>
 <input type="text" name="email" id="mailem" style="width:150px">
 </td>
 </tr>
 <tr>
 <td>Сообщение</td>
 <td>
 <textarea name="maildesc" id="maildesc" cols="30" rows="10" style="width:150px;resize:none;"></textarea>
 </td>
 </tr>
 <tr><td></td>
 <td><input id="sendmail" onclick="
			 var mailsubject = jQuery(\'#mailsubject\').val();
			 var maildesc = jQuery(\'#maildesc\').val();
			 var mailem = jQuery(\'#mailem\').val();
			 console.log(mailsubject);
			 console.log(maildesc);
			 console.log(mailem);
			 if(!mailem & !!maildesc) {
			 jQuery(\'#mailresponse\').html(\'<br>Необходимо указать телефон\');
			 return false;
			 }
			 if(!maildesc & !!mailem) {
			 jQuery(\'#mailresponse\').html(\'<br>Сообщение не может быть пустым\');
			 return false;
			 }
			 if(!!mailem & !!maildesc) 
			 jQuery.ajax({
			 type: \'POST\',
			 url: location.href,
			 data: {mailsubject:mailsubject, maildesc:maildesc, mailem:mailem, task_ubrir:7},
			 success: function(response){
			 jQuery(\'#mailresponse\').html(\'Письмо отправлено на почтовый сервер\');
			 jQuery(\'#maildesc\').val(null);
			 jQuery(\'#mailsubject\').val(null);
			 jQuery(\'#mailem\').val(null);
			 }
			 });
			 else jQuery(\'#mailresponse\').html(\'<br>Заполнены не все поля\');
			 return false;
			 " type="button" name="sendmail" value="Отправить">
			 </tr>
			 <tr>
			 <td>
			 </td>
			 <td style="padding: 0" id="mailresponse">
			 </td>
			 </tr>
			 <tr>
			 <td></td>
			<td>8 (800) 1000-200</td></tr>
 </table>
 </div>
 <div style="width: 100%; margin-top: 10px;">'.$out.'</div>
<div style="margin: 70px auto; text-align: center; padding: 20px; width: 415px; border-radius: 5px; background-color: white;"> 
<h3 style="text-align: center; padding: 0 0 20px 0; margin: 0;">Получить детальную информацию:</h3>
<div style="margin: 0 auto; text-align: center; padding: 5px; width: 200px; border: 1px dashed #999;"><form action="" method="post">Номер платежного документа (смотрите внутри заказа): <br>
<input style="margin: 5px;" type="text" name="shoporderidforstatus" id="shoporderidforstatus" value="'.@$_POST['shoporderidforstatus'].'" placeholder="№" size="8">
<input style="margin: 5px;" type="hidden" name="task_ubrir" id="task_ubrir" value="">
      <input class="twpginput" type="button" onclick="$(\'#task_ubrir\').val(1); submit();" id="statusbutton" value="Запросить статус заказа">
      <input class="twpginput" type="button" onclick="$(\'#task_ubrir\').val(2); submit();" id="detailstatusbutton" value="Информация о заказе">
      <input class="twpginput" type="button" onclick="$(\'#task_ubrir\').val(3); submit();" id="reversbutton" value="Отмена заказа"><br>
 </div>  
      <input class="twpgbutton" type="button" onclick="$(\'#task_ubrir\').val(4); submit();" id="recresultbutton" value="Сверка итогов">
      <input class="twpgbutton" type="button" onclick="$(\'#task_ubrir\').val(5); submit();" id="journalbutton" value="Журнал операций Visa">
	  <input class="twpgbutton" type="button" onclick="$(\'#task_ubrir\').val(6); submit();" id="unijournalbutton" value="Журнал операций MasterCard">
	  <input class="twpgbutton" type="button" onclick="jQuery(\'#callback\').toggle();" id="mailbutton" value="Написать в банк">
	  </form>
</div>
';			
?>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<meta charset="utf-8">
<style>
body {
	 color: #111;
	 /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#10a6d9+0,eb0140+100 */
	background: rgb(16,166,217); /* Old browsers */
	/* IE9 SVG, needs conditional override of 'filter' to 'none' */
	background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzEwYTZkOSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNlYjAxNDAiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
	background: -moz-linear-gradient(top,  rgba(16,166,217,1) 0%, rgba(235,1,64,1) 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(16,166,217,1)), color-stop(100%,rgba(235,1,64,1))); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  rgba(16,166,217,1) 0%,rgba(235,1,64,1) 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  rgba(16,166,217,1) 0%,rgba(235,1,64,1) 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  rgba(16,166,217,1) 0%,rgba(235,1,64,1) 100%); /* IE10+ */
	background: linear-gradient(to bottom,  rgba(16,166,217,1) 0%,rgba(235,1,64,1) 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#10a6d9', endColorstr='#eb0140',GradientType=0 ); /* IE6-8 */

	 
	 width: 100%;
	 height: 100%;
	 min-height: 100%;
	 
	 font: 100% 'Verdana', sans-serif;
	 font-weight: 300;
	 
	 position: relative;
	 line-height: 1.5em;
}
.ubr_s {
padding:10px;
color:#3c763d;
background-color:#dff0d8;
border-color:#d6e9c;
border:1px;
}
.ubr_f {
padding:10px;
color:#a94442;
background-color:#f2dede;
border-color:#ebccd1;
border:1px;
}
.twpgdt {
	width: 100%;
}
.twpgbutton {
	font-weight: 100 !important;
	margin: 20px 5px 5px 5px; 
	width: 100% !important;
}
.twpginput {
	margin: 5px; 
	width: 180px;
}
.twpgdt td {
	border-bottom: 1px solid #ddd;
	border-right: 1px solid #ddd;
	font-size: 80%;
}
#callback {
 padding: 20px;
 position: fixed;
 width:335px;
 bottom: 0;
 left: 0;
 height: 340px;
 z-index:999;
 background-color: white;
 box-shadow: 0 0 25px 3px;
 border-radius: 3px;
 margin: 20px;
 text-align: left;
 }
</style>
<title>Операции по УБРиР</title>
</head>
<body>
<?
echo $toprint;
?>
</body>
</html>