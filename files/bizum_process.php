<?php

declare(strict_types=1);
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

if(!class_exists('RedsysAPI')) {
	require_once('includes/modules/payment/apiRedsys/apiRedsysFinal.php');
}

if (!empty( $_POST ) ) {//URL DE RESP. ONLINE

	// Se crea Objeto
	$miObj = new RedsysAPI();

	/** Se decodifican los datos enviados y se carga el array de datos **/
	$datos = $_POST['Ds_MerchantParameters'];
	$dDatos = $miObj->decodeMerchantParameters($datos);
	$miObj->stringToArray($dDatos);

	# Recogida de parámetros
	$postString= 'Ds_SignatureVersion=' . $_POST['Ds_SignatureVersion'] .
        '&Ds_MerchantParameters=' . $_POST['Ds_MerchantParameters'] .
        '&Ds_Signature=' . $_POST['Ds_Signature'] .
        '&zenid=' . $miObj->getParameter('Ds_MerchantData');

	# Opciones del context
	$opts = [
        'http' =>
		[
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $postString
        ]
    ];

	# Creamos el context
	$context = stream_context_create($opts);
	# URL de verificación
	$home = explode('/', $_SERVER['REQUEST_URI']);
	$dest = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $home[1]. '/index.php?main_page=checkout_process';
	# Get the response
	$result = file_get_contents($dest, false, $context);
}
