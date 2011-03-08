<?php
class movproveedores extends Controller {
	var $data_type = null;
	var $data = null;

	function movproveedores(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
    	$this->datasis->modulo_id(507,1);
    	redirect("finanzas/movproveedores/filteredgrid");
    }

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$sprv=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'cod_prv'),
			'titulo'  =>'Buscar Proveedor',
			'where'   =>'tipo IN (3,4)');
		$boton=$this->datasis->modbus($sprv);

		$filter = new DataFilter('Filtro', 'sprm');

		$filter->fecha = new dateonlyField('Fecha', 'fecha');
		$filter->fecha->size     = 10;
		$filter->fecha->operator = '=';
		$filter->fecha->clause   = 'where';

		$filter->tipo_doc = new dropdownField('Tipo', 'tipo_doc');
		$filter->tipo_doc->option('','Todos');
		$filter->tipo_doc->option('FC','Factura');
		$filter->tipo_doc->option('ND','D&eacute;bito');
		$filter->tipo_doc->option('AN','Anticipos');
		$filter->tipo_doc->option('GI','Giros');
		$filter->tipo_doc->operator = '=';
		$filter->tipo_doc->style    = 'width: 100px;';
		$filter->tipo_doc->clause   = 'where';

		$filter->cod_prv = new inputField('C&oacute;digo', 'cod_prv');
		$filter->cod_prv->append($boton);
		$filter->cod_prv->size=6;
	
		$filter->numero = new inputField('N&uacute;mero','numero');
		$filter->numero->size=15;

		$filter->buttons('reset','search');
		$filter->build();

		$fields = $this->db->field_data('sprm');
		foreach ($fields as $field){
			if($field->primary_key==1){
				$ppk[]='<#'.$field->name.'#>';
			}
		}

		$uri = anchor('finanzas/movproveedores/dataedit/show/'.implode('/',$ppk),'<#numero#>');

		$grid = new DataGrid('Lista de Movimiento de Proveedores');
		$grid->order_by('fecha','desc');
		$grid->per_page = 20;

		$grid->column_orderby('Nro'      ,$uri      ,'numero'  );
		$grid->column_orderby('Proveedor','cod_prv' ,'cod_prv' );
		$grid->column_orderby('Nombre'   ,'nombre'  ,'nombre'  );
		$grid->column_orderby('Tipo'     ,'tipo_doc','tipo_doc');
		$grid->column_orderby('Fecha'    ,'<dbdate_to_human><#fecha#></dbdate_to_human>'   ,'fecha'   );
		$grid->column_orderby('Monto'    ,'<nformat><#monto#></nformat>'   ,'monto'   ,'align="right"');

		//$grid->add("finanzas/movproveedores/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Movimiento de Proveedores');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit(){
		$this->rapyd->load("dataedit");		
		$script ='
		$(function(){
			$(".inputnum").numeric(".");
		});';

		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;digo Proveedor',
			'nombre'=>'Nombre',
			'rif'=>'RIF'),
			'filtro'  =>array('proveed' =>'C&oacute;digo Proveedor','nombre'=>'Nombre','rif'=>'RIF'),
			'retornar'=>array('proveed'=>'cod_prv','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor');
		$bsprv =$this->datasis->modbus($mSPRV);

		$edit = new DataEdit("Movimiento de Proveedores", "sprm");
		$edit->back_url = site_url("finanzas/movproveedores/filteredgrid");
		//$edit->script($script, "create");
		//$edit->script($script, "modify");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->cod_prv =new inputField("C&oacute;digo de Proveedor", "cod_prv");
		$edit->cod_prv->mode="autohide";
		$edit->cod_prv->size=12;
		$edit->cod_prv->rule= "trim|required";//|callback_chexiste
		$edit->cod_prv->append($bsprv);
		$edit->cod_prv->readonly=true;

		$edit->nombre =new inputField('Nombre', 'nombre');
		$edit->nombre->size=30;
		$edit->nombre->rule= 'trim|strtoupper|required';
		$edit->nombre->maxlength=40;

		$edit->tipo_doc = new dropdownField('Tipo', 'tipo_doc');
		$edit->tipo_doc->option('ND','Debito');
		$edit->tipo_doc->option('FC','Factura');
		$edit->tipo_doc->option('AN','Anticipos');
		$edit->tipo_doc->option('GI','Giros');
		$edit->tipo_doc->style = 'width:100px';
		$edit->tipo_doc->rule  = 'required';

		$edit->numero = new inputField('N&uacute;mero','numero');
		$edit->numero->mode='autohide';
		$edit->numero->size =12;       
		$edit->numero->maxlength = 8;
		$edit->numero->rule = 'trim|required';

		$edit->fecha =new DateField('Fecha', 'fecha');
		$edit->fecha->size = 12;
		$edit->fecha->rule = 'trim|required';

		$edit->monto =new inputField('Monto', 'monto');
		$edit->monto->size = 12;
		$edit->monto->maxlength=17;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric';

		$edit->impuesto = new inputField('IVA', 'impuesto');
		$edit->impuesto->size = 12;
		$edit->impuesto->maxlength=17;
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->rule='trim|numeric';

		$edit->abonos = new inputField('Abonado','abonos');
		$edit->abonos->rule='max_length[17]|numeric';
		$edit->abonos->css_class='inputnum';
		$edit->abonos->size =19;
		$edit->abonos->maxlength =17;

		$edit->vence = new DateField('Vence', 'vence');
		$edit->vence->size =12;

		$edit->tipo_ref = new dropdownField('Tipo de Referencia', 'tipo_ref');
		$edit->tipo_ref->option("OS","OS");
		$edit->tipo_ref->option("AB","AB");
		$edit->tipo_ref->option("AC","AC");
		$edit->tipo_ref->option("AP","AP");
		$edit->tipo_ref->option("CR","CR");
		$edit->tipo_ref->style ='width:100px';

		$edit->num_ref =  new inputField('N&uacute;mero de Referencia','num_ref');
		$edit->num_ref->size     = 12;
		$edit->num_ref->maxlength= 8;
		$edit->num_ref->rule='trim';
		
		$edit->observa1 = new inputField('Observaciones','observa1');
		$edit->observa1->size =50;
		$edit->observa1->maxlength=50;
		$edit->observa1->rule='trim';
		
		$edit->observa2 = new inputField('.','observa2');
		$edit->observa2->size =50;
		$edit->observa2->maxlength=50;
		$edit->observa2->rule='trim';

		$edit->banco =  new dropdownField('Banco', 'banco');
		$edit->banco->option('','Selecionar');
		$edit->banco->options('SELECT codbanc,banco FROM banc ORDER BY codbanc');
		$edit->banco->style ='width:185px';

		$edit->tipo_op = new dropdownField('Tipo de Operaci&oacute;n','tipo_op');
		$edit->tipo_op->option('CH',"Cheque");
		$edit->tipo_op->option('DE',"Deposito");
		$edit->tipo_op->option('NC',"Nota de credito");
		$edit->tipo_op->option('ND',"Nota de debito");
		$edit->tipo_op->style='width:100px';

		$edit->numche = new inputField('N&uacute;mero de Cheque','numche');
		$edit->numche->size =12;
		$edit->numche->maxlength=12;
		$edit->numche->rule='trim';

		$edit->posdata =new DateonlyField('Posdata','posdata');
		$edit->posdata->size =12;

		$edit->benefi = new inputField('Beneficiario','benefi');
		$edit->benefi->size =50;
		$edit->benefi->maxlength=40;
		$edit->benefi->rule='trim';
    
		$edit->buttons('undo','back');//"delete","save", "modify",
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = heading('Movimiento de Proveedores');
		$data['head']    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data); 
	}

	function _post_insert($do){
		$codigo=$do->get('cod_prv');
		$nombre=$do->get('nombre');
		logusu('sprm',"MOVIMIENTO DE PROVEEDOR $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('cod_prv');
		$nombre=$do->get('nombre');
		logusu('sprm',"MOVIMIENTO DE PROVEEDOR $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('cod_prv');
		$nombre=$do->get('nombre');
		logusu('sprm',"MOVIMIENTO DE PROVEEDOR $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('cod_prv');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM sprm WHERE cod_prv='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM sprm  WHERE cod_prv='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el movimiento de proveedor $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}	
}