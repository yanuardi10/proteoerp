<?php
class exportar extends Controller {

	function exportar(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){

	}

	function extransac(){
		$this->rapyd->load('dataform');
		$this->load->library('Sqlinex');

		$form = new DataForm("ventas/exportar/extransac/process");

		$form->fecha = new dateonlyField("Fecha","fecha");
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->rule ="require|chfecha";
		$form->fecha->size =12;

		$form->submit("btnsubmit","Descargar");
		$form->build_form();

		if ($form->on_success()){
			$fecha=$form->fecha->newValue;
			$this->_transacciones($fecha);
		}

		$data['content'] = $form->output;
		$data['title']   = '<h1>Exportar ventas a SQL</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function transacciones($fecha=null){
		if(is_numeric($fecha) AND $fecha>10000000){
			$anio=substr($fecha,0,4);
			$mes =substr($fecha,4,2);
			$dia =substr($fecha,6,2);
			if(checkdate($mes,$dia,$anio)){
				if(!array_key_exists('HTTP_USER_AGENT', $_SERVER))
					$_SERVER['HTTP_USER_AGENT']='curl';

				$this->_transacciones($fecha);
			}
		}
	}

	function _transacciones($fecha=null){
		set_time_limit(600);
		if(empty($fecha)) return 1;

		$ssucu     = $this->datasis->traevalor('NROSUCU');
		$sucu     = $this->db->escape($ssucu);
		$pre_caja = $this->datasis->dameval("SELECT prefijo FROM sucu WHERE codigo=$sucu");
		$cant     = strlen($pre_caja);
		$pre_caja = $this->db->escape($pre_caja);

		$this->load->library("sqlinex");

		$data[]=array('select' =>'tipo_doc,numero,fecha,vence,vd,cod_cli,rifci,nombre,direc,dire1,orden,referen,iva,inicial,totals,totalg,status,observa,observ1,devolu,cajero,almacen,peso,factura,pedido,usuario,estampa,hora,transac,nfiscal,zona,ciudad,comision,pagada,sepago,dias,fpago,comical,exento,tasa,reducida,sobretasa,montasa,monredu,monadic,notcred,fentrega,fpagom,fdespacha,udespacha,numarma,maqfiscal,dmaqfiscal',
									'distinc'=>false,
									'table'  =>'sfac',
									'where'  =>"fecha = $fecha AND MID(numero,1,$cant)=$pre_caja");
		$data[]=array('select' =>'tipoa,numa,codigoa,desca,cana,preca,tota,iva,fecha,vendedor,costo,pos,pvp,comision,cajero,mostrado,usuario,estampa,hora,transac,despacha,flote,precio4,detalle,fdespacha,udespacha,combo,descuento',
									'distinc'=>false,
									'table'  =>'sitems',
									'where'  =>"fecha = $fecha AND MID(numa  ,1,$cant)=$pre_caja");
		$data[]=array('select' =>'tipo_doc,numero,tipo,monto,num_ref,clave,fecha,banco,f_factura,cod_cli,vendedor,cobrador,status,cobro,cambio,almacen,transac,usuario,estampa,hora',
									'distinc'=>false,
									'table'  =>'sfpa',
									'where'  =>"fecha = $fecha AND MID(numero,1,$cant)=$pre_caja");
		$data[]=array('distinc'=>false,
									'table'  =>'fiscalz',
									'where'  =>"fecha = $fecha");
		$data[]=array('select'=>'numero,fecha,envia,recibe,observ1,observ2,totalg,tratot,estampa,hora,usuario,transac,gasto,numeen,numere',
									'distinc'=>false,
									'table'  =>'stra',
									'where'  =>"fecha = $fecha AND MID(numero,1,$cant)=$pre_caja");
		$data[]=array('select' =>'numero,codigo,descrip,cantidad,precio1,precio2,precio3,precio4,iva,anteri,costo',
									'distinc'=>false,
									'table'  =>'itstra',
									'where'  =>"MID(numero,1,$cant)=$pre_caja");

		$this->sqlinex->exportzip($data,'ve'.$fecha.'_'.$ssucu,$ssucu);
	}

	function exsinv(){
		$this->load->library("sqlinex");
		$sucu=$this->datasis->traevalor('NROSUCU');
		$data[]=array('table'  =>'dpto');
		$data[]=array('table'  =>'line');
		$data[]=array('table'  =>'grup');
		$data[]=array('table'  =>'sinv');
		$data[]=array('table'  =>'marc');
		$data[]=array('table'  =>'itsinv');
		$fecha=date('d-m-Y');
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER))
			$_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportzip($data,'invent'.$fecha,$sucu);
	}
}
?>