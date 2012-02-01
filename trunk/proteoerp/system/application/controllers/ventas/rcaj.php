<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Rcaj extends validaciones {

	function Rcaj(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->load->library("menues");
		$this->datasis->modulo_id('12A',1);
		$this->load->database();
	}

	function index(){
		redirect('ventas/rcaj/filteredgrid');
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
		$recep = anchor('ventas/rcaj/forcierre/','Recepcion de Caja');
		//$filter = new DataFilter($titulo);
		$filter = new DataFilter('Filtro');
		$filter->fecha = new dateonlyField('Fecha','fecha','d/m/Y');
		$filter->fecha->db_name='c.f_factura';
		$filter->fecha->size =11;
		$filter->fecha->clause='where';
		$filter->fecha->operator='=';
		$filter->fecha->insertValue=date('Y-m-d');

		$filter->cajero = new dropdownField('Cajero', 'cajero');
		$filter->cajero->db_name='c.cobrador';
		$filter->cajero->option('','Todos');
		$filter->cajero->options('SELECT cajero, nombre FROM scaj ORDER BY nombre');

		$filter->buttons('reset','search');
		$filter->build();

		$data['content'] = $filter->output;

		function iconcaja($cajero,$fecha,$numero='',$tipo='',$reve=0){
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

				/*$mSQL="SELECT COUNT(*) FROM sfpa WHERE cobrador='$cajero' AND fecha=DATE_ADD($fecha, INTERVAL 1 DAY) AND MID(hora,1,2)< 7";
				$cerrado = $CI->datasis->dameval($mSQL);

				if($cerrado>0){ //caja fuera de turno
					return image('caja_inhabilitada.gif',"Cajero Inhabilitado: $cajero",$atts).'<h3>Cajero de turno</h3>Debe gestionarse al d&iacute;a siguiente';
				}else{
					return image('caja_abierta.gif',"Cajero Abierto: $cajero",$atts).'<h3>Abierto</h3><center>'.anchor("ventas/rcaj/precierre/99/$cajero/$fecha", 'Pre-cerrar cajero').'</center>';
				}*/

				return image('caja_abierta.gif',"Cajero Abierto: $cajero",$atts).'<h3>Abierto</h3><center>'.anchor("ventas/rcaj/precierre/99/$cajero/$fecha", 'Pre-cerrar cajero').'</center>';
			}else{
			//$cerrado = $CI->datasis->dameval("SELECT numero FROM rcaj WHERE cajero='$cajero' AND fecha='$fecha' ");
				$reversar=($reve==1) ? anchor('ventas/rcaj/reversar/'.$numero, 'Reversar'):'';
				if($tipo=='T'){
					return image('caja_precerrada.gif',"Cajero Pre-Cerrado: $cajero",$atts).'<h3>'.anchor("ventas/rcaj/forcierre/$numero/", 'Cerrar cajero').'</h3><center>'.anchor('formatos/ver/RECAJA/'.$numero, ' Ver cuadre de caja').br().$reversar.'</center>';
				}else{
					return image('caja_cerrada.gif',"Cajero Cerrado: $cajero",$atts).'<h3>Cerrado</h3><center>'.anchor('formatos/ver/RECAJA/'.$numero, ' Ver cuadre de caja').br().$reversar.'</center>';
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

			$grid = new DataGrid('Recepci&oacute;n de cajas para la fecha: '.$filter->fecha->value);

			$select=array('c.cobrador AS cajero','c.f_factura AS fecha','a.tipo','a.recibido','a.numero','d.nombre');
			$grid->db->select($select);
			$grid->db->from('sfpa AS c');
			$grid->db->join('scaj AS d','c.cobrador=d.cajero');
			$grid->db->join('rcaj AS a','a.cajero=c.cobrador AND a.fecha=c.f_factura','right');

			//$grid->db->from('rcaj AS a');
			//$grid->db->join('sfpa AS c','a.cajero=c.cobrador AND a.fecha=c.f_factura','RIGHT');
			//$grid->db->join('scaj AS d','c.cobrador=d.cajero','LEFT');

			$grid->db->groupby('c.cobrador');
			$grid->use_function('iconcaja');

			$reve=$this->secu->puede('12A0');
			$grid->column('Numero'     ,'<sinulo><#numero#>|---</sinulo>','align=\'center\'');
			$grid->column('Fecha'      ,'<dbdate_to_human><#fecha#></dbdate_to_human>');
			$grid->column('Cajero'     ,'<#cajero#>-<#nombre#>','align=\'center\'');
			$grid->column('Recibido'   ,'<sinulo><nformat><#recibido#></nformat>|0.00</sinulo>','align=\'right\'');
			//$grid->column('Ingreso'    ,'<nformat><#ingreso#></nformat>' ,'align=\'right\'');
			$grid->column('Status/Caja','<iconcaja><#cajero#>|<#fecha#>|<#numero#>|<#tipo#>|'.$reve.'</iconcaja>','align="center"');
			$grid->column('Ver html'   ,"<siinulo><#numero#>|---|$urih</siinulo>",'align=\'center\'');
			$grid->build();
			//echo $grid->db->last_query();
			$data['content'] .= $grid->output;
		}

		$data['title']   = '<h1>Recepci&oacute;n de cajas</h1>';
		$data['head']    = $this->rapyd->get_head();
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
				$form->$obj->autocomplete=false;
				if($o==1){
					$form->$obj->in=$sobj;
					$form->$obj->readonly=true;
					$form->$obj->type='inputhidden';
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
			$form->$obj->autocomplete=false;
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
				$form->$obj->autocomplete=false;
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
			$form->$obj->autocomplete=false;
			$form->$obj->type='inputhidden';
		}
		$form->$obj->type='';

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
				val=mul*valor;
				$("#c"+obj).val(roundNumber(val,2));
				$("#c"+obj+"_val").text(nformat(val,2));
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

			$("#TEFE_val").text(nformat(TEFE,2));
			$("#TOTR_val").text(nformat(TOTR,2));
			$("#TGLOB_val").text(nformat(TOTR+TEFE,2));
		}';
		$this->rapyd->jquery[]='$("input[name^=\'cOT\']").calculator( {showOn: "button",useThemeRoller:true,onClose: function(value, inst) { gtotal(); }, onClose: function(value, inst) { gtotal(); }} );';

		//hace el precierre
		if ($form->on_success()){
			$dbfecha  = $this->db->escape($fecha);
			$dbcajero = $this->db->escape($cajero);

			//$mSQL="DELETE FROM sitems WHERE MID(numero,1,1)='_' AND cajero=$dbcajero";
			//$this->db->simple_query($mSQL);

			//$mSQL="DELETE FROM sfac WHERE MID(numero,1,1)='_' AND cajero=$dbcajero";
			//$this->db->simple_query($mSQL);

			$mSQL="SELECT c.tipo, IFNULL(SUM(aa.monto),0) AS monto FROM
				(SELECT b.tipo, b.monto AS monto
				FROM sfac AS a
				JOIN sfpa AS b ON a.transac=b.transac
				WHERE a.fecha=$dbfecha AND b.cobrador=$dbcajero AND a.tipo_doc<>'X' AND MID(a.numero,1,1)<>'_'
				UNION ALL
				SELECT e.tipo,e.monto AS monto
				FROM sfpa AS e
				WHERE e.f_factura=$dbfecha AND e.cobrador=$dbcajero AND e.tipo_doc IN ('AB','AN')
				) AS aa
				RIGHT JOIN tarjeta AS c ON aa.tipo=c.tipo GROUP BY c.tipo";

			/*$objfecha = DateTime::createFromFormat('Ymd', $fecha);
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
			GROUP BY tipo";*/

			//Toma en cuenta los retiros
			$rret=array();
			if ($this->db->table_exists('rret')){
				$retiquery = $this->db->query("SELECT tipo,SUM(monto) AS monto FROM rret WHERE cajero=$dbcajero AND fecha=$dbfecha AND cierre IS NULL GROUP BY tipo");
				foreach ($retiquery->result() as $rreti){
					$rret[$rreti->tipo] = $rreti->monto;
				}
			}

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$str='';
				$ingreso= $rrecibido=$parcial=0;
				$numero = $this->datasis->fprox_numero('ningreso');
				$transac= $this->datasis->fprox_numero('ntransa');
				$arr    = array('numero'=>$numero);

				foreach ($query->result() as $row){
					if($row->tipo == 'EF'){
						$nobj='TEFE';
					}else{
						$nobj='cOTR'.$row->tipo;
					}

					$recibido = (isset($form->$nobj))? (empty($form->$nobj->newValue))? 0.00 :floatval($form->$nobj->newValue) : 0.00;
					if(array_key_exists($row->tipo, $rret)) $recibido += $rret[$row->tipo];
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

				//Esto es tambien del cajero nocturno
				//$mSQL="UPDATE sfpa JOIN sfac ON sfac.numero=sfpa.numero AND sfpa.tipo_doc=CONCAT(sfac.tipo_doc, IF(sfac.referen='M','E',sfac.referen))
				//SET sfpa.cierre=$dbnumero
				//WHERE sfpa.f_factura>=$dbfecha_s AND SUBSTRING(sfpa.tipo_doc,2,1)!='X' AND sfpa.cobrador=$dbcajero AND MID(sfpa.hora,1,2)>18";
				//$this->db->simple_query($mSQL);

				if($this->db->table_exists('rret')){
					$mSQL="UPDATE rret SET cierre=$dbnumero WHERE cajero=$dbcajero AND fecha=$dbfecha AND cierre IS NULL";
					$this->db->simple_query($mSQL);
				}
			}
			logusu('rcaj',"Pre-cerro cajero $cajero de $fecha");
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
		$data['head']   .= style('jquery.calculator.css');
		$data['head']   .= script('plugins/jquery.calculator.min.js');
		$data['head']   .= script('plugins/jquery.calculator-es.js');
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

		/*$form->titulos = new freeField("","","Recibido &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		$form->titulos1 = new freeField("","","Sistema &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		$form->titulos1->in='titulos';
		$form->titulos2 = new freeField("","","Diferencia");
		$form->titulos2->in='titulos';*/

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
					$form->$obj->autocomplete=false;
					if($o==0) $sobj=$obj; else $form->$obj->in=$sobj;
					if($o!=0) {
						$form->$obj->readonly=true;
						$form->$obj->type='inputhidden';
					}
				}
			}

			foreach($arr AS $o=>$nobj){
				$obj = 't'.$nobj;
				$form->$obj = new inputField('Totales:', $obj);
				$form->$obj->style='text-align:right';
				$form->$obj->size=10;
				$form->$obj->insertValue=$totales[$o];
				$form->$obj->rule='numeric';
				$form->$obj->autocomplete=false;
				if($o==0) $sobj=$obj; else $form->$obj->in=$sobj;
				$form->$obj->readonly=true;
				$form->$obj->type='inputhidden';
			}
		}

		$form->button('btn_reg', 'Regresar',"javascript:window.location='".site_url('ventas/rcaj/filteredgrid/search')."'", 'BL');
		$form->submit('btnsubmit','Cerrar cajero');
		$form->build_form();

		$this->rapyd->jquery[]='$(":input").numeric(".");';
		$this->rapyd->jquery[]='$(\'input[name^="recibido"]\').bind("keyup",function() { gtotal(); });';
		$this->rapyd->jquery[]='$(\'input[name^="recibido"]\').bind("mouseleave",function() { gtotal(); });';
		$this->rapyd->jquery[]='$("#df1").submit(function() { return confirm("Estas seguro de realizar el Cierre?"); })';
		$this->rapyd->jquery[]='function gtotal(){
			TRECI=TSIS=TDIFE=0;
			$(\'input[name^="recibido"]\').each(function(i,e){
				nombre=this.name;
				tipo=nombre.substring(nombre.length-2,nombre.length);

				if($(this).val().length>0){
					recibido   =parseFloat($(this).val());
					sistema    =parseFloat($("#sistema"+tipo).val());
					diferencia=recibido-sistema;
					$("#diferencia"+tipo).val(roundNumber(diferencia,2));
					$("#diferencia"+tipo+"_val").text(nformat(diferencia,2));
				}
				if($(this).val().length>0) TRECI = TRECI+parseFloat($(this).val());
			});

			$(\'input[name^="diferencia"]\').each(function(i,e){
				if($(this).val().length>0){
					pval=parseFloat($(this).val());
					TDIFE = TDIFE+pval;
				}
			});

			$("#trecibido").val(roundNumber(TRECI,2));
			$("#tdiferencia").val(roundNumber(TDIFE,2));
			$("#trecibido_val").text(nformat(TRECI,2));
			$("#tdiferencia_val").text(nformat(TDIFE,2));
		}';
		$this->rapyd->jquery[]='gtotal();';

		//Cierre de caja
		if ($form->on_success()){
			$mSQL="SELECT a.fecha,c.tipo,c.nombre ,b.recibido,b.sistema,b.diferencia, a.transac
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
				}$rcajfecha=$this->db->escape($row->fecha);
				$transac=$row->transac;
				//$transac=$this->datasis->fprox_numero('ntransa');

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
				$sifact=$this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE cajero=".$this->db->escape($cajero)." AND fecha > $rcajfecha");
				if($sifact==0){
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
				}
				//echo $mmSQL;

				//Crea el movimiento en smov
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
					$ban=$this->db->simple_query($mSQL);
					if($ban==false) memowrite($mSQL,'rcaj');
				}
				logusu('rcaj',"Cerro cajero $cajero de $fecha");

				redirect('ventas/rcaj/filteredgrid/search');
			}
		}

		$attr=array(
			'class'  => 'ui-state-default ui-corner-all',
			'onclick'=> "javascript:window.location='".site_url('ventas/rcaj/filteredgrid/search')."'",
			'value'  => 'Regresar'
		);

		$credito=$this->datasis->dameval("SELECT SUM((a.totalg-a.inicial)*IF(a.tipo_doc='D',-1,1)) AS credito
		FROM sfac AS a
		JOIN rcaj AS b ON a.fecha=b.fecha AND b.cajero=a.cajero
		WHERE a.referen='C' AND b.numero=${dbnumero}");

		$cont['credito'] = (empty($credito))? 0 : $credito;
		$cont['form']    = &$form;
		$data['content'] = $this->load->view('view_rcajcierre',$cont, true);
		//$data['content'] = $form->output;
		$data['title']   = heading('Recepci&oacute;n de cajas');
		$data['head']    = $this->rapyd->get_head().script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function reversar($numero){
		if($this->secu->puede('12A0')){
			$this->rapyd->uri->keep_persistence();
			$persistence = $this->rapyd->session->get_persistence('ventas/rcaj/filteredgrid', $this->rapyd->uri->gfid);
			$back= (isset($persistence['back_uri'])) ? $persistence['back_uri'] : 'ventas/rcaj/filteredgrid';

			$rt=$this->_reversar($numero);
		}
		redirect($back);
	}

	function _reversar($numero){
		$dbnumero=$this->db->escape($numero);
		$mSQL='SELECT tipo, transac FROM rcaj WHERE numero='.$dbnumero;
		$query = $this->db->query($mSQL);
		$er    = 0;

		if ($query->num_rows() > 0){
			$row = $query->row();
			if($row->tipo=='F'){
				$transac  = $row->transac;
				$dbtransac= $this->db->escape($transac);
				$sfecha   = date('Ymd');

				//Reversa los movimientos de caja
				$mmSQL='SELECT codbanc,monto,tipo_op FROM bmov WHERE transac='.$dbtransac;
				$qquery = $this->db->query($mmSQL);
				if ($qquery->num_rows() > 0){
					$rrow = $qquery->row();
					$caja = $rrow->codbanc;
					$monto= ($rrow->tipo_op=='NC') ? $rrow->monto : (-1)*$rrow->monto;

					$mSQL="CALL sp_actusal('$caja','$sfecha',$monto)";
					$ban =$this->db->simple_query($mSQL);
					if($ban==false) memowrite($mSQL,'rcaj');
					$er +=$ban;
				}
				$mSQL='DELETE FROM bmov WHERE transac='.$dbtransac;
				$ban =$this->db->simple_query($mSQL);
				if($ban==false) memowrite($mSQL,'rcaj');
				$er +=$ban;
			}

			$mSQL='DELETE FROM rcaj   WHERE numero='.$dbnumero;
			$ban =$this->db->simple_query($mSQL);
			if($ban==false) memowrite($mSQL,'rcaj');
			$er +=$ban;
			$mSQL='DELETE FROM itrcaj WHERE numero='.$dbnumero;
			$ban =$this->db->simple_query($mSQL);
			if($ban==false) memowrite($mSQL,'rcaj');
			$er +=$ban;
			$mSQL='UPDATE rret SET cierre=NULL WHERE numero='.$dbnumero;
			$ban =$this->db->simple_query($mSQL);
			if($ban==false) memowrite($mSQL,'rcaj');
			$er +=$ban;
		}

		return ($er>0) ? false: true;
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
		$mSQL="ALTER TABLE `rcaj` CHANGE COLUMN `estampa` `estampa` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
		$this->db->simple_query($mSQL);
	}
}
