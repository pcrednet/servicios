<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2014-2015  Carlos Garcia Gomez  neorazorx@gmail.com
 * Copyright (C) 2014  Francesc Pineda Segarra  shawe.ewahs@gmail.com
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



require_model('albaran_cliente.php');
require_model('cliente.php');
require_model('linea_servicio_cliente.php');
require_model('secuencia.php');
require_model('estados_servicios.php');

/**
 * Pedido de cliente
 */
class servicio_cliente extends fs_model {

   public $idservicio;
   public $idalbaran;
   public $codigo;
   public $codserie;
   public $codejercicio;
   public $codcliente;
   public $codagente;
   public $codpago;
   public $coddivisa;
   public $codalmacen;
   public $codpais;
   public $coddir;
   public $codpostal;
   public $numero;
   public $prioridad;  
   /**
    * Número opcional a disposición del usuario.
    * @var type 
    */
   public $numero2;
   public $nombrecliente;
   public $cifnif;
   public $direccion;
   public $ciudad;
   public $srovincia;
   public $apartado;
   public $fecha;
   public $hora;
   public $neto;
   public $total;
   public $totaliva;
   public $totaleuros;
   public $irpf;
   public $totalirpf;
   public $sorcomision;
   public $tasaconv;
   public $recfinanciero;
   public $totalrecargo;
   public $observaciones;
   public $status;
   public $editable;
   public $descripcion;
   public $solucion;
   public $material;
   public $material_estado;
   public $accesorios;
   public $estado;
   public $fechafin;
   public $fechainicio;
   public $garantia;
   
   private static $estados;

   public function __construct($s = FALSE)
   {
      parent::__construct('servicioscli', 'plugins/servicios/');
      if ($s)
      {
         $this->idservicio = $this->intval($s['idservicio']);
         $this->idalbaran = $this->intval($s['idalbaran']);
         $this->codigo = $s['codigo'];
         $this->codagente = $s['codagente'];
         $this->codpago = $s['codpago'];
         $this->codserie = $s['codserie'];
         $this->codejercicio = $s['codejercicio'];
         $this->codcliente = $s['codcliente'];
         $this->coddivisa = $s['coddivisa'];
         $this->codalmacen = $s['codalmacen'];
         $this->codpais = $s['codpais'];
         $this->coddir = $s['coddir'];
         $this->codpostal = $s['codpostal'];
         $this->numero = $s['numero'];
         $this->numero2 = $s['numero2'];
         $this->nombrecliente = $s['nombrecliente'];
         $this->cifnif = $s['cifnif'];
         $this->direccion = $s['direccion'];
         $this->ciudad = $s['ciudad'];
         $this->provincia = $s['provincia'];
         $this->apartado = $s['apartado'];
         $this->fecha = Date('d-m-Y', strtotime($s['fecha']));

         $this->hora = Date('H:i:s', strtotime($s['fecha']));
         if (!is_null($s['hora']))
            $this->hora = $s['hora'];

         $this->neto = floatval($s['neto']);
         $this->total = floatval($s['total']);
         $this->totaliva = floatval($s['totaliva']);
         $this->totaleuros = floatval($s['totaleuros']);
         $this->irpf = floatval($s['irpf']);
         $this->totalirpf = floatval($s['totalirpf']);
         $this->porcomision = floatval($s['porcomision']);
         $this->tasaconv = floatval($s['tasaconv']);
         $this->recfinanciero = floatval($s['recfinanciero']);
         $this->totalrecargo = floatval($s['totalrecargo']);
         $this->observaciones = $s['observaciones'];
         $this->descripcion = $s['descripcion'];
         $this->solucion = $s['solucion'];
         $this->material = $s['material'];
         $this->status = $s['status'];
         $this->material_estado = $s['material_estado'];
         $this->accesorios = $s['accesorios'];
         $this->estado = $s['estado'];
         $this->fechafin = Date('d-m-Y', strtotime($s['fechafin']));
         $this->fechainicio = Date('d-m-Y', strtotime($s['fechainicio']));
         $this->garantia = $s['garantia'];
         $this->prioridad = $s['prioridad'];
         
          if (is_null($this->idalbaran))
         {
              $this->editable = TRUE;
             
              if ($this->status == 2)
                {
                 $this->status = 2;
                }
              else
                $this->status = 0;
         }    
         else
         {
              $this->editable = FALSE;
         }
         
      }
      else
      {
         $this->idservicio = NULL;
         $this->idalbaran = NULL;
         $this->codigo = NULL;
         $this->codagente = NULL;
         $this->codpago = NULL;
         $this->codserie = NULL;
         $this->codejercicio = NULL;
         $this->codcliente = NULL;
         $this->coddivisa = NULL;
         $this->codalmacen = NULL;
         $this->codpais = NULL;
         $this->coddir = NULL;
         $this->codpostal = '';
         $this->numero = NULL;
         $this->numero2 = NULL;
         $this->nombrecliente = NULL;
         $this->cifnif = NULL;
         $this->direccion = NULL;
         $this->ciudad = NULL;
         $this->provincia = NULL;
         $this->apartado = NULL;
         $this->fecha = Date('d-m-Y');
         $this->hora = Date('H:i:s');
         $this->neto = 0;
         $this->total = 0;
         $this->totaliva = 0;
         $this->totaleuros = 0;
         $this->irpf = 0;
         $this->totalirpf = 0;
         $this->porcomision = NULL;
         $this->tasaconv = 1;
         $this->recfinanciero = 0;
         $this->totalrecargo = 0;
         $this->observaciones = NULL;
         $this->status = 0;
         $this->descripcion = NULL;
         $this->solucion = NULL;
         $this->material = NULL;
         $this->material_estado = NULL;
         $this->accesorios = NULL;
         $this->prioridad = 3;
         $this->editable = TRUE;
         $this->fechafin = Date('d-m-Y');
         $this->fechainicio = Date('d-m-Y');
         $this->garantia = FALSE;
      }
      
       if( !isset(self::$estados) )
      {
         $estado = new estados_servicios();
         self::$estados = $estado->all();
      }
   }

