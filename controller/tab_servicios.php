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

require_model('servicio_cliente.php');


class tab_servicios extends fs_controller 
{

public $descripcion;
public $solucion;
public $material;
public $estado;
public $accesorios;

public function __construct()
   {
      parent::__construct(__CLASS__, 'servicio', 'ventas', FALSE, FALSE);
   }
   
 protected function private_core()
 {
   $this->descripcion = NULL;
   $this->solucion = NULL;
   $this->material = NULL;
   $this->estado = NULL;
   $this->accesorios = NULL;

     
   $this->share_extension();   
 }
 
  private function share_extension()
   {
      /// metemos la pestaña servicios en la página de nueva venta
      $fsext = new fs_extension();
      $fsext->name = 'servicio_cliente';
      $fsext->from = __CLASS__;
      $fsext->to = 'nueva_venta';
      $fsext->type = 'tab';
      $fsext->text = '<span class="glyphicon glyphicon-wrench" aria-hidden="true"></span><span class="hidden-xs">&nbsp; Servicio</span>';
      $fsext->save();
      
   }
   
}