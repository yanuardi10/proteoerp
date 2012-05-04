<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Caudi extends validaciones {
	var $titp = 'Arqueo de cajas';
	var $url  = 'finanzas/caudi';

	function Caudi(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('528',1);
	}

	function index(){
		redirect('finanzas/caudi/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp);
		$filter->db->from('caudi AS a');
		$filter->db->join('usuario AS b','a.uscaja = b.us_codigo');

		$filter->caja = new dropdownField('Caja', 'caja');
		$filter->caja->style='width:200px';
		$filter->caja->option('','Todos');
		$filter->caja->options("SELECT codbanc,CONCAT_WS('-',codbanc,banco) AS val FROM banc WHERE tbanco = 'CAJ' ORDER BY banco");

		$filter->uscaja = new dropdownField('Responsable', 'uscaja');
		$filter->uscaja->style='width:200px';
		$filter->uscaja->option('','Todos');
		$filter->uscaja->options("SELECT us_codigo,us_nombre FROM usuario ORDER BY us_nombre");

		$filter->status = new dropdownField('Estatus','status');
		$filter->status->style='width:200px';
		$filter->status->option('','Todos');
		$filter->status->option('P','Pendientes');
		$filter->status->option('A','Anulados');
		$filter->status->option('C','Cerrados');

		$filter->observa = new inputField('Observaci&oacute;n','observa');

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<str_pad><#id#>|10|0|0</str_pad>');

		function status($sto){
			if($sto=='P')
				return 'Pendiente';
			elseif($sto=='A')
				return 'Anulado';
			else
				return 'Cerrado';
		}

		$grid = new DataGrid('');
		$grid->use_function('str_pad','status');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('N&uacute;mero',$uri,'id','align="left"');
		$grid->column_orderby('Caja','caja','caja','align="left"');
		$grid->column_orderby('Responsable','<#uscaja#>-<#us_nombre#>','uscaja','align="left"');
		$grid->column_orderby('Estatus','<status><#status#></status>','status','align="left"');
		$grid->column_orderby('Saldo','<nformat><#saldo#></nformat>','saldo','align="right"');
		$grid->column_orderby('Monto','<nformat><#monto#></nformat>','monto','align="right"');
		$grid->column_orderby('Diferencia','<nformat><#diferencia#></nformat>','diferencia','align="right"');

		$grid->add($this->url.'/agregar');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);

	}

	function agregar(){
		$this->rapyd->load('datafilter','datagrid');

		$atts = array(
			'width'      => '800',
			'height'     => '600',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '0',
			'screeny'    => '0');

		$recep = anchor('ventas/rcaj/forcierre/','Recepcion de Caja');


		$data['content'] = '';

		function iconcaja($cajero,$audi){
			if(empty($audi)){
				return image('caja_abierta.gif'   ,"Caja disponible");
			}else{
				return image('caja_precerrada.gif',"En auditoria"  );
			}
		}

		$data['forma'] ='';

		$urip = anchor('formatos/ver/RECAJA/<#numero#>','Descargar html');
		$urih = anchor_popup('formatos/verhtml/RECAJA/<#numero#>', ' Ver cuadre pantalla',$atts);
		$grid = new DataGrid('Lista de caja');
		$grid->order_by('fecha','desc');
		$grid->per_page=15;

		$grid = new DataGrid('Arqueo de cajas');

		$select=array('a.codbanc AS cajero','a.banco AS nombre','a.saldo','b.id AS audi');
		$grid->db->select($select);
		$grid->db->from('banc AS a');
		$grid->db->join('caudi AS b', 'a.codbanc=b.caja AND b.status=\'P\'','left');
		$grid->db->where('a.tbanco','CAJ');
		$grid->use_function('iconcaja');

		$link=anchor('finanzas/caudi/auditoria/<#cajero#>','Arquear');
		$grid->column('Caja'    ,'<iconcaja><#cajero#>|<#audi#></iconcaja>');
		$grid->column('Cajero'  ,'<#cajero#>-<#nombre#>');
		$grid->column('Saldo'   ,'<nformat><#saldo#></nformat>' ,'align=\'right\'');
		$grid->column('Arqueo'  ,$link);
		//$grid->column('Ver html' ,"<siinulo><#numero#>|---|$urih</siinulo>",'align=\'center\'');
		$action = 'javascript:window.location=\''.site_url($this->url.'/filteredgrid').'\'';
		$grid->button('btn_reg', 'Regresar', $action,'TR');
		$grid->build();
		//echo $grid->db->last_query();
		$data['content'] .= $grid->output;

		$data['title']   = '<h1>Recepci&oacute;n de cajas</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


	function auditoria($caja=NULL){
		$this->rapyd->load('dataform');
		$url_submit='finanzas/caudi/auditoria/'.$caja.'/process';

		$sel=array('a.id','b.tipo','b.monto','a.estampa AS fecha','a.observa','a.uscaja');
		$this->db->select($sel);
		$this->db->from('caudi   AS a');
		$this->db->join('itcaudi AS b','b.id_caudi=a.id');
		$this->db->where('a.caja'  ,$caja);
		$this->db->where('a.status','P');
		$qq=$this->db->get();

		$pmontos=array();
		if ($qq->num_rows() > 0){
			foreach ($qq->result() as $chrow){
				$pmontos[$chrow->tipo] = $chrow->monto;
			}
			$idcaudi = $chrow->id;
			$fecha   = $chrow->fecha;
			$uobser  = $chrow->observa;
			$uuscaj  = $chrow->uscaja;
			$accion  = 'modify';
		}else{
			$accion  = 'create';
			$fecha = date('Y-m-d');
		}

		$form  = new DataForm($url_submit);

		$attr=array(
			'class'  => 'ui-state-default ui-corner-all',
			'onclick'=> "javascript:window.location='".site_url('finanzas/caudi/filteredgrid')."'",
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
		$arr=array('OEFE'=>'Otras denominaciones');
		foreach($arr AS $obj=>$titulo){
			$form->$obj = new inputField($titulo, $obj);
			$form->$obj->style='text-align:right';
			$form->$obj->css_class='efectivo';
			$form->$obj->rule='numeric';
			$form->$obj->size=10;
			$form->$obj->autocomplete=false;
			if(isset($pmontos['EF'])){
				$form->$obj->insertValue=$pmontos['EF'];
			}
		}
		//Fin del efectivo

		//Inicio otras formas de pago
		$c_otrp=0;
		$mSQL='SELECT a.tipo,a.nombre FROM tarjeta a WHERE a.tipo NOT IN (\'EF\',\'RP\',\'NC\',\'ND\', \'DE\',\'DP\',\'RI\',\'IR\',\'RP\') AND a.activo=\'S\'';
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

				if(isset($pmontos[$row->tipo])){
					$form->$obj->insertValue=$pmontos[$row->tipo];
				}

			}
		}
		//Fin otras formas de pago

		//Inicio tabla Resumen
		$arr=array('TOTR'=>'Total de otras formas de pago','TEFE'=>'Total Efectivo','TGLOB'=>'Total Global');
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
		//$form->$obj->type='';

		//$form->$obj->readonly=false;
		//$form->$obj->insertValue='';
		//fin Resumen

		$form->uscaja = new dropdownField('Responsable', 'uscaja');
		$form->uscaja->rule='required';
		$form->uscaja->style='width:200px';
		$form->uscaja->option('','Seleccionar');
		$form->uscaja->options("SELECT us_codigo,us_nombre FROM usuario ORDER BY us_nombre");
		if(isset($uuscaj)) $form->uscaja->insertValue=$uuscaj;

		$form->observa = new textareaField('Observaci&oacute;n', 'observa');
		$form->observa->cols = 20;
		$form->observa->rows = 4;
		if(isset($uobser)) $form->observa->insertValue=$uobser;

		$form->submit('btnsubmit','Cerrar cajero');
		$form->build_form();

		$this->rapyd->jquery[]='var denomi='.json_encode($denomi).';';
		$this->rapyd->jquery[]='$(":input").not("#observa").numeric(".");';
		$this->rapyd->jquery[]='$(\'input[name^="cEFE"]\').bind("keyup",function() { gtotal(); });';
		$this->rapyd->jquery[]='$(\'input[name^="cOTR"]\').bind("keyup",function() { gtotal(); });';
		$this->rapyd->jquery[]='$(\'input[name^="FEFE"]\').bind("keyup",function() { gtotal(); });';
		$this->rapyd->jquery[]='$(\'input[name^="OEFE"]\').bind("keyup",function() { gtotal(); });';
		$this->rapyd->jquery[]='$("#df1").submit(function() { return confirm("Estas seguro de que desea guardar el arqueo?"); })';
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

			$("#TEFE").val(roundNumber(TEFE,2));
			$("#TOTR").val(roundNumber(TOTR,2));
			$("#TGLOB").val(roundNumber(TOTR+TEFE,2));

			$("#TEFE_val").text(nformat(TEFE,2));
			$("#TOTR_val").text(nformat(TOTR,2));
			$("#TGLOB_val").text(nformat(TOTR+TEFE,2));
		}';
		$this->rapyd->jquery[]='$("input[name^=\'cOT\']").not("input[id$=\'IR\']").not("input[id$=\'RI\']").not("input[id$=\'RP\']").calculator( {showOn: "button",useThemeRoller:true,onClose: function(value, inst) { gtotal(); }, onClose: function(value, inst) { gtotal(); }} );';
		$this->rapyd->jquery[]='gtotal();';

		//hace el arqueo
		if ($form->on_success()){
			$dbfecha = $this->db->escape($fecha);
			$dbcaja  = $this->db->escape($caja);
			$usuario = $this->secu->usuario();
			$transac = $this->datasis->fprox_numero('ntransa');
			$estampa = date('Y-m-d');
			$hora    = date('H:i:s');
			$saldo   = $this->datasis->dameval("SELECT saldo FROM banc WHERE codbanc=$dbcaja");
			$observa = $form->observa->newValue;
			$uscaja  = $form->uscaja->newValue;

			$mSQL="SELECT c.tipo, 0 AS monto FROM tarjeta AS c WHERE activo='S'";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$rrecibido=0;

				$itcaudi    = array();

				foreach ($query->result() as $row){
					if($row->tipo == 'EF'){
						$nobj='TEFE';
					}else{
						$nobj='cOTR'.$row->tipo;
					}

					$monto = (isset($form->$nobj))? (empty($form->$nobj->newValue))? 0.00 :floatval($form->$nobj->newValue) : 0.00;
					if( $monto > 0 ){
						$arr=array(
							'caja'   => $caja,
							'tipo'   => $row->tipo,
							'monto'  => $monto,
							'estampa'=> $estampa,
						);
						$rrecibido += $monto;
						$itcaudi[]=$arr;
					}
				}

				if($accion=='create'){
					$arr = array(
						'caja'       => $caja,
						'uscaja'     => '',
						'status'     => 'P',
						'saldo'      => $saldo,
						'monto'      => $rrecibido,
						'diferencia' => $saldo-$rrecibido,
						'observa'    => '',
						'transac'    => $transac,
						'estampa'    => $estampa,
						'hora'       => $hora,
						'usuario'    => $usuario,
						'observa'    => $observa,
                        'uscaja '    => $uscaja
					);
					$mSQL = $this->db->insert_string('caudi', $arr);
					$this->db->simple_query($mSQL);

					$id_caudi=$this->db->insert_id();
				}else{
					$id_caudi=$idcaudi;
					$dbobserva= $this->db->escape($observa);
					$dbuscaja = $this->db->escape($uscaja);

					$mSQL="UPDATE caudi SET
						monto=$rrecibido,
						diferencia=saldo-$rrecibido,
						observa = $dbobserva,
						uscaja  = $dbuscaja
						WHERE id=$id_caudi";
					$this->db->simple_query($mSQL);

					$mSQL="DELETE FROM itcaudi WHERE id_caudi=$id_caudi";
					$this->db->simple_query($mSQL);
				}

				foreach($itcaudi AS $rrow){
					$rrow['id_caudi']=$id_caudi;

					$mSQL = $this->db->insert_string('itcaudi', $rrow);
					$this->db->simple_query($mSQL);
				}
			}
			if($accion='create'){
				logusu('arqueo',"Arqueo de la caja $caja realizado, monto $rrecibido");
			}else{
				logusu('arqueo',"Arqueo de la caja $caja modificado, monto $rrecibido");
			}
			redirect('finanzas/caudi/filteredgrid/search');
		}

		$cont['efeadic'] = 1;
		$cont['form']    = &$form;
		$cont['c_efe']   = $c_efe*2;
		$cont['c_otrp']  = $c_otrp;
		$cont['regresa'] = form_button($attr,'Regresar');
		$data['content'] = $this->load->view('view_rcaj',$cont, true);
		$data['title']   = '<h1>Arqueo de caja '.$caja.' Fecha '.dbdate_to_human($fecha).'</h1>';
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$data['head']   .= style('jquery.calculator.css');
		$data['head']   .= script('plugins/jquery.calculator.min.js');
		$data['head']   .= script('plugins/jquery.calculator-es.js');
		$this->load->view('view_ventanas', $data);
	}


	function instalar(){

	}
}