   protected function install()
   {
      return '';
   }

   public function show_hora($s = TRUE)
   {
      if ($s)
      {
         return Date('H:i:s', strtotime($this->hora));
      }
      else
         return Date('H:i', strtotime($this->hora));
   }

   public function observaciones_resume()
   {
      if ($this->observaciones == '')
      {
         return '-';
      }
      else if (strlen($this->observaciones) < 60)
      {
         return $this->observaciones;
      }
      else
         return substr($this->observaciones, 0, 50) . '...';
   }

   public function url()
   {
      if (is_null($this->idservicio))
      {
         return 'index.php?page=ventas_servicios';
      }
      else
         return 'index.php?page=ventas_servicio&id=' . $this->idservicio;
   }

   public function albaran_url()
   {
      if (is_null($this->idalbaran)){
         return 'index.php?page=ventas_albaran';
      }
      else
         return 'index.php?page=ventas_albaran&id=' . $this->idalbaran;
   }

   public function agente_url()
   {
      if (is_null($this->codagente))
      {
         return "index.php?page=admin_agentes";
      }
      else
         return "index.php?page=admin_agente&cod=" . $this->codagente;
   }

   public function cliente_url()
   {
      if (is_null($this->codcliente))
      {
         return "index.php?page=ventas_clientes";
      }
      else
         return "index.php?page=ventas_cliente&cod=" . $this->codcliente;
   }

   public function get_lineas()
   {
      $linea = new linea_servicio_cliente();
      return $linea->all_from_servicio($this->idservicio);
   }

   public function get($id)
   {
      $servicio = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idservicio = " . $this->var2str($id) . ";");
      if($servicio)
      {
         return new servicio_cliente($servicio[0]);
      }
      else
         return FALSE;
   }

