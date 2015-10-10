<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of calendario_data
 *
 * @author luismi
 */

require_model('servicio_cliente.php');


class ventas_servicios_calendario_d extends fs_controller
{
    public $servicio;
    public $datos;
    
    
    public function __construct()
    {
      parent::__construct(__CLASS__,'datos calendario');
    }

    protected function private_core()
    {
        $this->template = FALSE;    
        $servicio = new servicio_cliente();
        $this->datos = $servicio->calendar_servicios();
    }
 
}
