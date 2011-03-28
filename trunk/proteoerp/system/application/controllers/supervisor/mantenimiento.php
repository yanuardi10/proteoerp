<?php
class Mantenimiento extends Controller{

	function Mantenimiento(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('900',1);
	}

	function index(){
		$list = array();
		$list[]=anchor('supervisor/mantenimiento/bprefac','Borrar PreFacturas menores o iguales al d&iacute;a de ayer');
		$list[]=anchor('supervisor/mantenimiento/puertosdir','Descargar PUERTOS.DIR');
		$list[]=anchor('supervisor/mantenimiento/bmodbus','Vaciar la tabla ModBus');
		$list[]=anchor('supervisor/mantenimiento/centinelas','Centinelas');
		$list[]=anchor('supervisor/mantenimiento/reparatabla','Reparar Tablas');
		$list[]=anchor('supervisor/mantenimiento/clinconsis','Incosistencias Clientes');
		$list[]=anchor('supervisor/mantenimiento/calcosto','Recalcula Inventario');
		$list[]=anchor('supervisor/repodupli/','Reportes Duplicado');
		$list[]=anchor('supervisor/mantenimiento/contadores','Cambios en contadores').'Advertencia: uselo solo si sabe lo que esta haciendo';
		$list[]=anchor('supervisor/mantenimiento/tablas','Mantenimiento de Tablas');
		$list[]=anchor('supervisor/mantenimiento/sntealma','Modifica el almac&eacute;n en las notas de entrega');
		$list[]=anchor('supervisor/mantenimiento/actualizaproteo','Actualiza proteo a la &uacute;ltima vesi&oacute;n del svn');

		$attributes = array(
			'class' => 'boldlist',
			'id'    => 'mylist'
		);

		$out=ul($list, $attributes);
		$data['content'] = $out;
		$data['head']    = script("jquery.pack.js").script("jquery.treeview.pack.js").$this->rapyd->get_head().style('jquery.treeview.css');
		$data['title']   = '<h1>Mantenimiento</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function reparatabla(){
		$this->load->dbutil();
		$tables = $this->db->list_tables();
		foreach ($tables as $table){
			$this->dbutil->repair_table($table);
		}
		redirect('supervisor/mantenimiento');
	}

	function bprefac(){
		$mSQL="DELETE FROM sitems WHERE MID(numa,1,1)='_' AND fecha<CURDATE()";
		$this->db->simple_query($mSQL);
		$mSQL="DELETE FROM sfac WHERE MID(numero,1,1)='_' AND fecha<CURDATE()";
		$this->db->simple_query($mSQL);
		redirect('supervisor/mantenimiento');
	}

	function bmodbus(){
		$mSQL="TRUNCATE modbus";
		$this->db->simple_query($mSQL);
		redirect('supervisor/mantenimiento');
	}

	function centinelas(){
		$this->load->helper('directory');
		$this->load->library('table');
		$tmpl = array('row_start' => '<tr valign="top">');
		$this->table->set_template($tmpl);

		$map = directory_map('./system/logs/');
		$lista=array();
		foreach($map AS $file) {
			if($file!='index.html')
			$lista[]=anchor("supervisor/mantenimiento/borracentinela/$file",'X')." <a href='javascript:void(0)' onclick=\"carga('$file')\" >$file</a>";
		}
		$copy="<br><a href='javascript:void(0)' class='mininegro'  onclick=\"copiar()\" >Copiar texto</a>";
		$tadata = array(
			'name'  => 'sql',
			'id'    => 'log',
			'rows'  => '20',
			'cols'  => '60'
		);

		$form = form_open('ejecutasql/filteredgrid/process').form_textarea($tadata).br();
		$form.= ($this->datasis->essuper()) ? form_submit('mysubmit', 'Ejecutar como SQL') : '';
		$form.= form_close();
		$this->table->add_row(ul($lista), '<b id="fnom">Seleccione un archivo de centinela</b><br>'.$form);
		$link=site_url('supervisor/mantenimiento/vercentinela');
		$data['script']  ="<script>
		  function carga(arch){
		    link='$link'+'/'+arch;
		    //alert(link);
		    $('#fnom').text(arch);
		    $('#log').load(link);
		  };
		  function copiar(){
		    $('#log').copy();
		  };
		</script>";

		$data['content'] = $this->table->generate();
		$data['title']   = heading('Centinelas');
		//script('plugins/jquery.clipboard.pack.js')
		$data['head']    =  script("jquery.pack.js").script('plugins/jquery.copy.min.js').$this->rapyd->get_head().style('marcos.css').style('estilos.css');
		$this->load->view('view_ventanas', $data);
	}

