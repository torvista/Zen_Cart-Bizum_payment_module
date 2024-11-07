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

/////////GLOBALES PARA LOG

$logLevel = 0;

$logDISABLED = 0;
$logINFOR = 1;
$logDEBUG = 2;

/** Polyfill str_contains */

if (!function_exists('str_contains')) {
    /**
     * Check if substring is contained in string
     *
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    function str_contains($haystack, $needle) {
        return (strpos($haystack, $needle) !== false);
    }
}

 
///////////////////// FUNCIONES DE VALIDACION

function checkFirma($firma_local, $firma_remota) {
	if ($firma_local == $firma_remota)
		return 1;
	else
		return 0;
}

//Importe

function checkImporte($total) {
	return preg_match("/^\d+$/", $total);
}
 
//Pedido
function checkPedidoNum($pedido) {
	return preg_match("/^\d{1,12}$/", $pedido);
}
function checkPedidoAlfaNum($pedido, $pedidoExtendido = false) {
	if ($pedidoExtendido)
		return preg_match("/^\w{4,256}$/", $pedido);
	else
		return preg_match("/^\w{4,12}$/", $pedido);
}

//Fuc
function checkFuc($codigo) {
	$retVal = preg_match("/^\d{2,9}$/", $codigo);
	if($retVal) {
		$codigo = str_pad($codigo,9,"0",STR_PAD_LEFT);
		$fuc = intval($codigo);
		$check = substr($codigo, -1);
		$fucTemp = substr($codigo, 0, -1);
		$acumulador = 0;
		$tempo = 0;
		
		for ($i = strlen($fucTemp)-1; $i >= 0; $i-=2) {
			$temp = intval(substr($fucTemp, $i, 1)) * 2;
			$acumulador += intval($temp/10) + ($temp%10);
			if($i > 0) {
				$acumulador += intval(substr($fucTemp,$i-1,1));
			}
		}
		$ultimaCifra = $acumulador % 10;
		$resultado = 0;
		if($ultimaCifra != 0) {
			$resultado = 10 - $ultimaCifra;
		}
		$retVal = $resultado == $check;
	}
	return $retVal;
}

//Moneda
function checkMoneda($moneda) {
   return preg_match("/^\d{1,3}$/", $moneda);
}

//Respuesta
function checkRespuesta($respuesta) {
   return preg_match("/^\d{1,4}$/", $respuesta);
}

//Firma
function checkFirmaComposicion($firma) {
   return preg_match("/^[a-zA-Z0-9\/+]{32}$/", $firma);
}

//AutCode
function checkAutCode($id_trans) {
	return preg_match("/^.{0,6}$/", $id_trans);
}

//Nombre del Comecio
function checkNombreComecio($nombre) {
	return preg_match("/^\w*$/", $nombre);
}

//Terminal
function checkTerminal($terminal) {
	return preg_match("/^\d{1,3}$/", $terminal);
}

function getVersionClave() {
	return "HMAC_SHA256_V1";
}

///////////////////// FUNCIONES DE LOG

function make_seed() {

  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);

}

function iniciarLog($logLevel, $idLog) {

	$GLOBALS["logLevel"] = (int)$logLevel;
	return $idLog;

}

function generateIdLog($logLevel, $logString, $idCart = NULL, $force = false) {

	($idCart == NULL) ? srand(make_seed()) : srand(intval($idCart));

	$stringLength = strlen ( $logString );
	$idLog = '';

	for($i = 0; $i < 30; $i ++) {

		$idLog .= $logString [rand ( 0, $stringLength - 1 )];
	}
	
	$GLOBALS["logLevel"] = (int)$logLevel;
	return $idLog;

}

function createMerchantTitular($nombre, $apellidos, $email) {
		
	$nombreCompleto  = $nombre . " " . $apellidos;
	$nombreAbreviado = mb_substr($nombre, 0, 1) . ". " . $apellidos;
	
	if (empty($email))
		return $nombreCompleto;

	$nombreEmail = $nombreAbreviado . " | " . $email;

	if (strlen($nombreEmail) > 70)
		return $email;
	else
		return $nombreEmail;

}


function escribirLog($tipo, $idLog, $texto, $logLevel = NULL, $method = NULL) {

	$logfilename = dirname(__FILE__).'/../logs/redsysLog.log';
	$level = $logLevel ?: $GLOBALS["logLevel"];

	(is_null($method)) ? ($methodLog = "") : ($methodLog = $method . " -- ");

	switch ($level) {
		case 0:
			
			if ($tipo == "ERROR")
				file_put_contents($logfilename, date('M d Y G:i:s') . ' -- [' . $tipo . ']'  . ' -- ' . $idLog . ' -- ' . $methodLog . $texto . "\r\n", is_file($logfilename)?FILE_APPEND:0);
			
			break;

		case 1:
		
			if ($tipo == "ERROR" || $tipo == "INFO ")
				file_put_contents($logfilename, date('M d Y G:i:s') . ' -- [' . $tipo . ']'  . ' -- ' . $idLog . ' -- ' . $methodLog . $texto . "\r\n", is_file($logfilename)?FILE_APPEND:0);
			
			break;

		case 2:
	
			if ($tipo == "ERROR" || $tipo == "INFO " || $tipo == "DEBUG")
				file_put_contents($logfilename, date('M d Y G:i:s') . ' -- [' . $tipo . ']'  . ' -- ' . $idLog . ' -- ' . $methodLog . $texto . "\r\n", is_file($logfilename)?FILE_APPEND:0);
			
			break;
		
		default:
			# Nothing to do here...
			break;
	}
}

function array_to_xml($array, &$xml_user_info) {
    foreach($array as $key => $value) {
        if(is_array($value)) {
            if(!is_numeric($key)){
                $subnode = $xml_user_info->addChild("$key");
                array_to_xml($value, $subnode);
            }else{
                $subnode = $xml_user_info->addChild("item$key");
                array_to_xml($value, $subnode);
            }
        }else {
            $xml_user_info->addChild("$key",htmlspecialchars("$value"));
        }
    }
}

/** ENCODES ADICIONALES */

function b64url_encode($data) {
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function b64url_decode($data) {
	return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}