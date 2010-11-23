<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Rcaj extends validaciones {

	function Rcaj(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->load->library("menues");
		$this->datasis->modulo_id('12A',1);
		$this->load->database();
	}

	function index(){
		redirect("ventas/rcaj/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$atts = array(
			'width'      => '800',
			'height'     => '600',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '0',
			'screeny'    => '0');

		$titulo  = anchor_popup('supermercado/lresumen', ' Ver Resumen de caja',$atts)." <---> ";
		$titulo .= anchor_popup('supermercado/lresumen/indext',' Ver Resumen de todas las cajas',$atts);
		$recep = anchor('ventas/rcaj/forcierre/',"Recepcion de Caja");
		//$filter = new DataFilter($titulo);
		$filter = new DataFilter("Filtro");
		$filter->fecha = new dateonlyField("Fecha","b.fecha","d/m/Y");
		$filter->fecha->size =11;
		$filter->fecha->clause="where";
		$filter->fecha->operator="=";
		$filter->fecha->insertValue=date("Y-m-d");

		$filter->cajero = new dropdownField("Cajero", "b.cajero");
		$filter->cajero->option("","Todos");
		$filter->cajero->options('SELECT cajero, nombre FROM scaj ORDER BY nombre');

		$filter->buttons("reset","search");
		$filter->build();

		$data['content'] = $filter->output;

		function iconcaja($cajero,$fecha,$numero='',$tipo=''){
			$cajero=trim($cajero);
			$fecha =trim($fecha);
			$numero=trim($numero);

			$CI =& get_instance();
			$cerrado = $CI->datasis->dameval("SELECT numero FROM rcaj WHERE cajero='$cajero' AND fecha='$fecha' ");
			$atts=array('align'=>'LEFT','border'=>'0');
			$fecha=str_replace('-','',$fecha);
			$atRI = array(
				'width'     => '800','height' => '600',
				'scrollbars'=> 'yes','status' => 'yes',
				'resizable' => 'yes','screenx'=> '0',
				'screeny'   => '0');
			if (!empty($cerrado)){
				if($tipo=='T')
					return image('caja_precerrada.gif',"Cajero Pre-Cerrado: $cajero",$atts).'<h3>'.anchor("ventas/rcaj/forcierre/$numero/", 'Cerrar cajero').'</h3><center>'.anchor('formatos/ver/RECAJA/'.$numero, ' Ver cuadre de caja');
				return image('caja_cerrada.gif',"Cajero Cerrado: $cajero",$atts).'<h3>Cerrado</h3><center>'.anchor('formatos/ver/RECAJA/'.$numero, ' Ver cuadre de caja');
			}else{
				return image('caja_abierta.gif',"Cajero Abierto: $cajero",$atts).'<h3>Abierto</h3><center>'.anchor("ventas/rcaj/precierre/99/$cajero/$fecha", 'Pre-cerrar cajero').'</center>';
			}
		}

		$data['forma'] ='';
		if($this->rapyd->uri->is_set('search') AND !empty($filter->fecha->value)){
			$fecha=$filter->fecha->value;

			$urip = anchor('formatos/ver/RECAJA/<#numero#>','Descargar html');
			$urih = anchor_popup('formatos/verhtml/RECAJA/<#numero#>', ' Ver cuadre pantalla',$atts);
			//anchor('formatos/ver/RECAJA/<#numero#>'    ,'Descargar pdf');
			$grid = new DataGrid('Lista de Cierres de caja');
			$grid->order_by('fecha','desc');
			$grid->per_page=15;

			$grid = new DataGrid('Recepcion de cajas para la fecha: '.$filter->fecha->value);
			$select=array('b.cajero','b.fecha','a.tipo','b.cajero','a.recibido','SUM(b.totalg) AS ingreso','a.numero');
			//$select=array('b.cajero','b.fecha','b.cajero','b.recibido','b.numero');

			$grid->db->select($select);
			//$grid->db->from('rcaj as b');
			$grid->db->from('sfac as b');
			$grid->db->join('rcaj as a','a.cajero=b.cajero AND a.fecha=b.fecha','LEFT');
			$grid->db->groupby('b.cajero');
			$grid->use_function('iconcaja');

			$grid->column('Numero'     ,'<sinulo><#numero#>|Caja abierta</sinulo>','align=\'center\'');
			//$grid->column('Tipo'       ,'<#tipo#>','align=\'center\'');
			$grid->column('Fecha'      ,'<dbdate_to_human><#fecha#></dbdate_to_human>');
			$grid->column('Cajero'     ,'cajero','align=\'center\'');
			$grid->column('Recibido'   ,'<sinulo><nformat><#recibido#></nformat>|0.00</sinulo>','align=\'right\'');
			$grid->column('Ingreso'    ,'<nformat><#ingreso#></nformat>' ,'align=\'right\'');
			$grid->column('Status/Caja','<iconcaja><#cajero#>|<#fecha#>|<#numero#>|<#tipo#></iconcaja>','align="center"');
			$grid->column('Ver html'   ,$urih,'align=\'center\'');
			$grid->build();
			//echo $grid->db->last_query();
			$data['content'] .= $grid->output;
		}
		
		$data['title']   = '<h1>Recepci&oacute;n de cajas</h1>';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


	function precierre($caja=NULL,$cajero=NULL,$fecha=NULL){

		//Para cuando venga de datasis y sin parametros
		if(is_null($caja) || is_null($cajero) || is_null($fecha)){
			$usuario = $this->session->userdata('usuario');
			$caja    = '99';
			$mSQL    = 'SELECT cajero FROM usuario WHERE us_codigo= ?';
			$query   = $this->db->query($mSQL,array($usuario));
			$rrow    = $query->first_row();
			$cajero  = $rrow->cajero;
			$fecha   = date('Ymd');
			if(empty($cajero)) return;
			$redir=false;
		}else{
			$redir=true;
		}

		$this->rapyd->load('dataform');
		$cana=$this->datasis->dameval('SELECT COUNT(*) FROM rcaj WHERE cajero='.$this->db->escape($cajero).' AND fecha='.$this->db->escape($fecha));
		if($cana>0){
			$data['content'] = 'Cajero '.$cajero.' ya fue cerrado para la fecha '.dbdate_to_human($fecha).' ';
			if($redir) $data['content'] .= anchor('ventas/rcaj/filteredgrid/search','Regresar');
			$data['title']   = '<h1>Recepci&oacute;n de cajas</h1>';
			$data['head']    = $this->rapyd->get_head().script('jquery.pack.js');
			$this->load->view('view_ventanas', $data);
			return ;
		}

		$form = new DataForm("ventas/rcaj/precierre/$caja/$cajero/$fecha/process");

		$attr=array(
			'class'  => 'ui-state-default ui-corner-all',
			'onclick'=> "javascript:window.location='".site_url('ventas/rcaj/filteredgrid')."'",
			'value'  => 'Regresar'
		);

		//Inicio del efectivo
		$denomi=array();
		$c_efe=0;
		$query = $this->db->query('SELECT a.tipo,a.denomina,b.cambiobs*a.denomina equivalencia,a.nombre FROM monebillet a JOIN mone b ON a.moneda=b.moneda ORDER BY a.tipo,a.moneda,a.denomina DESC');
		foreach ($query->result() as $i=>$row){
			$arr=array('EFE','cEFE');
			$nn=$arr[0];
			$denomi[$nn]=$row->denomina;
			$c_efe++;
			foreach($arr AS $o=>$nobj){
				$obj = $nobj.$i;
				$denomi[$obj]=$row->denomina;
				$form->$obj = new inputField($row->nombre, $obj);
				$form->$obj->group=($row->tipo=='BI') ? 'Billetes': 'Monedas';
				$form->$obj->size=5+5*$o;
				$form->$obj->style='text-align:right';
				$form->$obj->rule='numeric';
				if($o==1){
					$form->$obj->in=$sobj;
					$form->$obj->readonly=true;
				}else{
					$form->$obj->css_class='cefectivo';
				}
				$sobj=$obj;
			}
		}
		$arr=array('OEFE'=>'Otras denominaciones','FEFE'=>'Fondo de caja (-)');
		foreach($arr AS $obj=>$titulo){
			$form->$obj = new inputField($titulo, $obj);
			$form->$obj->style='text-align:right';
			$form->$obj->css_class='efectivo';
			$form->$obj->rule='numeric';
			$form->$obj->size=10;
		}
		//Fin del efectivo

		//Inicio otras formas de pago
		$c_otrp=0;
		$mSQL='SELECT a.tipo,a.nombre FROM tarjeta a WHERE a.tipo NOT IN (\'EF\',\'NC\',\'ND\', \'DE\',\'IR\',\'DP\')';
		$query = $this->db->query($mSQL);
		foreach ($query->result() as $i=>$row){
			$c_otrp++;
			$arr=array('OTR','cOTR');
			foreach($arr AS $o=>$nobj){
				$obj = $nobj.$row->tipo;
				$form->$obj = new inputField($row->nombre, $obj);
				$form->$obj->style='text-align:right';
				$form->$obj->size=5+5*$o;
				$form->$obj->rule='numeric';
				if($o==1){
					$form->$obj->in=$sobj;
				}else{
					$form->$obj->css_class='cotrasf';
					$form->$obj->indice   = $row->tipo;
				}
				$sobj=$obj;
			}
		}
		//Fin otras formas de pago

		//Inicio tabla Resumen
		$arr=array('TOTR'=>'Total de otras formas de pago','TEFE'=>'Total Efectivo','TGLOB'=>'Total Global','UFAC'=>'Ultima Factura');
		foreach($arr AS $obj=>$titulo){
			$form->$obj = new inputField($titulo, $obj);
			$form->$obj->style='text-align:right';
			$form->$obj->css_class='efectivo';
			$form->$obj->rule='numeric';
			$form->$obj->readonly=true;
			$form->$obj->size=10;
		}
		
		$form->$obj->readonly=false;
		//$form->$obj->rule='required';
		$form->$obj->insertValue='';
		//fin Resumen

		$form->submit('btnsubmit','Cerrar cajero');
		$form->build_form();

		$this->rapyd->jquery[]='var denomi='.json_encode($denomi).';';
		$this->rapyd->jquery[]='$(":input").numeric(".");';
		$this->rapyd->jquery[]='$(\'input[name^="cEFE"]\').bind("keyup",function() { gtotal(); });';
		$this->rapyd->jquery[]='$(\'input[name^="cOTR"]\').bind("keyup",function() { gtotal(); });';
		$this->rapyd->jquery[]='$(\'input[name^="FEFE"]\').bind("keyup",function() { gtotal(); });';
		$this->rapyd->jquery[]='$(\'input[name^="OEFE"]\').bind("keyup",function() { gtotal(); });';
		$this->rapyd->jquery[]='$(".cefectivo").bind("keyup",
			function() {
				obj=this.name;
				mul=eval("denomi."+obj);
				valor=$(this).val();
				$("#c"+obj).val(mul*valor);
				gtotal();
			});';
		$this->rapyd->jquery[]='function gtotal(){
			TEFE=TOTR=0;
			$(\'input[name^="cEFE"]\').each(function(i,e){
				if($(this).val().length>0)    TEFE = TEFE+parseFloat($(this).val());
			});
			$(\'input[name^="cOTR"]\').each(function(i,e){
				if($(this).val().length>0)
					TOTR = TOTR+parseFloat($(this).val());
			});
			if($("#OEFE").val().length>0) TEFE=TEFE+parseFloat($("#OEFE").val());
			if($("#FEFE").val().length>0) TEFE=TEFE-parseFloat($("#FEFE").val())

			$("#TEFE").val(TEFE);
			$("#TOTR").val(TOTR);
			$("#TGLOB").val(TOTR+TEFE);
		}';

		//hace el precierre
		if ($form->on_success()){
			$dbfecha  = $this->db->escape($fecha);
			$dbcajero = $this->db->escape($cajero);

			$mSQL="SELECT c.tipo, IFNULL(aa.monto,0) AS monto FROM
				(SELECT b.tipo ,SUM(IF(a.tipo_doc='D',-1,1)*b.monto) AS monto 
				FROM sfac AS a 
				JOIN sfpa AS b ON a.transac=b.transac 
				WHERE a.fecha=$dbfecha AND a.cajero=$dbcajero
				GROUP BY b.tipo) AS aa
				RIGHT JOIN tarjeta AS c ON aa.tipo=c.tipo";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$str='';
				$ingreso= $rrecibido=$parcial=0;
				$numero = $this->datasis->fprox_numero('ningreso');
				$transac= $this->datasis->fprox_numero('transac');
				$arr    = array('numero'=>$numero);

				foreach ($query->result() as $row){
					if($row->tipo == 'EF'){
						$nobj='TEFE';
					}else{
						$nobj='cOTR'.$row->tipo;
					}

					$recibido = (isset($form->$nobj))? (empty($form->$nobj->newValue))? 0.00 :floatval($form->$nobj->newValue) : 0.00;
					if($row->monto>0 || $recibido>0){
						$str.= $row->tipo.' '.$recibido.'  ';
						$arr['tipo']       = $row->tipo;
						$arr['recibido']   = $recibido;
						$arr['sistema']    = $row->monto;
						$arr['diferencia'] = $recibido-$row->monto;
						$ingreso   += $row->monto;
						$rrecibido += $recibido;
						$mSQL = $this->db->insert_string('itrcaj', $arr);
						$this->db->simple_query($mSQL);
					}
				}
				$arr = array(
					'numero'  => $numero,
					'transac' => $transac,
					'fecha'   => $fecha,
					'cajero'  => $cajero,
					'caja'    => $caja,
					'observa' => $str,
					'usuario' => $this->session->userdata('usuario'),
					'tipo'    => 'T',
					'recibido'=> $rrecibido,
					'ingreso' => $ingreso,
					'parcial' => $parcial
				);
				$mSQL = $this->db->insert_string('rcaj', $arr);
				$this->db->simple_query($mSQL);

				$dbnumero=$this->db->escape($numero);
				$mSQL="UPDATE sfac JOIN sfpa ON sfac.transac=sfpa.transac SET sfpa.cierre=$dbnumero
				WHERE sfac.fecha=$dbfecha AND sfac.cajero=$dbcajero";
			}
			if($redir)
				redirect('ventas/rcaj/filteredgrid/search');
			else
				redirect('ventas/rcaj/precierre');
		}

		$attr=array(
			'class'  => 'ui-state-default ui-corner-all',
			'onclick'=> "javascript:window.location='".site_url('ventas/rcaj/filteredgrid/search')."'",
			'value'  => 'Regresar'
		);

		$cont['form']    = &$form;
		$cont['c_efe']   = $c_efe*2;
		$cont['c_otrp']  = $c_otrp*2;
		$cont['regresa'] = form_button($attr,'Regresar');
		$data['content'] = $this->load->view('view_rcaj',$cont, true);
		$data['title']   = '<h1>Recepci&oacute;n de cajero '.$cajero.' Fecha '.dbdate_to_human($fecha).'</h1>';
		$data['head']    = $this->rapyd->get_head().script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data);
	}


	function forcierre($numero){
		$this->rapyd->load('dataform');
		$caja = '99';
		$dbnumero = $this->db->escape($numero);
		$cana=$this->datasis->dameval('SELECT COUNT(*) FROM rcaj WHERE tipo="T" AND numero='.$dbnumero);

		if($cana<1){
			$data['content'] = 'El efecto a cerrar es inv&aacute;lido o ya fue cerrado '.anchor('ventas/rcaj/filteredgrid/search','Regresar');
			$data['title']   = '<h1>Recepci&oacute;n de cajas</h1>';
			$data['head']    = $this->rapyd->get_head().script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
			$this->load->view('view_ventanas', $data);
			return ;
		}

		$form = new DataForm("ventas/rcaj/forcierre/$numero/process");

		$attr=array(
			'class'  => 'ui-state-default ui-corner-all',
			'onclick'=> "javascript:window.location='".site_url('ventas/rcaj/filteredgrid')."'",
			'value'  => 'Regresar'
		);

		$mSQL="SELECT c.tipo,c.nombre ,b.recibido,b.sistema,b.diferencia
		FROM rcaj    AS a 
		JOIN itrcaj  AS b ON a.numero=b.numero
		JOIN tarjeta AS c ON c.tipo=b.tipo 
		WHERE a.numero=${dbnumero}";

		$query = $this->db->query($mSQL);
		if($query->num_rows()>0){
			$totales=array(0,0,0);
			$arr=array('recibido','sistema','diferencia');
			foreach ($query->result() as $i=>$row){
				foreach($arr AS $o=>$nobj){
					$obj = $nobj.$row->tipo;
					$totales[$o]+=$row->$nobj;
					$form->$obj = new inputField('('.$row->tipo.') '.$row->nombre, $obj);
					$form->$obj->style='text-align:right';
					$form->$obj->insertValue=$row->$nobj;
					$form->$obj->size=10;
					$form->$obj->rule='numeric';
					if($o==0) $sobj=$obj; else $form->$obj->in=$sobj;
					if($o!=0) $form->$obj->readonly=true;
				}
			}

			foreach($arr AS $o=>$nobj){
				$obj = 't'.$nobj;
				$form->$obj = new inputField('Totales:', $obj);
				$form->$obj->style='text-align:right';
				$form->$obj->size=10;
				$form->$obj->insertValue=$totales[$o];
				$form->$obj->rule='numeric';
				if($o==0) $sobj=$obj; else $form->$obj->in=$sobj;
				$form->$obj->readonly=true;
			}
		}

		$form->submit('btnsubmit','Cerrar cajero');
		$form->build_form();

		$this->rapyd->jquery[]='$(":input").numeric(".");';
		$this->rapyd->jquery[]='$(\'input[name^="recibido"]\').bind("keyup",function() { gtotal(); });';
		$this->rapyd->jquery[]='function gtotal(){
			TRECI=TSIS=TDIFE=0;
			$(\'input[name^="recibido"]\').each(function(i,e){
				nombre=this.name;
				tipo=nombre.substring(nombre.length-2,nombre.length);

				if($(this).val().length>0){
					recibido   =parseFloat($(this).val());
					sistema    =parseFloat($("#sistema"+tipo).val());
					diferencia=recibido-sistema;
					$("#diferencia"+tipo).val(diferencia);
				}
				if($(this).val().length>0) TRECI = TRECI+parseFloat($(this).val());
			});

			$(\'input[name^="diferencia"]\').each(function(i,e){
				if($(this).val().length>0)
					TDIFE = TDIFE+parseFloat($(this).val());
			});

			$("#trecibido").val(TRECI);
			$("#tdiferencia").val(TDIFE);
		}';

		//Cierre de caja
		if ($form->on_success()){
			$mSQL="SELECT c.tipo,c.nombre ,b.recibido,b.sistema,b.diferencia
			FROM rcaj    AS a 
			JOIN itrcaj  AS b ON a.numero=b.numero
			JOIN tarjeta AS c ON c.tipo=b.tipo 
			WHERE a.numero=${dbnumero}";

			$query = $this->db->query($mSQL);
			if($query->num_rows()>0){
				$str='';
				$arr=array();
				$rrecibido=$sistema=0;
				foreach ($query->result() as $i=>$row){
					$nobj='recibido'.$row->tipo;
					$recibido = (isset($form->$nobj))? (empty($form->$nobj->newValue))? 0.00 :floatval($form->$nobj->newValue) : 0.00;
					if($row->sistema>0 || $recibido>0){
						$str.= $row->tipo.' '.$recibido.'  ';
						$arr['recibido']   = $recibido;
						$arr['sistema']    = $row->sistema;
						$arr['diferencia'] = $recibido-$row->sistema;
						$arr['numero']     = $numero;
						$arr['cierre']     = 'S';
						$arr['tipo']       = $row->tipo;

						$rrecibido   += $recibido;
						$sistema     += $row->sistema;
						$mmSQL = $this->db->insert_string('itrcaj', $arr);
						$this->db->simple_query($mmSQL);
						//echo $mmSQL."\n";
					}
				}

				$arr = array(
					'tipo'     => 'F',
					'recibido' => $rrecibido,
					'observa'  => $str
				);
				$where = 'numero='.$this->db->escape($numero);
				$mmSQL = $this->db->update_string('rcaj', $arr, $where); 
				$this->db->simple_query($mmSQL);
				//echo $mmSQL;

				//cierra el cajero
				$cajero=$this->datasis->dameval('SELECT cajero FROM rcaj WHERE numero='.$dbnumero);
				$arr= array('status'=>'C',
					'fechac'=>date('Ymd'),
					'horac' =>date('h:i:s'),
					'cierre'=>$rrecibido,
					'caja'  =>'99'
					);

				$where = 'cajero='.$this->db->escape($cajero);
				$mmSQL = $this->db->update_string('scaj', $arr, $where);
				$ban=$this->db->simple_query($mmSQL);
				if($ban==false) memowrite($mmSQL,'rcaj');
				//echo $mmSQL;

				//Crea el movimiento en smov
				$transac=$this->datasis->fprox_numero('transac');
				$mSQL  = 'SELECT fecha, cajero FROM rcaj WHERE numero='.$dbnumero;
				$query = $this->db->query($mSQL);
				$row   = $query->first_row();
				$fecha =$row->fecha;
				$sfecha=str_replace('','-',$fecha);
				$cajero=$row->cajero;

				$nbmov=$this->_banprox($caja);
				$mSQL = 'SELECT moneda, numcuent,banco,saldo FROM banc WHERE codbanc= ? ';
				$query= $this->db->query($mSQL,array($caja));
				$row  = $query->first_row();

				$data = array();
				$data['codbanc']    =$caja;
				$data['moneda']     =$row->moneda;
				$data['numcuent']   =$row->numcuent;
				$data['banco']      =$row->banco;
				$data['saldo']      =$row->saldo;
				$data['tipo_op']    ='NC';
				$data['numero']     =$nbmov;
				$data['fecha']      =$fecha;
				$data['clipro']     ='O';
				$data['codcp']      ='VENT';
				$data['nombre']     ='INGRESOS DIARIOS';
				$data['monto']      =$rrecibido;
				$data['concepto']   ="ENTREGA FINAL CAJERO $cajero DIA ".dbdate_to_human($fecha);
				$data['transac']    =$transac;

				$mSQL = $this->db->insert_string('bmov', $data);
				$ban=$this->db->simple_query($mSQL);
				if($ban==false) memowrite($mSQL,'rcaj');
				//Fin del movimiento en smov

				//Actualiza el saldo en la caja
				$mSQL="CALL sp_actusal('$caja','$sfecha',$rrecibido)";
				$ban=$this->db->simple_query($mSQL);
				if($ban==false) memowrite($mSQL,'rcaj');

				//Crea la diferencia en caja si la hay
				$dif=$rrecibido-$sistema;
				if($dif!=0.00){
					$mSQL = 'SELECT COUNT(*) AS n  FROM banc WHERE codbanc="DF"';
					$query= $this->db->query($mSQL);
					$row  = $query->first_row();
					if($row->n==0){
						$data['codbanc'] ='DF';
						$data['tbanco']  ='CAJ';
						$data['moneda']  ='Bs';
						$data['banco']   ='CAJA';
						//$data['nombre']  ='DIFERENCIA EN CAJA';
						$data['numcuent']='DIFERENCIA EN CAJA';
						$data['activo']  ='S';
						$data['tipocta'] ='C';
						$data['monto']   = 0;
						$data['saldo']   = 0;

						$mSQL = $this->db->insert_string('banc', $data);
						$ban=$this->db->simple_query($mSQL);
						if($ban==false) memowrite($mSQL,'rcaj');
					}

					$nbmov=$this->_banprox('DF');
					$mSQL = 'SELECT moneda, numcuent,banco,saldo FROM banc WHERE codbanc="DF"';
					$query= $this->db->query($mSQL);
					$row  = $query->first_row();

					if($dif<0){ // crea la NC a causa del faltante de caja
						$data = array();
						$data['codbanc']    ='DF';
						$data['moneda']     =$row->moneda;
						$data['numcuent']   =$row->numcuent;
						$data['banco']      =$row->banco;
						$data['saldo']      =$row->saldo;
						$data['tipo_op']    ='NC';
						$data['numero']     =$nbmov;
						$data['fecha']      =$fecha;
						$data['clipro']     ='O';
						$data['codcp']      ='VENT';
						$data['nombre']     ='INGRESOS DIARIOS';
						$data['monto']      =abs($dif);
						$data['concepto']   ="FALTANTE EN CAJA $caja CAJERO $cajero DIA ".dbdate_to_human($fecha);
						$data['transac']    =$transac;

						$mSQL = $this->db->insert_string('bmov', $data);
						$ban=$this->db->simple_query($mSQL);
						if($ban==false) memowrite($mSQL,'rcaj');

					}else{ //Crea la ND a causa del sobrante de caja
						$data = array();
						$data['codbanc']    ='DF';
						$data['moneda']     =$row->moneda;
						$data['numcuent']   =$row->numcuent;
						$data['banco']      =$row->banco;
						$data['saldo']      =$row->saldo;
						$data['tipo_op']    ='ND';
						$data['numero']     =$nbmov;
						$data['fecha']      =$fecha;
						$data['clipro']     ='O';
						$data['codcp']      ='VENT';
						$data['nombre']     ='INGRESOS DIARIOS';
						$data['monto']      =abs($dif);
						$data['concepto']   ="SOBRANTE EN CAJA $caja CAJERO $cajero DIA ".dbdate_to_human($fecha);
						$data['transac']    =$transac;

						$mSQL = $this->db->insert_string('bmov', $data);
						$ban=$this->db->simple_query($mSQL);
						if($ban==false) memowrite($mSQL,'rcaj');
					}

					$mSQL="CALL sp_actusal('DF','$sfecha',$dif)";
					echo $mSQL;
					$ban=$this->db->simple_query($mSQL);
					if($ban==false) memowrite($mSQL,'rcaj');
				}

				redirect('ventas/rcaj/filteredgrid/search');
			}
		}

		$attr=array(
			'class'  => 'ui-state-default ui-corner-all',
			'onclick'=> "javascript:window.location='".site_url('ventas/rcaj/filteredgrid/search')."'",
			'value'  => 'Regresar'
		);

		$data['content'] = $form->output;
		$data['title']   = '<h1>Recepci&oacute;n de cajas</h1>';
		$data['head']    = $this->rapyd->get_head().script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data);
	}


	function forcierre2() {
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("datagrid");
		$this->rapyd->load("fields");

		$caja    = $this->uri->segment(4);
		$cajero  = $this->uri->segment(5);
		$qfecha  = $this->uri->segment(6);
		if (!$caja or !$cajero or !$qfecha ) redirect('/ventas/rcaj');

		//$data['forma'] =script('nformat.js');

		$efectivo=array();
		$atts    =array('class'=>'inputnum','size'=>'15','align'=>'right');
		$attsr   =array('class'=>'inputnum','size'=>'15','align'=>'right','readonly'=>'readonly');
		$catts   =array('class'=>'inputnum','size'=>'6' ,'align'=>'right');

		$query = $this->db->query('SELECT a.tipo,a.denomina,b.cambiobs*a.denomina equivalencia,a.nombre FROM monebillet a JOIN mone b ON a.moneda=b.moneda ORDER BY a.tipo,a.moneda,a.denomina DESC');
		$i=0;
		foreach ($query->result() as $row){
			$valor=$row->equivalencia;
			$catts['name']=$catts['id']=$row->tipo.$row->denomina;

			$catts['name']=$catts['id']='EFE'.$i;
			$catts['onKeyDown']=$catts['onKeyUp']="opera($valor,'".$catts['name']."');";
			$cantidad = form_input($catts);

			//cantidad sistema
			$attsr['name']=$attsr['id']='T'.$catts['name'];
			$attsr['value']='0,00';
			$total = form_input($attsr);

			$pasa=array();
			$pasa['denom']  =$row->nombre;
			$pasa['cant']   =$cantidad;
			$pasa['sistema']=$total;

			if($row->tipo=='MO')     $pasa['tipo']   ='Monedas';
			elseif($row->tipo=='BI') $pasa['tipo']   ='Billetes';
			else                     $pasa['tipo']   =$row->tipo;

			$efectivo[]=$pasa;
			$i++;
		}$efe_filas=$i;
		$query->free_result();

		$atts['name']=$atts['id']='TOTRAS';
		$atts['onChange']="montosolo('".$atts['name']."');tefectivo();";
		$total = form_input($atts);
		$pasa['denom']  ='Monto';
		$pasa['cant']   ='';
		$pasa['sistema']=$total;
		$pasa['tipo']   ='Otras Denominaciones';
		$efectivo[]=$pasa;

		$atts['name']=$atts['id']='TFONDO';
		$total = form_input($atts);

		$pasa['sistema']=$total;
		$pasa['tipo']   ='Fondo de caja (-)';
		$efectivo[]=$pasa;

		$attsr['name']=$attsr['id']='EFETOTAL';
		$total = form_input($attsr);

		$pasa['denom']  ='TOTAL';
		$pasa['sistema']=$total;
		$pasa['tipo']   ='Total Efectivo';
		$efectivo[]=$pasa;

		$msistema=$this->datasis->dameval("SELECT sum(a.monto) 
		FROM sfpa as a 
		JOIN sfac b ON a.transac=b.transac and b.tipo_doc='F'
		WHERE b.cajero=$cajero AND b.fecha=$qfecha AND a.tipo IN ('EF','DE') AND SUBSTRING(a.numero,1,1)<>'X' ");

		$attsr['value']=number_format($msistema,'2',',','.');
		$attsr['name']=$attsr['id']='EFESISTEMA';
		$total = form_input($attsr);
		$pasa['denom']  ='SISTEMA';
		$pasa['sistema']=$total;
		$pasa['tipo']   ='Total Efectivo';
		$efectivo[]=$pasa;
		$attsr['value']='';
		$query->free_result();
		//fin efectivo

		$mSQL="SELECT a.tipo,a.nombre, sum(b.monto) sistema 
		FROM tarjeta a 
		JOIN sfpa b ON a.tipo=b.tipo 
		JOIN sfac c ON b.transac=c.transac and c.tipo_doc='F'
		WHERE a.tipo NOT IN ('EF','CT','NC','ND', 'DE','IR','DP') 
		AND c.cajero='$cajero' AND b.fecha=$qfecha AND 
		SUBSTRING(b.numero,1,1)<>'X' GROUP BY a.tipo
		UNION 
		SELECT tipo,nombre, '0' sistema FROM tarjeta 
		WHERE tipo NOT IN (SELECT a.tipo
		FROM tarjeta a 
		JOIN sfpa b ON a.tipo=b.tipo 
		JOIN sfac c ON b.transac=c.transac and c.tipo_doc='F'
		WHERE a.tipo NOT IN ('EF','CT','NC','ND', 'DE','IR','DP') 
		AND c.cajero='$cajero' AND b.fecha=$qfecha AND 
		SUBSTRING(b.numero,1,1)<>'X' GROUP BY a.tipo)
		AND tipo NOT IN ('EF','CT','NC','ND', 'DE','IR','DP') ORDER BY tipo";
		//echo $mSQL;

		$i=0;
		$fpsistema=0;
		$tarjetas=array();
		$js_ofpArray=array();
		$query = $this->db->query($mSQL);
		foreach ($query->result() as $row){
			$catts['name']=$catts['id']='COFP'.$row->tipo;
			$cantidad = form_input($catts);
			
			$atts['name']=$atts['id']='TOFP'.$row->tipo;
			$atts['values']=0;
			$js_ofpArray[]=$row->tipo;
			$atts['onChange']="montosolo('".$atts['name']."'); totrasfpa();";
			$monto = form_input($atts);//.form_hidden('TOFP_T'.$i,$row->tipo);
			
			$attsr['name']=$attsr['id']='SOFP'.$row->tipo;
			$attsr['value']=number_format($row->sistema,'2',',','.');
			$sistema = form_input($attsr);
			$attsr['value']='';

			$pasa=array();
			$pasa['descrip']='<b>'.$row->tipo.'</b> '.$row->nombre;
			$pasa['cant']   =$cantidad;
			$pasa['monto']  =$monto;
			$pasa['sistema']=$sistema;
			$fpsistema+=$row->sistema;
			$tarjetas[]=$pasa;
			$i++;
		}$otr_filas=$i;
		$query->free_result();

		$attsr   =array('class'=>'inputnum','size'=>'20','align'=>'right','readonly'=>'readonly');

		$attRecEfe=$attSisEfe=$attDifEfe=$attsr;
		$attRecEfe['name']=$attRecEfe['id']='EFERECI';
		$attSisEfe['name']=$attSisEfe['id']='EFESIST';
		$attDifEfe['name']=$attDifEfe['id']='EFEDIFE';
		$attSisEfe['value']=number_format($msistema,'2',',','.');



		$attRecOtr=$attSisOtr=$attDifOtr=$attsr;
		$attRecOtr['name']=$attRecOtr['id']='OTRRECI';
		$attSisOtr['name']=$attSisOtr['id']='OTRSIST';
		$attDifOtr['name']=$attDifOtr['id']='OTRDIFE';
		$attSisOtr['value']=number_format($fpsistema,'2',',','.');

		$attRecRec=$attSisRec=$attDifRec=$attsr;
		$attRecRec['name']=$attRecRec['id']='RECRECI';
		$attSisRec['name']=$attSisRec['id']='RECSIST';
		$attDifRec['name']=$attDifRec['id']='RECDIFE';
		$attSisRec['value']=number_format($fpsistema+$msistema,'2',',','.');

		$resumen=array(
			0 => array(
				'descrip' =>'Efectivo',
				'recibido'=>form_input($attRecEfe),//.form_hidden('QEFEGLOBAL'),
				'sistema' =>form_input($attSisEfe),//.form_hidden('QEFESISTEMA',$msistema),
				'diferen' =>form_input($attDifEfe),
				),
			1 => array(
				'descrip' =>'Otros',
				'recibido'=>form_input($attRecOtr),
				'sistema' =>form_input($attSisOtr),//.form_hidden('QOTRSISTEMA',$fpsistema),
				'diferen' =>form_input($attDifOtr),
				),
			2 => array(
				'descrip' =>'Recibido',
				'recibido'=>form_input($attRecRec),
				'sistema' =>form_input($attSisRec),//.form_hidden('QTOTSISTEMA',$fpsistema+$ctsistema+$msistema),
				'diferen' =>form_input($attDifRec),
				)
		);

		$script="<script type='text/javascript'>
		function caldiferencia() {
			var pre=new Array('EFE','OTR','REC');
			var i=0,acumulador=0;
			for(i=0;i<2;i++){
				recibid=des_number_format(document.getElementById(pre[i]+'RECI').value,'.',',');
				sistema=des_number_format(document.getElementById(pre[i]+'SIST').value,'.',',');
				document.getElementById(pre[i]+'DIFE').value=number_format(recibid-sistema,'.',',');
				acumulador=acumulador+recibid;
			}
			document.getElementById(pre[i]+'RECI').value=number_format(acumulador,'.',',');
			sistema=des_number_format(document.getElementById(pre[i]+'SIST').value,'.',',');
			document.getElementById(pre[i]+'DIFE').value=number_format(acumulador-sistema,'.',',');
		}
		
		function opera(valor,traeid) {
			cantidad=document.getElementById(traeid);
			recibe  =document.getElementById('T'+traeid)
			recibe.value= number_format(cantidad.value*valor,'.',',');
			tefectivo();
		}
		function tefectivo() {
			var i=0,acumulador=0;
			for(i=0;i<$efe_filas;i++){
				valor=des_number_format(document.getElementById('TEFE'+i).value,'.',',');
				acumulador=acumulador+valor;
			}
			totras=des_number_format(document.getElementById('TOTRAS').value,'.',',');
			tfondo=des_number_format(document.getElementById('TFONDO').value,'.',',');
			total=document.getElementById('EFETOTAL');
			total.value=number_format(acumulador+totras-tfondo,'.',',');
			recibido=document.getElementById('EFERECI');
			recibido.value=number_format(acumulador+totras-tfondo,'.',',');  
			caldiferencia();
		}
		function montosolo(traeid){
			elemento=document.getElementById(traeid);
			valor=des_number_format(elemento.value,'.',',');
			elemento.value=number_format(valor,'.',',');
		}

		function totrasfpa() {
			var pre=new Array('".join("','",$js_ofpArray)."');
			var i=0,acumulador=0;
			for(i=0;i<$otr_filas;i++){
				valor=des_number_format(document.getElementById('TOFP'+pre[i]).value,'.',',');
				acumulador=acumulador+valor;
			}
			recibido=document.getElementById('OTRRECI');
			recibido.value=number_format(acumulador,'.',',');
			caldiferencia();
		}
		</script>";

		$att_pcf=array('class'=>'input','size'=>'30','align'=>'right');;
		$att_pcf['name']='controlp';
		$pcfiscal=form_input($att_pcf);

		$att_ucf=array('class'=>'input','size'=>'30','align'=>'right');;
		$att_ucf['name']='controlf';
		$ucfiscal=form_input($att_ucf);

		$att_obs=array('class'=>'input','rows'=>'2','align'=>'right');;
		$att_obs['name']='observaciones';
		$observa=form_textarea($att_obs);

		$efegrid = new DataGrid2('Efectivo',$efectivo);
		$efegrid->agrupar(' ', 'tipo');
		$efegrid->per_page = count($efectivo);
		$efegrid->column("Denominacion","denom"      ,'align="RIGHT"');
		$efegrid->column("Cantidad"    ,"<#cant#>"   ,'align="RIGHT"');
		$efegrid->column("Sub-total"   ,"<#sistema#>",'align="RIGHT"');
		$efegrid->build();
		$data['listai'] = $efegrid->output;

		$targrid = new DataGrid('Otras Formas de Pago',$tarjetas);
		$targrid->per_page = count($tarjetas);
		$targrid->column("Descripcion","<#descrip#>");
		$targrid->column("Cantidad"   ,"<#cant#>"   ,'align="RIGHT"');
		$targrid->column("Monto"      ,"<#monto#>"  ,'align="RIGHT"');
		$targrid->column("Sistema"    ,"<#sistema#>",'align="RIGHT"');
		$targrid->build();
		$data['listad'] = $targrid->output;

		/*$cestagrid = new DataGrid('Cesta Tiket',$cestatiket);
		$cestagrid->per_page = count($cestatiket);
		$cestagrid->column("Descripcion","<#descrip#>");
		$cestagrid->column("Cantidad"   ,"<#cant#>",'align="RIGHT"');
		$cestagrid->column("Monto"      ,"<#monto#>"   ,'align="RIGHT"');
		$cestagrid->column("Sistema"    ,"<#sistema#>" ,'align="RIGHT"');
		$cestagrid->build();
		$data['listad'] .= $cestagrid->output;*/

		$resugrid = new DataGrid('Resumen',$resumen);
		$resugrid->per_page = count($resumen);
		$resugrid->column("Descripcion","<#descrip#>");
		$resugrid->column("Recibido"   ,"<#recibido#>",'align="RIGHT"');
		$resugrid->column("Sistema"    ,"<#sistema#>" ,'align="RIGHT"');
		$resugrid->column("Diferencia" ,"<#diferen#>" ,'align="RIGHT"');
		$resugrid->build();
		//$data['listab'] = $resugrid->output.'Primer control Fiscal '.$pcfiscal.'<br>Ultimo control Fiscal '.$ucfiscal.'<br>Observaciones<br>'.$observa;
		$data['listab'] = $resugrid->output.'<br>Observaciones<br>'.$observa;


		$data['submit']=form_submit('mysubmit', 'Guardar');

		$atts=array('onsubmit'=>"return confirm('Seguro que desea Guardar')");
		
		$hidden = array('otr_filas' => $otr_filas, 'ces_filas' => 0);
		//$data['form']=form_open("supermercado/cierre/guardar/$caja/$cajero/$qfecha",$atts,$hidden);

		//$data['titulo'] = $script.$this->rapyd->get_head()."<center><h2>Cierre de Caja $caja Cajero $cajero</h2></center>\n";
		//$this->layout->buildPage('supermercado/view_cierre', $data);

		$ddata['content'] = form_open("ventas/rcaj/guardar/$caja/$cajero/$qfecha",$atts,$hidden);
		$ddata['content'].= $this->load->view('view_cierre', $data, true);
		$ddata['content'].= '<center>'.form_submit('mysubmit', 'Guardar').'</center>';
		$ddata['content'].= form_close();
		$ddata['title']   = "<h1>Cierre de caja $caja Cajero $cajero</h1>";
		$ddata["head"]    = $this->rapyd->get_head().script('nformat.js').$script;
		$this->load->view('view_ventanas', $ddata);
	}

	function guardar(){
		$caja    = $this->uri->segment(4);
		$cajero  = $this->uri->segment(5);
		$qfecha  = $this->uri->segment(6);
		//print_r($_POST);

		$mSQL="SELECT COUNT(*) FROM rcaj WHERE caja='$caja' AND cajero='$cajero' AND fecha='$qfecha'";
		$hh=$this->datasis->dameval($mSQL);
		if($hh>0){
			$salida=anchor('ventas/rcaj/filteredgrid','<pre>Regresar</pre>').'</center>';
			$data['content'] = $salida;
			$data['content'] .= "<BR><H2>CIERRE YA REALIZADO CON ATERIORIDAD</H2><BR><BR>\n";
			$data['title']   = "<h1>Recepci&oacute;n de cajas</h1>";
			$data["head"]    = $this->rapyd->get_head();
			$this->load->view('view_ventanas', $data);
			return;
		}

		$ntransac   = str_pad($this->datasis->prox_sql("ntransa"),   8, "0", STR_PAD_LEFT) ;
		$numero  = str_pad($this->datasis->prox_sql("nrcaja"), 8, "0", STR_PAD_LEFT) ;

		//** Totales **
		//Efectivo
		$efrecibido   = (empty($_POST['EFERECI'])) ? 0 : des_nformat($_POST['EFERECI']);
		$efsistema    = (empty($_POST['EFESIST'])) ? 0 : des_nformat($_POST['EFESIST']);
		$efdiferencia = (empty($_POST['EFEDIFE'])) ? 0 : des_nformat($_POST['EFEDIFE']);

		//Otras formas de pago
		$ofrecibido   = (empty($_POST['OTRRECI'])) ? 0 : des_nformat($_POST['OTRRECI']);
		$ofsistema    = (empty($_POST['OTRSIST'])) ? 0 : des_nformat($_POST['OTRSIST']);
		$ofdiferencia = (empty($_POST['OTRDIFE'])) ? 0 : des_nformat($_POST['OTRDIFE']);


		//Resumen
		$torecibido   = (empty($_POST['RECRECI'])) ? 0 : des_nformat($_POST['RECRECI']);
		$tosistema    = (empty($_POST['RECSIST'])) ? 0 : des_nformat($_POST['RECSIST']);
		$todiferencia = (empty($_POST['RECDIFE'])) ? 0 : des_nformat($_POST['RECDIFE']);

		// Ahora Carga el efectivo
		$query_1="INSERT INTO itrcaj( numero,tipo,recibido,sistema,diferencia)
				    VALUES   ($numero,'EF',$efrecibido,$efsistema,$efdiferencia)";
		$this->db->query($query_1);

		// Otras formas de Pago
		$ofpobser='';
		$mSQL = 'SELECT tipo,nombre FROM tarjeta WHERE tipo NOT IN ("EF","CT","NC","ND", "DE", "IR","DP") ORDER BY tipo';
		$query = $this->db->query($mSQL);
		$efectos = 0;
		foreach ( $query->result() as $row ){

			$tipo=$row->tipo;
			$mmonto    = (empty($_POST["TOFP".$row->tipo])) ? 0 : des_nformat($_POST["TOFP".$row->tipo]);
			$msistema  = (empty($_POST["SOFP".$row->tipo])) ? 0 : des_nformat($_POST["SOFP".$row->tipo]);
			$can       = (empty($_POST["COFP".$row->tipo])) ? 0 : des_nformat($_POST["COFP".$row->tipo]);
			$can       = intval($can);
			$diferencia=$msistema-$mmonto;

			if ($mmonto<>0 or $msistema<>0){
				$ofpobser.="$tipo  $mmonto ";
				$query="INSERT INTO itrcaj( numero,tipo,recibido,sistema,diferencia)
				        VALUES   ($numero,'$tipo',$mmonto,$msistema,$diferencia)";
				$this->db->query($query);
			}
		}

		$observa='EF '.$efrecibido.'  '.$ofpobser;
		$mSQL="INSERT INTO rcaj (fecha,cajero,tipo,usuario,caja,recibido,ingreso,parcial,observa,numero,transac,estampa,hora )
		VALUE ($qfecha,'$cajero','F','".$this->session->userdata['usuario']."','$caja',$torecibido,$tosistema,$todiferencia,'$observa','$numero','$ntransac', now(),'".date("h:m:s")."')";
		$this->db->query($mSQL);

		$atRI = array(
			'width'     => '800','height' => '600',
			'scrollbars'=> 'yes','status' => 'yes',
			'resizable' => 'yes','screenx'=> '0',
			'screeny'   => '0');

		$salida ="<br><pre>Para Imprimir haga click ".anchor_popup("formatos/ver/RECAJA/$numero",'Aqui',$atRI).'<br>';
		$salida.=anchor('ventas/rcaj/filteredgrid','<pre>Regresar</pre>').'</center>';
		$data['content'] = $salida;
		$data['content'] .= "<BR><H2>CIERRE YA GUARDADO</H2><BR><BR>\n";
		$data['title']   = "<h1>Recepci&oacute;n de cajas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _banprox($codban){
		$nom='nBAN'.$codban;
		while(1){
			$numero=$this->datasis->fprox_numero($nom,12);
			$dbnumero=$this->db->escape($numero);
			$mSQL = "SELECT COUNT(*) AS n FROM bmov WHERE numero=$dbnumero";
			$query= $this->db->query($mSQL);
			$row  = $query->first_row('array');
			if($row['n']==0) break;
		}
		return $numero;
	}

	function instalar(){
		$mSQL="CREATE TABLE `itrcaj` (`numero` VARCHAR (8), `tipo` VARCHAR (15), `recibido` DECIMAL (17,2), `sistema` DECIMAL (17,2), `diferencia` DECIMAL (17,2),PRIMARY KEY (`numero`, `tipo`))";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `itrcaj`  ADD COLUMN `cierre` CHAR(1) NOT NULL DEFAULT 'N' AFTER `tipo`";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `itrcaj`  DROP PRIMARY KEY,  ADD PRIMARY KEY (`numero`, `tipo`, `cierre`)";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `sfpa`  ADD COLUMN `cierre` CHAR(8) DEFAULT '' AFTER `hora`";
		$this->db->simple_query($mSQL);

	}
}