	function vercentinela($file=NULL){
		if(empty($file)) return FALSE;
		$this->load->helper('file');
		$string = read_file("./system/logs/$file");
		$string = $string;
		echo $string;
	}

	function borracentinela($file=NULL){
		if(!empty($file)){
			$this->load->helper('file');
			unlink("./system/logs/$file");
		}
		redirect('supervisor/mantenimiento/centinelas');
	}
	function almainconsis(){

		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Clientes inconsistentes");

		$filter->fechad = new dateonlyField('Desde','fechad');
		$filter->fechah = new dateonlyField('Hasta','fechah');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d");
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";

		$filter->buttons("reset","search");
		$filter->build();

		if($this->rapyd->uri->is_set("search") AND $filter->is_valid()){
			$fechah=$filter->fechah->newValue;
			$fechad=$filter->fechad->newValue;
				
			$alma=$this->datasis->dameval("SELECT a.ubica FROM (`costos` as a) LEFT JOIN `caub` AS b ON `a`.`ubica`=`b`.`ubica` WHERE `b`.`ubica` = 'NULL' AND `origen` = '3I' AND a.fecha >= '$fechad' AND a.fecha <= '$fechah'");
			//echo $alma;
			 
			$uri = anchor('supervisor/mantenimiento/cambioalma/modify/<#tipo_doc#>/<#numero#>','Cambio');
				
			$grid = new DataGrid('Almacenes inconsistentes');
			$select=array('a.fecha','a.numero','a.cod_cli','a.tipo_doc','a.totalg','a.almacen');
			$grid->db->select($select);
			$grid->db->from('sfac as a');
			$grid->db->where("a.almacen",$alma);
			$grid->db->where("a.fecha >= ",$fechad);
			$grid->db->where("a.fecha <=",$fechah);
			$grid->per_page = 15;
			 
			$grid->column('Fecha'      ,'<dbdate_to_human><#fecha#></dbdate_to_human>' ,'fecha');
			$grid->column('Numero'     ,'numero'  );
			$grid->column('Cliente'    ,'cod_cli' );
			$grid->column('Tipo'       ,'tipo_doc');
			$grid->column('Monto'      ,'totalg'  );
			$grid->column('Almacen'    ,'almacen' );
			$grid->column('Realizar'    ,$uri );
				
			$grid->build();
			//echo $grid->db->last_query();
			//memowrite($grid->db->last_query());

			$tabla=$grid->output;
		}else{
			$tabla='';
		}
		$data['content']  = $filter->output.$tabla;
		$data['title']    = "<h1>Almacenes con problemas de incosistencias</h1>";
		$data["head"]     = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function cambioalma(){
		$this->rapyd->load("dataedit");
		$edit = new DataEdit("Realizar cambio de almacen","sfac");
		$edit->back_url = site_url("supervisor/mantenimiento/almainconsis");

		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->size = 10;
		$edit->fecha->mode="autohide";

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->mode="autohide";

		$edit->tipo = new dropdownField("Tipo", "tipo_doc");
		$edit->tipo->option("D","D");
		$edit->tipo->option("F","F");
		$edit->tipo->option("X","X");
		$edit->tipo->mode="autohide";

		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 55;
		$edit->nombre->maxlength=40;
		$edit->nombre->mode="autohide";

		$edit->almacen = new  dropdownField ("Almacen", "almacen");
		$edit->almacen->option("","Todos");
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' and invfis='N' ORDER BY ubides");

		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();

		//$smenu['link']=barra_menu('113');
		//$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['content'] =$edit->output;
		$data['title']   = "Almacen Inconsistente";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function clinconsis(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli'),
		'titulo'  =>'Buscar Cliente');

		$boton=$this->datasis->modbus($scli);

		$filter = new DataFilter('Clientes inconsistentes');
		$select=array(
			'a.fecha',
			'a.tipo_doc',
			'a.cod_cli',
			'a.numero',
			'a.nombre',
			'a.monto',
			'sum(b.abono)+(SELECT COALESCE(SUM(d.monto),0) FROM `itcruc` AS d WHERE CONCAT(`a`.`tipo_doc`,`a`.`numero`)=`d`.`onumero`) AS abonoreal',
			'a.abonos AS inconsist',);

		$filter->db->select($select);
		$filter->db->from('smov AS a');
		$filter->db->join('itccli AS b','a.cod_cli=b.cod_cli AND a.numero=b.numero AND a.tipo_doc=b.tipo_doc');
		//$filter->db->join('itcruc AS c','c.onumero=')
		$filter->db->groupby('a.cod_cli, a.tipo_doc,a.numero');
		$filter->db->having('abonoreal  <>','inconsist');
		//$filter->db->having('diferencia >=','0.05');
		$filter->db->orderby('a.cod_cli','b.numero');

		$filter->fechad = new dateonlyField('Desde','fechad');
		$filter->fechah = new dateonlyField('Hasta','fechah');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="a.fecha";
		//$filter->fechad->insertValue = date("Y-m-d");
		//$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";

		$filter->cliente = new inputField("Cliente", "cod_cli");
		$filter->cliente->db_name="a.cod_cli";
		$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons("reset","search");
		$filter->build();

		function descheck($numero,$cod_cli,$tipo_doc,$fecha,$abonoreal){
			$pk=array($numero,$cod_cli,$tipo_doc,$fecha,$abonoreal);
			$str=htmlspecialchars(serialize($pk));
			$data = array(
			  'name'    => 'pk',
			  'value'   => $str,
			  'checked' => FALSE);
			return form_checkbox($data);
		}

		function diff($a,$b){
			return nformat($a-$b);
		}

		$uri1 = anchor('supervisor/mantenimiento/itclinconsis/<str_replace>/|:slach:|<#cod_cli#></str_replace>/<#numero#>/<#tipo_doc#>','<#cod_cli#>');
		$uri2 = anchor('supervisor/mantenimiento/ajustar/<#cod_cli#>','Ajustar Saldo');

		$grid = new DataGrid("Lista de Clientes");
		$grid->use_function('descheck','diff');
		$grid->per_page = 15;
		$grid->use_function('str_replace');

		$grid->column_orderby('Cliente'        ,$uri1    ,'cod_cli');
		$grid->column_orderby('Nombre'         ,'nombre' ,'nombre');
		$grid->column_orderby('Fecha'          ,'<dbdate_to_human><#fecha#></dbdate_to_human>' ,'fecha');
		$grid->column_orderby('N&uacute;mero'  ,'<#tipo_doc#><#numero#>'    ,'numero');
		$grid->column_orderby('Monto'          ,'<nformat><#monto#></nformat>'        ,'monto'     ,"align='right'");
		$grid->column_orderby('Abono Real'     ,'<nformat><#abonoreal#></nformat>'    ,'abonoreal' ,"align='right'");
		$grid->column_orderby('Abono Inconsis.','<nformat><#inconsist#></nformat>'    ,'inconsist' ,"align='right'");
		$grid->column('Faltante'               ,'<diff><#abonoreal#>|<#inconsist#></diff>',"align='right'");
		$grid->column('Ajustar Saldo'          ,'<descheck><#numero#>|<#cod_cli#>|<#tipo_doc#>|<#fecha#>|<#abonoreal#></descheck>',"align=center");

		$grid->build();
		echo $grid->db->last_query();
		//memowrite($grid->db->last_query());

		$script='';
		$url=site_url('supervisor/mantenimiento/ajustesaldo');
		$data['script']='<script type="text/javascript">
			$(document).ready(function() {
				$("form :checkbox").click(function () {
				$.ajax({
					  type: "POST",
					  url: "'.$url.'",
					  data: $(this).serialize(),
					  success: function(msg){
					    if(msg==0)
					      alert("No se puedo ajustar el saldo");
					  }
					});
				}).change(); 
			});
			</script>';

		$data['content']  = $filter->output;
		$data['content'] .= form_open('').$grid->output.form_close().$script;
		$data['title']    = "<h1>Clientes con problemas de incosistencias</h1>";
		$data["head"]     = script("jquery.js");
		$data["head"]    .= script("plugins/jquery.checkboxes.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function clinconsismasivo(){
		$mSQL="SELECT
			`a`.`fecha`, 
			`a`.`tipo_doc`, 
			`a`.`cod_cli`, 
			`a`.`numero`, 
			sum(b.abono)+(SELECT COALESCE(SUM(d.monto),0) FROM `itcruc` AS d WHERE CONCAT(`a`.`tipo_doc`,`a`.`numero`)=`d`.`onumero`) AS abonoreal, 
			`a`.`abonos` AS inconsist 
			FROM (`smov` AS a) 
			JOIN `itccli` AS b ON `a`.`cod_cli`=`b`.`cod_cli` AND a.numero=b.numero AND a.tipo_doc=b.tipo_doc 
			WHERE b.tipo_doc='FC'
			GROUP BY `a`.`cod_cli`, `a`.`tipo_doc`, `a`.`numero` HAVING `abonoreal` <> inconsist LIMIT 300";

		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				if(!$this->_sclisaldo($row->numero,$row->cod_cli,$row->tipo_doc,$row->fecha,$row->abonoreal)){
					echo "No se pudo cambiar ".$row->numero.' '.$row->cod_cli;
				}
			}
		}
	}

