<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$route['default_controller'] = 'servicio_controllers';
$route['sku/(any:)/(num:)'] = 'Servicio_controllers/update_sku/$1/$2'; // primer valor tipo de operacion, segundo valor numero de factura
// $route['sku/(num:)'] = 'Servicio_controllers/update_sku/$1';
// $route['rest_sku/(num:)'] = 'Servicio_controllers/substract_sku/$1';



$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;