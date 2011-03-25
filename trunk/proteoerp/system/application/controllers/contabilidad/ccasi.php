<?php //require_once(BASEPATH.'application/controllers/validaciones.php');
class ccasi extends Controller {
	var $qformato;
	var $chrepetidos=array();

	function ccasi(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index() {
		redirect('contabilidad/ccasi/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load("datagrid","datafilter");
		
		$filter = new DataFilter("Filtro de Asientos");
		$filter->db->select=array("comprob","fecha","descrip","origen","debe","haber","total");
		$filter->db->from('casi');
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d"); 
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->comprob = new inputField("N&uacute;mero"     , "comprob");
		$filter->comprob->size=15;
		
		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name="descrip"; 
		
		$filter->origen = new dropdownField("Or&iacute;gen", "origen");  
		$filter->origen->option("","Todos");
		$filter->origen->options("SELECT modulo, modulo valor FROM reglascont GROUP BY modulo");
		
		$filter->status = new dropdownField("Status", "status");  
		$filter->status->option("","Todos");
		$filter->status->option("A","Actualizado");
		$filter->status->option("D","Diferido");
		
		$filter->vdes = new checkboxField("Ver solo asientos descuadrados","vdes",'S','N');
		$filter->vdes->insertValue='N';
		$filter->vdes->clause='';
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('contabilidad/ccasi/dataedit/show/<#comprob#>','<#comprob#>');
    
		$grid = new DataGrid();
		$vdes = $this->input->post('vdes');
		if($vdes)
		$grid->db->where('(debe-haber) <>',0);
		$grid->order_by("comprob","asc");
		$grid->per_page = 15;
		$grid->column_orderby("N&uacute;mero",$uri,'comprob');
		$grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
		$grid->column_orderby("Descripci&oacute;n","descrip",'descrip');
		$grid->column_orderby("Or&iacute;gen"  ,"origen"  ,'origen',"align='center'");
		$grid->column_orderby("Debe"  ,"debe"  ,'debe',"align='right'");
		$grid->column_orderby("Haber" ,"haber" ,'haber',"align='right'");
		$grid->column_orderby("Total" ,"total" ,'total',"align='right'");
		
		$grid->add("contabilidad/casi/dataedit/create");
		$grid->build();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Asientos</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		
		$this->qformato=$qformato=$this->datasis->formato_cpla();
 		
 		$modbus=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'cuenta_<#i#>',
 				'departa'=>'ccosto<#i#>',
				'descrip'=>'concepto_<#i#>'
 			),
			'titulo'  =>'Buscar Cuenta',
			'p_uri'=>array(4=>'<#i#>'),
			'where'=>"codigo LIKE \"$qformato\"",
			'script'=>array('departa(<#i#>)')
			);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');
 		 			
 		$uri="/contabilidad/casi/dpto/";

		$do = new DataObject('casi');
		$do->rel_one_to_many('itcasi', 'itcasi', 'comprob');
		$do->rel_pointer('itcasi','cpla','itcasi.cuenta=cpla.codigo','cpla.ccosto AS cplaccosto');
		
		$edit = new DataDetails('Asientos', $do);
		$edit->back_url = site_url('contabilidad/ccasi/filteredgrid');
		$edit->set_rel_title('itcasi','Producto <#o#>');

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

		$edit->comprob = new inputField('N&uacute;mero', 'comprob');
		$edit->comprob->size     = 12;
		$edit->comprob->maxlength= 8;
		$edit->comprob->rule     ='required';
		//$edit->comprob->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		//$edit->comprob->when=array('show','modify');
		//$edit->comprob->mode='autohide';

		$edit->descrip = new inputField('Descripci&ocaute;n', 'descrip');
		$edit->descrip->size      = 40;
		$edit->descrip->maxlength = 60;

		$edit->status = new  dropdownField ('Status', 'status');
		$edit->status->option('A','Actualizado');
		$edit->status->option('D','Diferido');
		$edit->status->style='width:110px;';
		$edit->status->size = 5;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->cuenta = new inputField('Cuenta <#o#>', 'cuenta_<#i#>');
		$edit->cuenta->size     = 12;
		$edit->cuenta->db_name  = 'cuenta';
		$edit->cuenta->readonly = true;
		$edit->cuenta->rel_id   = 'itcasi';
		$edit->cuenta->rule     = 'required|callback_chrepetidos';
		$edit->cuenta->append($btn);

		$edit->concepto = new inputField('Concepto <#o#>', 'concepto_<#i#>');
		$edit->concepto->size      = 30;
		$edit->concepto->db_name   = 'concepto';
		$edit->concepto->maxlength = 50;
		$edit->concepto->readonly  = true;
		$edit->concepto->rel_id    = 'itcasi';
		
		$edit->referen = new inputField('Referencia <#o#>', 'referen_<#i#>');
		$edit->referen->size      = 15;
		$edit->referen->db_name   = 'referen';
		$edit->referen->maxlength = 12;
		$edit->referen->rel_id    = 'itcasi';

		$edit->debe = new inputField('Debe <#o#>', 'debe_<#i#>');
		$edit->debe->db_name      = 'debe';
		$edit->debe->css_class    = 'inputnum';
		$edit->debe->rel_id       = 'itcasi';
		$edit->debe->maxlength    = 10;
		$edit->debe->size         = 6;
		$edit->debe->rule         = 'required|positive';
		$edit->debe->autocomplete = false;
		$edit->debe->onkeyup      = 'validaDebe(<#i#>)';

		$edit->haber = new inputField('Haber <#o#>', 'haber_<#i#>');
		$edit->haber->db_name      = 'haber';
		$edit->haber->css_class    = 'inputnum';
		$edit->haber->rel_id       = 'itcasi';
		$edit->haber->maxlength    = 10;
		$edit->haber->size         = 6;
		$edit->haber->rule         = 'required|positive';
		$edit->haber->autocomplete = false;
		$edit->haber->onkeyup      = 'validaHaber(<#i#>)';
		
		$edit->cccosto = new hiddenField('', 'ccosto_<#i#>');
		$edit->cccosto->db_name   = 'ccosto';
		$edit->cccosto->rel_id    = 'itcasi';
		
		$edit->cplaccosto = new hiddenField('', 'cplaccosto_<#i#>');
		$edit->cplaccosto->db_name   = 'cplaccosto';
		$edit->cplaccosto->rel_id    = 'itcasi';
		$edit->cplaccosto->pointer   = true;
		//**************************
		//fin de campos para detalle
		//**************************
		
		$edit->debe = new inputField('Debe', 'debe');
		$edit->debe->css_class ='inputnum';
		$edit->debe->readonly  =true;
		$edit->debe->size      = 10;

		$edit->haber = new inputField('Haber', 'haber');
		$edit->haber->css_class ='inputnum';
		$edit->haber->readonly  =true;
		$edit->haber->size      = 10;

		$edit->total = new inputField('Total', 'total');
		$edit->total->css_class ='inputnum';
		$edit->total->readonly  =true;
		$edit->total->size      = 10;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_casi', $conten,true);
		$data['title']   = heading('Asientos Contables');
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
		$cana=$do->count_rel('itcasi');
		$monto=$debe=$haber=0;
		//Hasta aca en costo trae el valor del ultimo de sinv, se opera para cambiarlo a:
		//costo=costo*(entrada o salida segun se el caso)
		for($i=0;$i<$cana;$i++){
			$adebe=$do->get_rel('itcasi','debe',$i);
			$ahaber=$do->get_rel('itcasi','haber' ,$i);
			if ($adebe!=0 && $ahaber!=0){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='No puede tener debe y haber en el asiento .'.$i+1;
				return false;	
			}
			if ($adebe==0 && $aHaber==0){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe tener debe o haber en el asiento .'.$i+1;
				return false;	
			}
			if($adebe != 0){
				$debe+=$adebe;
			}
			if($ahaber != 0){
				$haber+=$ahaber;
			}
		}
		if ($debe == 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe ingresar al menos un monto en la columna de debe.';
			return false;	
		}
		if ($haber == 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe ingresar al menos un monto en la columna de haber.';
			return false;	
		}
		
		$numero =$this->datasis->fprox_numero('ncasi');
		$transac=$this->datasis->fprox_numero('ntransa');
		$usuario=$do->get('usuario');
		$estampa=date('Ymd');
		$hora   =date("H:i:s");
		
		$do->set('estampa',$estampa);
		$do->set('hora'   ,$hora);
		$do->set('transac',$transac);
		$do->set('origen','MANUAl');
		
		return true;
	}

	function _pre_update($do){
		return false;
	}

	function _post_insert($do){
		//trafrac ittrafrac
		$codigo=$do->get('comprob');
		logusu('casi',"asiento $codigo CREADO");
	}

	function _post_delete($do){
		$codigo=$do->get('comprob');
		logusu('casi',"asiento $codigo ELIMINADO");
	}
}