<?php
class servicios_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

    public function update_sku($operacion, $numFact) { //operacion = resta_sku o suma_sku

        $db_asterisk = $this->load->database('db_remota', TRUE);

        $Sku_update_batch                   = array();
        $articulosRemotos                   = array();
        $lineasDeFactura                    = array();
        $Sku_update_batch_wp_wc_product     = array();

        $lineasDeFactura                    = $this->obtenerLineasDeFacturas($numFact);
        $articulosRemotos                   = $this->obtenerArticulosRemotos($db_asterisk);
        
        foreach ($lineasDeFactura as $key){
            $Codigo             = $key['ARTICULO'];
            $ExistenciaLocal    = $key['CANTIDAD'];
            $found_key          = array_search($key['ARTICULO'], array_column($articulosRemotos, 'Codigo'));
            $ID_Post            = $articulosRemotos[$found_key]['ID'];
            
            if($operacion == 'suma_sku'){
                $resVal = floatval($articulosRemotos[$found_key]['stock_quantity']) + floatval($ExistenciaLocal);
            } else if($operacion == 'resta_sku'){
                 $resVal = floatval($articulosRemotos[$found_key]['stock_quantity']) - floatval($ExistenciaLocal);
            } 

           print('POST ID:'.$ID_Post.' <br> ARTICULO:'.$Codigo.' <br>Existencia Local: '.$ExistenciaLocal.' <br> Existencia remota: '.$articulosRemotos[$found_key]['stock_quantity'].'<br> TOTAL_SUMA_RESTA: '.$resVal.'<br><br>');

            $Sku_update_batch[] = array(
                'post_id'   => $ID_Post,
                'meta_value' => number_format($resVal,0)
            );


            $Sku_update_batch_wp_wc_product[] = array(
                'product_id' => $ID_Post,
                'stock_quantity' => $resVal
            );

        }

        $db_asterisk->where('meta_key','_sku');
        $db_asterisk->update_batch('wp_postmeta',$Sku_update_batch,'post_id');

        echo '<br>';

        $db_asterisk->update_batch('wp_wc_product_meta_lookup',$Sku_update_batch_wp_wc_product,'product_id');
       
    }

// Obtener articulos por numero de factura en base local de datos Produccion
    private function obtenerLineasDeFacturas($numFact){
     
        $queryDetFact = "SELECT ARTICULO, DESCRIPCION, SUM(CANTIDAD) as CANTIDAD FROM PRODUN_HOLDINGS_FACTURAS where FACTURA = ".$numFact."  GROUP BY ARTICULO, DESCRIPCION ";        
        $query_lineas_facturado = $this->sqlsrv->fetchArray($queryDetFact,SQLSRV_FETCH_ASSOC);

        return $query_lineas_facturado;
    }

    // Obtenre articulos por numero de Codigo en base remota
    private function obtenerArticulosRemotos($db_asterisk){
        
        $queryRemota = $db_asterisk->get('view_info_sku');
        $articulosRemotos = $queryRemota->result_array();// convertir stdClass a array
       
        return $articulosRemotos;
    }
}?>






