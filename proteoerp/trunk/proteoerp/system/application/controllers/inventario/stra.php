<?php
class stra extends Controller {

	function stra(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(302,1);
		$this->back_dataedit='inventario/stra/filteredgrid';
	}

	function index(){
		redirect('inventario/stra/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter("Filtro de Transferencias","stra");

		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size=15;

		$filter->fecha = new dateonlyField("Fecha", "fecha");
		$filter->fecha->size=12;

		$filter->envia = new inputField("Envia", "envia");
		$filter->envia->size=12;

		$filter->recibe = new inputField("Recibe", "recibe");		
		$filter->recibe->size=12;

		$filter->buttons("reset","search");    
		$filter->build();

		$uri = anchor('inventario/stra/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid('Lista de Transferencias');
		$grid->order_by('numero','desc');
		$grid->per_page = 15;
		$grid->use_function("substr");

		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha','<dbdate_to_human><#fecha#></dbdate_to_human>','fecha',"align='center'");
		$grid->column_orderby('Env&iacute;a','envia','envia');
		$grid->column_orderby('Recibe','recibe','recibe');
		$grid->column_orderby('Observaci&oacute;n','observ1','observ1');

		//echo $grid->db->last_query();
		$grid->add('inventario/stra/dataedit/create');
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Transferencias de inventario');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				'peso'=>'Peso'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'descrip_<#i#>'),
			'where'   =>'activo = "S" AND tipo="Articulo"',
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Busqueda de producto en inventario');
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');
		
		$script="
		function post_add_itstra(id){
			$('#cantidad_'+id).numeric(".");
			return true;
		}";

		$do = new DataObject('stra');
		$do->rel_one_to_many('itstra', 'itstra', 'numero');
		//$do->rel_pointer('itstra','sinv','itstra.codigo=sinv.codigo','sinv.descrip as sinvdescrip');

		$edit = new DataDetails("Transferencia", $do);
		$edit->back_url = $this->back_dataedit;
		$edit->set_rel_title('itstra','Producto <#o#>');

		$edit->script($script,'create');
		$edit->script($script,'modify');
		
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');
		$edit->post_process('insert','_post_insert');

		$edit->numero= new inputField('N&uacute;mero', 'numero');
		$edit->numero->mode="autohide";
		$edit->numero->size=10;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->fecha = new  dateonlyField("Fecha",  "fecha");
		$edit->fecha->rule='required|chfecha';
		$edit->fecha->insertValue = date("Y-m-d");  
		$edit->fecha->size =12;

		$edit->envia = new dropdownField("Almac&eacute;n que Env&iacute;a", "envia");  
		$edit->envia->option("","Seleccionar");  
		$edit->envia->options("SELECT ubica,ubides FROM caub ORDER BY ubica");
		$edit->envia->rule ="required";
		$edit->envia->style="width:200px;";

		$edit->recibe = new dropdownField('Almac&eacute;n que Recibe', 'recibe');
		$edit->recibe->option('','Seleccionar');  
		$edit->recibe->options("SELECT ubica,ubides FROM caub ORDER BY ubica");
		$edit->recibe->rule ='required|callback_chrecibe';
		$edit->recibe->style="width:200px;";

		$edit->observ1 = new inputField('Observaci&oacute;n','observ1');
		$edit->observ1->rule='max_length[60]|trim';
		$edit->observ1->size =32;
		$edit->observ1->maxlength =30;

		//comienza el detalle
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->db_name='codigo';
		$edit->codigo->append($btn);
		$edit->codigo->rule = 'trim|required';
		$edit->codigo->rel_id='itstra';
		$edit->codigo->readonly=true;
		$edit->codigo->maxlength=15;
		$edit->codigo->size     =15;

		$edit->descrip = new inputField('Descripci&oacute;n', 'descrip_<#i#>');
		$edit->descrip->db_name='descrip';
		$edit->descrip->rel_id='itstra';
		$edit->descrip->maxlength=45;
		$edit->descrip->size     =40;

		$edit->cantidad = new inputField('Cantidad', 'cantidad_<#i#>');
		$edit->cantidad->db_name  ='cantidad';
		$edit->cantidad->css_class='inputnum';
		$edit->cantidad->rel_id   ='itstra';
		$edit->cantidad->rule     ='numeric|mayorcero|required';
		$edit->cantidad->maxlength=10;
		$edit->cantidad->autocomplete=false;
		$edit->cantidad->size     =10;
		//Fin del detalle

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('save', 'undo', 'back','add_rel'); 
		$edit->build();
		$conten['form']  =&  $edit;
		$data['content'] =  $this->load->view('view_stra', $conten,true);
		$data['title']   =  heading('Transferencias de inventario');
		$data['head']    =  script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data); 
	}

	function chrecibe($recibe){
		$envia=$this->input->post('envia');
		if($recibe!=$envia){
			return true;
		}
		$this->validation->set_message('chrecibe',"El almac&eacute;n que env&iacute;a no puede ser igual a que recibe");
		return false;
	}

	function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('nstra');
		$do->set('numero',$numero);
		$transac = $this->datasis->fprox_numero('ntransa');
		$do->set('transac', $transac);

		$cana = $do->count_rel('itstra'); $error=0;
		for($i = 0;$i < $cana;$i++){
			$itcodigo  = $do->get_rel('itstra', 'codigo'  ,$i);
			$dbitcodigo=$this->db->escape($itcodigo);
			$sinvrow=$this->datasis->damerow('SELECT iva,precio1,precio2,precio3,precio4, ultimo FROM sinv WHERE codigo='.$dbitcodigo);

			$do->set_rel('itstra', 'precio1',  $sinvrow['precio1'], $i);
			$do->set_rel('itstra', 'precio2',  $sinvrow['precio2'], $i);
			$do->set_rel('itstra', 'precio3',  $sinvrow['precio3'], $i);
			$do->set_rel('itstra', 'precio4',  $sinvrow['precio4'], $i);
			$do->set_rel('itstra', 'iva'    ,  $sinvrow['iva']    , $i);
			$do->set_rel('itstra', 'costo'  ,  $sinvrow['ultimo'] , $i);
		}
		return true;
	}

	function _post_insert($do){
		$envia   = $do->get('envia');
		$recibe  = $do->get('recibe');
		$dbenvia = $this->db->escape($envia);
		$dbrecibe= $this->db->escape($recibe);

		$cana = $do->count_rel('itstra'); $error=0;
		for($i = 0;$i < $cana;$i++){
			$itcana    = floatval($do->get_rel('itstra', 'cantidad',$i));
			$itcodigo  = $do->get_rel('itstra', 'codigo'  ,$i);
			$dbitcodigo=$this->db->escape($itcodigo);

			$mSQL="INSERT INTO itsinv (codigo,alma,existen) VALUES (${dbitcodigo},${dbenvia},-$itcana) ON DUPLICATE KEY UPDATE existen=existen-${itcana}";
			$ban=$this->db->simple_query($mSQL);
			if(!$ban){ memowrite($mSQL,'stra'); $error++;}

			$mSQL="INSERT INTO itsinv (codigo,alma,existen) VALUES (${dbitcodigo},${dbrecibe},$itcana) ON DUPLICATE KEY UPDATE existen=existen+${itcana}";
			$ban=$this->db->simple_query($mSQL);
			if(!$ban){ memowrite($mSQL,'stra'); $error++;}
		}

		$codigo=$do->get('numero');
		logusu('stra',"TRANSFERENCIA $codigo CREADO");
		return true;
	}

	function _pre_update($do){
		return false;
	}

	function _pre_delete($do){
		return false;
	}
}