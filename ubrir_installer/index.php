<?php

    $className = "ubrir";
    $paymentName = "Платежный модуль УБРиР";
 
    include "../standalone.php";

    /* $objectTypesCollection = umiObjectTypesCollection::getInstance();
    $objectsCollection = umiObjectsCollection::getInstance();
 
    // получаем родительский тип
    $parentTypeId = $objectTypesCollection->getTypeIdByGUID("emarket-payment");
 
    // Тип для внутреннего объекта, связанного с публичным типом
    $internalTypeId = $objectTypesCollection->getTypeIdByGUID("emarket-paymenttype");
    $typeId = $objectTypesCollection->addType($parentTypeId, $paymentName);
 
    // Создаем внутренний объект
    $internalObjectId = $objectsCollection->addObject($paymentName, $internalTypeId);
    $internalObject = $objectsCollection->getObject($internalObjectId);
    $internalObject->setValue("class_name", $className); // имя класса для реализации
 
    // связываем его с типом
    $internalObject->setValue("payment_type_id", $typeId);
    $internalObject->setValue("payment_type_guid", "user-emarket-payment-" . $typeId);
    $internalObject->commit();
 
    // Связываем внешний тип и внутренний объект
    $type = $objectTypesCollection->getType($typeId);
    $type->setGUID($internalObject->getValue("payment_type_guid"));
    $type->commit(); */
 
	
	$destination = dirname(__FILE__).'/../classes/modules/emarket/classes/payment/systems/'; 
	
	$config = mainConfiguration::getInstance();
	$host = $config->get('connections', 'core.host');
    $login = $config->get('connections', 'core.login');
    $password = $config->get('connections', 'core.password');
    $dbname = $config->get('connections', 'core.dbname');
	$mysqli = new mysqli($host, $login, $password, $dbname);
	$mysqli->set_charset("utf8");
	
	$sql = "SELECT @parent_id:=id FROM `cms3_object_types` WHERE `guid`='emarket-payment';
SELECT @hierarchy_type_id:=id FROM `cms3_hierarchy_types` WHERE `name` = 'emarket' AND `ext`='payment';
SELECT @type_id:=id FROM `cms3_object_types` WHERE `guid`='emarket-paymenttype';
SELECT @payment_type_id:=id FROM `cms3_object_fields` WHERE `name`='payment_type_id';

INSERT INTO `cms3_object_types` VALUES(NULL, 'emarket-payment-820', 'Платежный модуль УБРИР', 1, @parent_id, 0, 0, @hierarchy_type_id, 0);
SET @obj_type = LAST_INSERT_ID( ) ;
INSERT INTO `cms3_import_types` VALUES ('1', '820', @obj_type);
INSERT INTO `cms3_objects` VALUES(NULL, 'emarket-paymenttype-ubrir', 'ubrir', 0, @type_id, 9, NULL);
SET @obj = LAST_INSERT_ID( ) ;
INSERT INTO `cms3_import_objects`  VALUES(1, 'onapy', @obj);
SELECT @field_id:=new_id FROM `cms3_import_fields` WHERE `source_id`='1' AND `field_name`='class_name' AND `type_id`=@type_id;
INSERT INTO `cms3_object_content` VALUES(@obj, @field_id, NULL, 'ubrir', NULL, NULL, NULL, NULL);
SELECT @field_id:=new_id FROM `cms3_import_fields` WHERE `source_id`='1' AND `field_name`='payment_type_id' AND `type_id`=@type_id;
INSERT INTO `cms3_object_content` VALUES(@obj, @field_id, @obj_type, NULL, NULL, NULL, NULL, NULL);
SELECT @field_id:=new_id FROM `cms3_import_fields` WHERE `source_id`='1' AND `field_name`='payment_type_guid' AND `type_id`=@type_id;
INSERT INTO `cms3_object_content` VALUES(@obj, @field_id, NULL, 'emarket-payment-820', NULL, NULL, NULL, NULL);

INSERT INTO `cms3_object_field_groups` VALUES(NULL, 'payment_props', 'Свойства способа оплаты', @obj_type, 1, 1, 5, 0, '');
SET @field_group = LAST_INSERT_ID( ) ;
INSERT INTO `cms3_fields_controller` VALUES(5, @payment_type_id, @field_group);

INSERT INTO `cms3_object_field_groups` VALUES(NULL, 'settings', 'Параметры', @obj_type, 1, 1, 10, 0, '');
SET @field_group = LAST_INSERT_ID( ) ;

