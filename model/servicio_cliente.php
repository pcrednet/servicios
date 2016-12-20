<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2014-2016    Carlos Garcia Gomez  neorazorx@gmail.com
 * Copyright (C) 2014         Francesc Pineda Segarra  shawe.ewahs@gmail.com
 * Copyright (C) 2015         Luis Miguel Pérez Romero  luismipr@gmail.com
 *
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
require_model('estado_servicio.php');

/**
 * Servicio de cliente
 */
class servicio_cliente extends fs_model
{
   /**
    * Clave primaria.
    * @var type 
    */
   public $idservicio;
   
   /**
    * ID del albarán relacionado, si lo hay.
    * @var type 
    */
   public $idalbaran;
   
   /**
    * Código único del documento. Para humanos.
    * @var type 
    */
   public $codigo;
   
   /**
    * Serie relacionada.
    * @var type 
    */
   public $codserie;
   
   /**
    * Ejercicio relacionado. El que corresponde a la fecha.
    * @var type 
    */
   public $codejercicio;
   
   /**
    * Código del cliente al que se presta el servicio.
    * @var type 
    */
   public $codcliente;
   
   /**
    * Empleado que se encarga del servicio.
    * @var type 
    */
   public $codagente;
   
   /**
    * Forma de pago.
    * @var type 
    */
   public $codpago;
   
   /**
    * Divisa del documento.
    * @var type 
    */
   public $coddivisa;
   
   /**
    * Almacén del que saldría la mercancía.
    * @var type 
    */
   public $codalmacen;
   
   /**
    * País del cliente.
    * @var type 
    */
   public $codpais;
   
   /**
    * ID de la dirección del cliente.
    * Modelo direccion_cliente.
    * @var type 
    */
   public $coddir;
   
   public $codpostal;
   
   /**
    * Número de servicio.
    * Único dentro de la serie+ejercicio.
    * @var type 
    */
   public $numero;
   
   /**
    * Prioridad del servicio.
    * @var type 
    */
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
   public $provincia;
   public $apartado;
   public $fecha;
   public $hora;
   
   /**
    * Importe total antes de impuestos.
    * Es la suma del pvptotal de las líneas.
    * @var type 
    */
   public $neto;
   
   /**
    * Importe total del servicio, con impuesto.
    * @var type 
    */
   public $total;
   
   /**
    * Suma total del IVA de las líneas.
    * @var type 
    */
   public $totaliva;
   
   /**
    * totaleuros = total*tasaconv
    * Esto es para dar compatibilidad a Eneboo. Fuera de eso, no tiene sentido.
    * Ni siquiera hace falta rellenarlo, al hacer save() se calcula el valor.
    * @var type 
    */
   public $totaleuros;
   
   /**
    * % de retención IRPF de la factura.
    * Puede variar en cada línea.
    * @var type 
    */
   public $irpf;
   
   /**
    * Suma total de retenciones IRPF de las líneas.
    * @var type 
    */
   public $totalirpf;
   
   /**
    * % de comisión del empleado.
    * @var type 
    */
   public $porcomision;
   
   /**
    * Tasa de conversión a Euros de la divisa de la factura.
    * @var type 
    */
   public $tasaconv;
   
   /**
    * Todavía sin uso.
    * @var type 
    */
   private $recfinanciero;
   
   /**
    * Suma del recargo de equivalencia de las líneas.
    * @var type 
    */
   public $totalrecargo;
   
   public $observaciones;
   
   public $descripcion;
   
   public $solucion;
   
   public $material;
   
   public $material_estado;
   
   public $accesorios;
   
   /**
    * ID del estado asociado.
    * @var type 
    */
   public $idestado;
   
   public $fechafin;
   
   public $fechainicio;
   
   public $garantia;
   
   /**
    * Fecha en la que se envió el servicio por email.
    * @var type 
    */
   public $femail;
   
   /**
    * Todavía sin uso.
    * @var type 
    */
   public $editable;
   
   private static $estados;

