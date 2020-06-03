<?php
class servicios_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->database();
    }


    public function update_sku($operacion, $numFact) { //operacion = resta_sku o suma_sku

        $queryDetFact                       ="";
        $Decimal                            = 4;
        $Sku_update_batch                   = array();
        $Cadena_sku_codigo                  = array();
        $articulosRemotos                   = array();
        $lineasDeFactura                    = array();
        $Sku_update_batch_wp_wc_product     = array();
        $ObtItemsExistentesEnRemotoYLocal   = array();

        //variables usadas para verificar si el sku de un item remot es menor al la cantidad restada del item en factura local 
        $skuMayorACantLocal                = true;
        

        $lineasDeFactura                    = $this->obtenerLineasDeFacturas($numFact);
        $articulosRemotos                   = $this->obtenerArticulosRemotos();
        $ObtItemsExistentesEnRemotoYLocal      = $this->compararItemsExistentesEnRemotoYLocal($lineasDeFactura, $articulosRemotos);
        //print_r($ObtItemsExistentesEnRemotoYLocal);


        if ($operacion == 'suma_sku') {
            $skuMayorACantLocal = true;
            foreach ($ObtItemsExistentesEnRemotoYLocal as $key) {
                $retVal = 0;
                $retVal = floatval($key["SKU_Local"]) + floatval($key["SKU_REMOTO"]); 

                $Sku_update_batch[] = array(
                    'post_id'   => $key["ID_POST"],
                    'meta_value' => number_format($retVal,0)
                );


                $Sku_update_batch_wp_wc_product[] = array(
                    'product_id' => $key["ID_POST"],
                    'stock_quantity' => number_format($retVal,0)
                );
                
            }
        }else if($operacion == 'resta_sku'){
            foreach ($ObtItemsExistentesEnRemotoYLocal as $key) {
                $retVal = 0;
                if (floatval($key["SKU_REMOTO"]) > floatval($key["SKU_Local"])) {
                    $skuMayorACantLocal = true;
                    $retVal = floatval($key["SKU_REMOTO"]) - floatval($key["SKU_Local"]); 

                    $Sku_update_batch[] = array(
                        'post_id'   => $key["ID_POST"],
                        'meta_value' => number_format($retVal,0)
                    );


                    $Sku_update_batch_wp_wc_product[] = array(
                        'product_id' => $key["ID_POST"],
                        'stock_quantity' => number_format($retVal,0)
                    );
                }else{
                    $skuMayorACantLocal = false;
                    break;
                }
              
                
            }
        }else{
            echo 'La operacion selecionada no se puede realizar';
        }
        
       
        // switch($operacion) {
        //     case 'suma_sku':
        //         foreach ($ObtItemsExistentesEnRemotoYLocal as $key) {
        //             $retVal = 0;
        //             $retVal = floatval($key["SKU_LOCAL"]) + floatval($key["SKU_REMOTO"]); 

        //             $Sku_update_batch[] = array(
        //                 'post_id'   => $key["ID_POST"],
        //                 'meta_value' => number_format($retVal,0)
        //             );


        //             $Sku_update_batch_wp_wc_product[] = array(
        //                 'product_id' => $key["ID_POST"],
        //                 'sku' => number_format($retVal,0)
        //             );
                    
        //         }
        //         break;
            
        //     case 'resta_sku':
        //         foreach ($ObtItemsExistentesEnRemotoYLocal as $key) {
        //             $retVal = 0;
        //             if (floatval($key["SKU_REMOTO"]) > floatval($key["SKU_LOCAL"])) {
        //                 $skuMayorACantLocal = true;
        //                 $retVal = floatval($key["SKU_REMOTO"]) - floatval($key["SKU_LOCAL"]); 

        //                 $Sku_update_batch[] = array(
        //                     'post_id'   => $key["ID_POST"],
        //                     'meta_value' => number_format($retVal,0)
        //                 );


        //                 $Sku_update_batch_wp_wc_product[] = array(
        //                     'product_id' => $key["ID_POST"],
        //                     'stock_quantity' => number_format($retVal,0)
        //                 );
        //             }else{
        //                 $skuMayorACantLocal = false;
        //                 break;
        //             }
                  
                    
        //         }
        //         break;
        // }
       
        
        if ($skuMayorACantLocal == true) {
            $this->db->where('meta_key','_sku');
            $this->db->update_batch('wp_postmeta',$Sku_update_batch,'post_id');

            echo '<br>';

            $this->db->update_batch('wp_wc_product_meta_lookup',$Sku_update_batch_wp_wc_product,'product_id');
            
        }else{
            echo 'No se puede realizar la operacion,  la cantidad de producto en uno o mas items de factura es mayor a la cantidad de sku en servidor remoto';
        }
        

    }





