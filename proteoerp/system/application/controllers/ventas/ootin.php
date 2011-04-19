<?php
class Ootin extends Controller {
	//otrosingresos
	function ootin(){
		parent::Controller(); 
		$this->load->library("rapyd");
    	$this->datasis->modulo_id(122,1);      
	}
	
	function index() {		
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
		
    	$scli=array(
	  	'tabla'   =>'scli',
	  	'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'contacto'=>'Contacto'),
	  	'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
	  	'retornar'=>array('cliente'=>'cod_cli'),
	  	'titulo'  =>'Buscar Cliente');
			
		$boton=$this->datasis->modbus($scli);
		
		$filter = new DataFilter("Filtro de Otros Ingresos");
		$filter->db->select('fecha,numero,cod_cli,nombre,totals,totalg,iva,tipo_doc,orden,rifci,observa1,observa2');
		$filter->db->from('otin');
		
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
		$filter->numero->size = 30;

    	$filter->cliente = new inputField("Cliente", "cod_cli");
    	$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');
    
		$uri = anchor('ventas/ootin/dataedit/show/<#tipo_doc#>/<#numero#>','<#numero#>');
		$uri_2  = anchor('ventas/ootin/dataedit/show/<#tipo_doc#>/<#numero#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));
		
		$grid = new DataGrid();
		$grid->order_by("fecha","desc");
		$grid->per_page = 50;  
		
		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("N&uacute;mero",$uri,'numero');
    	$grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
    	$grid->column_orderby("C&oacute;digo Cliente","cod_cli",'cod_cli');
    	$grid->column_orderby("Orden","orden",'orden');
    	$grid->column_orderby("Rif","rifci",'rifci');
    	$grid->column_orderby("Nombre","nombre",'nombre');
    	$grid->column_orderby("Observaci&oacute;n 1","observa1",'observa1');
    	$grid->column_orderby("Observaci&oacute;n 2","observa2",'observa2');
    	$grid->column_orderby("Sub.Total","<nformat><#totals#>|2</nformat>",'totals',"align=right");
    	$grid->column_orderby("IVA","<nformat><#iva#>|2</nformat>",'iva',"align=right");
    	$grid->column_orderby("Total","<nformat><#totalg#>|2</nformat>",'totalg',"align=right");
    	
		$grid->add("ventas/ootin/dataedit/create");
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

		$data['title']  = heading('Otros Ingresos');
		$data['head']   = script('jquery.js');
		$data["head"]  .= script('superTables.js');
		$data['head']  .= $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
	}
	
	function dataedit(){
 		$this->rapyd->load('dataobject','datadetails');
 	 
 	 	$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre', 
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n',
			'tipo'=>'Tipo'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','rifci'=>'rifci',
						  'dire11'=>'direc'),
		'titulo'  =>'Buscar Cliente',
		'script'  => array('post_modbus_scli()'));
		$boton =$this->datasis->modbus($mSCLId);
 		
