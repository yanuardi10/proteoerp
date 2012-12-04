<?php
require_once(BASEPATH.'application/controllers/ventas/sfac_add.php');
class mensualidad extends sfac_add {

	var $titp='Mensualidad';
	var $tits='Cobro de servicios mensuales';
	var $url ='ventas/mensualidad/';

	function mensualidad(){
		parent::Controller();
		$this->back_dataedit='/datafilter';
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(216,1);
	}

	//Para facturar servicios por mes
	function servxmes($status){
		$this->genesal=false;
		$this->back_url=$this->url.'filteredgrid';

		$codigo = $this->datasis->traevalor('SINVTARIFA');
		$cliente = $this->input->post('cod_cli');
		$cana    = $this->input->post('cana_0');

		$sclir  = $this->datasis->damereg("SELECT * FROM scli WHERE cliente= ".$this->db->escape($cliente));
		$sinvr  = $this->datasis->damereg("SELECT * FROM sinv WHERE codigo = ".$this->db->escape($codigo));

		$campos = $this->db->list_fields('sfac');
		if (!in_array('upago',$campos)){
			$mSQL="ALTER TABLE `sfac` ADD COLUMN `upago` INT(10) NULL DEFAULT NULL COMMENT 'Fecha desde que se pago el servicio mensual' AFTER `maestra`;";
			$this->db->simple_query($mSQL);
		}

		if ($status=='insert'){

			$desde = dbdate_to_human($sclir['upago'].'01','m/Y');

			$tarifa= round($this->input->post('preca_0'),2);

			$_POST['pfac']        = '';
			$_POST['fecha']       = date('d/m/Y');
			$_POST['cajero']      = $this->secu->getcajero();
			$_POST['vd']          = $this->secu->getvendedor();
			$_POST['almacen']     = $this->secu->getalmacen();
			$_POST['tipo_doc']    = 'F';
			$_POST['factura']     = '';
			//$_POST['cod_cli']     = $row->cliente;
			$_POST['sclitipo']    = '1';
			$_POST['nombre']      = $sclir['nombre'];
			$_POST['rifci']       = $sclir['rifci'];
			$_POST['direc']       = $sclir['dire11'];
			$_POST['upago']       = $sclir['upago'];
			$_POST['codigoa_0']   = $codigo;

			$_POST['desca_0']     = $sinvr['descrip'];

			$_POST['detalle_0']   = "Desde $desde";

			//$_POST['cana_0']      = $cana;
			//$_POST['preca_0']     = $tarifa;

			$_POST['tota_0']      = $tarifa*$cana;
			$_POST['precio1_0']   = 0;
			$_POST['precio2_0']   = 0;
			$_POST['precio3_0']   = 0;
			$_POST['precio4_0']   = 0;
			$_POST['itiva_0']     = round($sinvr['iva'],2);
			$_POST['sinvpeso_0']  = 0;
			$_POST['sinvtipo_0']  = 'Servicio';

			//$_POST['tipo_0']     = $this->input->post('fcodigo');
			$_POST['sfpafecha_0']  = '';
			//$_POST['num_ref_0']  = $this->input->post('fcomprob');
			$_POST['banco_0']      = '';
			$_POST['monto_0']      = $_POST['tota_0']*(1+($sinvr['iva']/100)) ;

			echo utf8_encode(parent::dataedit());
		}
	}

	function _pre_insert($do){
		$rt=parent::_pre_insert($do);
		if($rt === false){
			return $rt;
		}

		$dbcliente = $this->db->escape($do->get('cod_cli'));
		$msql    = 'SELECT tarifa,upago FROM scli WHERE cliente='.$dbcliente;
		$row     = $this->datasis->damerow($msql);

		$upago   = trim($row['upago']).'01';
		$tarifa  = trim($row['tarifa']);
		if(empty($upago)) $upago=20120101;

		$ccana=$do->count_rel('sitems');
		$i = 0;

		$detalle = $do->get_rel('sitems','detalle',$i);
		$cana    = $do->get_rel('sitems','cana'   ,$i);

		$objdated = date_create(dbdate_to_human($upago,'Y-m-d'));
		$objdated->add(new DateInterval('P1M'));
		$desde   = date_format($objdated, 'm/Y');

		$objdate = date_create(dbdate_to_human($upago,'Y-m-d'));
		$objdate->add(new DateInterval('P'.$cana.'M'));
		$hasta   = date_format($objdate, 'm/Y');

		$this->_fhasta = date_format($objdate, 'Ym');
		$det     = "Desde $desde hasta $hasta";
		$do->set_rel('sitems','detalle',$det,$i);
	}

	function _post_insert($do){
		$rt = parent::_post_insert($do);

		if($rt === false){
			return $rt;
		}

		if(isset($this->_fhasta)){
			$dbcliente = $this->db->escape($do->get('cod_cli'));
			$dbupago   = $this->db->escape($this->_fhasta);
			$mSQL = "UPDATE scli SET upago=$dbupago WHERE cliente=$dbcliente";
			$this->db->simple_query($mSQL);
			echo 'Entre aqui';
		}

		return true;
	}

