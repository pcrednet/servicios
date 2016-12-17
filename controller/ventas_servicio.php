
<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2014-2016    Carlos Garcia Gomez  neorazorx@gmail.com
 * Copyright (C) 2014-2015    Francesc Pineda Segarra  shawe.ewahs@gmail.com
 * Copyright (C) 2015         Luis Miguel Pérez Romero  luismipr@gmail.com
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

require_model('articulo.php');
require_model('cliente.php');
require_model('divisa.php');
require_model('ejercicio.php');
require_model('albaran_cliente.php');
require_model('familia.php');
require_model('forma_pago.php');
require_model('impuesto.php');
require_model('linea_servicio_cliente.php');
require_model('pais.php');
require_model('servicio_cliente.php');
require_model('regularizacion_iva.php');
require_model('serie.php');
require_model('estado_servicio.php');
require_model('detalle_servicio.php');
require_model('fabricante.php');

class ventas_servicio extends fs_controller
{
   public $agente;
   public $cliente;
   public $cliente_s;
   public $divisa;
   public $ejercicio;
   public $estado;
   public $fabricante;
   public $familia;
   public $forma_pago;
   public $impuesto;
   public $nuevo_servicio_url;
   public $pais;
   public $servicio;
   public $serie;
   public $setup;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, ucfirst(FS_SERVICIO), 'ventas', FALSE, FALSE);
   }

   protected function private_core()
   {
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $this->ppage = $this->page->get('ventas_servicios');
      
      $this->agente = new agente();
      $this->cliente = new cliente();
      $this->cliente_s = FALSE;
      $this->divisa = new divisa();
      $this->ejercicio = new ejercicio();
      $this->estado = new estado_servicio();
      $this->fabricante = new fabricante();
      $this->familia = new familia();
      $this->forma_pago = new forma_pago();
      $this->impuesto = new impuesto();
      $this->pais = new pais();
      $this->serie = new serie();
      
      /// cargamos la configuración de servicios
      $fsvar = new fs_var();
      $this->setup = $fsvar->array_get(
         array(
            'servicios_diasfin' => 10,
            'servicios_material' => 0,
            'servicios_mostrar_material' => 0,
            'servicios_material_estado' => 0,
            'servicios_mostrar_material_estado' => 0,             
            'servicios_accesorios' => 0,
            'servicios_mostrar_accesorios' => 0,             
            'servicios_descripcion' => 0,
            'servicios_mostrar_descripcion' => 0,            
            'servicios_solucion' => 0,
            'servicios_mostrar_solucion' => 0,             
            'servicios_fechafin' => 0,
            'servicios_mostrar_fechafin' => 0,             
            'servicios_fechainicio' => 0,
            'servicios_mostrar_fechainicio' => 0,
            'servicios_mostrar_garantia' => 0,
            'servicios_garantia' => 0,
            'cal_inicio' => "09:00",
            'cal_fin' => "20:00",
            'cal_intervalo' => "30",
            'servicios_linea' => 0,
            'servicios_linea1' => 0,
            'servicios_material_linea' => 0,
            'servicios_material_estado_linea' => 0,
            'servicios_accesorios_linea' => 0,
            'servicios_descripcion_linea' => 0,
            'servicios_solucion_linea' => 0, 
            'servicios_fechainicio_linea' => 0,
            'servicios_fechafin_linea' => 0,
            'servicios_garantia_linea' => 0,
            'st_servicio' => "Servicio",
            'st_servicios' => "Servicios",
            'st_material' => "Material",
            'st_material_estado' => "Estado del material entregado",
            'st_accesorios' => "Accesorios que entrega",
            'st_descripcion' => "Descripción de la averia",
            'st_solucion' => "Solución",
            'st_fechainicio' => "Fecha de Inicio",
            'st_fechafin' => "Fecha de finalización",
            'st_garantia' => "Garantía"
         ),
         FALSE
      );
      
      /**
       * Comprobamos si el usuario tiene acceso a nueva_venta,
       * necesario para poder añadir líneas.
       */
      $this->nuevo_servicio_url = FALSE;
      if( $this->user->have_access_to('nueva_venta', FALSE) )
      {
         $nuevoserv = $this->page->get('nueva_venta');
         if($nuevoserv)
         {
            $this->nuevo_servicio_url = $nuevoserv->url();
         }
      }
      
      //NUEVO?
      if(isset($_REQUEST['nuevo']))
      {
         $this->new_message('Nuevo servicio creado correctamente');
      }
      
      $this->servicio = FALSE;
      $servicio = new servicio_cliente();
      if( isset($_POST['idservicio']) )
      {
         $this->servicio = $servicio->get($_POST['idservicio']);
         $this->modificar();
      }
      else if( isset($_GET['id']) )
      {
         $this->servicio = $servicio->get($_GET['id']);
      }

      if($this->servicio)
      {
         $this->page->title = $this->servicio->codigo;

         /// cargamos el agente
         if($this->servicio->codagente)
         {
            $age0 = new agente();
            $this->agente = $age0->get($this->servicio->codagente);
            if(!$this->agente)
            {
               $this->agente = new agente();
            }
         }
         else
            $this->agente = $this->user->get_agente();
         
         /// cargamos el cliente
         $this->cliente_s = $this->cliente->get($this->servicio->codcliente);
         
         $this->modificar_detalles();
      }
      else
         $this->new_error_msg("¡" . ucfirst(FS_SERVICIO) . " de cliente no encontrado!");
   }

   public function url()
   {
      if( !isset($this->servicio) )
      {
         return parent::url();
      }
      else if($this->servicio)
      {
         return $this->servicio->url();
      }
      else
         return $this->page->url();
   }

   private function modificar()
   {
      $this->servicio->observaciones = $_POST['observaciones'];
      $this->servicio->numero2 = $_POST['numero2'];
      $this->servicio->codagente = $_POST['codagente'];
      $this->servicio->estado = $_POST['estado'];
      $this->servicio->codpago = $_POST['codpago'];
      
      if( isset($_POST['material']) )
      {
         $this->servicio->material = $_POST['material'];
      }
      
      if( isset($_POST['material_estado']) )
      {
         $this->servicio->material_estado = $_POST['material_estado'];
      }
      
      if( isset($_POST['accesorios']) )
      {
         $this->servicio->accesorios = $_POST['accesorios'];
      }
      
      if( isset($_POST['descripcion']) )
      {
         $this->servicio->descripcion = $_POST['descripcion'];
      }
      
      if( isset($_POST['solucion']) )
      {
         $this->servicio->solucion = $_POST['solucion'];
      }
      
      $this->servicio->fechainicio = Date('d-m-Y H:i');
      if( isset($_POST['fechainicio']) )
      {
         $this->servicio->fechainicio = $_POST['fechainicio'];
      }
      
      if( isset($_POST['fechafin']) )
      {
         $this->servicio->fechafin = $_POST['fechafin'];
      }
      else
      {
         $this->servicio->fechafin = date('Y-m-d H:i', strtotime($this->servicio->fechainicio. '+ '.$this->setup['cal_intervalo'].'minutes'));   
      }
      
      if( $this->servicio->editable() )
      {
         $this->servicio->garantia = isset($_POST['garantia']);
         $this->servicio->prioridad = $_POST['prioridad'];
         
         /// si no hay codejercicio; lo buscamos:
         if(!$this->servicio->codejercicio)
         {
            $ejercicio = $this->ejercicio->get_by_fecha($this->servicio->fecha);
            if($ejercicio)
            {
               $this->servicio->codejercicio = $ejercicio->codejercicio;
            }
         }
         
         /// obtenemos los datos del ejercicio para acotar la fecha
         $eje0 = $this->ejercicio->get($this->servicio->codejercicio);
         if($eje0)
         {
            $this->servicio->fecha = $eje0->get_best_fecha($_POST['fecha'], TRUE);
            $this->servicio->hora = $_POST['hora'];
         }
         else
            $this->new_error_msg('No se encuentra el ejercicio asociado al ' . FS_SERVICIO);
         
         /// ¿cambiamos el cliente?
         if($_POST['cliente'] != $this->servicio->codcliente OR $this->servicio->cifnif == '')
         {
            if(isset($_POST['cliente']))
            {
               $cliente = $this->cliente->get($_POST['cliente']);
            }
            else
               $cliente = $this->servicio->codcliente;
            
            if($cliente)
            {
               foreach($cliente->get_direcciones() as $d)
               {
                  if($d->domfacturacion)
                  {
                     $this->servicio->codcliente = $cliente->codcliente;
                     $this->servicio->cifnif = $cliente->cifnif;
                     $this->servicio->nombrecliente = $cliente->razonsocial;
                     $this->servicio->apartado = $d->apartado;
                     $this->servicio->ciudad = $d->ciudad;
                     $this->servicio->coddir = $d->id;
                     $this->servicio->codpais = $d->codpais;
                     $this->servicio->codpostal = $d->codpostal;
                     $this->servicio->direccion = $d->direccion;
                     $this->servicio->provincia = $d->provincia;
                     break;
                  }
               }
            }
            else
               die('No se ha encontrado el cliente.');
         }
         else
         {
            $this->servicio->nombrecliente = $_POST['nombrecliente'];
            $this->servicio->cifnif = $_POST['cifnif'];
            $this->servicio->codpais = $_POST['codpais'];
            $this->servicio->provincia = $_POST['provincia'];
            $this->servicio->ciudad = $_POST['ciudad'];
            $this->servicio->codpostal = $_POST['codpostal'];
            $this->servicio->direccion = $_POST['direccion'];
            
            $cliente = $this->cliente->get($this->servicio->codcliente);
         }

         $serie = $this->serie->get($this->servicio->codserie);

         /// ¿cambiamos la serie?
         if($_POST['serie'] != $this->servicio->codserie)
         {
            $serie2 = $this->serie->get($_POST['serie']);
            if($serie2)
            {
               $this->servicio->codserie = $serie2->codserie;
               $this->servicio->new_codigo();

               $serie = $serie2;
            }
         }
         
         /// ¿Cambiamos la divisa?
         if($_POST['divisa'] != $this->servicio->coddivisa)
         {
            $divisa = $this->divisa->get($_POST['divisa']);
            if($divisa)
            {
               $this->servicio->coddivisa = $divisa->coddivisa;
               $this->servicio->tasaconv = $divisa->tasaconv;
            }
         }
         else if($_POST['tasaconv'] != '')
         {
            $this->servicio->tasaconv = floatval($_POST['tasaconv']);
         }
         
         if( isset($_POST['numlineas']) )
         {
            $numlineas = intval($_POST['numlineas']);

            $this->servicio->neto = 0;
            $this->servicio->totaliva = 0;
            $this->servicio->totalirpf = 0;
            $this->servicio->totalrecargo = 0;
            $this->servicio->irpf = 0;
            
            $lineas = $this->servicio->get_lineas();
            $articulo = new articulo();

            /// eliminamos las líneas que no encontremos en el $_POST
            foreach($lineas as $l)
            {
               $encontrada = FALSE;
               for($num = 0; $num <= $numlineas; $num++)
               {
                  if( isset($_POST['idlinea_' . $num]) )
                  {
                     if($l->idlinea == intval($_POST['idlinea_' . $num]))
                     {
                        $encontrada = TRUE;
                        break;
                     }
                  }
               }
               if(!$encontrada)
               {
                  if( !$l->delete() )
                  {
                     $this->new_error_msg("¡Imposible eliminar la línea del artículo " . $l->referencia . "!");
                  }
               }
            }
            
            $regimeniva = 'general';
            if($cliente)
            {
               $regimeniva = $cliente->regimeniva;
            }
            
            /// modificamos y/o añadimos las demás líneas
            for($num = 0; $num <= $numlineas; $num++)
            {
               $encontrada = FALSE;
               if( isset($_POST['idlinea_' . $num]) )
               {
                  foreach($lineas as $k => $value)
                  {
                     /// modificamos la línea
                     if($value->idlinea == intval($_POST['idlinea_' . $num]))
                     {
                        $encontrada = TRUE;
                        $lineas[$k]->cantidad = floatval($_POST['cantidad_' . $num]);
                        $lineas[$k]->pvpunitario = floatval($_POST['pvp_' . $num]);
                        $lineas[$k]->dtopor = floatval($_POST['dto_' . $num]);
                        $lineas[$k]->pvpsindto = ($value->cantidad * $value->pvpunitario);
                        $lineas[$k]->pvptotal = ($value->cantidad * $value->pvpunitario * (100 - $value->dtopor) / 100);
                        $lineas[$k]->descripcion = $_POST['desc_' . $num];

                        $lineas[$k]->codimpuesto = NULL;
                        $lineas[$k]->iva = 0;
                        $lineas[$k]->recargo = 0;
                        $lineas[$k]->irpf = floatval($_POST['irpf_'.$num]);
                        if(!$serie->siniva AND $regimeniva != 'Exento')
                        {
                           $imp0 = $this->impuesto->get_by_iva($_POST['iva_' . $num]);
                           if ($imp0)
                           {
                              $lineas[$k]->codimpuesto = $imp0->codimpuesto;
                           }

                           $lineas[$k]->iva = floatval($_POST['iva_' . $num]);
                           $lineas[$k]->recargo = floatval($_POST['recargo_' . $num]);
                        }
                        
                        if($lineas[$k]->save())
                        {
                           $this->servicio->neto += $value->pvptotal;
                           $this->servicio->totaliva += $value->pvptotal * $value->iva / 100;
                           $this->servicio->totalirpf += $value->pvptotal * $value->irpf / 100;
                           $this->servicio->totalrecargo += $value->pvptotal * $value->recargo / 100;
                           
                           if($value->irpf > $this->servicio->irpf)
                           {
                              $this->servicio->irpf = $value->irpf;
                           }
                        }
                        else
                           $this->new_error_msg("¡Imposible modificar la línea del artículo " . $value->referencia . "!");
                        
                        break;
                     }
                  }

                  /// añadimos la línea
                  if(!$encontrada AND intval($_POST['idlinea_' . $num]) == -1 AND isset($_POST['referencia_' . $num]))
                  {
                     $linea = new linea_servicio_cliente();
                     $linea->idservicio = $this->servicio->idservicio;
                     $linea->descripcion = $_POST['desc_' . $num];
                     
                     if(!$serie->siniva AND $regimeniva != 'Exento')
                     {
                        $imp0 = $this->impuesto->get_by_iva($_POST['iva_' . $num]);
                        if($imp0)
                        {
                           $linea->codimpuesto = $imp0->codimpuesto;
                        }
                        
                        $linea->iva = floatval($_POST['iva_' . $num]);
                        $linea->recargo = floatval($_POST['recargo_' . $num]);
                     }
                     
                     $linea->irpf = floatval($_POST['irpf_'.$num]);
                     $linea->cantidad = floatval($_POST['cantidad_' . $num]);
                     $linea->pvpunitario = floatval($_POST['pvp_' . $num]);
                     $linea->dtopor = floatval($_POST['dto_' . $num]);
                     $linea->pvpsindto = ($linea->cantidad * $linea->pvpunitario);
                     $linea->pvptotal = ($linea->cantidad * $linea->pvpunitario * (100 - $linea->dtopor) / 100);
                     
                     $art0 = $articulo->get($_POST['referencia_' . $num]);
                     if($art0)
                     {
                        $linea->referencia = $art0->referencia;
                     }
                     
                     if( $linea->save() )
                     {
                        $this->servicio->neto += $linea->pvptotal;
                        $this->servicio->totaliva += $linea->pvptotal * $linea->iva / 100;
                        $this->servicio->totalirpf += $linea->pvptotal * $linea->irpf / 100;
                        $this->servicio->totalrecargo += $linea->pvptotal * $linea->recargo / 100;
                        
                        if($linea->irpf > $this->servicio->irpf)
                        {
                           $this->servicio->irpf = $linea->irpf;
                        }
                     }
                     else
                        $this->new_error_msg("¡Imposible guardar la línea del artículo " . $linea->referencia . "!");
                  }
               }
            }

            /// redondeamos
            $this->servicio->neto = round($this->servicio->neto, FS_NF0);
            $this->servicio->totaliva = round($this->servicio->totaliva, FS_NF0);
            $this->servicio->totalirpf = round($this->servicio->totalirpf, FS_NF0);
            $this->servicio->totalrecargo = round($this->servicio->totalrecargo, FS_NF0);
            $this->servicio->total = $this->servicio->neto + $this->servicio->totaliva - $this->servicio->totalirpf + $this->servicio->totalrecargo;

            if( abs(floatval($_POST['atotal']) - $this->servicio->total) >= .02 )
            {
               $this->new_error_msg("El total difiere entre el controlador y la vista (" . $this->servicio->total .
                       " frente a " . $_POST['atotal'] . "). Debes informar del error.");
            }
         }
      }

      if( $this->servicio->save() )
      {
         $this->new_message(ucfirst(FS_SERVICIO) . " modificado correctamente.");
         $this->new_change(ucfirst(FS_SERVICIO) . ' Cliente ' . $this->servicio->codigo, $this->servicio->url());
      }
      else
         $this->new_error_msg("¡Imposible modificar el " . FS_SERVICIO . "!");
      
      if($this->servicio->idestado != $_POST['estado'])
      {
         /// si tiene el mismo estado no tiene que hacer nada sino tiene que añadir un detalle
         $this->servicio->idestado = $_POST['estado'];
         $this->agrega_detalle_estado($_POST['estado']);
         
         foreach($this->estado->all() as $est)
         {
            if($est->id == $this->servicio->idestado)
            {
               if($est->albaran)
               {
                  if(!$this->servicio->idalbaran)
                  {
                     $this->generar_albaran();
                  }
                  else
                     $this->new_error_msg('Este ' . FS_SERVICIO . ' ya tiene este albaran generado:  <a href=" ' . $this->servicio->url() . '"> ' . $this->servicio->codigo . '</a>');
               }
               break;
            }
         }

         $this->servicio->save();
      }
   }
   
   private function modificar_detalles()
   {
      if( isset($_POST['detalle']) )
      {
         $this->agrega_detalle();
      }
      else if( isset($_GET['delete_detalle']) )
      {
         $det0 = new detalle_servicio();
         $detalle = $det0->get($_GET['delete_detalle']);
         if($detalle)
         {
            if( $detalle->delete() )
            {
               $this->new_message('Detalle eliminado correctamente.');
            }
            else
               $this->new_error_msg('Error al eliminar el detalle.');
         }
         else
            $this->new_error_msg('Detalle no encontrado.');
      }
   }

   private function generar_albaran()
   {
      $albaran = new albaran_cliente();
      $albaran->apartado = $this->servicio->apartado;
      $albaran->cifnif = $this->servicio->cifnif;
      $albaran->ciudad = $this->servicio->ciudad;
      $albaran->codagente = $this->servicio->codagente;
      
      if($this->servicio->codalmacen)
      {
         $albaran->codalmacen = $this->servicio->codalmacen;
      }
      else
      {
         $albaran->codalmacen = $this->empresa->codalmacen;
      }
      
      $albaran->codcliente = $this->servicio->codcliente;
      $albaran->coddir = $this->servicio->coddir;
      $albaran->coddivisa = $this->servicio->coddivisa;
      $albaran->tasaconv = $this->servicio->tasaconv;
      $albaran->codpago = $this->servicio->codpago;
      $albaran->codpais = $this->servicio->codpais;
      $albaran->codpostal = $this->servicio->codpostal;
      $albaran->codserie = $this->servicio->codserie;
      $albaran->direccion = $this->servicio->direccion;
      $albaran->neto = $this->servicio->neto;
      $albaran->nombrecliente = $this->servicio->nombrecliente;
      
      $albaran->observaciones = $this->servicio->observaciones;
      
      $albaran->provincia = $this->servicio->provincia;
      $albaran->total = $this->servicio->total;
      $albaran->totaliva = $this->servicio->totaliva;
      $albaran->numero2 = $this->servicio->numero2;
      $albaran->irpf = $this->servicio->irpf;
      $albaran->porcomision = $this->servicio->porcomision;
      $albaran->totalirpf = $this->servicio->totalirpf;
      $albaran->totalrecargo = $this->servicio->totalrecargo;

      /**
       * Obtenemos el ejercicio para la fecha de hoy (puede que
       * no sea el mismo ejercicio que el del servicio, por ejemplo
       * si hemos cambiado de año)
       */
      $eje0 = $this->ejercicio->get_by_fecha($albaran->fecha);
      $albaran->codejercicio = $eje0->codejercicio;
      
      if(!$eje0)
      {
         $this->new_error_msg("Ejercicio no encontrado.");
      }
      else if( !$eje0->abierto() )
      {
         $this->new_error_msg("El ejercicio está cerrado.");
      }
      else if( $albaran->save() )
      {
         $this->new_message("El ".FS_ALBARAN." ".$albaran->codigo." ha sido creado correctamente.");
         $continuar = TRUE;
         $art0 = new articulo();
         $i = 0;
         foreach($this->servicio->get_lineas() as $l)
         {
            $n = new linea_albaran_cliente();
            $n->idalbaran = $albaran->idalbaran;
            $n->cantidad = $l->cantidad;
            $n->codimpuesto = $l->codimpuesto;
            $n->descripcion = $l->descripcion;
            if($i == 0)
            {
               if($this->setup['servicios_linea'] && $this->setup['servicios_linea1'])
               {
                  $n->descripcion .= "\n";

                  if($this->setup['servicios_material_linea'])
                  {
                     $n->descripcion .= $this->setup['st_material'] . ": " . $this->servicio->material . "\n";
                  }
                  if($this->setup['servicios_material_estado_linea'])
                  {
                     $n->descripcion .= $this->setup['st_material_estado'] . ": " . $this->servicio->material_estado . "\n";
                  }
                  if($this->setup['servicios_accesorios_linea'])
                  {
                     $n->descripcion.= $this->setup['st_accesorios'] . ": " . $this->servicio->accesorios . "\n";
                  }
                  if($this->setup['servicios_descripcion_linea'])
                  {
                     $n->descripcion .= $this->setup['st_descripcion'] . ": " . $this->servicio->descripcion . "\n";
                  }
                  if($this->setup['servicios_solucion_linea'])
                  {
                     $n->descripcion .= $this->setup['st_solucion'] . ": " . $this->servicio->solucion . "\n";
                  }
                  if($this->setup['servicios_fechainicio_linea'])
                  {
                     $n->descripcion .= $this->setup['st_fechainicio'] . ": " . $this->servicio->fechainicio . "   ";
                  }
                  if($this->setup['servicios_fechafin_linea'])
                  {
                     $n->descripcion .= $this->setup['st_fechafin'] . ": " . $this->servicio->fechafin . "   ";
                  }
                  if($this->setup['servicios_garantia_linea'])
                  {
                     $n->descripcion .= $this->setup['st_garantia'] . ": " . $this->servicio->garantia . "\n";
                  }
               }
            }

            $n->dtopor = $l->dtopor;
            $n->irpf = $l->irpf;
            $n->iva = $l->iva;
            $n->pvpsindto = $l->pvpsindto;
            $n->pvptotal = $l->pvptotal;
            $n->pvpunitario = $l->pvpunitario;
            $n->recargo = $l->recargo;
            $n->referencia = $l->referencia;
            
            $i++;

            if( $n->save() )
            {
               /// descontamos del stock
               if( !is_null($n->referencia) )
               {
                  $articulo = $art0->get($n->referencia);
                  if($articulo)
                  {
                     $articulo->sum_stock($albaran->codalmacen, 0 - $l->cantidad);
                  }
               }
            }
            else
            {
               $continuar = FALSE;
               $this->new_error_msg("¡Imposible guardar la línea el artículo " . $n->referencia . "! ");
               break;
            }
         }

         if($this->setup['servicios_linea'] && !$this->setup['servicios_linea1'])
         {
            /// generamos la linea con detalles del servicio
            if($this->setup['servicios_linea'])
            {

               $ns = new linea_albaran_cliente();
               $ns->idalbaran = $albaran->idalbaran;
               $ns->cantidad = '0';
               
               /// usamos el impuestos por defecto
               $imp0 = new impuesto();          
               foreach ($imp0->all() as $imp)
               {
                  if( $imp->is_default() )
                  {
                     $ns->codimpuesto = $imp->codimpuesto;
                     $ns->iva = $imp->iva;
                  }
               }

               $ns->descripcion = FS_SERVICIO . ": " . $this->servicio->codigo . " Fecha: " . $this->servicio->fecha . "\n";
               if($this->setup['servicios_material_linea'])
               {
                  $ns->descripcion .= $this->setup['st_material'] . ": " . $this->servicio->material . "\n";
               }
               if($this->setup['servicios_material_estado_linea'])
               {
                  $ns->descripcion .= $this->setup['st_material_estado'] . ": " . $this->servicio->material_estado . "\n";
               }
               if($this->setup['servicios_accesorios_linea'])
               {
                  $ns->descripcion .= $this->setup['st_accesorios'] . ": " . $this->servicio->accesorios . "\n";
               }
               if($this->setup['servicios_descripcion_linea'])
               {
                  $ns->descripcion .= $this->setup['st_descripcion'] . ": " . $this->servicio->descripcion . "\n";
               }
               if($this->setup['servicios_solucion_linea'])
               {
                  $ns->descripcion .= $this->setup['st_solucion'] . ": " . $this->servicio->solucion . "\n";
               }
               if($this->setup['servicios_fechainicio_linea'])
               {
                  $ns->descripcion .= $this->setup['st_fechainicio'] . ": " . $this->servicio->fechainicio . "   ";
               }
               if($this->setup['servicios_fechafin_linea'])
               {
                  $ns->descripcion .= $this->setup['st_fechafin'] . ": " . $this->servicio->fechafin . "   ";
               }
               if($this->setup['servicios_garantia_linea'])
               {
                  $ns->descripcion .= $this->setup['st_garantia'] . ": " . $this->servicio->garantia . "\n";
               }

               $ns->dtopor = '0';
               $ns->irpf = '0';
               $ns->pvpsindto = '0';
               $ns->pvptotal = '0';
               $ns->pvpunitario = '0';
               $ns->recargo = '0';
               $ns->referencia = '';
               $ns->save();
            }
         }
         if($continuar)
         {
            $this->servicio->idalbaran = $albaran->idalbaran;
         }
         else
         {
            if( $albaran->delete() )
            {
               $this->new_error_msg("El " . FS_ALBARAN . " se ha borrado.");
            }
            else
               $this->new_error_msg("¡Imposible borrar el " . FS_ALBARAN . "!");
         }
      }
      else
         $this->new_error_msg("¡Imposible guardar el " . FS_ALBARAN . "!");
   }
 
   public function listar_servicio_detalle()
   {
      $detalle = new detalle_servicio();
      return $detalle->all_from_servicio($this->servicio->idservicio);
   }
   
   private function agrega_detalle()
   {
      $detalle = new detalle_servicio();
      $detalle->descripcion = $_POST['detalle'];
      $detalle->idservicio = $this->servicio->idservicio;
      $detalle->nick = $this->user->nick;
      
      if( $detalle->save() )
      {
         $this->new_message('Detalle guardados correctamente.');
      }
      else
      {
         $this->new_error_msg('Imposible guardar el detalle.');
      }
   }

   private function agrega_detalle_estado($id)
   {
      $this->estado = new estado_servicio();
      $estado = $this->estado->get($id);
      if($estado)
      {
         $detalle = new detalle_servicio();
         $detalle->descripcion = "Se ha cambiado el estado a: " . $estado->descripcion;
         $detalle->idservicio = $this->servicio->idservicio;
         $detalle->nick = $this->user->nick;
         
         if( $detalle->save() )
         {
            $this->new_message('Detalle guardados correctamente.');
         }
         else
         {
            $this->new_error_msg('Imposible guardar el detalle.');
         }
      }
   }
}
