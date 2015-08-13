<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2014-2015  Carlos Garcia Gomez  neorazorx@gmail.com
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

require_once 'plugins/facturacion_base/extras/fs_pdf.php';
require_once 'extras/phpmailer/class.phpmailer.php';
require_once 'extras/phpmailer/class.smtp.php';
require_model('cliente.php');
require_model('impuesto.php');
require_model('servicio_cliente.php');

/**
 * Esta clase agrupa los procedimientos de imprimir/enviar presupuestos y servicios.
 */
class imprimir_servicio extends fs_controller
{
   public $cliente;
   public $impresion;
   public $impuesto;
   public $servicio;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'imprimir', 'ventas', FALSE, FALSE);
   }
   
   protected function process()
   {
      $this->cliente = FALSE;
      $this->impuesto = new impuesto();
      $this->servicio = FALSE;
      
      /// obtenemos los datos de configuración de impresión
      $this->impresion = array(
          'print_ref' => '1',
          'print_dto' => '1',
          'print_alb' => '0'
      );
      $fsvar = new fs_var();
      $this->impresion = $fsvar->array_get($this->impresion, FALSE);
      
     
     if (isset($_REQUEST['id']) )
      {
         $serv = new servicio_cliente();
         $this->servicio = $serv->get($_REQUEST['id']);
         if($this->servicio)
         {
            $cliente = new cliente();
            $this->cliente = $cliente->get($this->servicio->codcliente);
         }
         
         if( isset($_POST['email']) )
         {
            $this->enviar_email('pedio');
         }
         else
            $this->generar_pdf_servicio();
      }
      
      $this->share_extensions();
   }
   
   private function share_extensions()
  {
      $extensiones = array(
        array(
              'name' => 'imprimir_servicio',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_servicio',
              'type' => 'pdf',
              'text' => ucfirst(FS_SERVICIO).' simple',
              'params' => ''
          ),
          array(
              'name' => 'email_servicio',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_servicio',
              'type' => 'email',
              'text' => ucfirst(FS_SERVICIO).' simple',
              'params' => ''
          ),    
     );
      foreach($extensiones as $ext)
      {
         $fsext = new fs_extension($ext);
         if( !$fsext->save() )
         {
            $this->new_error_msg('Error al guardar la extensión '.$ext['name']);
         }
      }
   }
   
   private function generar_pdf_servicio($archivo = FALSE)
   {
      if( !$archivo )
      {
         /// desactivamos la plantilla HTML
         $this->template = FALSE;
      }
      
      $pdf_doc = new fs_pdf();
      $pdf_doc->pdf->addInfo('Title', ucfirst(FS_SERVICIO).' '. $this->servicio->codigo);
      $pdf_doc->pdf->addInfo('Subject', ucfirst(FS_SERVICIO).' de cliente ' . $this->servicio->codigo);
      $pdf_doc->pdf->addInfo('Author', $this->empresa->nombre);
      
      $lineas = $this->servicio->get_lineas();
      $lineas_iva = $this->get_lineas_iva($lineas);
      if($lineas)
      {
         $linea_actual = 0;
         $pagina = 1;
         
         /// imprimimos las páginas necesarias
         while( $linea_actual < count($lineas) )
         {
            $lppag = 35;
            
            /// salto de página
            if($linea_actual > 0)
            {
               $pdf_doc->pdf->ezNewPage();
            }
            
            /// ¿Añadimos el logo?
            if( file_exists('tmp/'.FS_TMP_NAME.'logo.png') )
            {
               if( function_exists('imagecreatefromstring') )
               {
                  $pdf_doc->pdf->ezImage('tmp/'.FS_TMP_NAME.'logo.png', 0, 200, 'none');
                  $lppag -= 2; /// si metemos el logo, caben menos líneas
               }
               else
               {
                  die('ERROR: no se encuentra la función imagecreatefromstring(). '
                  . 'Y por tanto no se puede usar el logotipo en los documentos.');
               }
            }
            else
            {
               $pdf_doc->pdf->ezText("<b>".$this->empresa->nombre."</b>", 16, array('justification' => 'center'));
               $pdf_doc->pdf->ezText(FS_CIFNIF.": ".$this->empresa->cifnif, 8, array('justification' => 'center'));
               
               $direccion = $this->empresa->direccion;
               if($this->empresa->codpostal)
                  $direccion .= ' - ' . $this->empresa->codpostal;
               if($this->empresa->ciudad)
                  $direccion .= ' - ' . $this->empresa->ciudad;
               if($this->empresa->provincia)
                  $direccion .= ' (' . $this->empresa->provincia . ')';
               if($this->empresa->telefono)
                  $direccion .= ' - Teléfono: ' . $this->empresa->telefono;
               $pdf_doc->pdf->ezText($this->fix_html($direccion), 9, array('justification' => 'center'));
            }
            
            /*
             * Esta es la tabla con los datos del cliente:
             * Pedido:             Fecha:
             * Cliente:             CIF/NIF:
             * Dirección:           Teléfonos:
             */
            $pdf_doc->new_table();
            $pdf_doc->add_table_row(
               array(
                   'campo1' => "<b>".ucfirst(FS_SERVICIO).":</b>",
                   'dato1' => $this->servicio->codigo,
                   'campo2' => "<b>Fecha:</b>",
                   'dato2' => $this->servicio->fecha
               )
            );
            $pdf_doc->add_table_row(
               array(
                   'campo1' => "<b>Cliente:</b>",
                   'dato1' => $this->fix_html($this->servicio->nombrecliente),
                   'campo2' => "<b>".FS_CIFNIF.":</b>",
                   'dato2' => $this->servicio->cifnif
               )
            );
            $pdf_doc->add_table_row(
               array(
                   'campo1' => "<b>Dirección:</b>",
                   'dato1' => $this->fix_html($this->servicio->direccion.' CP: '.$this->servicio->codpostal.
                           ' - '.$this->servicio->ciudad.' ('.$this->servicio->provincia.')'),
                   'campo2' => "<b>Teléfonos:</b>",
                   'dato2' => $this->cliente->telefono1.'  '.$this->cliente->telefono2
               )
            );
            $pdf_doc->save_table(
               array(
                   'cols' => array(
                       'campo1' => array('justification' => 'right'),
                       'dato1' => array('justification' => 'left'),
                       'campo2' => array('justification' => 'right'),
                       'dato2' => array('justification' => 'left')
                   ),
                   'showLines' => 0,
                   'width' => 520,
                   'shaded' => 0
               )
            );
            $pdf_doc->pdf->ezText("\n", 10);
            
            
            /*Esta es la tabla de los datos del servicio y trabajos a realizar*/            
            $pdf_doc->new_table();
            $pdf_doc->add_table_row(
               array(
                   'campo1' => "<b>Material que entrega:</b>",
                   'dato1' => $this->fix_html($this->servicio->material),
                   'campo2' => "<b>material_estado del material</b>",
                   'dato2' => $this->servicio->material_estado
               )
            );
            $pdf_doc->add_table_row(
               array(
                   'campo1' => "<b>Accesorios que entrega</b>",
                   'dato1' => $this->fix_html($this->servicio->accesorios),
               )
            ); 
            $pdf_doc->add_table_row(
               array(
                   'campo1' => "<b>Descripción de la averia:</b>",
                   'dato1' => $this->fix_html($this->servicio->descripcion),
                   'campo2' => "<b>Solución: </b>",
                   'dato2' => $this->servicio->solucion
               )
            );
            $pdf_doc->save_table(
               array(
                   'cols' => array(
                       'campo1' => array('justification' => 'left'),
                       'dato1' => array('justification' => 'left'),
                       'campo2' => array('justification' => 'left'),
                       'dato2' => array('justification' => 'left')
                   ),
                   'showLines' => 0,
                   'width' => 520,
                   'shaded' => 0
               )
            );
            $pdf_doc->pdf->ezText("\n", 10);
            
            
            /*
             * Creamos la tabla con las lineas del servicio:
             * 
             * Descripción    PVP   DTO   Cantidad    Importe
             */
            $pdf_doc->new_table();

            
            if($this->impresion['print_dto'])
            {
               $pdf_doc->add_table_header(
                  array(
                     'descripcion' => '<b>Descripción</b>',
                     'cantidad' => '<b>Cantidad</b>',
                     'pvp' => '<b>PVP</b>',
                     'dto' => '<b>DTO</b>',
                     'importe' => '<b>Importe</b>'
                  )
               );
            }
            else
            {
               $pdf_doc->add_table_header(
                  array(
                     'descripcion' => '<b>Descripción</b>',
                     'cantidad' => '<b>Cantidad</b>',
                     'pvp' => '<b>PVP</b>',
                     'importe' => '<b>Importe</b>'
                  )
               );
            }
            
            for($i = $linea_actual; (($linea_actual < ($lppag + $i)) AND ($linea_actual < count($lineas)));)
            {
               $descripcion = $this->fix_html($lineas[$linea_actual]->descripcion);
               if( $this->impresion['print_ref'] AND !is_null($lineas[$linea_actual]->referencia) )
               {
                  $descripcion = '<b>'.$lineas[$linea_actual]->referencia.'</b> '.$descripcion;
               }
               
               $fila = array(
                  'descripcion' => $descripcion,
                  'cantidad' => $lineas[$linea_actual]->cantidad,
                  'pvp' => $this->show_precio($lineas[$linea_actual]->pvpunitario, $this->servicio->coddivisa),
                  'dto' => $this->show_numero($lineas[$linea_actual]->dtopor, 0) . " %",
                  'importe' => $this->show_precio($lineas[$linea_actual]->pvptotal, $this->servicio->coddivisa)
               );
               
               $pdf_doc->add_table_row($fila);
               $linea_actual++;
            }
            $pdf_doc->save_table(
               array(
                   'fontSize' => 8,
                   'cols' => array(
                       'cantidad' => array('justification' => 'right'),
                       'pvp' => array('justification' => 'right'),
                       'dto' => array('justification' => 'right'),
                       'importe' => array('justification' => 'right')
                   ),
                   'width' => 520,
                   'shaded' => 0
               )
            );
            
            if( $linea_actual == count($lineas) )
            {
               if($this->servicio->observaciones != '')
               {
                  $pdf_doc->pdf->ezText("\n".$this->servicio->observaciones, 9);
               }
            }
            
            $pdf_doc->set_y(80);
            
            /*
             * Rellenamos la última tabla de la página:
             * 
             * Página            Neto    IVA   Total
             */
            $pdf_doc->new_table();
            $titulo = array('pagina' => '<b>Página</b>', 'neto' => '<b>Neto</b>',);
            $fila = array(
                'pagina' => $pagina . '/' . ceil(count($lineas) / $lppag),
                'neto' => $this->show_precio($this->servicio->neto, $this->servicio->coddivisa),
            );
            $opciones = array(
                'cols' => array(
                    'neto' => array('justification' => 'right'),
                ),
                'showLines' => 4,
                'width' => 520
            );
            foreach($lineas_iva as $li)
            {
               $imp = $this->impuesto->get($li['codimpuesto']);
               if($imp)
               {
                  $titulo['iva'.$li['iva']] = '<b>'.$imp->descripcion.'</b>';
               }
               else
                  $titulo['iva'.$li['iva']] = '<b>'.FS_IVA.' '.$li['iva'].'%</b>';
               
               $fila['iva'.$li['iva']] = $this->show_precio($li['totaliva'], $this->servicio->coddivisa);
               
               if($li['totalrecargo'] != 0)
               {
                  $fila['iva'.$li['iva']] .= ' (RE: '.$this->show_precio($li['totalrecargo'], $this->servicio->coddivisa).')';
               }
               
               $opciones['cols']['iva'.$li['iva']] = array('justification' => 'right');
            }
            
            if($this->servicio->totalirpf != 0)
            {
               $titulo['irpf'] = '<b>'.FS_IRPF.' '.$this->servicio->irpf.'%</b>';
               $fila['irpf'] = $this->show_precio(0 - $this->servicio->totalirpf);
               $opciones['cols']['irpf'] = array('justification' => 'right');
            }
            
            $titulo['liquido'] = '<b>Total</b>';
            $fila['liquido'] = $this->show_precio($this->servicio->total, $this->servicio->coddivisa);
            $opciones['cols']['liquido'] = array('justification' => 'right');
            $pdf_doc->add_table_header($titulo);
            $pdf_doc->add_table_row($fila);
            $pdf_doc->save_table($opciones);
            
            $pdf_doc->pdf->addText(10, 10, 8, $pdf_doc->center_text($this->fix_html($this->empresa->pie_factura), 153), 0, 1.5);
            
            $pagina++;
         }
      }
      
      if($archivo)
      {
         if( !file_exists('tmp/'.FS_TMP_NAME.'enviar') )
            mkdir('tmp/'.FS_TMP_NAME.'enviar');
         
         $pdf_doc->save('tmp/'.FS_TMP_NAME.'enviar/'.$archivo);
      }
      else
         $pdf_doc->show();
   }
   
   private function enviar_email($doc)
   {
      if( $this->empresa->can_send_mail() )
      {
         if( $_POST['email'] != $this->cliente->email )
         {
            $this->cliente->email = $_POST['email'];
            $this->cliente->save();
         }
         
         /// obtenemos la configuración extra del email
         $mailop = array(
             'mail_host' => 'smtp.gmail.com',
             'mail_port' => '465',
             'mail_user' => '',
             'mail_enc' => 'ssl'
         );
         $fsvar = new fs_var();
         $mailop = $fsvar->array_get($mailop, FALSE);
         
        
          $filename = 'servicio_'.$this->servicio->codigo.'.pdf';
          $this->generar_pdf_servicio($filename);      
            
         if( file_exists('tmp/'.FS_TMP_NAME.'enviar/'.$filename) )
         {
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPAuth = TRUE;
            $mail->SMTPSecure = $mailop['mail_enc'];
            $mail->Host = $mailop['mail_host'];
            $mail->Port = intval($mailop['mail_port']);
            
            $mail->Username = $this->empresa->email;
            if($mailop['mail_user'] != '')
            {
               $mail->Username = $mailop['mail_user'];
            }
            
            $mail->Password = $this->empresa->email_password;
            $mail->From = $this->empresa->email;
            $mail->FromName = $this->user->nick;
            $mail->CharSet = 'UTF-8';
            
          
            $mail->Subject = $this->empresa->nombre . ': Su '.FS_SERVICIO.' '.$this->servicio->codigo;
            $mail->AltBody = 'Buenos días, le adjunto su '.FS_SERVICIO.' '.$this->servicio->codigo.".\n".$this->empresa->email_firma;

            $mail->WordWrap = 50;
            $mail->MsgHTML( nl2br($_POST['mensaje']) );
            $mail->AddAttachment('tmp/'.FS_TMP_NAME.'enviar/'.$filename);
            $mail->AddAddress($_POST['email'], $this->cliente->razonsocial);
            $mail->IsHTML(TRUE);
            
            if( $mail->Send() )
            {
               $this->new_message('Mensaje enviado correctamente.');
            }
            else
               $this->new_error_msg("Error al enviar el email: " . $mail->ErrorInfo);
            
            unlink('tmp/'.FS_TMP_NAME.'enviar/'.$filename);
         }
         else
            $this->new_error_msg('Imposible generar el PDF.');
      }
   }
   
   private function fix_html($txt)
   {
      $newt = str_replace('&lt;', '<', $txt);
      $newt = str_replace('&gt;', '>', $newt);
      $newt = str_replace('&quot;', '"', $newt);
      $newt = str_replace('&#39;', "'", $newt);
      return $newt;
   }
   
   private function get_lineas_iva($lineas)
   {
      $retorno = array();
      $lineasiva = array();
      
      foreach($lineas as $lin)
      {
         if( isset($lineasiva[$lin->codimpuesto]) )
         {
            $lineasiva[$lin->codimpuesto]['neto'] += $lin->pvptotal;
            $lineasiva[$lin->codimpuesto]['totaliva'] += ($lin->pvptotal*$lin->iva)/100;
            $lineasiva[$lin->codimpuesto]['totalrecargo'] += ($lin->pvptotal*$lin->recargo)/100;
            $lineasiva[$lin->codimpuesto]['totallinea'] = $lineasiva[$lin->codimpuesto]['neto']
                    + $lineasiva[$lin->codimpuesto]['totaliva'] + $lineasiva[$lin->codimpuesto]['totalrecargo'];
         }
         else
         {
            $lineasiva[$lin->codimpuesto] = array(
                'codimpuesto' => $lin->codimpuesto,
                'iva' => $lin->iva,
                'recargo' => $lin->recargo,
                'neto' => $lin->pvptotal,
                'totaliva' => ($lin->pvptotal*$lin->iva)/100,
                'totalrecargo' => ($lin->pvptotal*$lin->recargo)/100,
                'totallinea' => 0
            );
            $lineasiva[$lin->codimpuesto]['totallinea'] = $lineasiva[$lin->codimpuesto]['neto']
                    + $lineasiva[$lin->codimpuesto]['totaliva'] + $lineasiva[$lin->codimpuesto]['totalrecargo'];
         }
      }
      
      foreach($lineasiva as $lin)
      {
         $retorno[] = $lin;
      }
      
      return $retorno;
   }
}