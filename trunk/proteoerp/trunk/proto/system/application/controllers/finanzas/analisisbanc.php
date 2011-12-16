<?php
class Analisisbanc extends Controller {

	var $ante=0;
	function Analisisbanc(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->library('calendar');		
		$this->datasis->modulo_id('50C',1);
		$this->rapyd->config->set_item("theme","repo");
	}
	
	function index() {
		
		$this->rapyd->load("dataform");

		$script ='
		function filtro(){
			$("#tr_valor").hide();
			$("#tr_oper").hide();
			campo=$("#campo").val();
			valor.value="";
			if(campo.length>0){
				if(campo=="tipo_op"){
					$("#tr_oper").show();
				}else{
					$("#tr_valor").show();
				}
			}			
		}
		$(document).ready(function(){
				$("#tr_cod").hide();
				$(".inputnum").numeric(".");
				$("#campo").change(function () { filtro(); }).change();
			});
			';		
		
		$fechad=date("Y/m/d");
		$date = new DateTime();
		$date->setDate(substr($fechad, 0, 4),substr($fechad, 5, 2),substr($fechad, 8,2));
		$date->modify("-6 month");
		$fechad=$date->format("Y/n/d");
		
		$filter = new DataForm("finanzas/analisisbanc/movimientos/process");
		$filter->title('Filtro de Caja y Bancos');
		$filter->script($script, "create");
		$filter->script($script, "modify");
		
		//$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		//$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		//$filter->fechad->clause  =$filter->fechah->clause="where";
		//$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		//$filter->fechad->insertValue = $fechad;
		//$filter->fechah->insertValue = date("Y/m/d");
		//$filter->fechah->size=$filter->fechad->size=10;
		//$filter->fechad->operator=">=";
		//$filter->fechah->operator="<=";
		//
		$filter->ano = new inputField("","ano");
		$filter->ano->insertValue=date("Y");
		//
		//$filter->campo = new dropdownField("Filtrar por:", "campo");
		//$filter->campo->option("","");
		//$filter->campo->option("numero","Numero");
		//$filter->campo->option("tipo_op","Tipo Operaci&oacute;n");
		//$filter->campo->option("benefi","Beneficiario");
		//$filter->campo->style='width:150px;';
		//
		//$filter->oper = new dropdownField("Tipo:", "oper");
		//$filter->oper->option("","");
		//$filter->oper->option('CH',"Cheque");
		//$filter->oper->option('DE',"Deposito");
		//$filter->oper->option('NC',"Nota de Cr&eacute;dito");
		//$filter->oper->option('ND',"Nota de D&eacute;bito");
		//$filter->oper->style='width:150px;';
		//
		//$filter->valor = new inputField("", "valor");
		//$filter->valor->size=20;
		//$filter->valor->rule = "trim";
		//
		//$filter->orden = new dropdownField("Ordenar por:", "orden");
		//$filter->orden->option("fecha","Fecha");		
		//$filter->orden->option("numero","Numero");
		//$filter->orden->option("tipo_op","Tipo Operaci&oacute;n");
		//$filter->orden->option("benefi","Beneficiario");		
		//$filter->orden->style='width:150px;';		
		//
		//$filter->desc = new radiogroupField("desc", "desc", array("0"=>"Ascendente","1"=>"Descendente"),'0');		
		//$filter->desc->in='orden';

		$filter->submit("btnsubmit","Buscar");
		$filter->build_form();
		
		$mSQL_1=$this->db->query("SELECT tipocta,CASE (tipocta)
			WHEN 'C' THEN 'CUENTAS CORRIENTES'
			WHEN 'A' THEN 'CUENTAS DE AHORRO'
			WHEN 'P' THEN 'CUENTAS DE PARTICIPACION A PLAZO'
			WHEN 'K' THEN 'CAJAS' END as tipocta2  FROM banc WHERE tbanco<>'CAJ' GROUP BY tipocta");//
			
		$mSQL=$this->db->query("SELECT tbanco, banco, numcuent, cuenta, saldo, codbanc, tipocta,moneda	
            FROM  banc 
            WHERE activo='S' AND tbanco<>'CAJ'
            ORDER BY moneda,codbanc");//tbanco='CAJ',
      
   	$mSQL_12=$this->db->query("SELECT tipocta,CASE (tipocta)
			WHEN 'C' THEN 'CUENTAS CORRIENTES'
			WHEN 'A' THEN 'CUENTAS DE AHORRO'
			WHEN 'P' THEN 'CUENTAS DE PARTICIPACION A PLAZO'
			WHEN 'K' THEN 'CAJAS' END as tipocta2  FROM banc WHERE tbanco='CAJ' GROUP BY tipocta");
			
		$mSQL2=$this->db->query("SELECT tbanco, banco, numcuent, cuenta, saldo, codbanc, tipocta,moneda	
          FROM  banc 
          WHERE activo='S' AND tbanco='CAJ'
          ORDER BY moneda,codbanc");
           
    $mSQLmon=$this->db->query("SELECT moneda FROM banc WHERE activo='S' GROUP BY moneda");
    //$meses=array();
  	//for($i=1;$i<=12;$i++){
  	//	if($i<=date("m")){
  	//		if($i==1)$meses[1]="Enero";
  	//		if($i==2)$meses[2]="Febrero";  			
  	//		if($i==3)$meses[3]="Marzo";
  	//		if($i==4)$meses[4]="Abril";
  	//		if($i==5)$meses[5]="Mayo";
  	//		if($i==6)$meses[6]="Junio";
  	//		if($i==7)$meses[7]="Julio";
  	//		if($i==8)$meses[8]="Agosto";
  	//		if($i==9)$meses[9]="Septiembre";
  	//		if($i==10)$meses[10]="Octubre";
  	//		if($i==11)$meses[11]="Noviembre";
  	//		if($i==12)$meses[12]="Diciembre";
  	//	}  			
  	//}

		if(isset($_POST['ano']))$ano=$_POST['ano']; else $ano=date("Y");
		//$data2['meses']= $meses;
		$data2['ano']= date("Y");//$ano;
		$data2['monedas']= $mSQLmon->result();
		$data2['grupo2']= $mSQL_12->result();
		$data2['detalle2']= $mSQL2->result();		
		$data2['grupo']= $mSQL_1->result();
		$data2['detalle']= $mSQL->result();
		$data['content']= //$filter->output.
		$this->load->view('view_analisisbanc', $data2,TRUE);

		//$data['content'] = $filter->output;
		$data['title']   = "<h1>Relaci&oacute;n de Caja y Bancos</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
function movmes(){}
function meses(){
	$this->rapyd->load("datagrid2","dataform");
	$cod=$this->uri->segment(4);
	$ano=$this->uri->segment(5);
	
	$filter = new DataForm("finanzas/analisisbanc/meses/process");
	$filter->title('Filtro de Caja y Bancos');
	
	//$filter->ano = new inputField("A&ntilde;o","ano");
	//$filter->ano->insertValue=$ano;
	//$filter->ano->maxlength=4;
	//$filter->ano->size=5;
	$filter->ano = new dropdownField("A&ntilde;o", "ano");
	$filter->ano->option($ano,$ano);                   
	$filter->ano->options('SELECT ano,ano as ano2 FROM bsal GROUP BY ano ORDER BY ano');
	$filter->ano->style='width:80px;';
	
			
	$filter->cod = new inputField("C&oacute;digo","cod");
	$filter->cod->insertValue=$cod;
	$filter->cod->size=5;
	$filter->cod->maxlength=2;	
	$filter->cod->type='hidden';
		
	$filter->button("btnsubmit", "Buscar", form2uri(site_url("/finanzas/analisisbanc/meses"),array('cod','ano')), $position="BL");//
	$filter->build_form(); 	
	
	function blanco($num){
		if(empty($num)||$num==0){
		 return '';
		}else{
			return number_format($num,2,',','.');
		}
	}	
	///////////////////////////////////////////////////////////////////////////////////////////////////////
	$bdata=$this->datasis->damerow("SELECT saldo01,saldo02,saldo03,saldo04,saldo05,saldo06,saldo06,saldo07,saldo08,saldo09,saldo10,saldo11,saldo12 FROM bsal WHERE codbanc='$cod' AND ano='$ano'");
	$d=array();
	//print_r($bdata);	
	//if($bdata['saldo01']!=0){
	if(!($bdata==NULL)){
		for($i=0;$i<12;++$i){
			$r='saldo'.str_pad($i+1,2,'0',STR_PAD_LEFT);
			$d[$i]['saldo']=$bdata[$r];
			switch($i+1){ 
				case 1 :$d[$i]['mes']= 'Enero'     ;$d[$i]['m']=$i+1;break;   
				case 2 :$d[$i]['mes']= 'Febrero'   ;$d[$i]['m']=$i+1;break;  
				case 3 :$d[$i]['mes']= 'Marzo'     ;$d[$i]['m']=$i+1;break; 
				case 4 :$d[$i]['mes']= 'Abril'     ;$d[$i]['m']=$i+1;break; 
				case 5 :$d[$i]['mes']= 'Mayo'      ;$d[$i]['m']=$i+1;break; 
				case 6 :$d[$i]['mes']= 'Junio'     ;$d[$i]['m']=$i+1;break; 
				case 7 :$d[$i]['mes']= 'Julio'     ;$d[$i]['m']=$i+1;break; 
				case 8 :$d[$i]['mes']= 'Agosto'    ;$d[$i]['m']=$i+1;break;      
				case 9 :$d[$i]['mes']= 'Septiembre';$d[$i]['m']=$i+1;break; 
				case 10:$d[$i]['mes']= 'Octubre'   ;$d[$i]['m']=$i+1;break; 
				case 11:$d[$i]['mes']= 'Noviembre' ;$d[$i]['m']=$i+1;break;  
				case 12:$d[$i]['mes']= 'Diciembre' ;$d[$i]['m']=$i+1;break;   
			}
		}
	}
	$link="finanzas/analisisbanc/movimientos/$cod/$ano/<#m#>";
	//print_r($d);
	$grid = new DataGrid2("Movimientos por meses",$d);
	$grid->use_function('blanco');	
	$grid->column("Mes" ,anchor($link, '<#mes#>') ,"align=left");
  $grid->column("Saldo" ,"<blanco><#saldo#></blanco>"   ,"align=right");
  //$grid->column("m","<#m#>","align=left");
	$grid->build();

	//memowrite( $grid->db->last_query());
	$salida= anchor("finanzas/analisisbanc/","Atras");
	
	$data['content'] = $filter->output.$salida.$grid->output;
	$data['title']   = "<h1>Relaci&oacute;n de Caja y Bancos</h1>";
	$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
	$this->load->view('view_ventanas', $data);  
	}
	
function movimientos(){	
		$this->rapyd->load("dataform","datagrid2");
		
		if((isset($_POST['fechad']))&&($_POST['fechad']!='')){
		 $fechad=$_POST['fechad'];		 
		}else{
				$fechad=date("Y/m/d");
				$date = new DateTime();
				$date->setDate(substr($fechad, 0, 4),substr($fechad, 5, 2),substr($fechad, 8,2));
				$date->modify("-6 month");
				$fechad=$date->format("Y/n/j");
		}
		 	
		if((isset($_POST['fechah']))&&($_POST['fechah']!=''))
		 $fechah=$_POST['fechah'];
		 else		 
		 	$fechah=date("Y/m/d");
		 			
		if(isset($_POST['cod']))
		 $cod=$_POST['cod'];
		 elseif($cod=$this->uri->segment(4)){		 	
		 	}else redirect("finanzas/analisisbanc");
		if(isset($_POST['campo']))
		 $campo=$_POST['campo'];
		 else
		 	$campo='';
		if(isset($_POST['oper'])){
		 $oper=$_POST['oper'];
		 if($oper!='')$valor=$oper;
		}
		if(isset($_POST['valor'])){
		 $valor=$_POST['valor'];
			if($valor==''){
				if(isset($_POST['oper'])){
				 $oper=$_POST['oper'];
				 if($oper!='')$valor=$oper;
				 else
				 	$campo='';
				}			
		}
		}else{
			$campo='';
		}
		if(isset($_POST['oper'])){
			$oper=$_POST['oper'];
		 if($oper!='')$valor=$oper;
		}
		if(isset($_POST['orden'])){
			$orden=$_POST['orden'];
		 if($orden=='')$orden='fecha';
		}else{
			$orden='fecha';
		}
		if(isset($_POST['desc'])){
			$desc=$_POST['desc'];
		 if($desc=='')$desc='asc';
		 if($desc=='0')$desc='asc';
		 if($desc=='1')$desc='desc';		 
		}else{
			$desc='asc';
		}
		
		$cod=$this->uri->segment(4);
		$ano=$this->uri->segment(5);
		$mes=$this->uri->segment(6);
		
		if($cod=='process'){			
			if(isset($_POST['cod'])){				
				$cod=$_POST['cod'];				
			}else{
				$cod='|!';
				$ano='';
				$mes='';
			}			
		}else{
			if((!empty($ano))&&(!(empty($mes)))){
					for ($dia=28;$dia<=31;$dia++)
      	   if(checkdate($mes,$dia,$ano)){ $mes=str_pad($mes,2,'0',STR_PAD_LEFT); $fechah="$ano/$mes/$dia";}
      	   $fechad="$ano/$mes/01";
			}
		}
		//echo $cod.'/'.$ano.'/'.$mes;
		//echo $fechad."---".$fechah;

		$script ='		
		function filtro(){
			$("#tr_valor").hide();
			$("#tr_oper").hide();
			campo=$("#campo").val();
			valor.value="";
			if(campo.length>0){
				if(campo=="tipo_op"){
					$("#tr_oper").show();
				}else{
					$("#tr_valor").show();
				}
			}
		}
		$(document).ready(function(){
				$("#tr_cod").hide();
				$(".inputnum").numeric(".");
				$("#campo").change(function () { filtro(); }).change();
			});
			';		
				
		$filter = new DataForm("finanzas/analisisbanc/movimientos/process");
		$filter->title('Filtro de Caja y Bancos');
		$filter->script($script, "create");
		$filter->script($script, "modify");		
		$filter->fechad = new dateonlyField("Desde", "fechad",'m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";		
		$filter->fechad->insertValue = $fechad;
		$filter->fechah->insertValue = $fechah;
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";
    
		$filter->cod = new inputField("","cod");
		$filter->cod->insertValue=$cod;
		$filter->cod->readonly=TRUE;
		$filter->cod->type='hidden';
		
		$filter->campo = new dropdownField("Filtrar por:", "campo");
		$filter->campo->option("","");
		$filter->campo->option("numero","Numero");
		$filter->campo->option("tipo_op","Tipo Operaci&oacute;n");
		$filter->campo->option("benefi","Beneficiario");
		$filter->campo->style='width:150px;';
		
		$filter->oper = new dropdownField("Tipo:", "oper");
		$filter->oper->option("","");
		$filter->oper->option('CH',"Cheque");
		$filter->oper->option('DE',"Deposito");
		$filter->oper->option('NC',"Nota de Cr&eacute;dito");
		$filter->oper->option('ND',"Nota de D&eacute;bito");
		$filter->oper->style='width:150px;';
		
		$filter->valor = new inputField("", "valor");
		$filter->valor->size=20;
		$filter->valor->rule = "trim";
		
		$filter->orden = new dropdownField("Ordenar por:", "orden");
		$filter->orden->option("fecha","Fecha");		
		$filter->orden->option("numero","Numero");
		$filter->orden->option("tipo_op","Tipo Operaci&oacute;n");
		$filter->orden->option("benefi","Beneficiario");		
		$filter->orden->style='width:150px;';		
		
		$filter->desc = new radiogroupField("desc", "desc", array("0"=>"Ascendente","1"=>"Descendente"),'0');		
		$filter->desc->in='orden';
		

		$filter->submit("btnsubmit","Buscar");
		
		
		$filter->build_form();
		
		if($cod!='|!')
			$fila=$this->datasis->damerow("SELECT moneda,banco,numcuent FROM banc WHERE codbanc='$cod'");
		else{
			$fila['moneda']='';
			$fila['numcuent']='';
			$fila['banco']='';			
		}
		//print_r($fila);
		function blanco($num,$m=''){
			if(empty($num)||$num==0){
			 return '';
			}else{
				return number_format($num,2,',','.').$m;
			}
		}
		$atts = array(
              'width'     =>'800',
              'height'    =>'600',
              'scrollbars'=>'yes',
              'status'    =>'yes',
              'resizable' =>'yes',
              'screenx'   =>'5',
              'screeny'   =>'5');
		$link="finanzas/bmovshow/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>";
		
		////////////////////////consulta grid//////////////////////////////
		$this->db->select("codbanc,fecha, numero, tipo_op, monto*(tipo_op IN ('DE','NC')) as ingresos,monto*(tipo_op NOT IN ('DE','NC')) as egresos,CONCAT(concepto,' ',concep2,' ',concep3) as concep, 
                    benefi,monto*(tipo_op IN ('DE','NC'))-monto*(tipo_op NOT IN ('DE','NC')) as saldo,CASE (tipo_op)
			WHEN 'CH' THEN 'Cheque'
			WHEN 'DE' THEN 'Deposito'
			WHEN 'NC' THEN 'Nota de Cr&eacute;dito'
			WHEN 'ND' THEN 'Nota de D&eacute;bito'
		 	END
		 AS tipo");
		$this->db->from('bmov as a');
		if($campo&&$valor)$consult="AND $campo LIKE '%$valor%'";
			else
				$consult='';
		if($cod!='|!')
			$b="codbanc='$cod' AND ";
		else
			$b='';
			
		if(strpos(substr($fechad, 6, 4), '/')==NULL){
			$fechad2 = substr($fechad, 3, 4).substr($fechad, 0, 2);//.substr($fechad, 0,2);
			$fechah2 = substr($fechah, 3, 4).substr($fechah, 0, 2);//.substr($fechah, 0,2));			
		}else{
			$fechad2 = substr($fechad, 0,4).substr($fechad, 5,2) ;
			$fechah2 = substr($fechah, 0,4).substr($fechah, 5,2) ;		
		}
		
		$this->db->where("$b EXTRACT(YEAR_MONTH FROM fecha) BETWEEN '$fechad2' AND '$fechah2'$consult");//a.fecha like '%-$mm-%'  
		$this->db->orderby($orden.' '.$desc);
		$query = $this->db->get();
		//memowrite($this->db->last_query());
		////////////////////////consulta grid//////////////////////////////		
		///////////////////////SALDO ANTERIOR//////////////////////////////
		$ddata=array();
		$banmes='';
		$anterior=0;
		foreach ($query->result_array() as $row){			
			if($banmes!=substr($row['fecha'], 5, 2)){
				$ban=$banmes=substr($row['fecha'], 5, 2);
				$ano2=substr($row['fecha'], 0, 4);
				if((1*($banmes))==1){
					$ban=13;
					$ano2=(1*$ano2)-1;
				}
				
				$campo='saldo'.str_pad(((1*$ban)-1),2,'0',STR_PAD_LEFT);
				$sal=$this->datasis->dameval("SELECT $campo FROM bsal WHERE codbanc='$cod' AND ano='$ano2'");

				if($sal==NULL){
				$sal=0;
				$row['salAnterior']=number_format($sal,2,',','.');
				}else{
					$anterior=$sal;
					$row['salAnterior']=number_format($anterior,2,',','.');
					$anterior=$anterior+($row['saldo']);
				}				
			}else{
				$row['salAnterior']=number_format($anterior,2,',','.');
				$anterior=$anterior+($row['saldo']);
				//$campo='';
			}	
			//$campo;//
			$ddata[]=$row;
		}
		///////////////////////SALDO ANTERIOR//////////////////////////////
//print_r($ddata);
		if($cod!='|!')
			$o="Movimientos del Banco ".$fila['banco']." cuenta #".$fila['numcuent']." desde $fechad hasta $fechah";
		else
			$o="Todos los Bancos";
		$grid = new DataGrid2($o,$ddata);		
		$grid->use_function('blanco');
		if($cod=='|!')
		$grid->column("Banco", "<#codbanc#>" ,'align=left');		
		$grid->column("Fecha", "<dbdate_to_human><#fecha#></dbdate_to_human>");
		$grid->column("Numero", anchor_popup($link, '<#numero#>',$atts),'nowrap=yes');
		$grid->column("Tipo Operaci&oacute;n", "<#tipo#>" ,'align=left');
		$grid->column("Ingresos", "<number_format><#ingresos#>|2|,|.</number_format>" ,"align=right");
		$grid->column("Egresos", "<number_format><#egresos#>|2|,|.</number_format>" ,"align=right");
		$grid->column("Saldo", "<number_format><#saldo#>|2|,|.</number_format>" ,"align=right");
		$grid->column("Saldo Anterior", "<#salAnterior#>" ,"align=right");
		$grid->column("Beneficiario", "<#benefi#>" ,'align=left');
		$grid->column("Concepto", "<#concep#>" ,'align=left');
		$grid->totalizar('ingresos','egresos','saldo');
		
		$grid->build();
		
		//memowrite( $grid->db->last_query());
		if(!isset($ano2)){
			$ano2=$ano;
			}
		$salida= anchor("finanzas/analisisbanc/meses/$cod/$ano2","Atras");

		$data['content']= //$filter->output.
		$salida.$grid->output;
		$data['title']   = "<h1>Relaci&oacute;n de Caja y Bancos</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}
} 
?>
  
    