<?php
class Otal extends Controller {
	
	function otal(){
		parent::Controller();
		$this->load->library("rapyd");
	}
	
	function index() {		
		$this->rapyd->load("datagrid","datafilter");
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
		
		$filter = new DataFilter("Filtro de Contrato de Nomina",'otal');
		
		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechad->insertValue = date('Y-m-d');
		$filter->fechah->insertValue = date('Y-m-d');
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 30;

		$filter->cliente = new inputField('Cliente', 'cod_cli');
		$filter->cliente->size = 30;
		$filter->cliente->append($boton);
		
		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');
    
		$uri = anchor('ventas/otal/dataedit/show/<#numero#>','<#numero#>');
		$uri_2  = anchor('ventas/otal/dataedit/modify/<#numero#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));
    
		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."ventas/otal/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";
		
		$grid = new DataGrid($mtool);
		$grid->order_by("numero","asc");
		$grid->per_page = 30;
		
		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("N&uacute;mero",$uri,'numero');
		$grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha');
		$grid->column_orderby("Tipo","tipo",'tipo');
		$grid->column_orderby("C.Cliente","cod_cli",'cod_cli');
		$grid->column_orderby("Nombre","nombre",'nombre');
		$grid->column_orderby("Factura","factura",'factura');
		$grid->column_orderby("C&oacute;digo","codigo",'codigo');
		$grid->column_orderby("Descripcio&oacute;n","descrip",'descrip');
		$grid->column_orderby("Ofrecido","<dbdate_to_human><#ofrecido#></dbdate_to_human>",'ofrecido');
		
		//$grid->add("nomina/noco/dataedit/create");
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

		$data['title']  = heading('Orden de taller');
		$data['script'] = script('jquery.js');
		$data["script"].= script('superTables.js');
		$data['head']   = $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
	}
	
	function dataedit(){
 		$this->rapyd->load("dataedit");
						
		$edit = new DataEdit("Orden de taller", "otal");
		$edit->back_url = site_url("ventas/otal/index");

		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
					  
		
		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre'),
		'titulo'  =>'Buscar Clientes');
		
		$cboton=$this->datasis->modbus($scli);
		
		$sfac=array(
		'tabla'   =>'sfac',
		'columnas'=>array(
			'numero' =>'N&uacute;mero de Factura',
			'cod_cli'=>'Codigo Cliente',
			'nombre'  =>'Nombre',
			'rifci'=>'Rif',
			'tipo_doc'=>'Tipo'),
		'filtro'  =>array('numero'=>'N&uacute;mero de factura','cod_cli'=>'C&oacute;digo de cliente'),
		'retornar'=>array('numero'=>'factura','cod_cli'=>'cod_cli','nombre'=>'nombre'),
		'where'=>array('tipo_doc'=>'F'),
		'titulo'  =>'Buscar Factura');
		
		$boton=$this->datasis->modbus($sfac);
		
		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigo',
				'descrip'=>'descrip',
				),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
		);
		$btn=$this->datasis->modbus($modbus);
		
		/*
		$edit->numero =  new inputField("N&uacute;mero", "numero");
		$edit->numero->rule="trim|required|callback_chexiste";
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=15;
		$edit->numero->size=16;
		*/
		
		$edit->fecha = new DateOnlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->size = 12; 
		$edit->fecha->rule="trim|chfecha";
		
		$edit->factura =  new inputField("Factura", "factura");
		$edit->factura->size = 12;
		$edit->factura->maxlength= 8;
		$edit->factura->rule="trim|required";
		$edit->factura->append($boton);
		$edit->factura->readonly=TRUE;
		
		$edit->cod_cli =  new inputField("Cliente", "cod_cli");
		$edit->cod_cli->size = 12;
		$edit->cod_cli->maxlength= 8;
		$edit->cod_cli->rule="trim|required";
		$edit->cod_cli->readonly=TRUE;
		
		$edit->nombre =  new inputField("Nombre", "nombre");
		$edit->nombre->size = 25;
		$edit->nombre->maxlength= 30;
		$edit->nombre->rule="trim|required";
		$edit->nombre->in="cod_cli";
		$edit->nombre->readonly=TRUE;
		 
		$edit->codigo =  new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size = 12;
		$edit->codigo->maxlength= 8;
		$edit->codigo->rule="trim|required";
		$edit->codigo->append($btn);
		$edit->codigo->readonly=TRUE;
		
		$edit->descrip = new textareaField("Descripci&oacute;n", "descrip");
		$edit->descrip->rule = "trim";
		$edit->descrip->cols = 70;
		$edit->descrip->rows =3;
		$edit->descrip->readonly=TRUE;
		 
		$edit->tipo = new dropdownField("Tipo","tipo");
		$edit->tipo->option("","");
		$edit->tipo->options(array("G"=> "Garantia","R"=>"Reparaci&oacute;n"));
		$edit->tipo->style = "width:100px;";
		
		$edit->ofrecido = new DateField("Ofrecido", "ofrecido","d/m/Y");
		$edit->ofrecido->size = 12;
		$edit->ofrecido->rule="trim|chfecha";
		    
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		
		
		$data['content'] = $edit->output;
		//$data['content'] = $edit->output; 
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Orden de Taller</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('otal',"Orden de taller $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('otal',"Orden de taller $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('otal',"Orden de taller $codigo ELIMINADO");
	}
	function instala(){
		$sql="CREATE TABLE `otal` (
			`numero` INT(10) NULL DEFAULT NULL,
			`fecha` DATE NULL DEFAULT NULL,
			`cod_cli` CHAR(5) NULL DEFAULT NULL,
			`nombre` VARCHAR(50) NULL DEFAULT NULL,
			`tipo` CHAR(1) NULL DEFAULT NULL,
			`factura` CHAR(8) NULL DEFAULT NULL,
			`codigo` CHAR(15) NULL DEFAULT NULL,
			`descrip` VARCHAR(50) NULL DEFAULT NULL,
			`falla` TEXT NULL,
			`ofrecido` DATE NULL DEFAULT NULL
			)
			COMMENT='Orden de Taller'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT
		";
		$this->db->query($sql);	
		$sql="ALTER TABLE `otal` CHANGE COLUMN `numero` `numero` INT(10) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`numero`)";
		$this->db->query($sql);	
		
	}
}
?>