	function ajustesaldo(){
		$pk  = unserialize(htmlspecialchars_decode($this->input->post('pk')));
		//print_r($pk);
		if(count($pk)!=5){
			echo 0;
		}else{
			if($this->_sclisaldo($pk[0],$pk[1],$pk[2],$pk[3],$pk[4]))
			echo 1;
			else
			echo 0;
		}

		/*$data = array('abonos' => $pk[4]);

		$where  =' numero='.$this->db->escape($pk[0]);
		$where .=' AND cod_cli ='.$this->db->escape($pk[1]);
		$where .=' AND tipo_doc='.$this->db->escape($pk[2]);
		$where .=' AND fecha   ='.$this->db->escape($pk[3]);

		$mSQL = $this->db->update_string('smov', $data, $where);

		if($this->db->simple_query($mSQL)){
		echo 1;
		return true;
		}else{
		memowrite($mSQL,'ajusal');
		echo 0;
		return false;
		}*/
	}

	function _sclisaldo($numero,$cod_cli,$tipo_doc,$fecha,$abono){
		$data = array('abonos' => $abono);

		$where  =' numero='.$this->db->escape($numero);
		$where .=' AND cod_cli ='.$this->db->escape($cod_cli);
		$where .=' AND tipo_doc='.$this->db->escape($tipo_doc);
		$where .=' AND fecha   ='.$this->db->escape($fecha);

		$mSQL = $this->db->update_string('smov', $data, $where);

		return $this->db->simple_query($mSQL);
	}


