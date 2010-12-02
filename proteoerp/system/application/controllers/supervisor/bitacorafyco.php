<?php
class bitacorafyco extends Controller {

	function bitacorafyco(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->load->library("menues");
		define ("THISFILE",   APPPATH."controllers/ventas/". $this->uri->segment(2).EXT);
		$this->datasis->modulo_id(907,1);
		$this->load->database();
	}

	function index(){ 
		redirect("supervisor/bitacorafyco/filteredgrid");
	}

	function filteredgrid(){

		$this->rapyd->load("datafilter2","datagrid");
		$this->rapyd->uri->keep_persistence();
 		$modbus=array(
			'tabla'   =>'usuario',
			'columnas'=>array(
				'us_codigo' =>'C&oacute;digo',
				'us_nombre'=>'Nombre'),
			'filtro'  =>array('us_nombre'=>'nombre'),
			'retornar'=>array('us_codigo'=>'asignacion'),
			'titulo'  =>'Buscar Usuario');

		$atts = array(
		              'width'      => '800',
		              'height'     => '600',
		              'scrollbars' => 'yes',
		              'status'     => 'yes',
		              'resizable'  => 'yes',
		              'screenx'    => '0',
		              'screeny'    => '0'
		            );
		
		$link=anchor_popup('supervisor/bitacorafyco/resumen', 'Ver Promedio de &eacute;xitos', $atts);

		$filter = new DataFilter2("Filtro de Bit&aacute;cora ($link)");
		$select=array("a.asignacion","a.horac","a.actualizado","a.actividad","a.fecha","a.hora","a.nombre","a.comentario","a.id","if(revisado='P','Pendiente',if(revisado='B','Bueno',if(revisado='C','Consulta',if(revisado='F','Fallo','sin revision')))) revisado");
		
		$filter->db->select($select);
		$filter->db->from('bitacora as a');
		//$filter->db->orderby('a.actualizado,horac desc');
		
    $filter->fechad = new dateonlyField("Fecha Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Fecha Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="a.fecha";
		//$filter->fechad->insertValue = date("Y-m-d"); 
		//$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		$filter->fechah->group="Fecha";
		$filter->fechad->group="Fecha";
		
		$filter->revisado = new dropdownField("Revisado", "revisado");
		$filter->revisado->option("","Todos");
		$filter->revisado->option("P","Pendiente");
		$filter->revisado->option("F","Fallos");
		$filter->revisado->option("B","Buenos"); 
		$filter->revisado->option("C","Consulta"); 
				
		$filter->usuario = new inputField("Asignado", "asignacion");
		$filter->usuario->size=11;
		$filter->usuario->append($this->datasis->modbus($modbus));
		$filter->usuario->db_name="asignacion";

		$filter->actividad = new inputField("Actividad", "a.actividad");
		$filter->actividad->clause ="likesensitive";
		$filter->actividad->append("Sencible a las Mayusc&uacute;las");
		
		$filter->actualizadod = new dateonlyField("Actualizado Desde", "fechad",'d/m/Y');
		$filter->actualizadoh = new dateonlyField("Actualizado Hasta", "fechah",'d/m/Y');
		$filter->actualizadod->clause  =$filter->actualizadoh->clause="where";
		$filter->actualizadod->db_name =$filter->actualizadoh->db_name="a.actualizado";
		//$filter->actualizadod->insertValue = date("Y-m-d"); 
		//$filter->actualizadoh->insertValue = date("Y-m-d"); 
		$filter->actualizadoh->size=$filter->actualizadod->size=10;
		$filter->actualizadod->operator=">="; 
		$filter->actualizadoh->operator="<=";
		$filter->actualizadoh->group="Actualizacion";
		$filter->actualizadod->group="Actualizacion";
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor("supervisor/bitacorafyco/dataedit/show/<#id#>","<#id#>");
		$link1=anchor_popup('supervisor/bitacorafyco/resultados/<#id#>/create', 'Ver', $atts);
		$link2=anchor_popup('supervisor/bitacorafyco/reciente/', 'Actividad Reciente', $atts);
		
		function colum($id='',$id2=''){
  			if ($id==$id2)
						return ('<b style="color:green;">'.$id.'</b>');
					else
						return ($id);
			}

		$grid = new DataGrid("Lista de Actividades");
		$grid->order_by("a.actualizado","desc");
		$grid->per_page = 10;
		$grid->use_function('colum');

		$grid->column("Nº",$uri);					  
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>");
		$grid->column("Hora","horac");
		$grid->column("Actualizado","<dbdate_to_human><#actualizado#></dbdate_to_human>");
		$grid->column("Nombre","nombre");
		$grid->column("Actividad realizada","actividad");
		//$grid->column("Resultado","evaluacion");
		$grid->column("Revisado","revisado");
		$grid->column("Asignado","asignacion");
		
		//$Sql="SELECT a.id,b.actividad FROM bitacora as a JOIN itbitacora as b on a.id=b.actividad GROUP BY actividad ORDER BY a.id DESC LIMIT 5";
		//echo $Sql;
		//$mSQL_1=  $this->db->query($Sql);
		//$data['result']=$mSQL_1->result();	
		//			
		//foreach ($data['result'] AS $items){
		//				$id2=$items->id;						
		//	}
		//$grid->column("Nº","<colum><#id#>|$id2</colum>","align='center'");
		$grid->column("Resultados",$link1,"align='center'");
				
		$grid->add("supervisor/bitacorafyco/dataedit/create");
		$grid->build();
						
		$data["crud"] = $filter->output.$grid->output;
		$data["titulo"] = 'Bit&aacute;cora de bitacorafyco';
		
		//echo $filter->db->last_query();
		
		$data['content'] =$filter->output.'<pre>'.$link2.'</pre>'.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Control de Bit&aacute;cora</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function dataedit(){ 
		$this->rapyd->load("dataedit");
		$usr=$this->session->userdata['usuario'];
		//echo 'Usuario:'.$usr;
		
		$modbus=array(
			'tabla'   =>'usuario',
			'columnas'=>array(
			'us_codigo' =>'C&oacute;digo',
			'us_nombre'=>'Nombre'),
			'filtro'  =>array('us_nombre'=>'nombre'),
			'retornar'=>array('us_codigo'=>'asignacion'),
			'titulo'  =>'Buscar Usuario');

		$edit = new DataEdit("Agregar Actividad", "bitacora");
		
		$edit->_dataobject->db->set('nombre' , $this->session->userdata('nombre') );
		$edit->_dataobject->db->set('usuario', $this->session->userdata('usuario'));
		$edit->_dataobject->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$edit->_dataobject->db->set('fecha'  , 'NOW()', FALSE);
			
		$edit->back_url = site_url("supervisor/bitacorafyco/filteredgrid");
		
		$edit->actividad = new textareaField("Actividad", "actividad");
		$edit->actividad->rule = "required";
		$edit->actividad->rows = 6;
		$edit->actividad->cols=90;
		$edit->actividad->when=array('show','create');
		
		if ($edit->_status=='show'){
			$edit->fecha = new dateonlyField("Fecha","fecha", "d/m/Y");
			$edit->fecha->when=array('show');
			$edit->fecha->mode='readonly';
			
			$edit->usuario = new inputField("Autor", "usuario");
			$edit->usuario->size = 90;
			$edit->usuario->when=array('show');
			$edit->usuario->mode='readonly';
		}
		
		//$edit->comentario = new textareaField("Comentario", "comentario");
		//$edit->comentario->rule = "required";
		//$edit->comentario->rows = 3;
		//$edit->comentario->cols=90;
		
		$edit->asignacion = new inputField("Asignada para:", "asignacion");
		$edit->asignacion->size=11;
		$edit->asignacion->append($this->datasis->modbus($modbus));
		$edit->asignacion->when=array('show','create','modify');
		
		$edit->evaluacion = new textareaField("Resultados", "evaluacion");
		$edit->evaluacion->rows =3;
		$edit->evaluacion->cols=90;
	
		$edit->revisado = new dropdownField("Revisado", "revisado");
		$edit->revisado->option("P","Pendiente");
		$edit->revisado->option("F","Fallos");
		$edit->revisado->option("B","Buenos"); 
		$edit->revisado->option("C","Consulta");
		
		if($usr=='EDGAR'){
			$edit->buttons("modify","undo", "delete","save","back");
		}else{
			$edit->buttons( "undo","back","save");
		}
		
		$edit->build();
		
		$acti=new myiframeField('acti_repo', '/supervisor/bitacorafyco/actividad/'.$edit->_status.'/'.$this->uri->segment(5),true,"300","auto","0");
		$acti->status='show';
		$acti->build();
		
		$data['content'] =$edit->output.$acti->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Bitacora</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function resultados($actividad=''){ 
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Agregar", "itbitacora");
		$edit->back_url = site_url("supervisor/bitacorafyco/filteredgrid");
		$edit->pre_process('insert','_pre_insert');
		
		$edit->_dataobject->db->set('usuario', $this->session->userdata('usuario'));
		$edit->_dataobject->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$edit->_dataobject->db->set('fecha'  , 'NOW()', FALSE);
				
		$edit->actividad = new inputField("Actividad","actividad");
    $edit->actividad->size = 10;
    $edit->actividad->insertValue=$actividad;
				
		if ($edit->_status=='show'){
			$edit->fecha = new dateonlyField("Fecha","fecha", "d/m/Y");
			$edit->fecha->when=array('show');
			$edit->fecha->mode='readonly';
			
			$edit->usuario = new inputField("Autor", "usuario");
			$edit->usuario->size = 90;
			$edit->usuario->when=array('show');
			$edit->usuario->mode='readonly';
		}
		
		$edit->resultado = new textareaField("Resultado", "resultado");
		$edit->resultado->rule = "required";
		$edit->resultado->rows = 3;
		$edit->resultado->cols=90;
	
		$edit->buttons("modify", "save", "undo");
		$edit->build();
		
		$acti=new myiframeField('acti_repo',"/supervisor/bitacorafyco/ver/$actividad".$edit->_status.'/'.$this->uri->segment(5),true,"300","auto","0");
		$acti->status='show';
		$acti->build();
				
		$data['content'] =$edit->output.$acti->output;;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Resultados</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function actividad($nid){
		$this->rapyd->load("datafilter2","datagrid");
		
		$filter = new DataFilter2("Actividades Relacionadas");
		$select=array("actividad","fecha","hora","usuario","comentario","actividad","id",'evaluacion', "if(revisado='P','Pendiente',if(revisado='B','Bueno',if(revisado='C','Consulta','Fallo'))) revisado");
		
		$id=$this->uri->segment(3);
		$filter->db->select($select);
		$filter->db->from('bitacora');
		if($this->uri->segment(4)!='create')
		$filter->db->where('id <>',$id);
		$filter->db->orderby('fecha','desc');

		$filter->actividad = new inputField("Actividad", "actividad");
		$filter->actividad->clause ="likesensitive";
		$filter->actividad->append("Sencible a las Mayusc&uacute;las");

		$filter->buttons("search");
		$filter->build();
		
		$uri = "supervisor/bitacorafyco/dataedit/show/<#id#>";
		$salida=$filter->output;
		if (!empty($filter->actividad->newValue)){
			$grid = new DataGrid("Lista de Actividades");
			$grid->order_by("fecha","desc");
			$grid->per_page = 10;
    	
			$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>");
			$grid->column("Usuario","usuario");
			$grid->column("Actividad realizada","actividad");
			$grid->column("Comentario","comentario");
			$grid->column("Resultado","evaluacion");
			$grid->column("Revisado","revisado");
			$grid->build();
			$salida.=$grid->output;
		}
		$data['content'] =$salida;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='';
		$this->load->view('view_ventanas_sola', $data);
	}
	function reciente(){

		$this->rapyd->load("datatable");
		
		$table = new DataTable(null);
		$table->cell_attributes = 'style="vertical-align:middle;"';
		
		$select=array("actividad","id","usuario","resultado","fecha","hora");
		$table->db->select($select);
		$table->db->from("itbitacora");
		$table->db->orderby("id  DESC");
		//$table->db->limit(10,0);
		
		$this->db->_escape_char='';
		$this->db->_protect_identifiers=false;

		$table->per_row  = 1;
		$table->per_page = 5;
		$table->cell_template = "<div class='marco1' ><#resultado#><br><b class='mininegro'>&nbsp;<dbdate_to_human><#fecha#>|d/m/Y h:m:s</dbdate_to_human> Usuario: <#usuario#> Actividad: <#actividad#> $link</b></div><br>";
		$table->build();
		//echo $table->db->last_query();

		$data['content'] = $table->output;
		$data["head"]    = $this->rapyd->get_head().style('marcos.css').style('estilos.css');
		$this->load->view('view_ventanas_sola', $data);
	}
	function ver($actividad){
		if(empty($actividad)) redirect("supervisor/bitacorafyco/filteredgrid");
		$this->rapyd->load("datatable");
		
		$link=($this->datasis->puede(908001))? anchor('/supervisor/tiket/dataedit/delete/<#id#>','borrar'):'';

		$table = new DataTable(null);
		$table->cell_attributes = 'style="vertical-align:middle;"';
		
		$select=array("id","usuario","resultado","fecha","hora");
		$table->db->select($select);
		$table->db->from("itbitacora");
		$table->db->where('actividad',$actividad);
		$table->db->orderby("id");
		
		$this->db->_escape_char='';
		$this->db->_protect_identifiers=false;

		$table->per_row  = 1;
		$table->per_page = 50;
		$table->cell_template = "<div class='marco1' ><#resultado#><br><b class='mininegro'>&nbsp;<dbdate_to_human><#fecha#>|d/m/Y h:m:s</dbdate_to_human> Usuario: <#usuario#> $link</b></div><br>";
		$table->build();

		$data['content'] = $table->output;
		$data["head"]    = $this->rapyd->get_head().style('marcos.css').style('estilos.css');
		$this->load->view('view_ventanas_sola', $data);
	}	
	function resumen(){
		
		$this->rapyd->load("datafilter","datagrid2");
		
		$grid = new DataGrid2("Res&uacute;men de Bitacora");
		$grid->agrupar('Autor: ', 'usr');
		$grid->db->select(array('UPPER(asignacion) AS usr',"if(revisado='P','Pendientes',if(revisado='B','Buenos',if(revisado='C','Consultas','Fallos'))) AS resul",'COUNT(*) AS cant'));
		$grid->db->from('bitacora');
		//$grid->db->where('usuario<>','coicoi');
		$grid->db->groupby('asignacion,revisado');
		
		$grid->column("Resultado","resul");
		$grid->column("Cantidad","cant");
		$grid->build();
		//echo $grid->db->last_query();
		//echo '<pre>'; print_r($grid->data); echo '</pre>';
		
		$totales=$buenos=$promedio=array();
		foreach($grid->data AS $colum){
			$revisado=substr($colum['resul'], 0, 1);
			if($revisado=='B' OR $revisado=='F' ){
				if (!isset($totales[$colum['usr']])) $totales[$colum['usr']]=0;
				if($revisado=='B') $buenos[$colum['usr']]=$colum['cant'];
				$totales[$colum['usr']]+=$colum['cant'];
			}
		}
		foreach($totales AS $ind=>$tot){
			$promedio[$ind]=round (($buenos[$ind]/$tot)*100,2);
		}
		$out='<table align="center">';
		foreach($promedio AS $usuario=>$prome)
			$out.="<tr><td>$usuario:</td><td> $prome %</td></tr>";
		$out.='</table>';
		
		$data['content'] =$grid->output.'<h3>Promedio de &Eacute;xitos </h3>'.$out;
		$data["head"]    =$this->rapyd->get_head();
		$data['title']   ='';
		$this->load->view('view_ventanas', $data);
	}	
	function _pre_del($do) {
		$codigo=$do->get('us_codigo');
		if ($codigo==$this->session->userdata('usuario')){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='No se puede borrar usted mismo';
			return False;
		}
		return True;
	}
	function _pos_del($do){
		$codigo=$do->get('us_codigo');
		$mSQL="DELETE FROM intrasida WHERE usuario='$codigo'";
		$this->db->query($mSQL);
		return True;
	}
	function instalar(){
		$mSQL="CREATE TABLE `bitacora` (
		  `id` bigint(20) unsigned NOT NULL auto_increment,
		  `usuario` varchar(50) default NULL,
		  `nombre` varchar(100) default NULL,
		  `fecha` date default NULL,
		  `hora` time default NULL,
		  `actividad` text,
		  `comentario` text,
		  `revisado` char(1) default 'P',
		  `evaluacion` text,
		  `asignacion` varchar(10),
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=524 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	}
	function _pre_insert($do){
			$actividad=$do->get('actividad');
			$mSQL="UPDATE bitacora SET actualizado=CURDATE(),horac=CURTIME() WHERE id=$actividad";
			$this->db->simple_query($mSQL);
	}
} 
?>
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  