<?php

///// 3DSecure | TABLA 4 - Json Object acctInfo

//// chAccAgeInd & chAccDate | En OsCommerce no se pueden hacer pedidos sin estar registrado
$customerQuery				= tep_db_query_rds("SELECT customers_info_date_account_created FROM " . TABLE_CUSTOMERS_INFO . " WHERE customers_info_id = '" . $_SESSION['customer_id'] . "';");
$customerInfo				= $customerQuery->fields;
$accountCreated				= intval( (strtotime("now") - strtotime($customerInfo['customers_info_date_account_created']))/60 );
$nDays						= intval( $accountCreated/1440 );

$dt							= new DateTime($customerInfo['customers_info_date_account_created']);
$chAccDate					= $dt->format('Ymd');

if ($accountCreated < 20) {
	$chAccAgeInd 			= "02";
}
elseif ($nDays < 30) {
	$chAccAgeInd 			= "03";
}
elseif ($nDays >= 30 && $nDays <= 60) {
	$chAccAgeInd 			= "04";
}
else {
	$chAccAgeInd 			= "05";
}

//// chAccChange & chAccChangeInd
$customerQuery				= tep_db_query_rds("SELECT customers_info_date_account_last_modified FROM " . TABLE_CUSTOMERS_INFO . " WHERE customers_info_id = '" . $_SESSION['customer_id'] . "';");
$customerInfo				= $customerInfo->fields;
$accountModified			= intval( (strtotime("now") - ($customerInfo['customers_info_date_account_last_modified']?strtotime($customerInfo['customers_info_date_account_last_modified']):strtotime("now")) )/60 );

$dt							= new DateTime($customerInfo['customers_info_date_account_last_modified']);
$chAccChange				= $dt->format('Ymd');

if ($accountModified < 20) {
	$chAccChangeInd 		= "01";
}
elseif ($nDays < 30) {
	$chAccChangeInd			= "02";
}
elseif ($nDays >= 30 && $nDays <= 60) {
	$chAccChangeInd			= "03";
}
else {
	$chAccChangeInd			= "04";
}

//// chAccPwChange			| No se puede sacar este dato
// $chAccPwChange			= "";

//// chAccPwChangeInd		| No se puede sacar este dato
// $chAccPwChangeInd		= "";

//// nbPurchaseAccount
$fechaBase					= strtotime("-6 month");
$dt 						= new DateTime("@$fechaBase");
$estadoCompletado			= 3;
$ordersQuery				= tep_db_query_rds("SELECT * FROM " . TABLE_ORDERS . " WHERE customers_id = '" . $_SESSION['customer_id'] . "' AND date_purchased > '" . $dt->format('Y-m-d H:i:s') . "' AND orders_status = '" . $estadoCompletado . "';");
$nbPurchaseAccount			= $ordersQuery->resource->num_rows;

//// provisionAttemptsDay	| No se puede sacar este dato
// $provisionAttemptsDay	= "";

//// txnActivityDay
$fechaBase					= strtotime("-1 day");
$dt 						= new DateTime("@$fechaBase");
$ordersQuery				= tep_db_query_rds("SELECT * FROM " . TABLE_ORDERS . " WHERE customers_id = '" . $_SESSION['customer_id'] . "' AND date_purchased > '" . $dt->format('Y-m-d H:i:s') . "' AND orders_status = '" . $estadoCompletado . "';");
$txnActivityDay				= $ordersQuery->resource->num_rows;

//// txnActivityYear
$fechaBase					= strtotime("-1 year");
$dt 						= new DateTime("@$fechaBase");
$ordersQuery				= tep_db_query_rds("SELECT * FROM " . TABLE_ORDERS . " WHERE customers_id = '" . $_SESSION['customer_id'] . "' AND date_purchased > '" . $dt->format('Y-m-d H:i:s') . "' AND orders_status = '" . $estadoCompletado . "';");
$txnActivityYear			= $ordersQuery->resource->num_rows;

