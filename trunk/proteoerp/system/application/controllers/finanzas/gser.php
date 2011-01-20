<?php
class gser extends Controller {

	function gser(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(604,1);
	}
	function index() {
		redirect("finanzas/gser/filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Gastos",'gser');
		//		$filter->db->select("numero,fecha,vence,nombre,totiva,totneto");
		//		$filter->db->from('gser');

		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d");
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";

		$filter->numero = new inputField("N&uacute;mero", "numero");
			
		$filter->proveed = new inputField("Proveedor", "proveed");
		//$filter->proveed->append($boton);
		$filter->proveed->db_name = "proveed";

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/gser/dataedit/show/<#id#>','<#numero#>');
		$uri3  = anchor('finanzas/mgser/dataedit/modify/<#fecha#>/<#numero#>/<#proveed#>','Modificar');
		
		$grid = new DataGrid();
		$grid->order_by("numero","desc");
		$grid->per_page = 15;
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Vence","<dbdate_to_human><#vence#></dbdate_to_human>","align='center'");
		$grid->column("Nombre","nombre");
		$grid->column("IVA"  ,"totiva"  ,"align='right'");
		$grid->column("monto" ,"totneto" ,"align='right'");

		$grid->add("finanzas/gser/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Gastos</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'mgas',
			'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'descrip'=>'descrip'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'descrip_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Articulo',
			'script'  =>array('lleva(<#i#>)'));
	
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$script='$(function(){
			$(".inputnum").numeric(".");
			
			';

		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;odigo',
			'nombre'=>'Nombre',
			'rif'=>'Rif'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre','reteiva'=>'__reteiva'),
			
			'titulo'  =>'Buscar Proveedorr');
		$bSPRV=$this->datasis->modbus($mSPRV);

		$mBANC=array(
			'tabla'   =>'banc',
			'columnas'=>array(
			'codbanc' =>'C&oacute;odigo','tbanco'=>'Entidad',
			'banco'=>'Banco',
			'dire1'=>'Direcci&oacute;n','proxch'=>'ProxChe'),
			'filtro'  =>array('codbanc'=>'C&oacute;digo','banco'=>'Banco'),
			'retornar'=>array('codbanc'=>'codb1','proxch'=>'cheque1'),
			
			'titulo'  =>'Buscar Banco');
		$bBANC=$this->datasis->modbus($mBANC);

