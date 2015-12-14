<?php

$FORMS = Array();

$FORMS['form_block'] = <<<END

<form action="https://91.208.121.201/estore_listener.php" name="uniteller" method="post">
		<input type="hidden" name="SHOP_ID" value="%uni_id%" >
		<input type="hidden" name="LOGIN" value="%uni_login%" >
		<input type="hidden" name="ORDER_ID" value="%order_id%">
		<input type="hidden" name="PAY_SUM" value="%amount%" >
		<input type="hidden" name="VALUE_1" value="%order_id%" >
		<input type="hidden" name="URL_OK" value="%urlok%" >
		<input type="hidden" name="URL_NO" value="%urlno%" >
		<input type="hidden" name="SIGN" value="%sign%" >
		<input type="hidden" name="LANG" value="RU" >
	  </form>
	     
</form>
<p>
   <INPUT TYPE="button" value="Оплатить Visa" onclick="document.location = '%twpg_url%'"> %uni_submit%
	</p>
END;
?>