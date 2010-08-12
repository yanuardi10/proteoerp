<?php
class Exportar extends Controller {

	function Exportar(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->sucu=$this->datasis->traevalor('NROSUCU');
		$sucu = $this->db->escape($this->sucu);
		$this->prefijo = $this->datasis->dameval("SELECT prefijo FROM sucu WHERE codigo=$sucu");
	}

	function index(){

	}

//***********************
// Interfaces graficas
//***********************
	function ui($metodo=null){
		$obj='_'.$metodo; if(!method_exists($this,$obj)) show_404('page');
		$this->rapyd->load('dataform');

		$form = new DataForm("sincro/exportar/ui/$metodo/process");
		$form->fecha = new dateonlyField("Fecha","fecha");
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->rule ="required|chfecha";
		$form->fecha->size =12;
		$form->submit("btnsubmit","Descargar");
		$form->build_form();

		if ($form->on_success()){
			$fecha=$form->fecha->newValue;
			$this->$obj($fecha);
			return 0;
		}

		$data['content'] = $form->output;
		$data['title']   = '<h1>Exportar data a zip ('.$metodo.')</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function uig(){
		$this->rapyd->load('dataform');
		$this->datasis->modulo_id('91D',1);
		$sucu=$this->db->escape($this->sucu);

		$form = new DataForm("sincro/exportar/uig/process");

		$form->qtrae = new dropdownField("Que exportar?", "qtrae");
		$form->qtrae->rule ='required';
		$form->qtrae->option("","Selecionar");
		$form->qtrae->option("scli"  ,"Clientes");
		$form->qtrae->option("sinv"  ,"Inventario");
		$form->qtrae->option("maes"  ,"Inventario Supermercado");
		$form->qtrae->option("smov"  ,"Movimientos de clientes");
		$form->qtrae->option("transacciones","Facturas y transferencias");
		$form->qtrae->option("supertransa"  ,"Ventas Supermercado");
		$form->qtrae->option("rcaj"  ,"Cierres de cajas");
		$form->qtrae->option("fiscalz"  ,"Cierres Z");

		$form->fecha = new dateonlyField("Fecha","fecha");
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->rule ="required|chfecha";
		$form->fecha->size =12;
		$form->submit("btnsubmit","Descargar");
		$form->build_form();

		$exito='';
		if ($form->on_success()){
			$fecha=$form->fecha->newValue;
			$obj='_'.str_replace('_','',$form->qtrae->newValue);
			if(method_exists($this,$obj))
				$rt=$this->$obj($fecha);
			else
				$rt='Metodo no definido ('.$form->qtrae->newValue.')';
			if(strlen($rt)>0){
				$form->error_string=$rt;
				$form->build_form();
			}else{
				$exito='Transferencia &Eacute;xitosa';
			}
		}

		$data['content'] = $form->output.$exito;
		$data['title']   = '<h1>Exportar data de Sucursal</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);

	}

//***********************
//  Interfaces uri
//***********************
	function uri($clave,$metodo,$fecha=null){
		$obj='_'.$metodo; 
		if(!method_exists($this,$obj)) show_404('page');
		if($clave!=sha1($this->config->item('encryption_key'))) return false;
		
		/*$usr=$this->db->escape($usr);
		$pws=$this->db->escape($pws);
		$cursor=$this->db->query("SELECT us_nombre FROM usuario WHERE us_codigo=$usr AND SHA(us_clave)=$pws");
		if($cursor->num_rows()==0) return false;
		$existe = $this->datasis->dameval("SELECT COUNT(*) FROM intrasida WHERE usuario=$usr AND modulo='$id'");
		if ($existe==0 ) return  false;*/
		
		if (empty($fecha)){
			$dias=3;
			$fecha=date("Ymd",mktime(0,0,0,date('n'),date('j')-$dias,date('Y')));
		}elseif(!$this->__chekfecha($fecha)){
			return false;
		}
		
		$opcionales=array();
		for($i=7;$i<12;$i++){
			$opt=$this->uri->segment($i);
			if($opt!==false){
				$opcionales[]=$opt;
			}else break;
		}
		$this->$obj($fecha,$opcionales);
	}

//***********************
//  Metodos de Chequeo
//***********************
	function __chekfecha($fecha){
		if(is_numeric($fecha) AND $fecha>10000000){
			$anio=substr($fecha,0,4);
			$mes =substr($fecha,4,2);
			$dia =substr($fecha,6);
			if(checkdate($mes,$dia,$anio))
				return TRUE;
		}
		return FALSE;
	}

//***********************
// Metodos para exportar
//***********************
	function _sinv($fecha,$opt=null){
		set_time_limit(600);
		$this->load->library("sqlinex");
		$data[]=array('table' => 'dpto');
		$data[]=array('table' => 'line');
		$data[]=array('table' => 'grup');
		$data[]=array('table' => 'sinv');
		$data[]=array('table' => 'marc');
		$data[]=array('table' => 'itsinv');
		$fecha=date('d-m-Y');
		
		$nombre='sinv_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}

	function _transacciones($fecha,$opt=null){
		set_time_limit(600);
		$this->load->library("sqlinex");

		$sucu     = $this->db->escape($this->sucu);
		$pre_caja = $this->datasis->dameval("SELECT prefijo FROM sucu WHERE codigo=$sucu");
		$cant     = strlen($pre_caja);
		$pre_caja = $this->db->escape($pre_caja);

		$this->load->library("sqlinex");

		$data[]=array('select' =>'tipo_doc,numero,fecha,vence,vd,cod_cli,rifci,nombre,direc,dire1,orden,referen,iva,inicial,totals,totalg,status,observa,observ1,devolu,cajero,almacen,peso,factura,pedido,usuario,estampa,hora,transac,nfiscal,zona,ciudad,comision,pagada,sepago,dias,fpago,comical,exento,tasa,reducida,sobretasa,montasa,monredu,monadic,notcred,fentrega,fpagom,fdespacha,udespacha,numarma,maqfiscal,dmaqfiscal',
				'distinc'=>false,
				'table'  =>'sfac',
				'where'  =>"fecha >= $fecha AND MID(numero,1,$cant)=$pre_caja");
		$data[]=array('select' =>'tipoa,numa,codigoa,desca,cana,preca,tota,iva,fecha,vendedor,costo,pos,pvp,comision,cajero,mostrado,usuario,estampa,hora,transac,despacha,flote,precio4,detalle,fdespacha,udespacha,combo,descuento',
				'distinc'=>false,
				'table'  =>'sitems',
				'where'  =>"fecha >= $fecha AND MID(numa  ,1,$cant)=$pre_caja");
		$data[]=array('select' =>'tipo_doc,numero,tipo,monto,num_ref,clave,fecha,banco,f_factura,cod_cli,vendedor,cobrador,status,cobro,cambio,almacen,transac,usuario,estampa,hora',
				'distinc'=>false,
				'table'  =>'sfpa',
				//'where'  =>"fecha = $fecha AND MID(numero,1,$cant)=$pre_caja AND tipo_doc IN ('FE','DE','AN')");
				'where'  =>"fecha >= $fecha AND MID(numero,1,$cant)=$pre_caja");
		/*$data[]=array('distinc'=>false,
				'table'  =>'fiscalz',
				'where'  =>"fecha >= $fecha");*/
		
		$data[]=array('select'=>'numero,fecha,envia,recibe,observ1,observ2,totalg,tratot,estampa,hora,usuario,transac,gasto,numeen,numere',
				'distinc'=>false,
				'table'  =>'stra',
				'where'  =>"fecha >= $fecha AND MID(numero,1,$cant)=$pre_caja");
		$data[]=array('select' =>'itstra.numero,itstra.codigo,itstra.descrip,itstra.cantidad,itstra.precio1,itstra.precio2,itstra.precio3,itstra.precio4,itstra.iva,itstra.anteri,itstra.costo',
				'distinc'=>false,
				'table'  =>'itstra',
				'where'  =>"MID(numero,1,$cant)=$pre_caja");
		
		$data[]=array(
			'distinc'   =>true,
			'select'    =>'itccli.numccli, itccli.tipoccli, itccli.cod_cli, itccli.tipo_doc, itccli.numero, itccli.fecha, itccli.monto, itccli.abono, itccli.ppago, itccli.reten, itccli.cambio, itccli.mora, itccli.transac, itccli.estampa, itccli.hora, itccli.usuario, itccli.reteiva, itccli.nroriva, itccli.emiriva, itccli.recriva',
			'table'     =>'itccli',
			'join'      =>array(
					0 => array(
						'table'=>'smov',
						'on'=>'smov.transac=itccli.transac'),
					1 => array(
						'table'=>'sfpa',
						'on'=>'sfpa.transac=itccli.transac')
					),
			'where'   =>"itccli.fecha>=$fecha AND MID(itccli.transac,1,$cant)='".$this->prefijo."'"
		);

		$data[]=array('table' => 'smov',
				'where' => "estampa >= $fecha AND MID(transac,1,$cant)='".$this->prefijo."'"
				);
		
		
		$nombre='ve_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}

	function _scli($fecha,$opt=null){
		set_time_limit(600);
		$this->load->library("sqlinex");
		$this->sqlinex->ignore   =TRUE;
		$this->sqlinex->limpiar  =FALSE;
		$data[]=array('select' => 'cliente,nombre,grupo,gr_desc,nit,cuenta,formap,tipo,limite,socio,contacto,dire11,dire12,ciudad1,dire21,dire22,ciudad2,telefono,telefon2,zona,pais,email,vendedor,porvend,cobrador,porcobr,repre,cirepre,ciudad,separa,copias,regimen,comisio,porcomi,rifci,observa,fecha1,fecha2,tiva,clave,nomfis,riffis,mensaje,modifi',
				'table'  =>'scli',
				'where'  =>"modifi>=$fecha");

		$data[]=array('table' => 'grcl');

		$nombre='scli_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}

	/*function _smov($fecha,$opt=null){
		set_time_limit(600);
		$this->load->library("sqlinex");
		$this->sqlinex->ignore   =TRUE;
		$this->sqlinex->limpiar  =FALSE;
		
		$cant = strlen($this->prefijo);
		
		$data[]=array('table' => 'smov',
				'where' => "estampa >= $fecha AND tipo_doc='FC'");

		$nombre='smov_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,'smov_'.$this->sucu,$this->sucu);
	}*/


	function _smov($fecha,$opt=null){
		set_time_limit(600);
		$this->load->library("sqlinex");
		$cant = strlen($this->prefijo);

		$data[]=array(
			'distinc'   =>true,
			'select'    =>'itccli.numccli, itccli.tipoccli, itccli.cod_cli, itccli.tipo_doc, itccli.numero, itccli.fecha, itccli.monto, itccli.abono, itccli.ppago, itccli.reten, itccli.cambio, itccli.mora, itccli.transac, itccli.estampa, itccli.hora, itccli.usuario, itccli.reteiva, itccli.nroriva, itccli.emiriva, itccli.recriva',
			'table'     =>'itccli',
			'join'      =>array(
					0 => array(
						'table'=>'smov',
						'on'=>'smov.transac=itccli.transac'),
					1 => array(
						'table'=>'sfpa',
						'on'=>'sfpa.transac=itccli.transac')
					),
			'where'   =>"itccli.fecha=$fecha AND MID(itccli.transac,1,$cant)='".$this->prefijo."'"
		);

/*		$data[]=array(
			'distinc'   =>true,
			'select'    =>'sfpa.*',
			'table'     =>'sfpa',
			'join'      =>array(
					0 => array(
						'table'=>'itccli',
						'on'=>'itccli.transac=sfpa.transac'),
					1 => array(
						'table'=>'smov',
						'on'=>'smov.transac=sfpa.transac')
					),
			'where'   =>"sfpa.fecha=$fecha AND MID(sfpa.transac,1,$cant)='".$this->prefijo."' AND sfpa.tipo_doc NOT IN ('FE','DE','AN')"
		);*/

		$data[]=array(
			'distinc'   =>true,
			'select'    =>'smov.*',
			'table'     =>'smov',
			'join'      =>array(
					0 => array(
						'table'=>'itccli',
						'on'=>'itccli.transac=smov.transac'),
					1 => array(
						'table'=>'sfpa',
						'on'=>'sfpa.transac=smov.transac')
					),
			'where'   =>"smov.fecha=$fecha AND MID(smov.transac,1,$cant)='".$this->prefijo."'"
			//'wherejoin' =>"ittran.recibe=$dbalma"
		);

		$nombre='smov_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}


	function _fiscalz($fecha,$opt=null){
		set_time_limit(600);
		$this->load->library("sqlinex");
		$this->sqlinex->ignore   =TRUE;
		$this->sqlinex->limpiar  =FALSE;
		$data[]=array('table' => 'fiscalz',
				'where' => "fecha >= $fecha");

		$nombre='fiscalz_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}

	//Para supermercado
	function _supertransa($fecha,$opt=null){
		set_time_limit(600);
		$this->load->library("sqlinex");

		$sucu=$this->datasis->traevalor('NROSUCU');
		$pre_caja=$this->prefijo;
		$cant=strlen($pre_caja);
		$pre_caja=$this->db->escape($this->prefijo);

		$this->load->library("sqlinex");

		$data[]=array('distinc'=>false,
		              'table'  =>'viefac',
		              'where'  =>"fecha = $fecha AND MID(caja,1,$cant)=$pre_caja");
		$data[]=array('distinc'=>false,
		              'table'  =>'vieite',
		              'where'  =>"fecha = $fecha AND MID(caja,1,$cant)=$pre_caja");
		$data[]=array('distinc'=>false,
		              'table'  =>'viepag',
		              'where'  =>"f_factura = $fecha AND MID(caja,1,$cant)=$pre_caja");
		$data[]=array('distinc'=>false,
		              'table'  =>'fiscalz',
		              'where'  =>"fecha = $fecha AND MID(caja,1,$cant)=$pre_caja");

		$nombre='supertransa_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}

	function _maes($fecha,$opt=null){
		$this->load->library("sqlinex");
		$data[]=array('table'  =>'dpto');
		$data[]=array('table'  =>'fami');
		$data[]=array('table'  =>'grup');
		$data[]=array('table'  =>'maes');
		$data[]=array('table'  =>'ubica');
		$fecha=date('d-m-Y');

		$nombre='maes_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}

	function _rcaj($fecha,$opt=null){
		set_time_limit(600);
		//$prefijo=str_pad($this->prefijo,8,'0');
		$prefijo=$this->prefijo;
		$cant=strlen($this->prefijo)+1;
		$this->load->library("sqlinex");
		$this->sqlinex->ignore   =TRUE;
		$this->sqlinex->limpiar  =FALSE;
		$data[]=array('table' => 'rcaj',
		                'select'=>"fecha,cajero,tipo,usuario,caja,recibido,ingreso,parcial,observa, CONCAT('$prefijo',MID(numero,$cant)) AS numero ,transac,estampa,hora",
		                'where' => "fecha >= $fecha");

		$nombre='rcaj_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}

//***********************
// Metodos dependientes 
//     del almacen
//***********************
	function _maesalma($fecha,$opt=null){
		if(empty($opt)) return false;
		$this->load->library("sqlinex");
		$dbalma=$this->db->escape($opt[0]);
		$data[]=array('table'  =>'dpto');
		$data[]=array('table'  =>'fami');
		$data[]=array('table'  =>'grup');
		$data[]=array(
			'distinc'   =>true,
			'select'    =>'maes.*',
			'table'     =>'maes',
			'join'      =>array(0 => array('table'=>'ittran','on'=>'ittran.codigo=maes.codigo')),
			//'wherejoin' =>"ittran.fecha>=$fecha AND ittran.recibe=$dbalma"
			'wherejoin' =>"ittran.recibe=$dbalma"
		);
		$fecha=date('d-m-Y');

		$nombre='maesalma_'.$opt[0].'_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}

	function _tranalma($fecha,$opt=null){
		if(empty($opt)) return false;
		$this->load->library("sqlinex");
		$this->sqlinex->ignore   =TRUE;
		$this->sqlinex->limpiar  =FALSE;

		$dbalma=$this->db->escape($opt[0]);
		$data[]=array('table'  =>'ittran',
			'where'=>"recibe=$dbalma AND fecha>=$fecha"
		);
		$data[]=array('table'  =>'tran',
			'where'=>"recibe=$dbalma AND fecha>=$fecha"
		);

		$nombre='tranalma_'.$opt[0].'_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}

	function _ubicalma($fecha,$opt=null){
		if(empty($opt)) return false;
		$this->load->library("sqlinex");
		//$this->sqlinex->ignore   =TRUE;
		//$this->sqlinex->limpiar  =FALSE;

		$dbalma=$this->db->escape($opt[0]);
		$data[]=array('table'  =>'ubic',
			'where'=>"ubica=$dbalma"
		);

		$nombre='ubicalma_'.$opt[0].'_'.$fecha.'_'.$this->sucu;
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl';
		$this->sqlinex->exportunbufferzip($data,$nombre,$this->sucu);
	}
}