// Obtener articulos por numero de factura en base local de datos Produccion
    private function obtenerLineasDeFacturas($numFact){
        $Decimal = 4;
        $j = 0;
     
        $queryDetFact = "SELECT ARTICULO, DESCRIPCION, SUM(CANTIDAD) as CANTIDAD FROM PRODUN_HOLDINGS_FACTURAS where FACTURA = ".$numFact."  GROUP BY ARTICULO, DESCRIPCION ";        
        $query_lineas_facturado = $this->sqlsrv->fetchArray($queryDetFact,SQLSRV_FETCH_ASSOC);

        //  foreach ($query_sku_facturado as $key) {
        //     $lineasDeFactura[$j]["CODIGO"] = $key["ARTICULO"];
        //     $lineasDeFactura[$j]["DESCRIPCION"] = $key["DESCRIPCION"];
        //     $lineasDeFactura[$j]["CANTIDAD"] = number_format($key["CANTIDAD"],$Decimal);
        //     $j++;
                
        // }
        return $query_lineas_facturado;
    }







    // Obtenre articulos por numero de Codigo en base remota


    private function obtenerArticulosRemotos(){
       
        $db_asterisk = $this->load->database('db_remota', TRUE);
        
        $queryRemota = $db_asterisk->get('view_info_sku');

        //  if ($queryRemota->num_rows()>0) {
        //     foreach ($queryRemota->result() as $key) {
        //         $articulosRemotos[] = $key;
        //     } 
        // }

        $articulosRemotos = $queryRemota->result_array();// convertir stdClass a array
       
        return $articulosRemotos;
    }







    //retorna array con campos de item existente en bae de datos local "Produccion" y remota
    private function compararItemsExistentesEnRemotoYLocal($lineasDeFact, $articulosRem){
        $Decimal = 4;
        $codigoExisteEnRemotoYLocal = array();
        $lineasDeFactura = array();
        $articulosRemotos = array();
        $lineasDeFactura = $lineasDeFact;
        $articulosRemotos = $articulosRem;
        $j = 0;


        for ($i=0; $i < count($articulosRemotos); $i++) { 

           // array_search($articulosRemotos[$i]['CODIGO'], $lineasDeFactura);
            if($this->in_array_r($articulosRemotos[$i]['Codigo'], $lineasDeFactura)){
                $codigoExisteEnRemotoYLocal[$j]['Codigo'] = $articulosRemotos[$i]["Codigo"];
                $codigoExisteEnRemotoYLocal[$j]["ID_POST"] = $articulosRemotos[$i]["ID"];
                $codigoExisteEnRemotoYLocal[$j]["DESCRIPCION"] = $articulosRemotos[$i]["Nombre"];
                if (empty($articulosRemotos[$i]["stock_quantity"])){
                     $codigoExisteEnRemotoYLocal[$j]["SKU_REMOTO"] =   0;
                }else{
                    $codigoExisteEnRemotoYLocal[$j]["SKU_REMOTO"] = $articulosRemotos[$i]["stock_quantity"];
                }
                $index= "";
                $palabra_a_buscar   = ($articulosRemotos[$i]['Codigo']);
                foreach($lineasDeFactura as $key=>$value){
                    $indice = array_search($palabra_a_buscar,$value);
                    if($indice){
                        $index=$key;
                    }
                }
                
                $codigoExisteEnRemotoYLocal[$j]["SKU_Local"] = $lineasDeFactura[$index]["CANTIDAD"];
                $j++;
            }
        }
        
        //$j = 0;

        // foreach ($articulosRemotos as $remoteKey) {
            

        //     foreach ($lineasDeFactura as $ItemFacKey) {
        //          if ($remoteKey["Codigo"] == $ItemFacKey["ARTICULO"]) {
                       
        //                 $codigoExisteEnRemotoYLocal[$j]["CODIGO"] = $ItemFacKey["ARTICULO"];
        //                 $codigoExisteEnRemotoYLocal[$j]["ID_POST"] = $remoteKey["ID"];
        //                 $codigoExisteEnRemotoYLocal[$j]["DESCRIPCION"] = $ItemFacKey["DESCRIPCION"];
        //                 $codigoExisteEnRemotoYLocal[$j]["SKU_LOCAL"] = $ItemFacKey["CANTIDAD"];
        //                 $codigoExisteEnRemotoYLocal[$j]["SKU_REMOTO"] = $remoteKey["sku"];
        //                 $j++;
        //         }
        //     } 
        // }
       
        return $codigoExisteEnRemotoYLocal;
    }

    private function in_array_r($item , $array){
        return preg_match('/"'.preg_quote($item, '/').'"/i' , json_encode($array));
    }



}?>
