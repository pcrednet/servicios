<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of calendario
 *
 * @author luismi
 */
require_model('agente.php');
require_model('articulo.php');
require_model('cliente.php');
require_model('servicio_cliente.php');
require_model('estado_servicios.php');

class ventas_servicios_calendario extends fs_controller {

    public $servicio;
    public $cliente;
    public $agente;
    public $codagente;
    public $codcliente;
    public $estado;
    public $datos;

    public function __construct() {
        parent::__construct(__CLASS__, 'Calendario', 'Servicios', FALSE, FALSE);
    }

    protected function private_core() {
        $this->share_extensions();
        $this->servicio = new servicio_cliente();
        $this->agente = new agente();
        $this->serie = new serie();
        $this->estados = new estado_servicio();

        if (isset($_REQUEST['buscar_cliente'])) {
            $this->buscar_cliente();
        }

        if (isset($_REQUEST['codagente']) OR isset($_REQUEST['codcliente']) OR isset($_REQUEST['estado'])) {
            if (isset($_REQUEST['codcliente'])) {
                if ($_REQUEST['codcliente'] != '') {
                    $cli0 = new cliente();
                    $this->cliente = $cli0->get($_REQUEST['codcliente']);
                    $this->codcliente = $_REQUEST['codcliente'];
                }

                if (isset($_REQUEST['codagente'])) {
                    $this->codagente = $_REQUEST['codagente'];
                }

                if (isset($_REQUEST['estado'])) {
                    $this->estado = $_REQUEST['estado'];
                }
            }
        }
        
    }

    private function buscar_cliente() {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;

        $cli0 = new cliente();
        $json = array();
        foreach ($cli0->search($_REQUEST['buscar_cliente']) as $cli) {
            $json[] = array('value' => $cli->nombre, 'data' => $cli->codcliente);
        }

        header('Content-Type: application/json');
        echo json_encode(array('query' => $_REQUEST['buscar_cliente'], 'suggestions' => $json));
    }

    private function share_extensions() {
        $fsext = new fs_extension();
        $fsext->name = 'ventas_servicios_calendario';
        $fsext->from = __CLASS__;
        $fsext->to = 'ventas_servicios';
        $fsext->type = 'button';
        $fsext->text = '<span class="glyphicon glyphicon-calendar" aria-hidden="true">'
                . '</span><span class="hidden-xs">&nbsp; Calendario</span>';
        $fsext->save();
    }
    
    public function get_datos()
    { 
      $servlist = array();
      $sql = " FROM servicioscli ";
      $where = 'WHERE ';
      
      if($codcliente !='')
      {
          $sql .= $where."codcliente= ".$this->var2str($this->codcliente)."";
          $where = ' AND ';
      }
      
      if($codagente !='')
      {
          $sql .= $where."codagente= ".$this->var2str($this->codagente)."";
          $where = ' AND ';
      }
      
      if($estado !='')
      {
          $sql .= $where."idestado= ".$this->var2str($this->estado)."";
          $where = ' AND ';
      }
      
      $sql .=";";
      $servicios = $this->db->select("SELECT *".$sql);
      if($servicios)
      {
         foreach($servicios as $s) 
            $servlist[] = array(
                    'id' => $s['idservicio'],
                    'title' => $s['codigo']." | ".$s['fechainicio']." -> ".$s['fechafin']." | ".$s['nombrecliente'],
                    'url' => 'index.php?page=ventas_servicio&id=' . $s['idservicio'],
                    'class' => $this->class_prioridad ($s['prioridad']),
                    'start' => $this->formatDate($s['fechainicio']),
                    'end' => $this->formatDate($s['fechafin']),
            );
        
      
        echo json_encode(array('success' => 1, 'result' => $servlist));
     
    }
  }       

}
