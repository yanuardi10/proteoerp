<?php require_once(BASEPATH . 'application/controllers/validaciones.php');
class pfaclite extends validaciones{
	var $genesal=true;

	function pfaclite(){
		parent :: Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(143,1);
	}

	function index(){
		redirect('ventas/pfaclite/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datagrid', 'datafilter');

		$atts = array(
			'width'      => '800',
			'height'     => '600',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '0',
			'screeny'    => '0'
		);

		$atts2 = array(
			'width'      => '480',
			'height'     => '240',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '980',
			'screeny'    => '760'
		);

		$scli = array(
			'tabla' => 'scli',
			'columnas' => array(
				'cliente' => 'C&oacute;digo Cliente',
				'nombre' => 'Nombre',
				'contacto' => 'Contacto'),
			'filtro' => array('cliente' => 'C&oacute;digo Cliente', 'nombre' => 'Nombre'),
			'retornar' => array('cliente' => 'cod_cli'),
			'titulo' => 'Buscar Cliente');

		$boton = $this->datasis->modbus($scli);

		$filter = new DataFilter('Filtro de Pedidos Clientes', 'pfac');

		$filter->fechad = new dateonlyField('Desde', 'fechad');
		$filter->fechah = new dateonlyField('Hasta', 'fechah');
		$filter->fechad->clause = $filter->fechah->clause   = 'where';
		$filter->fechad->db_name = $filter->fechah->db_name = 'fecha';
		//$filter->fechad->insertValue = date('Y-m-d');
		//$filter->fechah->insertValue = date('Y-m-d');
		$filter->fechah->size = $filter->fechad->size = 10;
		$filter->fechad->operator = '>=';
		$filter->fechah->operator = '<=';
		$filter->fechad->group = "uno";
		$filter->fechah->group = "uno";

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 10;
		$filter->numero->group = "dos";

		$filter->cliente = new inputField('Cliente', 'cod_cli');
		$filter->cliente->size = 8;
		$filter->cliente->append($boton);
		$filter->cliente->group = "dos";

		$filter->buttons('reset', 'search');
		$filter->build('dataformfiltro');

		$uri = anchor('ventas/pfaclite/dataedit/show/<#id#>', '<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/PFAC/<#numero#>', 'Ver HTML', $atts);
		$uri3 = anchor_popup('ventas/sfac/creadpfacf/<#numero#>', 'Facturar', $atts2);

		$grid = new DataGrid('');
		$grid->order_by('numero', 'desc');
		$grid->per_page = 50;

		//$grid->column('Vista'    , $uri2, "align='center'");
		$grid->column_orderby('N&uacute;mero', $uri ,'numero');
		$grid->column_orderby("Fecha"        , '<dbdate_to_human><#fecha#></dbdate_to_human>','fecha', "align='center'");
		$grid->column_orderby("Cliente"      , 'cod_cli','cod_cli');
		$grid->column_orderby("Nombre"       , 'nombre','nombre');
		$grid->column_orderby('Total'        , '<nformat><#totalg#></nformat>', "totalg", "align=right");

		$grid->add('ventas/pfaclite/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['filtro']  = '';

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Pedidos Clientes');
		$this->load->view('view_ventanas', $data);
	}

	function dataedit($status='',$id=''){

		$this->rapyd->load('dataobject', 'datadetails');
		$this->load->helper('form');

		$do = new DataObject('pfac');
		$do->rel_one_to_many('itpfac', 'itpfac', array('numero' => 'numa'));
		$do->pointer('scli' , 'scli.cliente=pfac.cod_cli', 'scli.tipo AS sclitipo', 'left');
		$do->rel_pointer('itpfac', 'sinv', 'itpfac.codigoa=sinv.codigo', 'sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo,sinv.precio1 As sinvprecio1,sinv.pond AS sinvpond,sinv.mmargen as sinvmmargen,sinv.ultimo sinvultimo,sinv.formcal sinvformcal,sinv.pm sinvpm,sinv.existen pexisten,sinv.marca pmarca,sinv.descrip pdesca,sinv.peso ppeso');
		$do->order_by('itpfac','sinv.marca',' ');
		$do->order_by('itpfac','sinv.descrip',' ');
		

		$edit = new DataDetails('Pedidos', $do);
		$edit->back_url = site_url('ventas/pfaclite/filteredgrid');
		$edit->set_rel_title('itpfac', 'Producto <#o#>');

		$edit->pre_process('insert' , '_pre_insert');
		$edit->pre_process('update' , '_pre_insert');
		$edit->pre_process('delete' , '_pre_delete');
		$edit->post_process('insert', '_post_insert');
		$edit->post_process('update', '_post_update');
		$edit->post_process('delete', '_post_delete');

		$fenvia  =strtotime($edit->get_from_dataobjetct('fenvia'));
		$faplica =strtotime($edit->get_from_dataobjetct('faplica'));
		$hoy     =strtotime(date('Y-m-d'));

		$edit->fecha = new inputField('Fecha', 'fecha');
		$edit->fecha->insertValue = date('Y-m-d');
		//$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->mmargen = new inputField('mmargen', 'mmargen');

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode = 'autohide';
		$edit->numero->maxlength = 8;
		$edit->numero->apply_rules = false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when = array('show', 'modify');

		$usr  =$this->session->userdata('usuario');
		$vd   =$this->datasis->damerow("SELECT vendedor,almacen FROM usuario WHERE us_codigo='$usr'");
		$edit->cliente = new dropdownField('CLIENTE', 'cod_cli');
		$edit->cliente->options("SELECT cliente, CONCAT(' (',cliente,') ', nombre) FROM scli WHERE vendedor='".$vd['vendedor']."'  ORDER  BY nombre");//
		
		$edit->observa = new inputField('Observaciones', 'observa');
		$edit->observa->size = 25;

		// Campos para el detalle
		$edit->codigoa = new inputField('C&oacute;digo <#o#>', 'codigoa_<#i#>');
		$edit->codigoa->size = 12;
		$edit->codigoa->db_name = 'codigoa';
		$edit->codigoa->rel_id = 'itpfac';
		$edit->codigoa->rule = 'callback_chcodigoa';
		//$edit->codigoa->onkeyup = 'OnEnter(event,<#i#>)';
		$edit->codigoa->type='inputhidden';

		$edit->pdesca = new inputField('Descripci&oacute;n <#o#>', 'pdesca_<#i#>');
		$edit->pdesca->size = 32;
		$edit->pdesca->db_name = 'pdesca';
		$edit->pdesca->maxlength = 50;
		$edit->pdesca->readonly = true;
		$edit->pdesca->rel_id = 'itpfac';
		$edit->pdesca->type='inputhidden';
		$edit->pdesca->pointer=true;
		
		$edit->ppeso = new inputField('Peso <#o#>', 'ppeso_<#i#>');
		$edit->ppeso->size    = 10;
		$edit->ppeso->db_name = 'ppeso';
		$edit->ppeso->rel_id  = 'itpfac';
		$edit->ppeso->pointer =true;
		$edit->ppeso->type    ='inputhidden';
				
		$edit->pexisten = new inputField('Existencia <#o#>', 'pexisten_<#i#>');
		$edit->pexisten->size    = 10;
		$edit->pexisten->db_name = 'pexisten';
		$edit->pexisten->rel_id  = 'itpfac';
		$edit->pexisten->pointer =true;
		$edit->pexisten->type='inputhidden';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name = 'cana';
		$edit->cana->css_class = 'inputnum';
		$edit->cana->rel_id = 'itpfac';
		$edit->cana->maxlength = 10;
		$edit->cana->size = 2;
		//$edit->cana->rule = 'required|positive';
		$edit->cana->autocomplete = false;
		$edit->cana->onkeyup = 'total(<#i#>)';
		//$edit->cana->insertValue=1;
		$edit->cana->style ="height:25px;font-size:14";

		$edit->preca = new dropdownField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name   = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id    = 'itpfac';
		$edit->preca->rule      = 'positive|callback_chpreca[<#i#>]';
//		$edit->preca->readonly = true;

		$edit->precat = new hiddenField('', 'precat_<#i#>');
		$edit->precat->db_name = 'precat';
		$edit->precat->rel_id  = 'itpfac';
		$edit->precat->pointer = true;
		
		$edit->pmarca = new inputField('', 'pmarca_<#i#>');
		$edit->pmarca->db_name = 'pmarca';
		$edit->pmarca->rel_id  = 'itpfac';
		$edit->pmarca->pointer = true;
		// fin de campos para detalle

		$edit->totalg = new hiddenField('Monto Total', 'totalg');
		$edit->totalg->css_class = 'inputnum';
		$edit->totalg->readonly = true;
		$edit->totalg->size = 10;

		$edit->usuario = new autoUpdateField('usuario', $this->session->userdata('usuario'), $this->session->userdata('usuario'));
		
		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));

