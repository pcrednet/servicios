<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2014-2015    Carlos Garcia Gomez        neorazorx@gmail.com
 * Copyright (C) 2014-2015    Francesc Pineda Segarra    shawe.ewahs@gmail.com
 * Copyright (C) 2015         Luis Miguel Pérez Romero   luismipr@gmail.com
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

require_model('almacen.php');
require_model('articulo.php');
require_model('asiento_factura.php');
require_model('cliente.php');
require_model('divisa.php');
require_model('familia.php');
require_model('forma_pago.php');
require_model('grupo_clientes.php');
require_model('impuesto.php');
require_model('pais.php');
require_model('servicio_cliente.php');
require_model('presupuesto_cliente.php');
require_model('serie.php');
require_model('tarifa.php');
require_model('estados_servicios.php');
require_model('fabricante.php');

class nuevo_servicio extends fs_controller
{
   public $agente;
   public $almacen;
   public $articulo;
   public $cliente;
   public $cliente_s;
   public $direccion;
   public $divisa;
   public $familia;
   public $forma_pago;
   public $impuesto;
   public $pais;
   public $results;
   public $serie;
   public $descripcion;
   public $solucion;
   public $material;
   public $material_estado;
   public $accesorios;
   public $grupo;
   public $estado;
   public $fechaprevista;
   public $servicios_setup;
   public $fechafin;
   public $fechainicio;
   public $garantia;
   public $prioridad;
   public $servicio;
   public $fabricante;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'nuevo servicio', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      /// cargamos configuración de servicios
      $fsvar = new fs_var();
      $this->fabricante = new fabricante();
      $this->servicios_setup = $fsvar->array_get(
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
               ),
               FALSE
      );
      
      /// cargamos traducciones
      $this->st = $fsvar->array_get(
              array(
                  'st_servicio' => "Servicio",
                  'st_servicios' => "Servicios",
                  'st_material' => "Material",
                  'st_material_estado' => "Estado del material entregado",
                  'st_accesorios' => "Accesorios que entrega",
                  'st_descripcion' => "Descripción de la averia",
                  'st_solucion' => "Solución"
               ),
               FALSE
      );
      
      // cargamos la configuración
      $fsvar = new fs_var();
      $this->nuevocli_setup = $fsvar->array_get(
         array(
            'nuevocli_cifnif_req' => 0,
            'nuevocli_direccion' => 0,
            'nuevocli_direccion_req' => 0,
            'nuevocli_codpostal' => 0,
            'nuevocli_codpostal_req' => 0,
            'nuevocli_pais' => 0,
            'nuevocli_pais_req' => 0,
            'nuevocli_provincia' => 0,
            'nuevocli_provincia_req' => 0,
            'nuevocli_ciudad' => 0,
            'nuevocli_ciudad_req' => 0,
            'nuevocli_telefono1' => 0,
            'nuevocli_telefono1_req' => 0,
            'nuevocli_telefono2' => 0,
            'nuevocli_telefono2_req' => 0,
            'nuevocli_grupo' => 0,
            'nuevocli_grupo_req' => 0,
            'nuevocli_grupo_pred' => 0,
         ),
         FALSE
      );
      
      $this->servicio = new servicio_cliente(); 
      $this->cliente = new cliente();
      $this->cliente_s = FALSE;
      $this->direccion = FALSE;
      $this->familia = new familia();
      $this->impuesto = new impuesto();
      $this->results = array();
      $this->descripcion = NULL;
      $this->solucion = NULL;
      $this->prioridad = 3;
      $this->material = NULL;
      $this->material_estado = NULL;
      $this->accesorios = NULL;
      $this->grupo = new grupo_clientes();
      $this->estado = new estados_servicios();
      $this->pais = new pais();
      $this->fechaprevista = date('d-m-Y', strtotime($this->today(). '+ '.$this->servicios_setup['servicios_diasfin'].'days'));
      
      if( isset($_REQUEST['buscar_cliente']) )
      {
         $this->buscar_cliente();
      }
      else if( isset($_REQUEST['datoscliente']) )
      {
         $this->datos_cliente();
      }
      else if( isset($_REQUEST['new_articulo']) )
      {
         $this->new_articulo();
      }
      else if($this->query != '')
      {
         $this->new_search();
      }
      else if( isset($_POST['referencia4precios']) )
      {
         $this->get_precios_articulo();
      }
      else if( isset($_POST['cliente']) )
      {
         $this->cliente_s = $this->cliente->get($_POST['cliente']);
         
         /**
          * Nuevo cliente
          */
         if( isset($_POST['nuevo_cliente']) )
         {
            if($_POST['nuevo_cliente'] != '')
            {
               $this->cliente_s = FALSE;
               if($_POST['nuevo_cifnif'] != '')
               {
                  $this->cliente_s = $this->cliente->get_by_cifnif($_POST['nuevo_cifnif']);
                  if($this->cliente_s)
                  {
                     $this->new_advice('Ya existe un cliente con ese '.FS_CIFNIF.'. Se ha seleccionado.');
                  }
               }
               
               if(!$this->cliente_s)
               {
                  $this->cliente_s = new cliente();
                  $this->cliente_s->codcliente = $this->cliente_s->get_new_codigo();
                  $this->cliente_s->nombre = $this->cliente_s->razonsocial = $_POST['nuevo_cliente'];
                  $this->cliente_s->cifnif = $_POST['nuevo_cifnif'];
                  $this->cliente_s->codserie = $this->empresa->codserie;
                  
                  if( isset($_POST['nuevo_grupo']) )
                  {
                     if($_POST['nuevo_grupo'] != '')
                     {
                        $this->cliente_s->codgrupo = $_POST['nuevo_grupo'];
                     }
                  }
                  
                  if( isset($_POST['nuevo_telefono1']) )
                  {
                     $this->cliente_s->telefono1 = $_POST['nuevo_telefono1'];
                  }
                  
                  if( isset($_POST['nuevo_telefono2']) )
                  {
                     $this->cliente_s->telefono2 = $_POST['nuevo_telefono2'];
                  }
                  
                  if( $this->cliente_s->save() )
                  {
                     $dircliente = new direccion_cliente();
                     $dircliente->codcliente = $this->cliente_s->codcliente;
                     $dircliente->codpais = $this->empresa->codpais;
                     $dircliente->provincia = $this->empresa->provincia;
                     $dircliente->ciudad = $this->empresa->ciudad;
                     $dircliente->descripcion = 'Principal';
                     
                     if( isset($_POST['nuevo_pais']) )
                     {
                        $dircliente->codpais = $_POST['nuevo_pais'];
                     }
                     
                     if( isset($_POST['nuevo_provincia']) )
                     {
                        $dircliente->provincia = $_POST['nuevo_provincia'];
                     }
                     
                     if( isset($_POST['nuevo_ciudad']) )
                     {
                        $dircliente->ciudad = $_POST['nuevo_ciudad'];
                     }
                     
                     if( isset($_POST['nuevo_codpostal']) )
                     {
                        $dircliente->codpostal = $_POST['nuevo_codpostal'];
                     }
                     
                     if( isset($_POST['nuevo_direccion']) )
                     {
                        $dircliente->direccion = $_POST['nuevo_direccion'];
                     }
                     
                     if( $dircliente->save() )
                     {
                        $this->new_message('Cliente agregado correctamente.');
                     }
                  }
                  else
                     $this->new_error_msg("¡Imposible guardar la dirección del cliente!");  
               }
            }
         }
         
         if($this->cliente_s)
         {
            foreach($this->cliente_s->get_direcciones() as $dir)
            {
               if($dir->domfacturacion)
               {
                  $this->direccion = $dir;
                  break;
               }
            }
         }
         
         if( isset($_POST['codagente']) )
         {
            $agente = new agente();
            $this->agente = $agente->get($_POST['codagente']);
         }
         else
            $this->agente = $this->user->get_agente();
         
         $this->almacen = new almacen();
         $this->serie = new serie();
         $this->forma_pago = new forma_pago();
         $this->divisa = new divisa();

         
         if( isset($_POST['numlineas']) )
         {
            $this->nuevo_servicio_cliente();
            
            if(!$this->direccion)
            {
               $this->direccion = new direccion_cliente();
               $this->direccion->codcliente = $this->cliente_s->codcliente;
               $this->direccion->codpais = $_POST['codpais'];
               $this->direccion->provincia = $_POST['provincia'];
               $this->direccion->ciudad = $_POST['ciudad'];
               $this->direccion->codpostal = $_POST['codpostal'];
               $this->direccion->direccion = $_POST['direccion'];
               $this->direccion->descripcion = 'Principal';
               $this->direccion->save();
            }
         }     
      }
   }
   
   public function url()
   {
      return 'index.php?page='.__CLASS__;
   }
   
   private function buscar_cliente()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $json = array();
      foreach($this->cliente->search($_REQUEST['buscar_cliente']) as $cli)
      {
         $json[] = array('value' => $cli->nombre, 'data' => $cli->codcliente);
      }
      
      header('Content-Type: application/json');
      echo json_encode( array('query' => $_REQUEST['buscar_cliente'], 'suggestions' => $json) );
   }
   
   private function datos_cliente()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      header('Content-Type: application/json');
      echo json_encode( $this->cliente->get($_REQUEST['datoscliente']) );
   }
   
   private function new_articulo()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $art0 = new articulo();
      $art0->referencia = $_REQUEST['referencia'];
      if( $art0->exists() )
      {
         $this->results[] = $art0->get($_REQUEST['referencia']);
      }
      else
      {
         $art0->descripcion = $_REQUEST['descripcion'];
         $art0->set_impuesto($_REQUEST['codimpuesto']);
         $art0->set_pvp( floatval($_REQUEST['pvp']) );
         
         if($_POST['codfamilia'] != '')
         {
            $art0->codfamilia = $_REQUEST['codfamilia'];
         }
         if($_REQUEST['codfabricante'] != '')
         {
            $art0->codfabricante = $_REQUEST['codfabricante'];
         }
         
         if( $art0->save() )
         {
            $this->results[] = $art0;
         }
      }
      
      header('Content-Type: application/json');
      echo json_encode($this->results);
   }
   
   private function new_search()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $articulo = new articulo();
      $codfamilia = '';
      if( isset($_REQUEST['codfamilia']) )
      {
         $codfamilia = $_REQUEST['codfamilia'];
      }
      $codfabricante = '';
      if( isset($_REQUEST['codfabricante']) )
      {
         $codfabricante = $_REQUEST['codfabricante'];
      }
      
      $con_stock = isset($_REQUEST['con_stock']);
      $this->results = $articulo->search($this->query, 0, $codfamilia, $con_stock, $codfabricante);
      
      /// añadimos la busqueda, el descuento y la cantidad
      foreach($this->results as $i => $value)
      {
         $this->results[$i]->query = $this->query;
         $this->results[$i]->dtopor = 0;
         $this->results[$i]->cantidad = 1;
      }
      
      /// ejecutamos las funciones de las extensiones
      foreach($this->extensions as $ext)
      {
         if($ext->type == 'function' AND $ext->params == 'new_search')
         {
            $name = $ext->text;
            $name($this->db, $this->results);
         }
      }
      
      /// buscamos el grupo de clientes y la tarifa
      if( isset($_REQUEST['codcliente']) )
      {
         $cliente = $this->cliente->get($_REQUEST['codcliente']);
         if($cliente->codgrupo)
         {
            $grupo0 = new grupo_clientes();
            $tarifa0 = new tarifa();
            
            $grupo = $grupo0->get($cliente->codgrupo);
            if($grupo)
            {
               $tarifa = $tarifa0->get($grupo->codtarifa);
               if($tarifa)
               {
                  $tarifa->set_precios($this->results);
                  }
               }
            }
         }
      
      header('Content-Type: application/json');
      echo json_encode($this->results);
   }
   
   private function get_precios_articulo()
   {
      /// cambiamos la plantilla HTML
      $this->template = 'ajax/nueva_venta_precios';
      
      $articulo = new articulo();
      $this->articulo = $articulo->get($_POST['referencia4precios']);
   }
   
   public function get_tarifas_articulo($ref)
   {
      $tarlist = array();
      $articulo = new articulo();
      $tarifa = new tarifa();
      
      foreach($tarifa->all() as $tar)
      {
         $art = $articulo->get($ref);
         if($art)
         {
            $art->dtopor = 0;
            $aux = array($art);
            $tar->set_precios($aux);
            $tarlist[] = $aux[0];
         }
      }
      
      return $tarlist;
   }
   
	/**
    * Devuelve los tipos de documentos a guardar,
    * así para añadir tipos no hay que tocar la vista.
    * @return type
    */
   public function tipos_a_guardar()
   {
      $tipos = array();
      
      $tipos[] = array('tipo' => 'servicio', 'nombre' => ucfirst(FS_SERVICIO).' de cliente');
      
      return $tipos;
   }
  
   
   private function nuevo_servicio_cliente()
   {
      $continuar = TRUE;
      
      $cliente = $this->cliente->get($_POST['cliente']);
      $almacen = $this->almacen->get($_POST['almacen']);
      $eje0 = new ejercicio();
      $ejercicio = $eje0->get_by_fecha($_POST['fecha']);
      if( $ejercicio )
         $this->save_codejercicio( $ejercicio->codejercicio );
      else
      {
         $this->new_error_msg('Ejercicio no encontrado.');
         $continuar = FALSE;
      }
      
      $serie = $this->serie->get($_POST['serie']);
      if( !$serie )
      {
         $this->new_error_msg('Serie no encontrada.');
         $continuar = FALSE;
      }
      
      $forma_pago = $this->forma_pago->get($_POST['forma_pago']);
      if( $forma_pago )
         $this->save_codpago( $forma_pago->codpago );
      else
      {
         $this->new_error_msg('Forma de pago no encontrada.');
         $continuar = FALSE;
      }
      
      $divisa = $this->divisa->get($_POST['divisa']);
      if( $divisa )
         $this->save_coddivisa( $divisa->coddivisa );
      else
      {
         $this->new_error_msg('Divisa no encontrada.');
         $continuar = FALSE;
      }
      
      $servicio = new servicio_cliente();
      
      if( $this->duplicated_petition($_POST['petition_id']) )
      {
         $this->new_error_msg('Petición duplicada. Has hecho doble clic sobre el botón guardar
               y se han enviado dos peticiones. Mira en <a href="'.$servicio->url().'">Servicios</a>
               para ver si el servicio se ha guardado correctamente.');
         $continuar = FALSE;
      }
      
      if($continuar)
      {
         $servicio->fecha = $_POST['fecha'];
         $servicio->codalmacen = $almacen->codalmacen;
         $servicio->codejercicio = $ejercicio->codejercicio;
         $servicio->codserie = $serie->codserie;
         $servicio->codpago = $forma_pago->codpago;
         $servicio->coddivisa = $divisa->coddivisa;
         $servicio->tasaconv = $divisa->tasaconv;
         
         if($_POST['tasaconv'] != '')
         {
            $servicio->tasaconv = floatval($_POST['tasaconv']);
         }
         
         $servicio->codagente = $this->agente->codagente;
         $servicio->observaciones = $_POST['observaciones'];
         
         if( isset($_POST['numero2']) )
         {
            $servicio->numero2 = $_POST['numero2'];
         }
         
         $servicio->irpf = $serie->irpf;
         $servicio->porcomision = $this->agente->porcomision;
         
         $servicio->codcliente = $cliente->codcliente;
         $servicio->cifnif = $cliente->cifnif;
         $servicio->nombrecliente = $cliente->razonsocial;
         $servicio->ciudad = $_POST['ciudad'];
         $servicio->codpais = $_POST['codpais'];
         $servicio->codpostal = $_POST['codpostal'];
         $servicio->direccion = $_POST['direccion'];
         $servicio->provincia = $_POST['provincia'];
         $servicio->prioridad = intval($_POST['prioridad']);
         $servicio->idestado = $_POST['estado'];
         
         if( isset($_POST['material']) )
         {
            $servicio->material = $_POST['material'];
         }
         if( isset($_POST['material_estado']) )
         {
            $servicio->material_estado = $_POST['material_estado'];
         }
         if( isset($_POST['accesorios']) )
         {
            $servicio->accesorios = $_POST['accesorios'];
         }
         if( isset($_POST['descripcion']) )
         {
            $servicio->descripcion = $_POST['descripcion'];
         }
         if( isset($_POST['solucion']) )
         {
            $servicio->solucion = $_POST['solucion'];
         }
         if( isset($_POST['fechainicio']) )
         {
            $servicio->fechainicio = $_POST['fechainicio'];
         }
         if( isset($_POST['fechafin']) )
         {
            $servicio->fechafin = $_POST['fechafin'];
         }         
         if( isset($_POST['garantia']) )
         {
            $servicio->garantia = $_POST['garantia'];
         }
      
         if( $servicio->save() )
         {
            $art0 = new articulo();
            $n = floatval($_POST['numlineas']);
            for($i = 0; $i <= $n; $i++)
            {
               if( isset($_POST['referencia_'.$i]) )
               {
                  $linea = new linea_servicio_cliente();
                  $linea->idservicio = $servicio->idservicio;
                  $linea->descripcion = $_POST['desc_'.$i];
                  
                  if( !$serie->siniva AND $cliente->regimeniva != 'Exento' )
                  {
                     $imp0 = $this->impuesto->get_by_iva($_POST['iva_'.$i]);
                     if($imp0)
                     {
                        $linea->codimpuesto = $imp0->codimpuesto;
                        $linea->iva = floatval($_POST['iva_'.$i]);
                        $linea->recargo = floatval($_POST['recargo_'.$i]);
                     }
                     else
                     {
                        $linea->iva = floatval($_POST['iva_'.$i]);
                        $linea->recargo = floatval($_POST['recargo_'.$i]);
                     }
                  }
                  
                  $linea->irpf = floatval($_POST['irpf_'.$i]);
                  $linea->pvpunitario = floatval($_POST['pvp_'.$i]);
                  $linea->cantidad = floatval($_POST['cantidad_'.$i]);
                  $linea->dtopor = floatval($_POST['dto_'.$i]);
                  $linea->pvpsindto = ($linea->pvpunitario * $linea->cantidad);
                  $linea->pvptotal = floatval($_POST['neto_'.$i]);
                  
                  $articulo = $art0->get($_POST['referencia_'.$i]);
                  if($articulo)
                  {
                     $linea->referencia = $articulo->referencia;
                  }
                  
                  if( $linea->save() )
                  {
                     $servicio->neto += $linea->pvptotal;
                     $servicio->totaliva += ($linea->pvptotal * $linea->iva/100);
                     $servicio->totalirpf += ($linea->pvptotal * $linea->irpf/100);
                     $servicio->totalrecargo += ($linea->pvptotal * $linea->recargo/100);
                  }
                  else
                  {
                     $this->new_error_msg("¡Imposible guardar la linea con referencia: ".$linea->referencia);
                     $continuar = FALSE;
                  }
               }
            }
            
            if($continuar)
            {
               /// redondeamos
               $servicio->neto = round($servicio->neto, FS_NF0);
               $servicio->totaliva = round($servicio->totaliva, FS_NF0);
               $servicio->totalirpf = round($servicio->totalirpf, FS_NF0);
               $servicio->totalrecargo = round($servicio->totalrecargo, FS_NF0);
               $servicio->total = $servicio->neto + $servicio->totaliva - $servicio->totalirpf + $servicio->totalrecargo;
               
               if( abs(floatval($_POST['atotal']) - $servicio->total) >= .02)
               {
                  $this->new_error_msg("El total difiere entre el controlador y la vista (".
                          $servicio->total." frente a ".$_POST['atotal']."). Debes informar del error.");
                  $servicio->delete();
               }
               else if( $servicio->save() )
               {
                  $this->new_message("<a href='".$servicio->url()."'>".ucfirst(FS_SERVICIO)."</a> guardado correctamente.");
                  $this->new_change(ucfirst(FS_SERVICIO)." a Cliente ".$servicio->codigo, $servicio->url(), TRUE);
                  header('Location: '.$servicio->url());
               }
               else
                  $this->new_error_msg("¡Imposible actualizar el <a href='".$servicio->url()."'>".FS_SERVICIO."</a>!");
            }
            else if( $servicio->delete() )
            {
               $this->new_message(ucfirst(FS_SERVICIO)." eliminado correctamente.");
            }
            else
               $this->new_error_msg("¡Imposible eliminar el <a href='".$servicio->url()."'>".FS_SERVICIO."</a>!");
         }
         else
            $this->new_error_msg("¡Imposible guardar el ".FS_SERVICIO."!");
      }
   }
}