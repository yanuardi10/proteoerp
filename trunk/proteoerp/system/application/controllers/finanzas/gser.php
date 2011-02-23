<?php include('common.php');
class gser extends Controller {

	function gser(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->mcred='_CR';
		//$this->datasis->modulo_id(604,1);
	}

	function index() {
		redirect('finanzas/gser/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Gastos','gser');
		//$filter->db->select("numero,fecha,vence,nombre,totiva,totneto");
		//$filter->db->from('gser');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');

		$filter->proveed = new inputField('Proveedor', 'proveed');
		//$filter->proveed->append($boton);
		$filter->proveed->db_name = 'proveed';

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('finanzas/gser/mgserdataedit/modify/<#id#>','<#numero#>');

		$grid = new DataGrid();
		$grid->order_by('fecha','desc');
		$grid->per_page = 15;
		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha' ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Fecha' ,'<dbdate_to_human><#vence#></dbdate_to_human>','vence','align=\'center\'');
		$grid->column_orderby('Nombre','nombre'  ,'nombre');
		$grid->column_orderby('IVA'   ,'totiva'  ,'totiva' ,'align=\'right\'');
		$grid->column_orderby('monto' ,'totneto' ,'totneto','align=\'right\'');

		$grid->add('finanzas/gser/agregar');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Gastos');
		$this->load->view('view_ventanas', $data);
	}

	function agregar(){
		$data['content'] = '<table align="center">'.br();

		$data['content'].= '<tr><td><img src="'.base_url().'images/dinero.jpg'.'" height="100px"></td><td>';
		$data['content'].= '<p></p>';
		$data['content'].= anchor('finanzas/gser/gserchi'  ,'Ingresar/eliminar/modifcar facturas a caja Chica').br();

		$data['content'].= '<tr><td><img src="'.base_url().'images/dinero.jpg'.'" height="100px"></td><td>';
		$data['content'].= '<p></p>';
		$data['content'].= anchor('finanzas/gser/cierregserchi'  ,'Cerrar Caja Chica').br();

		$data['content'].= '</td></tr><tr><td colspan=2 align="center">'.anchor('finanzas/gser/index'        ,'Regresar').br();
		$data['content'].= '</td></tr></table>'.br();

		$data['title']   = heading('Selecciona la operaci&oacute;n que desea realizar');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	//Para Caja chica
	function gserchi(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de gastos de cajas chicas','gserchi');
		$select=array('numfac','fechafac','proveedor','tasa + sobretasa + reducida AS totiva','montasa + monadic + monredu AS totneto');
		$filter->db->select($select);

		$filter->codbanc = new dropdownField('C&oacute;digo de la caja','codbanc');
		$filter->codbanc->option('','Todos');
		$filter->codbanc->options("SELECT codbanc, CONCAT_WS('-',codbanc,banco) AS label FROM banc WHERE tbanco='CAJ' ORDER BY codbanc");

		$filter->fechad = new dateonlyField('Fecha desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Fecha hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  = $filter->fechah->clause ='where';
		$filter->fechad->db_name = $filter->fechah->db_name='fechafac';
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('N&uacute;mero', 'numfac');

		$filter->proveed = new inputField('Proveedor', 'proveedor');
		//$filter->proveed->append($boton);
		$filter->proveed->db_name = 'proveedor';

		//$action = "javascript:window.location='".site_url('finanzas/gser/gserchipros')."'";
		//$filter->button('btn_pross', 'Procesar gatos', $action, 'TR');

		$action = "javascript:window.location='".site_url('finanzas/gser/agregar')."'";
		$filter->button('btn_regresa', 'Regresar', $action, 'TR');

		$filter->buttons('reset','search');
		$filter->build();

		$uri  = anchor('finanzas/gser/datagserchi/show/<#id#>','<#numfac#>');

		$grid = new DataGrid();
		$grid->order_by('numfac','desc');
		$grid->per_page = 15;
		$grid->column_orderby('Caja','codbanc','caja');
		$grid->column_orderby('N&uacute;mero',$uri,'numfac');
		$grid->column_orderby('Fecha' ,'<dbdate_to_human><#fechafac#></dbdate_to_human>','fechafac','align=\'center\'');
		$grid->column_orderby('Proveedor','proveedor','proveedor');
		$grid->column_orderby('IVA'   ,'totiva'  ,'totiva' ,'align=\'right\'');
		$grid->column_orderby('Monto' ,'totneto' ,'totneto','align=\'right\'');

		$grid->add('finanzas/gser/datagserchi/create','Agregar nueva factura');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Agregar/Modificar facturas de Caja Chica');
		$this->load->view('view_ventanas', $data);
	}

	function datagserchi(){
		$this->rapyd->load('dataedit');
		$mgas=array(
			'tabla'   => 'mgas',
			'columnas'=> array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n','tipo'=>'Tipo'),
			'filtro'  => array('descrip'=>'Descripci&oacute;n'),
			'retornar'=> array('codigo' =>'codigo','descrip'=>'descrip'),
			'titulo'  => 'Buscar enlace administrativo');
		$bcodigo=$this->datasis->modbus($mgas);

		$ivas=$this->datasis->ivaplica();

		$tasa      = $ivas['tasa']/100;
		$redutasa  = $ivas['redutasa']/100;
		$sobretasa = $ivas['sobretasa']/100;

		$consulrif=$this->datasis->traevalor('CONSULRIF');
		$script="
		function consulrif(){
			vrif=$('#rif').val();
			if(vrif.length==0){
				alert('Debe introducir primero un RIF');
			}else{
				vrif=vrif.toUpperCase();
				$('#rif').val(vrif);
				window.open('$consulrif'+'?p_rif='+vrif,'CONSULRIF','height=350,width=410');
			}
		}

		function poneiva(tipo){
			if(tipo==1){
				ptasa = $redutasa;
				campo = 'reducida';
				monto = 'monredu';
			} else if (tipo==3){
				ptasa = $sobretasa;
				campo = 'sobretasa';
				monto = 'monadic'
			} else {
				ptasa = $tasa;
				campo = 'tasa';
				monto = 'montasa';
			}
			if($('#'+monto).val().length>0)  base=parseFloat($('#'+monto).val());   else  base  =0;
			$('#'+campo).val(roundNumber(base*ptasa,2));
			totaliza();
		}

		function totaliza(){
			if($('#montasa').val().length>0)   montasa  =parseFloat($('#montasa').val());   else  montasa  =0;
			if($('#tasa').val().length>0)      tasa     =parseFloat($('#tasa').val());      else  tasa     =0;
			if($('#monredu').val().length>0)   monredu  =parseFloat($('#monredu').val());   else  monredu  =0;
			if($('#reducida').val().length>0)  reducida =parseFloat($('#reducida').val());  else  reducida =0;
			if($('#monadic').val().length>0)   monadic  =parseFloat($('#monadic').val());   else  monadic  =0;
			if($('#sobretasa').val().length>0) sobretasa=parseFloat($('#sobretasa').val()); else  sobretasa=0;
			if($('#exento').val().length>0)    exento   =parseFloat($('#exento').val());    else  exento   =0;

			total=roundNumber(montasa+tasa+monredu+reducida+monadic+sobretasa+exento,2);
			$('#importe').val(total);
		}";

		$edit = new DataEdit('Gastos de caja chica', 'gserchi');
		$edit->back_url = site_url('finanzas/gser/gserchi');
		$edit->script($script,'create');
		$edit->script($script,'modify');
		$edit->pre_process('insert' ,'_pre_gserchi');
		$edit->pre_process('update' ,'_pre_gserchi');

		$edit->codbanc = new dropdownField('C&oacute;digo de la caja','codbanc');
		$edit->codbanc->option('','Seleccionar');
		$edit->codbanc->options("SELECT codbanc, CONCAT_WS('-',codbanc,banco) AS label FROM banc WHERE tbanco='CAJ' ORDER BY codbanc");
		$edit->codbanc->rule='max_length[5]|required';

		$edit->fechafac = new dateField('Fecha de la factura','fechafac');
		$edit->fechafac->rule='max_length[10]|required';
		$edit->fechafac->size =12;
		$edit->fechafac->insertValue=date('Y-m-d');
		$edit->fechafac->maxlength =10;

		$edit->numfac = new inputField('N&uacute;mero de la factura','numfac');
		$edit->numfac->rule='max_length[8]|required';
		$edit->numfac->size =10;
		$edit->numfac->maxlength =8;

		$edit->nfiscal = new inputField('Control fiscal','nfiscal');
		$edit->nfiscal->rule='max_length[12]|required';
		$edit->nfiscal->size =14;
		$edit->nfiscal->maxlength =12;

		$lriffis='<a href="javascript:consulrif();" title="Consultar RIF en el SENIAT" onclick="">Consultar RIF en el SENIAT</a>';
		$edit->rif = new inputField('RIF','rif');
		$edit->rif->rule='max_length[13]|required';
		$edit->rif->size =13;
		$edit->rif->maxlength =13;
		$edit->rif->group='Datos del proveedor';
		$edit->rif->append(HTML::button('traesprv', 'Consultar Proveedor', '', 'button', 'button'));
		$edit->rif->append($lriffis);

		$edit->proveedor = new inputField('Nombre del proveedor','proveedor');
		$edit->proveedor->rule='max_length[40]|strtoupper';
		$edit->proveedor->size =40;
		$edit->proveedor->group='Datos del proveedor';
		$edit->proveedor->maxlength =40;

		$edit->codigo = new inputField('C&oacute;digo del gasto','codigo');
		$edit->codigo->rule ='max_length[6]|required';
		$edit->codigo->size =6;
		$edit->codigo->maxlength =8;
		$edit->codigo->append($bcodigo);

		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		$edit->descrip->rule='max_length[50]|strtoupper';
		$edit->descrip->size =50;
		$edit->descrip->maxlength =50;

		$arr=array(
			'exento'   =>'Monto <b>Exento</b>|Base exenta',
			'montasa'  =>'Montos con Alicuota <b>general</b>|Base imponible',
			'tasa'     =>'Montos con Alicuota <b>general</b>|Monto del IVA',
			'monredu'  =>'Montos con Alicuota <b>reducida</b>|Base imponible',
			'reducida' =>'Montos con Alicuota <b>reducida</b>|Monto del IVA',
			'monadic'  =>'Montos con Alicuota <b>adicional</b>|Base imponible',
			'sobretasa'=>'Montos con Alicuota <b>adicional</b>|Monto del IVA',
			'importe'  =>'Importe total');

		foreach($arr AS $obj=>$label){
			$pos = strrpos($label, '|');
			if($pos!==false){
				$piv=explode('|',$label);
				$label=$piv[1];
				$grupo=$piv[0];
			}else{
				$grupo='';
			}

			$edit->$obj = new inputField($label,$obj);
			$edit->$obj->rule='max_length[17]|numeric';
			$edit->$obj->css_class='inputnum';
			$edit->$obj->size =17;
			$edit->$obj->maxlength =17;
			$edit->$obj->group=$grupo;
			$edit->$obj->autocomplete=false;
		}
		$edit->$obj->readonly=true;

		/*$edit->montasa->rule='max_length[17]|numeric|callback_chmontasa';
		$edit->monredu->rule='max_length[17]|numeric|callback_chmonredu';
		$edit->monadic->rule='max_length[17]|numeric|callback_chmonadic';*/

		$edit->tasa->rule     ='max_length[17]|numeric|condi_required|callback_chtasa';
		$edit->reducida->rule ='max_length[17]|numeric|condi_required|callback_chreducida';
		$edit->sobretasa->rule='max_length[17]|numeric|condi_required|callback_chsobretasa';

		$edit->sucursal = new dropdownField('Sucursal','sucursal');
		$edit->sucursal->options('SELECT codigo,sucursal FROM sucu ORDER BY sucursal');
		$edit->sucursal->rule='max_length[2]|required';

		$edit->departa = new dropdownField('Departamento','departa');
		$edit->departa->options("SELECT depto, CONCAT_WS('-',depto,descrip) AS label FROM dpto WHERE tipo='G' ORDER BY depto");
		$edit->departa->rule='max_length[2]';

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->estampa = new autoUpdateField('estampa' ,date('YmD'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:m:s'), date('H:m:s'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$url=site_url('finanzas/gser/ajaxsprv');
		//$this->rapyd->jquery[]='$(".inputnum").bind("keyup",function() { totaliza(); })';
		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$this->rapyd->jquery[]='$("#exento"   ).bind("keyup",function() { totaliza(); })';
		$this->rapyd->jquery[]='$("#montasa"  ).bind("keyup",function() { poneiva(2); })';
		$this->rapyd->jquery[]='$("#tasa"     ).bind("keyup",function() { totaliza(); })';
		$this->rapyd->jquery[]='$("#monredu"  ).bind("keyup",function() { poneiva(1); })';
		$this->rapyd->jquery[]='$("#reducida" ).bind("keyup",function() { totaliza(); })';
		$this->rapyd->jquery[]='$("#monadic"  ).bind("keyup",function() { poneiva(3); })';
		$this->rapyd->jquery[]='$("#sobretasa").bind("keyup",function() { totaliza(); })';

		$this->rapyd->jquery[]='$("input[name=\'traesprv\']").click(function() {
			rif=$("#rif").val();
			if(rif.length > 0){
				$.post("'.$url.'", { rif: rif },function(data){
					$("#proveedor").val(data);
				});
			}else{
				alert("Debe introducir un rif");
			}
		});';


		$data['content'] = $edit->output;
		$data['title']   = heading('Agregar/Modificar facturas de Caja Chica');
		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_gserchi($do){
		$rif   =$do->get('rif');
		$dbrif = $this->db->escape($rif);
		$nombre=$do->get('proveedor');
		$fecha =date('Y-m-d');
		$csprv =$this->datasis->dameval('SELECT COUNT(*) FROM sprv WHERE rif='.$dbrif);
		if($csprv==0){
			$mSQL ='INSERT IGNORE INTO provoca (rif,nombre,fecha) VALUES ('.$dbrif.','.$this->db->escape($nombre).','.$this->db->escape($fecha).')';
			$this->db->simple_query($mSQL);
		}

		$total  = 0;
		$total += $do->get('exento')   ;
		$total += $do->get('montasa')  ;
		$total += $do->get('tasa')     ;
		$total += $do->get('monredu')  ;
		$total += $do->get('reducida') ;
		$total += $do->get('monadic')  ;
		$total += $do->get('sobretasa');

		if($total>0){
			$do->set('importe',$total);
			return true;
		}else{
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['pre_upd'] = 'No se puede guardar un gasto con monto cero';
			return false;
		}
	}

	//Para Caja chica
	function cierregserchi(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$uri  = anchor('finanzas/gser/gserchipros/<#codbanc#>','<#codbanc#>');

		$grid = new DataGrid('Lista de cajas chicas para cerrar');
		$select=array('MAX(fechafac) AS fdesde',
					  'MIN(fechafac) AS fhasta',
					  'SUM(tasa+sobretasa+reducida) AS totiva',
					  'SUM(montasa+monadic+monredu+tasa+sobretasa+reducida) AS total',
					  'TRIM(codbanc) AS codbanc',
					  'COUNT(*) AS cana');
		$grid->db->select($select);
		$grid->db->from('gserchi');
		$grid->db->groupby('codbanc');

		$grid->order_by('codbanc','desc');
		$grid->per_page = 15;
		$grid->column_orderby('Caja',$uri,'codbanc');
		$grid->column('N.facturas','cana','align=\'center\'');
		$grid->column_orderby('Fecha inicial','<dbdate_to_human><#fdesde#></dbdate_to_human>','fdesde','align=\'center\'');
		$grid->column_orderby('Fecha final'  ,'<dbdate_to_human><#fhasta#></dbdate_to_human>','fdesde','align=\'center\'');
		$grid->column_orderby('IVA'   ,'<nformat><#totiva#></nformat>'  ,'totiva' ,'align=\'right\'');
		$grid->column_orderby('Monto' ,'<nformat><#total#></nformat>' ,'total','align=\'right\'');

		$action = "javascript:window.location='".site_url('finanzas/gser/agregar')."'";
		$grid->button('btn_regresa', 'Regresar', $action, 'TR');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Agregar/Modificar facturas de Caja Chica');
		$this->load->view('view_ventanas', $data);
	}

	//Convierte los gastos en caja chica
	function gserchipros($codbanc=null){
		if(empty($codbanc)) show_error('Faltan par&aacute;metros');
		$dbcodbanc=$this->db->escape($codbanc);
		$mSQL="SELECT a.codprv, b.nombre FROM banc AS a JOIN sprv AS b ON a.codprv=b.proveed WHERE a.codbanc=$dbcodbanc";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$row    = $query->row(); 
			$nombre = $row->nombre;
			$codprv = $row->codprv;
		}else{
			$nombre =$codprv = '';
		}

		$this->rapyd->load('dataform');

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'  =>'Nombre',
				'rif'     =>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'codprv','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor'
		);
		$bsprv=$this->datasis->modbus($modbus);

		$form = new DataForm('finanzas/gser/gserchipros/process');

		/*$form->codbanc = new dropdownField('Caja chica o fondo','codbanc');
		$form->codbanc->option('','Seleccionar');
		$form->codbanc->options("SELECT codbanc, CONCAT_WS('-',codbanc,banco) AS label FROM banc WHERE tbanco='CAJ' ORDER BY codbanc");
		$form->codbanc->rule='max_length[5]|required';*/

		$form->codprv = new inputField('Proveedor', 'codprv');
		$form->codprv->rule='required';
		$form->codprv->insertValue=$codprv;
		$form->codprv->size=5;
		$form->codprv->append($bsprv);

		$form->nombre = new inputField('Nombre', 'nombre');
		$form->nombre->rule='required';
		$form->nombre->insertValue=$nombre;
		$form->nombre->in = 'codprv';

		$form->cargo = new dropdownField('Con cargo a','cargo');
		$form->cargo->option($this->mcred,'Cr&eacute;dito');
		$form->cargo->options("SELECT codbanc, CONCAT_WS('-',codbanc,banco) AS label FROM banc WHERE activo='S' AND codbanc<>$dbcodbanc ORDER BY codbanc");
		$form->cargo->rule='max_length[5]|required';

		$form->cheque = new inputField('N&uacute;mero de cheque', 'cheque');
		$form->cheque->rule='condi_required|callback_chobligaban';
		$form->cheque->append('Aplica  solo si la caja es un banco');

		$form->benefi = new inputField('Beneficiario', 'benefi');
		$form->benefi->insertValue=$nombre;
		$form->benefi->rule='condi_required|callback_chobligaban';
		$form->benefi->append('Aplica  solo si la caja es un banco');

		$action = "javascript:window.location='".site_url('finanzas/gser/cierregserchi/'.$codbanc)."'";
		$form->button('btn_regresa', 'Regresar', $action, 'BR');

		$form->submit('btnsubmit','Procesar');
		$form->build_form();

		if($form->on_success()){
			$codprv  = $form->codprv->newValue;
			$cargo   = $form->cargo->newValue;
			$nombre  = $form->nombre->newValue;
			$benefi  = $form->benefi->newValue;
			$cheque  = $form->cheque->newValue;

			$rt=$this->_gserchipros($codbanc,$cargo,$codprv,$benefi,$cheque);
			if($rt){
				redirect('finanzas/gser/listo/n');
			}else{
				redirect('finanzas/gser/listo/s');
			}
		}

		$data['content'] = $form->output;
		$data['title']   = heading('Agregar/Modificar Gasto de caja chica');
		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function chobligaban($val){
		$ban=$this->input->post('cargo');
		if($ban==$this->mcred) return true;
		$tipo=common::_traetipo($ban);
		if($tipo!='CAJ'){
			if(empty($val)){
				$this->validation->set_message('chobligaban', 'El campo %s es obligatorio cuando el caja es un banco');
				return false;
			}
		}
		return true;
	}

	function _gserchipros($codbanc,$cargo,$codprv,$benefi,$numeroch=null){
			$dbcodprv = $this->db->escape($codprv);
			$numeroch = str_pad($numeroch, 12, '0', STR_PAD_LEFT);
			$nombre   = $this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$dbcodprv);
			$fecha    = date('Y-m-d');
			$sp_fecha = str_replace('-','',$fecha);
			$error    = 0;
			$cr=$this->mcred;//Marca para el credito

			$sql  = 'SELECT tbanco FROM banc WHERE codbanc='.$this->db->escape($codbanc);
			$tipo = $this->datasis->dameval($sql);

			$cheque = ($tipo=='CAJ')? $this->datasis->banprox($codbanc): $numeroch ;

			$mSQL='SELECT codbanc,fechafac,numfac,nfiscal,rif,proveedor,codigo,descrip,
			  moneda,montasa,tasa,monredu,reducida,monadic,sobretasa,exento,importe,sucursal,departa,usuario,estampa,hora
			FROM gserchi WHERE ngasto IS NULL AND codbanc';

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$transac  = $this->datasis->fprox_numero('ntransa');
				$numero   = $this->datasis->fprox_numero('ngser');
				$negreso  = $this->datasis->fprox_numero('negreso');

				$montasa=$monredu=$monadic=$tasa=$reducida=$sobretasa=$exento=$totpre=$totiva=0;
				foreach ($query->result() as $row){

					$data = array();
					$data['fecha']      = $fecha;
					$data['numero']     = $numero;
					$data['proveed']    = $codprv;
					$data['codigo']     = $row->codigo;
					$data['descrip']    = $row->descrip;
					$data['precio']     = $row->montasa+$row->monredu+$row->monadic+$row->exento;
					$data['iva']        = $row->tasa+$row->reducida+$row->sobretasa;
					$data['importe']    = $data['precio']+$data['iva'];
					$data['unidades']   = 1;
					$data['fraccion']   = 0;
					$data['almacen']    = '';
					$data['sucursal']   = $row->sucursal;
					$data['departa']    = $row->departa ;
					$data['transac']    = $transac;
					$data['usuario']    = $this->session->userdata('usuario');
					$data['estampa']    = date('Y-m-d');
					$data['hora']       = date('H:i:s');
					$data['huerfano']   = '';
					$data['rif']        = $row->rif      ;
					$data['proveedor']  = $row->proveedor;
					$data['numfac']     = $row->numfac   ;
					$data['fechafac']   = $row->fechafac ;
					$data['nfiscal']    = $row->nfiscal  ;
					$data['feprox']     = '';
					$data['dacum']      = '';
					$data['residual']   = '';
					$data['vidau']      = '';
					$data['montasa']    = $row->montasa  ;
					$data['monredu']    = $row->monredu  ;
					$data['monadic']    = $row->monadic  ;
					$data['tasa']       = $row->tasa     ;
					$data['reducida']   = $row->reducida ;
					$data['sobretasa']  = $row->sobretasa;
					$data['exento']     = $row->exento   ;
					$data['reteica']    = 0;
					//$data['idgser']     = '';

					$sql=$this->db->insert_string('gitser', $data);
					$ban=$this->db->simple_query($sql);
					if($ban==false){ memowrite($sql,'bcaj'); $error++;}

					$montasa  +=$row->montasa  ;
					$monredu  +=$row->monredu  ;
					$monadic  +=$row->monadic  ;
					$tasa     +=$row->tasa     ;
					$reducida +=$row->reducida ;
					$sobretasa+=$row->sobretasa;
					$exento   +=$row->exento   ;
				}
				$totpre = $montasa+$monredu+$monadic+$exento;
				$totiva = $tasa+$reducida+$sobretasa;
				$totneto= $totpre+$totiva;
				
				if($ttipo==$cr){
					$nombre = $this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$this->db->escape($codprv));
					$tipo1  = '';
					$credito= $totneto;
					$causado = $this->datasis->fprox_numero('ncausado');

					$data=array();
					$data['cod_prv']    = $codprv;
					$data['nombre']     = $nombre;
					$data['tipo_doc']   = 'FC';
					$data['numero']     = $numero ;
					$data['fecha']      = $fecha ;
					$data['monto']      = $totneto;
					$data['impuesto']   = $totiva ;
					$data['abonos']     = 0;
					$data['vence']      = $fecha;
					/*$data['tipo_ref']   = '';
					$data['num_ref']    = '';
					$data['observa1']   = '';
					$data['observa2']   = '';
					$data['banco']      = '';
					$data['tipo_op']    = '';
					$data['comprob']    = '';
					$data['numche']     = '';
					$data['codigo']     = '';
					$data['descrip']    = '';
					$data['ppago']      = '';
					$data['nppago']     = '';
					$data['reten']      = '';
					$data['nreten']     = '';
					$data['mora']       = '';
					$data['posdata']    = '';
					$data['benefi']     = '';
					$data['control']    = '';*/
					$data['transac']    = $transac;
					$data['estampa']    = date('Y-m-d');
					$data['hora']       = date('H:i:s');
					$data['usuario']    = $this->session->userdata('usuario');
					//$data['cambio']     ='';
					//$data['pmora']      ='';
					$data['reteiva']    = 0;
					//$data['nfiscal']    ='';
					$data['montasa']    = $montasa;
					$data['monredu']    = $monredu;
					$data['monadic']    = $monadic;
					$data['tasa']       = $tasa;
					$data['reducida']   = $reducida;
					$data['sobretasa']  = $sobretasa;
					$data['exento']     = $exento;
					/*$data['fecdoc']     = '';
					$data['afecta']     = '';
					$data['fecapl']     = '';
					$data['serie']      = '';
					$data['depto']      = '';
					$data['negreso']    = '';
					$data['ndebito']    = '';*/
					$data['causado']    = $causado;

					$sql=$this->db->insert_string('gser', $data);
					$ban=$this->db->simple_query($sql);
					if($ban==false){ memowrite($sql,'bcaj'); $error++;}
				}else{
					$ttipo  = common::_traetipo($cargo);
					$tipo1  = ($ttipo=='CAJ') ? 'ND': 'CH';
					$credito= 0;
				}
				

				$data = array();
				$data['fecha']      = $fecha;
				$data['numero']     = $numero;
				$data['proveed']    = $codprv;
				$data['nombre']     = $nombre;
				$data['vence']      = $fecha;
				$data['totpre']     = $totpre;
				$data['totiva']     = $totiva;
				$data['totbruto']   = $totneto;
				$data['reten']      = 0;
				$data['totneto']    = $totneto;//totneto=totbruto-reten
				$data['codb1']      = $cargo;
				$data['tipo1']      = $tipo1;
				$data['cheque1']    = $cheque;
				$data['comprob1']   = '';
				$data['monto1']     = '';
				$data['codb2']      = '';
				$data['tipo2']      = '';
				$data['cheque2']    = '';
				$data['comprob2']   = '';
				$data['monto2']     = '';
				$data['codb3']      = '';
				$data['tipo3']      = '';
				$data['cheque3']    = '';
				$data['comprob3']   = '';
				$data['monto3']     = '';
				$data['credito']    = $credito;
				$data['tipo_doc']   = 'FC';
				$data['orden']      = '';
				$data['anticipo']   = 0;
				$data['benefi']     = $benefi;
				$data['mdolar']     = '';
				$data['usuario']    = $this->session->userdata('usuario');
				$data['estampa']    = date('Y-m-d');
				$data['hora']       = date('H:i:s');
				$data['transac']    = $transac;
				$data['preten']     = '';
				$data['creten']     = '';
				$data['breten']     = '';
				$data['huerfano']   = '';
				$data['reteiva']    = 0;
				$data['nfiscal']    = '';
				$data['afecta']     = '';
				$data['fafecta']    = '';
				$data['ffactura']   = '';
				$data['cajachi']    = 'S';
				$data['montasa']    = $montasa;
				$data['monredu']    = $monredu;
				$data['monadic']    = $monadic;
				$data['tasa']       = $tasa;
				$data['reducida']   = $reducida;
				$data['sobretasa']  = $sobretasa;
				$data['exento']     = $exento;
				$data['compra']     = '';
				$data['serie']      = '';
				$data['reteica']    = '';
				$data['retesimple'] = 3;
				$data['negreso']    = $negreso;
				$data['ncausado']   = '';
				$data['tipo_or']    = '';

				$sql=$this->db->insert_string('gser', $data);
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'bcaj'); $error++;}

				$mSQL='CALL sp_actusal('.$this->db->escape($codbanc).",'$sp_fecha',-$totneto)";
				$ban=$this->db->simple_query($mSQL); 
				if($ban==false){ memowrite($mSQL,'bcaj'); $error++; }
			}
		return ($error==0)? true : false;
	}

	function ajaxsprv(){
		$rif=$this->input->post('rif');
		if($rif!==false){
			$dbrif=$this->db->escape($rif);
			$nombre=$this->datasis->dameval("SELECT nombre FROM provoca WHERE rif=$dbrif");
			if(empty($nombre))
				$nombre=$this->datasis->dameval("SELECT nombre FROM sprv WHERE rif=$dbrif");
			echo $nombre;
		}
	}

	function listo($error,$numero=null){
		if($error=='n'){
			$data['content'] = 'Transacci&oacute;n completada ';
			if(!empty($numero)){
				$url='formatos/verhtml/';

				$data['content'] .= ', puede <a href="#" onclick="fordi.print();">imprimirla</a>';
				$data['content'] .= ' o '.anchor('finanzas/gser/index','Regresar');
				$data['content'] .= "<iframe name='fordi' src ='$url' width='100%' height='450'><p>Tu navegador no soporta iframes.</p></iframe>";
			}else{
				$data['content'] .= anchor('finanzas/gser/index','Regresar');
			}
		}else{
			$data['content'] = 'Lo siento pero hubo alg&uacute;n error en la transacci&oacute;n, se genero un centinela '.anchor('finanzas/gser/index','Regresar');
		}
		$data['title']   = heading('Transferencias entre cajas');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'mgas',
			'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'descrip'=>'descrip'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'descrip_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Articulo',
			'script'  =>array('lleva(<#i#>)'));

		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$script='$(function(){
			$(".inputnum").numeric(".");
			
			';

		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;odigo',
			'nombre'=>'Nombre',
			'rif'=>'Rif'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre','reteiva'=>'__reteiva'),
			
			'titulo'  =>'Buscar Proveedorr');
		$bSPRV=$this->datasis->modbus($mSPRV);

		$mBANC=array(
			'tabla'   =>'banc',
			'columnas'=>array(
			'codbanc' =>'C&oacute;odigo','tbanco'=>'Entidad',
			'banco'=>'Banco',
			'dire1'=>'Direcci&oacute;n','proxch'=>'ProxChe'),
			'filtro'  =>array('codbanc'=>'C&oacute;digo','banco'=>'Banco'),
			'retornar'=>array('codbanc'=>'codb1','proxch'=>'cheque1'),
			
			'titulo'  =>'Buscar Banco');
		$bBANC=$this->datasis->modbus($mBANC);

		$mRETE=array(
			'tabla'   =>'rete',
			'columnas'=>array(
			'codigo' =>'C&oacute;odigo','activida'=>'Actividad',
			'base1'=>'Base1','pama1'=>'Para Mayores','tari1'=>'%'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','activida'=>'Actividad'),
			'retornar'=>array('codigo'=>'creten','base1'=>'__base','tari1'=>'__tar','pama1'=>'__pama'),
			'titulo'  =>'Buscar Retencion',
			'script'=>array('islr()'));
		$bRETE=$this->datasis->modbus($mRETE);

		$do = new DataObject('gser');
		$do->rel_one_to_many('gitser', 'gitser',array('id'=>'idgser'));
		//$do->rel_pointer('itspre','sinv','itspre.codigo=sinv.codigo','sinv.descrip as sinvdescrip');

		$edit = new DataDetails("Gastos", $do);
		$edit->back_url = site_url("finanzas/gser/filteredgrid");
		$edit->set_rel_title('gitser','Gasto <#o#>');

		$edit->script($script,'create');
		$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->tipo_doc =  new dropdownField("Tipo Documento", "tipo_doc");

		$edit->tipo_doc->option('FC',"Factura");
		$edit->tipo_doc->option('ND',"Nota Debito");
		$edit->tipo_doc->option('AD',"Amortizaci&oacute;n");
		$edit->tipo_doc->option('GA',"Gasto de N&oacute;mina");
		$edit->tipo_doc->style="30px";

		$edit->ffactura = new DateonlyField("Fecha Documento", "ffactura","d/m/Y");
		//$edit->ffactura->insertValue = date("Y-m-d");
		//$edit->ffactura->mode="autohide";
		$edit->ffactura->size = 10;

		$edit->fecha = new DateonlyField("Fecha Recepci&oacute;n", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		//$edit->fecha->mode="autohide";
		$edit->fecha->size = 10;

		$edit->vence = new DateonlyField("Fecha Vencimiento", "vence","d/m/Y");
		//$edit->vence->insertValue = date("Y-m-d");
		//$edit->vence->mode="autohide";
		$edit->vence->size = 10;

		$edit->id = new inputField("ID", "id");
		$edit->id->size = 10;
		$edit->id->mode="autohide";
		$edit->id->maxlength=8;
		$edit->id->when=array("");

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		//$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;
		//$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		//$edit->numero->when=array('create','modify');

		$edit->proveedg = new inputField("Proveedor","proveed");
		$edit->proveedg->size = 10;
		$edit->proveedg->maxlength=5;
		$edit->proveedg->append($bSPRV);
		$edit->proveedg->rule= "required";
		//$edit->proveedg->mode="autohide";

		$edit->nfiscal  = new inputField("Control Fiscal", "nfiscal");
		$edit->nfiscal->size = 10;
		$edit->nfiscal->maxlength=20;
		//$edit->nfiscal->css_class='inputnum';

		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 30;
		$edit->nombre->maxlength=40;
		$edit->nombre->rule= "required";

		$edit->totpre  = new inputField("Sub.Total", "totpre");
		$edit->totpre->size = 10;
		$edit->totpre->css_class='inputnum';
		$edit->totpre->onkeyup="valida(0)";

		$edit->totbruto= new inputField("Total", "totbruto");
		$edit->totbruto->size = 10;
		$edit->totbruto->css_class='inputnum';
		$edit->totbruto->onkeyup="valida(0)";

		$edit->totiva = new inputField("TOTAL IVA", "totiva");
		//$edit->totiva->mode="autohide";
		$edit->totiva->css_class ='inputnum';
		//$edit->totiva->when=array('show','modify');
		$edit->totiva->size      = 10;
		$edit->totiva->onkeyup="valida(0)";

		$edit->codb1 = new inputField("Banco","codb1");
		$edit->codb1->size = 5;
		$edit->codb1->maxlength=2;
		$edit->codb1->append($bBANC);

		$edit->tipo1 =  new dropdownField("Tipo", "tipo1");
		$edit->tipo1->option('',"Tipo");
		$edit->tipo1->option('C',"Cheque");
		$edit->tipo1->option('D',"Debito");
		$edit->tipo1->style="20px";

		$edit->cheque1 = new inputField("N&uacute;mero","cheque1");
		$edit->cheque1->size = 15;
		$edit->cheque1->maxlength=20;

		$edit->benefi = new inputField("Beneficiario","benefi");
		$edit->benefi->size = 30;
		$edit->benefi->maxlength=40;

		$edit->monto1= new inputField("Monto", "monto1");
		$edit->monto1->size = 10;
		$edit->monto1->css_class='inputnum';

		$edit->credito= new inputField("Saldo Cr&eacute;dito", "credito");
		$edit->credito->size = 10;
		$edit->credito->css_class='inputnum';

		$edit->comprob1= new inputField("Comprobante externo", "comprob1");
		$edit->comprob1->size = 20;
		$edit->comprob1->css_class='inputnum';

		$edit->transac= new inputField("Transacci&oacute;n", "transac");
		$edit->transac->size = 10;
		$edit->transac->css_class='inputnum';
		$edit->transac->mode="autohide";
		$edit->transac->when=array('show','modify');

		$edit->creten = new inputField("Literal","creten");
		$edit->creten->size = 10;
		$edit->creten->maxlength=10;
		$edit->creten->append($bRETE);
		//$edit->creten->rule= "required";

		$edit->breten = new inputField("Base","breten");
		$edit->breten->size = 10;
		$edit->breten->maxlength=10;
		$edit->breten->css_class='inputnum';
		$edit->breten->onkeyup="valida(0)";

		$edit->reten = new inputField("Monto","reten");
		$edit->reten->size = 10;
		$edit->reten->maxlength=10;
		$edit->reten->css_class='inputnum';
		$edit->reten->onkeyup="valida(0)";

		$edit->reteiva = new inputField("Retenci&oacute;n de IVA","reteiva");
		$edit->reteiva->size = 10;
		$edit->reteiva->maxlength=10;
		$edit->reteiva->css_class='inputnum';
		$edit->reteiva->onkeyup="valida(0)";

		//$edit->anticipo = new inputField("Anticipo","anticipo");
		//$edit->anticipo->size = 10;
		//$edit->anticipo->maxlength=10;
		//$edit->anticipo->css_class='inputnum';
		//$edit->anticipo->mode="autohide";

		$edit->totneto = new inputField("Total Neto","totneto");
		$edit->totneto->size = 10;
		$edit->totneto->maxlength=10;
		$edit->totneto->css_class='inputnum';

		//Campos para el detalle
		$edit->codigo = new inputField("C&oacute;digo <#o#>", "codigo_<#i#>");
		$edit->codigo->size=8;
		$edit->codigo->db_name='codigo';
		$edit->codigo->append($btn);
		$edit->codigo->rule="required";
		$edit->codigo->rel_id='gitser';
		$detalle->importe->mode="autohide";

		$edit->descrip = new inputField("Descripci&oacute;n <#o#>", "descrip_<#i#>");
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->rel_id='gitser';

		$edit->precio = new inputField("Precio <#o#>", "precio_<#i#>");
		$edit->precio->db_name='precio';
		$edit->precio->css_class='inputnum';
		$edit->precio->size=7;
		$edit->precio->rule='required';
		$edit->precio->rel_id='gitser';
		$edit->precio->onkeyup="totalizar(<#i#>)";

		$edit->tasaiva =  new dropdownField("IVA <#o#>", "tasaiva_<#i#>");
		$edit->tasaiva->options("SELECT tasa,tasa as t1 FROM civa ORDER BY fecha desc limit 1");
		$edit->tasaiva->options("SELECT redutasa,redutasa as rt1 FROM civa ORDER BY fecha desc limit 1");
		$edit->tasaiva->options("SELECT sobretasa,sobretasa as st1 FROM civa ORDER BY fecha desc limit 1");
		$edit->tasaiva->option('0','0.00');
		$edit->tasaiva->db_name='tasaiva';
		$edit->tasaiva->style="30px";
		$edit->tasaiva->rel_id   ='gitser';
		$edit->tasaiva->onchange="totalizar(<#i#>)";

		$edit->iva = new inputField("importe <#o#>", "iva_<#i#>");
		$edit->iva->db_name='iva';
		$edit->iva->css_class='inputnum';
		$edit->iva->rel_id   ='gitser';
		$edit->iva->size=7;
		$edit->iva->onkeyup="valida(<#i#>)";

		$edit->importe = new inputField("importe <#o#>", "importe_<#i#>");
		$edit->importe->db_name='importe';
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   ='gitser';
		$edit->importe->size=7;
		$edit->importe->onkeyup="valida(<#i#>)";

		$edit->departa =  new dropdownField("Departamento <#o#>", "departa_<#i#>");
		$edit->departa->option('',"Seleccion Departamento");
		$edit->departa->options("SELECT departa,CONCAT(departa,'-',depadesc) as descrip FROM depa ORDER BY departa");
		$edit->departa->db_name='departa';
		$edit->departa->style="30px";
		$edit->departa->rel_id   ='gitser';

		$edit->sucursal =  new dropdownField("Sucursal <#o#>", "sucursal_<#i#>");
		$edit->sucursal->option('',"Seleccion Sucursal");
		$edit->sucursal->options("SELECT codigo,CONCAT(codigo,'-', sucursal)as sucursal FROM sucu ORDER BY codigo");
		$edit->sucursal->db_name='sucursal';
		$edit->sucursal->style="20px";
		$edit->sucursal->rel_id   ='gitser';

		$edit->fechad = new inputField("fecha <#o#>", "fecha_<#i#>");
		$edit->fechad->db_name='fecha';
		$edit->fechad->size=0;
		$edit->fechad->rel_id   ='gitser';
		$edit->fechad->mode="autohide";
		$edit->fechad->when=array("");

		$edit->numerod = new inputField("numero <#o#>", "numero_<#i#>");
		$edit->numerod->db_name='numero';
		$edit->numerod->size=0;
		$edit->numerod->rel_id   ='gitser';
		$edit->numerod->mode="autohide";
		$edit->numerod->when=array("");

		$edit->proveed = new inputField("Proveedor <#o#>", "proveed_<#i#>");
		$edit->proveed->db_name='proveed';
		$edit->proveed->size=0;
		$edit->proveed->rel_id   ='gitser';
		$edit->proveed->mode="autohide";
		$edit->proveed->when=array("");

		//$edit->idgser = new inputField("id <#o#>", "idgser_<#i#>");
		//$edit->idgser->db_name='idgser';
		//$edit->idgser->size=0;
		//$edit->idgser->rel_id   ='gitser';
		//$edit->idgser->mode="autohide";
		//$edit->idgser->when=array("");

		//fin de campos para detalle

		$edit->buttons("modify", "save", "undo", "delete", "back","add_rel");
		$edit->build();
		//echo $edit->_dataobject->db->last_query();

		$conten['form']  =&$edit;
		$data['content'] = $this->load->view('view_gser', $conten,true);
		$data['title']   = heading('Gastos');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data);
	}

	function mgserdataedit(){
		$this->rapyd->load('dataedit');
		$this->rapyd->uri->keep_persistence();

		$sprv=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;digo Proveedor',
			'nombre'=>'Nombre',
			'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor');

		$bsprv=$this->datasis->modbus($sprv);

		$edit = new DataEdit('Modificar Egreso','gser');
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save=true;
		$edit->pre_process( 'create','_pre_mgsercreate' );
		$edit->pre_process( 'update','_pre_mgserupdate' );
		$edit->post_process('update','_post_mgserupdate');
		$edit->back_url = 'finanzas/gser';

		$edit->fecha = new dateonlyField('Fecha recepci&oacute;n', 'fecha');
		$edit->fecha->size = 10;
		$edit->fecha->rule= 'required';

		$edit->ffactura = new dateonlyField('Fecha de factura', 'ffactura');
		$edit->ffactura->size = 10;
		$edit->ffactura->rule= 'required';

		$edit->vence = new dateonlyField('Vencimiento', 'vence');
		$edit->vence->size = 10;
		$edit->vence->rule= 'required';

		$edit->serie = new inputField('N&uacute;mero', 'serie');
		$edit->serie->size = 20;
		$edit->serie->rule= 'required|trim';
		$edit->serie->maxlength=20;

		$edit->nfiscal = new inputField('Control F&iacute;scal', 'nfiscal');
		$edit->nfiscal->size = 20;
		$edit->nfiscal->rule= 'required|max_length[12]|trim';
		$edit->nfiscal->maxlength=20;

		$edit->codigo = new inputField('C&oacute;digo del proveedor', 'proveed');
		$edit->codigo->size =8;
		$edit->codigo->maxlength=5;
		$edit->codigo->append($bsprv);
		$edit->codigo->rule = 'required|trim';
		//$edit->codigo->group='Proveedor';

		$edit->nombre = new inputField('Nombre del proveedor', 'nombre');
		$edit->nombre->size =  50;
		$edit->nombre->maxlength=40; 
		$edit->nombre->rule= 'required';
		//$edit->nombre->group='Proveedor';

		$edit->totpre = new inputField('Monto neto', 'totpre');
		$edit->totpre->mode='autohide';
		$edit->totpre->group='Montos';

		$edit->totiva = new inputField('Impuesto', 'totiva');
		$edit->totiva->mode='autohide';
		$edit->totiva->group='Montos';

		$edit->totbruto = new inputField('Monto total', 'totbruto');
		$edit->totbruto->mode='autohide';
		$edit->totbruto->group='Montos';

		$edit->buttons('save','undo','modify','back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Egresos');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_mgserupdate($do){
		$serie     = $do->get('serie');
		$nnumero   = substr($serie,-8);
		$do->set('numero',$nnumero);
	}

	function _post_mgserupdate($do){
		$fecha     = $this->db->escape($do->get('fecha'));
		$vence     = $this->db->escape($do->get('vence'));
		$proveed   = $this->db->escape($do->get('proveed'));
		$nombre    = $this->db->escape($do->get('nombre'));
		$transac   = $do->get('transac');
		$dbtransac = $this->db->escape($transac);
		$numero    = $this->db->escape($do->get('numero'));

		$update="UPDATE gser SET serie=$numero WHERE transac=$dbtransac";
		$this->db->query($update);

		$update2="UPDATE gitser SET fecha=$fecha, proveed=$proveed,numero=$numero WHERE transac=$dbtransac";
		$this->db->query($update2);

		//MODIFICA SPRM
		$update3="UPDATE sprm SET fecha=$fecha,vence=$vence, numero=$numero, cod_prv=$proveed,nombre=$nombre WHERE tipo_doc='FC'AND transac=$dbtransac";
		$this->db->query($update3);

		//MODIFICA BMOV
		$update4="UPDATE bmov SET fecha=$fecha, numero=$numero, codcp=$proveed,nombre=$nombre WHERE clipro='P' AND transac=$dbtransac";
		$this->db->query($update4);

		//MODIFICA RIVA
		$update5="UPDATE riva SET fecha=$fecha, numero=$numero,clipro=$proveed,nombre=$nombre WHERE transac=$dbtransac";
		$this->db->query($update5);

		logusu('gser',"Gasto $numero CAMBIADO");
		return true;
	}

	function _pre_mgsercreate($do){
		return false;
	}


	function _pre_insert($do){
		if($do->get('numero')==""){
			$numero=$this->datasis->fprox_numero('ngser');
			$do->set('numero',$numero);
		}
		else $numero=$do->get('numero');
		$trans=$this->datasis->fprox_numero('ntransa');
		//		$do->set('numero',$numero);
		$do->set('transac',$trans);

		$datos=$do->get_all();
		$ivat=0;$subt=0;$total=0;
		$cana=$do->count_rel("gitser");
		for($i=0;$i<$cana;$i++){
			$do->set_rel('gitser','fecha',$do->get('fecha'),$i);
			$do->set_rel('gitser','numero',$numero,$i);

		}
		$tasa=0;$reducida=0;$sobretasa=0;$montasa=0;$monredu=0;$monadic=0;$exento=0;
		$con=$this->db->query("select tasa,redutasa,sobretasa from civa order by fecha desc limit 1");
		$t=$con->row('tasa');$rt=$con->row('redutasa');$st=$con->row('sobretasa');

		foreach($datos['gitser'] as $rel){
			$auxt=$rel['tasaiva'];
			if($auxt==$t) {
				$tasa+=$rel['iva'];
				$montasa+=$rel['precio'];
			}elseif($auxt==$rt) {
				$reducida+=$rel['iva'];
				$monredu+=$rel['precio'];
			}elseif($auxt==$st) {
				$sobretasa+=$rel['iva'];
				$monadic+=$rel['precio'];
			}else{
				$exento+=$rel['precio'];
			}
			$p=$rel['precio'];
			$i=$rel['iva'];
			$total+=$i+$p;
			$subt+=$p;
			//$rel['fecha']=$do->get('fecha');
		}
		$ivat=$total-$subt;
		$do->set('tasa',$tasa);$do->set('montasa',$montasa);
		$do->set('reducida',$reducida);$do->set('monredu',$monredu);
		$do->set('sobretasa',$sobretasa);$do->set('monadic',$monadic);
		$do->set('exento',$exento);
		//$do->set('totpre',$subt);
		//$do->set('totbruto',$total);
		//$do->set('totiva',$ivat);

		if ($do->get('monto1') != 0){
			$negreso  = $this->datasis->fprox_numero("negreso");
			$ncausado = "";
		}else{
			$ncausado = $this->datasis->fprox_numero("ncausado");
			$negreso  = "";
		}
		$do->set('negreso',$negreso);
		$do->set('ncausado',$ncausado);
		//		echo $this->datasis->traevalor('pais');
		if ($this->datasis->traevalor('pais') == 'COLOMBIA'){
			if($this->datasis->dameval("SELECT tiva FROM sprv WHERE proveed='".$do->get('proveed')."'")=='S'){
				foreach($datos['gitser'] as $rel){
					$mIVA  = $rel['iva'];
					$mRIVA = $this->datasis->dameval("SELECT reteiva FROM sprv WHERE proveed='".$do->get('proveed')."' ");
					if ($mRIVA == 0)$mRIVA = 50;
					$mRETEIVA = ROUND($do->get('precio')*($mIVA/100)*($mRIVA/100),0);
				}
				$do->set("RETESIMPLE",  $mRETEIVA);
				$retesumple = $mRETEIVA;
			}
		}
		$serie=$do->get('serie');
		if(empty($serie))
		$XSERIE = $numero;
		$do->set('serie',$XSERIE);
		$XORDEN=$do->get('orden');
		if ($do->get('tipo_doc') == 'ND')$XORDEN = '        ';

		if($do->get('credito')>0){
			$ncontrol=$this->datasis->fprox_numero('nsprm');
			$abonos=$do->get("monto1")+$do->get("anticipo");


			$IMPUESTO=$ivat;
			$VENCE=$do->get('vence');
			$ABONOS =$abonos+$do->get('reten')+$do->get('reteiva');
			if($this->datasis->traevalor('pais') == 'COLOMBIA')$ABONOS+=$do->get('reteica');
			$NFISCAL=$do->get('nfiscal');

			$sql="REPLACE INTO sprm (transac,
			numero,cod_prv,nombre,tipo_doc,fecha ,
			monto,impuesto,vence,abonos,tipo_ref,num_ref,
			nfiscal, control,reteiva,montasa,monredu,monadic,
			tasa,reducida, sobretasa,exento)
			values('".$trans."','".$numero."','".$do->get('proveed')."','".$do->get('nombre')."','".$do->get('tipo_doc')."',
			'".$do->get('fecha')."',".$total.",".$ivat.",'".$do->get('vence')."',
			".$ABONOS.",'','','".$do->get('nfiscal')."','".$ncontrol."',
			".$do->get('reteiva').",".$montasa.",".$monredu.",".$monadic.",
			".$tasa.",".$reducida.",".$sobretasa.",".$exento.")
			";
			$this->db->query($sql);

			if(empty($XORDEN)){
				$mANTICIPO = $do->get('anticipo');


				//Luego buscar anticipos
				$mSQL = "SELECT * FROM sprm WHERE cod_prv='".$do->get('proveed')."' ";

				$mSQL .= "AND tipo_doc='AN' AND num_ref='".$XORDEN."' ";

				$mSQL .= "AND tipo_ref='OS' ";
				$banticipo=$this->db->query($mSQL);

				$resultado=$banticipo->num_rows();

				foreach($banticipo->result() as $registro){
					$mTEMPO=$mANTICIPO;
					$mANTICIPO -=$registro['monto']-$registro['abonos'];
					$mMONTO=$registro['monto'];
					$mABONOS=$registro['abonos'];
					if($mANTICIPO >= 0){
						$mSQLant="UPDATE sprm SET abonos=".$mMONTO." WHERE tipo_doc='".$registro['tipo_doc']."' AND numero=".$registro['numero']." AND cod_prv='".$do->get('proveed')."'";
						$this->db->query($mSQLant);
					}else{
						$mANTICIPO = 0;
						$mSQLant="UPDATE sprm SET abonos=".$mTEMPO." WHERE tipo_doc='".$registro['tipo_doc']."' AND numero=".$registro['numero']." AND cod_prv='".$do->get('proveed')."'";
						$this->db->query($mSQLant);
					}
					if($mANTICIPO == 0) break;
					$campos=array('numppro','tipoppro','cod_prv','numero','tipo_doc','fecha','monto','abono','breten','creten','reten','reteiva','ppago','cambio','mora','transac');
					$valores=array($registro['numero'],$registro['tipo_doc'],$do->get('proveed'),$do->get('numero'),$do->get('tipo_doc'),$do->get('fecha'),$mMONTO,$mABONOS,0,'',0,0,0,0,0);
					$mSQL = "INSERT INTO itppro SET(".$campos.")VALUES(".$valores.") ";
					echo $msql;
				}
			}
		}

		return true;
	}

	function _pre_update($do){
		//print("<pre>");
		//echo $do->get_rel('itspre','preca',2);
		$datos=$do->get_all();
		$ivat=0;$subt=0;$total=0;
		$cana=$do->count_rel("gitser");
		$tasa=0;$reducida=0;$sobretasa=0;$montasa=0;$monredu=0;$monadic=0;$exento=0;
		$con=$this->db->query("select tasa,redutasa,sobretasa from civa order by fecha desc limit 1");
		$t=$con->row('tasa');$rt=$con->row('redutasa');$st=$con->row('sobretasa');

		for($i=0;$i<$cana;$i++){
			$do->set_rel('gitser','fecha',$do->get('fecha'),$i);
			$do->set_rel('gitser','numero',$do->get('numero'),$i);

		}
		foreach($datos['gitser'] as $rel){
			$auxt=$rel['tasaiva'];
			if($auxt==$t) {
				$tasa+=$rel['iva'];
				$montasa+=$rel['precio'];
			}elseif($auxt==$rt) {
				$reducida+=$rel['iva'];
				$monredu+=$rel['precio'];
			}elseif($auxt==$st) {
				$sobretasa+=$rel['iva'];
				$monadic+=$rel['precio'];
			}else{
				$exento+=$rel['precio'];
			}
			$p=$rel['precio'];
			$i=$rel['iva'];
			$total+=$i+$p;
			$subt+=$p;
		}
		$ivat=$total-$subt;
		$do->set('tasa',$tasa);$do->set('montasa',$montasa);
		$do->set('reducida',$reducida);$do->set('monredu',$monredu);
		$do->set('sobretasa',$sobretasa);$do->set('monadic',$monadic);
		$do->set('exento',$exento);


		if ($do->get('monto1') != 0){
			$negreso  = $this->datasis->fprox_numero("negreso");
			$ncausado = "";
		}else{
			$ncausado = $this->datasis->fprox_numero("ncausado");
			$negreso  = "";
		}
		$do->set('negreso',$negreso);
		$do->set('ncausado',$ncausado);
		//		echo $this->datasis->traevalor('pais');
		if ($this->datasis->traevalor('pais') == 'COLOMBIA'){
			if($this->datasis->dameval("SELECT tiva FROM sprv WHERE proveed='".$do->get('proveed')."'")=='S'){
				foreach($datos['gitser'] as $rel){
					$mIVA  = $rel['iva'];
					$mRIVA = $this->datasis->dameval("SELECT reteiva FROM sprv WHERE proveed='".$do->get('proveed')."' ");
					if ($mRIVA == 0)$mRIVA = 50;
					$mRETEIVA = ROUND($do->get('precio')*($mIVA/100)*($mRIVA/100),0);
				}
				$do->set("RETESIMPLE",  $mRETEIVA);
				$retesumple = $mRETEIVA;
			}
		}
		$serie=$do->get('serie');
		if(empty($serie))
		$XSERIE = $do->get('numero');
		$do->set('serie',$XSERIE);
		$XORDEN=$do->get('orden');
		if ($do->get('tipo_doc') == 'ND')$XORDEN = '        ';

		if($do->get('credito')>0){
			$ncontrol=$this->datasis->fprox_numero('nsprm');
			$abonos=$do->get("monto1")+$do->get("anticipo");


			$IMPUESTO=$ivat;
			$VENCE=$do->get('vence');
			$ABONOS =$abonos+$do->get('reten')+$do->get('reteiva');
			if($this->datasis->traevalor('pais') == 'COLOMBIA')$ABONOS+=$do->get('reteica');
			$NFISCAL=$do->get('nfiscal');

			$sql="REPLACE INTO sprm (transac,
			numero,cod_prv,nombre,tipo_doc,fecha ,
			monto,impuesto,vence,abonos,tipo_ref,num_ref,
			nfiscal, control,reteiva,montasa,monredu,monadic,
			tasa,reducida, sobretasa,exento)
			values('".$do->get('transac')."','".$do->get('numero')."','".$do->get('proveed')."','".$do->get('nombre')."','".$do->get('tipo_doc')."',
			'".$do->get('fecha')."',".$total.",".$ivat.",'".$do->get('vence')."',
			".$ABONOS.",'','','".$do->get('nfiscal')."','".$ncontrol."',
			".$do->get('reteiva').",".$montasa.",".$monredu.",".$monadic.",
			".$tasa.",".$reducida.",".$sobretasa.",".$exento.")
			";
			$this->db->query($sql);

			if(empty($XORDEN)){
				$mANTICIPO = $do->get('anticipo');


				//Luego buscar anticipos
				$mSQL = "SELECT * FROM sprm WHERE cod_prv='".$do->get('proveed')."' ";

				$mSQL .= "AND tipo_doc='AN' AND num_ref='".$XORDEN."' ";

				$mSQL .= "AND tipo_ref='OS' ";
				$banticipo=$this->db->query($mSQL);
				//echo "aqui".$mSQL."/fin";
				//exit;
				$resultado=$banticipo->num_rows();

				foreach($banticipo->result() as $registro){
					$mTEMPO=$mANTICIPO;
					$mANTICIPO -=$registro['monto']-$registro['abonos'];
					$mMONTO=$registro['monto'];
					$mABONOS=$registro['abonos'];
					if($mANTICIPO >= 0){
						$mSQLant="UPDATE sprm SET abonos=".$mMONTO." WHERE tipo_doc='".$registro['tipo_doc']."' AND numero=".$registro['numero']." AND cod_prv='".$do->get('proveed')."'";
						$this->db->query($mSQLant);
					}else{
						$mANTICIPO = 0;
						$mSQLant="UPDATE sprm SET abonos=".$mTEMPO." WHERE tipo_doc='".$registro['tipo_doc']."' AND numero=".$registro['numero']." AND cod_prv='".$do->get('proveed')."'";
						$this->db->query($mSQLant);
					}
					if($mANTICIPO == 0) break;
					$campos=array('numppro','tipoppro','cod_prv','numero','tipo_doc','fecha','monto','abono','breten','creten','reten','reteiva','ppago','cambio','mora','transac');
					$valores=array($registro['numero'],$registro['tipo_doc'],$do->get('proveed'),$do->get('numero'),$do->get('tipo_doc'),$do->get('fecha'),$mMONTO,$mABONOS,0,'',0,0,0,0,0);
					$mSQL = "INSERT INTO itppro SET(".$campos.")VALUES(".$valores.") ";
					echo $msql;
				}

			}
		}
		return true;
	}

	function chtasa($monto){
		$iva=$this->input->post('montasa');
		if($monto>0 && $iva>0){
			return true;
		}elseif($monto==0 && $iva==0){
			return true;
		}else{
			$this->validation->set_message('chtasa', "Si la base general es mayor que cero debe generar impuesto");
			return false;
		}
	}

	function chreducida($monto){
		$iva=$this->input->post('monredu');
		if($monto>0 && $iva>0){
			return true;
		}elseif($monto==0 && $iva==0){
			return true;
		}else{
			$this->validation->set_message('chreducida', "Si la base reducida es mayor que cero debe generar impuesto");
			return false;
		}
	}

	function chsobretasa($monto){
		$iva=$this->input->post('monadic');
		if($monto>0 && $iva>0){
			return true;
		}elseif($monto==0 && $iva==0){
			return true;
		}else{
			$this->validation->set_message('chsobretasa', "Si la base adicional es mayor que cero debe generar impuesto");
			return false;
		}
	}


	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('gser',"Gasto $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('gser',"Gasto $codigo Modificado");

	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('gser',"Gasto $codigo ELIMINADO");
	}

	function instalar(){
		$query="SHOW INDEX FROM gser";
		$resul=$this->db->query($query);
		$existe=0;
		foreach($resul->result() as $ind){
			$nom= $ind->Column_name;
			if ($nom == 'id'){
				$existe=1;
				break;
			}
		}
		if($existe != 1){
			$query="ALTER TABLE `gser` DROP PRIMARY KEY";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD UNIQUE INDEX `gser` (`fecha`, `numero`, `proveed`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `ncausado`,  ADD PRIMARY KEY (`id`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gitser` ADD COLUMN `idgser` INT(15) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`, ADD INDEX `idgser` (`idgser`)";
			var_dump($this->db->simple_query($query));

			$query="UPDATE gitser AS a
					JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
					SET a.idgser=b.id";
			var_dump($this->db->simple_query($query));

			//$query="ALTER TABLE `gitser`  ADD COLUMN `tasaiva` DECIMAL(7,2) UNSIGNED NOT NULL DEFAULT '0' AFTER `idgser`;";
			//$this->db->simple_query($query);
		}

		$query="CREATE TABLE IF NOT EXISTS `gserchi` (
			`codbanc` varchar(5) NOT NULL DEFAULT '', 
			`fechafac` date DEFAULT NULL, 
			`numfac` varchar(8) DEFAULT NULL, 
			`nfiscal` varchar(12) DEFAULT NULL, 
			`rif` varchar(13) DEFAULT NULL, 
			`proveedor` varchar(40) DEFAULT NULL, 
			`codigo` varchar(6) DEFAULT NULL, 
			`descrip` varchar(50) DEFAULT NULL, 
			`moneda` char(2) DEFAULT NULL, 
			`montasa` decimal(17,2) DEFAULT '0.00', 
			`tasa` decimal(17,2) DEFAULT NULL, 
			`monredu` decimal(17,2) DEFAULT '0.00', 
			`reducida` decimal(17,2) DEFAULT NULL, 
			`monadic` decimal(17,2) DEFAULT '0.00', 
			`sobretasa` decimal(17,2) DEFAULT NULL, 
			`exento` decimal(17,2) DEFAULT '0.00', 
			`importe` decimal(12,2) DEFAULT NULL, 
			`sucursal` char(2) DEFAULT NULL, 
			`departa` char(2) DEFAULT NULL, 
			`usuario` varchar(12) DEFAULT NULL, 
			`estampa` date DEFAULT NULL, 
			`hora` varchar(8) DEFAULT NULL, 
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
		var_dump($this->db->simple_query($query));

		$query="ALTER TABLE `gserchi` ADD COLUMN `ngasto` VARCHAR(8) NULL DEFAULT NULL AFTER `departa`";
		var_dump($this->db->simple_query($query));
	}
}