//// paymentAccAge			| No se puede sacar este dato
// $paymentAccAge			= "";

//// paymentAccInd			| No se puede sacar este dato
// $paymentAccInd			= "";

//// shipAddressUsage & shipAddressUsageInd
if ($order->delivery['street_address'] != null) {
	$delivery_street_address	= tep_sanitize_string_rds($order->delivery['street_address']);
	$delivery_suburb			= tep_sanitize_string_rds($order->delivery['suburb']);
	$delivery_city				= tep_sanitize_string_rds($order->delivery['city']);
	$delivery_postcode			= tep_sanitize_string_rds($order->delivery['postcode']);
	$delivery_state				= tep_sanitize_string_rds($order->delivery['state']);
	$delivery_country			= tep_sanitize_string_rds($order->delivery['country']['title']);
	$ordersQuery				= tep_db_query_rds("SELECT date_purchased FROM " . TABLE_ORDERS . " WHERE delivery_street_address = '" . $delivery_street_address . "' AND delivery_suburb = '" . $delivery_suburb . "' AND delivery_city = '" . $delivery_city . "' AND delivery_postcode = '" . $delivery_postcode . "' AND delivery_state = '" . $delivery_state . "' AND delivery_country = '" . $delivery_country . "' ORDER BY date_purchased;");
	if ($ordersQuery->resource->num_rows != 0){
		$queryResult			= $ordersQuery->fields['date_purchased'];
		$dt						= new DateTime($queryResult);
		$shipAddressUsage		= $dt->format('Ymd');
		
		$duringTransaction		= intval( (strtotime("now") - strtotime($queryResult))/60 );
		$nDays 					= intval($duringTransaction/1440);
		if ($nDays < 30) {
			$shipAddressUsageInd = "02";
		}
		elseif ($nDays >= 30 && $nDays <= 60) {
			$shipAddressUsageInd = "03";
		}
		else {
			$shipAddressUsageInd = "04";
		}
	}
	else {
		$fechaBase				= strtotime("now");
		$dt						= new DateTime("@$fechaBase");
		$shipAddressUsage		= $dt->format('Ymd');
		$shipAddressUsageInd	= "01";
	}
}

//// shipNameIndicator		| No se puede sacar este dato
// $shipNameIndicator		= "";

//// suspiciousAccActivity	| No se puede sacar este dato
// $suspiciousAccActivity	= "";

///// 3DSecure | FIN TABLA 4


///// 3DSecure | TABLA 1 - Ds_Merchant_EMV3DS (json Object)
//// addrMatch
if ($order->delivery['street_address'] != null) {
	if (
		($order->delivery['street_address'] == $order->billing['street_address'])
		&&
		($order->delivery['suburb'] == $order->billing['suburb'])
		&&
		($order->delivery['city'] == $order->billing['city'])
		&&
		($order->delivery['postcode'] == $order->billing['postcode'])
		&&
		($order->delivery['state'] == $order->billing['state'])
		&&
		($order->delivery['country_id'] == $order->billing['country_id'])	
		) {
		$addrMatch 				= "Y";
	}
	else {
		$addrMatch				= "N";
	}
}
else {
	$addrMatch					= "N";
}

//// billAddrCity
$billAddrCity				= $order->billing['city'];

//// billAddrCountry
$billAddrCountry 			= $order->billing['country']['id'];

//// billAddrLine1
$billAddrLine1 				= $order->billing['street_address'];

//// billAddrLine2			| No se puede sacar este dato
//$billAddrLine2			= "";

//// billAddrLine3			| No se puede sacar este dato
// $billAddrLine3			= "";

//// billAddrPostCode
$billAddrPostCode			= $order->billing['postcode'];

//// billAddrState			| No se puede sacar este dato
//$billAddrState			= $order->billing['state'];

//// Email
$Email						= $order->customer['email_address'];

