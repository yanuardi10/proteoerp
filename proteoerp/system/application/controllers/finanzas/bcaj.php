<?php
class Bcaj extends Controller {
	function bcaj(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->guitipo=array('DE'=>'Depositos','TR'=>'Transferencia','RM'=>'Remesas');
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter('Filtro de cheques');
		$select=array('fecha','numero','nombre','CONCAT_WS(\'-\',banco ,numcuent) AS banco','tipo_op','codbanc','LEFT(concepto,20)AS concepto','anulado');
		$filter->db->select($select);
		$filter->db->from('bmov');
		$filter->db->where('tipo_op','CH');

		$filter->fecha = new dateonlyField('Fecha','fecha');
		$filter->fecha->size=10;
		$filter->fecha->operator='=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=20;

		$filter->nombre = new inputField('Nombre', 'nombre');
		$filter->nombre->size=40;

		$filter->banco = new dropdownField('Banco', 'codbanc');
		$filter->banco->option('','');
		$filter->banco->options('SELECT codbanc,banco FROM banc where tbanco<>\'CAJ\' ORDER BY codbanc');

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('finanzas/bmov/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>','<#numero#>');

		$grid = new DataGrid('Lista de cheques');
		$grid->order_by('numero','desc');
		$grid->per_page = 15;

		$grid->column('N&uacute;mero',$uri );
		$grid->column('Nombre'       ,'nombre');
		$grid->column('Banco'        ,'banco');
		$grid->column('Monto'        ,'<nformat><#monto#></nformat>' ,'align=right');
		$grid->column('Concepto'     ,'concepto');
		$grid->column('Anulado'      ,'anulado','align=center');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Cheques</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit($uriattr='paso1'){
		$this->rapyd->load('dataform');

		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});';

		$edit = new DataForm('finanzas/bcaj/dataedit/'.$uriattr);
		$edit->title='Deposito en caja';
		$edit->script($script, 'create');
		$edit->script($script, 'modify');

		$edit->back_url = site_url('finanzas/bcaj/index');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'chfecha|required';

		$edit->tipo = new dropdownField('Tipo', 'tipo');
		$edit->tipo->options($this->guitipo);
		$edit->tipo->rule = 'required';
		$edit->tipo->style = 'width:180px';

		$edit->submit('btnsubmit','Siguiente');
		$edit->build_form();

		if ($edit->on_success()){
			$arr['fecha'] = 'fecha';
			$salida=$this->_dataedit2($arr);
		}else{
			$edit->_process_uri='finanzas/bcaj/dataedit/paso1';
			$edit->build_form();
			$salida=$edit->output;
		}

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $salida;
		$data['title']   = '<h1>Deposito,transferencias y remesas</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _dataedit2($arr){
		$tipo=$this->input->post('tipo');
		if($tipo===FALSE) return 'Error de secuencia';

		foreach($arr AS $obj=>$titulo) $arr[$obj]=$this->input->post($obj);
		$arr['tipo']=$tipo;

		$edit = new DataForm('finanzas/bcaj/dataedit/paso2');
		$edit->title($this->guitipo[$tipo].' para la fecha '.$arr['fecha']);

		$edit->envia = new dropdownField('Envia','envia');
		$edit->envia->option('','Seleccionar');

		$edit->recibe = new dropdownField('Recibe','recibe');
		$edit->recibe->option('','Seleccionar');

