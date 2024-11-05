<?php
/**
* NOTA SOBRE LA LICENCIA DE USO DEL SOFTWARE
*
* El uso de este software está sujeto a las Condiciones de uso de software que
* se incluyen en el paquete en el documento "Aviso Legal.pdf". También puede
* obtener una copia en la siguiente url:
* http://www.redsys.es/wps/portal/redsys/publica/areadeserviciosweb/descargaDeDocumentacionYEjecutables
*
* Redsys es titular de todos los derechos de propiedad intelectual e industrial
* del software.
*
* Quedan expresamente prohibidas la reproducción, la distribución y la
* comunicación pública, incluida su modalidad de puesta a disposición con fines
* distintos a los descritos en las Condiciones de uso.
*
* Redsys se reserva la posibilidad de ejercer las acciones legales que le
* correspondan para hacer valer sus derechos frente a cualquier infracción de
* los derechos de propiedad intelectual y/o industrial.
*
* Redsys Servicios de Procesamiento, S.L., CIF B85955367
*/
if(!function_exists("escribirLog")) {
	require_once('apiRedsys/redsysLibrary.php');
}
if(!class_exists("RedsysAPI")) {
	require_once('apiRedsys/apiRedsysFinal.php');
}

function tep_db_query_biz($query)
{
        global $db;
        return($db->Execute($query));
}

