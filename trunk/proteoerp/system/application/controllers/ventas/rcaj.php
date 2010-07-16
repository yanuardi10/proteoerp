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
		
		$this->rapyd->load("datagrid");
		$this->rapyd->load("datafilter");
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
		$filter->cajero->options("SELECT cajero, nombre FROM scaj ORDER BY nombre");
		
		$filter->buttons("reset","search");
		$filter->build();

		$data['content'] = $filter->output;
		
		function iconcaja($cajero,$fecha){
			$CI =& get_instance();
			$cerrado = $CI->datasis->dameval("SELECT numero FROM rcaj WHERE cajero='$cajero' AND fecha='$fecha' ");
			$atts=array('align'=>'LEFT','border'=>'0');
			$fecha=str_replace('-','',$fecha);
			$atRI = array(
              'width'     => '800','height' => '600',
              'scrollbars'=> 'yes','status' => 'yes',
              'resizable' => 'yes','screenx'=> '0',
              'screeny'   => '0');
			if (!empty($cerrado))
				return image('caja_cerrada.gif',"Cajero Cerrado: $cajero",$atts)."<h3>Cerrado</h3><br><center>";//.anchor_popup("/supermercado/cierre/doccierre/$cerrado",'Ver Cuadre',$atRI).' '.anchor_popup("/supermercado/cierre/ventasdia/$fecha/$caja",'Detalle de Ventas',$atRI).'</center>';/
				
			else
				return image('caja_abierta.gif',"Cajero Abierto: $cajero",$atts)."<h3>Abierto</h3>".'<center><a href='.site_url("ventas/rcaj/forcierre/99/$cajero/$fecha").'>Cerrar Cajero</a></center>';
		}
				
		$data['forma'] ='';
		if($this->rapyd->uri->is_set("search") AND !empty($filter->fecha->value)){
			$fecha=$filter->fecha->value;
					
			$urih = anchor('formatos/verhtml/RECAJA/<#numero#>','Descargar html');
			$urip = anchor('formatos/ver/RECAJA/<#numero#>'    ,'Descargar pdf');
    	
			$grid = new DataGrid("Lista de Cierres de caja");
			$grid->order_by("fecha","desc");
			$grid->per_page=15;
						
			$grid = new DataGrid('Recepcion de cajas para la fecha: '.$filter->fecha->value);
			$select=array('b.cajero','b.fecha','a.cajero','a.recibido','a.ingreso','a.numero');
			
			$grid->db->select($select);
			$grid->db->from('rcaj as a');
			$grid->db->join('sfac as b','a.cajero=b.cajero','LEFT');
			$grid->db->groupby("a.cajero","b.fecha");
			$grid->use_function('iconcaja','number_format');
    	
			$grid->column("Numero"   ,'numero',"align='center'");
			$grid->column("Fecha"    ,'<dbdate_to_human><#fecha#></dbdate_to_human>');
			$grid->column("Cajero"   ,"cajero"  ,"align='center'");
			$grid->column("Recibido" ,"recibido","align='right'");
			$grid->column("Ingreso"  ,"ingreso" ,"align='right'");
			$grid->column("Status/Caja","<iconcaja><#cajero#>|<#fecha#></iconcaja>",'align="center"');
			$grid->column("Imprimir",$urip,"align='center'");
			$grid->build();
			//echo $grid->db->last_query();
			$data['content'] .= $grid->output;
		}
		
		$data['title']   = "<h1>Recepci&oacute;n de cajas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function forcierre() {
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("datagrid");
		$this->rapyd->load("fields");
		
		$caja    = $this->uri->segment(4);
		$cajero  = $this->uri->segment(5);
		$qfecha  = $this->uri->segment(6);
		if (!$caja or !$cajero or !$qfecha ) redirect('/supermercado/cierre');
   
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
		 
		$mSQL="SELECT a.concepto, a.descrip, sum(b.monto) sistema 
		FROM tardet a 
		JOIN sfpa b ON b.banco=a.concepto 
		JOIN sfac c ON b.transac=c.transac and c.tipo_doc='F'
		WHERE a.tarjeta='CT' AND b.tipo='CT'  AND c.cajero='$cajero' 
		AND b.fecha=$qfecha AND SUBSTRING(b.numero,1,1)<>'X' 
		GROUP BY a.concepto
		UNION 
		SELECT concepto, descrip, '0' sistema 
		FROM tardet 
		WHERE tarjeta='CT' AND concepto NOT IN 
		(SELECT a.concepto
		FROM tardet a 
		JOIN sfpa b ON b.banco=a.concepto 
		JOIN sfac c ON b.transac=c.transac and c.tipo_doc='F'
		WHERE a.tarjeta='CT' AND b.tipo='CT'  AND c.cajero='$cajero' 
		AND b.fecha=$qfecha AND SUBSTRING(b.numero,1,1)<>'X' 
		GROUP BY a.concepto)
		ORDER BY concepto";
		
		//echo $mSQL;
		$i=0;
		$ctsistema=0;
		$js_cesArray=array();
		$cestatiket=array();
		$query = $this->db->query($mSQL);
		foreach ($query->result() as $row){
			$catts['name']=$catts['id']='CCESTA'.$row->concepto;
			$cantidad = form_input($catts);
			
			$atts['name']=$atts['id']='TCESTA'.$row->concepto;
			$js_cesArray[]=$row->concepto;
			$atts['onChange']="montosolo('".$atts['name']."'); tcestatiket();";
			$monto = form_input($atts);//.form_hidden('TCESTA_T'.$i,$row->concepto);
			
			$attsr['name']=$attsr['id']='SCESTA'.$row->concepto;
			$attsr['value']=number_format($row->sistema,'2',',','.');
			$sistema = form_input($attsr);
			$attsr['value']='';

			$pasa=array();
			$pasa['descrip']='<b>'.$row->concepto.'</b> '.$row->descrip;
			$pasa['cant']   =$cantidad;
			$pasa['monto']  =$monto;
			$pasa['sistema']=$sistema;
			$i++;
			$cestatiket[]=$pasa;
			$ctsistema+=$row->sistema;
		}$ces_filas=$i;
		$query->free_result();
		
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
		
		$attRecCtk=$attSisCtk=$attDifCtk=$attsr;
		$attRecCtk['name']=$attRecCtk['id']='CTKRECI';
		$attSisCtk['name']=$attSisCtk['id']='CTKSIST';
		$attDifCtk['name']=$attDifCtk['id']='CTKDIFE';
		$attSisCtk['value']=number_format($ctsistema,'2',',','.');
		
		$attRecOtr=$attSisOtr=$attDifOtr=$attsr;
		$attRecOtr['name']=$attRecOtr['id']='OTRRECI';
		$attSisOtr['name']=$attSisOtr['id']='OTRSIST';
		$attDifOtr['name']=$attDifOtr['id']='OTRDIFE';
		$attSisOtr['value']=number_format($fpsistema,'2',',','.');
		
		$attRecRec=$attSisRec=$attDifRec=$attsr;
		$attRecRec['name']=$attRecRec['id']='RECRECI';
		$attSisRec['name']=$attSisRec['id']='RECSIST';
		$attDifRec['name']=$attDifRec['id']='RECDIFE';
		$attSisRec['value']=number_format($fpsistema+$ctsistema+$msistema,'2',',','.');
		
		$resumen=array(
			0 => array(
				'descrip' =>'Efectivo',
				'recibido'=>form_input($attRecEfe),//.form_hidden('QEFEGLOBAL'),
				'sistema' =>form_input($attSisEfe),//.form_hidden('QEFESISTEMA',$msistema),
				'diferen' =>form_input($attDifEfe),
				),
			1 => array(
				'descrip' =>'Cesta Tickets',
				'recibido'=>form_input($attRecCtk),
				'sistema' =>form_input($attSisCtk),//.form_hidden('QCESSISTEMA',$ctsistema),
				'diferen' =>form_input($attDifCtk),
				),
			3 => array(
				'descrip' =>'Otros',
				'recibido'=>form_input($attRecOtr),
				'sistema' =>form_input($attSisOtr),//.form_hidden('QOTRSISTEMA',$fpsistema),
				'diferen' =>form_input($attDifOtr),
				),
			4 => array(
				'descrip' =>'Recibido',
				'recibido'=>form_input($attRecRec),
				'sistema' =>form_input($attSisRec),//.form_hidden('QTOTSISTEMA',$fpsistema+$ctsistema+$msistema),
				'diferen' =>form_input($attDifRec),
				)
		);
		
		$script="<script type='text/javascript'>
		function caldiferencia() {
			var pre=new Array('EFE','CTK','OTR','REC');
			var i=0,acumulador=0;
			for(i=0;i<3;i++){
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
		function tcestatiket() {
			var pre=new Array('".join("','",$js_cesArray)."');
			var i=0,acumulador=0;
			for(i=0;i<$ces_filas;i++){
				valor=des_number_format(document.getElementById('TCESTA'+pre[i]).value,'.',',');
				acumulador=acumulador+valor;
			}
			recibido=document.getElementById('CTKRECI');
			recibido.value=number_format(acumulador,'.',',');
			caldiferencia();
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
		
		$cestagrid = new DataGrid('Cesta Tiket',$cestatiket);
		$cestagrid->per_page = count($cestatiket);
		$cestagrid->column("Descripcion","<#descrip#>");
		$cestagrid->column("Cantidad"   ,"<#cant#>",'align="RIGHT"');
		$cestagrid->column("Monto"      ,"<#monto#>"   ,'align="RIGHT"');
		$cestagrid->column("Sistema"    ,"<#sistema#>" ,'align="RIGHT"');
		$cestagrid->build();
		$data['listad'] .= $cestagrid->output;
		
		$resugrid = new DataGrid('Resumen',$resumen);
		$resugrid->per_page = count($resumen);
		$resugrid->column("Descripcion","<#descrip#>");                 
		$resugrid->column("Recibido"   ,"<#recibido#>",'align="RIGHT"');
		$resugrid->column("Sistema"    ,"<#sistema#>" ,'align="RIGHT"');
		$resugrid->column("Diferencia" ,"<#diferen#>" ,'align="RIGHT"');
		$resugrid->build();
		$data['listab'] = $resugrid->output.'Primer control Fiscal '.$pcfiscal.'<br>Ultimo control Fiscal '.$ucfiscal.'<br>Observaciones<br>'.$observa;

		$data['submit']=form_submit('mysubmit', 'Guardar');
		
		$atts=array('onsubmit'=>"return confirm('Seguro que desea Guardar')");
		
		$hidden = array('otr_filas' => $otr_filas, 'ces_filas' => $ces_filas);
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
		
		$ntransac   = str_pad($this->datasis->prox_sql("ntransa"),   8, "0", STR_PAD_LEFT) ;
		$numero  = str_pad($this->datasis->prox_sql("nrcaja"), 8, "0", STR_PAD_LEFT) ;
		
		$trayecto  =$this->input->post('trayecto');
		
		//** Totales **
		
		//Efectivo
		$efrecibido   = str_replace(",",'.',str_replace(".","",$_POST['EFERECI']));
		$efsistema    = str_replace(",",'.',str_replace(".","",$_POST['EFESIST']));
		$efdiferencia = str_replace(",",'.',str_replace(".","",$_POST['EFEDIFE']));
		
		//Cesta Tickets'
		
		$ctrecibido   = str_replace(",",'.',str_replace(".","",$_POST['CTKRECI']));
		$ctsistema    = str_replace(",",'.',str_replace(".","",$_POST['CTKSIST']));
		$ctdiferencia = str_replace(",",'.',str_replace(".","",$_POST['CTKDIFE']));
		
		//Otras formas de pago
		
		$ofrecibido   = str_replace(",",'.',str_replace(".","",$_POST['OTRRECI']));
		$ofsistema    = str_replace(",",'.',str_replace(".","",$_POST['OTRSIST']));
		$ofdiferencia = str_replace(",",'.',str_replace(".","",$_POST['OTRSIST']));
		
		//Resumen
		
		$torecibido   = str_replace(",",'.',str_replace(".","",$_POST['RECRECI']));
		$tosistema    = str_replace(",",'.',str_replace(".","",$_POST['RECSIST']));
		$todiferencia = str_replace(",",'.',str_replace(".","",$_POST['RECDIFE']));
		
		
		//echo 'efect.1 '.$efrecibido;
		//echo 'efect.2 '.$efsistema; 
		//echo 'efect.3 '.$efdiferencia;
		//echo 'cesta.1'.$ctrecibido;
		//echo 'cesta.2'.$ctsistema; 
		//echo 'cesta.3'.$ctdiferencia;
		//echo 'otras 1'.$ofrecibido;
		//echo 'otras 2'.$ofsistema; 
		//echo 'otras 3'.$ofdiferencia;
		//echo 'resum 1'.$torecibido;
		//echo 'resum 2'.$tosistema; 
		//echo 'resum 3'.$todiferencia;
		
		// Ahora Carga el efectivo
		
		$query_1="INSERT INTO itrcaj( numero,tipo,recibido,sistema,diferencia)
				        VALUES   ($numero,'EF',$efrecibido,$efsistema,$efdiferencia)";
		//echo '<pre>'.$query_1.'</pre>';
		$this->db->query($query_1);
					
		// Carga Cesta Tickets
		$cesta = 0;
		$query = $this->db->query("SELECT concepto, descrip FROM tardet WHERE tarjeta='CT'");
		foreach ($query->result() as $row){
			
			$tipo=$row->concepto;
			$mmonto    = $this->__post("TCESTA".$row->concepto);
			$msistema  = $_POST["SCESTA".$row->concepto];	
			$can       = intval($_POST["CCESTA".$row->concepto]);
			$mmonto    = str_replace(",",".",str_replace(".","",$mmonto));
			$msistema  = str_replace(",",".",str_replace(".","",$msistema));
			$diferencia=$msistema-$mmonto;
			
			//echo  '<pre>'.'Cesta :'.$tipo.'</pre>';
			//echo  '<pre>'.'Cesta Cajero :'.$mmonto.'</pre>' ;
	  	//echo  '<pre>'.'Cesta Sist: '.$msistema.'</pre>';
	  	//echo  '<pre>'.'Cant :'.$can.'</pre>' ;
	  	
			if ($mmonto<>0 or $msistema<>0){
				$query="INSERT INTO itrcaj( numero,tipo,recibido,sistema,diferencia)
				        VALUES   ($numero,'$tipo',$mmonto,$msistema,$diferencia)";
				//echo '<pre>'.$query.'</pre>';
				$this->db->query($query);
				        
			}
		};
	
		// Otras formas de Pago
		$mSQL = 'SELECT tipo,nombre FROM tarjeta WHERE tipo NOT IN ("EF","CT","NC","ND", "DE", "IR","DP") ORDER BY tipo';
		$query = $this->db->query($mSQL);
		$efectos = 0;
		foreach ( $query->result() as $row ){   

			$tipo=$row->tipo;
			$mmonto    = (empty($_POST["TOFP".$row->tipo])) ? 0 : $_POST["TOFP".$row->tipo];
			$msistema  = (empty($_POST["SOFP".$row->tipo])) ? 0 : $_POST["SOFP".$row->tipo];
			$can       = (empty($_POST["COFP".$row->tipo])) ? 0 : $_POST["COFP".$row->tipo];
			$can       = intval($can);
			$mmonto    = str_replace(",",".",str_replace(".","",$mmonto));
			$msistema  = str_replace(",",".",str_replace(".","",$msistema));
			$diferencia=$msistema-$mmonto;
			
			//echo  '<pre>'.'Tarj :'.$tipo.'</pre>';
			//echo  '<pre>'.'Tarj Cajero :'.$mmonto.'</pre>' ;
	  	//echo  '<pre>'.'Tarj Sist: '.$msistema.'</pre>';
	  	//echo  '<pre>'.'Cant :'.$can.'</pre>' ;
			
			if ($mmonto<>0 or $msistema<>0){
				$query="INSERT INTO itrcaj( numero,tipo,recibido,sistema,diferencia)
				        VALUES   ($numero,'$tipo',$mmonto,$msistema,$diferencia)";
				//echo '<pre>'.$query.'</pre>';
				$this->db->query($query);
				        
			}
		}
			
		$observa='EF '.$efrecibido.'  CESTA '.$ctrecibido.' OTRAS '.$ofrecibido;
		$mSQL="INSERT INTO rcaj (fecha,cajero,tipo,usuario,caja,recibido,ingreso,parcial,observa,numero,transac,estampa,hora )
		VALUE ($qfecha,$cajero,'F','".$this->session->userdata['usuario']."',$caja,$torecibido,$tosistema,$todiferencia,'$observa',$numero,$ntransac, now(),'".date("h:m:s")."')";
		//echo '<pre>'.$mSQL.'</pre>';
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
	function instalar(){
		$mSQL="CREATE TABLE `itrcaj` (`numero` VARCHAR (8), `tipo` VARCHAR (15), `recibido` DECIMAL (17,2), `sistema` DECIMAL (17,2), `diferencia` DECIMAL (17,2),PRIMARY KEY (`numero`, `tipo`))";		
		$this->db->simple_query($mSQL);
	} 
}
?>