		$desca='CONCAT_WS(\'-\',codbanc,banco) AS desca';
		if($tipo=='DE'){  //Depositos
			$edit->envia->options("SELECT  codbanc,$desca FROM banc WHERE tbanco='CAJ'");

			$edit->recibe->options("SELECT codbanc,$desca FROM banc WHERE tbanco<>'CAJ'");
			$edit->recibe->rule='callback_chtr|required';

			$campos=array(	'tarjeta' =>'T.Cr&eacute;dito',
					'tdebito' =>'T.Debito',
					'cheques' =>'Cheques',
					'efectivo'=>'Efectivo',
					'comision'=>'Comision',
					'islr'    =>'I.S.L.R');
			foreach($campos AS $obj=>$titulo){
				$edit->$obj = new inputField($titulo, $obj);
				$edit->$obj->css_class='inputnum';
				$edit->$obj->rule='trim|numeric';
				$edit->$obj->maxlength =15;
				$edit->$obj->size = 20;
				$edit->$obj->group = 'Montos';
			}

		}elseif($tipo=='TR'){ //Transferencias
			$link  = site_url('finanzas/bcaj/get_trrecibe');
			$script='
			function get_trrecibe(){
				$.post("'.$link.'",{ envia: $("#envia").val()}, function(data){
					//alert(data);
					$("#recibe").html(data);
				});
			}';

			$edit->script($script);

			$edit->envia->options("SELECT codbanc,$desca FROM banc ORDER BY banco");
			$edit->envia->onchange = 'get_trrecibe();';
			$edit->envia->rule     = 'required';

			$codigo=$this->input->post('envia');
			if($codigo!==false){
				$tipo= $this->_traetipo($codigo);
				$ww=($tipo=='CAJ') ? 'tbanco="CAJ"' : 'tbanco<>"CAJ"';
				$edit->recibe->options("SELECT codbanc,$desca FROM banc WHERE $ww AND codbanc<>".$this->db->escape($codigo)." ORDER BY banco");
			}else{
				$edit->recibe->option('','Seleccione una caja de envio');
			}
			$edit->recibe->rule  = 'required';

			$edit->monto = new inputField('Monto', 'monto');
			$edit->monto->css_class='inputnum';
			$edit->monto->rule='trim|numeric|required';
			$edit->monto->maxlength =15;
			$edit->monto->size = 20;

		}elseif($tipo=='RM'){ //Remesas
			$edit->recibe->options("SELECT codbanc,$desca FROM banc WHERE tbanco<>'CAJ'");
			$edit->recibe->rule  = 'required';

			$edit->envia->options("SELECT  codbanc,$desca FROM banc WHERE tbanco='CAJ'");

			$edit->monto = new inputField('Monto', 'monto');
			$edit->monto->css_class='inputnum';
			$edit->monto->rule='trim|numeric|required';
			$edit->monto->maxlength =15;
			$edit->monto->size = 20;
		}

		$edit->envia->rule   = 'required';
		$edit->envia->style  = 'width:180px';
		$edit->recibe->style = 'width:180px';

		$edit->container = new containerField('alert',form_hidden($arr));

		$back_url = site_url('finanzas/bcaj/dataedit/');
		$edit->button('btn_undo', 'Regresar', "javascript:window.location='${back_url}'", 'TR');

		$edit->submit('btnsubmit','Guardar');
		$edit->build_form();

		if ($edit->on_success()){
			//aqui es donde se deberia guardar el efecto
			
			
			
			
			
			
		}
		return $edit->output;
	}

	function get_trrecibe(){
		$codigo=$this->input->post('envia');
		echo "<option value=''>Seleccionar</option>";

		if($codigo!==false){
			$tipo= $this->_traetipo($codigo);

			if(!empty($tipo)){
				$ww=($tipo=='CAJ') ? 'tbanco="CAJ"' : 'tbanco<>"CAJ"';
				$desca='CONCAT_WS(\'-\',codbanc,banco) AS desca';
				$mSQL=$this->db->query("SELECT codbanc,$desca FROM banc WHERE $ww AND codbanc<>".$this->db->escape($codigo)." ORDER BY banco");
				if($mSQL){
					foreach($mSQL->result() AS $fila )
						echo "<option value='".$fila->codbanc."'>".$fila->desca."</option>";
				}
			}
		}
	}

	function _traetipo($codigo){
		$sql='SELECT tbanco FROM banc WHERE codbanc='.$this->db->escape($codigo);
		return $this->datasis->dameval($sql);
	}

	function chtr(){
		$recibe=$this->input->post('recibe');
	}
}