   public function exists()
   {
      if( is_null($this->idservicio) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idservicio = " . $this->var2str($this->idservicio) . ";");
   }

   public function new_codigo()
   {
      $sec = new secuencia();
      $sec = $sec->get_by_params2($this->codejercicio, $this->codserie, 'nserviciocli');
      if($sec)
      {
         $this->numero = $sec->valorout;
         $sec->valorout++;
         $sec->save();
      }

      if(!$sec OR $this->numero <= 1)
      {
         $numero = $this->db->select("SELECT MAX(" . $this->db->sql_to_int('numero') . ") as num
            FROM " . $this->table_name . " WHERE codejercicio = " . $this->var2str($this->codejercicio) .
                 " AND codserie = " . $this->var2str($this->codserie) . ";");
         if($numero)
         {
            $this->numero = 1 + intval($numero[0]['num']);
         }
         else
            $this->numero = 1;

         if($sec)
         {
            $sec->valorout = 1 + $this->numero;
            $sec->save();
         }
      }
      
      if(FS_NEW_CODIGO == 'eneboo')
      {
         $this->codigo = $this->codejercicio.sprintf('%02s', $this->codserie).sprintf('%06s', $this->numero);
      }
      else
      {
         $this->codigo = strtoupper(substr(FS_SERVICIO, 0, 3)).$this->codejercicio.$this->codserie.$this->numero;
      }
   }

   public function test()
   {
      $this->observaciones = $this->no_html($this->observaciones);
      $this->totaleuros = $this->total * $this->tasaconv;

      if($this->floatcmp($this->total, $this->neto + $this->totaliva - $this->totalirpf + $this->totalrecargo, FS_NF0, TRUE))
      {
         return TRUE;
      }
      else
      {
         $this->new_error_msg("Error grave: El total está mal calculado. ¡Informa del error!");
         return FALSE;
      }
   }

   public function full_test($duplicados = TRUE)
   {
      $status = TRUE;

      /// comprobamos las líneas
      $neto = 0;
      $iva = 0;
      $irpf = 0;
      $recargo = 0;
      foreach ($this->get_lineas() as $l)
      {
         if (!$l->test())
            $status = FALSE;

         $neto += $l->pvptotal;
         $iva += $l->pvptotal * $l->iva / 100;
         $irpf += $l->pvptotal * $l->irpf / 100;
         $recargo += $l->pvptotal * $l->recargo / 100;
      }

      $neto = round($neto, FS_NF0);
      $iva = round($iva, FS_NF0);
      $irpf = round($irpf, FS_NF0);
      $recargo = round($recargo, FS_NF0);
      $total = $neto + $iva - $irpf + $recargo;

      if (!$this->floatcmp($this->neto, $neto, FS_NF0, TRUE))
      {
         $this->new_error_msg("Valor neto de " . FS_SERVICIO . " incorrecto. Valor correcto: " . $neto);
         $status = FALSE;
      }
      else if (!$this->floatcmp($this->totaliva, $iva, FS_NF0, TRUE))
      {
         $this->new_error_msg("Valor totaliva de " . FS_SERVICIO . " incorrecto. Valor correcto: " . $iva);
         $status = FALSE;
      }
      else if (!$this->floatcmp($this->totalirpf, $irpf, FS_NF0, TRUE))
      {
         $this->new_error_msg("Valor totalirpf de " . FS_SERVICIO . " incorrecto. Valor correcto: " . $irpf);
         $status = FALSE;
      }
      else if (!$this->floatcmp($this->totalrecargo, $recargo, FS_NF0, TRUE))
      {
         $this->new_error_msg("Valor totalrecargo de " . FS_SERVICIO . " incorrecto. Valor correcto: " . $recargo);
         $status = FALSE;
      }
      else if (!$this->floatcmp($this->total, $total, FS_NF0, TRUE))
      {
         $this->new_error_msg("Valor total de " . FS_SERVICIO . " incorrecto. Valor correcto: " . $total);
         $status = FALSE;
      }
      else if (!$this->floatcmp($this->totaleuros, $this->total * $this->tasaconv, FS_NF0, TRUE))
      {
         $this->new_error_msg("Valor totaleuros de " . FS_SERVICIO . " incorrecto.
            Valor correcto: " . round($this->total * $this->tasaconv, FS_NF0));
         $status = FALSE;
      }

      if($this->idalbaran)
      {
         $alb0 = new albaran_cliente();
         $albaran = $alb0->get($this->idalbaran);
         if (!$albaran)
         {
            $this->idalbaran = NULL;
            $this->save();
         }
      }

      return $status;
   }

   public function save()
   {
      if( $this->test() )
      {
         if( $this->exists() )
         {
            $sql = "UPDATE " . $this->table_name . " SET apartado = " . $this->var2str($this->apartado) . ",
               cifnif = " . $this->var2str($this->cifnif) . ", ciudad = " . $this->var2str($this->ciudad) . ",
               codagente = " . $this->var2str($this->codagente) . ", codalmacen = " . $this->var2str($this->codalmacen) . ",
               codcliente = " . $this->var2str($this->codcliente) . ", coddir = " . $this->var2str($this->coddir) . ",
               coddivisa = " . $this->var2str($this->coddivisa) . ", codejercicio = " . $this->var2str($this->codejercicio) . ",
               codigo = " . $this->var2str($this->codigo) . ", codpago = " . $this->var2str($this->codpago) . ",
               codpais = " . $this->var2str($this->codpais) . ", codpostal = " . $this->var2str($this->codpostal) . ",
               codserie = " . $this->var2str($this->codserie) . ", direccion = " . $this->var2str($this->direccion) . ",
               editable = " . $this->var2str($this->editable) . ", fecha = " . $this->var2str($this->fecha) . ", hora = " . $this->var2str($this->hora) . ",
               fechafin = " . $this->var2str($this->fechafin) . ", idalbaran = " . $this->var2str($this->idalbaran) . ",
               irpf = " . $this->var2str($this->irpf) . ", neto = " . $this->var2str($this->neto) . ", fechainicio = " . $this->var2str($this->fechainicio) . ", 
               nombrecliente = " . $this->var2str($this->nombrecliente) . ", numero = " . $this->var2str($this->numero) . ",
               numero2 = " . $this->var2str($this->numero2) . ", observaciones = " . $this->var2str($this->observaciones) . ", 
               status = " . $this->var2str($this->status) . ", porcomision = " . $this->var2str($this->porcomision) . ",
               provincia = " . $this->var2str($this->provincia) . ", recfinanciero = " . $this->var2str($this->recfinanciero) . ",
               tasaconv = " . $this->var2str($this->tasaconv) . ", prioridad = " . $this->var2str($this->prioridad) . ",
               descripcion = " . $this->var2str($this->descripcion) . ", solucion = " . $this->var2str($this->solucion) . ",
               material = " . $this->var2str($this->material) . ", material_estado = " . $this->var2str($this->material_estado) . ", accesorios = " . $this->var2str($this->accesorios) . ",
               estado = " . $this->var2str($this->estado) . ", garantia = " . $this->var2str($this->garantia) . ",
               total = " . $this->var2str($this->total) . ", totaleuros = " . $this->var2str($this->totaleuros) . ",
               totalirpf = " . $this->var2str($this->totalirpf) . ", totaliva = " . $this->var2str($this->totaliva) . ",
               totalrecargo = " . $this->var2str($this->totalrecargo) . " WHERE idservicio = " . $this->var2str($this->idservicio) . ";";
            
            return $this->db->exec($sql);
         }
         else
         {
            $this->new_codigo();
            $sql = "INSERT INTO " . $this->table_name . " (apartado,cifnif,ciudad,codagente,codalmacen,
               codcliente,coddir,coddivisa,codejercicio,codigo,codpais,codpago,codpostal,codserie,
               direccion,editable,fecha,hora,idalbaran,irpf,neto,nombrecliente,
               numero,observaciones,status,porcomision,estado,fechafin,fechainicio,garantia,provincia,recfinanciero,tasaconv,total,totaleuros,
               totalirpf,totaliva,totalrecargo,descripcion,solucion,material,material_estado,accesorios,prioridad,numero2) VALUES (" . $this->var2str($this->apartado) . "," . $this->var2str($this->cifnif) . ",
               " . $this->var2str($this->ciudad) . "," . $this->var2str($this->codagente) . "," . $this->var2str($this->codalmacen) . ",
               " . $this->var2str($this->codcliente) . "," . $this->var2str($this->coddir) . "," . $this->var2str($this->coddivisa) . ",
               " . $this->var2str($this->codejercicio) . "," . $this->var2str($this->codigo) . "," . $this->var2str($this->codpais) . ",
               " . $this->var2str($this->codpago) . "," . $this->var2str($this->codpostal) . "," . $this->var2str($this->codserie) . ",
               " . $this->var2str($this->direccion) . "," . $this->var2str($this->editable) . "," . $this->var2str($this->fecha) . ",
               " . $this->var2str($this->hora) . "," . $this->var2str($this->idalbaran) . ",
               " . $this->var2str($this->irpf) . "," . $this->var2str($this->neto) . "," . $this->var2str($this->nombrecliente) . ",
               " . $this->var2str($this->numero) . "," . $this->var2str($this->observaciones) . "," . $this->var2str($this->status) . "," . $this->var2str($this->porcomision) . ",
               " . $this->var2str($this->estado) . "," . $this->var2str($this->fechafin) . "," . $this->var2str($this->fechainicio) . "," . $this->var2str($this->garantia) . ",
               " . $this->var2str($this->provincia) . "," . $this->var2str($this->recfinanciero) . ",
               " . $this->var2str($this->tasaconv) . "," . $this->var2str($this->total) . "," . $this->var2str($this->totaleuros) . ",
               " . $this->var2str($this->totalirpf) . "," . $this->var2str($this->totaliva) . "," . $this->var2str($this->totalrecargo) . ",
               " . $this->var2str($this->descripcion) . "," . $this->var2str($this->solucion) . "," . $this->var2str($this->material) . ", 
               " . $this->var2str($this->material_estado) . "," . $this->var2str($this->accesorios) . ",
               " . $this->var2str($this->prioridad) . "," . $this->var2str($this->numero2) . ");";

            if ($this->db->exec($sql))
            {
               $this->idservicio = $this->db->lastval();
               return TRUE;
            }
            else
               return FALSE;
         }
      }
      else
         return FALSE;
   }

   public function delete()
   {
      if( $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idservicio = " . $this->var2str($this->idservicio) . ";") )
      {
         if($this->idalbaran)
         {
            /**
             * Delegamos la eliminación en la clase correspondiente,
             * que tendrá que hacer más cosas.
             */
            $albaran = new albaran_cliente();
            $alb0 = $albaran->get($this->idalbaran);
            if($alb0)
            {
               $alb0->delete();
            }
         }

         

         return TRUE;
      }
      else
         return FALSE;
   }

   public function all($offset=0, $order='fecha DESC')
   {
      $servlist = array();
      $sql = "SELECT * FROM ".$this->table_name." ORDER BY ".$order;
      
      $servicios = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($servicios)
      {
         foreach($servicios as $s)
            $servlist[] = new servicio_cliente($s);
      }
      
      return $servlist;
   }

   public function all_ptealbaran($offset=0, $order='fecha ASC')
   {
      $servlist = array();
      $sql = "SELECT * FROM ".$this->table_name." WHERE idalbaran IS NULL AND status=0 ORDER BY ".$order;
      
      $servicios = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($servicios)
      {
         foreach($servicios as $s)
            $servlist[] = new servicio_cliente($s);
      }
      
      return $servlist;
   }

   public function all_rechazados($offset = 0, $order = 'DESC')
   {
      $sreclist = array();
      
      $servicios = $this->db->select_limit("SELECT * FROM " . $this->table_name .
              " WHERE status=2 ORDER BY fecha " . $order . ", codigo " . $order, FS_ITEM_LIMIT, $offset);
      if ($servicios)
      {
         foreach ($servicios as $s)
            $sreclist[] = new servicio_cliente($s);
      }
      
      return $sreclist;
   }

   public function all_from_cliente($codcliente, $offset = 0)
   {
      $sedilist = array();
      
      $servicios = $this->db->select_limit("SELECT * FROM " . $this->table_name .
              " WHERE codcliente = " . $this->var2str($codcliente) .
              " ORDER BY fecha DESC, codigo DESC", FS_ITEM_LIMIT, $offset);
      if ($servicios)
      {
         foreach ($servicios as $s)
            $sedilist[] = new servicio_cliente($s);
      }
      
      return $sedilist;
   }

   public function all_from_agente($codagente, $offset = 0)
   {
      $sedilist = array();
      
      $servicios = $this->db->select_limit("SELECT * FROM " . $this->table_name .
              " WHERE codagente = " . $this->var2str($codagente) ." ORDER BY fecha DESC, codigo DESC", FS_ITEM_LIMIT, $offset);
      if($servicios)
      {
         foreach($servicios as $s)
            $sedilist[] = new servicio_cliente($s);
      }
      
      return $sedilist;
   }

   public function all_desde($desde, $hasta)
   {
      $sedlist = array();
      
      $servicios = $this->db->select("SELECT * FROM " . $this->table_name .
              " WHERE fecha >= " . $this->var2str($desde) . " AND fecha <= " . $this->var2str($hasta) ." ORDER BY codigo ASC;");
      if($servicios)
      {
         foreach($servicios as $s)
            $sedlist[] = new servicio_cliente($s);
      }
      
      return $sedlist;
   }

   public function search($query, $offset = 0)
   {
      $sedilist = array();
      $query = strtolower($this->no_html($query));

      $consulta = "SELECT * FROM " . $this->table_name . " WHERE ";
      if( is_numeric($query) )
      {
         $consulta .= "codigo LIKE '%" . $query . "%' OR numero2 LIKE '%" . $query . "%' OR observaciones LIKE '%" . $query . "%'
            OR total BETWEEN '" . ($query - .01) . "' AND '" . ($query + .01) . "'";
      }
      else if( preg_match('/^([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})$/i', $query) )
      {
         /// es una fecha
         $consulta .= "fecha = " . $this->var2str($query) . " OR observaciones LIKE '%" . $query . "%'";
      }
      else
      {
         $consulta .= "lower(codigo) LIKE '%" . $query . "%' OR lower(numero2) LIKE '%" . $query . "%' "
                 . "OR lower(observaciones) LIKE '%" . str_replace(' ', '%', $query) . "%'";
      }
      $consulta .= " ORDER BY fecha DESC, codigo DESC";

      $servicios = $this->db->select_limit($consulta, FS_ITEM_LIMIT, $offset);
      if($servicios)
      {
         foreach($servicios as $s)
            $sedilist[] = new servicio_cliente($s);
      }
      
      return $sedilist;
   }

   public function search_from_cliente($codcliente, $desde, $hasta, $serie, $obs = '')
   {
      $sedilist = array();
      
      $sql = "SELECT * FROM " . $this->table_name . " WHERE codcliente = " . $this->var2str($codcliente) .
              " AND idalbaran AND fecha BETWEEN " . $this->var2str($desde) . " AND " . $this->var2str($hasta) .
              " AND codserie = " . $this->var2str($serie);

      if($obs != '')
         $sql .= " AND lower(observaciones) = " . $this->var2str(strtolower($obs));

      $sql .= " ORDER BY fecha DESC, codigo DESC;";

      $servicios = $this->db->select($sql);
      if($servicios)
      {
         foreach($servicios as $s)
            $sedilist[] = new servicio_cliente($s);
      }
      
      return $sedilist;
   }
   
   public function cron_job()
   {
      $this->db->exec("UPDATE ".$this->table_name." SET status = '0', idalbaran = NULL "
              . "WHERE status = '1' AND idalbaran NOT IN (SELECT idalbaran FROM albaranescli);");
   }
   
   public function color_estado()
   {
      $color = 'FFFFFF';
      
      foreach(self::$estados as $est)
      {
         if($est->id == $this->estado)
         {
            $color = $est->color;
            break;
         }
      }
      
      return $color;
   }
   
    public function nombre_estado()
   {
      $nombre = '';
      
      foreach(self::$estados as $est)
      {
         if($est->id == $this->estado)
         {
            $nombre = $est->descripcion;
            break;
         }
      }
      
      return $nombre;
   }
   
   public function listar_prioridad()
   {
      $prioridad = array();

      /**
       * En servicio_cliente::prioridad() nos devuelve un array con todos los prioridades,
       * pero como queremos también el id, pues hay que hacer este bucle para sacarlos.
       */
      foreach ($this->prioridad() as $i => $value)
         $prioridad[] = array('id_prioridad' => $i, 'nombre_prioridad' => $value);

      return $prioridad;
   }
   
   
     
   public function prioridad()
   {
      $prioridad = array(
          1 => 'Urgente',
          2 => 'Prioridad alta',
          3 => 'Prioridad media',
          4 => 'Prioridad baja',
      );
      
      return $prioridad;
   }
   
   public function nombre_prioridad()
   {
      $prioridades = $this->prioridad();
      return $prioridades[$this->prioridad];
   }
   
   public function estrellas_prioridad()
   {
      $retorno = '';
      $estrella = '<span class="glyphicon glyphicon-star" aria-hidden="true"></span>';
      $no_estrella = '<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>';
      
      $i = 0;
      for(;$i < 5-$this->prioridad; $i++)
      {
         $retorno .= $estrella;
      }
      
      while($i < 4)
      {
         $retorno .= $no_estrella;
         $i++;
      }
      
      return $retorno;
   }
   
   public function num_detalles()
   {
      $num = 0;
      
      if( $this->db->table_exists('detalles_servicios') )
      {
         $result = $this->db->select("SELECT count(*) as num FROM detalles_servicios WHERE idservicio = ".$this->var2str($this->idservicio).";");
         if($result)
         {
            $num = intval($result[0]['num']);
         }
      }
      
      return $num;
   }
}