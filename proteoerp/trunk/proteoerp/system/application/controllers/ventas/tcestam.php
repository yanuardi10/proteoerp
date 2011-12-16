<?php
class tcestam extends Controller {
	var $titp='Cesta Tickets diarios';
	var $tits='Cesta Tickets';
	var $url ='ventas/tcestam/';
	function tcestam(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(216,1);
	}
	function index(){
		redirect($this->url."filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'tcestam');

		$filter->empre = new inputField('Empresa','empre');
		$filter->empre->rule      ='max_length[5]';
		$filter->empre->size      =7;
		$filter->empre->maxlength =5;

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;

		$filter->descrip = new textareaField('Descripci&oacute;n','descrip');
		$filter->descrip->rule      ='max_length[8]';
		$filter->descrip->cols = 70;
		$filter->descrip->rows = 4;

		$filter->cant = new inputField('Cantidad','cant');
		$filter->cant->rule      ='max_length[19]|numeric';
		$filter->cant->css_class ='inputnum';
		$filter->cant->size      =21;
		$filter->cant->maxlength =19;

		$filter->monto = new inputField('Monto','monto');
		$filter->monto->rule      ='max_length[19]|numeric';
		$filter->monto->css_class ='inputnum';
		$filter->monto->size      =21;
		$filter->monto->maxlength =19;

		$filter->total = new inputField('Total','total');
		$filter->total->rule      ='max_length[19]|numeric';
		$filter->total->css_class ='inputnum';
		$filter->total->size      =21;
		$filter->total->maxlength =19;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<#id#>','<#empre#><#fecha#>');

		$grid = new DataGrid('');
		$grid->order_by('empre,fecha');
		$grid->per_page = 40;

		$grid->column_orderby('Empresa - Fecha'   ,"$uri"                                        ,'empre'     ,'align="left"');
		$grid->column_orderby('Empresa'           ,"empre"                                       ,'empre'     ,'align="left"');
		$grid->column_orderby('Fecha'             ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha'     ,'align="center"');
		$grid->column_orderby('Descripci&oacute;n',"descrip"                                     ,'descrip'   ,'align="left"');
		$grid->column_orderby('Cantidad'          ,"<nformat><#cant#></nformat>"                 ,'cant'      ,'align="right"');
		$grid->column_orderby('Monto'             ,"<nformat><#monto#></nformat>"                ,'monto'     ,'align="right"');
		$grid->column_orderby('Total'             ,"<nformat><#total#></nformat>"                ,'total'     ,'align="right"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);

	}
	function dataedit(){
		$this->rapyd->load('dataobject','dataedit');

		$script='
		$(".inputnum").numeric(".");
		$(document).ready(function() { 
			function calcula(){
				cant = parseFloat($("#cant").val());
				monto=parseFloat($("#monto").val());
				total=cant*monto;
				
				$("#total").val(total);
			}
			$("#cant").change(function(){
				calcula();
			});
			$("#monto").change(function(){
				calcula();
			});
		}); 
		
		';

		$tsprv=array(
		'tabla'   =>'tsprv',
		'columnas'=>array(
		'codigo' =>'C&oacute;digo',
		'nombre'=>'Nombre'),
		'filtro'  =>array('codigo'=>'C&oacute;digo','nombre'=>'Nombre'),
		'retornar'=>array('codigo'=>'empre','nombre'=>'nombrep'),
		'titulo'  =>'Buscar Empresa de Tickets');
		
		$boton=$this->datasis->modbus($tsprv);

		$do = new DataObject('tcestam');
		$do->pointer('tsprv','tcestam.empre=tsprv.codigo','tsprv.nombre nombrep');

		$edit = new DataEdit($this->tits, $do);
		
		$edit->on_save_redirect=TRUE;

		$edit->script($script,"create");
		$edit->script($script,"modify"); 

		$edit->back_url = site_url($this->url."filteredgrid");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_valida');
		$edit->pre_process('update','_valida');

		$edit->id = new inputField('Ref','id');
		$edit->id->rule='trim|max_length[5]';
		$edit->id->when =array('show','modify');
		$edit->id->mode ='autohide';

		$edit->empre = new inputField('Empresa','empre');
		$edit->empre->rule='trim|max_length[5]|required';
		$edit->empre->size =7;
		$edit->empre->maxlength =5;
		$edit->empre->append($boton);
		
		$edit->nombrep = new inputField('Empresa','nombrep');
		$edit->nombrep->readonly=true;
		$edit->nombrep->pointer=true;
		$edit->nombrep->size =40;
		$edit->nombrep->maxlength =5;
		$edit->nombrep->in  ='empre';

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha|required';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;
		$edit->fecha->insertValue=date('Ymd');

		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		//$edit->descrip->rule='max_length[8]';
		$edit->descrip->size = 70;

		$edit->cant = new inputField('Cantidad','cant');
		$edit->cant->rule='max_length[19]|numeric|required';
		$edit->cant->css_class='inputnum';
		$edit->cant->size =21;
		$edit->cant->maxlength =19;

		$edit->monto = new inputField('Denominacion','monto');
		$edit->monto->rule='max_length[19]|numeric|required';
		$edit->monto->css_class='inputnum';
		$edit->monto->size =21;
		$edit->monto->maxlength =19;

		$edit->total = new inputField('Total','total');
		$edit->total->rule='max_length[19]|numeric|required';
		$edit->total->css_class='inputnum';
		$edit->total->size =21;
		$edit->total->maxlength =19;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();
		$data['content'] = $edit->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);

	}
	
	function _valida($do){
		$error='';
		$empre  = $do->get('empre');
//		$fecha = $do->get('fecha');
		$cant  = $do->get('cant');
		$monto = $do->get('monto');
		
		
		$do->set('total',$cant*$monto);

		$empree=$this->db->escape($empre);
		
		$c=$this->datasis->dameval("SELECT COUNT(*) FROM tsprv WHERE codigo=$empree");
		if($c==0)
		$error.="Error. La empresa no existe";
		
		if($c>0)
		$error.="";
		
		if(!($cant+$monto>0))
		$error.='los montos no son validos';
//		$cant=$this->datasis->dameval("SELECT COUNT(*) FROM tcestam WHERE fecha=$fecha AND empre=$enpree");
//		if($cant>0)
//		$error.="Error. la ";
		
		
		if(!empty($error)){
			$do->error_message_ar['pre_ins']=$error;
			$do->error_message_ar['pre_upd']=$error;
			return false;
		}
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
		redirect('ventas/tcestam/dataedit/create');
	}
	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}
	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}

	function instalar(){
		$mSQL="CREATE TABLE `tcestam` (
		`empre` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		`fecha` date NOT NULL DEFAULT '0000-00-00',
		`cod_prov` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
		`descrip` text COLLATE utf8_unicode_ci,
		`cant` decimal(19,2) DEFAULT '0.00',
		`monto` decimal(19,2) DEFAULT '0.00',
		`total` decimal(19,2) DEFAULT '0.00',
		PRIMARY KEY (`empre`,`fecha`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$this->db->simple_query($mSQL);
	}

}
?>