	/*function facturar($id=null){
		$this->rapyd->load('dataedit');
		$iva  = $this->datasis->ivaplica();

		$sel=array('a.nombre','a.rifci','b.codigo','b.descrip','b.base1','b.iva');
		$this->db->select($sel);
		$this->db->from('scli AS a');
		$this->db->join('sinv AS b','a.tarifa=b.codigo');
		$this->db->where('a.id',$id);

		$query = $this->db->get();

		if ($query->num_rows() > 0){
			$row = $query->row();

			$codigo  = $row->codigo;
			$rifci   = $row->rifci;
			$nombre  = $row->nombre;
			$descrip = $row->descrip;
			$base1   = round($row->base1,2);
			$tasa    = round($row->iva,2);
		}

		$edit = new DataForm($this->url.$id.'/insert');

		$edit->back_url = site_url($this->url.'filteredgrid');


		$edit->cliente = new freeField('Cliente', 'rif',$rifci.' - '.$nombre);
		$edit->cliente->group = 'Datos de la factura';

		$edit->codigo = new freeField('Producto', 'codigo',$codigo.' - '.$descrip);
		$edit->codigo->group = 'Datos del financieros';

		$edit->base =  new inputField('Monto base de venta','base');
		$edit->base->size  = 20;
		$edit->base->rule  = 'required|numeric';
		$edit->base->group = 'Datos del financieros';
		$edit->base->insertValue=$base1;

		$edit->cana =  new inputField('Cantidad','cana');
		$edit->cana->size  = 20;
		$edit->cana->rule  = 'required|numeric';
		$edit->cana->group = 'Datos del financieros';

		$edit->total = new freeField('Total a pagar', 'total','<span id="total"></span>');
		$edit->total->group = 'Datos del financieros';

		$accion="javascript:window.location='".site_url('concesionario/inicio')."'";
		$edit->button('btn_cargar','Regresar',$accion,'BL');
		$edit->submit('btnsubmit','Realizar venta');
		$edit->build_form();

		if($edit->on_success()){
			$this->genesal=false;

			$sel=array('a.rifci','a.dire11');
			$this->db->select($sel);
			$this->db->from('scli AS a');
			$this->db->where('a.cliente',$edit->cliente->newValue);

			$query = $this->db->get();
			if ($query->num_rows() > 0){
				$row    = $query->row();
				$rifci  = $row->rifci;
				$dire11 = $row->dire11;
			}

			$_POST['btn_submit']  = 'Guardar';
			$_POST['pfac']        = '';
			$_POST['fecha']       = date('d/m/Y');
			$_POST['cajero']      = $this->secu->getcajero();
			$_POST['vd']          = $edit->vd->newValue;
			$_POST['almacen']     = $edit->almacen->newValue;
			$_POST['tipo_doc']    = 'F';
			$_POST['factura']     = '';
			$_POST['cod_cli']     = $edit->cliente->newValue;
			$_POST['sclitipo']    = '1';
			$_POST['nombre']      = $edit->nombre->newValue;
			$_POST['rifci']       = $rifci ;
			$_POST['direc']       = $dire11;

			$_POST['codigoa_0']   = 'PLACA';
			$_POST['desca_0']     = 'PLACA';
			$_POST['detalle_0']   = 'PLACA '.$placa;
			$_POST['cana_0']      = 1;
			$_POST['preca_0']     = $precioplaca;
			$_POST['tota_0']      = $precioplaca;
			$_POST['precio1_0']   = $precioplaca;
			$_POST['precio2_0']   = $precioplaca;
			$_POST['precio3_0']   = $precioplaca;
			$_POST['precio4_0']   = $precioplaca;
			$_POST['itiva_0']     = 0;
			$_POST['sinvpeso_0']  = 0;
			$_POST['sinvtipo_0']  = 'Servicio';

			$_POST['codigoa_1']   = $codigo_sinv;
			$_POST['desca_1']     = $modelo;
			$_POST['detalle_1']   = '';
			$_POST['cana_1']      = 1;
			$_POST['preca_1']     = $edit->base->newValue;
			$_POST['tota_1']      = $edit->base->newValue;
			$_POST['precio1_1']   = $precio1;
			$_POST['precio2_1']   = $precio2;
			$_POST['precio3_1']   = $precio3;
			$_POST['precio4_1']   = $precio4;
			$_POST['itiva_1']     = $edit->tasa->newValue;
			$_POST['sinvpeso_1']  = $peso;
			$_POST['sinvtipo_1']  = 'Articulo';

			$totals = $precioplaca+$edit->base->newValue;
			$iva    = $edit->base->newValue*($edit->tasa->newValue/100);
			$totalg = $totals+$iva;

			$_POST['tipo_0']      = '';
			$_POST['sfpafecha_0'] = '';
			$_POST['num_ref_0']   = '';
			$_POST['banco_0']     = '';
			$_POST['monto_0']     = $totalg;

			$_POST['totals']      = $totals;
			$_POST['iva']         = $iva   ;
			$_POST['totalg']      = $totalg;

			$rt=$this->dataedit();
			if($rt=='Venta Guardada'){

				$data=array();
				$data['id_sfac'] = $this->claves['id'];
				$mSQL = $this->db->update_string('sinvehiculo', $data,'id='.$this->db->escape($id));
				$this->db->simple_query($mSQL);

				redirect($this->url.'dataprint/modify/'.$data['id_sfac']);
				return;
			}else{
				$edit->error_string =  htmlentities($rt);
				$edit->build_form();
			}
		}

		$script= '<script type="text/javascript" >
		$(function() {
			var calcula = function (){
				if($("#base").val().length>0) base=parseFloat($("#base").val()); else base=0;
				if($("#cana").val().length>0) cana=parseFloat($("#cana").val()); else cana=0;
				tasa='.$tasa.';
				$("#total").text(nformat(base*(1+(tasa/100)*cana),2));
			}

			$("#cana").keyup(calcula);

		});
		</script>';

		$data['content'] = $edit->output;
		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$data['script'] .= phpscript('nformat.js');
		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
		//$data['script']  = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$data['script'] .= $script;
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);

	}*/

}
