<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2014-2015  Carlos Garcia Gomez  neorazorx@gmail.com
 * Copyright (C) 2014  Francesc Pineda Segarra  shawe.ewahs@gmail.com
 * Copyright (C) 2015  Luis Miguel Pérez Romero  luismipr@gmail.com
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

class linea_servicio_cliente extends fs_model
{
   public $cantidad;
   public $codimpuesto;
   public $descripcion;
   public $dtopor;
   public $idlinea;
   public $idservicio;
   public $irpf;
   public $iva;
   public $svpsindto;
   public $svptotal;
   public $svpunitario;
   public $recargo;
   public $referencia;
   
   private static $servicios;
   
   public function __construct($l = FALSE)
   {
      parent::__construct('lineasservicioscli');
      
      if( !isset(self::$servicios) )
         self::$servicios = array();
      
      if($l)
      {
         $this->cantidad = floatval($l['cantidad']);
         $this->codimpuesto = $l['codimpuesto'];
         $this->descripcion = $l['descripcion'];
         $this->dtopor = floatval($l['dtopor']);
         $this->idlinea = $this->intval($l['idlinea']);
         $this->idservicio = $this->intval($l['idservicio']);
         $this->irpf = floatval($l['irpf']);
         $this->iva = floatval($l['iva']);
         $this->pvpsindto = floatval($l['pvpsindto']);
         $this->pvptotal = floatval($l['pvptotal']);
         $this->pvpunitario = floatval($l['pvpunitario']);
         $this->recargo = floatval($l['recargo']);
         $this->referencia = $l['referencia'];
      }
      else
      {
         $this->cantidad = 0;
         $this->codimpuesto = NULL;
         $this->descripcion = '';
         $this->dtopor = 0;
         $this->idlinea = NULL;
         $this->idlineapresupuesto = NULL;
         $this->idservicio = NULL;
         $this->idpresupuesto = NULL;
         $this->irpf = 0;
         $this->iva = 0;
         $this->pvpsindto = 0;
         $this->pvptotal = 0;
         $this->pvpunitario = 0;
         $this->recargo = 0;
         $this->referencia = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function pvp_iva()
   {
      return $this->pvpunitario*(100+$this->iva)/100;
   }
   
   public function total_iva()
   {
      return $this->pvptotal*(100+$this->iva-$this->irpf+$this->recargo)/100;
   }
   
   public function show_codigo()
   {
      $codigo = 'desconocido';
      
      $encontrado = FALSE;
      foreach(self::$servicios as $s)
      {
         if($s->idservicio == $this->idservicio)
         {
            $codigo = $s->codigo;
            $encontrado = TRUE;
            break;
         }
      }
      
      if( !$encontrado )
      {
         $sre = new servicio_cliente();
         self::$servicios[] = $sre->get($this->idservicio);
         $codigo = self::$servicios[ count(self::$servicios)-1 ]->codigo;
      }
      
      return $codigo;
   }
   
   public function show_fecha()
   {
      $fecha = 'desconocida';
      
      $encontrado = FALSE;
      foreach(self::$servicios as $s)
      {
         if($s->idservicio == $this->idservicio)
         {
            $fecha = $s->fecha;
            $encontrado = TRUE;
            break;
         }
      }
      
      if( !$encontrado )
      {
         $sre = new servicio_cliente();
         self::$servicios[] = $sre->get($this->idservicio);
         $fecha = self::$servicios[ count(self::$servicios)-1 ]->fecha;
      }
      
      return $fecha;
   }
   
   public function show_nombrecliente()
   {
      $nombre = 'desconocido';
      
      $encontrado = FALSE;
      foreach(self::$servicios as $s)
      {
         if($s->idservicio == $this->idservicio)
         {
            $nombre = $s->nombrecliente;
            $encontrado = TRUE;
            break;
         }
      }
      
      if( !$encontrado )
      {
         $sre = new servicio_cliente();
         self::$servicios[] = $sre->get($this->idservicio);
         $nombre = self::$servicios[ count(self::$servicios)-1 ]->nombrecliente;
      }
      
      return $nombre;
   }
   
   public function url()
   {
      return 'index.php?page=ventas_servicio&id='.$this->idservicio;
   }
   
   public function articulo_url()
   {
      if( is_null($this->referencia) OR $this->referencia == '')
      {
         return "index.php?page=ventas_articulos";
      }
      else
         return "index.php?page=ventas_articulo&ref=".urlencode($this->referencia);
   }
   
   public function exists()
   {
      if( is_null($this->idlinea) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE idlinea = ".$this->var2str($this->idlinea).";");
   }
   
   public function test()
   {
      $this->descripcion = $this->no_html($this->descripcion);
      $total = $this->pvpunitario * $this->cantidad * (100 - $this->dtopor) / 100;
      $totalsindto = $this->pvpunitario * $this->cantidad;
      
      if( !$this->floatcmp($this->pvptotal, $total, FS_NF0, TRUE) )
      {
         $this->new_error_msg("Error en el valor de pvptotal de la línea ".$this->referencia." del ".FS_SERVICIO.". Valor correcto: ".$total);
         return FALSE;
      }
      else if( !$this->floatcmp($this->pvpsindto, $totalsindto, FS_NF0, TRUE) )
      {
         $this->new_error_msg("Error en el valor de pvpsindto de la línea ".$this->referencia." del ".FS_SERVICIO.". Valor correcto: ".$totalsindto);
         return FALSE;
      }
      else
         return TRUE;
   }
   
   public function save()
   {
      if( $this->test() )
      {
         if( $this->exists() )
         {
            $sql = "UPDATE ".$this->table_name." SET cantidad = ".$this->var2str($this->cantidad).",
               codimpuesto = ".$this->var2str($this->codimpuesto).", descripcion = ".$this->var2str($this->descripcion).",
               dtopor = ".$this->var2str($this->dtopor).", idservicio = ".$this->var2str($this->idservicio).",
               irpf = ".$this->var2str($this->irpf).", iva = ".$this->var2str($this->iva).",
               pvpsindto = ".$this->var2str($this->pvpsindto).", pvptotal = ".$this->var2str($this->pvptotal).",
               pvpunitario = ".$this->var2str($this->pvpunitario).", recargo = ".$this->var2str($this->recargo).",
               referencia = ".$this->var2str($this->referencia)." WHERE idlinea = ".$this->var2str($this->idlinea).";";
            
            return $this->db->exec($sql);
         }
         else
         {
            $sql = "INSERT INTO ".$this->table_name." (cantidad,codimpuesto,descripcion,dtopor,idservicio,
               irpf,iva,pvpsindto,pvptotal,pvpunitario,recargo,referencia)
               VALUES (".$this->var2str($this->cantidad).",".$this->var2str($this->codimpuesto).",
               ".$this->var2str($this->descripcion).",".$this->var2str($this->dtopor).",
               ".$this->var2str($this->idservicio).",".$this->var2str($this->irpf).",".$this->var2str($this->iva).",
               ".$this->var2str($this->pvpsindto).",".$this->var2str($this->pvptotal).",".$this->var2str($this->pvpunitario).",
               ".$this->var2str($this->recargo).",".$this->var2str($this->referencia).");";
            
            if( $this->db->exec($sql) )
            {
               $this->idlinea = $this->db->lastval();
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
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE idlinea = ".$this->var2str($this->idlinea).";");
   }
   
   public function all_from_servicio($idp)
   {
      $slist = array();
      
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE idservicio = ".$this->var2str($idp)." ORDER BY idlinea ASC;");
      if($data)
      {
         foreach($data as $d)
            $slist[] = new linea_servicio_cliente($d);
      }
      
      return $slist;
   }
   
   public function all_from_articulo($ref, $offset=0, $limit=FS_ITEM_LIMIT)
   {
      $linealist = array();
      $lineas = $this->db->select_limit("SELECT * FROM ".$this->table_name.
              " WHERE referencia = ".$this->var2str($ref).
              " ORDER BY idservicio DESC", $limit, $offset);
      if( $lineas )
      {
         foreach($lineas as $l)
            $linealist[] = new linea_servicio_cliente($l);
      }
      
      return $linealist;
   }
   
   public function search($query='', $offset=0)
   {
      $linealist = array();
      $query = strtolower( $this->no_html($query) );
      
      $sql = "SELECT * FROM ".$this->table_name." WHERE ";
      if( is_numeric($query) )
      {
         $sql .= "referencia LIKE '%".$query."%' OR descripcion LIKE '%".$query."%'";
      }
      else
      {
         $buscar = str_replace(' ', '%', $query);
         $sql .= "lower(referencia) LIKE '%".$buscar."%' OR lower(descripcion) LIKE '%".$buscar."%'";
      }
      $sql .= " ORDER BY idservicio DESC, idlinea ASC";
      
      $lineas = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if( $lineas )
      {
         foreach($lineas as $l)
            $linealist[] = new linea_servicio_cliente($l);
      }
      
      return $linealist;
   }

    public function search_from_cliente($codcliente, $query='', $offset=0)
   {
      $linealist = array();
      $query = strtolower( $this->no_html($query) );
      
      $sql = "SELECT * FROM ".$this->table_name." WHERE idservicio IN
         (SELECT idservicio FROM servicioscli WHERE codcliente = ".$this->var2str($codcliente).") AND ";
      if( is_numeric($query) )
      {
         $sql .= "(referencia LIKE '%".$query."%' OR descripcion LIKE '%".$query."%')";
      }
      else
      {
         $buscar = str_replace(' ', '%', $query);
         $sql .= "(lower(referencia) LIKE '%".$buscar."%' OR lower(descripcion) LIKE '%".$buscar."%')";
      }
      $sql .= " ORDER BY idservicio DESC, idlinea ASC";
      
      $lineas = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if( $lineas )
      {
         foreach($lineas as $l)
            $linealist[] = new linea_servicio_cliente($l);
      }
      return $linealist;
   }
   
   public function search_from_cliente2($codcliente, $ref='', $obs='', $offset=0)
   {
      $linealist = array();
      $ref = strtolower( $this->no_html($ref) );
      
      $sql = "SELECT * FROM ".$this->table_name." WHERE idservicio IN
         (SELECT idservicio FROM servicioscli WHERE codcliente = ".$this->var2str($codcliente)."
         AND lower(observaciones) LIKE '".strtolower($obs)."%') AND ";
      if( is_numeric($ref) )
      {
         $sql .= "(referencia LIKE '%".$ref."%' OR descripcion LIKE '%".$ref."%')";
      }
      else
      {
         $buscar = str_replace(' ', '%', $ref);
         $sql .= "(lower(referencia) LIKE '%".$ref."%' OR lower(descripcion) LIKE '%".$ref."%')";
      }
      $sql .= " ORDER BY idservicio DESC, idlinea ASC";
      
      $lineas = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if( $lineas )
      {
         foreach($lineas as $l)
            $linealist[] = new linea_servicio_cliente($l);
      }
      return $linealist;
   }
   
   public function last_from_cliente($codcliente, $offset=0)
   {
      $linealist = array();
      
      $sql = "SELECT * FROM ".$this->table_name." WHERE idservicio IN
         (SELECT idservicio FROM servicioscli WHERE codcliente = ".$this->var2str($codcliente).")
         ORDER BY idservicio DESC, idlinea ASC";
      
      $lineas = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if( $lineas )
      {
         foreach($lineas as $l)
            $linealist[] = new linea_servicio_cliente($l);
      }
      return $linealist;
   }
   
   public function count_by_articulo()
   {
      $lineas = $this->db->select("SELECT COUNT(DISTINCT referencia) as total FROM ".$this->table_name.";");
      if($lineas)
      {
         return intval($lineas[0]['total']);
      }
      else
         return 0;
   }
}