		$mRETE=array(
			'tabla'   =>'rete',
			'columnas'=>array(
			'codigo' =>'C&oacute;odigo','activida'=>'Actividad',
			'base1'=>'Base1','pama1'=>'Para Mayores','tari1'=>'%'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','activida'=>'Actividad'),
			'retornar'=>array('codigo'=>'creten','base1'=>'__base','tari1'=>'__tar','pama1'=>'__pama'),
			'titulo'  =>'Buscar Retencion',
			'script'=>array('islr()'));
		$bRETE=$this->datasis->modbus($mRETE);
			



		$do = new DataObject("gser");
		$do->rel_one_to_many('gitser', 'gitser',array('id'=>'idgser'));
		//			$do->rel_pointer('itspre','sinv','itspre.codigo=sinv.codigo','sinv.descrip as sinvdescrip');

		$edit = new DataDetails("Gastos", $do);
		$edit->back_url = site_url("finanzas/gser/filteredgrid");
		$edit->set_rel_title('gitser','Gasto <#o#>');

		$edit->script($script,'create');
		$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->tipo_doc =  new dropdownField("Tipo Documento", "tipo_doc");

		$edit->tipo_doc->option('FC',"Factura");
		$edit->tipo_doc->option('ND',"Nota Debito");
		$edit->tipo_doc->option('AD',"Amortizaci&oacute;n");
		$edit->tipo_doc->option('GA',"Gasto de N&oacute;mina");
		$edit->tipo_doc->style="30px";

		$edit->ffactura = new DateonlyField("Fecha Documento", "ffactura","d/m/Y");
		//$edit->ffactura->insertValue = date("Y-m-d");
		//$edit->ffactura->mode="autohide";
		$edit->ffactura->size = 10;

		$edit->fecha = new DateonlyField("Fecha Recepci&oacute;n", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		//			$edit->fecha->mode="autohide";
		$edit->fecha->size = 10;

		$edit->vence = new DateonlyField("Fecha Vencimiento", "vence","d/m/Y");
		//			$edit->vence->insertValue = date("Y-m-d");
		//			$edit->vence->mode="autohide";
		$edit->vence->size = 10;

		$edit->id = new inputField("ID", "id");
		$edit->id->size = 10;
		$edit->id->mode="autohide";
		$edit->id->maxlength=8;
		$edit->id->when=array("");

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		//$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;
		//$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('create','modify');

		$edit->proveedg = new inputField("Proveedor","proveed");
		$edit->proveedg->size = 10;
		$edit->proveedg->maxlength=5;
		$edit->proveedg->append($bSPRV);
		$edit->proveedg->rule= "required";
		//$edit->proveedg->mode="autohide";

		$edit->nfiscal  = new inputField("Control Fiscal", "nfiscal");
		$edit->nfiscal->size = 10;
		$edit->nfiscal->maxlength=20;
		//			$edit->nfiscal->css_class='inputnum';

		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 30;
		$edit->nombre->maxlength=40;
		$edit->nombre->rule= "required";

		$edit->totpre  = new inputField("Sub.Total", "totpre");
		$edit->totpre->size = 10;
		$edit->totpre->css_class='inputnum';
		$edit->totpre->onkeyup="valida(0)";

		$edit->totbruto= new inputField("Total", "totbruto");
		$edit->totbruto->size = 10;
		$edit->totbruto->css_class='inputnum';
		$edit->totbruto->onkeyup="valida(0)";

		$edit->totiva = new inputField("TOTAL IVA", "totiva");
		//			$edit->totiva->mode="autohide";
		$edit->totiva->css_class ='inputnum';
		//			$edit->totiva->when=array('show','modify');
		$edit->totiva->size      = 10;
		$edit->totiva->onkeyup="valida(0)";

		$edit->codb1 = new inputField("Banco","codb1");
		$edit->codb1->size = 5;
		$edit->codb1->maxlength=2;
		$edit->codb1->append($bBANC);

		$edit->tipo1 =  new dropdownField("Tipo", "tipo1");
		$edit->tipo1->option('',"Tipo");
		$edit->tipo1->option('C',"Cheque");
		$edit->tipo1->option('D',"Debito");
		$edit->tipo1->style="20px";

		$edit->cheque1 = new inputField("N&uacute;mero","cheque1");
		$edit->cheque1->size = 15;
		$edit->cheque1->maxlength=20;

		$edit->benefi = new inputField("Beneficiario","benefi");
		$edit->benefi->size = 30;
		$edit->benefi->maxlength=40;

		$edit->monto1= new inputField("Monto", "monto1");
		$edit->monto1->size = 10;
		$edit->monto1->css_class='inputnum';

		$edit->credito= new inputField("Saldo Cr&eacute;dito", "credito");
		$edit->credito->size = 10;
		$edit->credito->css_class='inputnum';

		$edit->comprob1= new inputField("Comprobante externo", "comprob1");
		$edit->comprob1->size = 20;
		$edit->comprob1->css_class='inputnum';

		$edit->transac= new inputField("Transacci&oacute;n", "transac");
		$edit->transac->size = 10;
		$edit->transac->css_class='inputnum';
		$edit->transac->mode="autohide";
		$edit->transac->when=array('show','modify');

		$edit->creten = new inputField("Literal","creten");
		$edit->creten->size = 10;
		$edit->creten->maxlength=10;
		$edit->creten->append($bRETE);
		//			$edit->creten->rule= "required";

		$edit->breten = new inputField("Base","breten");
		$edit->breten->size = 10;
		$edit->breten->maxlength=10;
		$edit->breten->css_class='inputnum';
		$edit->breten->onkeyup="valida(0)";

		$edit->reten = new inputField("Monto","reten");
		$edit->reten->size = 10;
		$edit->reten->maxlength=10;
		$edit->reten->css_class='inputnum';
		$edit->reten->onkeyup="valida(0)";

		$edit->reteiva = new inputField("Retenci&oacute;n de IVA","reteiva");
		$edit->reteiva->size = 10;
		$edit->reteiva->maxlength=10;
		$edit->reteiva->css_class='inputnum';
		$edit->reteiva->onkeyup="valida(0)";

//		$edit->anticipo = new inputField("Anticipo","anticipo");
//		$edit->anticipo->size = 10;
//		$edit->anticipo->maxlength=10;
//		$edit->anticipo->css_class='inputnum';
//		$edit->anticipo->mode="autohide";
		
		$edit->totneto = new inputField("Total Neto","totneto");
		$edit->totneto->size = 10;
		$edit->totneto->maxlength=10;
		$edit->totneto->css_class='inputnum';

		//Campos para el detalle
		$edit->codigo = new inputField("C&oacute;digo <#o#>", "codigo_<#i#>");
		$edit->codigo->size=8;
		$edit->codigo->db_name='codigo';
		$edit->codigo->append($btn);
		$edit->codigo->rule="required";
		$edit->codigo->rel_id='gitser';
		$detalle->importe->mode="autohide";

		$edit->descrip = new inputField("Descripci&oacute;n <#o#>", "descrip_<#i#>");
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->rel_id='gitser';

		$edit->precio = new inputField("Precio <#o#>", "precio_<#i#>");
		$edit->precio->db_name='precio';
		$edit->precio->css_class='inputnum';
		$edit->precio->size=7;
		$edit->precio->rule='required';
		$edit->precio->rel_id='gitser';
		$edit->precio->onkeyup="totalizar(<#i#>)";

		$edit->tasaiva =  new dropdownField("IVA <#o#>", "tasaiva_<#i#>");
		$edit->tasaiva->options("SELECT tasa,tasa as t1 FROM civa ORDER BY fecha desc limit 1");
		$edit->tasaiva->options("SELECT redutasa,redutasa as rt1 FROM civa ORDER BY fecha desc limit 1");
		$edit->tasaiva->options("SELECT sobretasa,sobretasa as st1 FROM civa ORDER BY fecha desc limit 1");
		$edit->tasaiva->option('0','0.00');
		$edit->tasaiva->db_name='tasaiva';
		$edit->tasaiva->style="30px";
		$edit->tasaiva->rel_id   ='gitser';
		$edit->tasaiva->onchange="totalizar(<#i#>)";

		$edit->iva = new inputField("importe <#o#>", "iva_<#i#>");
		$edit->iva->db_name='iva';
		$edit->iva->css_class='inputnum';
		$edit->iva->rel_id   ='gitser';
		$edit->iva->size=7;
		$edit->iva->onkeyup="valida(<#i#>)";

		$edit->importe = new inputField("importe <#o#>", "importe_<#i#>");
		$edit->importe->db_name='importe';
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   ='gitser';
		$edit->importe->size=7;
		$edit->importe->onkeyup="valida(<#i#>)";

		$edit->departa =  new dropdownField("Departamento <#o#>", "departa_<#i#>");
		$edit->departa->option('',"Seleccion Departamento");
		$edit->departa->options("SELECT depto,CONCAT(depto,'-',descrip) as descrip FROM dpto ORDER BY descrip");
		$edit->departa->db_name='departa';
		$edit->departa->style="30px";
		$edit->departa->rel_id   ='gitser';

		$edit->sucursal =  new dropdownField("Sucursal <#o#>", "sucursal_<#i#>");
		$edit->sucursal->option('',"Seleccion Sucursal");
		$edit->sucursal->options("SELECT codigo,CONCAT(codigo,'-', sucursal)as sucursal FROM sucu ORDER BY codigo");
		$edit->sucursal->db_name='sucursal';
		$edit->sucursal->style="20px";
		$edit->sucursal->rel_id   ='gitser';

		$edit->fechad = new inputField("fecha <#o#>", "fecha_<#i#>");
		$edit->fechad->db_name='fecha';
		$edit->fechad->size=0;
		$edit->fechad->rel_id   ='gitser';
		$edit->fechad->mode="autohide";
		$edit->fechad->when=array("");

		$edit->numerod = new inputField("numero <#o#>", "numero_<#i#>");
		$edit->numerod->db_name='numero';
		$edit->numerod->size=0;
		$edit->numerod->rel_id   ='gitser';
		$edit->numerod->mode="autohide";
		$edit->numerod->when=array("");

		$edit->proveed = new inputField("Proveedor <#o#>", "proveed_<#i#>");
		$edit->proveed->db_name='proveed';
		$edit->proveed->size=0;
		$edit->proveed->rel_id   ='gitser';
		$edit->proveed->mode="autohide";
		$edit->proveed->when=array("");

		//			$edit->idgser = new inputField("id <#o#>", "idgser_<#i#>");
		//			$edit->idgser->db_name='idgser';
		//			$edit->idgser->size=0;
		//			$edit->idgser->rel_id   ='gitser';
		//			$edit->idgser->mode="autohide";
		//			$edit->idgser->when=array("");

		//fin de campos para detalle

		$edit->buttons("modify", "save", "undo", "delete", "back","add_rel");
		$edit->build();
		//			echo $edit->_dataobject->db->last_query();

		$conten["form"]  =&$edit;
		$data['content'] = $this->load->view('view_gser', $conten,true);
		$data['title']   = "<h1>Gastos</h1>";
		$data["head"]    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		if($do->get('numero')==""){
			$numero=$this->datasis->fprox_numero('ngser');
			$do->set('numero',$numero);
		}
		else $numero=$do->get('numero');
		$trans=$this->datasis->fprox_numero('ntransa');
		//		$do->set('numero',$numero);
		$do->set('transac',$trans);

		$datos=$do->get_all();
		$ivat=0;$subt=0;$total=0;
		$cana=$do->count_rel("gitser");
		for($i=0;$i<$cana;$i++){
			$do->set_rel('gitser','fecha',$do->get('fecha'),$i);
			$do->set_rel('gitser','numero',$numero,$i);

		}
		$tasa=0;$reducida=0;$sobretasa=0;$montasa=0;$monredu=0;$monadic=0;$exento=0;
		$con=$this->db->query("select tasa,redutasa,sobretasa from civa order by fecha desc limit 1");
		$t=$con->row('tasa');$rt=$con->row('redutasa');$st=$con->row('sobretasa');

		foreach($datos['gitser'] as $rel){
			$auxt=$rel['tasaiva'];
			if($auxt==$t) {
				$tasa+=$rel['iva'];
				$montasa+=$rel['precio'];
			}elseif($auxt==$rt) {
				$reducida+=$rel['iva'];
				$monredu+=$rel['precio'];
			}elseif($auxt==$st) {
				$sobretasa+=$rel['iva'];
				$monadic+=$rel['precio'];
			}else{
				$exento+=$rel['precio'];
			}
			$p=$rel['precio'];
			$i=$rel['iva'];
			$total+=$i+$p;
			$subt+=$p;
//			$rel['fecha']=$do->get('fecha');
		}
		$ivat=$total-$subt;
		$do->set('tasa',$tasa);$do->set('montasa',$montasa);
		$do->set('reducida',$reducida);$do->set('monredu',$monredu);
		$do->set('sobretasa',$sobretasa);$do->set('monadic',$monadic);
		$do->set('exento',$exento);
//		$do->set('totpre',$subt);
//		$do->set('totbruto',$total);
//		$do->set('totiva',$ivat);
	
		if ($do->get('monto1') != 0){
			$negreso  = $this->datasis->fprox_numero("negreso");
			$ncausado = "";
		}else{
			$ncausado = $this->datasis->fprox_numero("ncausado");
			$negreso  = "";
		}
		$do->set('negreso',$negreso);
		$do->set('ncausado',$ncausado);
		//		echo $this->datasis->traevalor('pais');
		if ($this->datasis->traevalor('pais') == 'COLOMBIA'){
			if($this->datasis->dameval("SELECT tiva FROM sprv WHERE proveed='".$do->get('proveed')."'")=='S'){
				foreach($datos['gitser'] as $rel){
					$mIVA  = $rel['iva'];
					$mRIVA = $this->datasis->dameval("SELECT reteiva FROM sprv WHERE proveed='".$do->get('proveed')."' ");
					if ($mRIVA == 0)$mRIVA = 50;
					$mRETEIVA = ROUND($do->get('precio')*($mIVA/100)*($mRIVA/100),0);
				}
				$do->set("RETESIMPLE",  $mRETEIVA);
				$retesumple = $mRETEIVA;
			}
		}
		$serie=$do->get('serie');
		if(empty($serie))
		$XSERIE = $numero;
		$do->set('serie',$XSERIE);
		$XORDEN=$do->get('orden');
		if ($do->get('tipo_doc') == 'ND')$XORDEN = '        ';

		if($do->get('credito')>0){
			$ncontrol=$this->datasis->fprox_numero('nsprm');
			$abonos=$do->get("monto1")+$do->get("anticipo");


			$IMPUESTO=$ivat;
			$VENCE=$do->get('vence');
			$ABONOS =$abonos+$do->get('reten')+$do->get('reteiva');
			if($this->datasis->traevalor('pais') == 'COLOMBIA')$ABONOS+=$do->get('reteica');
			$NFISCAL=$do->get('nfiscal');

			$sql="REPLACE INTO sprm (transac,
			numero,cod_prv,nombre,tipo_doc,fecha ,
			monto,impuesto,vence,abonos,tipo_ref,num_ref,
			nfiscal, control,reteiva,montasa,monredu,monadic,
			tasa,reducida, sobretasa,exento)
			values('".$trans."','".$numero."','".$do->get('proveed')."','".$do->get('nombre')."','".$do->get('tipo_doc')."',
			'".$do->get('fecha')."',".$total.",".$ivat.",'".$do->get('vence')."',
			".$ABONOS.",'','','".$do->get('nfiscal')."','".$ncontrol."',
			".$do->get('reteiva').",".$montasa.",".$monredu.",".$monadic.",
			".$tasa.",".$reducida.",".$sobretasa.",".$exento.")
			";
			$this->db->query($sql);

			if(empty($XORDEN)){
				$mANTICIPO = $do->get('anticipo');


				//Luego buscar anticipos
				$mSQL = "SELECT * FROM sprm WHERE cod_prv='".$do->get('proveed')."' ";

				$mSQL .= "AND tipo_doc='AN' AND num_ref='".$XORDEN."' ";

				$mSQL .= "AND tipo_ref='OS' ";
				$banticipo=$this->db->query($mSQL);
				
				$resultado=$banticipo->num_rows();

				foreach($banticipo->result() as $registro){
					$mTEMPO=$mANTICIPO;
					$mANTICIPO -=$registro['monto']-$registro['abonos'];
					$mMONTO=$registro['monto'];
					$mABONOS=$registro['abonos'];
					if($mANTICIPO >= 0){
						$mSQLant="UPDATE sprm SET abonos=".$mMONTO." WHERE tipo_doc='".$registro['tipo_doc']."' AND numero=".$registro['numero']." AND cod_prv='".$do->get('proveed')."'";
						$this->db->query($mSQLant);
					}else{
						$mANTICIPO = 0;
						$mSQLant="UPDATE sprm SET abonos=".$mTEMPO." WHERE tipo_doc='".$registro['tipo_doc']."' AND numero=".$registro['numero']." AND cod_prv='".$do->get('proveed')."'";
						$this->db->query($mSQLant);
					}
					if($mANTICIPO == 0) break;
					$campos=array('numppro','tipoppro','cod_prv','numero','tipo_doc','fecha','monto','abono','breten','creten','reten','reteiva','ppago','cambio','mora','transac');
					$valores=array($registro['numero'],$registro['tipo_doc'],$do->get('proveed'),$do->get('numero'),$do->get('tipo_doc'),$do->get('fecha'),$mMONTO,$mABONOS,0,'',0,0,0,0,0);
					$mSQL = "INSERT INTO itppro SET(".$campos.")VALUES(".$valores.") ";
					echo $msql;
				}
			}
		}

		return true;
	}

