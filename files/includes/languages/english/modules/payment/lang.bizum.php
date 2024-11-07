<?php

declare(strict_types=1);
/**
 * Bizum Payment Module
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @updated 05/11/2024
 */
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

$define = [
      'MODULE_PAYMENT_BIZUM_TEXT_TITLE' => 'Bizum',
    //admin description
    // image link is hard-coded instead of using the admin constant as admin constant does not exist storefront
      'MODULE_PAYMENT_BIZUM_TEXT_DESCRIPTION' => '<strong>Description:</strong><br>Pay via Bizum<br><img src="../images/modules/payment/bizum.png" alt="logo Bizum">',
    //storefront checkout_payment (payment selection page), title/description is inserted via
    //includes\modules\payment\bizum.php: function selection()
     'MODULE_PAYMENT_BIZUM_TEXT_DESCRIPTION_STOREFRONT_CHECKOUT_PAYMENT' => '<img src="' . DIR_WS_IMAGES . 'modules/payment/AF_BIZUM_BOTON_100x30_GRIS-02.png" alt="logo Bizum"> <span class="biggerText">Bizum instant payment by mobile phone</span>',
    //storefront checkout_payment (payment selection page), description suffixed to
    //includes\modules\payment\bizum.php: function selection()
    'MODULE_PAYMENT_BIZUM_TEXT_DESCRIPTION_STOREFRONT_CHECKOUT_CONFIRMATION' => '<img src="' . DIR_WS_IMAGES . 'modules/payment/bizum.png" alt="logo Bizum">',
      'MODULE_PAYMENT_BIZUM_TEXT_ERROR_MESSAGE' => 'Processing error',
      'MODULE_PAYMENT_BIZUM_TEXT_CANCEL' => 'Process Cancelled',
      'MODULE_PAYMENT_BIZUM_ERROR_NOT_CONFIGURED' => 'data missing (module is disabled)'
];

return $define;
