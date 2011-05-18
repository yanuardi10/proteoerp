<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class ordcc extends validaciones {

	function ordcc(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index() {
		redirect('compras/ordcc/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load("datagrid","datafilter");

		$atts = array(
              'width'      => '800',
              'height'     => '600',
              'scrollbars' => 'yes',
              'status'     => 'yes',
              'resizable'  => 'yes',
              'screenx'    => '0',
              'screeny'    => '0'
              );

        $modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');

        $boton=$this->datasis->modbus($modbus);

        $filter = new DataFilter("Filtro de Orden de Compras",'ordc');

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
        $filter->numero->size=20;

        $filter->proveedor = new inputField("Proveedor", "proveed");
        $filter->proveedor->append($boton);
        $filter->proveedor->db_name = "proveed";
        $filter->proveedor->size=20;

        $filter->buttons("reset","search");
        $filter->build('dataformfiltro');

        $uri = anchor('compras/ordcc/dataedit/show/<#numero#>','<#numero#>');
        $uri_2  = anchor('compras/ordcc/dataedit/show/<#numero#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));
        //$uri_2  .= anchor('formatos/verhtml/ORDC/<#numero#>',img(array('src'=>'images/html_icon.gif','border'=>'0','alt'=>'Editar','height'=>'12')));
        $uri2 = anchor_popup('formatos/verhtml/ORDC/<#numero#>',"Ver HTML",$atts);

        $grid = new DataGrid();
        $grid->order_by("numero","desc");
        $grid->per_page = 15;

        $grid->column('Acci&oacute;n',$uri_2,'align=center');
        $grid->column_orderby("N&uacute;mero",$uri,'numero');
        $grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
        $grid->column_orderby("Fecha F.","<dbdate_to_human><#fechafac#></dbdate_to_human>",'fecha',"align='center'");
        $grid->column_orderby("Proveedor","proveed",'proveed');
        $grid->column_orderby("Nombre","nombre",'nombre');
        $grid->column_orderby("Peso","peso",'peso');
        $grid->column_orderby("IVA"  ,"montoiva" ,'montoiva' ,"align='right'");
        $grid->column_orderby("Monto" ,"montonet",'montoner' ,"align='right'");
        $grid->column_orderby("Monto Total"  ,"montotot" ,'montotot' ,"align='right'");
        //$grid->column("Vista",$uri2,"align='center'");
		$grid->add('compras/ordcc/dataedit/create');
        $grid->build('datagridST');
        
//************ SUPER TABLE ************* 
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
	headerRows : 1,
	onStart : function () {	this.start = new Date();},
	onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
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
    width: 740px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>	
';
//****************************************

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['extras']  = $extras;

        $data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		
        $data["head"]    = script('jquery.js').script('superTables.js'). $this->rapyd->get_head();
        $data['title']   ='<h1>Orden de Compras</h1>';
        $this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		
		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor');

        $boton=$this->datasis->modbus($modbus);

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
		),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigo_<#i#>',
				'descrip'=>'descrip_<#i#>',
				'base1'  =>'precio1_<#i#>',
				'base2'  =>'precio2_<#i#>',
				'base3'  =>'precio3_<#i#>',
				'base4'  =>'precio4_<#i#>',
				'iva'    =>'itiva_<#i#>',
				'peso'   =>'sinvpeso_<#i#>',
				'pond'   =>'pond_<#i#>',
				'ultimo' =>'ultimo_<#i#>',
				'precio1'=>'costo_<#i#>'
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
			'script'  => array('post_modbus_sinv(<#i#>)')
				);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');


		$do = new DataObject("ordc");
		$do->rel_one_to_many('itordc', 'itordc', 'numero');
		$do->rel_pointer('itordc','sinv','itordc.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Orden De Comnpra', $do);
		$edit->back_url = site_url('compras/ordcc/filteredgrid');
		$edit->set_rel_title('itordc','Producto <#o#>');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->proveed = new inputField('Proveedor','proveed');
		$edit->proveed->size = 6;
		$edit->proveed->maxlength=5;
		$edit->proveed->append($boton);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';

		$edit->status = new  dropdownField ('Estado', 'status');
		$edit->status->option('','');
		$edit->status->option('CE','Cerrado');
		$edit->status->option('PE','Pendiente');
		$edit->status->option('BA','BackOrde');
		$edit->status->style='width:200px;';

		$edit->arribo = new DateonlyField('Arribo', 'arribo','d/m/Y');
		$edit->arribo->insertValue = date('Y-m-d');
		$edit->arribo->rule = 'required';
		$edit->arribo->mode = 'autohide';
		$edit->arribo->size = 10;
		
		$edit->fechafac = new DateonlyField('fecha Factura', 'fechafac','d/m/Y');
		$edit->fechafac->insertValue = date('Y-m-d');
		$edit->fechafac->rule = 'required';
		$edit->fechafac->mode = 'autohide';
		$edit->fechafac->size = 10;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itordc';
		$edit->codigo->rule     = 'required';
		$edit->codigo->append($btn);

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>', 'descrip_<#i#>');
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->readonly  = true;
		$edit->descrip->rel_id='itordc';

		$edit->cantidad = new inputField('Cantidad <#o#>', 'cantidad_<#i#>');
		$edit->cantidad->db_name  = 'cantidad';
		$edit->cantidad->css_class= 'inputnum';
		$edit->cantidad->rel_id   = 'itordc';
		$edit->cantidad->maxlength= 10;
		$edit->cantidad->size     = 6;
		$edit->cantidad->rule     = 'required|positive';
		$edit->cantidad->autocomplete=false;
		$edit->cantidad->onkeyup  ='importe(<#i#>)';

		$edit->costo = new inputField('Precio <#o#>', 'costo_<#i#>');
		$edit->costo->db_name   = 'costo';
		$edit->costo->css_class = 'inputnum';
		$edit->costo->rel_id    = 'itordc';
		$edit->costo->size      = 10;
		$edit->costo->rule      = 'required|positive';
		$edit->costo->readonly  = true;

		$edit->importe = new inputField('Importe <#o#>', 'importe_<#i#>');
		$edit->importe->db_name='importe';
		$edit->importe->size=10;
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   ='itordc';

		for($i=1;$i<=4;$i++){
			$obj='precio'.$i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj.'_<#i#>');
			$edit->$obj->db_name   = 'sinv'.$obj;
			$edit->$obj->rel_id    = 'itordc';
			$edit->$obj->pointer   = true;
		}

		$edit->itiva = new hiddenField('', 'itiva_<#i#>');
		$edit->itiva->db_name  = 'iva';
		$edit->itiva->rel_id   = 'itordc';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name   = 'sinvpeso';
		$edit->sinvpeso->rel_id    = 'itordc';
		$edit->sinvpeso->pointer   = true;

		$edit->ultimo = new hiddenField('', 'ultimo_<#i#>');
		$edit->ultimo->db_name   = 'ultimo';
		$edit->ultimo->rel_id    = 'itordc';
		$edit->ultimo->pointer   = true;

		$edit->pond = new hiddenField('', "pond_<#i#>");
		$edit->pond->db_name='pond';
		$edit->pond->rel_id   ='itordc';
		$edit->pond->pointer   = true;
		//**************************
		//fin de campos para detalle
		//**************************

		$edit->montoiva = new inputField('Impuesto', 'montoiva');
		$edit->montoiva->css_class ='inputnum';
		$edit->montoiva->readonly  =true;
		$edit->montoiva->size      = 10;

		$edit->montotot = new inputField('Sub-Total', 'montotot');
		$edit->montotot->css_class ='inputnum';
		$edit->montotot->readonly  =true;
		$edit->montotot->size      = 10;

		$edit->montonet = new inputField('Monto Total', 'montonet');
		$edit->montonet->css_class ='inputnum';
		$edit->montonet->readonly  =true;
		$edit->montonet->size      = 10;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_ordc', $conten,true);
		$data['title']   = heading('Orden de Compra');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		$iva=$totals=0;
		$cana=$do->count_rel('itordc');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itordc','cantidad',$i);
			$itpreca   = $do->get_rel('itordc','costo',$i);
			$itiva     = $do->get_rel('itordc','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('itordc','importe' ,$itimporte,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;
			//$do->set_rel('itspre','mostrado',$iva+$totals,$i);
		}
		$totalg = $totals+$iva;

		
		$do->set('montonet' ,round($totals ,2));
		$do->set('montotot' ,round($totalg ,2));
		$do->set('montoiva'    ,round($iva    ,2));

		$numero =$this->datasis->fprox_numero('nordc');
		$transac=$this->datasis->fprox_numero('ntransa');
		$usuario=$do->get('usuario');
		$estampa=date('Ymd');
		$hora   =date("H:i:s");
			
		$do->set('estampa',$estampa);
		$do->set('hora'   ,$hora);
		$do->set('numero' ,$numero);
		$do->set('transac',$transac);
		
		for($i=0;$i<$cana;$i++){
			$do->set_rel('itordc','estampa' ,$estampa,$i);
			$do->set_rel('itordc','hora'    ,$hora   ,$i);
			$do->set_rel('itordc','transac' ,$transac,$i);
			$do->set_rel('itordc','usuario' ,$usuario,$i);;
		}

		return true;
	}

	function _pre_update($do){
		$iva=$totals=0;
		$cana=$do->count_rel('itordc');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itordc','cantidad',$i);
			$itpreca   = $do->get_rel('itordc','costo',$i);
			$itiva     = $do->get_rel('itordc','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('itordc','importe' ,$itimporte,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;
			//$do->set_rel('itspre','mostrado',$iva+$totals,$i);
		}
		$totalg = $totals+$iva;

		
		$do->set('montonet' ,round($totals ,2));
		$do->set('montotot' ,round($totalg ,2));
		$do->set('montoiva'    ,round($iva    ,2));

		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('ordc',"O.Compra $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('ordc',"O.Compra $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('ordc',"O.Compra $codigo ELIMINADO");
	}
}