<?php
class Mantenimiento extends Controller{
	
	function Mantenimiento(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		$list = array();
		$list[]=anchor('supervisor/mantenimiento/bprefac','Borrar PreFacturas menores o iguales al d&iacute;a de ayer');
		$list[]=anchor('supervisor/mantenimiento/bmodbus','Vaciar la tabla ModBus');
		$list[]=anchor('supervisor/mantenimiento/centinelas','Centinelas');
		$list[]=anchor('supervisor/mantenimiento/reparatabla','Reparar Tablas');
		$list[]=anchor('supervisor/mantenimiento/clinconsis','Incosistencias Clientes');
		$list[]=anchor('supervisor/repodupli/','Reportes Duplicado');
		
		$attributes = array(
			'class' => 'boldlist',
			'id'    => 'mylist'
			);

		$out=ul($list, $attributes);
		$data['content'] = $out;
		$data["head"]    = script("jquery.pack.js").script("jquery.treeview.pack.js").$this->rapyd->get_head().style('jquery.treeview.css');
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
		          'name'    => 'sql',
		          'id'      => 'log',
		          'rows'    => '20',
		          'cols'    => '60'
		        );
		
		$form= form_open('ejecutasql/filteredgrid/process').form_textarea($tadata).'<br>'.form_submit('mysubmit', 'Ejecutar como SQL').form_close();
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
		$data['title']   = "<h1>Centinelas</h1>";
		//script('plugins/jquery.clipboard.pack.js')
		$data["head"]    =  script("jquery.pack.js").script('plugins/jquery.copy.min.js').$this->rapyd->get_head().style('marcos.css').style('estilos.css');
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
			echo $alma;
    	
			$uri = anchor('supervisor/mantenimiento/cambioalma/modify/<#tipo_doc#>/<#numero#>','Cambio');
			
			$grid = new DataGrid('Almacenes inconsistentes');	
			$select=array('a.fecha','a.numero','a.cod_cli','a.tipo_doc','a.totalg','a.almacen');
			$grid->db->select($select);
			$grid->db->from('sfac as a');
			$grid->db->where("a.almacen",'A001');
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
		$select=array('a.fecha','a.abonos','a.tipo_doc','a.cod_cli','a.numero','a.nombre','a.monto as saldo',
		'sum(b.abono) AS abono','sum(b.abono)-a.monto as diferencia');

		$filter->db->select($select);
		$filter->db->from('smov as a');
		$filter->db->join('itccli as b','a.cod_cli=b.cod_cli AND a.numero=b.numero AND a.tipo_doc=b.tipo_doc');
		$filter->db->groupby('a.cod_cli, a.tipo_doc,a.numero');
		$filter->db->having('a.monto','a.abonos');
		$filter->db->having('abono < ','a.abonos');
		//$filter->db->having("diferencia >= ",'0.05');
		$filter->db->orderby('cod_cli');

		$filter->fechad = new dateonlyField('Desde','fechad');
		$filter->fechah = new dateonlyField('Hasta','fechah');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="a.fecha";
		$filter->fechad->insertValue = date("Y-m-d");
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";

		$filter->cliente = new inputField("Cliente", "cod_cli");
		$filter->cliente->db_name="a.cod_cli";
		$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons("reset","search");
		$filter->build();

		function descheck($numero,$cod_cli,$tipo_doc){
			$data = array(
			  'name'    => $numero,
			  'id'      => $cod_cli,
			  'value'   => $tipo_doc,
			  'checked' => FALSE);
			return form_checkbox($data);
		}

		$uri1 = anchor('supervisor/mantenimiento/itclinconsis/<str_replace>/|:slach:|<#cod_cli#></str_replace>/<#numero#>/<#tipo_doc#>','<#cod_cli#>');
		$uri2 = anchor('supervisor/mantenimiento/ajustar/<#cod_cli#>','Ajustar Saldo');

		$grid = new DataGrid("Lista de Clientes");
		$grid->use_function('descheck');
		$grid->per_page = 15;
		$grid->use_function('str_replace');

		$grid->column('Cliente'      ,$uri1,'cod_cli');
		$grid->column('Nombre'       ,'nombre','nombre');
		$grid->column('Fecha'        ,'<dbdate_to_human><#fecha#></dbdate_to_human>' ,'fecha');
		$grid->column('Numero'       ,'numero'     ,'numero');
		$grid->column('Saldo'        ,'saldo'      ,'saldo',"align='right'");
		$grid->column('Abonado'      ,'abono'      ,'abono',"align='right'");
		$grid->column('Diferencia'   ,'diferencia' ,'diferencia',"align='right'");
		$grid->column('Ajustar Saldo','<descheck><#numero#>|<#cod_cli#>|<#tipo_doc#></descheck>',"align=center"); 

		$grid->build();
		echo $grid->db->last_query();
		//memowrite($grid->db->last_query());

		$script='';
		$url=site_url('supervisor/mantenimiento/procesar');
		$data['script']='<script type="text/javascript">
			$(document).ready(function() {
				$("form :checkbox").click(function () {
				$.ajax({
					  type: "POST",
					  url: "'.$url.'",
					  data: "numero="+this.name+"&codigo="+this.id+"&tipo="+this.value,
					  success: function(msg){
					  alert("Saldo Ajustado");
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

	function procesar(){
		$numero  = $this->db->escape($this->input->post('numero'));
		$codigo  = $this->db->escape($this->input->post('codigo'));
		$tipo    = $this->db->escape($this->input->post('tipo'));

		$monto=$this->datasis->dameval("SELECT sum(abono) FROM itccli WHERE numero=$numero AND cod_cli=$codigo AND tipo_doc=$tipo");
		$mSQL="UPDATE smov set abonos='$monto' WHERE numero=$numero AND cod_cli=$codigo AND tipo_doc=$tipo";
		$SQL=$this->db->simple_query($mSQL);
	}

	function itclinconsis($cliente='',$numero='',$tipo_doc){
		$this->rapyd->load("datagrid2");
		
		$uri = anchor('supervisor/mantenimiento/clinconsis','Regresar');
		
		$select=array('numccli','tipoccli','fecha','abono','tipo_doc','cod_cli');	
		$grid = new DataGrid2($uri);
		$grid->per_page = 15;
		$grid->db->select($select);
		$grid->db->from('itccli');
		$grid->db->where('cod_cli',$cliente);
		$grid->db->where('tipo_doc',$tipo_doc);
		$grid->db->where('numero',$numero);
			
		$grid->column('Numero'	,'numccli' );
		$grid->column('Tipo'		,'tipoccli' );
		$grid->column('Fecha' 	,'<dbdate_to_human><#fecha#></dbdate_to_human>');
		$grid->column('Abono'   ,'abono');

		$grid->totalizar('abono');
		$grid->build();
		
		//echo $grid->db->last_query();
		//memowrite($grid->db->last_query());
		$data['content'] = $grid->output;
		$data['title']   = "<h1>Detalle de los Abonos del cliente:$cliente</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}
?>
