<?php include('common.php');
class gser extends Controller {

	function gser(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->mcred='_CR';
		$this->load->library('pi18n');
		$this->datasis->modulo_id('518',1);
		//$this->instalar();
	}

	function index() {
		if($this->pi18n->pais=='COLOMBIA'){
			redirect('finanzas/gsercol/filteredgrid');
		}else{ 
			//redirect('finanzas/gser/filteredgrid');
			$this->gserextjs();
		}
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Gastos','gser');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechad->db_name ='fecha';
		$filter->fechad->operator='>=';
		$filter->fechad->group = 'UNO';

		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechah->db_name='fecha';
		$filter->fechah->operator='<=';
		$filter->fechah->group = 'UNO';
		$filter->fechad->clause = $filter->fechah->clause ='where';
		$filter->fechah->size   = $filter->fechad->size=10;

		$filter->tipo_doc = new inputField('Tipo', 'tipo_doc');
		$filter->tipo_doc->db_name = 'tipo_doc';
		$filter->tipo_doc->size = 5;
		$filter->tipo_doc->group = 'UNO';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 10;
		$filter->numero->group = 'DOS';

		$filter->proveed = new inputField('Proveedor', 'proveed');
		$filter->proveed->db_name = 'proveed';
		$filter->proveed->size = 10;
		$filter->proveed->group = 'DOS';

		$filter->nombre = new inputField('Nombre', 'nombre');
		$filter->nombre->db_name = 'nombre';
		$filter->nombre->size = 20;
		$filter->nombre->group = 'DOS';

		$filter->buttons('reset','search');
		$filter->build("dataformfiltro");

		$uri2  = anchor('finanzas/gser/mgserdataedit/modify/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar')));
		$uri2 .= "&nbsp;";
		$uri2 .= anchor('formatos/ver/GSER/<#id#>',img(array('src'=>'images/pdf_logo.gif','border'=>'0','alt'=>'PDF')));
		$uri2 .= "&nbsp;";
		$uri2 .= anchor('formatos/verhtml/GSER/<#id#>',img(array('src'=>'images/html_icon.gif','border'=>'0','alt'=>'HTML')));

		$uri = anchor('finanzas/gser/dataedit/show/<#id#>','<#numero#>');

		$uri_3  = "<a href='javascript:void(0);' onclick='javascript:gserserie(\"<#id#>\")'>";
		$propiedad = array('src' => 'images/engrana.png', 'alt' => 'Modifica Nro de Serie', 'title' => 'Modifica Nro. de Serie','border'=>'0','height'=>'12');
		$uri_3 .= img($propiedad);
		$uri_3 .= "</a>";

		$uri_4  = "<a href='javascript:void(0);' onclick='javascript:gserfiscal(\"<#id#>\")'>";
		$propiedad = array('src' => 'images/engrana.png', 'alt' => 'Modifica Control Fiscal', 'title' => 'Modifica Control Fiscal','border'=>'0','height'=>'12');
		$uri_4 .= img($propiedad);
		$uri_4 .= "</a>";


		$grid = new DataGrid();
		$grid->order_by('fecha','desc');
		$grid->per_page = 50;
		$grid->column('Acciones',$uri2);
		$grid->column('Tipo',"tipo_doc",'tipo_doc');
		$grid->column('Caja',"cajachi",'cajachi');
		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Serie',$uri_3.'<#serie#>','serie');
		$grid->column_orderby('Fecha' ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Nombre','nombre'  ,'nombre');
		$grid->column_orderby('Base' ,'<nformat><#totpre#></nformat>' ,'totneto','align=\'right\'');
		$grid->column_orderby('IVA'   ,'<nformat><#totiva#></nformat>'  ,'totiva' ,'align=\'right\'');
		$grid->column_orderby('Total' ,'<nformat><#totbruto#></nformat>' ,'totbruto','align=\'right\'');
		$grid->column_orderby('Ret.IVA'   ,'reteiva'  ,'reteiva' ,'align=\'right\'');
		$grid->column_orderby('Ret.ISLR'   ,'reten'  ,'reten' ,'align=\'right\'');
		$grid->column_orderby('Total Neto' ,'<nformat><#totneto#></nformat>' ,'totneto','align=\'right\'');
		$grid->column_orderby('Ctrl. Fiscal',$uri_4.'<#nfiscal#>','nfiscal');

		$grid->column_orderby('Vence' ,'<dbdate_to_human><#vence#></dbdate_to_human>','vence','align=\'center\'');
		$grid->column_orderby('Prov.' ,'proveed','proveed','align=\'center\'');

		$grid->column_orderby('Banco'  , 'codb1'  ,'codb1' );
		$grid->column('Tipo'   , 'tipo1'  ,'tipo11' );
		$grid->column_orderby('Cheque' , 'cheque1'  ,'cheque1' );
	
		

		$grid->add('finanzas/gser/agregar','Agregar Egreso');
		$grid->build('datagridST');
		//echo $grid->db->last_query();

// Para usar SuperTable
		$extras = '
<script type="text/javascript">
//<![CDATA[

(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
		headerRows : 1,
		onStart : function () {
		this.start = new Date();
		},
		onFinish : function () {
		document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";
		}
	});
})();
//]]>
</script>
';

		$style ='
