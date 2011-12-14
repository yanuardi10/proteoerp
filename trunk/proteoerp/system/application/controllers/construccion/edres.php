<?php require_once(BASEPATH.'application/controllers/formams.php');
class edres extends Controller {
	var $titp='Reservaciones';
	var $tits='Reservaciones';
	var $url ='construccion/edres/';

	function edres(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('A05',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'edres');

		$filter->numero = new inputField('N&uacute;mero','numero');
		$filter->numero->rule      ='max_length[8]';
		$filter->numero->size      =10;
		$filter->numero->maxlength =8;

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;

		$filter->cliente = new inputField('Cliente','cliente');
		$filter->cliente->rule      ='max_length[5]';
		$filter->cliente->size      =7;
		$filter->cliente->maxlength =5;

		$filter->edificacion = new dropdownField('Edificaci&oacute;n','edificacion');
		$filter->edificacion->option('','Seleccionar');
		$filter->edificacion->options('SELECT id,TRIM(nombre) AS nombre FROM `edif` ORDER BY nombre');

		$filter->inmueble = new dropdownField('Inmueble','inmueble');
		$filter->inmueble->option('','Seleccionar');
		$filter->inmueble->options('SELECT id,TRIM(descripcion) AS nombre FROM `edinmue` ORDER BY descripcion');

		$filter->reserva = new inputField('Reservaci&oacute;n','reserva');
		$filter->reserva->rule      ='max_length[17]|numeric';
		$filter->reserva->css_class ='inputnum';
		$filter->reserva->size      =19;
		$filter->reserva->maxlength =17;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#numero#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('N&uacute;mero',$uri,'numero','align="left"');
		$grid->column_orderby('Fecha'   ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align="center"');
		$grid->column_orderby('Cliente' ,'cliente','cliente','align="left"');
		$grid->column_orderby('Edificaci&oacute;n','<nformat><#edificacion#></nformat>','edificacion','align="right"');
		$grid->column_orderby('Inmueble','<nformat><#inmueble#></nformat>','inmueble','align="right"');
		$grid->column_orderby('Reservaci&oacute;n','<nformat><#reserva#></nformat>','reserva','align="right"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente'=>'C&oacute;digo Cliente',
			'nombre' =>'Nombre',
			'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cliente'),
		'titulo'  =>'Buscar Cliente');

		$boton=$this->datasis->modbus($scli);

		$edit = new DataEdit($this->tits, 'edres');

		$id=$edit->getval('id');
		if($id!==false){
			$action = "javascript:window.location='" . site_url($this->url.'formato/'.$id) . "'";
			$edit->button('btn_formato', 'Descargar formato', $action);
		}

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->numero = new inputField('N&uacute;mero','numero');
		$edit->numero->rule='max_length[8]|required|unique';
		$edit->numero->size =10;
		$edit->numero->mode ='autohide';
		$edit->numero->maxlength =8;
		$edit->numero->when=array('show','modify');

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='required|chfecha';
		$edit->fecha->insertValue=date('Y-m-d');
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->cliente = new inputField('Cliente','cliente');
		$edit->cliente->rule='max_length[5]|existescli|required';
		$edit->cliente->size =7;
		$edit->cliente->maxlength =5;
		$edit->cliente->append($boton);

		$edit->edificacion = new dropdownField('Edificaci&oacute;n','edificacion');
		$edit->edificacion->option('','Seleccionar');
		$edit->edificacion->options('SELECT id,TRIM(nombre) AS nombre FROM `edif` ORDER BY nombre');
		$edit->edificacion->rule='max_length[11]';

		$edit->inmueble = new dropdownField('Inmueble','inmueble');
		$edit->inmueble->option('','Seleccionar');
		$edif=$edit->getval('edificacion');
		if($edif!==false){
			$dbedif=$this->db->escape($edif);
			$edit->inmueble->option('','Seleccionar');
			$edit->inmueble->options("SELECT id,TRIM(descripcion) AS nombre FROM `edinmue` WHERE status='D' AND edificacion=$dbedif ORDER BY descripcion");
		}else{
			$edit->inmueble->option('','Seleccione una edificacion');
		}
		$edit->inmueble->rule='max_length[11]';

		$edit->reserva = new inputField('Monto de la Reservaci&oacute;n','reserva');
		$edit->reserva->rule='max_length[17]|numeric|callback_chmonto|required';
		$edit->reserva->css_class='inputnum';
		$edit->reserva->size =19;
		$edit->reserva->maxlength =17;

		$mSQL="SELECT cod_banc, nomb_banc FROM tban WHERE cod_banc<>'CAJ'";
		$query = $this->db->query($mSQL);
		$bancos=array();
		foreach ($query->result() as $row){
			$bancos[$row->cod_banc]=$row->nomb_banc;
		}

		for($i=1;$i<4;$i++){
			$group='Formas de pago '.$i;

			$obj1='formap'.$i;
			$edit->$obj1 =  new dropdownField('Pago '.$i, $obj1);
			$edit->$obj1->option('','Ninguno'        );
			$edit->$obj1->option('CH','Cheque'       );
			$edit->$obj1->option('DE','Deposito'     );
			$edit->$obj1->option('NC','Transferencia');
			$edit->$obj1->group=$group;
			$edit->$obj1->style='width:140px';
			$edit->$obj1->rule ='max_length[2]';
			if($i==1) $edit->$obj1->rule='required';

			$obj2='banco'.$i;
			$edit->$obj2 =  new dropdownField('Banco '.$i, $obj2);
			$edit->$obj2->option('','Seleccionar banco');
			$edit->$obj2->options($bancos);
			$edit->$obj2->group=$group;
			$edit->$obj2->rule='max_length[3]|condi_required|callback_chpago['.$i.']';
			$edit->$obj2->in=$obj1;

			$obj4='pfecha'.$i;
			$edit->$obj4 =  new dateonlyField('Fecha ', $obj4);
			$edit->$obj4->group=$group;
			$edit->$obj4->rule='condi_required|callback_chpago['.$i.']';
			$edit->$obj4->size=10;
			//$edit->$obj4->in=$obj1;

			$obj3='nummp'.$i;
			$edit->$obj3 = new inputField('N&uacute;mero referencia',$obj3);
			$edit->$obj3->rule='max_length[20]|condi_required|callback_chpago['.$i.']';
			$edit->$obj3->size =20;
			$edit->$obj3->maxlength =20;
			$edit->$obj3->group=$group;

			$obj4='monto'.$i;
			$edit->$obj4 = new inputField('Monto',$obj4);
			$edit->$obj4->rule='condi_required|callback_chpago['.$i.']';
			$edit->$obj4->css_class='inputnum';
			$edit->$obj4->size =19;
			$edit->$obj4->maxlength =17;
			$edit->$obj4->group=$group;
		}

		$edit->notas = new textareaField('Notas','notas');
		$edit->notas->cols = 70;
		$edit->notas->rows = 4;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add');
		$edit->build();

		$link1=site_url('construccion/common/get_inmue');
		$script= '<script type="text/javascript" >
		$(function() {
			$("#edificacion").change(function(){ edif_change(); });
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
		});

		function edif_change(){
			$.post("'.$link1.'",{ edif:$("#edificacion").val() }, function(data){ $("#inmueble").html(data);})
		}
		</script>';

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['script']  = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$data['script'] .= $script;
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);
	}

	function chpago($val,$ind){
		$p=$this->input->post('formap'.$ind);
		if(!empty($p) && empty($val)){
			$this->validation->set_message('chpago', 'El campo %s es obligatorio cuando selecciona un medio de pago');
			return false;
		}
		return true;
	}

	function chmonto($dtotal){
		$this->validation->set_message('chmonto', 'El %s debe coincidir con la suma de los pagos');
		$total=0;
		for($i=1;$i<4;$i++){
			$monto=$this->input->post('monto'.$i);
			if(!empty($monto)){
				$total+=$monto;
			}
		}
		$diff=round($dtotal-$total,2);
		return ($diff==0)? true: false;
	}

	function formato($id){
		$this->load->plugin('numletra');
		$sel=array('a.numero','a.reserva','a.fecha','b.nombre','b.rifci',
			'a.formap1','a.banco1','a.nummp1','a.pfecha1',
			'a.formap2','a.banco2','a.nummp2','a.pfecha2',
			'a.formap3','a.banco3','a.nummp3','a.pfecha3',
			'CONCAT(b.dire11,b.dire12) AS direc','b.telefono','c.codigo AS inmueble',
			'd.descripcion AS ubicacion','e.uso');
		$this->db->select($sel);
		$this->db->from('edres AS a');
		$this->db->join('scli AS b','a.cliente=b.cliente');
		$this->db->join('edinmue AS c','c.id=a.inmueble');
		$this->db->join('edifubica AS d','d.id=c.ubicacion');
		$this->db->join('eduso AS e','e.id=c.uso');
		$this->db->where('a.id',$id);

		$query = $this->db->get();
		//echo $this->db->last_query();
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){

				$data=array();
				$data['numero']    =$row->numero;
				$data['fecha']     =dbdate_to_human($row->fecha);
				$data['monto']     =nformat($row->reserva);
				$data['montolet']  =strtoupper(numletra($row->reserva));
				$data['nom_scli']  =$row->nombre;
				$data['rif_scli']  =$row->rifci;
				$data['direc_scli']=$row->direc;
				$data['telef_scli']=$row->telefono;
				$data['inmueble']  =$row->inmueble;
				$data['ubicacion'] =$row->ubicacion;
				$data['uso']       =$row->uso;
				$data['fpagos']    ='';

				for($i=1;$i<4;$i++){
					$pago   = 'pago'.$i;
					$formap = 'formap'.$i;
					$pfecha = 'pfecha'.$i;
					$banco  = 'banco'.$i;
					$nummp  = 'nummp'.$i;
					$banco  = 'banco'.$i;

					$data[$pago]='';
					if(!empty($row->$formap)){
						if($row->$formap=='CH'){
							$data[$pago] .= 'Cheque';
						}elseif($row->$formap=='DE'){
							$data[$pago] .= 'Depósito';
						}elseif($row->$formap=='NC'){
							$data[$pago] .= 'Transferencia';
						}
						$dbcodbanc=$this->db->escape($row->$banco);
						$nombanc=$this->datasis->dameval("SELECT nomb_banc FROM tban WHERE cod_banc=$dbcodbanc");

						$data[$pago] .= ' del Banco ';
						$data[$pago] .= ucwords($nombanc);
						$data[$pago] .= ', Número '.$row->$nummp;
						$data[$pago] .= ' de fecha '.dbdate_to_human($row->$pfecha).'.';
					}else{
						$data[$pago] = '';
					}
				}

				formams::_msxml('reservacion',$data);
			}
		}
	}

	function _pre_insert($do){
		$transac   = $this->datasis->fprox_numero('ntransa');
		$numero    = $this->datasis->fprox_numero('nedres');
		$inmueble  = $do->get('inmueble');
		$dbinmueble= $this->db->escape($inmueble);

		$mSQL="UPDATE edinmue SET status='R' WHERE id=${dbinmueble}";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'edres'); }
		$do->set('numero',$numero);
		$do->set('transac',$transac);

		return true;
	}

	function _pre_update($do){
		return true;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$mnumant = $this->datasis->fprox_numero('nancli');
		$numero  = $do->get('numero');
		$cod_cli = $do->get('cliente');
		$fecha   = $do->get('fecha');
		$monto   = $do->get('reserva');
		$transac = $do->get('transac');
		$usuario = $this->secu->usuario();
		$estampa = date('Y-m-d');
		$hora    = date('H:i:s');
		$inmueble= $do->get('inmueble');

		$dbcod_cli=$this->db->escape($cod_cli);
		$nombre =$this->datasis->dameval("SELECT nombre FROM scli WHERE cliente=$dbcod_cli");

		$data=array();
		$data['cod_cli']    = $cod_cli;
		$data['nombre']     = $nombre;
		$data['tipo_doc']   = 'AN';
		$data['numero']     = $mnumant;
		$data['fecha']      = $fecha;
		$data['monto']      = $monto;
		$data['impuesto']   = 0;
		$data['vence']      = $fecha;
		$data['tipo_ref']   = 'RS';
		$data['num_ref']    = $numero;
		$data['observa1']   = 'RESERVACION NRO. '.$numero;
		$data['usuario']    = $usuario;
		$data['estampa']    = $estampa;
		$data['hora']       = $hora;
		$data['transac']    = $transac;
		$data['fecdoc']     = $fecha;

		$mSQL = $this->db->insert_string('smov', $data);
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'edres'); }

		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
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
		if (!$this->db->table_exists('edres')) {
			$mSQL="CREATE TABLE `edres` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `numero` char(8) DEFAULT NULL,
			  `fecha` date DEFAULT NULL,
			  `cliente` char(5) DEFAULT NULL,
			  `edificacion` int(11) DEFAULT '0',
			  `inmueble` int(11) DEFAULT '0',
			  `reserva` decimal(17,2) DEFAULT '0.00',
			  `formap1` char(2) DEFAULT '0',
			  `banco1` char(3) DEFAULT '0',
			  `nummp1` varchar(20) DEFAULT '0',
			  `monto1` decimal(17,2) DEFAULT '0.00',
			  `formap2` char(2) DEFAULT '0',
			  `banco2` char(3) DEFAULT '0',
			  `nummp2` varchar(20) DEFAULT '0',
			  `monto2` decimal(17,2) DEFAULT '0.00',
			  `formap3` char(2) DEFAULT '0',
			  `banco3` char(3) DEFAULT '0',
			  `nummp3` varchar(20) DEFAULT '0',
			  `monto3` decimal(17,2) DEFAULT '0.00',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Reserva de Inmuebles'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('notas', 'edres')){
			$mSQL="ALTER TABLE `edres`  ADD COLUMN `notas` TEXT NULL DEFAULT NULL AFTER `monto3`";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('pfecha1', 'edres')){
			$mSQL="ALTER TABLE `edres`  ADD COLUMN `pfecha1` DATE NULL AFTER `monto1`,  ADD COLUMN `pfecha2` DATE NULL AFTER `monto2`,  ADD COLUMN `pfecha3` DATE NULL AFTER `monto3`";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('transac', 'edres')){
			$mSQL="ALTER TABLE `edres` ADD COLUMN `transac` VARCHAR(8) NULL AFTER `notas`";
			$this->db->simple_query($mSQL);
		}
	}
}
