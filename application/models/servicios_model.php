<?php
class servicios_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->database();
    }


    public function update_sku(){

        $db_asterisk = $this->load->database('db_remota', TRUE);
        $db_asterisk->where('Codigo', '6IN00085');
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

            }

        /*$db_asterisk->where('meta_key','_sku');
        $db_asterisk->update_batch('wp_postmeta',$Sku_update_batch,'post_id');


        $db_asterisk->update_batch('wp_wc_product_meta_lookup',$Sku_update_batch_wp_wc_product,'product_id');*/


       $this->db->where('meta_key','_sku');
        $this->db->update_batch('wp_postmeta',$Sku_update_batch,'post_id');

        //echo $this->db->last_query();

        echo '<br>';


        $this->db->update_batch('wp_wc_product_meta_lookup',$Sku_update_batch_wp_wc_product,'product_id');
     //   echo $this->db->last_query();*/



    }


}
?>
