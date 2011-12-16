<?php
class aumentosueldo extends Controller {
	
	function aumentosueldo(){
		parent::Controller(); 
		$this->load->library("rapyd");
   }

    function index(){
    	$this->datasis->modulo_id(703,1);	
    	redirect("nomina/aumentosueldo/filteredgrid");
    }

	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");
		$this->rapyd->uri->keep_persistence();

		$pers=array(
		'tabla'   =>'pers',
		'columnas'=>array(
		'codigo'  =>'Codigo',
		'cedula'  =>'Cedula',
		'nombre'  =>'Nombre',
		'apellido' =>'Apellido'),
		'filtro'  =>array('codigo'=>'C&oacute;digo','cedula'=>'Cedula'),
		'retornar'=>array('codigo'=>'codigo'),
		'titulo'  =>'Buscar Personal');
					  
		$boton=$this->datasis->modbus($pers);


		$filter = new DataFilter2("Filtro por C&oacute;digo", 'ausu');
		
		$filter->codigo = new inputField("Codigo","codigo");
		$filter->codigo->size=15;
		$filter->codigo->append($boton);
		$filter->codigo->clause = "likerigth";
		
		$filter->fecha = new DateonlyField("Fecha","fecha");
		$filter->fecha->size=12;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('nomina/aumentosueldo/dataedit/show/<#codigo#>/<raencode><#fecha#></raencode>','<#codigo#>');

		$grid = new DataGrid("Lista de Aumentos de Sueldo");
		$grid->order_by("codigo","asc");
		$grid->per_page = 20;

		$grid->column("C&oacute;digo"  ,$uri);
		$grid->column("Nombre"         ,"nombre");
		$grid->column("Fecha"          ,"<dbdate_to_human><#fecha#></dbdate_to_human>"           ,"align='center'");
		$grid->column("Sueldo anterior","<number_format><#sueldoa#>|2|,|.</number_format>"       ,"align='right'");
		$grid->column("Sueldo nuevo"   ,"<number_format><#sueldo#>|2|,|.</number_format>"        ,"align='right'");
		$grid->column("Observaciones"  ,"observ1");
		$grid->column("..","oberv2");
			
		$grid->add("nomina/aumentosueldo/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Aumentos de Sueldo</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);

	}

	function dataedit()
 	{
		$this->rapyd->load("dataedit");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
		
		$pers=array(                         
			'tabla'   =>'pers',                         
			'columnas'=>array(                         
			'codigo'  =>'Codigo',                         
			'cedula'  =>'Cedula',                         
			'nombre'  =>'Nombre',                         
			'apellido' =>'Apellido'),                         
			'filtro'  =>array('codigo'=>'C&oacute;digo','cedula'=>'Cedula'),
			'retornar'=>array('codigo'=>'codigo','nombre'=>'nombre'),     
			'titulo'  =>'Buscar Personal');                         
					                           
		$boton=$this->datasis->modbus($pers);                         
		
		$edit = new DataEdit("Aumentos de Sueldo", "ausu");
		$edit->back_url = site_url("nomina/aumentosueldo/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
	  		
		$edit->codigo =   new inputField("Codigo","codigo");
		$edit->codigo->size = 15;
		$edit->codigo->append($boton);
		$edit->codigo->mode="autohide";
		$edit->codigo->maxlength=15;
		$edit->codigo->rule="required|callback_chexiste";
		$edit->codigo->group="Trabajador";
		
		$edit->nombre =  new inputField("Nombre", "nombre");
		$edit->nombre->size =40;
		$edit->nombre->maxlength=30;
		$edit->nombre->group="Trabajador";		
		
		$edit->fecha = new dateField("Apartir de la nomina", "fecha","d/m/Y");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 12;
		$edit->fecha->dbformat    = 'Ymd';
		$edit->fecha->rule ="required|callback_fpositiva";
		
		$edit->sueldoa =   new inputField("Sueldo anterior", "sueldoa");
		$edit->sueldoa->size = 14;
		$edit->sueldoa->css_class='inputnum';
		$edit->sueldoa->rule='callback_positivoa';
		$edit->sueldoa->maxlength=11;
		
		$edit->sueldo =   new inputField("Sueldo nuevo", "sueldo");
		$edit->sueldo->size = 14;
		$edit->sueldo->css_class='inputnum';
		$edit->sueldo->rule='callback_positivo';
		$edit->sueldo->maxlength=11;
		
		$edit->observ1 =   new inputField("Observaciones", "observ1");
		$edit->observ1->size = 51;
		$edit->observ1->maxlength=46;
		
		$edit->oberv2 = new inputField("", "oberv2");
		$edit->oberv2->size =51;
		$edit->oberv2->maxlength=46;
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Aumentos de Sueldo</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		$fecha=$do->get('fecha');
		logusu('ausu',"AUMENTO DE SUELDO A $codigo NOMBRE  $nombre FECHA $fecha CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		$fecha=$do->get('fecha');
		logusu('ausu',"AUMENTO DE SUELDO A $codigo NOMBRE  $nombre FECHA $fecha MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		$fecha=$do->get('fecha');
		logusu('ausu',"AUMENTO DE SUELDO A $codigo NOMBRE  $nombre FECHA $fecha ELIMINADO ");
	}
	function chexiste($fecha){
		$fecha=$this->input->post('fecha');
		$codigo=$this->input->post('codigo');
		
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM ausu WHERE codigo='$codigo' AND fecha='$fecha'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM ausu WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El aumento para $codigo $nombre fecha $fecha ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	
	function positivo($valor){
		if ($valor <= 0){
			$this->validation->set_message('positivo',"El campo Sueldo Nuevo debe ser positivo");
			return FALSE;
		}
  	return TRUE;
	}
	
	function positivoa($valor){
		if ($valor <= 0){
			$this->validation->set_message('positivoa',"El campo Sueldo Anterior debe ser positivo");
			return FALSE;
		}
  	return TRUE;
	}
	
	function fpositiva($valor){
		if ($valor < date('Ymd')){
			$this->validation->set_message('fpositiva',"El campo Apartir de la nomina, Debe ser una nomina futura");
			return FALSE;
		}
  	return TRUE;
	}
	
	function _post($do){
	
		$codigo=$do->get('codigo');
		$fecha =$do->get('fecha');
		redirect('nomina/aumentosueldo/dataedit/show/'.$codigo.'/'.raencode($fecha));
		echo 'nomina/aumentosueldo/dataedit/show/'.$codigo.'/'.raencode($fecha);
		exit;
	}
	
	function instalar(){
		$mSQL="ALTER TABLE ausu ADD PRIMARY KEY (codigo,fecha);";
		$this->db->simple_query($mSQL);	
	}
}
?>