//// homePhone
$homePhone					= array("subscriber" => $order->customer['telephone'], "cc" => "34");

//// mobilePhone
// $mobilePhone				= "";

//// cardholderName 		| No se puede sacar este dato
// $cardholderName			= "";

if ($order->delivery['street_address'] != null){
	//// shipAddrCity
	$shipAddrCity 			= $order->delivery['city'];
	
	//// shipAddrCountry
	$shipAddrCountry 		= $order->delivery['country']['id'];
	
	//// shipAddrLine1
	$shipAddrLine1 			= $order->delivery['street_address'];
	
	//// shipAddrLine2		| No se puede sacar este dato
	//$shipAddrLine2		= "";
	
	//// shipAddrLine3		| No se puede sacar este dato
	// $shipAddrLine3		= "";
	
	//// shipAddrPostCode
	$shipAddrPostCode		= $order->delivery['postcode'];
	
	//// shipAddrState		| No se puede sacar este dato
	//$shipAddrState		= $order->delivery['state'];
}

//// homePhone
// $homePhone				= "";

//// threeDSRequestorAuthenticationInfo | No lo ponemos

//// acctInfo					| información de la TABLA 4
$acctInfo						= array(
	'chAccAgeInd'				=> strval($chAccAgeInd),
	'chAccDate'					=> strval($chAccChange),
	'chAccChange'				=> strval($chAccChange),
	'chAccChangeInd'			=> strval($chAccChangeInd),
	'nbPurchaseAccount'			=> strval($nbPurchaseAccount),
	'txnActivityDay'			=> strval($txnActivityDay),
	'txnActivityYear'			=> strval($txnActivityYear),
);
if ($order->delivery['street_address'] != null) {
	$acctInfo['shipAddressUsage'] 		= strval($shipAddressUsage);
	$acctInfo['shipAddressUsageInd']	= strval($shipAddressUsageInd);
}

//// purchaseInstalData		| No se puede sacar este dato
// $purchaseInstalData		= "";

//// recurringExpiry		| No se puede sacar este dato
// $recurringExpiry			= "";

//// recurringFrequency		| No se puede sacar este dato
// $recurringFrequency		= "";

//// merchantRiskIndicator	| No se puede sacar este dato
// $merchantRiskIndicator   = array();

//// challengeWindowSize	| No se puede sacar este dato
// $challengeWindowSize 	= "";


///// 3DSecure | FIN TABLA 1

///// 3DSecure | Insertamos el parámetro "Ds_Merchant_EMV3DS" en $miObj

$Ds_Merchant_EMV3DS			= array(
	'addrMatch'				=> $addrMatch,
	'billAddrCity'			=> $billAddrCity,
	'billAddrCountry'		=> $billAddrCountry,
	'billAddrLine1'			=> $billAddrLine1,
	'billAddrPostCode'		=> $billAddrPostCode,
	'email'					=> $Email,
	'homePhone'				=> $homePhone,
	'acctInfo'				=> $acctInfo
);

if ($order->delivery['street_address'] != null) {
	$Ds_Merchant_EMV3DS['shipAddrCity']		= $shipAddrCity;
	$Ds_Merchant_EMV3DS['shipAddrCountry']	= $shipAddrCountry;
	$Ds_Merchant_EMV3DS['shipAddrLine1']	= $shipAddrLine1;
	$Ds_Merchant_EMV3DS['shipAddrPostCode']	= $shipAddrPostCode;
	// $Ds_Merchant_EMV3DS['acctInfo']			= array(
	// 	'shipAddressUsage'					=> $shipAddressUsage,
	// 	'shipAddressUsageInd'				=> $shipAddressUsageInd
	// );
}

$Ds_Merchant_EMV3DS 		= json_encode($Ds_Merchant_EMV3DS);

$miObj->setParameter("Ds_Merchant_EMV3DS", $Ds_Merchant_EMV3DS);

?>
