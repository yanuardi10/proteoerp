<?php
class Restaurante extends Controller {
	function Restaurante(){
		parent::Controller();
		$this->load->library("rapyd");
	}
	function index(){
		$this->rapyd->load("datatable");
		
		$table = new DataTable(null);
		$table->cell_attributes = 'style="vertical-align:middle; text-align: center;"';
		
		$table->db->select(array('numero','fecha','mesa','hora','mesonero'));
		$table->db->from("sfac");
		$table->db->where("tipo='P'");
		$table->db->orderby("mesa");

		$table->per_row = 4;
		$table->per_page = $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE tipo='P'");
		$table->cell_template = '<a href="'.site_url('hospitalidad/restaurante/modificar/<#numero#>').'" >'. image('mesa.png','Agregar',array('border'=>0,'align'=>'center')).'</a>'.'<br><#mesa#><br> <b class="mininegro"><#fecha#> <#hora#></b>';
		$table->build();

		$data['content'] = $table->output;
		$data['title']   = "";
		$data["head"]    = script("keyboard.js").script("prototype.js");
		$data["head"]   .= script("effects.js").style("ventanas.css").style("estilos.css").$this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);

	}

	function ver_tabla($numero){
		$this->rapyd->load("datagrid2");

		$grid = new DataGrid2();

		$grid->db->select(array("codigo","descri1","precio","cantidad","importe","hora"));
		$grid->db->from("sitems");
		$grid->db->where('numero',$numero);

		$grid->totalizar('importe');
		//$grid->add("hospitalidad/restaurante/agregar/$numero");
		$grid->column("Descripci&oacute;n", "descri1");
		$grid->column("Precio"            , "<number_format><#precio#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cantidad"          , "<number_format><#cantidad#>|2|,|.</number_format>",'align=right');
		$grid->column("Importe"           , "<number_format><#importe#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Hora"              , "hora"  ,'align=right');
		$grid->build();

		$data['content'] = $grid->output; //'<input type="button" value="Regresar" name="Back" onclick="history.back()" />'.
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);
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
			$data = array('name' => $row->codigo,
			  'id'   => $row->codigo,
			  'value' => $row->descri1,
			  'type' => 'button',
			  'style'=>'height: 60px;width: 80px',
			  'onClick' => "enviar('$row->codigo')",);
			$out.=form_input($data);
			//$out.=$row->descri1.'<br>';
			
		}
		$out='<div id="container-1"><ul>'.$tit.'</ul>'.substr($out,6).'</div>';