		$control=$this->rapyd->uri->get_edited_id();

		$edit->buttons('add');
		if($fenvia < $hoy){
			$edit->buttons('modify', 'save', 'delete', 'undo', 'back','add_rel');
			if($PFACRESERVA=$this->datasis->traevalor('PFACRESERVA','indica si un pedido descuenta de inventario los producto')=='S'){
				$accion="javascript:window.location='".site_url('ventas/pfaclite/reserva/'.$control)."/pfaclite'";
				$edit->button_status('btn_envia'  ,'Enviar Pedido'         ,$accion,'TR','show');
			}
		}else{
			$edit->buttons('save', 'undo', 'back', 'add_rel');
		}
		
		$accion="javascript:window.location='".site_url('ventas/pfaclite/load')."'";
		$edit->button_status('btn_load'  ,'Subir desde Excel' ,$accion,'TL','show');
		$edit->button_status('btn_load'  ,'Subir desde Excel' ,$accion,'TL','create');
		$edit->button_status('btn_load'  ,'Subir desde Excel' ,$accion,'TL','modify');

		
		$q="AND b.alma='".$vd['almacen']."'";
		$sinv=$this->db->query("SELECT a.codigo,descrip,precio1,precio2,precio3,precio4,marca,SUM(b.existen) existen,iva ,peso
		FROM sinv a 
		JOIN itsinv b ON a.codigo=b.codigo 
		WHERE activo='S' AND tipo='Articulo' ".(strlen($vd['vendedor'])>0?$q:'')."
		GROUP BY a.codigo 
		ORDER BY marca,descrip,peso");
		
		$sinv=$sinv->result_array();
		$sinv2=array();
		$sinviva=array();
		foreach($sinv as $k=>$v){
			$sinv2[$v['codigo']]=$v;
			$sinviva['_'.$v['codigo']]=array('codigo'=>$v['codigo'],'iva'=>$v['iva']);
		}
		
		if($this->genesal){
			$edit->build();

			$conten['form']    = & $edit;
			$conten['hoy']     = $hoy;
			$conten['fenvia']  = $fenvia;
			$conten['faplica'] = $faplica;
			$conten['sinv']    = $sinv2;
			$conten['sinviva'] = json_encode($sinviva);
			$data['content']   =$this->load->view('view_pfaclite', $conten,true);
			$data['title']     = heading('Pedidos No. '.$edit->numero->value);
			$this->load->view('view_ventanas_lite', $data);
		}else{
			$edit->on_save_redirect=false;
			$edit->build();
			
			if($edit->on_success()){
				echo 'Pedido Guardado';
			}elseif($edit->on_error()){
				echo html_entity_decode(preg_replace('/<[^>]*>/', '', $edit->error_string));
			}elseif($edit->on_show()){
				print_r($edit->dataobject->get_all());
			}else{
				
			}
		}
	}
	
	
	