<style type="text/css">
.fakeContainer { /* The parent container */
    margin: 5px;
    padding: 0px;
    border: none;
    width: 640px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>
		';

$script ='
<script type="text/javascript">
function gserserie(mid){
	jPrompt("Numero de Serie","" ,"Cambio de Serie", function(mserie){
		if( mserie==null){
			jAlert("Cancelado","Informacion");
		} else {
			$.ajax({ url: "'.site_url().'finanzas/gser/gserserie/"+mid+"/"+mserie,
				success: function(msg){
					jAlert("Cambio Finalizado "+msg,"Informacion");
					location.reload();
					}
			});
		}
	})
}

function gserfiscal(mid){
	jPrompt("Numero de Control Fiscal","" ,"Cambio de Serie", function(mserie){
		if( mserie==null){
			jAlert("Cancelado","Informacion");
		} else {
			$.ajax({ url: "'.site_url().'finanzas/gser/gserfiscal/"+mid+"/"+mserie,
				success: function(msg){
					jAlert("Cambio Finalizado "+msg,"Informacion");
					location.reload();
					}
			});
		}
	})
}

</script>';

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		
		$data['script']  = $script;
		$data['script'] .= script('jquery.js');
		$data["script"] .= script("jquery.alerts.js");
		$data['script'] .= script('superTables.js');
		
		$data['style']   = $style;
		$data['style']  .=style('superTables.css');
		$data['style']	.= style("jquery.alerts.css");

		$data['extras']  = $extras;
		
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Egresos por Gastos');
		$this->load->view('view_ventanas', $data);
	}

	function gserserie(){
		$serie   = $this->uri->segment($this->uri->total_segments());
		$id = $this->uri->segment($this->uri->total_segments()-1);
		if (!empty($serie)) {
			$this->db->simple_query("UPDATE gser SET serie='$serie' WHERE id='$id'");
			echo " con exito ";
		} else {
			echo " NO se guardo ";
		}
		logusu('GSER',"Cambia Nro. Serie $id ->  $serie ");
	}

	function gserfiscal(){
		$serie   = $this->uri->segment($this->uri->total_segments());
		$id = $this->uri->segment($this->uri->total_segments()-1);
		if (!empty($serie)) {
			$this->db->simple_query("UPDATE gser SET nfiscal='$serie' WHERE id='$id'");
			echo " con exito ";
		} else {
			echo " NO se guardo ";
		}
		logusu('GSER',"Cambia Nro. Serie $id ->  $serie ");
	}


	function agregar(){
		$data['content'] = '<div align="center" id="maso" >';

		$data['content'].= '<div class="box" style="width:240px;background-color: #F9F7F9;">'.br();
		$data['content'].= '<a href="'.base_url().'finanzas/gser/gserchi"><img border=0 src="'.base_url().'images/cajachica.gif'.'" height="80px"></a>'.br();
		$data['content'].= '<p>Incluir gastos pagados con dinero de caja chica para ser relacionados al cierre y/o reposicion</p>'.br();
		//$data['content'].= anchor('finanzas/gser/gserchi'  ,'Gastos de Caja Chica').br();
		$data['content'].= '</div>'.br();

		$data['content'].= '<div  class="box" style="width:240px;background-color: #F9F7F9;" class="box">'.br();
		$data['content'].= '<a href="'.base_url().'finanzas/gser/cierregserchi">';
		$data['content'].= '<img border=0 src="'.base_url().'images/rendicion.jpg'.'" height="90px"></a>'.br();
		$data['content'].= '<p>Reposicion de caja con las facturas ingresadas</p>'.br();
		//$data['content'].= anchor('finanzas/gser/cierregserchi'  ,'Cerrar Caja Chica').br();
		$data['content'].= '</div>'.br();

		$data['content'].= '<div class="box" style="width:240px;background-color: #F9F7F9;">'.br();
		$data['content'].= '<a href="'.base_url().'finanzas/gser/dataedit/create">';
		$data['content'].= '<img border="0" src="'.base_url().'images/gastos.jpg'.'" height="70px"></a>'.br();
		$data['content'].= '<p>Agregar factura y Notas de Debito individuales de gastos, donde permite hacer las retenciones de impuestos que correspondan</p>'.br();
		//$data['content'].= anchor('finanzas/gser/dataedit/create'  ,'Agregar un gasto').br();
		$data['content'].= '</div>'.br();

		$data['content'].= '<div class="box" style="width:240px;background-color: #F9F7F9;">'.br();
		$data['content'].= '<a href="'.base_url().'finanzas/gser/index" >';
		$data['content'].= '<p><img border="0" src="'.base_url().'images/regresar.jpg'.'" height="40px"></a>'.br();
		$data['content'].= 'Regresar al modulo de gastos';
		$data['content'].= '</p>'.br();
		$data['content'].= '</div>'.br();

		$data['content'].= '</div><center>';

		$data['title']   = heading('Agregar Gastos');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_masonry', $data);
	}

	//Para Caja chica
	function gserchi(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de gastos de cajas chicas','gserchi');
		$select=array('numfac','fechafac','proveedor','tasa + sobretasa + reducida AS totiva','exento + montasa + monadic + monredu AS totneto');
		$filter->db->select($select);

		$filter->codbanc = new dropdownField('C&oacute;digo de la caja','codbanc');
		$filter->codbanc->option('','Todos');
		$filter->codbanc->options("SELECT codbanc, CONCAT_WS('-',codbanc,banco) AS label FROM banc WHERE tbanco='CAJ' ORDER BY codbanc");

		$filter->fechad = new dateonlyField('Fecha desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Fecha hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  = $filter->fechah->clause ='where';
		$filter->fechad->db_name = $filter->fechah->db_name='fechafac';
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('N&uacute;mero', 'numfac');

		$filter->proveed = new inputField('Proveedor', 'proveedor');
		//$filter->proveed->append($boton);
		$filter->proveed->db_name = 'proveedor';

		$filter->aceptado = new dropdownField('Aceptados','aceptado');
		$filter->aceptado->option('','Todos');
		$filter->aceptado->option('S','Aceptados');
		$filter->aceptado->option('N','No aceptados');
		$filter->aceptado->style = 'width:120px';

		//$action = "javascript:window.location='".site_url('finanzas/gser/gserchipros')."'";
		//$filter->button('btn_pross', 'Procesar gatos', $action, 'TR');

		$action = "javascript:window.location='".site_url('finanzas/gser/agregar')."'";
		$filter->button('btn_regresa', 'Regresar', $action, 'TR');

		$filter->buttons('reset','search');
		$filter->build();

		$uri  = anchor('finanzas/gser/datagserchi/show/<#id#>','<#numfac#>');

		function checker($id,$conci){
			if($conci=='S'){
				return form_checkbox('nn'.$id,$id,true);
			}else{
				return form_checkbox('nn'.$id,$id,false);
			}
		}

		$grid = new DataGrid();
		$grid->use_function('checker');
		$grid->order_by('numfac','desc');
		$grid->per_page = 15;
		$grid->column_orderby('Caja','codbanc','caja');
		$grid->column_orderby('N&uacute;mero',$uri,'numfac');
		$grid->column_orderby('Fecha' ,'<dbdate_to_human><#fechafac#></dbdate_to_human>','fechafac','align=\'center\'');
		$grid->column_orderby('Proveedor','proveedor','proveedor');
		$grid->column_orderby('IVA'   ,'totiva'    ,'totiva'  ,'align=\'right\'');
		$grid->column_orderby('Monto' ,'totneto'   ,'totneto' ,'align=\'right\'');
		$grid->column_orderby('Aceptado','<checker><#id#>|<#aceptado#></checker>','aceptado','align=\'center\'');

		$grid->add('finanzas/gser/datagserchi/create','Agregar nueva factura');
		$grid->build();
		//echo $grid->db->last_query();

		$this->rapyd->jquery[]='$(":checkbox").change(function(){
			name=$(this).attr("name");
			$.post("'.site_url('finanzas/gser/gserchiajax').'",{ id: $(this).val()},
			function(data){
					if(data=="1"){
					return true;
				}else{
					$("input[name=\'"+name+"\']").removeAttr("checked");
					alert("Hubo un error, comuniquese con soporte tecnico: "+data);
					return false;
				}
			});
		});';

		$data['content'] = $filter->output.$grid->output;
		$data['head']    = script('jquery.js');
		$data['head']   .= $this->rapyd->get_head();
		$data['title']   = heading('Agregar/Modificar facturas de Caja Chica');
		$this->load->view('view_ventanas', $data);
	}

	function gserchiajax(){
		$id   = $this->input->post('id');
		$dbid = $this->db->escape($id);
		$rt='0';
		if($id!==false){
			$mSQL="UPDATE gserchi SET aceptado=IF(aceptado='S','N','S') WHERE id=$dbid";
			$ban=$this->db->simple_query($mSQL);
			if($ban==false){
				$rt='0';
				memowrite($mSQL,'gser');
			}else{
				$rt='1';
			}
		}
		echo $rt;
	}

	function datagserchi(){
		$this->rapyd->load('dataedit');
		$mgas=array(
			'tabla'   => 'mgas',
			'columnas'=> array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n','tipo'=>'Tipo'),
			'filtro'  => array('descrip'=>'Descripci&oacute;n'),
			'retornar'=> array('codigo' =>'codigo','descrip'=>'descrip'),
			'titulo'  => 'Buscar enlace administrativo');
		$bcodigo=$this->datasis->modbus($mgas);

		$ivas=$this->datasis->ivaplica();

		$tasa      = $ivas['tasa']/100;
		$redutasa  = $ivas['redutasa']/100;
		$sobretasa = $ivas['sobretasa']/100;

		$consulrif=$this->datasis->traevalor('CONSULRIF');
		$script="
		function consulrif(){
			vrif=$('#rif').val();
			if(vrif.length==0){
				alert('Debe introducir primero un RIF');
			}else{
				vrif=vrif.toUpperCase();
				$('#rif').val(vrif);
				window.open('$consulrif'+'?p_rif='+vrif,'CONSULRIF','height=350,width=410');
			}
		}

		function poneiva(tipo){
			if(tipo==1){
				ptasa = $redutasa;
				campo = 'reducida';
				monto = 'monredu';
			} else if (tipo==3){
				ptasa = $sobretasa;
				campo = 'sobretasa';
				monto = 'monadic'
			} else {
				ptasa = $tasa;
				campo = 'tasa';
				monto = 'montasa';
			}
			if($('#'+monto).val().length>0)  base=parseFloat($('#'+monto).val());   else  base  =0;
			$('#'+campo).val(roundNumber(base*ptasa,2));
			totaliza();
		}

		function totaliza(){
			if($('#montasa').val().length>0)   montasa  =parseFloat($('#montasa').val());   else  montasa  =0;
			if($('#tasa').val().length>0)      tasa     =parseFloat($('#tasa').val());      else  tasa     =0;
			if($('#monredu').val().length>0)   monredu  =parseFloat($('#monredu').val());   else  monredu  =0;
			if($('#reducida').val().length>0)  reducida =parseFloat($('#reducida').val());  else  reducida =0;
			if($('#monadic').val().length>0)   monadic  =parseFloat($('#monadic').val());   else  monadic  =0;
			if($('#sobretasa').val().length>0) sobretasa=parseFloat($('#sobretasa').val()); else  sobretasa=0;
			if($('#exento').val().length>0)    exento   =parseFloat($('#exento').val());    else  exento   =0;

			total=roundNumber(montasa+tasa+monredu+reducida+monadic+sobretasa+exento,2);
			$('#importe').val(total);
		}";

		$edit = new DataEdit('Gastos de caja chica', 'gserchi');
		$edit->back_url = site_url('finanzas/gser/gserchi');
		$edit->script($script,'create');
		$edit->script($script,'modify');
		$edit->pre_process('insert' ,'_pre_gserchi');
		$edit->pre_process('update' ,'_pre_gserchi');

		$edit->codbanc = new dropdownField('C&oacute;digo de la caja','codbanc');
		$edit->codbanc->option('','Seleccionar');
		$edit->codbanc->options("SELECT codbanc, CONCAT_WS('-',codbanc,banco) AS label FROM banc WHERE tbanco='CAJ' ORDER BY codbanc");
		$edit->codbanc->rule='max_length[5]|required';

		$edit->fechafac = new dateField('Fecha de la factura','fechafac');
		$edit->fechafac->rule='max_length[10]|required';
		$edit->fechafac->size =12;
		$edit->fechafac->insertValue=date('Y-m-d');
		$edit->fechafac->maxlength =10;

		$edit->numfac = new inputField('N&uacute;mero de la factura','numfac');
		$edit->numfac->rule='max_length[8]|required';
		$edit->numfac->size =10;
		$edit->numfac->maxlength =8;
		$edit->numfac->autocomplete =false;

		$edit->nfiscal = new inputField('Control fiscal','nfiscal');
		$edit->nfiscal->rule='max_length[12]|required';
		$edit->nfiscal->size =14;
		$edit->nfiscal->maxlength =12;
		$edit->nfiscal->autocomplete =false;

		$lriffis='<a href="javascript:consulrif();" title="Consultar RIF en el SENIAT" onclick="">Consultar RIF en el SENIAT</a>';
		$edit->rif = new inputField('RIF','rif');
		$edit->rif->rule='max_length[13]|required';
		$edit->rif->size =13;
		$edit->rif->maxlength =13;
		$edit->rif->group='Datos del proveedor';
		$edit->rif->append(HTML::button('traesprv', 'Consultar Proveedor', '', 'button', 'button'));
		$edit->rif->append($lriffis);

		$edit->proveedor = new inputField('Nombre del proveedor','proveedor');
		$edit->proveedor->rule='max_length[40]|strtoupper';
		$edit->proveedor->size =40;
		$edit->proveedor->group='Datos del proveedor';
		$edit->proveedor->maxlength =40;

		$edit->codigo = new inputField('C&oacute;digo del gasto','codigo');
		$edit->codigo->rule ='max_length[6]|required';
		$edit->codigo->size =6;
		$edit->codigo->maxlength =8;
		$edit->codigo->append($bcodigo);

		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		$edit->descrip->rule='max_length[50]|strtoupper';
		$edit->descrip->size =50;
		$edit->descrip->maxlength =50;

		$arr=array(
			'exento'   =>'Monto <b>Exento</b>|Base exenta',
			'montasa'  =>'Montos con Alicuota <b>general</b>|Base imponible',
			'tasa'     =>'Montos con Alicuota <b>general</b>|Monto del IVA',
			'monredu'  =>'Montos con Alicuota <b>reducida</b>|Base imponible',
			'reducida' =>'Montos con Alicuota <b>reducida</b>|Monto del IVA',
			'monadic'  =>'Montos con Alicuota <b>adicional</b>|Base imponible',
			'sobretasa'=>'Montos con Alicuota <b>adicional</b>|Monto del IVA',
			'importe'  =>'Importe total');

		foreach($arr AS $obj=>$label){
			$pos = strrpos($label, '|');
			if($pos!==false){
				$piv=explode('|',$label);
				$label=$piv[1];
				$grupo=$piv[0];
			}else{
				$grupo='';
			}

			$edit->$obj = new inputField($label,$obj);
			$edit->$obj->rule='max_length[17]|numeric';
			$edit->$obj->css_class='inputnum';
			$edit->$obj->insertValue =0;
			$edit->$obj->size =17;
			$edit->$obj->maxlength =17;
			$edit->$obj->group=$grupo;
			$edit->$obj->autocomplete=false;
		}
		$edit->$obj->readonly=true;

		$edit->tasa->rule     ='condi_required|max_length[17]|callback_chtasa';
		$edit->reducida->rule ='condi_required|max_length[17]|callback_chreducida';
		$edit->sobretasa->rule='condi_required|max_length[17]|callback_chsobretasa';
		$edit->importe->rule  ='max_length[17]|numeric|positive';

		$edit->sucursal = new dropdownField('Sucursal','sucursal');
		$edit->sucursal->options('SELECT codigo,sucursal FROM sucu ORDER BY sucursal');
		$edit->sucursal->rule='max_length[2]|required';

		$edit->departa = new dropdownField('Departamento','departa');
		$edit->departa->options("SELECT codigo, CONCAT_WS('-',codigo,departam) AS label FROM dept ORDER BY codigo");
		$edit->departa->rule='max_length[2]';

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->estampa = new autoUpdateField('estampa' ,date('YmD'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:m:s'), date('H:m:s'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$url=site_url('finanzas/gser/ajaxsprv');
		//$this->rapyd->jquery[]='$(".inputnum").bind("keyup",function() { totaliza(); })';
		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$this->rapyd->jquery[]='$("#exento"   ).bind("keyup",function() { totaliza(); })';
		$this->rapyd->jquery[]='$("#montasa"  ).bind("keyup",function() { poneiva(2); })';
		$this->rapyd->jquery[]='$("#tasa"     ).bind("keyup",function() { totaliza(); })';
		$this->rapyd->jquery[]='$("#monredu"  ).bind("keyup",function() { poneiva(1); })';
		$this->rapyd->jquery[]='$("#reducida" ).bind("keyup",function() { totaliza(); })';
		$this->rapyd->jquery[]='$("#monadic"  ).bind("keyup",function() { poneiva(3); })';
		$this->rapyd->jquery[]='$("#sobretasa").bind("keyup",function() { totaliza(); })';

		$this->rapyd->jquery[]='$("input[name=\'traesprv\']").click(function() {
			rif=$("#rif").val();
			if(rif.length > 0){
				$.post("'.$url.'", { rif: rif },function(data){
					$("#proveedor").val(data);
				});
			}else{
				alert("Debe introducir un rif");
			}
		});';

		$data['content'] = $edit->output;
		$data['title']   = heading('Agregar/Modificar facturas de Caja Chica');
		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_gserchi($do){
		$rif   =$do->get('rif');
		$dbrif = $this->db->escape($rif);
		$nombre=$do->get('proveedor');
		$fecha =date('Y-m-d');
		$csprv =$this->datasis->dameval('SELECT COUNT(*) FROM sprv WHERE rif='.$dbrif);
		if($csprv==0){
			$mSQL ='INSERT IGNORE INTO provoca (rif,nombre,fecha) VALUES ('.$dbrif.','.$this->db->escape($nombre).','.$this->db->escape($fecha).')';
			$this->db->simple_query($mSQL);
		}

		$total  = 0;
		$total += $do->get('exento')   ;
		$total += $do->get('montasa')  ;
		$total += $do->get('tasa')     ;
		$total += $do->get('monredu')  ;
		$total += $do->get('reducida') ;
		$total += $do->get('monadic')  ;
		$total += $do->get('sobretasa');

		if($total>0){
			$do->set('importe',$total);
			return true;
		}else{
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['pre_upd'] = 'No se puede guardar un gasto con monto cero';
			return false;
		}
	}

	//Para Caja chica
	function cierregserchi(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$uri  = anchor('finanzas/gser/gserchipros/<#codbanc#>','<#codbanc#>');

		$grid = new DataGrid('');
		$select=array('MAX(fechafac) AS fdesde',
					  'MIN(fechafac) AS fhasta',
					  'SUM(tasa+sobretasa+reducida) AS totiva',
					  'SUM(montasa+monadic+monredu+tasa+sobretasa+reducida+exento) AS total',
					  'TRIM(codbanc) AS codbanc',
					  'COUNT(*) AS cana');
		$grid->db->select($select);
		$grid->db->from('gserchi');
		$grid->db->where('ngasto IS NULL');
		$grid->db->where('aceptado','S');
		$grid->db->groupby('codbanc');

		$grid->order_by('codbanc','desc');
		$grid->per_page = 15;
		$grid->column_orderby('Caja',$uri,'codbanc');
		$grid->column('N.facturas','cana','align=\'center\'');
		$grid->column_orderby('Fecha inicial','<dbdate_to_human><#fdesde#></dbdate_to_human>','fdesde','align=\'center\'');
		$grid->column_orderby('Fecha final'  ,'<dbdate_to_human><#fhasta#></dbdate_to_human>','fdesde','align=\'center\'');
		$grid->column_orderby('IVA'   ,'<nformat><#totiva#></nformat>'  ,'totiva' ,'align=\'right\'');
		$grid->column_orderby('Monto' ,'<nformat><#total#></nformat>' ,'total','align=\'right\'');

		$action = "javascript:window.location='".site_url('finanzas/gser/agregar')."'";
		$grid->button('btn_regresa', 'Regresar', $action, 'TR');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Cajas pendientes por cerrar');
		$this->load->view('view_ventanas', $data);
	}

	//Convierte los gastos en caja chica
	function gserchipros($codbanc=null){
		if(empty($codbanc)) show_error('Faltan par&aacute;metros');
		$dbcodbanc=$this->db->escape($codbanc);
		$mSQL='SELECT COUNT(*) AS cana, SUM(exento+montasa+monadic+monredu+tasa+sobretasa+reducida) AS monto FROM gserchi WHERE ngasto IS NULL AND aceptado="S" AND codbanc='.$dbcodbanc;
		$r   =$this->datasis->damerow($mSQL);
		if($r['cana']==0) show_error('Caja sin gastos');
		
		$mSQL="SELECT a.codprv, b.nombre FROM banc AS a JOIN sprv AS b ON a.codprv=b.proveed WHERE a.codbanc=$dbcodbanc";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$row    = $query->row(); 
			$nombre = $row->nombre;
			$codprv = $row->codprv;
		}else{
			$nombre =$codprv = '';
		}

		$sql='SELECT TRIM(a.codbanc) AS codbanc,tbanco FROM banc AS a';
		$query = $this->db->query($sql);
		$comis=array();
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$ind='_'.$row->codbanc;
				$comis[$ind]['tbanco']  =$row->tbanco;
			}
		}
		$json_comis=json_encode($comis);

		$this->rapyd->load('dataform','datagrid');

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'  =>'Nombre',
				'rif'     =>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'codprv','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor'
		);
		$bsprv=$this->datasis->modbus($modbus);

		$script='var comis = '.$json_comis.';
		
		$(document).ready(function() {
			desactivacampo("");
		});
		
		function desactivacampo(codb1){
			if(codb1.length>0 && codb1!="'.$this->mcred.'"){
				eval("tbanco=comis._"+codb1+".tbanco;"  );
				if(tbanco=="CAJ"){
					$("#cheque").attr("disabled","disabled");
					$("#benefi").attr("disabled","disabled");
				}else{
					$("#cheque").removeAttr("disabled");
					$("#benefi").removeAttr("disabled");
				}
			}else{
				$("#cheque").attr("disabled","disabled");
				$("#benefi").attr("disabled","disabled");
			}
		}';

		$form = new DataForm('finanzas/gser/gserchipros/'.$codbanc.'/process');
		$form->title("N&uacute;mero de facturas aceptadas $r[cana], monto total <b>".nformat($r['monto']).'</b>');
		$form->script($script);

		$form->codprv = new inputField('Proveedor', 'codprv');
		$form->codprv->rule='required';
		$form->codprv->insertValue=$codprv;
		$form->codprv->size=5;
		$form->codprv->append($bsprv);

		$form->nombre = new inputField('Nombre', 'nombre');
		$form->nombre->rule='required';
		$form->nombre->insertValue=$nombre;
		$form->nombre->in = 'codprv';

		$form->cargo = new dropdownField('Con cargo a','cargo');
		$form->cargo->option($this->mcred,'Cr&eacute;dito');
		$form->cargo->options("SELECT codbanc, CONCAT_WS('-',codbanc,banco) AS label FROM banc WHERE activo='S' ORDER BY codbanc");
		$form->cargo->onchange='desactivacampo(this.value)';
		$form->cargo->rule='max_length[5]|required';

		$form->cheque = new inputField('N&uacute;mero de cheque', 'cheque');
		$form->cheque->rule='condi_required|callback_chobligaban';
		$form->cheque->append('Aplica  solo si el cargo es a un banco');

		$form->benefi = new inputField('Beneficiario', 'benefi');
		$form->benefi->insertValue=$nombre;
		$form->benefi->rule='condi_required|callback_chobligaban';
		$form->benefi->append('Aplica  solo si el cargo es a un banco');

		$action = "javascript:window.location='".site_url('finanzas/gser/cierregserchi/'.$codbanc)."'";
		$form->button('btn_regresa', 'Regresar', $action, 'BR');

		$form->submit('btnsubmit','Procesar');
		$form->build_form();

		$grid = new DataGrid('Lista de Gastos','gserchi');
		$select=array('exento + montasa + monadic + monredu + tasa + sobretasa + reducida AS totneto',
					  'tasa + sobretasa + reducida AS totiva','proveedor','fechafac','numfac','codbanc' );
		$grid->db->select($select);
		$grid->db->where('aceptado','S');
		$grid->db->where('ngasto IS NULL');
		$grid->db->where('codbanc',$codbanc);

		$grid->order_by('numfac','desc');
		$grid->per_page = 15;
		$grid->column('Caja','codbanc');
		$grid->column('N&uacute;mero','numfac');
		$grid->column('Fecha' ,'<dbdate_to_human><#fechafac#></dbdate_to_human>','align=\'center\'');
		$grid->column('Proveedor','proveedor');
		$grid->column('IVA'   ,'totiva'    ,'align=\'right\'');
		$grid->column('Monto' ,'totneto'   ,'align=\'right\'');

		//$grid->add('finanzas/gser/datagserchi/create','Agregar nueva factura');
		$grid->build();

		if($form->on_success()){
			$codprv  = $form->codprv->newValue;
			$cargo   = $form->cargo->newValue;
			$nombre  = $form->nombre->newValue;
			$benefi  = $form->benefi->newValue;
			$cheque  = $form->cheque->newValue;

			$rt=$this->_gserchipros($codbanc,$cargo,$codprv,$benefi,$cheque);
			//var_dump($rt);
			if($rt){
				redirect('finanzas/gser/listo/n');
			}else{
				redirect('finanzas/gser/listo/s');
			}
		}

		$data['content'] = $form->output.$grid->output;
		$data['title']   = heading('Reposici&oacute;n de caja chica '.$codbanc);
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['head']   .= phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function chtipoe($tipoe){
		$eenvia = $this->input->post('codb1');
		if(!empty($eenvia)){
			$envia  = common::_traetipo($eenvia);

			if($envia=='CAJ' && $tipoe!='D'){
				$this->validation->set_message('chtipoe', 'Cuando el gasto se carga a una caja el %s debe ser nota de d&eacute;bito.');
				return false;
			}elseif($envia!='CAJ' && empty($tipoe)){
				$this->validation->set_message('chtipoe', 'Cuando el gasto se carga a un banco el %s es obligatorio.');
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}

	function chcodb($codb1){
		$monto1=$this->input->post('monto1');
		if($monto1>0 && empty($codb1)){
			$this->validation->set_message('chcodb', 'El campo %s es obligatorio cuando se paga un monto al contado');
			return false;
		}
	}

	function chobligaban($val){
		$ban=$this->input->post('codb1');
		if($ban==$this->mcred) return true;
		$tipo=common::_traetipo($ban);
		if($tipo!='CAJ'){
			if(empty($val)){
				$this->validation->set_message('chobligaban', 'El campo %s es obligatorio cuando el cargo es a un banco');
				return false;
			}
		}
		return true;
	}

	//Chequea que el iva retenido sea 100, 0, 75%
	function chreteiva($monto){
		$totiva = round($this->input->post('totiva'),2);
		$monto  = round($monto,2);

		if($monto==0.00 || $monto==$totiva || round($totiva*0.75,2)==$monto){
			return true;
		}else{
			$this->validation->set_message('chreteiva', 'El campo %s tiene que ser 0, 75 o 100% del monto del iva');
			return false;
		}
	}

	function chobliganumero($val){
		/*$ban=$this->input->post('cargo');
		if($ban==$this->mcred) return true;
		$tipo=common::_traetipo($ban);
		if($tipo!='CAJ'){
			if(empty($val)){
				$this->validation->set_message('chobligaban', 'El campo %s es obligatorio cuando el caja es un banco');
				return false;
			}
		}
		return true;*/
		return $this->_chobliganumero($val,'cargo','chobliganumero');
	}

	function chobliganumerog($val){
		return $this->_chobliganumero($val,'codb1','chobliganumerog');
	}

	function _chobliganumero($val,$campo,$func){
		$ban=$this->input->post($campo);
		if(empty($ban)) return true;
		$tipo=common::_traetipo($ban);
		if($tipo!='CAJ'){
			if(empty($val)){
				$this->validation->set_message($func, 'El campo %s es obligatorio cuando el cargo es a un banco');
				return false;
			}
		}
		return true;
	}

	function _gserchipros($codbanc,$cargo,$codprv,$benefi,$numeroch=null){
			$dbcodprv = $this->db->escape($codprv);
			$nombre   = $this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$dbcodprv);
			$fecha    = date('Y-m-d');
			$numeroch = str_pad($numeroch, 12, '0', STR_PAD_LEFT);
			$sp_fecha = str_replace('-','',$fecha);
			$dbcodbanc= $this->db->escape($codbanc);
			$error    = 0;
			$cr       = $this->mcred; //Marca para el credito
			
			$databan  = common::_traebandata($codbanc);
			$datacar  = common::_traebandata($cargo);
			if(!is_null($datacar)){
				$tipo  = $datacar['tbanco'];
				$moneda= $datacar['moneda'];
			}

			$mSQL='SELECT codbanc,fechafac,numfac,nfiscal,rif,proveedor,codigo,descrip,
			  moneda,montasa,tasa,monredu,reducida,monadic,sobretasa,exento,importe,sucursal,departa,usuario,estampa,hora
			FROM gserchi WHERE ngasto IS NULL AND aceptado="S" AND codbanc='.$dbcodbanc;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$transac  = $this->datasis->fprox_numero('ntransa');
				$numero   = $this->datasis->fprox_numero('ngser');
				$cheque   = ($tipo=='CAJ')? $this->datasis->banprox($codbanc): $numeroch ;
				

				$montasa=$monredu=$monadic=$tasa=$reducida=$sobretasa=$exento=$totpre=$totiva=0;
				foreach ($query->result() as $row){

					$data = array();
					$data['fecha']      = $fecha;
					$data['numero']     = $numero;
					$data['proveed']    = $codprv;
					$data['codigo']     = $row->codigo;
					$data['descrip']    = $row->descrip;
					$data['precio']     = $row->montasa+$row->monredu+$row->monadic+$row->exento;
					$data['iva']        = $row->tasa+$row->reducida+$row->sobretasa;
					$data['importe']    = $data['precio']+$data['iva'];
					$data['unidades']   = 1;
					$data['fraccion']   = 0;
					$data['almacen']    = '';
					$data['sucursal']   = $row->sucursal;
					$data['departa']    = $row->departa ;
					$data['transac']    = $transac;
					$data['usuario']    = $this->session->userdata('usuario');
					$data['estampa']    = date('Y-m-d');
					$data['hora']       = date('H:i:s');
					$data['huerfano']   = '';
					$data['rif']        = $row->rif      ;
					$data['proveedor']  = $row->proveedor;
					$data['numfac']     = $row->numfac   ;
					$data['fechafac']   = $row->fechafac ;
					$data['nfiscal']    = $row->nfiscal  ;
					$data['feprox']     = '';
					$data['dacum']      = '';
					$data['residual']   = '';
					$data['vidau']      = '';
					$data['montasa']    = $row->montasa  ;
					$data['monredu']    = $row->monredu  ;
					$data['monadic']    = $row->monadic  ;
					$data['tasa']       = $row->tasa     ;
					$data['reducida']   = $row->reducida ;
					$data['sobretasa']  = $row->sobretasa;
					$data['exento']     = $row->exento   ;
					$data['reteica']    = 0;
					//$data['idgser']     = '';

					$sql=$this->db->insert_string('gitser', $data);
					$ban=$this->db->simple_query($sql);
					if($ban==false){ memowrite($sql,'gser'); $error++;}

					$montasa  +=$row->montasa  ;
					$monredu  +=$row->monredu  ;
					$monadic  +=$row->monadic  ;
					$tasa     +=$row->tasa     ;
					$reducida +=$row->reducida ;
					$sobretasa+=$row->sobretasa;
					$exento   +=$row->exento   ;
				}
				$totpre = $montasa+$monredu+$monadic+$exento;
				$totiva = $tasa+$reducida+$sobretasa;
				$totneto= $totpre+$totiva;

				if($cargo==$cr){ //si el cargo va a credito
					$nombre  = $this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$this->db->escape($codprv));
					$tipo1   = '';
					$credito = $totneto;
					$causado = $this->datasis->fprox_numero('ncausado');

					$data=array();
					$data['cod_prv']    = $codprv;
					$data['nombre']     = $nombre;
					$data['tipo_doc']   = 'FC';
					$data['numero']     = $numero ;
					$data['fecha']      = $fecha ;
					$data['monto']      = $totneto;
					$data['impuesto']   = $totiva ;
					$data['abonos']     = 0;
					$data['vence']      = $fecha;
					//$data['tipo_ref']   = '';
					//$data['num_ref']    = '';
					$data['observa1']   = 'REPOSICION DE CAJA CHICA '.$codbanc;
					/*$data['observa2']   = '';
					$data['banco']      = '';
					$data['tipo_op']    = '';
					$data['comprob']    = '';
					$data['numche']     = '';
					$data['codigo']     = '';
					$data['descrip']    = '';
					$data['ppago']      = '';
					$data['nppago']     = '';
					$data['reten']      = '';
					$data['nreten']     = '';
					$data['mora']       = '';
					$data['posdata']    = '';
					$data['benefi']     = '';
					$data['control']    = '';*/
					$data['transac']    = $transac;
					$data['estampa']    = date('Y-m-d');
					$data['hora']       = date('H:i:s');
					$data['usuario']    = $this->session->userdata('usuario');
					//$data['cambio']     ='';
					//$data['pmora']      ='';
					$data['reteiva']    = 0;
					//$data['nfiscal']    ='';
					$data['montasa']    = $montasa;
					$data['monredu']    = $monredu;
					$data['monadic']    = $monadic;
					$data['tasa']       = $tasa;
					$data['reducida']   = $reducida;
					$data['sobretasa']  = $sobretasa;
					$data['exento']     = $exento;
					/*$data['fecdoc']     = '';
					$data['afecta']     = '';
					$data['fecapl']     = '';
					$data['serie']      = '';
					$data['depto']      = '';
					$data['negreso']    = '';
					$data['ndebito']    = '';*/
					$data['causado']    = $causado;

					$sql=$this->db->insert_string('sprm', $data);
					$ban=$this->db->simple_query($sql);
					if($ban==false){ memowrite($sql,'gser'); $error++;}
					$cargo   = '';
					$cheque  = '';
					$negreso = '';
				}else{
					$ttipo  = $datacar['tbanco'];
					$tipo1  = ($ttipo=='CAJ') ? 'D': 'C';
					$negreso= $this->datasis->fprox_numero('negreso');
					$credito= 0;
					$causado='';

					$data=array();
					$data['codbanc']    = $cargo;
					$data['moneda']     = $moneda;
					$data['numcuent']   = $datacar['numcuent'];
					$data['banco']      = $datacar['banco'];
					$data['saldo']      = $datacar['saldo'];
					$data['tipo_op']    = ($ttipo=='CAJ') ? 'ND': 'CH';
					$data['numero']     = $cheque;
					$data['fecha']      = $fecha;
					$data['clipro']     = 'P';
					$data['codcp']      = $codprv;
					$data['nombre']     = $nombre;
					$data['monto']      = $totneto;
					$data['concepto']   = 'REPOSICION DE CAJA CHICA '.$codbanc;
					/*$data['concep2']    = '';
					$data['concep3']    = '';
					$data['documen']    = '';
					$data['comprob']    = '';
					$data['status']     = '';
					$data['cuenta']     = '';
					$data['enlace']     = '';
					$data['bruto']      = '';
					$data['comision']   = '';
					$data['impuesto']   = '';
					$data['registro']   = '';
					$data['concilia']   = '';*/
					$data['benefi']     = $benefi;
					$data['posdata']    = '';
					$data['abanco']     = '';
					$data['liable']     = ($ttipo=='CAJ') ? 'S': 'N';;
					$data['transac']    = $transac;
					$data['usuario']    = $this->session->userdata('usuario');
					$data['estampa']    = date('Y-m-d');
					$data['hora']       = date('H:i:s');
					$data['anulado']    = 'N';
					$data['susti']      = '';
					$data['negreso']    = $negreso;
					/*$data['ndebito']    = '';
					$data['ncausado']   = '';
					$data['ncredito']   = '';*/

					$sql=$this->db->insert_string('bmov', $data);
					$ban=$this->db->simple_query($sql);
					if($ban==false){ memowrite($sql,'gser'); $error++;}

					$sql='CALL sp_actusal('.$this->db->escape($cargo).",'$sp_fecha',-$totneto)";
					$ban=$this->db->simple_query($sql);
					if($ban==false){ memowrite($sql,'gser'); $error++; }
				}

				$data = array();
				$data['fecha']      = $fecha;
				$data['numero']     = $numero;
				$data['proveed']    = $codprv;
				$data['nombre']     = $nombre;
				$data['vence']      = $fecha;
				$data['totpre']     = $totpre;
				$data['totiva']     = $totiva;
				$data['totbruto']   = $totneto;
				$data['reten']      = 0;
				$data['totneto']    = $totneto;//totneto=totbruto-reten
				$data['codb1']      = $cargo;
				$data['tipo1']      = $tipo1;
				$data['cheque1']    = $cheque;
				/*$data['comprob1']   = '';
				$data['monto1']     = '';
				$data['codb2']      = '';
				$data['tipo2']      = '';
				$data['cheque2']    = '';
				$data['comprob2']   = '';
				$data['monto2']     = '';
				$data['codb3']      = '';
				$data['tipo3']      = '';
				$data['cheque3']    = '';
				$data['comprob3']   = '';
				$data['monto3']     = '';*/
				$data['credito']    = $credito;
				$data['tipo_doc']   = 'FC';
				$data['orden']      = '';
				$data['anticipo']   = 0;
				$data['benefi']     = $benefi;
				$data['mdolar']     = '';
				$data['usuario']    = $this->session->userdata('usuario');
				$data['estampa']    = date('Y-m-d');
				$data['hora']       = date('H:i:s');
				$data['transac']    = $transac;
				$data['preten']     = '';
				$data['creten']     = '';
				$data['breten']     = '';
				$data['huerfano']   = '';
				$data['reteiva']    = 0;
				$data['nfiscal']    = '';
				$data['afecta']     = '';
				$data['fafecta']    = '';
				$data['ffactura']   = '';
				$data['cajachi']    = 'S';
				$data['montasa']    = $montasa;
				$data['monredu']    = $monredu;
				$data['monadic']    = $monadic;
				$data['tasa']       = $tasa;
				$data['reducida']   = $reducida;
				$data['sobretasa']  = $sobretasa;
				$data['exento']     = $exento;
				$data['compra']     = '';
				$data['serie']      = '';
				$data['reteica']    = 0;
				$data['retesimple'] = 0;
				$data['negreso']    = $negreso;
				$data['ncausado']   = $causado;
				$data['tipo_or']    = '';

				$sql=$this->db->insert_string('gser', $data);
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'gser'); $error++;}
				$idgser=$this->db->insert_id();

				$data = array('idgser' => $idgser);
				$dbfecha  = $this->db->escape($fecha);
				$dbnumero = $this->db->escape($numero);
				$dbcodprv = $this->db->escape($codprv);
				$where = "fecha=$dbfecha AND proveed=$dbcodprv AND  numero=$dbnumero";
				$mSQL = $this->db->update_string('gitser', $data, $where);
				$ban=$this->db->simple_query($mSQL); 
				if($ban==false){ memowrite($mSQL,'gser'); $error++; }

				$data = array('ngasto' => $numero);
				$where = "ngasto IS NULL AND  codbanc=$dbcodbanc";
				$mSQL = $this->db->update_string('gserchi', $data, $where);
				$ban=$this->db->simple_query($mSQL); 
				if($ban==false){ memowrite($mSQL,'gser'); $error++; }
			}
		return ($error==0)? true : false;
	}

	//Crea la retencion
	function _gserrete($fecha,$tipo,$fechafac,$numero,$nfiscal,$afecta,$clipro,$montasa,$monredu,$monadic,$tasa,$reducida,$sobretasa,$exento,$reiva,$transac){
		$nrocomp=$this->datasis->fprox_numero('niva');
		$sp_fecha= str_replace('-','',$fecha);
		$row     = $this->datasis->damerow('SELECT nombre,rif FROM sprv WHERE proveed='.$this->db->escape($clipro));
		$totpre  = $montasa+$monredu+$monadic+$exento;
		$totiva  = $tasa+$reducida+$sobretasa;
		$totneto = $totpre+$totiva;
		$error   = 0;

		$data['nrocomp']    = $nrocomp;
		$data['emision']    = $fecha;
		$data['periodo']    = substr($sp_fecha,0,6);
		$data['tipo_doc']   = $tipo;
		$data['fecha']      = $fechafac;
		$data['numero']     = $numero;
		$data['nfiscal']    = $nfiscal;
		$data['afecta']     = $afecta;
		$data['clipro']     = $clipro;
		$data['nombre']     = $row['nombre'];
		$data['rif']        = $row['rif'];
		$data['exento']     = $exento;
		$data['tasa']       = ($montasa>0)? round($tasa*100/$montasa,2) : 0;
		$data['general']    = $montasa;
		$data['geneimpu']   = $tasa;
		$data['tasaadic']   = ($monadic>0)? round($sobretasa*100/$monadic,2) : 0;
		$data['adicional']  = $monadic;
		$data['adicimpu']   = $sobretasa;
		$data['tasaredu']   = ($monredu>0)? round($reducida*100/$monredu,2) : 0;
		$data['reducida']   = $monredu;
		$data['reduimpu']   = $reducida;
		$data['stotal']     = $totpre;
		$data['impuesto']   = $totiva;
		$data['gtotal']     = $totneto;
		$data['reiva']      = $reiva;
		$data['transac']    = $transac;
		$data['estampa']    = date('Y-m-d');
		$data['hora']       = date('H:i:s');
		$data['usuario']    = $this->session->userdata('usuario');
		//$data['ffactura']   = '';
		//$data['modificado'] = '';
		
		$sql=$this->db->insert_string('riva', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'gser'); $error++;}

		return ($error==0)? true : false;
	}

	function _rm_gserrete($transac){
		$this->db->delete('riva', array('transac' => $transac));
	}

	//Crea la cuenta por pagar en caso de que el gasto sea a credito
	function _gsersprm($codbanc,$codprv,$numero,$fecha,$montasa,$monredu,$monadic,$tasa,$reducida,$sobretasa,$exento,$causado,$transac,$abono=0){
		$nombre  = $this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$this->db->escape($codprv));
		$totpre = $montasa+$monredu+$monadic+$exento;
		$totiva = $tasa+$reducida+$sobretasa;
		$totneto= $totpre+$totiva;
		$error  = 0;

		$data=array();
		$data['cod_prv']    = $codprv;
		$data['nombre']     = $nombre;
		$data['tipo_doc']   = 'FC';
		$data['numero']     = $numero ;
		$data['fecha']      = $fecha ;
		$data['monto']      = $totneto;
		$data['impuesto']   = $totiva ;
		$data['abonos']     = $abono;
		$data['vence']      = $fecha;
		$data['observa1']   = 'EGRESO NRO. '.$numero.' PROVEEDOR '.$nombre;
		$data['transac']    = $transac;
		$data['estampa']    = date('Y-m-d');
		$data['hora']       = date('H:i:s');
		$data['usuario']    = $this->session->userdata('usuario');
		$data['reteiva']    = 0;
		$data['montasa']    = $montasa;
		$data['monredu']    = $monredu;
		$data['monadic']    = $monadic;
		$data['tasa']       = $tasa;
		$data['reducida']   = $reducida;
		$data['sobretasa']  = $sobretasa;
		$data['exento']     = $exento;
		$data['causado']    = $causado;

		$sql=$this->db->insert_string('sprm', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'gser'); $error++;}

		return ($error==0)? true : false;
	}

	//Reversa la transaccion en sprm
	function _rm_gsersprm($transac){
		$this->db->delete('sprm', array('transac' => $transac)); 
	}

	//genera el movimiento de banco cuando el pago es al contado
	function _bmovgser($codbanc,$codprv,$cargo,$negreso,$cheque,$fecha,$totneto,$benefi,$transac,$msj=''){
		$nombre  = $this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$this->db->escape($codprv));
		$datacar = common::_traebandata($cargo);
		$sp_fecha = str_replace('-','',$fecha);
		$ttipo   = $datacar['tbanco'];
		$tipo1   = ($ttipo=='CAJ') ? 'D': 'C';
		$error   = 0;
		
		$data=array();
		$data['codbanc']    = $cargo;
		$data['moneda']     = $datacar['moneda'];
		$data['numcuent']   = $datacar['numcuent'];
		$data['banco']      = $datacar['banco'];
		$data['saldo']      = $datacar['saldo'];
		$data['tipo_op']    = ($ttipo=='CAJ') ? 'ND': 'CH';
		$data['numero']     = str_pad($cheque, 12, '0', STR_PAD_LEFT);
		$data['fecha']      = $fecha;
		$data['clipro']     = 'P';
		$data['codcp']      = $codprv;
		$data['nombre']     = $nombre;
		$data['monto']      = $totneto;
		$data['concepto']   = $msj;
		$data['benefi']     = $benefi;
		$data['posdata']    = '';
		$data['abanco']     = '';
		$data['liable']     = ($ttipo=='CAJ') ? 'S': 'N';;
		$data['transac']    = $transac;
		$data['usuario']    = $this->session->userdata('usuario');
		$data['estampa']    = date('Y-m-d');
		$data['hora']       = date('H:i:s');
		$data['anulado']    = 'N';
		$data['susti']      = '';
		$data['negreso']    = $negreso;
		/*$data['ndebito']    = '';
		$data['ncausado']   = '';
		$data['ncredito']   = '';*/

		$sql=$this->db->insert_string('bmov', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'gser'); $error++;}

		$sql='CALL sp_actusal('.$this->db->escape($cargo).",'$sp_fecha',-$totneto)";
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'gser'); $error++; }

		return ($error==0)? true : false;
	}

	function _rm_bmovgser($transac){
		$sel=array('codbanc','tipo_op','numero','monto');
		$this->db->select($sel);
		$this->db->from('bmov');
		$this->db->where('transac',$transac);

		$query = $this->db->get();
		foreach ($query->result() as $row){
			$data = array(
               'anulado' => 'S',
            );

			$this->db->where('codbanc', $row->codbanc);
			$this->db->where('tipo_op', $row->tipo_op);
			$this->db->where('numero' , $row->numero);
			$this->db->update('bmov', $data); 

			$cargo   =$row->codbanc;
			$totneto =$row->monto;
			$sp_fecha=date('Ymd');

			$sql='CALL sp_actusal('.$this->db->escape($cargo).",'$sp_fecha',$totneto)";
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'gser');}
		}
	}

	function ajaxsprv(){
		$rif=$this->input->post('rif');
		if($rif!==false){
			$dbrif=$this->db->escape($rif);
			$nombre=$this->datasis->dameval("SELECT nombre FROM provoca WHERE rif=$dbrif");
			if(empty($nombre))
				$nombre=$this->datasis->dameval("SELECT nombre FROM sprv WHERE rif=$dbrif");
			echo $nombre;
		}
	}

	function listo($error,$numero=null){
		if($error=='n'){
			$data['content'] = 'Transacci&oacute;n completada ';
			if(!empty($numero)){
				$url='formatos/verhtml/';

				$data['content'] .= ', puede <a href="#" onclick="fordi.print();">imprimirla</a>';
				$data['content'] .= ' o '.anchor('finanzas/gser/index','Regresar');
				$data['content'] .= "<iframe name='fordi' src ='$url' width='100%' height='450'><p>Tu navegador no soporta iframes.</p></iframe>";
			}else{
				$data['content'] .= anchor('finanzas/gser/index','Regresar');
			}
		}else{
			$data['content'] = 'Lo siento pero hubo alg&uacute;n error en la transacci&oacute;n, se genero un centinela '.anchor('finanzas/gser/index','Regresar');
		}
		$data['title']   = heading('Transferencias entre cajas');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		$tipo_rete=$this->datasis->traevalor('CONTRIBUYENTE');
		$rif      =$this->datasis->traevalor('RIF');

		$fields = $this->db->field_data('gser');
		$url_pk = $this->uri->segment_array();
		$coun=0; $pk=array();
		foreach ($fields as $field){
			if($field->primary_key==1){
				$coun++;
				$pk[]=$field->name;
			}
		}
		$values=array_slice($url_pk,-$coun);
		$claves=array_combine (array_reverse($pk) ,$values );
		
		$query="UPDATE gitser AS a
			JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
			SET a.idgser=b.id
			WHERE a.id=".$claves['id']." ";
		$this->db->simple_query($query);

		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;odigo',
				'nombre'=>'Nombre',
				'rif'=>'Rif'
			),
			'filtro'  => array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=> array('proveed'=>'proveed','nombre'=>'nombre','tipo'=>'sprvtipo','reteiva'=>'sprvreteiva'),
			'script'  => array('post_sprv_modbus()'),
			'titulo'  =>'Buscar Proveedor');
		$bSPRV=$this->datasis->modbus($mSPRV);

		$do = new DataObject('gser');
		$do->pointer('sprv' ,'sprv.proveed=gser.proveed','sprv.tipo AS sprvtipo, sprv.reteiva AS sprvreteiva','left');
		$do->rel_one_to_many('gitser' ,'gitser' ,array('id'=>'idgser'));
		$do->rel_one_to_many('gereten','gereten',array('id'=>'idd'));
		//$do->rel_pointer('rete','rete','gereten.codigorete=rete.codigo','rete.pama1 AS retepama1');

		$edit = new DataDetails("Gastos", $do);
		if ( $edit->_status == 'show' ) {
			$edit->back_url = site_url("finanzas/gser/filteredgrid");
		} else {
			$edit->back_url = site_url("finanzas/gser/agregar");
		}

		$edit->set_rel_title('gitser','Gasto <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

		$edit->pre_process( 'insert','_pre_insert' );
		$edit->pre_process( 'update','_pre_update' );
		$edit->pre_process( 'delete','_pre_delete' );
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->tipo_doc =  new dropdownField("Tipo Documento", "tipo_doc");
		$edit->tipo_doc->style="width:100px";
		$edit->tipo_doc->option('FC',"Factura");
		$edit->tipo_doc->option('ND',"Nota Debito");
		if($edit->_status=='show'){
			$edit->tipo_doc->option('XX',"Anulado");
			$edit->tipo_doc->option('AD',"Amortizaci&oacute;n");
			$edit->tipo_doc->option('GA',"Gasto de N&oacute;mina");
		}

		$edit->ffactura = new DateonlyField("Fecha Documento", "ffactura","d/m/Y");
		$edit->ffactura->insertValue = date("Y-m-d");
		$edit->ffactura->size = 10;
		$edit->ffactura->rule = 'required';
		//$edit->ffactura->insertValue = date("Y-m-d");

		$edit->fecha = new DateonlyField('Fecha Registro', 'fecha');
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->size = 10;
		$edit->fecha->rule = 'required';

		$edit->vence = new DateonlyField("Fecha Vencimiento", "vence","d/m/Y");
		$edit->vence->insertValue = date("Y-m-d");
		$edit->vence->size = 10;
		//$edit->vence->insertValue = date("Y-m-d");

		$edit->compra = new inputField('Doc.Asociado','compra');
		$edit->compra->rule='max_length[8]';
		$edit->compra->size =10;
		$edit->compra->maxlength =8;

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->maxlength=8;
		$edit->numero->autocomplete=false;
		$edit->numero->rule='required';

		$edit->proveed = new inputField("Proveedor","proveed");
		$edit->proveed->size = 6;
		$edit->proveed->maxlength=5;
		$edit->proveed->append($bSPRV);
		$edit->proveed->rule= "required";

		$edit->nfiscal  = new inputField("Control Fiscal", "nfiscal");
		$edit->nfiscal->size = 10;
		$edit->nfiscal->autocomplete=false;
		$edit->nfiscal->maxlength=20;

		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 30;
		$edit->nombre->maxlength=40;
		$edit->nombre->type='inputhidden';
		$edit->nombre->rule= "required";

		$edit->sprvtipo = new hiddenField('','sprvtipo');
		$edit->sprvtipo->db_name = 'sclitipo';
		$edit->sprvtipo->pointer = true;

		$edit->sprvreteiva = new hiddenField('','sprvreteiva');
		$edit->sprvreteiva->db_name = 'sprvreteiva';
		$edit->sprvreteiva->insertValue=($tipo_rete=='ESPECIAL' && strtoupper($rif[0])!='V') ? '75':'0';
		$edit->sprvreteiva->pointer = true;

		$edit->totpre  = new inputField("Sub.Total", "totpre");
		$edit->totpre->size = 10;
		$edit->totpre->css_class='inputnum';
		$edit->totpre->readonly = true;
		$edit->totpre->showformat ='decimal';
		$edit->totpre->type='inputhidden';

		$edit->totbruto= new inputField("Total", "totbruto");
		$edit->totbruto->size = 10;
		$edit->totbruto->css_class='inputnum';
		$edit->totbruto->showformat ='decimal';
		$edit->totbruto->type='inputhidden';

		$edit->totiva = new inputField("Total IVA", "totiva");
		$edit->totiva->css_class ='inputnum';
		$edit->totiva->size      = 10;
		$edit->totiva->showformat ='decimal';
		$edit->totiva->type='inputhidden';

		$edit->reteica = new inputField('Ret. ICA', 'reteica');
		$edit->reteica->css_class = 'inputnum';
		$edit->reteica->when      = array('show');
		$edit->reteica->size      = 10;
		$edit->reteica->showformat ='decimal'; 

		$edit->retesimple = new inputField('Ret', 'retesimple');
		$edit->retesimple->css_class = 'inputnum';
		$edit->retesimple->when      = array('show');
		$edit->retesimple->size      = 10;
		$edit->retesimple->showformat ='decimal'; 

		$edit->codb1 = new dropdownField('Caja/Banco','codb1');
		$edit->codb1->option('','');
		$edit->codb1->options("SELECT TRIM(codbanc) AS ind, CONCAT_WS('-',codbanc,banco) AS label FROM banc ORDER BY codbanc");
		$edit->codb1->rule  = 'max_length[5]|callback_chcodb|condi_required';
		$edit->codb1->style = 'width:120px';
		$edit->codb1->onchange="esbancaja(this.value)";

		$edit->tipo1 =  new dropdownField("Cheque/ND", "tipo1");
		$edit->tipo1->option('','Ninguno');
		$edit->tipo1->option('C','Cheque');
		$edit->tipo1->option('D','D&eacute;bito');
		$edit->tipo1->rule = 'condi_required|callback_chtipoe';
		$edit->tipo1->style="width:100px";

		$edit->cheque1 = new inputField('N&uacute;mero',"cheque1");
		$edit->cheque1->rule = 'condi_required|callback_chobliganumerog';
		$edit->cheque1->size = 12;
		$edit->cheque1->maxlength=20;

		$edit->benefi = new inputField("Beneficiario","benefi");
		$edit->benefi->size = 39;
		$edit->benefi->maxlength=40;

		$edit->monto1= new inputField("Contado", "monto1");
		$edit->monto1->size = 10;
		$edit->monto1->css_class='inputnum';
		$edit->monto1->onkeyup="contado()";
		$edit->monto1->rule = 'condi_required|callback_chmontocontado|positive';
		$edit->monto1->autocomplete=false;
		$edit->monto1->showformat ='decimal'; 

		$edit->credito= new inputField("Cr&eacute;dito", "credito");
		$edit->credito->size = 10;
		$edit->credito->showformat ='decimal'; 
		$edit->credito->css_class='inputnum';
		$edit->credito->onkeyup="ccredito()";
		$edit->credito->autocomplete=false;

		$edit->reten = new inputField("Ret. ISLR","reten");
		$edit->reten->size = 10;
		$edit->reten->maxlength=10;
		$edit->reten->css_class='inputnum';
		//$edit->reten->when=array('show');
		$edit->reten->showformat ='decimal';
		$edit->reten->type='inputhidden';

		$edit->reteiva = new inputField("Ret.de IVA","reteiva");
		$edit->reteiva->size = 10;
		$edit->reteiva->maxlength=10;
		$edit->reteiva->rule = 'callback_chreteiva';
		$edit->reteiva->onchange="totalizar()";
		$edit->reteiva->css_class='inputnum';
		$edit->reteiva->showformat ='decimal';
		$edit->reteiva->autocomplete=false;
		//$edit->reteiva->onkeyup="reteiva()";

		$edit->reteica = new inputField("Ret. ICA","reteica");
		$edit->reteica->size = 10;
		$edit->reteica->maxlength=10;
		//$edit->reteica->rule = 'callback_chreteiva';
		$edit->reteica->css_class='inputnum';
		$edit->reteica->when=array('show');

		$edit->totneto = new inputField("Neto","totneto");
		$edit->totneto->size = 10;
		$edit->totneto->maxlength=10;
		$edit->totneto->css_class='inputnum';
		$edit->totneto->readonly=true;
		$edit->totneto->showformat ='decimal';
		$edit->totneto->type='inputhidden';

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));

		//***************************
		//Campos para el detalle 1
		//***************************
		$edit->codigo = new inputField("C&oacute;digo <#o#>", "codigo_<#i#>");
		$edit->codigo->size=5;
		$edit->codigo->db_name='codigo';
		//$edit->codigo->append($btn);
		$edit->codigo->rule="required";
		$edit->codigo->rel_id='gitser';

		$edit->descrip = new inputField("Descripci&oacute;n <#o#>", "descrip_<#i#>");
		$edit->descrip->size=25;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->rel_id='gitser';

		$edit->precio = new inputField("Precio <#o#>", "precio_<#i#>");
		$edit->precio->db_name='precio';
		$edit->precio->css_class='inputnum';
		$edit->precio->size=10;
		$edit->precio->rule='required|positive';
		$edit->precio->rel_id='gitser';
		$edit->precio->autocomplete=false;
		$edit->precio->onkeyup="importe(<#i#>)";
		$edit->precio->showformat ='decimal'; 

		$ivas=$this->datasis->ivaplica();
		$edit->tasaiva =  new dropdownField("IVA <#o#>", "tasaiva_<#i#>");
		$edit->tasaiva->option($ivas['tasa']     ,$ivas['tasa'].'%');
		$edit->tasaiva->option($ivas['redutasa'] ,$ivas['redutasa'].'%');
		$edit->tasaiva->option($ivas['sobretasa'],$ivas['sobretasa'].'%');
		$edit->tasaiva->option('0','0.00%');
		$edit->tasaiva->db_name='tasaiva';
		$edit->tasaiva->rule='positive';
		$edit->tasaiva->style="30px";
		$edit->tasaiva->rel_id   ='gitser';
		$edit->tasaiva->onchange="importe(<#i#>)";

		$edit->iva = new inputField("importe <#o#>", "iva_<#i#>");
		$edit->iva->db_name='iva';
		$edit->iva->css_class='inputnum';
		$edit->iva->rel_id   ='gitser';
		$edit->iva->size=8;
		$edit->iva->rule='positive|callback_chretiva';
		$edit->iva->onkeyup="valida(<#i#>)";
		$edit->iva->showformat ='decimal';
		$edit->iva->type='inputhidden';

		$edit->importe = new inputField("importe <#o#>", "importe_<#i#>");
		$edit->importe->db_name='importe';
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   ='gitser';
		$edit->importe->size=10;
		$edit->importe->onkeyup="valida(<#i#>)";
		$edit->importe->showformat ='decimal';
		$edit->importe->type='inputhidden';

		$edit->departa =  new dropdownField("Departamento <#o#>", "departa_<#i#>");
		$edit->departa->option('','Seleccionar');
		$edit->departa->options("SELECT codigo, CONCAT_WS('-',codigo,departam) AS label FROM dept ORDER BY codigo");
		$edit->departa->db_name='departa';
		$edit->departa->rule='required';
		$edit->departa->style = 'width:100px';
		$edit->departa->rel_id   ='gitser';
		$edit->departa->onchange="gdeparta(this.value)";

		$edit->sucursal =  new dropdownField("Sucursal <#o#>", "sucursal_<#i#>");
		$edit->sucursal->options("SELECT codigo,CONCAT(codigo,'-', sucursal) AS sucursal FROM sucu ORDER BY codigo");
		$edit->sucursal->db_name='sucursal';
		$edit->sucursal->rule='required';
		$edit->sucursal->style = 'width:100px';
		$edit->sucursal->rel_id   ='gitser';
		$edit->sucursal->onchange="gsucursal(this.value)";
		//*****************************
		//Fin de campos para detalle
		//*****************************

		//*****************************
		//Campos para el detalle reten
		//****************************
		$edit->itorigen = new autoUpdateField('origen','GSER','GSER');
		$edit->itorigen->rel_id ='gereten';

		$edit->codigorete = new dropdownField('','codigorete_<#i#>');
		$edit->codigorete->option('','Seleccionar');  
		$edit->codigorete->options('SELECT TRIM(codigo) AS codigo,TRIM(CONCAT_WS("-",codigo,activida)) AS activida FROM rete ORDER BY codigo'); 
		$edit->codigorete->db_name='codigorete';
		$edit->codigorete->rule   ='max_length[4]';
		$edit->codigorete->style  ='width: 350px';
		$edit->codigorete->rel_id ='gereten';
		$edit->codigorete->onchange='post_codigoreteselec(<#i#>,this.value)';

		$edit->base = new inputField('base','base_<#i#>');
		$edit->base->db_name='base';
		$edit->base->rule='max_length[10]|numeric|positive';
		$edit->base->css_class='inputnum';
		$edit->base->size =12;
		$edit->base->autocomplete=false;
		$edit->base->rel_id    ='gereten';
		$edit->base->maxlength =10;
		$edit->base->onkeyup   ='importerete(<#i#>)';
		$edit->base->showformat ='decimal'; 

		$edit->porcen = new inputField('porcen','porcen_<#i#>');
		$edit->porcen->db_name='porcen';
		$edit->porcen->rule='max_length[5]|numeric|positive';
		$edit->porcen->css_class='inputnum';
		$edit->porcen->size =7;
		$edit->porcen->rel_id    ='gereten';
		$edit->porcen->readonly  = true;
		$edit->porcen->maxlength =5;
		$edit->porcen->showformat ='decimal';
		$edit->porcen->type='inputhidden';

		$edit->monto = new inputField('monto','monto_<#i#>');
		$edit->monto->db_name='monto';
		$edit->monto->rule='max_length[10]|numeric|positive';
		$edit->monto->css_class='inputnum';
		$edit->monto->rel_id    ='gereten';
		$edit->monto->size =12;
		$edit->monto->readonly  = true;
		$edit->monto->maxlength =8;
		$edit->monto->showformat ='decimal';
		$edit->monto->type='inputhidden';
		//*****************************
		//Fin de campos para detalle
		//*****************************

		/*if($edit->_status=='create'){
			$accion="javascript:calcularete()";
			$edit->button_status('btn_crete','Calcular retenciones',$accion,'TR','show');
		}*/

		$edit->buttons('save', 'undo', 'delete', 'back','add_rel','add');
		$edit->build();

		$smenu['link']   = barra_menu('518');
		$conten['form']  =& $edit;
		$data['content'] =  $this->load->view('view_gser', $conten,true);
		$data['smenu']   =  $this->load->view('view_sub_menu', $smenu,true);
		$data['title']   =  heading('Registro de Gastos o Nota de D&eacute;bito');
		$data['head']    =  script('jquery.js').script('jquery-ui.js');
		$data['head']   .=  script('plugins/jquery.numeric.pack.js');
		$data['head']   .=  script('plugins/jquery.meiomask.js');
		$data['head']   .=  style('redmond/jquery-ui-1.8.1.custom.css');
		$data['head']   .=  $this->rapyd->get_head();
		$data['head']   .=  phpscript('nformat.js');
		$data['head']   .=  script('plugins/jquery.floatnumber.js');
		$data['head']   .=  script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$this->load->view('view_ventanas', $data);
	}

	//Calcula las retenciones para enviarlas por ajax
	function calcularete(){
		$post    = $_POST;

		if(!isset($post['proveed'])){
			return;
		}
		$proveed = $post['proveed'];

		$contribu= $this->datasis->traevalor('CONTRIBUYENTE');
		if($contribu<>'ESPECIAL'){
			//echo 'No es contribu';
			return;
		}

		$data=array();
		foreach($post as $ind=>$val){
			$subject = "abcdef";
			$pattern = '/^def/';
			if(preg_match('/^codigo_(?P<id>\d+)+/',$ind, $match)>0){
				$id=$match['id'];
				$pivot=array('codigo'=>$post['codigo_'.$id] , 'monto'=>$post['precio_'.$id]);
				$data[]=$pivot;
			}
		}

		$tiposprv= $this->datasis->dameval('SELECT tipo FROM sprv WHERE proveed='.$this->db->escape($proveed));
		$campo   = ($tiposprv=='1')? 'retej' : 'reten';

		$greten=$pgreten=array();
		foreach($data as $cgas){
			$codigo = $cgas['codigo'];
			$precio = $cgas['monto'];
			$mmsql="SELECT b.codigo ,a.descrip, b.base1,b.tari1,b.activida,b.pama1
						FROM mgas AS a
						LEFT JOIN rete AS b ON a.$campo=b.codigo
					WHERE a.codigo=".$this->db->escape($codigo)." LIMIT 1";

			$fila=$this->datasis->damerow($mmsql);
			if(!empty($fila['pama1'])){
				if($precio>=$fila['base1']){
					$itbase= $precio*($fila['base1']/100);
					$itret = $itbase*($fila['tari1']/100);
					if($itret>0){
						$gind=$fila['codigo'];
						if(isset($pgreten[$gind])){
							$pgreten[$gind][0]+= $itbase;
							$pgreten[$gind][2]+= $itret;
						}else{
							$pgreten[$gind]= array($itbase,$fila['tari1'],$itbase*$fila['tari1']/100);
						}
			
					}
				}
			}
		}
		$pivot=array();
		if(count($pgreten)>0){
			foreach($pgreten as $ind=>$vals){
				$pivot['codigo']= $ind;
				$pivot['base']  = round($vals[0],2);
				$pivot['porcen']= round($vals[1],2);
				$pivot['monto'] = round($vals[2],2);
				$greten[]=$pivot;
			}
			
		}
		echo json_encode($greten);
	}

	function mgserdataedit(){
		$this->rapyd->load('dataedit');
		$this->rapyd->uri->keep_persistence();

		$sprv=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;digo Proveedor',
			'nombre'=>'Nombre',
			'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor');

		$bsprv=$this->datasis->modbus($sprv);

		$edit = new DataEdit('Correccion','gser');
		//$edit->back_save  =true;
		//$edit->back_cancel=true;
		$edit->back_cancel_save=true;
		$edit->pre_process( 'create','_pre_mgsercreate' );
		$edit->pre_process( 'update','_pre_mgserupdate' );
		$edit->post_process('update','_post_mgserupdate');
		$edit->back_url = 'finanzas/gser';

		$edit->fecha = new dateonlyField('Fecha Recepci&oacute;n', 'fecha');
		$edit->fecha->size = 10;
		$edit->fecha->rule= 'required';

		$edit->ffactura = new dateonlyField('Fecha Documento', 'ffactura');
		$edit->ffactura->size = 10;
		$edit->ffactura->rule= 'required';

		$edit->vence = new dateonlyField('Fecha Vencimiento', 'vence');
		$edit->vence->size = 10;
		$edit->vence->rule= 'required';

		$edit->serie = new inputField('N&uacute;mero', 'serie');
		$edit->serie->size = 20;
		$edit->serie->rule= 'required|trim';
		$edit->serie->maxlength=20;

		$edit->nfiscal = new inputField('Control F&iacute;scal', 'nfiscal');
		$edit->nfiscal->size = 20;
		$edit->nfiscal->rule= 'required|max_length[12]|trim';
		$edit->nfiscal->maxlength=20;

		$edit->proveed = new inputField('C&oacute;digo', 'proveed');
		$edit->proveed->size =8;
		$edit->proveed->maxlength=5;
		$edit->proveed->append($bsprv);
		$edit->proveed->rule = 'required|trim';
		//$edit->proveed->group='Datos Proveedor';

		$edit->nombre = new inputField('Nombre ', 'nombre');
		$edit->nombre->size =  50;
		$edit->nombre->maxlength=40;
		$edit->nombre->readonly = true;
		$edit->nombre->rule= 'required';
		//$edit->nombre->group='Datos Proveedor';

		$edit->codb1 = new inputField('C&oacute;digo del banco', 'codb1');
		$edit->codb1->mode='autohide';
		$edit->codb1->group='Datos finacieros';

		$edit->tipo1 = new dropdownField('Tipo de operaci&oacute;n', 'tipo1');
		$edit->tipo1->option('N','Nota de d&eacute;bito');
		$edit->tipo1->option('C','Cheque');  
		$edit->tipo1->mode='autohide';
		$edit->tipo1->group='Datos finacieros';

		$edit->cheque1 = new inputField('N&uacute;mero', 'cheque1');
		$edit->cheque1->mode='autohide';
		$edit->cheque1->group='Datos finacieros';

		$edit->totpre = new inputField('Monto neto', 'totpre');
		$edit->totpre->mode='autohide';
		$edit->totpre->group='Montos';

		$edit->totiva = new inputField('Impuesto', 'totiva');
		$edit->totiva->mode='autohide';
		$edit->totiva->group='Montos';

		$edit->credito = new inputField('Monto a Cr&eacute;dito', 'credito');
		$edit->credito->mode='autohide';
		$edit->credito->group='Montos';

		$edit->totbruto = new inputField('Monto total', 'totbruto');
		$edit->totbruto->mode='autohide';
		$edit->totbruto->group='Montos';

		$edit->buttons('save');
		$edit->build();

		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_gsermgser', $conten,true);

		//$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Correccion de Egresos');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_mgserupdate($do){
		$serie   = $do->get('serie');
		$nnumero = substr($serie,-8);
		$do->set('numero',$nnumero);
	}

	function _post_mgserupdate($do) {
		$fecha     = $this->db->escape($do->get('fecha'));
		$vence     = $this->db->escape($do->get('vence'));
		$proveed   = $this->db->escape($do->get('proveed'));
		$nombre    = $this->db->escape($do->get('nombre'));
		$transac   = $do->get('transac');
		$dbtransac = $this->db->escape($transac);
		$numero    = $this->db->escape($do->get('numero'));

		$update="UPDATE gser SET serie=$numero WHERE transac=$dbtransac";
		$this->db->query($update);

		$update2="UPDATE gitser SET fecha=$fecha, proveed=$proveed,numero=$numero WHERE transac=$dbtransac";
		$this->db->query($update2);

		//MODIFICA SPRM
		$update3="UPDATE sprm SET fecha=$fecha,vence=$vence, numero=$numero, cod_prv=$proveed,nombre=$nombre WHERE tipo_doc='FC'AND transac=$dbtransac";
		$this->db->query($update3);

		//MODIFICA BMOV
		$update4="UPDATE bmov SET fecha=$fecha, numero=$numero, codcp=$proveed,nombre=$nombre WHERE clipro='P' AND transac=$dbtransac";
		$this->db->query($update4);

		//MODIFICA RIVA
		$update5="UPDATE riva SET fecha=$fecha, numero=$numero,clipro=$proveed,nombre=$nombre WHERE transac=$dbtransac";
		$this->db->query($update5);

		logusu('GSER',"Gasto $numero CAMBIADO");
		return true;
	}

	function _pre_mgsercreate($do){
		return false;
	}

	function _pre_insert($do){
		$fecha   = $do->get('fecha');
		$usuario = $do->get('usuario');
		$proveed = $do->get('proveed');
		$ffecha  = $do->get('ffactura');
		$codb1   = $do->get('codb1');
		$tipo1   = $do->get('tipo1');
		$benefi  = $do->get('benefi');
		$nombre  = $do->get('nombre');
		$numero  = $do->get('numero');
		$nfiscal = $do->get('nfiscal');
		$tipo_doc= $do->get('tipo_doc');
		$monto1  = $do->get('monto1');
		if(empty($monto1)){
			$monto1  = 0;
		}
		//$cheque1= $do->get('cheque1');
		$_tipo=common::_traetipo($codb1);

		$do->set('serie',$numero);
		$nnumero = substr($numero,-8);
		$do->set('numero',$nnumero);

		if(empty($benefi) && $tipo1=='C'){
			$do->set('benefi',$nombre);
		}

		if(empty($nfiscal)){
			$do->set('nfiscal',$numero);
		}

		if($_tipo=='CAJ'){
			$nn=$this->datasis->banprox($codb1);
			$do->set('cheque1',$nn);
		}else{
			if(!empty($codb1)){
				$cheque=$do->get('cheque1');
				$cheque=str_pad($cheque, 12, '0', STR_PAD_LEFT);
				$do->set('cheque1',$cheque);
			}
		}

		$mSQL='SELECT COUNT(*) FROM gser WHERE proveed='.$this->db->escape($proveed).' AND numero='.$this->db->escape($numero).' AND fecha='.$this->db->escape($fecha).' AND tipo_doc='.$this->db->escape($tipo_doc);
		$ca=$this->datasis->dameval($mSQL);
		if($ca>0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Al parecer ya esta registrado un gasto con la misma fecha de recepci&oacute;n, n&uacute;mero y proveedor.';
			return false;
		}

		//Totalizamos la retenciones (exepto la de iva)
		$retemonto=$rete_cana_vacio=0;
		$rete_cana=$do->count_rel('gereten');
		for($i=0;$i<$rete_cana;$i++){
			$codigorete = $do->get_rel('gereten','codigorete',$i);
			if(!empty($codigorete)){
				$importe = $do->get_rel('gereten','base'  ,$i);
				$monto   = $do->get_rel('gereten','monto' ,$i);

				//$do->set_rel('gereten','monto'    ,$monto           ,$i);
				//$do->set_rel('gereten','porcen'   ,$rete['tari1']   ,$i);
				$retemonto += $monto;
			}else{
				$rete_cana_vacio++;
			}
		}
		$do->set('reten',$retemonto);
		if($rete_cana_vacio==$rete_cana) $do->unset_rel('gereten'); //si no hay retencion elimina la relacion
		//Fin de las retenciones exepto iva

		$ivat=$subt=$total=0;
		$tasa=$reducida=$sobretasa=$montasa=$monredu=$monadic=$exento=0;
		$con=$this->db->query("SELECT tasa,redutasa,sobretasa FROM civa ORDER BY fecha desc LIMIT 1");
		$t=$con->row('tasa');$rt=$con->row('redutasa');$st=$con->row('sobretasa');
		$cana=$do->count_rel('gitser');

		for($i=0;$i<$cana;$i++){
			$codigo = $do->get_rel('gitser','codigo' ,$i);
			$auxt   = $do->get_rel('gitser','tasaiva',$i);
			$precio = $do->get_rel('gitser','precio' ,$i);
			$iva    = $precio*($auxt/100);

			$importe=$iva+$precio;
			$total+=$importe;
			$ivat +=$iva;
			$subt +=$precio;

			$do->set_rel('gitser','iva'    ,$iva        ,$i);
			$do->set_rel('gitser','importe',$importe,$i);
		}

		//Calcula la retencion del iva si aplica
		$rif      = $this->datasis->traevalor('RIF');
		$contribu = $this->datasis->traevalor('CONTRIBUYENTE');
		if($contribu=='ESPECIAL' && $rif!='V'){
			$prete=$this->datasis->dameval('SELECT reteiva FROM sprv WHERE proveed='.$this->db->escape($proveed));
			if(empty($prete)) $prete=75;
			$reteiva=$ivat*$prete/100;
		}else{
			$reteiva=0;
		}
		$do->get('reteiva', $reteiva);
		//Fin del calculo de la retencion de iva

		//Chequea que el monto retenido no sea mayor a la base del gasto
		if($retemonto+$reteiva>$subt){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Opps!! no se puede cargar un gasto cuyas retenciones sean mayores a la base del mismo.';
			return false;
		}

		//Calcula los totales
		$totneto=$total-$retemonto-$reteiva;
		$do->set('totpre'  ,$subt );
		$do->set('totbruto',$total);
		$do->set('totiva'  ,$ivat );
		$do->set('totneto' ,$totneto);
		$do->set('credito' ,$totneto-$monto1);

		//Calcula la tasa particulares
		$trans=$this->datasis->fprox_numero('ntransa');
		$do->set('transac',$trans);
		for($i=0;$i<$cana;$i++){
			$auxt   = $do->get_rel('gitser','tasaiva',$i);
			$precio = $do->get_rel('gitser','precio' ,$i);
			$iva    = $do->get_rel('gitser','iva'    ,$i);
			if($auxt-$t==0) {
				$tasa   +=$iva;
				$montasa+=$precio;
				$do->set_rel('gitser','tasa'     ,$iva   ,$i);
				$do->set_rel('gitser','montasa'  ,$precio,$i);
			}elseif($auxt-$rt==0) {
				$reducida+=$iva;
				$monredu +=$precio;
				$do->set_rel('gitser','reducida' ,$iva   ,$i);
				$do->set_rel('gitser','monredu'  ,$precio,$i);
			}elseif($auxt-$st==0) {
				$sobretasa+=$iva;
				$monadic  +=$precio;
				$do->set_rel('gitser','sobretasa',$iva   ,$i);
				$do->set_rel('gitser','monadic'  ,$precio,$i);
			}else{
				$exento+=$precio;
				$do->set_rel('gitser','exento'   ,$precio,$i);
			}

			$do->set_rel('gitser','fecha'   ,$fecha  ,$i);
			$do->set_rel('gitser','numero'  ,$numero ,$i);
			$do->set_rel('gitser','transac' ,$trans  ,$i);
			$do->set_rel('gitser','usuario' ,$usuario,$i);
			$do->set_rel('gitser','proveed' ,$proveed,$i);
			$do->set_rel('gitser','fechafac',$ffecha ,$i);

			$do->rel_rm_field('gitser','tasaiva',$i);//elimina el campo comodin
		}

		$do->set('tasa'     ,$tasa     );
		$do->set('montasa'  ,$montasa  );
		$do->set('reducida' ,$reducida );
		$do->set('monredu'  ,$monredu  );
		$do->set('sobretasa',$sobretasa);
		$do->set('monadic'  ,$monadic  );
		$do->set('exento'   ,$exento   );

		if ($monto1>0){
			$negreso  = $this->datasis->fprox_numero('negreso');
			$ncausado = "";
		}else{
			$ncausado = $this->datasis->fprox_numero('ncausado');
			$negreso  = "";
		}
		$do->set('negreso' ,$negreso );
		$do->set('ncausado',$ncausado);

		return true;
	}

	function _post_insert($do){
		$codbanc  = $do->get('codb1');
		$codprv   = $do->get('proveed');
		$numero   = $do->get('numero');
		$fecha    = $do->get('fecha');
		$fechafac = $do->get('ffactura');
		$montasa  = $do->get('montasa');
		$monredu  = $do->get('monredu');
		$monadic  = $do->get('monadic');
		$tasa     = $do->get('tasa');
		$reducida = $do->get('reducida');
		$sobretasa= $do->get('sobretasa');
		$exento   = $do->get('exento');
		$causado  = $do->get('ncausado');
		$negreso  = $do->get('negreso');
		$transac  = $do->get('transac');
		$cheque   = $do->get('cheque1');
		$monto1   = $do->get('monto1');
		$tipo1    = $do->get('tipo1');
		$codb1    = $do->get('codb1');
		$reiva    = $do->get('reteiva');
		$reten    = $do->get('reten');
		$nfiscal  = $do->get('nfiscal');
		$tipo     = $do->get('tipo_doc');
		$afecta   = $do->get('afecta');
		$estampa  = $do->get('estampa');
		$usuario  = $do->get('usuario');
		$hora     = $do->get('hora');
		$nombre   = $do->get('nombre');
		$totiva   = $do->get('totiva');

		$totbruto = $do->get('totbruto');
		$totneto  = $do->get('totneto');
		$totcred  = round($totneto-$monto1,2);

		//Crea el abono
		if($monto1>0){
			$absprm  = $this->datasis->fprox_numero('num_nd');
			$control = $this->datasis->fprox_numero('nsprm');

			$data=array();
			$data['cod_prv']    = $codprv;
			$data['nombre']     = $nombre;
			$data['tipo_doc']   = 'AB';
			$data['numero']     = $absprm;
			$data['fecha']      = $fecha ;
			$data['monto']      = $monto1;
			$data['impuesto']   = 0 ;
			$data['abonos']     = $monto1;
			$data['vence']      = $fecha;
			$data['observa1']   = 'ABONA A '.$tipo.$numero;
			$data['transac']    = $transac;
			$data['estampa']    = $estampa;
			$data['hora']       = $hora;
			$data['usuario']    = $usuario;
			$data['banco']      = $codb1;
			$data['tipo_op']    = ($tipo1=='C')? 'CH' :'DE';
			$data['numche']     = $cheque;
			$data['reteiva']    = 0;
			$data['montasa']    = 0;
			$data['monredu']    = 0;
			$data['monadic']    = 0;
			$data['tasa']       = 0;
			$data['reducida']   = 0;
			$data['sobretasa']  = 0;
			$data['exento']     = 0;
			$data['control']    = $control;

			$sql=$this->db->insert_string('sprm', $data);
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'gser');}
		}
		//Fin de la creacion del abono

		//Crea el movimiento para la retencion ISLR
		if($reten>0){
			$mnsprm  = $this->datasis->fprox_numero('num_nd');
			$control = $this->datasis->fprox_numero('nsprm');

			$data=array();
			$data['cod_prv']    = 'RETEN';
			$data['nombre']     = 'RETENCIONES POR ENTERAR';
			$data['tipo_doc']   = 'ND';
			$data['numero']     = $mnsprm;
			$data['fecha']      = $fecha;
			$data['monto']      = $reten;
			$data['impuesto']   = 0;
			$data['abonos']     = 0;
			$data['vence']      = $fecha;
			$data['tipo_ref']   = $tipo;
			$data['num_ref']    = $numero;
			$data['observa1']   = 'RET/I.S.L.R. CAUSADA EN';
			$data['observa2']   = 'FACTURA # '.$numero.' DE FECHA '.$fechafac;
			$data['transac']    = $transac;
			$data['estampa']    = $estampa;
			$data['hora']       = $hora;
			$data['usuario']    = $usuario;
			$data['reteiva']    = 0;
			$data['montasa']    = 0;
			$data['monredu']    = 0;
			$data['monadic']    = 0;
			$data['tasa']       = 0;
			$data['reducida']   = 0;
			$data['sobretasa']  = 0;
			$data['exento']     = 0;
			$data['control']    = $control;
			$data['codigo']     = 'NOCON';
			$data['descrip']    = 'NOTA DE CONTABILIDAD';

			$sql=$this->db->insert_string('sprm', $data);
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'gser');}
		}
		//Fin de la retencion ISLR

		//Crea el movimiento para la retencion de IVA
		if($reiva>0){
			$mnsprm  = $this->datasis->fprox_numero('num_nd');
			$control = $this->datasis->fprox_numero('nsprm');

			$data=array();
			$data['cod_prv']    = 'REIVA';
			$data['nombre']     = 'RETENCIONES POR ENTERAR';
			$data['tipo_doc']   = 'ND';
			$data['numero']     = $mnsprm;
			$data['fecha']      = $fecha;
			$data['monto']      = $reiva;
			$data['impuesto']   = 0;
			$data['abonos']     = 0;
			$data['vence']      = $fecha;
			$data['tipo_ref']   = $tipo;
			$data['num_ref']    = $numero;
			$data['observa1']   = 'RET/IVA CAUSADA EN';
			$data['observa2']   = 'FACTURA # '.$numero.' DE FECHA '.$fechafac;
			$data['transac']    = $transac;
			$data['estampa']    = $estampa;
			$data['hora']       = $hora;
			$data['usuario']    = $usuario;
			$data['reteiva']    = 0;
			$data['montasa']    = 0;
			$data['monredu']    = 0;
			$data['monadic']    = 0;
			$data['tasa']       = 0;
			$data['reducida']   = 0;
			$data['sobretasa']  = 0;
			$data['exento']     = 0;
			$data['control']    = $control;
			$data['codigo']     = 'NOCON';
			$data['descrip']    = 'NOTA DE CONTABILIDAD';

			$sql=$this->db->insert_string('sprm', $data);
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'gser'); }
			$this->_gserrete($fecha,$tipo,$fechafac,$numero,$nfiscal,$afecta,$codprv,$montasa,$monredu,$monadic,$tasa,$reducida,$sobretasa,$exento,$reiva,$transac);
		}
		//Fin de la retencion de IVA

		//Crea el movimiento en banco del monto al contado
		if($monto1 > 0.00){
			$benefi=$do->get('benefi');
			$msj = "EGRESO AL CONTADO SEGUN FACTURA $numero";
			$this->_bmovgser($codbanc,$codprv,$codbanc,$negreso,$cheque,$fecha,$monto1,$benefi,$transac,$msj);	
		}
		//Fin del movimiento en el banco
		
		//Crea la cuenta por pagar si es necesario
		if($totcred > 0.00){
			$causado = $this->datasis->fprox_numero('ncausado');
			$control = $this->datasis->fprox_numero('nsprm');

			$data=array();
			$data['cod_prv']    = $codprv;
			$data['nombre']     = $nombre;
			$data['tipo_doc']   = $tipo;
			$data['numero']     = $numero ;
			$data['fecha']      = $fecha ;
			$data['monto']      = $totbruto;
			$data['impuesto']   = $totiva ;
			$data['abonos']     = $monto1+$reten+$reiva;
			$data['vence']      = $fecha;
			$data['observa1']   = '';
			$data['transac']    = $transac;
			$data['estampa']    = $estampa;
			$data['hora']       = $hora;
			$data['usuario']    = $usuario;
			$data['reteiva']    = $reiva;
			$data['montasa']    = $montasa;
			$data['monredu']    = $monredu;
			$data['monadic']    = $monadic;
			$data['tasa']       = $tasa;
			$data['reducida']   = $reducida;
			$data['sobretasa']  = $sobretasa;
			$data['exento']     = $exento;
			$data['causado']    = $causado;
			$data['control']    = $control;

			$sql=$this->db->insert_string('sprm', $data);
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'gser');}
			
			//Si tiene retencion de IVA
			if($reiva>0){
				$ncsprm = $this->datasis->fprox_numero('num_nc');
				$data=array();
				$data['cod_prv']    = $codprv;
				$data['nombre']     = $nombre;
				$data['tipo_doc']   = 'NC';
				$data['numero']     = $ncsprm ;
				$data['fecha']      = $fecha ;
				$data['monto']      = $reiva;
				$data['impuesto']   = 0;
				$data['tipo_ref']   = $tipo;
				$data['num_ref']    = $numero;
				$data['abonos']     = $reiva;
				$data['vence']      = $fecha;
				$data['observa1']   = 'RET/IVA CAUSADA EN';
				$data['observa2']   = 'FACTURA # '.$numero.' DE FECHA '.$fechafac;
				$data['transac']    = $transac;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['usuario']    = $usuario;
				$data['reteiva']    = 0;
				$data['montasa']    = 0;
				$data['monredu']    = 0;
				$data['monadic']    = 0;
				$data['tasa']       = 0;
				$data['reducida']   = 0;
				$data['sobretasa']  = 0;
				$data['exento']     = 0;
				$data['control']    = $control;

				$sql=$this->db->insert_string('sprm', $data);
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'gser');}				
			}
			
			//Si tiene ISLR
			if($reten>0){
				$ncsprm = $this->datasis->fprox_numero('num_nc');
				$data=array();
				$data['cod_prv']    = $codprv;
				$data['nombre']     = $nombre;
				$data['tipo_doc']   = 'NC';
				$data['numero']     = $ncsprm ;
				$data['fecha']      = $fecha ;
				$data['monto']      = $reten;
				$data['impuesto']   = 0;
				$data['tipo_ref']   = $tipo;
				$data['num_ref']    = $numero;
				$data['abonos']     = $reten;
				$data['vence']      = $fecha;
				$data['observa1']   = 'RET/I.S.L.R. CAUSADA EN';
				$data['observa2']   = 'FACTURA # '.$numero.' DE FECHA '.$fechafac;
				$data['transac']    = $transac;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['usuario']    = $usuario;
				$data['reteiva']    = 0;
				$data['montasa']    = 0;
				$data['monredu']    = 0;
				$data['monadic']    = 0;
				$data['tasa']       = 0;
				$data['reducida']   = 0;
				$data['sobretasa']  = 0;
				$data['exento']     = 0;
				$data['control']    = $control;

				$sql=$this->db->insert_string('sprm', $data);
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'gser');}
			}
		}
		//Fin de la cuenta por pagar

		logusu('gser',"Gasto $numero CREADO");
		return true;
	}

	function _pre_delete($do){
		$transac  = $do->get('transac');		
		$tipo_doc = $do->get('tipo_doc');
		$cod_prv  = $do->get('proveed');

		if($tipo_doc=='XX'){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='El gasto ya fue anulado.';
			return false;
		}

		$this->db->select(array('cod_prv','tipo_doc','monto','abonos'));
		$this->db->from('sprm');
		$this->db->where('transac',$transac);
		$query = $this->db->get();

		$verif=true;
		$abs=$fcs=0;
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				if($row->tipo_doc=='NC' || $row->tipo_doc=='AB'){
					$abs+=$row->monto;
				}
				if($row->tipo_doc=='ND' && $row->abonos!=0 && $row->cod_prv!=$cod_prv){
					$verif=false;
					break;
				}
				if($row->tipo_doc==$tipo_doc && $row->cod_prv==$cod_prv){
					$fcs=$row->abonos;
				}
			}
			if(round($fcs-$abs,2)!=0.00){
				$verif=false;
			}
		}

		if(!$verif){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='No se puede anular el gasto por tener abonos, eliminelos antes de continuar.';
			return false;
		}

		$this->db->delete('sprm', array('transac' => $transac));
		$this->_rm_gserrete($transac);
		$this->_rm_bmovgser($transac);

		$this->db->where('transac', $transac);
		$this->db->update('gser', array('tipo_doc'=>'XX'));

		$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Gasto Anulado.';
		return false;
	}

	function _pre_update($do){
		return false;
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('gser',"Gasto $codigo Modificado");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('gser',"Gasto $codigo ELIMINADO");
	}

	//Chequea que exista un monto cuando se seleccion un banco/caja
	function chmontocontado($val){
		$codb1=$this->input->post('codb1');
		if(!empty($codb1)){
			if($val<=0){
				$this->validation->set_message('chmontocontado', 'El campo %s no puede ser menor o igual a cero si selecciono una caja o banco');
				return false;
			}
		}
		return true;
	}

	function chtasa($monto){
		$iva   = $this->input->post('montasa');
		$iva   = (empty($iva))?   0: $iva  ;
		$monto = (empty($monto))? 0: $monto;
		if(!is_numeric($monto)){
			$this->validation->set_message('chtasa', 'El campo %s general debe contener n&uacute;meros.');
			return false;			
		}

		if($monto>0 && $iva>0){
			return true;
		}elseif($monto==0 && $iva==0){
			return true;
		}else{
			$this->validation->set_message('chtasa', "Si la base general es mayor que cero debe generar impuesto");
			return false;
		}
	}

	function chreducida($monto){
		$iva=$this->input->post('monredu');
		$iva   = (empty($iva))?   0: $iva  ;
		$monto = (empty($monto))? 0: $monto;
		if(!is_numeric($monto)){
			$this->validation->set_message('chreducida', 'El campo %s reducida debe contener n&uacute;meros.');
			return false;			
		}

		if($monto>0 && $iva>0){
			return true;
		}elseif($monto==0 && $iva==0){
			return true;
		}else{
			$this->validation->set_message('chreducida', "Si la base reducida es mayor que cero debe generar impuesto");
			return false;
		}
	}

	function chsobretasa($monto){
		$iva=$this->input->post('monadic');
		$iva   = (empty($iva))?   0: $iva  ;
		$monto = (empty($monto))? 0: $monto;
		if(!is_numeric($monto)){
			$this->validation->set_message('chsobretasa', 'El campo %s adicional debe contener n&uacute;meros.');
			return false;			
		}

		if($monto>0 && $iva>0){
			return true;
		}elseif($monto==0 && $iva==0){
			return true;
		}else{
			$this->validation->set_message('chsobretasa', "Si la base adicional es mayor que cero debe generar impuesto");
			return false;
		}
	}

	function instalar(){
		$query="SHOW INDEX FROM gser";
		$resul=$this->db->query($query);
		$existe=0;
		foreach($resul->result() as $ind){
			$nom= $ind->Column_name;
			if ($nom == 'id'){
				$existe=1;
				break;
			}
		}

		if($existe != 1) {
			$query="ALTER TABLE `gser` DROP PRIMARY KEY";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD UNIQUE INDEX `gser` (`fecha`, `numero`, `proveed`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `ncausado`,  ADD PRIMARY KEY (`id`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gitser` ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `reteica`,  ADD PRIMARY KEY (`id`);";
			$this->db->simple_query($query);
			$query="ALTER TABLE `gitser` ADD COLUMN `idgser` INT(15) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`, ADD INDEX `idgser` (`idgser`)";
			$this->db->simple_query($query);

			$query="UPDATE gitser AS a
				JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
				SET a.idgser=b.id";
			$this->db->simple_query($query);
		}

		$query="UPDATE gitser AS a
			JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
			SET a.idgser=b.id";
		$this->db->simple_query($query);


		if (!$this->db->table_exists('gserchi')) {
			$query="CREATE TABLE IF NOT EXISTS `gserchi` (
				`codbanc` varchar(5) NOT NULL DEFAULT '', 
				`fechafac` date DEFAULT NULL, 
				`numfac` varchar(8) DEFAULT NULL, 
				`nfiscal` varchar(12) DEFAULT NULL, 
				`rif` varchar(13) DEFAULT NULL, 
				`proveedor` varchar(40) DEFAULT NULL, 
				`codigo` varchar(6) DEFAULT NULL, 
				`descrip` varchar(50) DEFAULT NULL, 
				`moneda` char(2) DEFAULT NULL, 
				`montasa` decimal(17,2) DEFAULT '0.00', 
				`tasa` decimal(17,2) DEFAULT NULL, 
				`monredu` decimal(17,2) DEFAULT '0.00', 
				`reducida` decimal(17,2) DEFAULT NULL, 
				`monadic` decimal(17,2) DEFAULT '0.00', 
				`sobretasa` decimal(17,2) DEFAULT NULL, 
				`exento` decimal(17,2) DEFAULT '0.00', 
				`importe` decimal(12,2) DEFAULT NULL, 
				`sucursal` char(2) DEFAULT NULL, 
				`departa` char(2) DEFAULT NULL, 
				`usuario` varchar(12) DEFAULT NULL, 
				`estampa` date DEFAULT NULL, 
				`hora` varchar(8) DEFAULT NULL, 
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
				PRIMARY KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
			$this->db->simple_query($query);
		}

		if (!$this->db->table_exists('rica')) {
			$query="CREATE TABLE `rica` (
				`codigo` CHAR(5)    NOT  NULL,
				`activi` CHAR(14)   NULL DEFAULT NULL,
				`aplica` CHAR(100)  NULL DEFAULT NULL,
				`tasa` DECIMAL(8,2) NULL DEFAULT NULL,
				PRIMARY KEY (`codigo`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
			$this->db->simple_query($query);
		}

		
		if (!$this->db->field_exists('ngasto','gserchi')) {
			$query="ALTER TABLE `gserchi` ADD COLUMN `ngasto` VARCHAR(8) NULL DEFAULT NULL AFTER `departa`";
			$this->db->simple_query($query);
		}

		if (!$this->db->field_exists('aceptado','gserchi')) {
			$query="ALTER TABLE gserchi ADD COLUMN aceptado CHAR(1) NULL DEFAULT NULL";
			$this->db->simple_query($query);
		}

		
		if (!$this->db->table_exists('gereten')) {
			$query="CREATE TABLE `gereten` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`idd` INT(11) NULL DEFAULT NULL,
				`origen` CHAR(4) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				`numero` VARCHAR(25) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				`codigorete` VARCHAR(4) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				`actividad` VARCHAR(45) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				`base` DECIMAL(10,2) NULL DEFAULT NULL,
				`porcen` DECIMAL(5,2) NULL DEFAULT NULL,
				`monto` DECIMAL(10,2) NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1;";
			$this->db->simple_query($query);
		}
		
		$mSQL="ALTER TABLE gereten CHANGE COLUMN id id INT(10) NOT NULL AUTO_INCREMENT FIRST";
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 30;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'gser');
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('gser');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		if ( $sort == '') $this->db->order_by( 'id', 'desc' );

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$sql = $this->db->_compile_select($this->db->_count_string . $this->db->_protect_identifiers('numrows'));
		$results = $this->datasis->dameval($sql);

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function griditgser(){
		$id   = isset($_REQUEST['id'])  ? $_REQUEST['id']   :  0;
		if ($id == 0 ) $id = $this->datasis->dameval("SELECT MAX(id) FROM gser")  ;
	
	
		$mSQL = "SELECT * FROM gitser WHERE idgser='$id' ORDER BY id ASC";
		$query = $this->db->query($mSQL);
		$results = $query->num_rows() ; 
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function sprvbu(){
		$control = $this->uri->segment(4);
		$id = $this->datasis->dameval("SELECT b.id FROM gser a JOIN sprv b ON a.proveed=b.proveed WHERE control='$control'");
		redirect('compras/sprv/dataedit/show/'.$id);
	}

	function tabla() {
		$id   = isset($_REQUEST['id'])  ? $_REQUEST['id']   :  0;

		$transac = $this->datasis->dameval("SELECT transac FROM gser WHERE id='$id'");

		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM sprm WHERE transac='$transac' ORDER BY cod_prv ";
		$query = $this->db->query($mSQL);
		$codprv = 'XXXXXXXXXXXXXXXX';
		$salida = '';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			
			foreach ($query->result_array() as $row)
			{
				if ( $codprv != $row['cod_prv']){
					$codprv = $row['cod_prv'];
					$salida .= "<tr bgcolor='#c7d3c7'>";
					$salida .= "<td colspan=4>".trim($row['nombre']). "</td>";
					$salida .= "</tr>";	
				}
				if ( $row['tipo_doc'] == 'FC' ) {
					$saldo = $row['monto']-$row['abonos'];
				}
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		echo $salida;

//		echo 'Prueba';
	}

	function gserextjs() {
		$encabeza='GASTOS Y EGRESOS';
		$listados= $this->datasis->listados('gser');
		$otros=$this->datasis->otros('gser', 'finanzas/gser');

		$urlajax = 'finanzas/gser/';

		$columnas = "
		{ header: 'tipo',        width: 40, sortable: true, dataIndex: 'tipo_doc', field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Fecha',       width: 70, sortable: true, dataIndex: 'fecha',    field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Numero',      width: 70, sortable: true, dataIndex: 'numero',   field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Proveed',     width: 60, sortable: true, dataIndex: 'proveed',  field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Nombre',      width:200, sortable: true, dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Vence',       width: 70, sortable: true, dataIndex: 'vence',    field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Base Imp.',   width: 80, sortable: true, dataIndex: 'totpre',   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'I.V.A.',      width: 80, sortable: true, dataIndex: 'totiva',   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Total',       width: 80, sortable: true, dataIndex: 'totbruto', field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'R. ISLR',     width: 80, sortable: true, dataIndex: 'reten',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Ret.IVA',     width: 60, sortable: true, dataIndex: 'reteiva',  field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Neto',        width: 80, sortable: true, dataIndex: 'totneto',  field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Banco',       width: 60, sortable: true, dataIndex: 'codb1',    field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Tipo',        width: 40, sortable: true, dataIndex: 'tipo1' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cheque',      width: 60, sortable: true, dataIndex: 'cheque1' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Comprob.',    width: 60, sortable: true, dataIndex: 'comprob1' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Monto',       width: 60, sortable: true, dataIndex: 'monto1' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Credito',     width: 60, sortable: true, dataIndex: 'credito' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Orden',       width: 60, sortable: true, dataIndex: 'orden' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Anticipo',    width: 60, sortable: true, dataIndex: 'anticipo' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Beneficiario',width: 60, sortable: true, dataIndex: 'benefi' , field: { type: 'textfield' }, filter: { type: 'string' }},
		//{ header: 'mdolar',    width: 60, sortable: true, dataIndex: 'mdolar' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Usuario',     width: 60, sortable: true, dataIndex: 'usuario' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Estampa',     width: 60, sortable: true, dataIndex: 'estampa' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Hora',        width: 60, sortable: true, dataIndex: 'hora' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Transaccion', width: 60, sortable: true, dataIndex: 'transac' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'preten',      width: 60, sortable: true, dataIndex: 'preten' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'creten',      width: 60, sortable: true, dataIndex: 'creten' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'breten',      width: 60, sortable: true, dataIndex: 'breten' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'No.Fiscal',   width: 60, sortable: true, dataIndex: 'nfiscal' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Afecta',      width: 60, sortable: true, dataIndex: 'afecta' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'F. Afecta',   width: 60, sortable: true, dataIndex: 'fafecta' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Fecha Fac',   width: 60, sortable: true, dataIndex: 'ffactura' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Caja',        width: 60, sortable: true, dataIndex: 'cajachi' , field: { type: 'textfield' }, filter: { type: 'string' }},
		//{ header: 'montasa' ,  width: 60, sortable: true, dataIndex: 'montasa' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		//{ header: 'monredu' ,  width: 60, sortable: true, dataIndex: 'monredu' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		//{ header: 'monadic' ,  width: 60, sortable: true, dataIndex: 'monadic' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		//{ header: 'tasa' ,     width: 60, sortable: true, dataIndex: 'tasa' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		//{ header: 'reducida' , width: 60, sortable: true, dataIndex: 'reducida' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		//{ header: 'sobretasa', width: 60, sortable: true, dataIndex: 'sobretasa' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Exento' ,     width: 60, sortable: true, dataIndex: 'exento' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		//{ header: 'compra' ,   width: 60, sortable: true, dataIndex: 'compra' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Ordeni' ,     width: 60, sortable: true, dataIndex: 'ordeni' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'serie' ,      width: 60, sortable: true, dataIndex: 'serie' , field: { type: 'textfield' }, filter: { type: 'string' }},
		//{ header: 'modificado',width: 60, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }},
		//{ header: 'reteica' ,  width: 60, sortable: true, dataIndex: 'reteica' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		//{ header: 'retesimple',width: 60, sortable: true, dataIndex: 'retesimple' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Egreso',   width: 60, sortable: true, dataIndex: 'negreso' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Causado',  width: 60, sortable: true, dataIndex: 'ncausado' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Tipo_or',   width: 60, sortable: true, dataIndex: 'tipo_or' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'id',        width: 60, sortable: true, dataIndex: 'id' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0000')}		";

		$coldeta = "
	var Deta1Col = [
		{ header: 'Codigo',      width: 60, sortable: true, dataIndex: 'codigo' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Descripcion', width:160, sortable: true, dataIndex: 'descrip' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'precio',      width: 80, sortable: true, dataIndex: 'precio' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'iva',         width: 80, sortable: true, dataIndex: 'iva' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'importe',     width: 80, sortable: true, dataIndex: 'importe' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'unidades',    width: 60, sortable: true, dataIndex: 'unidades' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'fraccion',  width: 60, sortable: true, dataIndex: 'fraccion' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'almacen' , width: 60, sortable: true, dataIndex: 'almacen' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'sucursal' , width: 60, sortable: true, dataIndex: 'sucursal' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'departa' , width: 60, sortable: true, dataIndex: 'departa' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'transac' , width: 60, sortable: true, dataIndex: 'transac' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'usuario' , width: 60, sortable: true, dataIndex: 'usuario' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'estampa' , width: 60, sortable: true, dataIndex: 'estampa' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'hora' , width: 60, sortable: true, dataIndex: 'hora' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'transa' , width: 60, sortable: true, dataIndex: 'transa' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'huerfano' , width: 60, sortable: true, dataIndex: 'huerfano' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'rif' , width: 60, sortable: true, dataIndex: 'rif' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'proveedor' , width: 60, sortable: true, dataIndex: 'proveedor' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'numfac' , width: 60, sortable: true, dataIndex: 'numfac' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'fechafac' , width: 60, sortable: true, dataIndex: 'fechafac' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'nfiscal' , width: 60, sortable: true, dataIndex: 'nfiscal' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'feprox' , width: 60, sortable: true, dataIndex: 'feprox' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'dacum' , width: 60, sortable: true, dataIndex: 'dacum' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'residual' , width: 60, sortable: true, dataIndex: 'residual' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'vidau' , width: 60, sortable: true, dataIndex: 'vidau' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'montasa' , width: 60, sortable: true, dataIndex: 'montasa' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'monredu' , width: 60, sortable: true, dataIndex: 'monredu' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'monadic' , width: 60, sortable: true, dataIndex: 'monadic' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'tasa' , width: 60, sortable: true, dataIndex: 'tasa' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'reducida' , width: 60, sortable: true, dataIndex: 'reducida' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'sobretasa' , width: 60, sortable: true, dataIndex: 'sobretasa' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'exento' , width: 60, sortable: true, dataIndex: 'exento' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'id' , width: 60, sortable: true, dataIndex: 'id' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'modificado' , width: 60, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'reteica' , width: 60, sortable: true, dataIndex: 'reteica' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},

		{ header: 'idgser' , width: 60, sortable: true, dataIndex: 'idgser' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0000')},
	]";

		$variables='';
		
		$valida="		{ type: 'length', field: 'numero',  min:  1 }";
		

		$funciones = "
function renderSprv(value, p, record) {
	var mreto='';
	if ( record.data.proveed == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'finanzas/gser/sprvbu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.control );
}

function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}
	";

		$campos = $this->datasis->extjscampos('gser');

//".$this->datasis->extjscampos('gitser')."

		$stores = "
	Ext.define('Itgser', {
		extend: 'Ext.data.Model',
		fields: [".$this->datasis->extjscampos('gitser')."],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'finanzas/gser/griditgser',
				method: 'POST'
			},
			reader: {
				type: 'json',
				root: 'data',
				successProperty: 'success',
				messageProperty: 'message',
				totalProperty: 'results'
			}
		}
	});

	//////////////////////////////////////////////////////////
	// create the Data Store
	var storeItgser = Ext.create('Ext.data.Store', {
		model: 'Itgser',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});
	
	//////////////////////////////////////////////////////////
	//
	var gridDeta1 = Ext.create('Ext.grid.Panel', {
		width:   '100%',
		height:  '100%',
		store:   storeItgser,
		title:   'Detalle del Gasto',
		iconCls: 'icon-grid',
		frame:   true,
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		columns: Deta1Col
	});

	var gserTplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR COMPRA</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/verhtml/COMPRA/{control}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/ver/COMPRA/{control}\',     \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',   'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',		
		'</table>','nanai'
	];

	// Al cambiar seleccion
	gridMaest.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridMaest.down('#delete').setDisabled(selectedRecord.length === 0);
			gridMaest.down('#update').setDisabled(selectedRecord.length === 0);
			mid = selectedRecord[0].data.id;
			gridDeta1.setTitle(selectedRecord[0].data.numero+' '+selectedRecord[0].data.nombre);
			storeItgser.load({ params: { id: mid }});
			var meco1 = Ext.getCmp('imprimir');
			Ext.Ajax.request({
				url: urlApp +'finanzas/gser/tabla',
				params: { id: mid, serie: selectedRecord[0].data.numero },
				success: function(response) {
					var vaina = response.responseText;
					gserTplMarkup.pop();
					gserTplMarkup.push(vaina);
					var gserTpl = Ext.create('Ext.Template', gserTplMarkup );
					meco1.setTitle('Imprimir Compra');
					gserTpl.overwrite(meco1.body, selectedRecord[0].data );
				}
			});
		}
	});
";

		$acordioni = "{
					layout: 'fit',
					items:[
						{
							name: 'imprimir',
							id: 'imprimir',
							border:false,
							html: 'Para imprimir seleccione una Compra '
						}
					]
				},
";


		$dockedItems = "{
			xtype: 'toolbar',
			items: [
				{
					iconCls: 'icon-add',
					text: 'Agregar',
					scope: this,
					handler: function(){
						window.open(urlApp+'finanzas/gser/dataedit/create', '_blank', 'width=970,height=700,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},
				{
					iconCls: 'icon-update',
					text: 'Modificar',
					disabled: true,
					itemId: 'update',
					scope: this,
					handler: function(selModel, selections){
						var selection = gridMaest.getView().getSelectionModel().getSelection()[0];
						gridMaest.down('#delete').setDisabled(selections.length === 0);
						window.open(urlApp+'finanzas/gser/dataedit/modify/'+selection.data.control, '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},{
					iconCls: 'icon-delete',
					text: 'Eliminar',
					disabled: true,
					itemId: 'delete',
					scope: this,
					handler: function() {
						var selection = gridMaest.getView().getSelectionModel().getSelection()[0];
						Ext.MessageBox.show({
							title: 'Confirme', 
							msg: 'Seguro que quiere eliminar la compra Nro. '+selection.data.numero, 
							buttons: Ext.MessageBox.YESNO, 
							fn: function(btn){ 
								if (btn == 'yes') { 
									if (selection) {
										//storeMaest.remove(selection);
									}
									storeMaest.load();
								} 
							}, 
							icon: Ext.MessageBox.QUESTION 
						});  
					}
				}
			]
		}		
		";



		$grid2 = ",{
				itemId: 'viewport-center-detail',
				activeTab: 0,
				region: 'south',
				height: '40%',
				split: true,
				margins: '0 0 0 0',
				preventHeader: true,
				items: gridDeta1
			}";


		$titulow = 'Compras';
		
		$filtros = "";
		$features = "
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		plugins: [Ext.create('Ext.grid.plugin.CellEditing', { clicksToEdit: 2 })],
";

		$final = "storeItgser.load();";

		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['stores']      = $stores;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		$data['grid2']       = $grid2;
		$data['coldeta']     = $coldeta;
		$data['acordioni']   = $acordioni;
		$data['final']       = $final;
		
		$data['title']  = heading('Gastos');
		$this->load->view('extjs/extjsvenmd',$data);
	}
}