	function itclinconsis($cliente='',$numero='',$tipo_doc){
		$this->rapyd->load("datagrid2");
		$this->rapyd->uri->keep_persistence();

		$uri = anchor('supervisor/mantenimiento/clinconsis','Regresar');

		$select=array('numccli','tipoccli','fecha','abono','tipo_doc','cod_cli');
		$grid = new DataGrid2($uri);
		$grid->per_page = 15;
		$grid->db->select($select);
		$grid->db->from('itccli');
		$grid->db->where('cod_cli',$cliente);
		$grid->db->where('tipo_doc',$tipo_doc);
		$grid->db->where('numero',$numero);

		$grid->column('Numero' ,'numccli' );
		$grid->column('Tipo'   ,'tipoccli' );
		$grid->column('Fecha'  ,'<dbdate_to_human><#fecha#></dbdate_to_human>');
		$grid->column('Monto'  ,'<nformat><#abono#></nformat>',"align='right'");

		$grid->totalizar('abono');
		$grid->build();

		//echo $grid->db->last_query();
		//memowrite($grid->db->last_query());
		$data['content'] = $grid->output;
		$data['title']   = "<h1>Detalle de los Abonos del cliente:$cliente</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function contadores(){
		if(!$this->datasis->essuper()) show_404();

		$this->rapyd->load("dataform");
		$edit = new DataForm('supervisor/mantenimiento/contadores/process');

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->rule='required|numeric|max_length[8]';
		$edit->numero->size = 10;
		$edit->numero->maxlength=8;
		$edit->numero->append('El n&uacute;mero que coloque va a ser el pr&oacute;ximo n&uacute;mero que proporcione el contador');

		$edit->container = new containerField("alert","<div class='alert'>Haga uso de este modulo solo si sabe lo que esta haciendo, una cambio mal puede dejar inoperativo el sistema</div>");

		$_POST['confirma']='';
		$edit->confirma = new inputField('Escriba ACEPTO para confirmaci&oacute;n', 'confirma');
		$edit->confirma->rule='callback_confirma';
		$edit->confirma->size = 6;
		$edit->confirma->append('Sencible a las may&uacute;sculas');

		$edit->submit('btnm_submit','Aceptar');

		$edit->build_form();

		$sal='';
		if ($edit->on_success()){
			$num = $edit->numero->newValue;
			$sal=$this->_contadores($num);
		}

		$data['content'] = $edit->output.'<pre>'.$sal.'</pre>';
		$data['title']   = '<h1>Cambio en los contadores</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _contadores($num=null){
		$rt='';
		if(!empty($num) AND is_numeric($num)){
			$tables = $this->db->list_tables();

			foreach ($tables as $table){
				$fields = $this->db->list_fields($table);
				if(count($fields)==3){
					if($fields[0]=='numero' AND $fields[1]=='usuario' AND $fields[2]=='fecha'){
						$mSQL="DELETE FROM `$table` WHERE numero>=$num";
						if($this->db->simple_query($mSQL)){
							$mSQL="ALTER TABLE `$table` AUTO_INCREMENT=$num";
							if (!$this->db->simple_query($mSQL)){
								$rt.= "Error cambiando el contador en $table \n";
							}else{
								$rt.= "$table cambiado \n";
							}
						}
					}
				}
			}
		}
		return $rt;
	}