   public function __construct($s = FALSE)
   {
      parent::__construct('servicioscli');
      if($s)
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
         if( !is_null($s['hora']) )
         {
            $this->hora = $s['hora'];
         }
         
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
         $this->material_estado = $s['material_estado'];
         $this->accesorios = $s['accesorios'];
         $this->idestado = $s['idestado'];
         
         $this->fechafin = NULL;
         if( isset($s['fechafin']) )
         {
            $this->fechafin = date('d-m-Y H:i', strtotime($s['fechafin'].' '.$s['horafin']));
         }
         
         $this->fechainicio = NULL;
         if( isset($s['fechainicio']) )
         {
            $this->fechainicio = date('d-m-Y H:i', strtotime($s['fechainicio'].' '.$s['horainicio']));
         }
         
         $this->garantia = $s['garantia'];
         $this->prioridad = intval($s['prioridad']);
         $this->editable = $this->str2bool($s['editable']);
         
         $this->femail = NULL;
         if( !is_null($s['femail']) )
         {
            $this->femail = Date('d-m-Y', strtotime($s['femail']));
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
         $this->porcomision = 0;
         $this->tasaconv = 1;
         $this->recfinanciero = 0;
         $this->totalrecargo = 0;
         $this->observaciones = NULL;
         $this->descripcion = NULL;
         $this->solucion = NULL;
         $this->material = NULL;
         $this->material_estado = NULL;
         $this->accesorios = NULL;
         $this->idestado = NULL;
         $this->prioridad = 3;
         $this->fechafin = NULL;
         $this->fechainicio = Date('d-m-Y');
         $this->garantia = FALSE;
         $this->editable = TRUE;
         $this->femail = NULL;
      }
      
      if( !isset(self::$estados) )
      {
         $estado = new estado_servicio();
         self::$estados = $estado->all();
      }
   }

   protected function install()
   {
      return '';
   }

   public function show_hora($s = TRUE)
   {
      if($s)
      {
         return Date('H:i:s', strtotime($this->hora));
      }
      else
         return Date('H:i', strtotime($this->hora));
   }
   
   public function horainicio()
   {
      return Date('H:i:s', strtotime($this->fechainicio));
   }
   
   public function horafin()
   {
      return Date('H:i:s', strtotime($this->fechafin));
   }
   
   public function observaciones_resume()
   {
      if($this->observaciones == '')
      {
         return '-';
      }
      else if(strlen($this->observaciones) < 60)
      {
         return $this->observaciones;
      }
      else
         return substr($this->observaciones, 0, 50) . '...';
   }
   
   public function editable()
   {
      $editable = $this->editable;
      
      foreach(self::$estados as $est)
      {
         if($est->id == $this->idestado)
         {
            $editable = $est->activo;
            break;
         }
      }
      
      return $editable;
   }
   
   public function url($nuevo = FALSE)
   {
      if(is_null($this->idservicio))
      {
         return 'index.php?page=ventas_servicios';
      }
      else
      {
         if($nuevo)
         {
            return 'index.php?page=ventas_servicio&id=' . $this->idservicio . '&nuevo=TRUE';
         }
         else
            return 'index.php?page=ventas_servicio&id=' . $this->idservicio;
      }
   }

   public function albaran_url()
   {
      if( is_null($this->idalbaran) )
      {
         return 'index.php?page=ventas_albaran';
      }
      else
         return 'index.php?page=ventas_albaran&id=' . $this->idalbaran;
   }

   public function agente_url()
   {
      if( is_null($this->codagente) )
      {
         return "index.php?page=admin_agentes";
      }
      else
         return "index.php?page=admin_agente&cod=" . $this->codagente;
   }

