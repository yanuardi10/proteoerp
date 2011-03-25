<?php //require_once(BASEPATH.'application/controllers/validaciones.php');
class conv extends Controller {

	var $chrepetidos=array();

	function conv(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index() {
		redirect('inventario/conv/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Conversiones');
		$filter->db->select(array('a.fecha','a.numero','a.almacen','b.ubides'));
		$filter->db->from('conv AS a');
		$filter->db->join('caub AS b','a.almacen=b.ubica');

		$filter->fecha = new dateonlyField('Fecha', 'fecha');
		$filter->fecha->size=15;
		$filter->fecha->maxlength=15;
		$filter->fecha->rule='trim';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=15;

		$filter->almacen = new inputField('Almac&eacute;n', 'almacen');
		$filter->almacen->size=15;

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('inventario/conv/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid('Lista de Conversiones');
		$grid->use_function('dbdate_to_human');
		$grid->order_by('numero','desc');
		$grid->per_page = 10;

		$grid->column_orderby('N&uacute;mero', $uri,'numero');
		$grid->column_orderby('Fecha','<dbdate_to_human><#fecha#></dbdate_to_human>','fecha');
		$grid->column_orderby('Almac&eacute;n','ubides','almacen');

		$grid->add('inventario/conv/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Conversiones de inventario');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigo_<#i#>',
				'descrip'=>'descrip_<#i#>',
				'ultimo' =>'costo_<#i#>'
			),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Articulo',
			'where'   => '`activo` = "S" AND `tipo` = "Articulo"',
			'script'  => array('post_modbus_sinv(<#i#>)')
		);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$do = new DataObject('conv');
		$do->rel_one_to_many('itconv', 'itconv', 'numero');
		$do->rel_pointer('itconv','sinv','itconv.codigo=sinv.codigo','sinv.descrip AS sinvdescrip','sinv.ultimo AS sinvultimo');

		$edit = new DataDetails('Conversiones', $do);
		$edit->back_url = site_url('inventario/conv/filteredgrid');
		$edit->set_rel_title('itconv','Producto <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->observa1 = new inputField('Observaciones', 'observ1');
		$edit->observa1->size      = 40;
		$edit->observa1->maxlength = 80;

		$edit->almacen = new  dropdownField ('Almac&eacute;n', 'almacen');
		$edit->almacen->options('SELECT ubica, CONCAT(ubica,\' \',ubides) nombre FROM caub ORDER BY ubica');
		$edit->almacen->rule = 'required';
		$edit->almacen->style='width:200px;';
		$edit->almacen->size = 5;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itconv';
		$edit->codigo->rule     = 'required|callback_chrepetidos';
		$edit->codigo->append($btn);

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>', 'descrip_<#i#>');
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->readonly  = true;
		$edit->descrip->rel_id='itconv';

		$edit->entrada = new inputField('Entrada <#o#>', 'entrada_<#i#>');
		$edit->entrada->db_name  = 'entrada';
		$edit->entrada->css_class= 'inputnum';
		$edit->entrada->rel_id   = 'itconv';
		$edit->entrada->maxlength= 10;
		$edit->entrada->size     = 6;
		$edit->entrada->rule     = 'required|positive';
		$edit->entrada->autocomplete=false;
		$edit->entrada->onkeyup  ='validaEnt(<#i#>)';

		$edit->salida = new inputField('Salida <#o#>', 'salida_<#i#>');
		$edit->salida->db_name  = 'salida';
		$edit->salida->css_class= 'inputnum';
		$edit->salida->rel_id   = 'itconv';
		$edit->salida->maxlength= 10;
		$edit->salida->size     = 6;
		$edit->salida->rule     = 'required|positive';
		$edit->salida->autocomplete=false;
		$edit->salida->onkeyup  ='validaSalida(<#i#>)';

		$edit->costo = new hiddenField('', 'costo_<#i#>');
		$edit->costo->db_name   = 'costo';
		$edit->costo->rel_id    = 'itconv';
		//**************************
		//fin de campos para detalle
		//**************************

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_conv', $conten,true);
		$data['title']   = heading('Conversiones de inventario');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}
	
	function chrepetidos($cod){
		if(array_search($cod, $this->chrepetidos)===false){
			$this->chrepetidos[]=$cod;
			return true;
		}else{
			$this->validation->set_message('chrepetidos', 'El art&iacute;culo '.$cod.' esta repetido');
			return false;
		}
	}

	function _pre_insert($do){
		$cana=$do->count_rel('itconv');
		$monto=$entradas=$salidas=0;
		//Hasta aca en costo trae el valor del ultimo de sinv, se opera para cambiarlo a:
		//costo=costo*(entrada o salida segun se el caso)
		for($i=0;$i<$cana;$i++){
			$ent=$do->get_rel('itconv','entrada',$i);
			$sal=$do->get_rel('itconv','salida' ,$i);
			if ($ent!=0 && $sal!=0){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='No puede tener entradas y salidas en el rubro .'.$i+1;
				return false;	
			}
			if ($ent==0 && $sal==0){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe tener entradas o salidas en el rubro .'.$i+1;
				return false;	
			}
			if($ent != 0){
				$entradas+=$ent;
				$monto=round($ent*$do->get_rel('itconv','costo',$i),2);
			}
			if($sal != 0){
				$salidas+=$sal;
				$monto=round($sal*$do->get_rel('itconv','costo',$i),2);
			}
			$do->set_rel('itconv','costo'   ,$monto  ,$i);
		}
		if ($entradas == 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe ingresar al menos una entrada.';
			return false;	
		}
		if ($salidas == 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe ingresar al menos una salida.';
			return false;	
		}
		
		$numero =$this->datasis->fprox_numero('nconv');
		$transac=$this->datasis->fprox_numero('ntransa');
		$usuario=$do->get('usuario');
		$estampa=date('Ymd');
		$hora   =date("H:i:s");
		
		$obs1=$obs2=$observa="";
		if(strlen($do->get("observ1")) >80 ) $observa=substr($do->get("observ1"),0,80);
		else $observa=$do->get("observ1");
		if (strlen($observa)>40){
			$obs1=substr($observa, 0, 39 );
			$obs2=substr($observa,40);
		}else{
			$obs1=$observa;
		}
		
		$do->set('observ1',$obs1);
		$do->set('observ2',$obs2);
		$do->set('estampa',$estampa);
		$do->set('hora'   ,$hora);
		$do->set('numero' ,$numero);
		$do->set('transac',$transac);
		
		for($i=0;$i<$cana;$i++){
			$do->set_rel('itconv','estampa' ,$estampa,$i);
			$do->set_rel('itconv','hora'    ,$hora   ,$i);
			$do->set_rel('itconv','transac' ,$transac,$i);
			$do->set_rel('itconv','usuario' ,$usuario,$i);;
		}
		return true;
	}

	function _pre_update($do){
		return false;
	}

	function _post_insert($do){
		//trafrac ittrafrac
		$codigo=$do->get('numero');
		logusu('conv',"Conversion $codigo CREADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('conv',"Conversion $codigo ELIMINADO");
	}
}