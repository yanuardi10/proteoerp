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
		
		 
		$filter = new DataFilter("Filtro");
		$select=array("a.fecha","a.abonos","a.tipo_doc","a.cod_cli","a.numero","a.nombre","a.monto as saldo",
		"sum(b.abono) as abono","sum(b.abono)-a.monto as diferencia");
		
		$filter->db->select($select);
		$filter->db->from('smov as a');
		$filter->db->join('itccli as b','a.cod_cli=b.cod_cli and a.numero=b.numero and a.tipo_doc=b.tipo_doc');
		$filter->db->groupby("a.cod_cli, a.tipo_doc,a.numero");
		$filter->db->having("a.monto","a.abonos");
		$filter->db->having("abono < ","a.monto");
		//$filter->db->having("diferencia >= ",'0.05');
		$filter->db->orderby("cod_cli");
		
		$filter->fechad = new dateonlyField("Desde", "fechad");
		$filter->fechah = new dateonlyField("Hasta", "fechah");
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="a.fecha";
		$filter->fechad->insertValue = date("Y-m-d");
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->cliente = new inputField("Cliente", "a.cod_cli");
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
								
		$uri1 = anchor('supervisor/mantenimiento/itclinconsis/<str_replace>/|:slach:|<#cod_cli#></str_replace>','<#cod_cli#>');
		$uri2 = anchor('supervisor/mantenimiento/ajustar/<#cod_cli#>','Ajustar Saldo');
		
		$grid = new DataGrid("Lista de Clientes");
		$grid->use_function('descheck');
		$grid->per_page = 15;
		$grid->use_function('str_replace');
			
		$grid->column('Cliente'        ,$uri1,'cod_cli');
		$grid->column('Nombre'         ,'nombre','nombre');
		$grid->column('Fecha'          ,'<dbdate_to_human><#fecha#></dbdate_to_human>' ,'fecha');
		$grid->column('Numero'         ,'numero'     ,'numero');
		$grid->column('Saldo'          ,'saldo'      ,'saldo',"align='right'");
		$grid->column('Abonado'       ,'abono'      ,'abono',"align='right'");
		$grid->column('Diferencia'     ,'diferencia' ,'diferencia',"align='right'");
		$grid->column("Ajustar Saldo","<descheck><#numero#>|<#cod_cli#>|<#tipo_doc#></descheck>","align=center"); 
		
		$grid->build();
		//echo $grid->db->last_query();
		//memowrite($grid->db->last_query());
				
		$script='';
		$url=site_url('supervisor/mantenimiento/procesar');
		//$url1=site_url('ventas/sfacdespfyco/activar1');
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
		             
		$data['content'] =  $filter->output;			                                                              
		$data['content'] .=form_open('').$grid->output.form_close().$script;
		$data['title']   =  "<h1>Clientes con problemas de incosistencias</h1>";
		$data["head"]    =  script("jquery-1.2.6.pack.js");
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
	function itclinconsis($proveed){
		$this->rapyd->load("datagrid");
		$select=array('cod_cli', 'nombre','numero',
		"monto*(tipo_doc IN ('FC','ND','GI')) AS debitos",
		"monto*(tipo_doc NOT IN ('FC','ND','GI')) AS creditos",
		"monto*IF(tipo_doc IN ('FC','ND','GI'),1,-1) AS saldo",
		"(monto-abonos)*(tipo_doc IN ('FC','ND','GI'))-(monto-abonos)*(tipo_doc='AN') AS abonado",
		"monto*IF(tipo_doc IN ('FC','ND','GI'),1,-1)-((monto-abonos)*(tipo_doc IN ('FC','ND','GI'))-(monto-abonos)*(tipo_doc='AN')) AS diferen");
		//(FC,ND,GI,AN)
		$uri1 = anchor('supervisor/repomenu/reporte/modify/<#alternativo#>/','Modificar');
		
		$grid = new DataGrid("Clientes inconsistentes");
		$grid->per_page = 15;
		$grid->db->select($select);
		$grid->db->from('smov');
		$grid->db->where('cod_cli',$proveed);
		$grid->db->where("tipo_doc IN ('FC','ND','GI','AN')");
		//$grid->db->having("abs(diferen)>0.01");
		//$grid->db->having('abs(100*diferen/saldo)>=0.05');
		
		$grid->column('Numero'   ,'numero' );
		$grid->column('Cliente'   ,'cod_cli' );
		$grid->column('Nombre'    ,'nombre'  );
		$grid->column('D&eacute;bitos'   ,'debitos' );
		$grid->column('Cr&eacute;ditos'  ,'creditos');
		$grid->column('Saldo'     ,'saldo'   );
		$grid->column('Abonados'  ,'abonado' );
		//$grid->column('Diferencia','diferen' );

		$grid->build();
		//echo $grid->db->last_query();
		//memowrite($grid->db->last_query());
		$data['content'] = $grid->output;
		$data['title']   = "<h1>Clientes con problemas de incosistencias</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}
?>