   public function cliente_url()
   {
      if( is_null($this->codcliente) )
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
      {
         return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idservicio = " . $this->var2str($this->idservicio) . ";");
      }
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
         $sql = "SELECT MAX(".$this->db->sql_to_int('numero').") as num FROM ".$this->table_name
                 ." WHERE codejercicio = ".$this->var2str($this->codejercicio)
                 ." AND codserie = ".$this->var2str($this->codserie).";";
         
         $numero = $this->db->select($sql);
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
      $this->nombrecliente = $this->no_html($this->nombrecliente);
      if($this->nombrecliente == '')
      {
         $this->nombrecliente = '-';
      }
      
      $this->direccion = $this->no_html($this->direccion);
      $this->ciudad = $this->no_html($this->ciudad);
      $this->provincia = $this->no_html($this->provincia);
      $this->numero2 = $this->no_html($this->numero2);
      $this->observaciones = $this->no_html($this->observaciones);
      
      if($this->prioridad > 4)
      {
         $this->prioridad = 4;
      }
      else if($this->prioridad < 1 OR !is_numeric($this->prioridad) )
      {
         $this->prioridad = 1;
      }
      
      /**
       * Usamos el euro como divisa puente a la hora de sumar, comparar
       * o convertir cantidades en varias divisas. Por este motivo necesimos
       * muchos decimales.
       */
      $this->totaleuros = round($this->total / $this->tasaconv, 5);
      
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
   
   public function save()
   {
      if( $this->test() )
      {
         $fechafin = NULL;
         if($this->fechafin)
         {
            $fechafin = substr($this->fechafin, 0, 10);
         }
         
         $horafin = NULL;
         if($this->fechafin)
         {
            $horafin = substr($this->fechafin, 10, 6);
         }
         
         $fechaini = NULL;
         if($this->fechainicio)
         {
            $fechaini = substr($this->fechainicio, 0, 10);
         }
         
         $horaini = NULL;
         if($this->fechainicio)
         {
            $horaini = substr($this->fechainicio, 10, 6);
         }
         
         if( $this->exists() )
         {
            $sql = "UPDATE " . $this->table_name . " SET apartado = " . $this->var2str($this->apartado)
                    .", cifnif = " . $this->var2str($this->cifnif)
                    .", ciudad = " . $this->var2str($this->ciudad)
                    .", codagente = " . $this->var2str($this->codagente)
                    .", codalmacen = " . $this->var2str($this->codalmacen)
                    .", codcliente = " . $this->var2str($this->codcliente)
                    .", coddir = " . $this->var2str($this->coddir)
                    .", coddivisa = " . $this->var2str($this->coddivisa)
                    .", codejercicio = " . $this->var2str($this->codejercicio)
                    .", codigo = " . $this->var2str($this->codigo)
                    .", codpago = " . $this->var2str($this->codpago)
                    .", codpais = " . $this->var2str($this->codpais)
                    .", codpostal = " . $this->var2str($this->codpostal)
                    .", codserie = " . $this->var2str($this->codserie)
                    .", direccion = " . $this->var2str($this->direccion)
                    .", fecha = " . $this->var2str($this->fecha)
                    .", hora = " . $this->var2str($this->hora)
                    .", fechafin = " . $this->var2str($fechafin)
                    .", horafin = " . $this->var2str($horafin)
                    .", horainicio = " . $this->var2str($horaini)
                    .", idalbaran = " . $this->var2str($this->idalbaran)
                    .", irpf = " . $this->var2str($this->irpf)
                    .", neto = " . $this->var2str($this->neto)
                    .", fechainicio = " . $this->var2str($fechaini)
                    .", nombrecliente = " . $this->var2str($this->nombrecliente)
                    .", numero = " . $this->var2str($this->numero)
                    .", numero2 = " . $this->var2str($this->numero2)
                    .", observaciones = " . $this->var2str($this->observaciones)
                    .", porcomision = " . $this->var2str($this->porcomision)
                    .", provincia = " . $this->var2str($this->provincia)
                    .", recfinanciero = " . $this->var2str($this->recfinanciero)
                    .", tasaconv = " . $this->var2str($this->tasaconv)
                    .", prioridad = " . $this->var2str($this->prioridad)
                    .", descripcion = " . $this->var2str($this->descripcion)
                    .", solucion = " . $this->var2str($this->solucion)
                    .", material = " . $this->var2str($this->material)
                    .", material_estado = " . $this->var2str($this->material_estado)
                    .", accesorios = " . $this->var2str($this->accesorios)
                    .", idestado = " . $this->var2str($this->idestado)
                    .", garantia = " . $this->var2str($this->garantia)
                    .", total = " . $this->var2str($this->total)
                    .", totaleuros = " . $this->var2str($this->totaleuros)
                    .", totalirpf = " . $this->var2str($this->totalirpf)
                    .", totaliva = " . $this->var2str($this->totaliva)
                    .", totalrecargo = " . $this->var2str($this->totalrecargo)
                    .", editable = " . $this->var2str($this->editable)
                    .", femail = ".$this->var2str($this->femail)
                    ."  WHERE idservicio = " . $this->var2str($this->idservicio).";";
            
            return $this->db->exec($sql);
         }
         else
         {
            $this->new_codigo();
            $sql = "INSERT INTO " . $this->table_name . " (apartado,cifnif,ciudad,codagente,codalmacen,
               codcliente,coddir,coddivisa,codejercicio,codigo,codpais,codpago,codpostal,codserie,
               direccion,fecha,hora,idalbaran,irpf,neto,nombrecliente,numero,observaciones,
               porcomision,fechafin,fechainicio,garantia,provincia,recfinanciero,tasaconv,
               total,totaleuros,totalirpf,totaliva,totalrecargo,descripcion,solucion,material,
               material_estado,accesorios,prioridad,numero2,idestado,editable,horainicio,horafin,femail)
                VALUES (" . $this->var2str($this->apartado)
                    . "," . $this->var2str($this->cifnif)
                    . "," . $this->var2str($this->ciudad)
                    . "," . $this->var2str($this->codagente)
                    . "," . $this->var2str($this->codalmacen)
                    . "," . $this->var2str($this->codcliente)
                    . "," . $this->var2str($this->coddir)
                    . "," . $this->var2str($this->coddivisa)
                    . "," . $this->var2str($this->codejercicio)
                    . "," . $this->var2str($this->codigo)
                    . "," . $this->var2str($this->codpais)
                    . "," . $this->var2str($this->codpago)
                    . "," . $this->var2str($this->codpostal)
                    . "," . $this->var2str($this->codserie)
                    . "," . $this->var2str($this->direccion)
                    . "," . $this->var2str($this->fecha)
                    . "," . $this->var2str($this->hora)
                    . "," . $this->var2str($this->idalbaran)
                    . "," . $this->var2str($this->irpf)
                    . "," . $this->var2str($this->neto)
                    . "," . $this->var2str($this->nombrecliente)
                    . "," . $this->var2str($this->numero)
                    . "," . $this->var2str($this->observaciones)
                    . "," . $this->var2str($this->porcomision)
                    . "," . $this->var2str($fechafin)
                    . "," . $this->var2str($fechaini)
                    . "," . $this->var2str($this->garantia)
                    . "," . $this->var2str($this->provincia)
                    . "," . $this->var2str($this->recfinanciero)
                    . "," . $this->var2str($this->tasaconv)
                    . "," . $this->var2str($this->total)
                    . "," . $this->var2str($this->totaleuros)
                    . "," . $this->var2str($this->totalirpf)
                    . "," . $this->var2str($this->totaliva)
                    . "," . $this->var2str($this->totalrecargo)
                    . "," . $this->var2str($this->descripcion)
                    . "," . $this->var2str($this->solucion)
                    . "," . $this->var2str($this->material)
                    . "," . $this->var2str($this->material_estado)
                    . "," . $this->var2str($this->accesorios)
                    . "," . $this->var2str($this->prioridad)
                    . "," . $this->var2str($this->numero2)
                    . "," . $this->var2str($this->idestado)
                    . "," . $this->var2str($this->editable)
                    . "," . $this->var2str($horaini)
                    . "," . $this->var2str($horafin)
                    . "," . $this->var2str($this->femail).");";
            
            if( $this->db->exec($sql) )
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

   public function all($offset = 0, $order = 'fecha DESC')
   {
      $servlist = array();
      $sql = "SELECT * FROM ".$this->table_name." ORDER BY ".$order;
      
      $servicios = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($servicios)
      {
         foreach($servicios as $s)
         {
            $servlist[] = new servicio_cliente($s);
         }
      }
      
      return $servlist;
   }

   public function all_from_cliente($codcliente, $offset = 0)
   {
      $servlist = array();
      
      $servicios = $this->db->select_limit("SELECT * FROM " . $this->table_name .
              " WHERE codcliente = " . $this->var2str($codcliente) .
              " ORDER BY fecha DESC, codigo DESC", FS_ITEM_LIMIT, $offset);
      if($servicios)
      {
         foreach($servicios as $s)
         {
            $servlist[] = new servicio_cliente($s);
         }
      }
      
      return $servlist;
   }
   
   public function all_from_agente($codagente, $offset = 0)
   {
      $servlist = array();
      
      $servicios = $this->db->select_limit("SELECT * FROM " . $this->table_name
              ." WHERE codagente = " . $this->var2str($codagente)
              ." ORDER BY fecha DESC, codigo DESC", FS_ITEM_LIMIT, $offset);
      if($servicios)
      {
         foreach($servicios as $s)
         {
            $servlist[] = new servicio_cliente($s);
         }
      }
      
      return $servlist;
   }
   
   /**
    * Devuelve todos los servicios relacionados con el albarán.
    * @param type $id
    * @return \servicio_cliente
    */
   public function all_from_albaran($id)
   {
      $servlist = array();
      $sql = "SELECT * FROM ".$this->table_name." WHERE idalbaran = ".$this->var2str($id)
              ." ORDER BY fecha DESC, codigo DESC;";
     
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $p)
         {
            $servlist[] = new \servicio_cliente($p);
         }
      }
     
      return $servlist;
   }

   public function color_estado()
   {
      $color = 'FFFFFF';
      
      foreach(self::$estados as $est)
      {
         if($est->id == $this->idestado)
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
         if($est->id == $this->idestado)
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
      foreach($this->prioridad() as $i => $value)
      {
         $prioridad[] = array('id_prioridad' => $i, 'nombre_prioridad' => $value);
      }

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
      $sql = "SELECT count(*) as num FROM detalles_servicios "
              . "WHERE idservicio = ".$this->var2str($this->idservicio).";";
      
      if( $this->db->table_exists('detalles_servicios') )
      {
         $result = $this->db->select($sql);
         if($result)
         {
            $num = intval($result[0]['num']);
         }
      }
      
      return $num;
   }
   
   /**
    * Devuelve un array con los servicios comprendidos entre $desde y $hasta
    * @param type $desde
    * @param type $hasta
    * @return \servicio cliente
    */
   public function all_desde($desde, $hasta)
   {
      $servlist = array();
      $sql = "SELECT * FROM ".$this->table_name." WHERE fecha >= ".$this->var2str($desde)
              ." AND fecha <= ".$this->var2str($hasta)." ORDER BY codigo ASC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $s)
         {
            $servlist[] = new servicio_cliente($s);
         }
      }
      
      return $servlist;
   }
}