<?php
/**
 * Created by PhpStorm.
 * User: maryan.espinoza
 * Date: 18/05/2017
 * Time: 11:08
 */
class Cumpleannos
{
    var $Codigo;
    var $cedula;
    var $ruta;

    /**
     * Cumpleannos constructor.
     * @param $codigo
     * @param $fecha
     * @param $ruta
     */
    public function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getCodigo()
    {
        return $this->Codigo;
    }

    /**
     * @param mixed $Codigo
     */
    public function setCodigo($Codigo)
    {
        $this->Codigo = $Codigo;
    }

    /**
     * @return mixed
     */
    public function getCedula()
    {
        return $this->cedula;
    }

    /**
     * @param mixed $cedula
     */
    public function setCedula($cedula)
    {
        $this->cedula = $cedula;
    }

    /**
     * @return mixed
     */
    public function getRuta()
    {
        return $this->ruta;
    }

    /**
     * @param mixed $ruta
     */
    public function setRuta($ruta)
    {
        $this->ruta = $ruta;
    }






}