function tep_db_num_rows_biz($query)
{
        return($query->RecordCount());
}

  class bizum {
    var $code, $title, $description, $enabled;
   /**
     * $_check is used to check the configuration key set up
     * @var int
     */
    protected $_check;
    /**
     * $order_status is the order status to set after processing the payment
     * @var int
     */
    public $order_status;


// class constructor
    function bizum() {
      global $order;

      $this->code = 'bizum';
      $this->title = MODULE_PAYMENT_BIZUM_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_BIZUM_TEXT_DESCRIPTION;
      $this->enabled = ((MODULE_PAYMENT_BIZUM_STATUS == 'True') ? true : false);
      $this->sort_order = MODULE_PAYMENT_BIZUM_SORT_ORDER;
      $this->mantener_pedido_ante_error_pago = ((MODULE_PAYMENT_BIZUM_ERROR_PAGO == 'si') ? true : false);
      $this->logActivo = MODULE_PAYMENT_BIZUM_LOG;

	  if ((int)MODULE_PAYMENT_BIZUM_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_BIZUM_ORDER_STATUS_ID;
      }
	  if(MODULE_PAYMENT_BIZUM_URL=="SIS-D"){
	    $this->form_action_url = "http://sis-d.redsys.es/sis/realizarPago/utf-8";
	  }
	  else if(MODULE_PAYMENT_BIZUM_URL=="SIS-I"){
		$this->form_action_url = "https://sis-i.redsys.es:25443/sis/realizarPago/utf-8";
	  }
	  else if(MODULE_PAYMENT_BIZUM_URL=="SIS-T"){
		$this->form_action_url = "https://sis-t.redsys.es:25443/sis/realizarPago/utf-8";
	  }
	  else if(MODULE_PAYMENT_BIZUM_URL=="SIS"){
		$this->form_action_url = "https://sis.redsys.es/sis/realizarPago/utf-8";
	  }
    }

// class methods

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button()
    {
      global $order, $language;

      //DATOS PARA EL TPV

      //Merchant Data
      $ds_merchant_data=zen_session_id();

      //Amount
      $total=number_format($order->info['total'], 2);
      $cantidad = round($total*$order->info['currency_value'],2);
      $cantidad = number_format($cantidad, 2, '.', '');
      $cantidad = preg_replace('/\./', '', $cantidad);

	  //Id_Pedido
	  $numpedido = rand(10,10000);

      //Nombre Com.
	  $ds_merchant_name= MODULE_PAYMENT_BIZUM_NAMECOM;

	  //Tipo MONEDA.
	  if(MODULE_PAYMENT_BIZUM_MERCHANT_CURRENCY=='DOLAR'){
		 $moneda = "840";
	  }
	  else {
		 $moneda = "978"; //EURO POR DEFECTO
	  }

	  //Nombre Terminal.
      $terminal = MODULE_PAYMENT_BIZUM_TERMINAL;
      $trans = "0";

	  //Idioma
      $idioma_tpv = "0";
      if ($language=='english')
      {
        $idioma_tpv='002';
	  }

	  //URL OK Y KO
      $ds_merchant_urlok=zen_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'NONSSL');
      $ds_merchant_urlko=zen_href_link(FILENAME_LOGOFF, 'error_message=ERROR', 'NONSSL', true, false);

	  //URL Respuesta ONLINE
	  $home = explode('/', $_SERVER['REQUEST_URI']);
	  $urltienda = "http://".$_SERVER['HTTP_HOST']."/".$home[1]."/bizum_process.php";

	  //Firma
      $clave256=MODULE_PAYMENT_BIZUM_ID_CLAVE256;
      $codigo=MODULE_PAYMENT_BIZUM_ID_COM;

      $ds_product_description="";
      for($i=0; $i<sizeof($order->products); $i++){
      	$ds_product_description=$order->products[$i]["qty"]."x".$order->products[$i]["name"]."/";
      }

      $ds_merchant_titular=$order->customer["firstname"]." ".$order->customer["lastname"];

	$miObj = new RedsysAPI;
	$miObj->setParameter("DS_MERCHANT_AMOUNT",$cantidad);
	$miObj->setParameter("DS_MERCHANT_ORDER",strval($numpedido));
	$miObj->setParameter("DS_MERCHANT_MERCHANTCODE",$codigo);
	$miObj->setParameter("DS_MERCHANT_CURRENCY",$moneda);
	$miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE",$trans);
	$miObj->setParameter("DS_MERCHANT_TERMINAL",$terminal);
	$miObj->setParameter("DS_MERCHANT_MERCHANTURL",$urltienda);
	$miObj->setParameter("DS_MERCHANT_URLOK",$ds_merchant_urlok);
	$miObj->setParameter("DS_MERCHANT_URLKO",$ds_merchant_urlko);
	$miObj->setParameter("Ds_Merchant_ConsumerLanguage",$idioma_tpv);
	$miObj->setParameter("Ds_Merchant_ProductDescription",$ds_product_description);
	$miObj->setParameter("Ds_Merchant_Titular",$ds_merchant_titular);
	$miObj->setParameter("Ds_Merchant_MerchantData",$ds_merchant_data);
	$miObj->setParameter("Ds_Merchant_MerchantName",$ds_merchant_name);
	$miObj->setParameter("Ds_Merchant_PayMethods", 'z');
	$miObj->setParameter("Ds_Merchant_Module","zencart_bizum_3.0.1");

	//Datos de configuración
	$version = getVersionClave();

	//Clave del comercio que se extrae de la configuración del comercio
	// Se generan los parámetros de la petición
	$request = "";
	$paramsBase64 = $miObj->createMerchantParameters();
	$signatureMac = $miObj->createMerchantSignature($clave256);

	$process_button_string =
		zen_draw_hidden_field('Ds_SignatureVersion', $version) .
		zen_draw_hidden_field('Ds_MerchantParameters', $paramsBase64) .
		zen_draw_hidden_field('Ds_Signature', $signatureMac);
	return $process_button_string;
    }

    function before_process()
    {
		$idLog = generateIdLog();
		$logActivo = MODULE_PAYMENT_BIZUM_LOG;
		$valido = FALSE;
		if (!empty( $_POST ) ) {//URL DE RESP. ONLINE

			$clave256=MODULE_PAYMENT_BIZUM_ID_CLAVE256;

			/** Recoger datos de respuesta **/
			$version     = $_POST["Ds_SignatureVersion"];
			$datos    = $_POST["Ds_MerchantParameters"];
			$firma_remota    = $_POST["Ds_Signature"];

			// Se crea Objeto
			$miObj = new RedsysAPI;

			/** Se decodifican los datos enviados y se carga el array de datos **/
			$decodec = $miObj->decodeMerchantParameters($datos);

			/** Se calcula la firma **/
			$firma_local = $miObj->createMerchantSignatureNotif($clave256,$datos);

			/** Extraer datos de la notificación **/
			$total     = $miObj->getParameter('Ds_Amount');
			$pedido    = $miObj->getParameter('Ds_Order');
			$codigo    = $miObj->getParameter('Ds_MerchantCode');
			$moneda    = $miObj->getParameter('Ds_Currency');
			$respuesta = $miObj->getParameter('Ds_Response');
			$id_trans = $miObj->getParameter('Ds_AuthorisationCode');

			//Nuevas variables
			$codigoOrig=MODULE_PAYMENT_BIZUM_ID_COM;

			if(checkRespuesta($respuesta)
				&& checkMoneda($moneda)
				&& checkFuc($codigo)
				&& checkPedidoNum($pedido)
				&& checkImporte($total)
				&& $codigo == $codigoOrig
			){
				escribirLog($idLog." -- El pedido con ID " . $pedido . " es válido y se ha registrado correctamente.",$logActivo);
				$valido = TRUE;
			} else {
				escribirLog($idLog." -- Parámetros incorrectos.",$logActivo);
				if(!checkImporte($total)) {
					escribirLog($idLog." -- Formato de importe incorrecto.",$logActivo);
				}
				if(!checkPedidoNum($pedido)) {
					escribirLog($idLog." -- Formato de nº de pedido incorrecto.",$logActivo);
				}
				if(!checkFuc($codigo)) {
					escribirLog($idLog." -- Formato de FUC incorrecto.",$logActivo);
				}
				if(!checkMoneda($moneda)) {
					escribirLog($idLog." -- Formato de moneda incorrecto.",$logActivo);
				}
				if(!checkRespuesta($respuesta)) {
					escribirLog($idLog." -- Formato de respuesta incorrecto.",$logActivo);
				}
				if(!checkFirma($firma_remota)) {
					escribirLog($idLog." -- Formato de firma incorrecto.",$logActivo);
				}
				escribirLog($idLog." -- El pedido con ID " . $pedido . " NO es válido.",$logActivo);
				$valido = FALSE;
			}

			if ($firma_local != $firma_remota || FALSE === $valido) {
				//El proceso no puede ser completado, error de autenticación
				escribirLog($idLog." -- La firma no es correcta.",$logActivo);
				$_SESSION['cart']->reset(true);
				zen_redirect(zen_href_link(FILENAME_LOGOFF, 'error_message=ERROR DE FIRMA', 'NONSSL', true, false));
			}

			$iresponse=(int)$respuesta;

			if (($iresponse>=0) && ($iresponse<=100)) {
				//after_Process();
			} else {
				if(!$this->mantener_pedido_ante_error_pago){
					$_SESSION['cart']->reset(true);
					escribirLog($idLog." -- Error de respuesta. Vaciando carrito.",$logActivo);
					zen_redirect(zen_href_link(FILENAME_LOGOFF, 'error_message=ERROR DE RESPUESTA', 'NONSSL', true, false));
				} else {
					escribirLog($idLog." -- Error de respuesta. Manteniendo carrito.",$logActivo);
					zen_redirect(zen_href_link(FILENAME_CHECKOUT, 'error_message=ERROR DE RESPUESTA', 'NONSSL', true, false));
				}
			}
		} else {
      		//Transacción denegada
			escribirLog($idLog." -- Error. Hacking atempt!",$logActivo);
      		die ("Hacking atempt!");
			exit;
      	}
    }

    function after_process()
    {
		global $db, $insert_id;

		//Actualizamos el Status del pedido
		$db->Execute("UPDATE " . TABLE_ORDERS  . " SET orders_status = ".MODULE_PAYMENT_BIZUM_ORDER_STATUS_ID.",payment_method = 'Pago mediante Bizum',payment_module_code = 'bizum' WHERE orders_id = '".$insert_id."'");
		$db->Execute("UPDATE " . TABLE_ORDERS_STATUS_HISTORY  . " SET orders_status_id = ".MODULE_PAYMENT_BIZUM_ORDER_STATUS_ID." WHERE orders_id = '".$insert_id."'");
		//Borrar carrito
		$_SESSION['cart']->reset(true);
		//zen_redirect(zen_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'NONSSL'));
	    return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query_biz("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_BIZUM_STATUS'");
        $this->_check = tep_db_num_rows_biz($check_query);
      }
      return $this->_check;
    }

	function getCurrenciesInstalled(){
		global $db;
		$currencies = array();
		$currency_query_raw = "select currencies_id, title, code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, last_updated, value from " . TABLE_CURRENCIES . " order by title";
		$currency = $db->Execute($currency_query_raw);


		while (!$currency->EOF) {
			$cInfo = new objectInfo($currency->fields);
			array_push($currencies, $cInfo);
			$currency->MoveNext();
		}
		return $currencies;
	}

    function install() {
	  tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Activar modulo Bizum', 'MODULE_PAYMENT_BIZUM_STATUS', 'True', 'Quiere aceptar pagos usando Bizum?', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
	  tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Nombre Comercio Bizum', 'MODULE_PAYMENT_BIZUM_NAMECOM', '', 'Nombre de comercio', '6', '4', now())");
      tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FUC Comercio Bizum', 'MODULE_PAYMENT_BIZUM_ID_COM', '', 'Codigo de comercio proporcionado por la entidad bancaria', '6', '4', now())");
      tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Clave de Encriptacion (SHA-256)', 'MODULE_PAYMENT_BIZUM_ID_CLAVE256', '', 'Clave de encriptacion SHA-256 proporcionada por la entidad bancaria', '6', '4', now())");
	  tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Terminal', 'MODULE_PAYMENT_BIZUM_TERMINAL', '1', 'Terminal de pago en Redsys', '6', '4', now())");
	  tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tipo de moneda', 'MODULE_PAYMENT_BIZUM_MERCHANT_CURRENCY', 'EURO', 'Codigo correspondiente a la moneda EURO', '6', '4','zen_cfg_select_option(array(\'EURO\', \'DOLAR\'), ', now())");
      tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Entorno de Bizum', 'MODULE_PAYMENT_BIZUM_URL', 'SIS-D', 'Direccion en internet de la pasarela de pago', '6', '4','zen_cfg_select_option(array(\'SIS-D\', \'SIS-I\', \'SIS-T\', \'SIS\'), ', now())");
	  tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function,date_added) values ('Error pago', 'MODULE_PAYMENT_BIZUM_ERROR_PAGO', 'no', 'Mantener carrito si se produce un error en el pago', '6', '4','zen_cfg_select_option(array(\'si\', \'no\'), ',  now())");
	  tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function,date_added) values ('Log activo', 'MODULE_PAYMENT_BIZUM_LOG', 'no', 'Crear trazas de log', '6', '4','zen_cfg_select_option(array(\'si\', \'no\'), ',  now())");
      tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Orden de aparicion', 'MODULE_PAYMENT_BIZUM_SORT_ORDER', '10', 'Orden de aparicion. Numero menor es mostrado antes que los mayores.', '6', '0', now())");
	  tep_db_query_biz("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Order Status', 'MODULE_PAYMENT_BIZUM_ORDER_STATUS_ID', '0', 'Selecciona el estado final del pedido', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
   }

    function remove() {
      tep_db_query_biz("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {

	  $claves = array();
	  array_push($claves,
      'MODULE_PAYMENT_BIZUM_STATUS',
	  'MODULE_PAYMENT_BIZUM_NAMECOM',
      'MODULE_PAYMENT_BIZUM_ID_COM',
	  'MODULE_PAYMENT_BIZUM_TERMINAL',
	  'MODULE_PAYMENT_BIZUM_MERCHANT_CURRENCY',
	  'MODULE_PAYMENT_BIZUM_ERROR_PAGO',
	  'MODULE_PAYMENT_BIZUM_LOG',
      'MODULE_PAYMENT_BIZUM_ID_CLAVE256',
      'MODULE_PAYMENT_BIZUM_URL',
      'MODULE_PAYMENT_BIZUM_SORT_ORDER',
	  'MODULE_PAYMENT_BIZUM_ORDER_STATUS_ID'
      );

	  return $claves;
    }
  }

?>
