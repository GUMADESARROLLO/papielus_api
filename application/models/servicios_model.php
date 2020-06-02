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
        
       
        switch($operacion) {
            case 'suma_sku':
            $skuMayorACantLocal = true;
                foreach ($ObtItemsExistentesEnRemotoYLocal as $key) {
                    $retVal = 0;
                    $retVal = floatval($key["SKU_LOCAL"]) + floatval($key["SKU_REMOTO"]); 

                    $Sku_update_batch[] = array(
                        'post_id'   => $key["ID_POST"],
                        'meta_value' => number_format($retVal,0)
                    );


                    $Sku_update_batch_wp_wc_product[] = array(
                        'product_id' => $key["ID_POST"],
                        'sku' => number_format($retVal,0)
                    );
                    
                }
                break;
            
            case 'resta_sku':
                foreach ($ObtItemsExistentesEnRemotoYLocal as $key) {
                    $retVal = 0;
                    if (floatval($key["SKU_REMOTO"]) > floatval($key["SKU_LOCAL"])) {
                        $skuMayorACantLocal = true;
                        $retVal = floatval($key["SKU_REMOTO"]) - floatval($key["SKU_LOCAL"]); 

                        $Sku_update_batch[] = array(
                            'post_id'   => $key["ID_POST"],
                            'meta_value' => number_format($retVal,0)
                        );


                        $Sku_update_batch_wp_wc_product[] = array(
                            'product_id' => $key["ID_POST"],
                            'sku' => number_format($retVal,0)
                        );
                    }else{
                        $skuMayorACantLocal = false;
                        break;
                    }
                  
                    
                }
                break;
        }
       
        
        if ($skuMayorACantLocal == true) {
            $this->db->where('meta_key','_sku');
            $this->db->update_batch('wp_postmeta',$Sku_update_batch,'post_id');

            echo '<br>';


            $this->db->update_batch('wp_wc_product_meta_lookup',$Sku_update_batch_wp_wc_product,'product_id');
            print_r($Sku_update_batch_wp_wc_product);
        }else{
            echo 'No se puede realizar la operacion,  la cantidad de producto en uno o mas items de factura es mayor a la cantidad de sku en servidor remoto';
        }
        

    }


    //retorna array con campos de item existente en bae de datos local "Produccion" y remota
    private function compararItemsExistentesEnRemotoYLocal($lineasDeFact, $articulosRem){
        $Decimal = 4;
        $codigoExisteEnRemotoYLocal = array();
        $lineasDeFactura = array();
        $articulosRemotos = array();
        $lineasDeFactura = json_decode(json_encode($lineasDeFact),true);
        $articulosRemotos = json_decode(json_encode($articulosRem),true);

        $j = 0;

        foreach ($articulosRemotos as $remoteKey) {
            

            foreach ($lineasDeFactura as $ItemFacKey) {
                 if ($remoteKey["Codigo"] == $ItemFacKey["CODIGO"]) {
                       
                        $codigoExisteEnRemotoYLocal[$j]["CODIGO"] = $ItemFacKey["CODIGO"];
                        $codigoExisteEnRemotoYLocal[$j]["ID_POST"] = $remoteKey["ID"];
                        $codigoExisteEnRemotoYLocal[$j]["DESCRIPCION"] = $ItemFacKey["DESCRIPCION"];
                        $codigoExisteEnRemotoYLocal[$j]["SKU_LOCAL"] = $ItemFacKey["CANTIDAD"];
                        $codigoExisteEnRemotoYLocal[$j]["SKU_REMOTO"] = $remoteKey["sku"];
                        $j++;
                }
            } 
        }
       
        return $codigoExisteEnRemotoYLocal;
    }

    // Obtenre articulos por numero de Codigo en base remota
    private function obtenerArticulosRemotos(){
        $articulosRemotos = array();
        $db_asterisk = $this->load->database('db_remota', TRUE);
        
        $queryRemota = $db_asterisk->get('view_info_sku');
       
        if ($queryRemota->num_rows()>0) {
            foreach ($queryRemota->result() as $key) {
                $articulosRemotos[] = $key;
            } 
        }

        return $articulosRemotos;
    }


    // Obtener articulos por numero de factura en base local de datos Produccion
    private function obtenerLineasDeFacturas($numFact){
        $Decimal = 4;
        $lineasDeFactura = array();
        $j = 0;
     
        $queryDetFact = "SELECT ARTICULO, DESCRIPCION, SUM(CANTIDAD) as CANTIDAD FROM PRODUN_HOLDINGS_FACTURAS where FACTURA = ".$numFact."  GROUP BY ARTICULO, DESCRIPCION ";        
        $query_sku_facturado = $this->sqlsrv->fetchArray($queryDetFact,SQLSRV_FETCH_ASSOC);

         foreach ($query_sku_facturado as $key) {
            $lineasDeFactura[$j]["CODIGO"] = $key["ARTICULO"];
            $lineasDeFactura[$j]["DESCRIPCION"] = $key["DESCRIPCION"];
            $lineasDeFactura[$j]["CANTIDAD"] = number_format($key["CANTIDAD"],$Decimal);
            $j++;
                
        }
        return $lineasDeFactura;
    }
     


       
         //$db_asterisk = $this->load->database('db_remota', TRUE);
       /*$db_asterisk = $this->load->database('papielusdb', TRUE);
        
        $db_asterisk->where('Codigo', '6IN00085');//dejarlo

        $query = $db_asterisk->get('view_info_sku');

        $Decimal                        = 4;
        $Sku_update_batch               = array();
        $Cadena_sku_codigo              = "";
        $Sku_update_batch_wp_wc_product = array();

        if($query->num_rows() > 0 ) {
            foreach ($query->result_array() as $key){
                $Cadena_sku_codigo .= "'".$key['Codigo']."',";
            }
        }

        $Cadena_sku_codigo = substr($Cadena_sku_codigo,0,-1);
       $QueryBuild = "SELECT * FROM inn_iweb_articulos where ARTICULO in (".$Cadena_sku_codigo.")";
        $query_sku_existencia = $this->sqlsrv->fetchArray($QueryBuild,SQLSRV_FETCH_ASSOC);

            foreach ($query_sku_existencia as $key){

                $Existencia         = number_format($key['total'],$Decimal);
                $FACTOR_EMPAQUE     = number_format($key['FACTOR_EMPAQUE'],$Decimal);
                $found_key = array_search($key['ARTICULO'], array_column($query->result_array(), 'Codigo'));
                $ID_Post = $query->result_array()[$found_key]['ID'];

                $retVal = ($Existencia==0) ? $Existencia : $Existencia / $FACTOR_EMPAQUE ;

                echo $ID_Post.' | '.$key['ARTICULO'].' | '.$key['DESCRIPCION'].' | '.number_format($key['total'],$Decimal).' | '.$FACTOR_EMPAQUE.' | '.number_format($retVal,0).'<br><br>';


                $Sku_update_batch[] = array(
                    'post_id'   => $ID_Post,
                    'meta_value' => number_format($retVal,0)
                );



                $Sku_update_batch_wp_wc_product[] = array(
                    'product_id' => $ID_Post,
                    'sku' => number_format($retVal,0)
                );

            }*/



        /*$db_asterisk->where('meta_key','_sku');
        $db_asterisk->update_batch('wp_postmeta',$Sku_update_batch,'post_id');


        $db_asterisk->update_batch('wp_wc_product_meta_lookup',$Sku_update_batch_wp_wc_product,'product_id');*/


      /* $this->db->where('meta_key','_sku');// esto dejarlo sin comentario
        $this->db->update_batch('wp_postmeta',$Sku_update_batch,'post_id');//esto dejarlo sin comentario*/

        //echo $this->db->last_query();

        //echo '<br>';// esto dejarlo sin comentario


      /*  $this->db->update_batch('wp_wc_product_meta_lookup',$Sku_update_batch_wp_wc_product,'product_id');//esto dejarlo sin comentario*/
     //   echo $this->db->last_query();*/

       
      //recuperar todos los articulos del servidor remoto 
    

}?>
