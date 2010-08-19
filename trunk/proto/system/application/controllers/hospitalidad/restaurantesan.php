<?php
class Restaurante extends Controller {
	var $errores;
	
	function Restaurante(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->errores=false;
		if($this->uri->segment(3)!='autentificar')
			$this->_chequeo();
		//echo $this->uri->uri_string();
		//$this->datasis->modulo_id(814,1);
	}
	function index(){
		$this->rapyd->load("datatable");
		$mesonero=$this->session->userdata['mesonero'];

		$table = new DataTable(null);
		$table->cell_attributes = 'style="vertical-align:middle; text-align: center;"';
		
		$table->db->select(array('numero','fecha','mesa','hora','mesonero'));
		$table->db->from("rfac");
		$table->db->where("tipo='P' AND mesonero='$mesonero'");
		$table->db->orderby("mesa");

		$table->per_row = 4;
		$table->per_page = $this->datasis->dameval("SELECT COUNT(*) FROM rfac WHERE tipo='P' AND mesonero='$mesonero'");
		$table->cell_template = '<div style="background-color: #FFFFFF;"><a href="'.site_url('hospitalidad/restaurante/modificar/<#numero#>').'" >'. image('mesa.png','Agregar',array('border'=>0,'align'=>'center')).'</a>'.'<br><b><#mesa#></b><br> <b class="mininegro"><dbdate_to_human><#fecha#></dbdate_to_human> <#hora#></b></div>';
		$table->build();

		$link=site_url('hospitalidad/restaurante/mesa');
		$prop2=array('type'=>'button','value'=>'Agregar Mesa','name'=>'add'  ,'onclick' => "javascript:window.location = '$link';" ,'style'=>'font-size:18');
		$link=site_url('hospitalidad/restaurante/cese');
		$prop1=array('type'=>'button','value'=>'Cerrar','name'=>'cese'  ,'onclick' => "javascript:window.location = '$link';" ,'style'=>'font-size:18');

		$data['content'] = $table->output.'<br><center>'.form_input($prop2).'<br>'.form_input($prop1).'</center>';
		$data['title']   = "$mesonero";
		$data["head"]    = script("keyboard.js").script("prototype.js");
		$data["head"]   .= script("effects.js").style("ventanas.css").style("restaurant.css").$this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);
	}

	function cuenta($numero){
		$out='<table id="taCuenta" class="ui-table-nav" cellspacing=0>';
		$out.='<thead><tr><th class="ui-table-nav1">Descripci&oacute;n</th><th>Precio</th><th>Cantidad</th><th>Importe</th><th>Hora</th></tr></thead>';
		$out.='<tbody>';
		
		$query = $this->db->query("SELECT servicio,impuesto,gtotal,stotal FROM rfac WHERE numero='$numero'");
		if ($query->num_rows() > 0){
			$row = $query->row();
			$servicio = number_format($row->servicio,2,',', '.');
			$importe  = number_format($row->gtotal+$row->servicio,2,',', '.');
			$stotal   = number_format($row->stotal,2,',', '.');
			$impuesto = number_format($row->impuesto,2,',', '.');
		} 
		//SELECT SUM(importe) AS gtotal, SUM(importe-(importe*100/(100+impuesto))) AS impuesto ,SUM((importe*100/(100+impuesto))*servicio/100) AS servicio FROM ritems WHERE numero='_0001831'
		$itimporte=$itimpuesto=0;
		$query = $this->db->query("SELECT codigo,descri1,precio,cantidad,importe,hora,servicio,importe*impuesto/100 AS cimpuesto FROM ritems WHERE numero='$numero'");
		if ($query->num_rows() > 0){ $i=0;
			foreach ($query->result() as $row){$i++;
				$itimporte = number_format($row->importe,2,',', '.');
				$clase= ($i%2==0) ? "class='par'":"class='inpar'"; 
				$out.="<tr $clase ><td>$row->descri1</td><td align='right'>$row->precio</td><td align='right'>$row->cantidad</td><td align='right'>$itimporte</td><td align='center'>$row->hora</td></tr>\n";
			}
		}else{
			$out.="<tr class='par'><td colspan=5 align='center'><b>No se han agregado art&iacute;culos</b></td></tr>\n";
		}
		$out.='<tfoot>';
		$out.='<tr><th colspan=3 align="right">Sub-total:<br>Impuesto:<br>Servicio:</th><th align="right">'."$stotal<br>$impuesto<br>$servicio".'</th><th>&nbsp</th></tr>';
		$out.='<tr><th colspan=3 align="right"><h2>Importe:</h2></th><th align="right">'."<h2>$importe</h2>".'</th><th>&nbsp</th></tr></tfoot>';
		$out.='</table>';
		return '<div id="pCuenta"><div class="alert">'.$this->errores.'</div>'.$out.'</div>';
	}

	function menu($numero){
		$this->load->helper('form');
		$tit='';
		$out=$grupo='';
		$query = $this->db->query("SELECT codigo,grupo, descgru,descri1 FROM menu ORDER BY grupo");
		foreach ($query->result() as $row){
			if ($grupo!=$row->grupo){
				$tit.="<li><a href='#m$row->grupo'><span>$row->descgru</span></a></li>";
				$out.="</div><div id='m$row->grupo'>";
				$grupo=$row->grupo;
			}
			$value=ucfirst(strtolower($row->descri1));
			$data = array('name' => $row->codigo,
			  'id'      => $row->codigo,
			  'content' => $value,
			  'value'   => $value,
			  'type'    => 'button',
			  'style'   =>'height: 60px;width: 80px;font-size: 12px;float: left',
			  'onClick' => "enviar('$row->codigo')",);
			$out.=form_button($data);
		}
		$cuenta=$this->cuenta($numero);

		$out='<div id="menu"><ul>'.$tit.'</ul><table cellspacing=0><tr><td valign="top">'.$cuenta.'</td><td valign="top">'.substr($out,6).'</td><tr></table></div>';
		return $out;
	}

	function envform($numero){
		$prop =array('name'=> 'codigo','id'=>'codigo','type'=>'hidden');
		$prop2=array('type'=>'button','value'=>' + ','name'=>'mas'  ,'onclick' => "suma();" ,'style'=>'font-size:28');
		$prop3=array('type'=>'button','value'=>' - ','name'=>'menos','onclick' => "resta();",'style'=>'font-size:28');
		$prop4=array('type'=>'button','value'=>'Guardar','name'=>'guardar','onclick' => "guardar_pedido();", 'style'=>'font-size:28');

		$form= '<div id="envform" style="display: none; font-family:Verdana, Arial, Helvetica, sans-serif;font-size:28;">';
		$form .= form_open("hospitalidad/restaurante/registrar/$numero/");
		$form .= '<div id="pNombre" ></div>';
		$form .= 'Cantidad '.form_input(array('name'=>'cantidad','id'=>'cantidad','value'=>'1','size'=>'4'));
		$form .= form_input($prop);
		$form .= form_input($prop2);
		$form .= form_input($prop3);
		$form .= form_input($prop4);
		$form .= form_close();
		$form .= '</div>';
		return $form;
	}

	function modificar($numero){
		$this->rapyd->load("fields"); 

		$menu=$this->menu($numero);
		$regresar=site_url('hospitalidad/restaurante');
		$form='<center>'.$this->envform($numero).'</center>';
		$link=site_url("hospitalidad/restaurante/procesar/$numero");
		$link2=site_url("hospitalidad/restaurante/cimprin/$numero");
		$link3=site_url("hospitalidad/restaurante/impcuenta/$numero");

		$data['script'] =<<<scriptab
		<script type='text/javascript'>
		var aparece=false;

		function enviar(valcod){
			var codigo = document.getElementById('codigo');
			var boton  = document.getElementById(valcod);
			var cana   = document.getElementById('cantidad');
			//alert(boton.value);
			$("#pNombre").text(boton.value);

			cana.value=1;
			codigo.value=valcod;
			if(!aparece){
				$("#envform").show("slow");
				aparece=true;
			}
		}

		function guardar_pedido(){
			var url = '$link';
			$.ajax({
				type: "POST",
				url: url,
				data: $("input").serialize(),
				success: function(msg){
					//alert(msg);
					$("#envform").hide("slow");
					aparece=false;
					$("#pCuenta").replaceWith(msg);
					jQuery('#taCuenta').Scrollable(400, 400);
				}
			});
		}

		function m_print(){
			var url = '$link2';
			$.ajax({
				url: url,
				success: function(msg){
					//alert(msg);
					window.location='$regresar';
				}
			});
		}

		function cuenta(){
			var url = '$link3';
			$.ajax({
				url: url,
				success: function(msg){
					//alert(msg);
				}
			});
		}

		function suma(){
			var cant = document.getElementById('cantidad');
			cant.value=parseInt(cant.value)+1;
		}
		function resta(){
			var cant = document.getElementById('cantidad');
			cant.value=parseInt(cant.value)-1;
		}
		
		//$(function() { $('#menu > ul').tabs({ fx: { opacity: 'toggle' } }).tabs(); });
		$(function() { $('#menu > ul').tabs();  });
		jQuery(document).ready(function() { jQuery('#taCuenta').Scrollable(400, 400); });

		</script>
scriptab;
		$prop2=array('type'=>'button','value'=>'Regresar','name'=>'mas'  ,'onclick' => "javascript:m_print();" ,'style'=>'font-size:18');
		$prop3=array('type'=>'button','value'=>'Cuenta','name'=>'cuenta' ,'onclick' => "javascript:cuenta();" ,'style'=>'font-size:18');

		$data['menu']     =$menu;
		$data['extras']   ='';
		$data['content']  = $form.'<br>'.form_input($prop2).form_input($prop3);
		$data['title']    = 'Mesa <b>'.$this->datasis->dameval("SELECT mesa FROM rfac WHERE numero='$numero'").'</b>';

		$data["head"]  = script("webtoolkit.scrollabletable.js");
		$data["head"]  .= script("jquery.pack.js");
		$data["head"]  .= script("ui.core.js");
		$data["head"]  .= script("ui.tabs.js");
		$data["head"]  .= script("webtoolkit.jscrollable.js");
		$data["head"]  .= style("ui.css");
		//$data["head"]  .= style("ui.tabs.css");
		$data["head"]  .= $this->rapyd->get_head();
		$this->load->view('view_restaurante_modi', $data);
	}

	function guardar($numero=null){
		$codigo = $this->input->post('codigo');
		$cant   = $this->input->post('cantidad');
		if(empty($numero) OR $codigo===false OR $cant==false){
			$this->errores='Parametros insuficientes o cantidad 0';
			return false;
		}

		if ($cant<0){
			$hay = $this->datasis->dameval("SELECT SUM(cantidad) FROM ritems WHERE numero='$numero' AND codigo='$codigo' GROUP BY numero, codigo");
			if ($hay<abs($cant)) {
				$this->errores='Producto no se ha pedido o devoluci&oacute;n mayor que lo pedido';
				return false;
			}
		}
		
		$data=array();
		$data['numero']  =$numero;
		$data['codigo']  =$codigo;
		$data['cantidad']=$cant;
		$data['hora']    = date("H:i:s");

		$query = $this->db->query("SELECT cajero,cuentas,mesonero,mesa,fecha,impuesto,servicio,stotal,gtotal FROM rfac WHERE numero='$numero'");
		if($query->num_rows() > 0){
			$row = $query->row();
			$data['cajero']  = $row->cajero;
			$data['cuenta']  = $row->cuentas;
			$data['mesonero']= $row->mesonero;
			$data['mesa']    = $row->mesa;
			$data['fecha']   = $row->fecha;
			$asfac['impuesto']=$row->impuesto;
			$asfac['servicio']=$row->servicio;
			$asfac['stotal']  =$row->stotal;
			$asfac['gtotal']  =$row->gtotal;
		}

		$query = $this->db->query("SELECT descri1,impuesto,servicio,grupo,departa,base,precio,ubica,base FROM menu WHERE codigo='$codigo'");
		if($query->num_rows() > 0){
			$row = $query->row();
			$data['descri1']  = $row->descri1;
			$data['impuesto'] = $row->impuesto;
			$data['precio']   = $row->precio;
			$data['importe']  = $row->precio*$cant;
			$data['grupo']    = $row->grupo;
			$data['servicio'] = $row->servicio;
			$data['departa']  = $row->departa;
			$data['ubica']    = $row->ubica;
			$asfac['impuesto']+=$row->base*$cant*($row->impuesto/100);
			$asfac['servicio']+=$row->base*$cant*($row->servicio/100);
			$asfac['stotal']  +=$row->base*$cant;
			$asfac['gtotal']  +=$data['importe'];
			$asfac['gtotal']  +=$row->base*$cant*($row->servicio/100);
		}
		$mSQL=$this->db->insert_string('ritems',$data);
		$this->db->simple_query($mSQL);
		$mSQL=$this->db->update_string('rfac',$asfac,"numero='$numero'"); 
		$this->db->simple_query($mSQL);
		
		return true;
	}

	function mesa(){
		$this->load->library('validation');
		$this->rapyd->load('dataform');  
		$mesonero=$this->session->userdata['mesonero'];

		$rform = new DataForm("hospitalidad/restaurante/mesa/process");
		$rform->mesa = new inputField("Mesa", "mesa");  
		$rform->mesa->rule = "trim|required|max_length[4]|callback_chmesa";
		$rform->mesa->style='font-size:28';
		$rform->mesa->size=6;
		$rform->mesa->maxlength=4;
		$rform->mesonero= new dropdownField("Mesonero", "mesonero");  
		$rform->mesonero->options("SELECT mesonero,nombre FROM meso ORDER BY nombre ");
		$rform->mesonero->status = "modify";
		$rform->mesonero->insertValue = $mesonero;
		$rform->mesonero->style='font-size:28';
		$rform->mesonero->build();
		$rform->submit("btnsubmit","SUBMIT");
		$rform->build_form();

		if ($rform->on_success()){  
			$this->_abrirmesa($rform->mesa->newValue);
		}  

		$back=site_url('hospitalidad/restaurante');
		$prop3=array('type'=>'button','value'=>RAPYD_BUTTON_BACK,'name'=>'regresar' ,'style'=>'font-size:28','onclick'=>"javascript:window.location='$back'");
		$prop4=array('type'=>'submit','value'=>RAPYD_BUTTON_SAVE,'name'=>'guardar', 'style'=>'font-size:28');

		$form= '<center><div class="alert">'.$rform->error_string.'</div><div id="envform" style=" font-family:Verdana, Arial, Helvetica, sans-serif;font-size:28;">';
		$form .= $rform->form_open;
		$form .= '<table style="font-size:28"><tr><td>Mesa</td><td>'.$rform->mesa->output.'</td></tr>';
		$form .= '<tr><td>Mesonero</td><td>'.$rform->mesonero->output.'</td></tr>';//form_input($prop3);
		$form .= '<tr><td>'.form_input($prop3).'</td><td align="right">'.form_input($prop4).'</td></tr></table>';
		$form .= $rform->form_close;
		$form .= '</div></center>';

		$data['content'] = '<br><br><br>'.$form;
		$data['title']   = "";
		$data["head"]    = script("jquery.pack.js");
		$data["head"]   .= script("jquery.keypad.min.js");
		$data["head"]   .= style("ventanas.css").style("restaurant.css").style("jquery.rkeypad.css").$this->rapyd->get_head();
		
		$data['script'] =<<<scriptab
		<script type='text/javascript'> jQuery(document).ready(function() { $('#mesa').keypad(); });</script>
scriptab;
		
		$this->load->view('view_ventanas_sola', $data);
	}
	function chmesa($mesa){
		$mesa=str_pad($mesa, 4, '0', STR_PAD_LEFT);
		$cant=$this->datasis->dameval("SELECT COUNT(*) FROM rfac WHERE tipo='P' AND mesa='$mesa'");
		if($cant>0){
			$this->validation->set_message('chmesa', 'La %s ya esta abierta');
			return FALSE;
		}else{
			return TRUE;
		}
	}

	function _abrirmesa($mesa=null){
		if(empty($mesa)){
			redirect("hospitalidad/restaurante/");
		}
		$user=$this->session->userdata('usuario');
		
		$query = $this->db->query('SELECT fechaa,cajero FROM scaj WHERE `status`="A" ORDER BY `fechaa` DESC, `horaa` DESC LIMIT 1');
		if ($query->num_rows() ==1){
			$row = $query->row();
			
			$mesa=str_pad($this->input->post('mesa'), 4, '0', STR_PAD_LEFT);
			$this->db->simple_query("INSERT INTO nedcu (usuario) values ('$user')");
			$numero='_'.str_pad($this->db->insert_id(), 7, '0', STR_PAD_LEFT);
			$data['numero']  = $numero;
			$data['estado']  = $numero;
			$data['llevar']  = 'N';
			$data['tipo']    = 'P';
			$data['cajero']  = $row->cajero;
			$data['cubierto']= 0;
			$data['cuentas'] = 1;
			$data['hora']    = date("H:i:s");
			$data['fecha']   = $row->fechaa;
			$data['mesa']    = $mesa;
			$data['mesonero']= $this->input->post('mesonero');
			
			$mSQL=$this->db->insert_string('rfac',$data);
			$this->db->simple_query($mSQL);
			
			redirect("hospitalidad/restaurante/modificar/$numero");
		} 
		
		$data['content'] = '<center><br><br>Debe estar abierto un cajero antes de abrir una mesa<br>'.anchor('hospitalidad/restaurante','regresar').'</center>';
		$data['title']   = "";
		$this->load->view('view_ventanas_sola', $data);
	}

	function procesar($numero){
		$gguarda=$this->guardar($numero);
		$ccuenta=$this->cuenta($numero);
		echo $ccuenta;
	}

	function autentificar(){
		$this->rapyd->load('dataform');  
		
		$rform = new DataForm("hospitalidad/restaurante/autentificar/process");
		$rform->mesonero = new inputField("Password", "mcla");
		$rform->mesonero->encrypt = false;
		$rform->mesonero->type='password';
		$rform->mesonero->style='font-size:28';
		$rform->mesonero->rule = "required";
		$rform->mesonero->status = "modify";
		$rform->mesonero->size =6;
		$rform->build_form();

		if ($rform->on_success()){  
			$this->_autentificar($rform->mesonero->newValue);
		}  

		$back=site_url('hospitalidad/restaurante');
		$prop4=array('type'=>'submit','value'=>'Aceptar','name'=>'aceptar', 'style'=>'font-size:28');

		$form= '<center><div class="alert">'.$rform->error_string.'</div><div id="envform" style=" font-family:Verdana, Arial, Helvetica, sans-serif;font-size:28;">';
		$form .= $rform->form_open;
		$form .= $rform->mesonero->output;
		$form .= form_input($prop4);
		$form .= $rform->form_close;
		$form .= '</div></center>';

		$data['content'] = $form;
		$data['title']   = "";
		$data["head"]    = script("jquery.pack.js").script("jquery.keypad.min.js");
		$data["head"]   .= style("ventanas.css").style("restaurant.css").style("jquery.rkeypad.css").$this->rapyd->get_head();
		
		$data['script'] ='<script type="text/javascript">
		$(document).ready(function(){
			//$("#mcla").focus();
			$("#mcla").keypad();
		});
		</script>';
		
		$this->load->view('view_ventanas_sola', $data);
	}
	
	function _autentificar($mesoclave){
		$query=$this->db->query("SELECT b.us_codigo,b.us_nombre,a.mesonero FROM meso AS a JOIN usuario AS b ON b.us_codigo=a.usuario WHERE CONCAT(RPAD(a.mesonero,5,'0'),b.us_clave)='$mesoclave'");
		if($query->num_rows() > 0){
			$row = $query->row();
			
			$sess_data = array('usuario' => $row->us_codigo,'mesonero' => $row->mesonero,'nombre'=> $row->us_nombre,'rlogged_in'=> TRUE );
		} else {
			$sess_data = array('rlogged_in'=> FALSE);
		}
		$this->session->set_userdata($sess_data);
		redirect('hospitalidad/restaurante');
	}
	
	function _chequeo(){
		if ($this->session->userdata['rlogged_in']==0)
			redirect('/hospitalidad/restaurante/autentificar');
	}
	
	function cese(){
		$sess_data = array('rlogged_in'=> FALSE);
		$this->session->set_userdata($sess_data);
		redirect('hospitalidad/restaurante');
	}

	function cimprin($numero){
		// F impreso
		$mesonero=$this->session->userdata['mesonero'];
		$where = "numero = '$numero' AND impstatus = 'E'";
		$mSQL="SELECT COUNT(*) FROM ritems WHERE $where";
		$cant=$this->datasis->dameval($mSQL);
		
		if($cant>0){
			$data  = array('impstatus' => 'N');
			$mSQL  = $this->db->update_string('ritems', $data, $where);
			$this->db->simple_query($mSQL);
			//$cmd="./comanda/imprime.pl $numero $mesonero";
			//Echo $cmd;
			//system ($cmd,$retorno);
			//Echo $retorno;
		}
	}

	function impcuenta($numero=''){
		if (empty($numero)) return 0;
		$this->load->helper('file');

		$query = $this->db->query("SELECT servicio,impuesto,gtotal,stotal,mesa,mesonero,CURDATE() AS fecha,CURTIME() AS hora FROM rfac WHERE numero='$numero'");
		if ($query->num_rows() > 0){
			$row = $query->row();
			$servicio = number_format($row->servicio,2,',', '.');
			$importe  = number_format($row->gtotal+$row->servicio,2,',', '.');
			$stotal   = number_format($row->stotal,2,',', '.');
			$impuesto = number_format($row->impuesto,2,',', '.');
			$mesa =$row->mesa;
			$fecha=dbdate_to_human($row->fecha);
			$hora =$row->hora;
			$mesonero =$row->mesonero;
			$piva=number_format(round($row->impuesto*100/$row->stotal,0),2,',', '.');
		}
		$linea =$mesonero."\n";
		$linea.=str_pad($this->datasis->traevalor('TITULO1'), 40, " ", STR_PAD_BOTH)."\n";
		$linea.=str_pad('RIF: '.$this->datasis->traevalor('RIF'), 40, " ", STR_PAD_BOTH)."\n";
		//$linea.=str_pad($this->datasis->traevalor('TITULO2').' '.$this->datasis->traevalor('TITULO3'), 40, " ", STR_PAD_BOTH)."\n";
		$linea.=" Mesa: $mesa Fecha: $fecha $hora\n";
		$linea.="----------------------------------------\n";
		$linea.="  Descrip       Cant  Precio  Importe  \n";
		$linea.="----------------------------------------\n";
		$query = $this->db->query("SELECT codigo,descri1,precio,cantidad,importe,hora,servicio,importe*impuesto/100 AS cimpuesto FROM ritems WHERE numero='$numero'");
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$linea.=str_pad(substr($row->descri1,0,16),16);
				$linea.=str_pad(substr($row->cantidad,0,3),3);
				$linea.=str_pad(number_format($row->precio,2,',', '.'),9,' ',STR_PAD_LEFT);
				$linea.=str_pad(number_format($row->importe,2,',', '.'),10,' ',STR_PAD_LEFT)."\n";
			}
		}else{
			$linea.=str_pad('No se han agregado articulos', 40, " ", STR_PAD_BOTH)."\n";
		}
		
		$linea.="----------------------------------------\n";
		$linea.="            SUB-TOTAL    : ".str_pad($stotal,11,' ',STR_PAD_LEFT)."\n";
		$linea.="            I.V.A. $piva% : ".str_pad($impuesto,11,' ',STR_PAD_LEFT)."\n";
		$linea.="            SERVICIO 10% : ".str_pad($servicio,11,' ',STR_PAD_LEFT)."\n";
		$linea.="            TOTAL        : ".str_pad($importe,11,' ',STR_PAD_LEFT)."\n";
		$linea.="----------------------------------------\n";
		$linea.="RAZON SOCIAL: \n";
		$linea.="\n";
		$linea.=" ---------------------------------------\n";
		$linea.="RIF:\n";
		$linea.=" ---------------------------------------\n";
		$linea.="DIRECCION Y TELEFONO:\n";

		if (!write_file("./comanda/cuentas/$mesa.txt", $linea, 'w')){
			echo 'Fallo, que vaina verda';
		}

	}

	function instalar(){
		$mSQL='ALTER TABLE `meso` DROP `clave`';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `meso` ADD `usuario` CHAR(12) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `meso` ADD UNIQUE `usuario` (`usuario`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `meso` ADD PRIMARY KEY (`mesonero`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `rfac` CHANGE `mesonero` `mesonero` CHAR(5) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `ritems` ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `ritems` ADD `impstatus` CHAR(1) DEFAULT "E" NULL AFTER `id`';
		$this->db->simple_query($mSQL);
	}
}
?>