INSERT INTO `cms3_object_fields` VALUES(NULL, 'mnt_ubrir_id', 'ID интернет-магазина для VISA', 1, 13, 0, 1, NULL, 0, 0, '', 0, NULL, 0, 0, 1);
SET @field = LAST_INSERT_ID( ) ;
INSERT INTO `cms3_fields_controller` VALUES (15, @field, @field_group);

INSERT INTO `cms3_object_fields` VALUES(NULL, 'mnt_secret_key', 'Пароль к сертификату VISA', 1, 13, 0, 1, NULL, 0, 0, '', 0, NULL, 0, 0, 1);
SET @field = LAST_INSERT_ID( ) ;
INSERT INTO `cms3_fields_controller` VALUES (20, @field, @field_group);

INSERT INTO `cms3_object_fields` VALUES(NULL, 'mnt_uni_id', 'ID интернет-магазина для MasterCard', 1, 13, 0, 1, NULL, 0, 0, '', 0, NULL, 0, 0, 0);
SET @field = LAST_INSERT_ID( ) ;
INSERT INTO `cms3_fields_controller` VALUES (25, @field, @field_group);

INSERT INTO `cms3_object_fields` VALUES(NULL, 'mnt_uni_login', 'Логин личного кабинета MasterCard', 1, 13, 0, 1, NULL, 0, 0, '', 0, NULL, 0, 0, 0);
SET @field = LAST_INSERT_ID( ) ;
INSERT INTO `cms3_fields_controller` VALUES (30, @field, @field_group);

INSERT INTO `cms3_object_fields` VALUES(NULL, 'mnt_uni_pass', 'Пароль интернет-магазина для MasterCard', 1, 13, 0, 1, NULL, 0, 0, '', 0, NULL, 0, 0, 0);
SET @field = LAST_INSERT_ID( ) ;
INSERT INTO `cms3_fields_controller` VALUES (35, @field, @field_group);

INSERT INTO `cms3_object_fields` VALUES(NULL, 'mnt_uni_emp', 'Пароль личного кабинета MasterCard', 1, 13, 0, 1, NULL, 0, 0, '', 0, NULL, 0, 0, 0);
SET @field = LAST_INSERT_ID( ) ;
INSERT INTO `cms3_fields_controller` VALUES (40, @field, @field_group);

INSERT INTO `cms3_object_fields` VALUES(NULL, 'mnt_two', 'Два процессинга', 1, 1, 0, 1, NULL, 0, 0, '', 0, NULL, 0, 0, 1);
SET @field = LAST_INSERT_ID( ) ;
INSERT INTO `cms3_fields_controller` VALUES (45, @field, @field_group);

CREATE TABLE IF NOT EXISTS `umi_twpg` (
   `id` INT  AUTO_INCREMENT  NOT NULL,
   `umi_id` VARCHAR (255)  NOT NULL,
   `twpg_id`  VARCHAR (255)  NOT NULL,
   `session_id`  VARCHAR (255) ,
   PRIMARY KEY (`id`)
);
";



$mysqli->multi_query($sql);
	
	
	$tmpls = scandir ('../templates');
	foreach($tmpls as $tmpp) {
		if($tmpp != '.' AND $tmpp != '..') {
		@copy( dirname(__FILE__)."/ubrir.phtml", "../templates/".$tmpp."/php/emarket/payment/ubrir.phtml");
		}
	}
	
	
	function copyDir( $source, $destination ) {
		if ( is_dir( $source ) ) {
			@mkdir( $destination, 0755 );
			$directory = dir( $source );
			while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
				if ( $readdirectory == '.' || $readdirectory == '..' ) continue;
				$PathDir = $source . '/' . $readdirectory; 
				if ( is_dir( $PathDir ) ) {
						copyDir( $PathDir, $destination . '/' . $readdirectory );
					continue;
				}
			copy( $PathDir, $destination . '/' . $readdirectory );
			}
			$directory->close();
		} else {
			copy( $source, $destination );
		}
	} 
	
	@copyDir( dirname(__FILE__)."/ubrir_files", $destination."/ubrir_files");
	@copyDir( dirname(__FILE__)."/tpl/ubrir", "../tpls/emarket/payment");
	copy( dirname(__FILE__)."/ubrir.php", $destination."/ubrir.php" );
	copy( dirname(__FILE__)."/lang.ru.php", dirname(__FILE__).'/../classes/modules/emarket/lang.ru.php' );
	copy( dirname(__FILE__)."/ubrir_orders.php", dirname(__FILE__).'/../ubrir_orders.php' );
	copy( dirname(__FILE__)."/ubriruniteller.php", dirname(__FILE__).'/../ubriruniteller.php' );

    echo "Готово!";


?>