a) 3.0.1

b) 3.1.1:
new file redsys_3ds.php added.
No other changes.

c) apiRedsys Pasarela Unificada para Prestashop 4.2.1

apiRedsysFinal.php: 
function decodeMerchantParameters($datos){
has this line added to convert to array
$this->stringToArray($decodec);

redsys_3ds.php is not included

a lot of changes, especially in logging.