	function _pre_update($do){
		//				print("<pre>");
		//		echo $do->get_rel('itspre','preca',2);
		$datos=$do->get_all();
		$ivat=0;$subt=0;$total=0;
		$cana=$do->count_rel("gitser");
		$tasa=0;$reducida=0;$sobretasa=0;$montasa=0;$monredu=0;$monadic=0;$exento=0;
		$con=$this->db->query("select tasa,redutasa,sobretasa from civa order by fecha desc limit 1");
		$t=$con->row('tasa');$rt=$con->row('redutasa');$st=$con->row('sobretasa');

		for($i=0;$i<$cana;$i++){
			$do->set_rel('gitser','fecha',$do->get('fecha'),$i);
			$do->set_rel('gitser','numero',$do->get('numero'),$i);

		}
		foreach($datos['gitser'] as $rel){
			$auxt=$rel['tasaiva'];
			if($auxt==$t) {
				$tasa+=$rel['iva'];
				$montasa+=$rel['precio'];
			}elseif($auxt==$rt) {
				$reducida+=$rel['iva'];
				$monredu+=$rel['precio'];
			}elseif($auxt==$st) {
				$sobretasa+=$rel['iva'];
				$monadic+=$rel['precio'];
			}else{
				$exento+=$rel['precio'];
			}
			$p=$rel['precio'];
			$i=$rel['iva'];
			$total+=$i+$p;
			$subt+=$p;
		}
		$ivat=$total-$subt;
		$do->set('tasa',$tasa);$do->set('montasa',$montasa);
		$do->set('reducida',$reducida);$do->set('monredu',$monredu);
		$do->set('sobretasa',$sobretasa);$do->set('monadic',$monadic);
		$do->set('exento',$exento);
	

	if ($do->get('monto1') != 0){
			$negreso  = $this->datasis->fprox_numero("negreso");
			$ncausado = "";
		}else{
			$ncausado = $this->datasis->fprox_numero("ncausado");
			$negreso  = "";
		}
		$do->set('negreso',$negreso);
		$do->set('ncausado',$ncausado);
		//		echo $this->datasis->traevalor('pais');
		if ($this->datasis->traevalor('pais') == 'COLOMBIA'){
			if($this->datasis->dameval("SELECT tiva FROM sprv WHERE proveed='".$do->get('proveed')."'")=='S'){
				foreach($datos['gitser'] as $rel){
					$mIVA  = $rel['iva'];
					$mRIVA = $this->datasis->dameval("SELECT reteiva FROM sprv WHERE proveed='".$do->get('proveed')."' ");
					if ($mRIVA == 0)$mRIVA = 50;
					$mRETEIVA = ROUND($do->get('precio')*($mIVA/100)*($mRIVA/100),0);
				}
				$do->set("RETESIMPLE",  $mRETEIVA);
				$retesumple = $mRETEIVA;
			}
		}
		$serie=$do->get('serie');
		if(empty($serie))
		$XSERIE = $do->get('numero');
		$do->set('serie',$XSERIE);
		$XORDEN=$do->get('orden');
		if ($do->get('tipo_doc') == 'ND')$XORDEN = '        ';

		if($do->get('credito')>0){
			$ncontrol=$this->datasis->fprox_numero('nsprm');
			$abonos=$do->get("monto1")+$do->get("anticipo");


			$IMPUESTO=$ivat;
			$VENCE=$do->get('vence');
			$ABONOS =$abonos+$do->get('reten')+$do->get('reteiva');
			if($this->datasis->traevalor('pais') == 'COLOMBIA')$ABONOS+=$do->get('reteica');
			$NFISCAL=$do->get('nfiscal');

			$sql="REPLACE INTO sprm (transac,
			numero,cod_prv,nombre,tipo_doc,fecha ,
			monto,impuesto,vence,abonos,tipo_ref,num_ref,
			nfiscal, control,reteiva,montasa,monredu,monadic,
			tasa,reducida, sobretasa,exento)
			values('".$do->get('transac')."','".$do->get('numero')."','".$do->get('proveed')."','".$do->get('nombre')."','".$do->get('tipo_doc')."',
			'".$do->get('fecha')."',".$total.",".$ivat.",'".$do->get('vence')."',
			".$ABONOS.",'','','".$do->get('nfiscal')."','".$ncontrol."',
			".$do->get('reteiva').",".$montasa.",".$monredu.",".$monadic.",
			".$tasa.",".$reducida.",".$sobretasa.",".$exento.")
			";
			$this->db->query($sql);

			if(empty($XORDEN)){
				$mANTICIPO = $do->get('anticipo');


				//Luego buscar anticipos
				$mSQL = "SELECT * FROM sprm WHERE cod_prv='".$do->get('proveed')."' ";

				$mSQL .= "AND tipo_doc='AN' AND num_ref='".$XORDEN."' ";

				$mSQL .= "AND tipo_ref='OS' ";
				$banticipo=$this->db->query($mSQL);
				//echo "aqui".$mSQL."/fin";
				//exit;
				$resultado=$banticipo->num_rows();

				foreach($banticipo->result() as $registro){
					$mTEMPO=$mANTICIPO;
					$mANTICIPO -=$registro['monto']-$registro['abonos'];
					$mMONTO=$registro['monto'];
					$mABONOS=$registro['abonos'];
					if($mANTICIPO >= 0){
						$mSQLant="UPDATE sprm SET abonos=".$mMONTO." WHERE tipo_doc='".$registro['tipo_doc']."' AND numero=".$registro['numero']." AND cod_prv='".$do->get('proveed')."'";
						$this->db->query($mSQLant);
					}else{
						$mANTICIPO = 0;
						$mSQLant="UPDATE sprm SET abonos=".$mTEMPO." WHERE tipo_doc='".$registro['tipo_doc']."' AND numero=".$registro['numero']." AND cod_prv='".$do->get('proveed')."'";
						$this->db->query($mSQLant);
					}
					if($mANTICIPO == 0) break;
					$campos=array('numppro','tipoppro','cod_prv','numero','tipo_doc','fecha','monto','abono','breten','creten','reten','reteiva','ppago','cambio','mora','transac');
					$valores=array($registro['numero'],$registro['tipo_doc'],$do->get('proveed'),$do->get('numero'),$do->get('tipo_doc'),$do->get('fecha'),$mMONTO,$mABONOS,0,'',0,0,0,0,0);
					$mSQL = "INSERT INTO itppro SET(".$campos.")VALUES(".$valores.") ";
					echo $msql;
				}
				
			}
		}
		
//		exit;
		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('gser',"Gasto $codigo CREADO");
		

	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('gser',"Gasto $codigo Modificado");
		
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('gser',"Gasto $codigo ELIMINADO");
	}

	function instala(){
		$query="show index FROM gser";
		$resul=$this->db->query($query);
		$existe=0;
		foreach($resul->result() as $ind){
			$nom= $ind->Column_name;
			if ($nom == 'id'){
				$existe=1;
				break;
			}
		}
		if($existe != 1){
			$query="ALTER TABLE `gser`  DROP PRIMARY KEY";
			$this->db->query($query);
			$query="ALTER TABLE `gser`  ADD UNIQUE INDEX `gser` (`fecha`, `numero`, `proveed`)";
			$this->db->query($query);
			$query="ALTER TABLE `gser`  ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `tipo_or`,  ADD PRIMARY KEY (`id`);";
			$this->db->query($query);
			$query="ALTER TABLE `gitser`  ADD COLUMN `idgser`
					INT(15) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`,ADD INDEX `idgser` (`idgser`)";
			$this->db->query($query);

			$query="update gitser as a
					join gser as b on (a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed)
					set a.idgser=b.id";
			$this->db->query($query);

			$query="ALTER TABLE `gitser`  ADD COLUMN `tasaiva` DECIMAL(7,2)
					UNSIGNED NOT NULL DEFAULT '0' AFTER `idgser`;";
			$this->db->query($query);
		}

	}

}
?>