	function _pre_insert($do){
		$numero=$do->get('numero');
		if(empty($numero)){
			$numero = $this->datasis->fprox_numero('npfac');
			$do->set('numero', $numero);
			$ntransac = $this->datasis->fprox_numero('transac');
			$do->set('transac', $ntransac);
			$fecha = date('%Y%m%d');
		}else{
			$fecha=$do->get('fecha');
		}
		
		$cod_cli=$do->get('cod_cli');
		$scli   =$this->datasis->damerow("SELECT rifci,nombre,CONCAT(dire11,' ',dire12) direc,CONCAT(dire21,' ',dire22) dire1 FROM scli WHERE cliente='$cod_cli'");
		$do->set('rifci' ,$scli['rifci'] );
		$do->set('nombre',$scli['nombre']);
		$do->set('direc' ,$scli['direc'] );
		$do->set('dire1' ,$scli['dire1'] );
		
		$usr=$this->session->userdata('usuario');
		$vd=$this->datasis->dameval("SELECT vendedor FROM usuario WHERE us_codigo='$usr'");
		$do->set('vd',$vd);
		$sinv=$this->db->query("SELECT codigo,iva,precio1 FROM sinv ORDER BY marca");
		$sinv=$sinv->result_array();
		$sinv2=array();
		foreach($sinv as $k=>$v){
			$sinv2[$v['codigo']]=$v;
		}

		$iva = $totals = 0;
		$borrar=array();
		for($i = 0;$i < $do->count_rel('itpfac');$i++){
			$itcana  = $do->get_rel('itpfac', 'cana', $i);
			if($itcana>0){
				$itpreca = $do->get_rel('itpfac', 'preca', $i);
				$itiva   = $sinv2[$do->get_rel('itpfac', 'codigoa', $i)]['iva'];
				$ittota  = $itpreca * $itcana;
				$do->set_rel('itpfac', 'tota' , $ittota, $i);
				$do->set_rel('itpfac', 'fecha' , $fecha , $i);
				$do->set_rel('itpfac', 'vendedor', $vd , $i);

				$iva    += $ittota * ($itiva / 100);
				$totals += $ittota;
				$do->set_rel('itpfac', 'mostrado', $iva + $ittota, $i);
			}else{
				$borrar[$i]=$i;
			}
		}
		$borrar=array_reverse($borrar,true);
		foreach($borrar AS $value){
			array_splice($do->data_rel['itpfac'],$value,1);
		}		
		$totalg = $totals + $iva;

		$do->set('totals' , round($totals , 2));
		$do->set('totalg' , round($totalg , 2));
		$do->set('iva'    , round($iva    , 2));
		return true;
	}