	function confirma($par){
		if($par=='ACEPTO'){
			return true;
		}
		$this->validation->set_message('confirma', 'Debe escribir ACEPTO en la confirmaci&oacute;n');
		return false;
	}

	function tablas(){
		$this->rapyd->load("dataform","datatable");
		$tables = $this->db->list_tables();
		//print("<pre>");
		//print_R($tables);
				
		$form = new DataForm("supervisor/mantenimiento/tablas/process"); 
		$form->free = new freeField("Lista de Tablas","free","Chequear|Reparar|Optimizar");  
		foreach($tables as $tabla){
			$che="chequea_".$tabla;
			$re="repara_".$tabla;
			$op="optimi_".$tabla;
			$ob1="con_".$tabla;
			$ob2="con2_".$tabla;

			$form->$che = new checkboxField("$tabla", "$che","CHECK TABLE $tabla","no");
			$form->$ob1 = new containerField("","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			$form->$ob1->in="$che";
			$form->$re = new checkboxField("", "$re","REPAIR TABLE $tabla","no");
			$form->$re->in="$che";
			$form->$ob2 = new containerField("","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			$form->$ob2->in="$che";
			$form->$op = new checkboxField("", "$op","OPTIMIZE TABLE $tabla","no");
			$form->$op->in="$che";
		}

		$form->submit("btnsubmit","Aceptar");
		$form->build_form();

		if  ($form->on_show() || $form->on_error()) {
			$data["content"] =$form->output;
		}

		if ($form->on_success()){
			$data["content"] = "<h1>Procesos y Consultas generadas</h1><br>";
			$atras=anchor('supervisor/mantenimiento/tablas','Atras');
			//print_R($_POST);
			foreach($_POST as $nom=>$val){
				
					
				if($this->db->simple_query($val)){
					$data["content"].= "Se Proceso:".$nom." Con la consulta:(".$val.")<br>";
				}else{
					if($val=="Aceptar") break;
					$data["content"].= "Error en consulta:".$val."<br>";
				}
			}
			$data["content"].=$atras;
		}
		$data['title']   = "Mantenimiento de tablas";
		$data["rapyd_head"] = $this->rapyd->get_head();
		 
		$this->load->view("view_ventanas", $data);
	}


	function sntealma(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Cambio de almac&eacute;n en notas de entrega",'snte');

		$filter->numero = new inputField('N&uacute;mero','numero');
		$filter->numero->size=10;

		$filter->fechad = new dateonlyField('Desde','fechad');
		$filter->fechah = new dateonlyField('Hasta','fechah');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d");
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";

		$filter->buttons("reset","search");
		$filter->build();

		if($this->rapyd->uri->is_set("search") AND $filter->is_valid()){

			$uri = anchor('supervisor/mantenimiento/sntecambioalma/modify/<#numero#>','<#almacen#>');

			$grid = new DataGrid('Notas de entrega');
			$grid->per_page = 15;

			$grid->column('Fecha'      ,'<dbdate_to_human><#fecha#></dbdate_to_human>');
			$grid->column('Almac&eacute;n'    ,$uri );
			$grid->column('Numero'     ,'numero'  );
			$grid->column('Cliente'    ,'cod_cli' );
			$grid->column('Nombre'    ,'nombre' );
			$grid->column('Monto'      ,'<nformat><#gtotal#></nformat>'  ,'align="right"');

			$grid->build();

			$tabla=$grid->output;
		}else{
			$tabla='';
		}
		$data['content']  = $filter->output.$tabla;
		$data['title']    = "<h1>Cambio de almac&eacute;n en notas de entrega</h1>";
		$data['head']     = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function sntecambioalma(){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Realizar cambio de almac&eacute;n','snte');
		$edit->back_url = site_url('supervisor/mantenimiento/sntealma');

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 55;
		$edit->nombre->maxlength=40;
		$edit->nombre->mode='autohide';

		$edit->almacen = new  dropdownField ('Almac&eacute;n', 'almacen');
		$edit->almacen->option('','Todos');
		$edit->almacen->options("SELECT ubica,CONCAT_WS('-',ubica,ubides) AS val FROM caub WHERE gasto='N' and invfis='N' ORDER BY ubides");

		$edit->buttons('modify', 'save', 'undo', 'back');
		$edit->build();

		//$smenu['link']=barra_menu('113');
		//$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['content'] =$edit->output;
		$data['title']   = '<h1>Cambio de almac&eacute;n en nota de entrega</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function secuencias(){
		$tables = $this->db->list_tables();
		foreach ($tables as $table){
			$fields = $this->db->list_fields($table);
			if(count($fields)==3){
				if($fields[0]=='numero' AND $fields[1]=='usuario' AND $fields[2]=='fecha'){
					$mSQL="SHOW TABLE STATUS LIKE '$table'";
					$query = $this->db->query($mSQL);
					if($query->num_rows() > 0){
						$row = $query->row();
						echo $table.' <b>'.$row->Auto_increment.'</b>'.br();
					}
				}
			}
		}
	}

	function actualizaproteo(){
		if (!extension_loaded('svn')) {
			$data['content'] = 'La extension svn no esta cargada, debe cargarla para poder usar estas opciones';
		}else{
			$dir=getcwd();
			$svn=$dir.'/.svn';

			if(!is_writable($svn)){
				$data['content']= 'No se tiene permiso al directorio .svn, comuniquese con soporte t&eacute;cnico';
			}else{
				$aver=0; //<-- falta consultar la version actual
				$ver =@svn_update($dir);

				if($ver>0){
					if($ver>$aver){
						$data['content'] = 'Actualizado a la versi&oacute;n: '.$ver;
					}else{
						$data['content'] = 'Ya estaba la ultima versi&oacute;n instalada '.$arr['revision'];
					}
				}else{
					$data['content'] = 'Hubo problemas con la actualizaci&oacute;n, comuniquese con soporte t&eacute;cnico';
				}
			}
		}

		$data['title']   = '<h1>Actualizacion de ProteoERP desde el svn</h1>';
		$data['head']    = '';
		$this->load->view('view_ventanas', $data);
	}

	function respaldo(){
		if(!$this->datasis->essuper()) show_404();
		$this->load->library('zip');
		$host= $this->db->hostname;
		$db  = $this->db->database;
		$pwd = $this->db->password;
		$usr = $this->db->username;
		$file= tempnam('/tmp',$db.'.sql');

		$cmd="mysqldump -u $usr -p $pwd -h $host --opt --routines $db > $file";
		$sal=exec($cmd);

		$this->zip->read_file($file);
		$this->zip->download($db.'.zip'); 
		unlink($file);
	}

	function puertosdir(){
		$this->load->helper('download');
		if (extension_loaded('dbase')) {
			$def = array(
			    array('FORMA'  , 'C',  10),
			    array('PUERTO' , 'C',  60),
			    array('DESCRIP', 'C',  200),
			);
			$temp =tempnam("/tmp", 'puertos');
			$db=dbase_create($temp, $def);
			if ($db){
				$query = $this->db->query('SELECT nombre FROM formatos UNION SELECT nombre FROM reportes');
				if ($query->num_rows() > 0){
					foreach ($query->result() as $row){
						$pivot=array($row->nombre,'C:\\spool\\'.$row->nombre.'.TXT','');
						dbase_add_record($db, $pivot);
					}
				}
				dbase_close($db);
			}
			$data = file_get_contents($temp);
			force_download('PUERTOS.DIR', $data);
			unlink($temp);
		}else{
			echo 'Debe cargar las librerias dbase para poder usar este modulo';
		}
	}

	//Para reconstruir sfac a partir de sfpa, sitems,scli y sinv
	function ressfac(){
		$mSQL="INSERT INTO sfac 
		SELECTaa.tipoa,aa.numa,aa.fecha,aa.fecha,aa.vendedor,aa.codigoa,aa.rifci,aa.nombre,aa.dire11,aa.dire12,'' AS orden,'' AS referen,SUM(aa.iva),0 AS inicial,SUM(aa.tota) AS totals,SUM(aa.tota+aa.iva) AS totalg,'' AS status, '' AS observa,'' AS observ1,0 AS devolu,aa.cajero,'0001' AS almacen,0 AS peso,'' AS factura,'' AS pedido,aa.usuario,aa.estampa,aa.hora,aa.transac,'' AS nfiscal,'' AS zona,'' AS ciudad,0 AS comision,'N' AS pagada,'N' AS sepago,
		0  AS dias,
		'' AS fpago,
		0  AS comical,
		SUM(aa.tota*(aa.sinviva=0))  AS exento,
		SUM(aa.iva*(aa.sinviva=12))  AS tasa,
		SUM(aa.iva*(aa.sinviva=8))   AS reducida,
		SUM(aa.iva*(aa.sinviva=22))  AS sobretasa,
		SUM(aa.tota*(aa.sinviva=12)) AS montasa,
		SUM(aa.tota*(aa.sinviva=8))  AS monredu,
		SUM(aa.tota*(aa.sinviva=22)) AS monadic,
		'' AS notcred,'' AS fentrega,'' AS  fpagom,'' AS fdespacha,'' AS udespacha,'' AS numarma,'' AS maqfiscal,null AS id,'' AS dmaqfiscal,'' AS nromanual,'' AS fmanual,'' AS lleva
		FROM
		(SELECT b.*,c.iva AS sinviva,c.peso,e.cliente,e.nombre,e.dire11,e.dire12,e.rifci
		FROM sfac AS a 
		RIGHT JOIN sitems AS b ON a.tipo_doc=b.tipoa AND a.numero=b.numa
		JOIN sinv AS c ON b.codigoa=c.codigo
		JOIN sfpa AS d ON b.tipoa=MID(d.tipo_doc,1,1) AND b.numa=d.numero
		JOIN scli AS e ON d.cod_cli=e.cliente WHERE a.numero IS NULL) AS aa
		GROUP BY aa.tipoa,aa.numa";
		echo $mSQL;
	}
	

	function calcosto(){
		$this->db->simple_query("CALL sp_calcopasa()");
		$this->db->simple_query("CALL sp_calcoinv()");
	}

	
}
