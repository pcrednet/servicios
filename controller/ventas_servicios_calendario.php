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

        //cargamos las opciones del calendario
        $fsvar = new fs_var();
        $this->servicios_setup = $fsvar->array_get(
         array(
            'cal_inicio' => "09:00",
            'cal_fin' => "20:00",
            'cal_intervalo' => "30"
         ),
         FALSE
      );
        
        
        
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
      
      if($this->codcliente !='')
      {
          $sql .= $where."codcliente= ".$this->empresa->var2str($this->codcliente)."";
          $where = ' AND ';
      }
      
      if($this->codagente !='')
      {
          $sql .= $where."codagente= ".$this->empresa->var2str($this->codagente)."";
          $where = ' AND ';
      }
      
      if($this->estado !='')
      {
          $sql .= $where."idestado= ".$this->empresa->var2str($this->estado)."";
          $where = ' AND ';
      }
      
      $sql .= $where."fechainicio IS NOT NULL;";
      $servicios = $this->db->select("SELECT *".$sql);
      if($servicios)
      {
         foreach($servicios as $s)  
            $servlist[] = array(
                    'id' => $s['idservicio'],
                    'title' => $s['codigo'].'<br/>'.$s['nombrecliente'],
                    'url' => 'index.php?page=ventas_servicio&id=' . $s['idservicio'],
                    'class' => $this->class_prioridad ($s['prioridad']),
                    'start' => $this->formatDate($s['fechainicio']),
                    'end' => $this->fechafin($s['fechainicio'], $s['fechafin']), 
            );
        
      
        echo json_encode($servlist);
     
    }
    else {
        echo json_encode([]);
    }
  } 
  private function fechafin($inicio, $fin){
      $fechafin = '';
        if($fin)
            {
              $fechafin = $this->formatDate($fin);
            }
            else
            { 
              $fechafin = $this->formatDate($inicio) + (60000 * $this->servicios_setup['cal_intervalo']);
            }    
      return $fechafin;
  }
  
  /**
    * @desc - formatea una fecha a microtime para añadir al evento tipo 1401517498985
    * @access public
    * @return strtotime
    */
    public function formatDate($date)
    {
        $time= '';
        $time = strtotime(substr($date, 6, 4)."-".substr($date, 3, 2)."-".substr($date, 0, 2)." " .substr($date, 10, 6)) * 1000;
        return "$time";
    }
    
    /**
    * @desc - Establece la clase event en función de la prioiridad asignada
    * @access public
    * @return class
    */
     public function class_prioridad($prioridad)
   {
      $class='';
      if($prioridad == '1')
      {
          $class = 'event-important';
      }
      else if($prioridad == '2')
      {
          $class = 'event-warning';
      }
      else if($prioridad == '3')
      {
          $class = 'event-info';
      }
      else if($prioridad == '4')
      {
          $class = 'event-sucess';
      }
      
      return $class;
   }

}