/*
$out.='<div id="container-1">
            <ul>
                <li><a href="#fragment-1"><span>One</span></a></li>
                <li><a href="#fragment-2"><span>Two</span></a></li>
                <li><a href="#fragment-3"><span>Tabs are flexible again</span></a></li>
            </ul>
            <div id="fragment-1">
                <p>First tab is active by default:</p>
                <pre><code>$(&#039;#container&#039;).tabs();</code></pre>
            </div>
            <div id="fragment-2">
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
            </div>
            <div id="fragment-3">
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
            </div>
        </div>';*/
		return $out;
	}


	function modificar($numero){
		$this->rapyd->load("fields"); 

		$iframe = new iframeField("encuenta", "hospitalidad/restaurante/ver_tabla/$numero","210");
		$iframe->build();
		$menu=$this->menu($numero);

		$prop =array('name'=> 'codigo','id'=>'codigo','type'=>'hidden');
		$prop2=array('type'=>'button','value'=>' + ','name'=>'mas'  ,'onclick' => "suma();");
		$prop3=array('type'=>'button','value'=>' - ','name'=>'menos','onclick' => "resta();");
		$prop4=array('type'=>'button','value'=>'Guardar','name'=>'guardar','onclick' => "guardar_pedido();");
		$attributes = array('id' => 'envform','style' => 'display: none;');
		$form  = form_open("hospitalidad/restaurante/registrar/$numero/",$attributes);
		$form .= 'Cantidad '.form_input(array('name'=>'cantidad','id'=>'cantidad','value'=>'1','size'=>'4'));
		$form .= form_input($prop);
		$form .= form_input($prop2);
		$form .= form_input($prop3);
		$form .= form_input($prop4);
		//$form .= form_submit('mysubmit', 'Guardar');
		$form .= form_close();

		$link=site_url("hospitalidad/restaurante/guardar/$numero");
		$data['script'] =<<<scriptab
		<script type='text/javascript'>
		var aparece=false;

		function enviar(valcod){
			var codigo = document.getElementById('codigo');
			codigo.value=valcod;
			if(!aparece){
				$("#envform").show();
				aparece=true;
			}
		}

		function guardar_pedido(){
			var url = '$link';
			alert($("input").serialize());
			$.ajax({
				type: "POST",
				url: url,
				data: $("envform").serialize(),
				success: function(msg){
				parent.encuenta.location.reload();
				alert( "Data Saved: " + msg );
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
		$(function() { $('#container-1').tabs(); });
		</script>
scriptab;

		$data['content']  =  '<table>';
		$data['content'] .= '<tr><td>'.$iframe->output.'</td><td>'.$form.'</td></tr>';
		$data['content'] .= '</table>'.$menu;
		$data['title']    = "<h1>Comanda</h1>";
		$data["head"]     = script("keyboard.js");
		//$data["head"]    .= script("prototype.js");
		//$data["head"]    .= script("tabber.js").script("effects.js");
		$data["head"]    .= script("jquery-1.2.6.pack.js");
		$data["head"]    .= script("jquery.history_remote.pack.js");
		$data["head"]    .= script("jquery.tabs.pack.js");
		$data["head"]    .= style("jquery.tabs.css");
		$data["head"]    .= $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);
	}

	function agregar($numero){
		$this->rapyd->load("datatable");
		$query = $this->db->query("SELECT grupo,descri1 FROM grme");

		$select=array("grupo","descri1");

		$table = new DataTable(null);
		$table->cell_attributes = 'style="vertical-align:middle;"';

		$table->db->select($select);
		$table->db->from("grme");

		$table->per_row = 1;
		$table->per_page = $this->datasis->dameval("SELECT COUNT(*) FROM grme");
		$table->cell_template = "<a href='#' onclick=\"platos('<#grupo#>'); return false;\" >". image('b1.png','Agregar',array('border'=>0,'align'=>'center')).'</a>'.'<#descri1#>';
		$table->build();
		
		$out =$grup= '';
		$tablas=$pivote=array();
		$mc  = $this->db->query("SELECT codigo,grupo,descri1 FROM menu ORDER BY grupo");
		$prop=array('border'=>'0');
		$pass=true;
		foreach( $mc->result() as $row ){
			if($grup != $row->grupo){
				$grup= $row->grupo;
				$out.="</ul><ul id='$row->grupo' style='display: none;'>";
				$pass=false;
			}
			$out .= '<li><a href=# onclick="enviar(\''.$row->codigo.'\')" >'.$row->descri1.'</a></li>';
		}$out.='</ul>'; $out=substr($out, 5);
		

		$prop =array('name'=> 'codigo','id'=>'codigo','type'=>'hidden');
		$prop2=array('type'=>'button','value'=>' + ','name'=>'mas'  ,'onclick' => "suma();");
		$prop3=array('type'=>'button','value'=>' - ','name'=>'menos','onclick' => "resta();");

		$attributes = array('id' => 'envform','style' => 'display: none;');
		$form  = form_open("hospitalidad/restaurante/registrar/$numero/",$attributes);
		$form .= 'Cantidad '.form_input(array('name'=>'cantidad','id'=>'cantidad','value'=>'1','size'=>'4'));
		$form .= form_input($prop);
		$form .= form_input($prop2);
		$form .= form_input($prop3);
		$form .= form_submit('mysubmit', 'Guardar');
		$form .= form_close();

		$data['script'] = "<script type='text/javascript'> 
		var esta= new String ('');
		function platos(id){
			if (esta.length>0 & esta!=id) Effect.toggle(esta, 'appear');
			Effect.toggle(id, 'appear');
			esta=id;
		}
		function enviar(valcod){
			var codigo = document.getElementById('codigo');
			codigo.value=valcod;
			Effect.toggle('envform', 'appear');
		}
		function suma(){
			var cant = document.getElementById('cantidad');
			cant.value=parseInt(cant.value)+1;
		}
		function resta(){
			var cant = document.getElementById('cantidad');
			cant.value=parseInt(cant.value)-1;
		}
		</script>";
		$data['content'] = '<table><tr><td>'.$table->output.'</td><td>'.$form.$out.'</td></tr></table>';
		$data['title']   = "";
		$data["head"]    = script("keyboard.js").script("prototype.js");
		$data["head"]   .= script("effects.js").style("ventanas.css").$this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);
	}

	function registrar($numero){
		$codigo = $this->input->post('codigo');
		$cant   = $this->input->post('cantidad');

		$data=array();
		$data['numero']  =$numero;
		$data['codigo']  =$codigo;
		$data['cantidad']=$cant;
		$data['hora']    = date("i:s");

		$query = $this->db->query("SELECT cajero,cuentas,mesonero,mesa,fecha,impuesto,servicio,stotal,gtotal FROM sfac WHERE numero='$numero'");
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
		$mSQL=$this->db->insert_string('sitems',$data);
		var_dum($this->db->simple_query($mSQL));
		$mSQL=$this->db->update_string('sfac',$asfac,"numero='$numero'"); 
		var_dum($this->db->simple_query($mSQL));
		//print_r($data);
		//print_r($asfac);
		
		//redirect('hospitalidad/restaurante/');
	}

	function guardar($numero){
		$this->registrar($numero);
		echo 'Listo!';
	}
}
?>