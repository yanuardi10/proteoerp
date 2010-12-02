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
		$this->rapyd->load('datafilter','datagrid');

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
			//echo $cajero;
			//var_dump(empty($numero));

			$atts=array('align'=>'LEFT','border'=>'0');
			$fecha=str_replace('-','',$fecha);
			$atRI = array(
				'width'     => '800','height' => '600',
				'scrollbars'=> 'yes','status' => 'yes',
				'resizable' => 'yes','screenx'=> '0',
				'screeny'   => '0');

			$CI =& get_instance();
			if(empty($numero)){
				//$mSQL="SELECT cierre FROM sfpa WHERE cobrador='$cajero' AND fecha=DATE_ADD($fecha, INTERVAL 1 DAY) AND MID(hora,1,2)< 7 LIMIT 1";
				//$cerrado = $CI->datasis->dameval($mSQL);

				$mSQL="SELECT COUNT(*) FROM sfpa WHERE cobrador='$cajero' AND fecha=DATE_ADD($fecha, INTERVAL 1 DAY) AND MID(hora,1,2)< 7";
				$cerrado = $CI->datasis->dameval($mSQL);

				if($cerrado>0){ //caja fuera de turno
					return image('caja_inhabilitada.gif',"Cajero Inhabilitado: $cajero",$atts).'<h3>Cajero de turno</h3>Debe gestionarse al d&iacute;a siguiente';
				}else{
					return image('caja_abierta.gif',"Cajero Abierto: $cajero",$atts).'<h3>Abierto</h3><center>'.anchor("ventas/rcaj/precierre/99/$cajero/$fecha", 'Pre-cerrar cajero').'</center>';
				}
			
			}else{
			//$cerrado = $CI->datasis->dameval("SELECT numero FROM rcaj WHERE cajero='$cajero' AND fecha='$fecha' ");

				if($tipo=='T'){
					return image('caja_precerrada.gif',"Cajero Pre-Cerrado: $cajero",$atts).'<h3>'.anchor("ventas/rcaj/forcierre/$numero/", 'Cerrar cajero').'</h3><center>'.anchor('formatos/ver/RECAJA/'.$numero, ' Ver cuadre de caja');
				}else{
					return image('caja_cerrada.gif',"Cajero Cerrado: $cajero",$atts).'<h3>Cerrado</h3><center>'.anchor('formatos/ver/RECAJA/'.$numero, ' Ver cuadre de caja');
				}
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
			$grid->db->from('sfac AS b');
			$grid->db->join('rcaj AS a','a.cajero=b.cajero AND a.fecha=b.fecha','LEFT');
			$grid->db->join('sfpa AS c','b.transac=c.transac');
			//$grid->db->where('c.cierre IS NULL');
			$grid->db->groupby('b.cajero');
			$grid->use_function('iconcaja');

			$grid->column('Numero'     ,'<sinulo><#numero#>|---</sinulo>','align=\'center\'');
			//$grid->column('Tipo'       ,'<#tipo#>','align=\'center\'');
			$grid->column('Fecha'      ,'<dbdate_to_human><#fecha#></dbdate_to_human>');
			$grid->column('Cajero'     ,'cajero','align=\'center\'');
			$grid->column('Recibido'   ,'<sinulo><nformat><#recibido#></nformat>|0.00</sinulo>','align=\'right\'');
			//$grid->column('Ingreso'    ,'<nformat><#ingreso#></nformat>' ,'align=\'right\'');
			$grid->column('Status/Caja','<iconcaja><#cajero#>|<#fecha#>|<#numero#>|<#tipo#></iconcaja>','align="center"');
			$grid->column('Ver html'   ,"<siinulo><#numero#>|---|$urih</siinulo>",'align=\'center\'');
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
			$redir=false;
			$usuario = $this->session->userdata('usuario');
			$caja    = '99';
			$mSQL    = 'SELECT cajero FROM usuario WHERE us_codigo= ?';
			$query   = $this->db->query($mSQL,array($usuario));
			$rrow    = $query->first_row();
			$cajero  = $rrow->cajero;
			$fecha   = date('Ymd');
			$url_submit='ventas/rcaj/precierre/process';
			if(empty($cajero)) return;
		}else{
			$url_submit="ventas/rcaj/precierre/$caja/$cajero/$fecha/process";
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

		$form = new DataForm($url_submit);

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
			$arr=array('cOTR');
			foreach($arr AS $o=>$nobj){
				$obj = $nobj.$row->tipo;
				$form->$obj = new inputField($row->nombre, $obj);
				$form->$obj->style='text-align:right';
				$form->$obj->size=10;
				$form->$obj->rule='numeric';
				/*if($o==1){
					$form->$obj->in=$sobj;
				}else{
					$form->$obj->css_class='cotrasf';
					$form->$obj->indice   = $row->tipo;
				}
				$sobj=$obj;*/
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
		$this->rapyd->jquery[]='$("#df1").submit(function() { return confirm("Estas seguro de realizar el Pre-Cierre?"); })';
		$this->rapyd->jquery[]='$(".cefectivo").bind("keyup",
			function() {
				obj=this.name;
				mul=eval("denomi."+obj);
				valor=$(this).val();
				$("#c"+obj).val(roundNumber(mul*valor,2));
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

			$("#TEFE").val(roundNumber(TEFE,2));
			$("#TOTR").val(roundNumber(TOTR,2));
			$("#TGLOB").val(roundNumber(TOTR+TEFE,2));
		}';

		//hace el precierre
		if ($form->on_success()){
			$dbfecha  = $this->db->escape($fecha);
			$dbcajero = $this->db->escape($cajero);

			/*$mSQL="SELECT c.tipo, IFNULL(aa.monto,0) AS monto FROM
				(SELECT b.tipo ,SUM(b.monto) AS monto 
				FROM sfac AS a 
				JOIN sfpa AS b ON a.transac=b.transac 
				WHERE a.fecha=$dbfecha AND a.cajero=$dbcajero AND a.tipo_doc<>'X'
				GROUP BY b.tipo) AS aa
				RIGHT JOIN tarjeta AS c ON aa.tipo=c.tipo";*/

			$objfecha = DateTime::createFromFormat('Ymd', $fecha);
			$objfecha->sub(new DateInterval('P1D'));
			$dbfecha_s=$this->db->escape($objfecha->format('Y-m-d'));
			$mSQL="SELECT bb.tipo, SUM(IFNULL(aa.monto,0)) AS monto FROM 
				(SELECT a.tipo,a.monto 
				FROM sfpa AS a 
				JOIN sfac AS b ON b.numero=a.numero AND a.tipo_doc=CONCAT(b.tipo_doc, IF(b.referen='M','E',b.referen)) 
				WHERE a.f_factura=$dbfecha AND SUBSTRING(a.tipo_doc,2,1)!='X' AND a.cobrador=$dbcajero 
			UNION ALL 
				SELECT a.tipo, monto 
				FROM sfpa AS a 
				JOIN sfac AS b ON b.numero=a.numero AND a.tipo_doc=CONCAT(b.tipo_doc, IF(b.referen='M','E',b.referen)) 
				WHERE a.f_factura=$dbfecha_s AND SUBSTRING(a.tipo_doc,2,1)!='X' AND a.cobrador=$dbcajero AND MID(a.hora,1,2)>18 ) AS aa
			RIGHT JOIN tarjeta AS bb ON aa.tipo=bb.tipo
			GROUP BY tipo";

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
				/*$mSQL="UPDATE sfac JOIN sfpa ON sfac.transac=sfpa.transac SET sfpa.cierre=$dbnumero
				WHERE sfac.fecha=$dbfecha AND sfac.cajero=$dbcajero";*/

				$mSQL="UPDATE sfpa JOIN sfac ON sfac.numero=sfpa.numero AND sfpa.tipo_doc=CONCAT(sfac.tipo_doc, IF(sfac.referen='M','E',sfac.referen))
				SET sfpa.cierre=$dbnumero
				WHERE sfpa.f_factura=$dbfecha    AND SUBSTRING(sfpa.tipo_doc,2,1)!='X' AND sfpa.cobrador=$dbcajero ";
				$this->db->simple_query($mSQL);

				$mSQL="UPDATE sfpa JOIN sfac ON sfac.numero=sfpa.numero AND sfpa.tipo_doc=CONCAT(sfac.tipo_doc, IF(sfac.referen='M','E',sfac.referen))
				SET sfpa.cierre=$dbnumero
				WHERE sfpa.f_factura>=$dbfecha_s AND SUBSTRING(sfpa.tipo_doc,2,1)!='X' AND sfpa.cobrador=$dbcajero AND MID(sfpa.hora,1,2)>18";
				$this->db->simple_query($mSQL);
			}
			if($redir){
				redirect('ventas/rcaj/filteredgrid/search');
			}else{
				redirect('ventas/rcaj/precierre');
			}
		}

		$attr=array(
			'class'  => 'ui-state-default ui-corner-all',
			'onclick'=> "javascript:window.location='".site_url('ventas/rcaj/filteredgrid/search')."'",
			'value'  => 'Regresar'
		);

		$cont['form']    = &$form;
		$cont['c_efe']   = $c_efe*2;
		$cont['c_otrp']  = $c_otrp;
		$cont['regresa'] = form_button($attr,'Regresar');
		$data['content'] = $this->load->view('view_rcaj',$cont, true);
		$data['title']   = '<h1>Recepci&oacute;n de cajero '.$cajero.' Fecha '.dbdate_to_human($fecha).'</h1>';
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
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
		$this->rapyd->jquery[]='$("#df1").submit(function() { return confirm("Estas seguro de realizar el Pre-Cierre?"); })';
		$this->rapyd->jquery[]='function gtotal(){
			TRECI=TSIS=TDIFE=0;
			$(\'input[name^="recibido"]\').each(function(i,e){
				nombre=this.name;
				tipo=nombre.substring(nombre.length-2,nombre.length);

				if($(this).val().length>0){
					recibido   =parseFloat($(this).val());
					sistema    =parseFloat($("#sistema"+tipo).val());
					diferencia=recibido-sistema;
					$("#diferencia"+tipo).val(numberFormat(diferencia,2));
				}
				if($(this).val().length>0) TRECI = TRECI+parseFloat($(this).val());
			});

			$(\'input[name^="diferencia"]\').each(function(i,e){
				if($(this).val().length>0)
					TDIFE = TDIFE+parseFloat($(this).val());
			});

			$("#trecibido").val(numberFormat(TRECI,2));
			$("#tdiferencia").val(numberFormat(TDIFE,2));
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
						$data['concepto'] ="SOBRANTE EN CAJERO $cajero DIA ".dbdate_to_human($fecha);

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
		$data['head']    = $this->rapyd->get_head().script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
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