	function _post_insert($do){
		$cana = $do->count_rel('itpfac');
		for($i = 0;$i < $cana;$i++){
			$itcodigo= $do->get_rel('itpfac', 'codigoa', $i);
			$itcana  = $do->get_rel('itpfac', 'cana', $i);
			$mSQL = "UPDATE sinv SET exdes=exdes+$itcana WHERE codigo=".$this->db->escape($itcodigo);

			$ban=$this->db->simple_query($mSQL);
			if($ban==false){ memowrite($mSQL,'pfac'); }
		}

		$codigo = $do->get('numero');
		logusu('pfac', "Pedido $codigo CREADO");
	}

	function _post_update($do){
		$cana = $do->count_rel('itpfac');
		for($i = 0;$i < $cana;$i++){
			$itcodigo= $do->get_rel('itpfac', 'codigoa', $i);
			$itcana  = $do->get_rel('itpfac', 'cana', $i);
			$mSQL = "UPDATE sinv SET exdes=exdes+$itcana WHERE codigo=".$this->db->escape($itcodigo);

			$ban=$this->db->simple_query($mSQL);
			if($ban==false){ memowrite($mSQL,'pfac'); }
		}
		$codigo = $do->get('numero');
		logusu('pfac', "Pedido $codigo MODIFICADO");
	}