 		$modbus=array(
			'tabla'   =>'botr',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'nombre' =>'Nombre',
				'cuenta' =>'Cuenta',
				'precio' =>'Precio',
				'iva' =>'Iva',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array(
				'codigo' =>'codigo_<#i#>',
				'nombre'=>'descrip_<#i#>',
				'precio'    =>'precio_<#i#>',
				'iva'    =>'impuesto_<#i#>',
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar ',
			'script'  => array('post_modbus_botr()')
		);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

 		$do = new DataObject("otin");
		$do->rel_one_to_many('itotin', 'itotin', array('tipo_doc','numero'));
		$do->pointer('scli' ,'scli.cliente=otin.cod_cli','tipo AS sclitipo','left');
		//$do->rel_pointer('itspre','sinv','itspre.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');
//		print('<pre>');
//		print_R($do);
		
		$edit = new DataDetails('Otros Ingresos', $do);
		$edit->back_url = site_url('ventas/ootin/index');
		$edit->set_rel_title('itotin','Producto <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

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
		
		$edit->vence = new DateonlyField('Fecha Vence', 'vence','d/m/Y');
		$edit->vence->insertValue = date('Y-m-d');
		$edit->vence->rule = 'required';
		$edit->vence->mode = 'autohide';
		$edit->vence->size = 10;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');
		
		$edit->orden  = new inputField("Orden","orden");
		$edit->orden->size = 20;
		
		$edit->tipo_doc = new dropdownField("Tipo", "tipo_doc");  
		$edit->tipo_doc->option("FC","FC");  
		$edit->tipo_doc->option("ND","ND");
		$edit->tipo_doc->option("OT","OT");
		$edit->tipo_doc->size = 20;  
	  	$edit->tipo_doc->style='width:70px;';
		
    	$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->maxlength=5;
		$edit->cliente->append($boton);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';

		$edit->rifci   = new inputField('RIF/CI','rifci');
		$edit->rifci->autocomplete=false;
		$edit->rifci->size = 15;   
		
		$edit->direc = new inputField("Direcci&oacute;n","direc");
		$edit->direc->size = 55;  
		
		$edit->dire1 = new inputField(" ","dire1");
		$edit->dire1->size = 55;
		
		$edit->observa = new inputField("Observaciones"  , "observa1");
		$edit->observa->size = 40; 
		
		$edit->observa1 = new inputField("Observaciones"  , "observa2");
		$edit->observa1->size = 40;  	 		  
		
		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itotin';
		$edit->codigo->rule     = 'required';
		$edit->codigo->append($btn);

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>', 'descrip_<#i#>');
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->readonly  = true;
		$edit->descrip->rel_id='itotin';

		$edit->precio = new inputField('Precio <#o#>', 'precio_<#i#>');
		$edit->precio->db_name   = 'precio';
		$edit->precio->css_class = 'inputnum';
		$edit->precio->rel_id    = 'itotin';
		$edit->precio->size      = 10;
		$edit->precio->rule      = 'required|positive';
		$edit->precio->readonly  = true;
		
		$edit->impuesto = new inputField('Impuesto <#o#>', 'impuesto_<#i#>');
		$edit->impuesto->db_name   = 'impuesto';
		$edit->impuesto->css_class = 'inputnum';
		$edit->impuesto->rel_id    = 'itotin';
		$edit->impuesto->size      = 10;
		$edit->impuesto->rule      = 'required|positive';
		$edit->impuesto->readonly  = true;

		$edit->importe = new inputField('Importe <#o#>', 'importe_<#i#>');
		$edit->importe->db_name='importe';
		$edit->importe->size=10;
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   ='itotin';
		
		///fin campo detalles/////
		
		$edit->iva = new inputField('Impuesto', 'iva');
		$edit->iva->css_class ='inputnum';
		$edit->iva->readonly  =true;
		$edit->iva->size      = 10;

		$edit->totals = new inputField('Sub-Total', 'totals');
		$edit->totals->css_class ='inputnum';
		$edit->totals->readonly  =true;
		$edit->totals->size      = 10;

		$edit->totalg = new inputField('Monto Total', 'totalg');
		$edit->totalg->css_class ='inputnum';
		$edit->totalg->readonly  =true;
		$edit->totalg->size      = 10;    
		
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');
		$edit->build();


		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_otin', $conten,true);
		$data['title']   = heading('Otros ingresos');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}
	
	function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('nspre');
		$transac=$this->datasis->fprox_numero('ntransa');
		$usuario=$do->get('usuario');
		$estampa=date('Ymd');
		$hora   =date("H:i:s");

		$iva=$totals=$totalg=0;
		$cana=$do->count_rel('itotin');
		for($i=0;$i<$cana;$i++){
			$precio   = $do->get_rel('itotin','precio',$i);
			$impuesto     = $do->get_rel('itotin','impuesto',$i);
			$importe = $precio+$impuesto;
			$do->set_rel('itotin','importe' ,$importe,$i);
			$do->set_rel('itotin','estampa',$estampa  ,$i);
			$do->set_rel('itotin','usuario',$usuario  ,$i);
			$do->set_rel('itotin','hora'   ,$hora     ,$i);
			$do->set_rel('itotin','transac',$transac  ,$i);

			$iva    +=$impuesto;
			$totals +=$precio;
			$totalg +=$importe;
		}
		
		$do->set('totals' ,round($totals ,2));
		$do->set('totalg' ,round($totalg ,2));
		$do->set('iva'    ,round($iva    ,2));
		$do->set('numero',$numero);
		$do->set('estampa',$estampa);
		$do->set('hora'   ,$hora);
		$do->set('numero' ,$numero);
		$do->set('transac',$transac);

		return true;
	}

	function _pre_update($do){
		$iva=$totals=$totalg=0;
		$cana=$do->count_rel('itotin');
		for($i=0;$i<$cana;$i++){
			$precio   = $do->get_rel('itotin','precio',$i);
			$impuesto     = $do->get_rel('itotin','impuesto',$i);
			$importe = $precio+$impuesto;

			$iva    +=$impuesto;
			$totals +=$precio;
			$totalg +=$importe;
		}
		
		$do->set('totals' ,round($totals ,2));
		$do->set('totalg' ,round($totalg ,2));
		$do->set('iva'    ,round($iva    ,2));

		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('otin',"Otro Gasto $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('otin',"Otro Gasto $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('otin',"Otro Gasto $codigo ELIMINADO");
	}
}
?>


