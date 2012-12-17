<?php
require_once(BASEPATH.'application/controllers/ventas/sfac.php');
class mensualidad extends sfac {

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

			ob_start();
				parent::dataedit();
				$rt = ob_get_contents();
			@ob_end_clean();

			$getdata=json_decode($rt);
			if($getdata['status']=='A'){
				echo "Registro guardado ".$getdata['pk']['id'];
			}else{
				echo $getdata['mensaje'];
			}

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
		}

		return true;
	}

	function lote($status=null,$grupo,$upago,$tarifa){
		$this->load->helper('download');
		$this->genesal=false;
		$this->back_url=$this->url.'filteredgrid';

		$dbgrupo = $this->db->escape($grupo);
		$dbupago = $this->db->escape($upago);
		$dbtarifa= $this->db->escape($tarifa);
		$data   = '';

		if($status=='insert'){
			$codigo = $this->datasis->traevalor('SINVTARIFA');
			$iva    = $this->datasis->dameval("SELECT iva FROM sinv WHERE codigo=".$this->db->escape($codigo));
			$descrip= $this->datasis->dameval("SELECT descrip FROM sinv WHERE codigo=".$this->db->escape($codigo));
			$ut     = $this->datasis->dameval("SELECT valor FROM utributa ORDER BY fecha DESC LIMIT 1");
			$cana   = 1;

			$mSQL="SELECT TRIM(a.nombre) AS nombre, TRIM(a.rifci) AS rifci, a.cliente, a.tipo , a.dire11 AS direc, round(b.minimo*$ut,2) precio1, a.upago, a.telefono, b.id codigo
				FROM scli AS a
				JOIN tarifa AS b ON a.tarifa=b.id
				WHERE a.grupo=$dbgrupo AND a.upago=$dbupago AND a.tarifa=$dbtarifa
				ORDER BY rifci";

			$query = $this->db->query($mSQL);
			foreach ($query->result() as $row){
				$dbcliente= $this->db->escape($row->cliente);
				$sql      = "SELECT SUM(monto*(tipo_doc IN ('FC','GI','ND'))) AS debe, SUM(monto*(tipo_doc IN ('NC','AB','AN'))) AS haber FROM smov WHERE cod_cli=$dbcliente";
				$qquery    = $this->db->query($sql);
				if ($qquery->num_rows() > 0){
					$rrow = $qquery->row();
					$saldo=$rrow->debe-$rrow->haber;
				}else{
					$saldo=0;
				}
				$saldo += $row->precio1*(1+($iva/100));
				$sql="UPDATE scli SET credito='S',tolera=10,maxtole=10,limite=$saldo,formap=30 WHERE cliente=$dbcliente";
				$this->db->simple_query($sql);

				$desde = dbdate_to_human($row->upago.'01','m/Y');

				$_POST['pfac']        = '';
				$_POST['fecha']       = date('d/m/Y');
				$_POST['cajero']      = $this->secu->getcajero();
				$_POST['vd']          = $this->secu->getvendedor();
				$_POST['almacen']     = $this->secu->getalmacen();
				$_POST['tipo_doc']    = 'F';
				$_POST['factura']     = '';
				$_POST['cod_cli']     = $row->cliente;
				$_POST['sclitipo']    = '1';
				$_POST['nombre']      = $row->nombre;
				$_POST['rifci']       = $row->rifci;
				$_POST['direc']       = $row->direc;
				$_POST['upago']       = $row->upago;
				$_POST['codigoa_0']   = $codigo;

				$_POST['desca_0']     = $descrip;

				$_POST['detalle_0']   = "Desde $desde";
				$_POST['cana_0']      = $cana;
				$_POST['preca_0']     = $row->precio1;

				$_POST['tota_0']      = $row->precio1;
				$_POST['precio1_0']   = 0;
				$_POST['precio2_0']   = 0;
				$_POST['precio3_0']   = 0;
				$_POST['precio4_0']   = 0;
				$_POST['itiva_0']     = round($iva,2);
				$_POST['sinvpeso_0']  = 0;
				$_POST['sinvtipo_0']  = 'Servicio';

				$_POST['tipo_0']       = '';
				$_POST['sfpafecha_0']  = '';
				$_POST['num_ref_0']    = '';
				$_POST['banco_0']      = '';
				$_POST['monto_0']      = $row->precio1*(1+($iva/100)) ;

				ob_start();
					parent::dataedit();
					$rt = ob_get_contents();
				@ob_end_clean();

				$getdata=json_decode($rt,true);

				if($getdata['status']=='A'){
					$id=$getdata['pk']['id'];
					$url=$this->_direccion='http://localhost/'.site_url('formatos/descargartxt/FACTSER/'.$id);
					$data .= file_get_contents($url);
					$data .= "<FIN>\r\n";
				}
			}
			force_download('inprin.prn', preg_replace("/[\r]*\n/","\r\n",$data));
		}
	}

	function factucred(){
		$this->rapyd->load('dataedit','dataform','datagrid');

		$form = new DataForm('ventas/mensualidad/factucred/modifica');

		$form->grupo = new dropdownField('Grupo', 'grupo');
		$form->grupo->option('','Seleccione un grupo');
		$form->grupo->options('SELECT grupo, CONCAT(grupo," ",gr_desc) gr_desc FROM grcl ORDER BY gr_desc');
		$form->grupo->rule = 'required';
		$form->grupo->size = 6;
		$form->grupo->maxlength = 4;
		$form->grupo->style = 'width:300px';
		//$form->grupo->insertValue = $this->datasis->dameval('SELECT grupo FROM grcl WHERE gr_desc like "CONSUMIDOR FINAL%"');

		$form->submit("btnsubmit","Consultar");
		$form->build_form();

		$tt='';
		if ($form->on_success()){
			$grupo  = trim($form->grupo->newValue);
			$dbgrupo= $this->db->escape($grupo);
			$ut     = $this->datasis->dameval("SELECT valor FROM utributa ORDER BY fecha DESC LIMIT 1");

			$link=anchor($this->url."lote/insert/$grupo/<#upago#>/<#tarifa#>",'Facturar Mes');
			$sel=array('COUNT(*) AS cana','CONCAT(a.upago,\'01\') AS pago','a.upago','TRIM(a.tarifa) AS tarifa','b.actividad',"SUM(b.minimo*$ut) AS monto");
			$grid = new DataGrid('Lista de efectos');
			$grid->db->select($sel);
			$grid->db->from('scli   AS a');
			$grid->db->join('tarifa AS b','a.tarifa=b.id');
			$grid->db->where('a.grupo',$grupo);
			$grid->db->where('a.upago IS NOT NULL');
			$grid->db->where('a.upago <>','');
			$grid->db->groupby('a.upago,a.tarifa');

			//$grid->column('Facturar'   );
			$grid->column_orderby('Cantidad'   , '<#cana#> '.$link ,'cana','align="right"');
			$grid->column_orderby('&Uacute;ltimo pago', '<dbdate_to_human><#pago#></dbdate_to_human>','upago','align="center"');
			$grid->column_orderby('Tarifa'     , '(<#tarifa#>) <#actividad#>','tarifa');
			$grid->column_orderby('Monto'      , '<nformat><#monto#></nformat>' ,'monto' ,'align="right"');
			$grid->build();

			$tt=$grid->output;
		}

		$data['content'] =$form->output.$tt;
		$data['head']    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head();
		$data['title']   = '<h1>Facturaci&oacute;n de servicio a cr&eacute;dito</h1>';
		$this->load->view('view_ventanas', $data);
	}
}