	function _pre_delete($do){
		$codigo = $do->get('numero');
		$mSQL='UPDATE sinv JOIN itpfac ON sinv.codigo=itpfac.codigoa SET sinv.exdes=sinv.exdes-itpfac.cana WHERE itpfac.numa='.$this->db->escape($codigo);
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'pfac'); }
		return true;
	}

	function _post_delete($do){
		$codigo = $do->get('numero');
		logusu('pfac', "Pedido $codigo ELIMINADO");
	}
	
	function load(){
		$this->load->library("path");
		$path=new Path();
		$path->setPath($this->config->item('uploads_dir'));
		$path->append('/archivos');
		$this->upload_path =$path->getPath().'/';
		
		$this->rapyd->load("dataform");
		$form = new DataForm("ventas/pfaclite/read");
		$form->title('Cargar Archivo de Productos (xls)');

		$form->archivo = new uploadField("Archivo","archivo");
		$form->archivo->upload_path   = $this->upload_path;    
		$form->archivo->allowed_types = "xls";
		$form->archivo->delete_file   =false;
		$form->archivo->rule   ="required";

		$form->submit("btnsubmit","Enviar");
		$form->build_form();

		$data['content'] = $form->output;
		$data['title']   = "<h1>Cargar Pedido desde Excel</h1>";
		//$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_lite', $data);
	}

	function read(){
		$this->load->library("Spreadsheet_Excel_Reader");
		$type='';
		if(isset($_FILES['archivoUserFile']['type']))$type=$_FILES['archivoUserFile']['type'];
		//print_r($_FILES);
		if( $type=='application/vnd.ms-excel'){
			$name=$_FILES['archivoUserFile']['name'];
			$dir=".././".$name;
			$name=$_FILES['archivoUserFile']['name'];
			if (copy($_FILES['archivoUserFile']['tmp_name'], 'uploads/'.$name)){
				$uploadsdir =getcwd().'/uploads/';
				$filedir    =$uploadsdir.$name;
				$tmp=$dir;
				$tmp=$filedir;
				//$_FILES['archivoUserFile']['tmp_name'];
				$data = new Spreadsheet_Excel_Reader();
				$data->setOutputEncoding('CP1251');
				$data->read($tmp);
				error_reporting(E_ALL ^ E_NOTICE);
				$cols=array();
				
				foreach($data->sheets AS $sheetk=>$sheetv){
					foreach($sheetv['cells'] AS $rowk=>$rowv){
						$data4[$sheetk][]=$rowv;
					}
				}
				$this->limpia($data4);
			}
		}else{
			echo "El archivo no puede ser leido";
			return "El archivo no puede ser leido";
		}
	}
	 
	function limpia($data){
		$las9=array();
		$lose=array();
		$line=0;
		
		$sinv=$this->db->query("SELECT codigo,descrip,precio1,precio2,precio3,precio4,marca,existen FROM sinv WHERE LENGTH(codigo)>0 ORDER BY marca");
		$sinv=$sinv->result_array();
		$sinv2=array();
		foreach($sinv as $k=>$v){
			$sinv2[$v['codigo']]=$v;
		}
		unset($inv);
		
		foreach($data as $hojak=>$hoja){
			$lose[$hojak]['cod_cli']=$data[$hojak][3][12];
			foreach($hoja as $lineak=>$linea){
				if(array_key_exists($linea[8],$sinv2)>0 && $linea[9]>0 && $linea[6]>0){
					$line++;
					$las9[$hojak][$line][$i=1]=$linea[$i];
					$las9[$hojak][$line][$i=2]=$linea[$i];
					$las9[$hojak][$line][$i=3]=$linea[$i];
					$las9[$hojak][$line][$i=4]=$linea[$i];
					$las9[$hojak][$line][$i=5]=$linea[$i];
					$las9[$hojak][$line][$i=6]=$linea[$i];
					$las9[$hojak][$line][$i=7]=$linea[$i];
					$las9[$hojak][$line][$i=8]=$linea[$i];
					$las9[$hojak][$line][$i=9]=$linea[$i];
				}
				
				if(array_key_exists($linea[18],$sinv2)>0 && $linea[19]>0 && $linea[16]>0){
					$line++;
					$las9[$hojak][$line][$i=11]=$linea[$i];
					$las9[$hojak][$line][$i=12]=$linea[$i];
					$las9[$hojak][$line][$i=13]=$linea[$i];
					$las9[$hojak][$line][$i=14]=$linea[$i];
					$las9[$hojak][$line][$i=15]=$linea[$i];
					$las9[$hojak][$line][$i=16]=$linea[$i];
					$las9[$hojak][$line][$i=17]=$linea[$i];
					$las9[$hojak][$line][$i=18]=$linea[$i];
					$las9[$hojak][$line][$i=19]=$linea[$i];
				}
			}
		}
		
		$i=0;
		$this->genesal=false;
		$error='';
		$usr=$this->session->userdata('usuario');
		
		foreach($lose as $hoja=>$cliente){
			$itpfac         =array();
			$cod_clie       =$this->db->escape($cliente['cod_cli']);
			$scli           =$this->datasis->damerow("SELECT * FROM scli WHERE cliente=$cod_clie");
			$vd             =$this->datasis->dameval("SELECT vendedor FROM usuario WHERE us_codigo='$usr'");
			$pfac['vd']     =$vd;
			$pfac['cod_cli']=$cliente['cod_cli'];
			$pfac['numero'] =$this->datasis->fprox_numero('npfac');	
			$pfac['transac']=$this->datasis->fprox_numero('ntransac');	
			$pfac['direc']  =$scli['dire11'].$scli['dire12'];
			$pfac['dire1']  =$scli['dire21'].$scli['dire22'];
			$pfac['fecha']  =date('Ymd');
			$pfac['nombre'] =$scli['nombre'];
			$pfac['rifci']  =$scli['rifci'];
			$pfac['usuario']=$usr;
			$pfac['estampa']=date('%Y%m%d');
			
			$totals=$iva=0;
			foreach($las9[$hoja] as $linea){
				if(array_key_exists($linea[8],$sinv2)>0 && $linea[9]>0 && $linea[6]>0){
					$itpfac=array(
						'cana'      =>$linea['9'],
						'preca'     =>$linea['6'],
						'codigoa'   =>$linea['8'],
						'iva'       =>$sinv['iva'],
						'tota'      =>round($linea['9']*$linea['6'],2),
						'numa'      =>$pfac['numero'],
						'desca'     =>$sinv2[$linea[8]]['descrip'],
						'usuario'   =>$usr,
						'estampa'   =>date('%Y%m%d')
					);
					$totals +=round($linea['9']*$linea['6'],2);
					$iva    +=round($totals*$sinv['iva']/100,2);
					$this->db->insert('itpfac'  ,$itpfac);
				}
				
				if(array_key_exists($linea[18],$sinv2)>0 && $linea[19]>0 && $linea[16]>0){
					$itpfac=array(
						'cana'      =>$linea['19'],
						'preca'     =>$linea['16'],
						'codigoa'   =>$linea['18'],
						'iva'       =>$sinv['iva'],						
						'tota'      =>round($linea['19']*$linea['16'],2),
						'numa'      =>$pfac['numero'],
						'desca'     =>$sinv2[$linea[8]]['descrip'],
						'usuario'   =>$usr,
						'estampa'   =>date('%Y%m%d')
					);
					$totals +=round($linea['19']*$linea['16'],2);
					$iva    +=round($totals*$sinv['iva']/100,2);
					$this->db->insert('itpfac'  ,$itpfac);
				}
			}
			$totalg +=round($totals+$iva/100,2);
			$pfac['totalg']=$totalg;
			$pfac['totals']=$totals;
			$pfac['iva']   =$iva;
			
			$this->db->insert('pfac',$pfac);
			$id     =$this->db->insert_id();
		}
		echo $error;
	
		if(count($data)>1)
		redirect('ventas/pfaclite/filteredgrid/'.$id);
		else
		redirect('ventas/pfaclite/dataedit/show/'.$id);
	}
	
	function reserva($id,$dir='pfac'){
		$error='';
		$PFACRESERVA=$this->datasis->traevalor('PFACRESERVA','indica si un pedido descuenta de inventario los producto');
		if($PFACRESERVA=='S'){
			$usr=$this->session->userdata('usuario');
			$vd['vendedor']=$this->datasis->dameval("SELECT vendedor FROM usuario WHERE us_codigo='$usr'");
			$vd['almacen'] =$this->datasis->dameval("SELECT almacen FROM usuario WHERE us_codigo='$usr'");
			
			$this->rapyd->load('dataobject');
			$do = new DataObject('pfac');
			$do->rel_one_to_many('itpfac', 'itpfac', array('numero' => 'numa'));
			$do->load($id);
			
			$sinv=$this->db->query("SELECT * FROM sinv");
			$sinv=$sinv->result_array();
			$sinv2=array();
			$sinviva=array();
			foreach($sinv as $k=>$v){
				$sinv2[$v['codigo']]=$v;
			}
			$sinv=$sinv2;
			unset($sinv2);
	
			for($i=0;$i < $do->count_rel('itpfac');$i++){
				$codigoa  = $do->get_rel('itpfac','codigoa'  ,$i);
				$cana     = $do->get_rel('itpfac','cana'     ,$i);
				$preca    = $do->get_rel('itpfac','preca'    ,$i);
				//$existen  =$this->datasis->dameval("SELECT existen FROM itsinv WHERE alma='".$vd['almacen']."' AND codigo='$codigoa'");
				//if($cana>$existen){
				//	$error.="ERROR. La cantidad solicitada(".nformat($cana).") es mayor a la existente (".nformat($existen).") para ($codigoa).</br>";
				//}
				if(round($preca,2)!=round($sinv[$codigoa]['precio1'],2))
				$error.="ERROR. El precio para el producto ($codigoa) cambio. por favor corrijalo ó presione el boton modificar y luego guardar. el sistema los actualizara en ese momento";
			}
			if(empty($error)){
				for($i=0;$i < $do->count_rel('itpfac');$i++){
					$codigoa  = $do->get_rel('itpfac','codigoa'  ,$i);
					$cana     = $do->get_rel('itpfac','cana'     ,$i);
					$this->datasis->sinvcarga( $codigoa, $vd['almacen'], -1*$cana);
					$this->datasis->sinvcarga( $codigoa, 'PEDI', $cana);
				}
			}
			$fenvia=date("Ymd");
			$do->set('reserva','S');
			$do->set('fenvia' ,$fenvia);
		}
		if(empty($error)){
			$do->save();
			logusu('pfaclite',"Reservo pedido $id");
			redirect("ventas/$dir/dataedit/show/$id");
		}else{
			$error="<div class='alert'>$error</div>";
			logusu('pfaclite',"Reservo pedido $id. con ERROR:$error ");
			$data['content'] = $error.anchor("ventas/pfaclite/dataedit/show/$id",'Regresar');
			$data['title']   = " Pedidos ";
			$data["head"]    = $this->rapyd->get_head();
			$this->load->view('view_ventanas', $data);
		}
	}
	
	function instalar(){
		$query="ALTER TABLE `pfac`  ADD COLUMN `id` INT NULL AUTO_INCREMENT AFTER `fenvia`,
		  ADD PRIMARY KEY (`id`),  ADD UNIQUE INDEX `numero` (`numero`)";
		$this->db->simple_query($query);
	}
}
