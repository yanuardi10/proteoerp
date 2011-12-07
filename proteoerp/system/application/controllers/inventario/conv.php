<?php //require_once(BASEPATH.'application/controllers/validaciones.php');
class conv extends Controller {

	var $chrepetidos=array();

	function conv(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->back_dataedit='inventario/conv/filteredgrid';
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

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
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
		$edit->back_url = $this->back_dataedit;
		$edit->set_rel_title('itconv','Producto <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->pre_process('delete','_pre_delete');
		$edit->post_process('insert','_post_insert');

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
		$edit->codigo->rel_id   = 'itconv';
		$edit->codigo->rule     = 'required|callback_chrepetidos|callback_chpeso[<#i#>]';
		$edit->codigo->append($btn);

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>', 'descrip_<#i#>');
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		//$edit->descrip->readonly  = true;
		$edit->descrip->rel_id='itconv';
		$edit->descrip->type='inputhidden';

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

		$edit->buttons('save', 'undo', 'back','add_rel','add');
		$edit->build();

		$conten['form']  =& $edit;
		$data['script']   = script('jquery.js');
		$data['script']  .= script('jquery-ui.js');
		$data['script']  .= script('plugins/jquery.numeric.pack.js');
		$data['script']  .= script('plugins/jquery.floatnumber.js');
		$data['script']  .= script('plugins/jquery.meiomask.js');
		$data['script']  .= phpscript('nformat.js');
		$data['style']    = style('redmond/jquery-ui-1.8.1.custom.css');
		$data['content']  = $this->load->view('view_conv', $conten,true);
		$data['title']    = heading('Conversiones de inventario');
		$data['head']     = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function chpeso($codigo,$id){
		$salida=$this->input->post('salida_'.$id);
		$this->validation->set_message('chpeso', 'El art&iacute;culo '.$codigo.' no tiene peso, se necesita para el c&aacute;lculo del costo');
		if($salida>0){
			$dbcodigo=$this->db->escape($codigo);
			$peso=$this->datasis->dameval('SELECT peso FROM sinv WHERE codigo='.$dbcodigo);
			if($peso>0){
				return true;
			}
			return false;
		}
		return true;
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
		$this->costo_entrada= 0;
		$this->peso_salida  = 0;
		$this->pesos        = array();
		
		//Hasta aca en costo trae el valor del ultimo de sinv, se opera para cambiarlo a:
		//costo=costo*(entrada o salida segun se el caso)
		for($i=0;$i<$cana;$i++){
			$ent=$do->get_rel('itconv','entrada',$i);
			$sal=$do->get_rel('itconv','salida' ,$i);
			$costo =$do->get_rel('itconv','costo' ,$i);
			$codigo=$do->get_rel('itconv','codigo' ,$i);

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
				$this->costo_entrada+=$ent*$costo;
				$monto=round($ent*$do->get_rel('itconv','costo',$i),2);
			}
			if($sal != 0){
				$salidas+=$sal;
				$dbcodigo=$this->db->escape($codigo);
				$peso    =$this->datasis->dameval('SELECT peso FROM sinv WHERE codigo='.$dbcodigo);
				$this->pesos[$codigo] = $peso;

				$this->peso_salida+=$sal*$peso;
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
			//$do->set_rel('itconv','numero'  ,$estampa,$i);
			$do->set_rel('itconv','estampa' ,$estampa,$i);
			$do->set_rel('itconv','hora'    ,$hora   ,$i);
			$do->set_rel('itconv','transac' ,$transac,$i);
			$do->set_rel('itconv','usuario' ,$usuario,$i);
		}
		return true;
	}

	function _pre_update($do){
		return false;
	}

	function _post_insert($do){
		$alma   = $do->get('almacen');
		$codigo = $do->get('numero');
		$cana   = $do->count_rel('itconv');
		for($i=0;$i<$cana;$i++){
			$codigo = $do->get_rel('itconv','codigo' ,$i);
			$ent    = $do->get_rel('itconv','entrada',$i);
			$sal    = $do->get_rel('itconv','salida' ,$i);

			$monto   = $sal-$ent;
			$dbcodigo= $this->db->escape($codigo);
			$dbalma  = $this->db->escape($alma);

			$mSQL="INSERT INTO itsinv (codigo,alma,existen) VALUES ($dbcodigo,$dbalma,$monto) ON DUPLICATE KEY UPDATE existen=existen+($monto)";
			$ban=$this->db->simple_query($mSQL);
			if(!$ban){ memowrite($mSQL,'conv');}

			if($monto>0){
				$peso=$this->pesos[$codigo]*$monto;
				$participa=$peso/$this->peso_salida;
				$ncosto   =round($this->costo_entrada*$participa/$monto,2);

				$mycosto="IF(formcal='P',pond,IF(formcal='U',$ncosto,IF(formcal='S',standard,GREATEST(pond,ultimo))))";
				$mSQL='UPDATE sinv SET
							ultimo ='.$ncosto.',
							base1  =ROUND(precio1*10000/(100+iva))/100, 
							base2  =ROUND(precio2*10000/(100+iva))/100, 
							base3  =ROUND(precio3*10000/(100+iva))/100, 
							base4  =ROUND(precio4*10000/(100+iva))/100, 
							margen1=ROUND(10000-(('.$mycosto.')*10000/base1))/100,
							margen2=ROUND(10000-(('.$mycosto.')*10000/base2))/100,
							margen3=ROUND(10000-(('.$mycosto.')*10000/base3))/100,
							margen4=ROUND(10000-(('.$mycosto.')*10000/base4))/100,
							existen=existen+('.$monto.')
					WHERE codigo='.$dbcodigo;
					$ban=$this->db->simple_query($mSQL);
					if(!$ban){ memowrite($mSQL,'conv');}
			}else{
				$mSQL="UPDATE sinv SET existen=existen+($monto) WHERE codigo=$dbcodigo";
				$ban=$this->db->simple_query($mSQL);
				if(!$ban){ memowrite($mSQL,'conv');}
			}
		}

		//trafrac ittrafrac
		logusu('conv',"Conversion $codigo CREADO");
	}

	function _pre_delete($do){
		return false;
	}

	function instalar(){
		$mSQL = "ALTER TABLE conv ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ";;
		$this->db->simple_query($mSQL);
	}
}