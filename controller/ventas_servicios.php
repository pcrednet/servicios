<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2014-2015  Carlos Garcia Gomez  neorazorx@gmail.com
 * Copyright (C) 2014-2015  Francesc Pineda Segarra  shawe.ewahs@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_model('agente.php');
require_model('articulo.php');
require_model('cliente.php');
require_model('servicio_cliente.php');

class ventas_servicios extends fs_controller
{
   public $buscar_lineas;
   public $lineas;
   public $mostrar;
   public $offset;
   public $resultados;

   public function __construct()
   {
      parent::__construct(__CLASS__, FS_SERVICIOS. ' de cliente', 'ventas');
   }

   protected function process()
   {
      $servicio = new servicio_cliente();

      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      $this->mostrar = 'todos';
      if( isset($_GET['mostrar']) )
      {
         $this->mostrar = $_GET['mostrar'];
      }

      if( isset($_POST['buscar_lineas']) )
      {
         $this->buscar_lineas();
      }
      else if( isset($_GET['codagente']) )
      {
         $this->template = 'extension/ventas_servicios_agente';

         $agente = new agente();
         $this->agente = $agente->get($_GET['codagente']);
         $this->resultados = $servicio->all_from_agente($_GET['codagente'], $this->offset);
      }
      else if( isset($_GET['codcliente']) )
      {
         $this->template = 'extension/ventas_servicios_cliente';

         $cliente = new cliente();
         $this->cliente = $cliente->get($_GET['codcliente']);
         $this->resultados = $servicio->all_from_cliente($_GET['codcliente'], $this->offset);
      }
      else if( isset($_GET['ref']) )
      {
         $this->template = 'extension/ventas_servicios_articulo';

         $articulo = new articulo();
         $this->articulo = $articulo->get($_GET['ref']);

         $linea = new linea_servicio_cliente();
         $this->resultados = $linea->all_from_articulo($_GET['ref'], $this->offset);
      }
      else
      {
         $this->share_extension();

         if (isset($_POST['delete']))
         {
            $this->delete_servicio();
         }

         if($this->query)
         {
            $this->resultados = $servicio->search($this->query, $this->offset);
         }
         else if($this->mostrar == 'pendientes')
         {
            $this->resultados = $servicio->all_ptealbaran($this->offset);
         }
         else if($this->mostrar == 'rechazados')
         {
            $this->resultados = $servicio->all_rechazados($this->offset);
         }
         else
         {
            /// ejecutamos el proceso del cron para servicios.
            $servicio->cron_job();
            $this->resultados = $servicio->all($this->offset);
         }
      }
   }

   public function anterior_url()
   {
      $url = '';
      $extra = '&mostrar='.$this->mostrar;

      if( isset($_GET['codagente']) )
      {
         $extra .= '&codagente=' . $_GET['codagente'];
      }
      else if( isset($_GET['codcliente']) )
      {
         $extra .= '&codcliente=' . $_GET['codcliente'];
      }
      else if( isset($_GET['ref']) )
      {
         $extra .= '&ref=' . $_GET['ref'];
      }

      if($this->query != '' AND $this->offset > '0')
      {
         $url = $this->url() . "&query=" . $this->query . "&offset=" . ($this->offset - FS_ITEM_LIMIT) . $extra;
      }
      else if($this->query == '' AND $this->offset > '0')
      {
         $url = $this->url() . "&offset=" . ($this->offset - FS_ITEM_LIMIT) . $extra;
      }

      return $url;
   }

   public function siguiente_url()
   {
      $url = '';
      $extra = '&mostrar='.$this->mostrar;

      if( isset($_GET['codagente']) )
      {
         $extra .= '&codagente=' . $_GET['codagente'];
      }
      else if( isset($_GET['codcliente']) )
      {
         $extra .= '&codcliente=' . $_GET['codcliente'];
      }
      else if( isset($_GET['ref']) )
      {
         $extra .= '&ref=' . $_GET['ref'];
      }

      if($this->query != '' AND count($this->resultados) == FS_ITEM_LIMIT)
      {
         $url = $this->url() . "&query=" . $this->query . "&offset=" . ($this->offset + FS_ITEM_LIMIT) . $extra;
      }
      else if($this->query == '' AND count($this->resultados) == FS_ITEM_LIMIT)
      {
         $url = $this->url() . "&offset=" . ($this->offset + FS_ITEM_LIMIT) . $extra;
      }

      return $url;
   }

   public function buscar_lineas()
   {
      /// cambiamos la plantilla HTML
      $this->template = 'ajax/ventas_lineas_servicios';

      $this->buscar_lineas = $_POST['buscar_lineas'];
      $linea = new linea_servicio_cliente();
      $this->lineas = $linea->search($this->buscar_lineas);
   }

   private function delete_servicio()
   {
      $serv = new servicio_cliente();
      $serv1 = $serv->get($_POST['delete']);
      if ($serv1)
      {
         if ($serv1->delete())
         {
            $this->new_message(ucfirst(FS_SERVICIO) . ' ' . $serv1->codigo . " borrado correctamente.");
         }
         else
            $this->new_error_msg("¡Imposible borrar el " . FS_SERVICIO . "!");
      }
      else
         $this->new_error_msg("¡" . ucfirst(FS_SERVICIO) . " no encontrado!");
   }

   private function share_extension()
   {
      /// añadimos las extensiones para clientes, agentes y artículos
      $extensiones = array(
          array(
              'name' => 'servicios_cliente',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_cliente',
              'type' => 'button',
              'text' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span> &nbsp; '.FS_SERVICIOS,
              'params' => ''
          ),
          array(
              'name' => 'servicios_agente',
              'page_from' => __CLASS__,
              'page_to' => 'admin_agente',
              'type' => 'button',
              'text' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span> &nbsp; '.FS_SERVICIOS . ' de cliente',
              'params' => ''
          ),
          array(
              'name' => 'servicios_articulo',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_articulo',
              'type' => 'tab_button',
              'text' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span> &nbsp; '.FS_SERVICIOS . ' de cliente',
              'params' => ''
          ),
      );
      foreach ($extensiones as $ext)
      {
         $fsext0 = new fs_extension($ext);
         if (!$fsext0->save())
         {
            $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
         }
      }
   }
   
   public function total_pendientes()
   {
      $data = $this->db->select("SELECT COUNT(idservicio) as total FROM servicioscli WHERE idalbaran IS NULL AND status=0;");
      if($data)
      {
         return intval($data[0]['total']);
      }
      else
         return 0;
   }
}
