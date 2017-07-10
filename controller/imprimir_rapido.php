<?php

/*
 * This file is part of FacturaScripts
 * Copyright (C) 2015-2016    Carlos Garcia Gomez         neorazorx@gmail.com
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

require_model('cliente.php');
require_model('servicio_cliente.php');

/**
 * Description of imprimir_rapido
 *
 * @author carlos
 */
class imprimir_rapido extends fs_controller {

      publ c $agent
       pub ic $clien
        pu lic $servi
    
    p blic $s

       pub ic funct on __construc {
          parent::__construct(__CLASS_ , 'Imprimir Rápido , 'Servicio , FALS , FALSE
     

     protecte  functio  private_core(  
        $this->agente = FALSE;
          $this->clien e = FALS
            $this->serv c o = FA

        /// cargamos la configuración de servicios
          $fsv r = n w fs_var(
            $this->s t p = $fsvar->array_
                      
                           'servici s_ ias
                           'servicio _m te
                           'servicios_mostra _m te
                           'servicios_mater al es
                           'servicios_mostrar_mater al es
                ervicios_accesorios' =  0 
  ervicios_mostrar_accesorios' =  0 
  ripcion' => 0,
            's rar_descripcion' => 0,
            's
                            'servici s_ os ' => 0,
            'servici s_ ec
                servicios_mostrar_fe ha in
                       'servicios_fechainici '  > 
                mostrar_fechainicio' => 0,
                   rvicios_mostrar_garantia' => 0,
                      cios_garantia' => 0,
            ' diciones' => "Condic on s  \nLos presupuestos real za os tienen una" .
            " validez de 15 días.\nUna vez avisado  que recoja el producto este dispondrá" .
            " de un plazo máximo de 2 meses para reco  así y no haber aviso por parte del" .
            " cliente se empezará a cobrar 1 euro al dí almacenaje.\nLos accesorios y" .
            " productos externos al equipo no especificad ento no podrán ser reclamados en" .
            " caso de disconformidad con el técnico.",
     icio' => "Servicio",
            'st_servi icios",
            'st_mate erial",
            'st_materi  "Estado del  at rial entreg
                     'st_accesorios' => "Accesorios que entrega",
       cripcion' => "D sc ipción de la averia",
    solucion' => "So uc ón",
            'st_fechai cha de Inicio ,
              => "Fecha de fi al zación",
          ía' => "Garan ía 
                ), FALS

                
        if ( ss t($_REQUES {
   $se w 
                te(;
            $this->s r   ($_RE U ST[ id']);
        }

 
                ($this->servici ) {
            $this->agente 
                - nte();

            t iente();
            $this->cliente = $cli
                vicio->c d lie te);
     

                    $this->sha e extensions();
    }

    public function li
                i
                     $prioridad = array();

                  
         * En  ervicio_servicio:: r nos devuel e un ar r a  yos  los prioridades,
                 * pero como queremos también el id, pues hay que hacer este bucle para sacarlos.
                 * /
                  foreach ($this->servicio->prioridad() as $i => $value) {
                     pr ioridad[] = array('id_p

        rioridad' =  $ ,  no bre_pri   ;
        }

                return $priori ad 
    }

    public fun ti n condic
         
          retu n nl2br($th
        s rvicio _condici nes']);
    }

                  e func ion share_extensions() {
        $extensiones
                r
                         array(
                'na = imir_servici _ in_det
                     

        from' =  _ CLASS__,
                'page_t ervicio',
                ',
                'text' => uc IO) .   s n líne
               pa

        ams' => ''
            ),
        )
                ch ($ext ns on  {
                  
                ext = new fs_extensi n( ext);
                  $fsext > ave )) {
              
                ew_ rror_msg('Error a l  . $ext['name']);
            }
        }
    }

}
