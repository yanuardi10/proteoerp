<?php
class concepto extends Controller {
	function concepto(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/inventario/". $this->uri->segment(2).EXT);
	}
 
	function index(){
		redirect("inventario/concepto/filteredgrid");
	}

	function filteredgrid(){
	
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de Conceptos", 'icon');
		
		$filter->codigo = new inputField("C&oacute;digo", "codigo");	
		$filter->codigo->size=20;		
		
		$filter->nombre = new inputField("C&oacute;ncepto", "concepto");
		$filter->nombre->size=20;

		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');
		
		$uri = anchor('inventario/concepto/dataedit/show/<#codigo#>','<#codigo#>');
		$uri_2  = anchor('inventario/concepto/dataedit/show/<#codigo#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));
	
		$grid = new DataGrid("Lista de Conceptos");
		$grid->order_by("codigo","asc");
		$grid->per_page = 50;

		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("C&oacute;digo",$uri,'codigo');
		$grid->column_orderby("Concepto ","concepto",'concepto');
		$grid->column_orderby("Gasto" ,"gasto",'gasto');
		$grid->column_orderby("Denominaci&oacute;n de Gasto","gastode",'gastode');
		$grid->column_orderby("Ingreso", "ingreso",'ingreso');
		$grid->column_orderby("Denominaci&oacute;n de Ingreso", "ingresod",'ingresod');
		
		$grid->add("inventario/concepto/dataedit/create");
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

		$data['title']  = heading('Otros Conceptos');
		$data['head']   = script('jquery.js');
		$data["head"]  .= script('superTables.js');
		$data['head']  .= $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
			
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$mMGAS=array(
		'tabla'   =>'mgas',
		'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'descrip'=>'Descripci&oacute;n', 
		),
		'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
		'retornar'=>array('codigo'=>'gasto','descrip'=>'gastode'),
		'titulo'  =>'Buscar Gasto',
		);
		$botonE =$this->datasis->modbus($mMGAS);
		
		$mBOTR=array(
		'tabla'   =>'botr',
		'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'nombre'=>'Nombre', 
		),
		'filtro'  =>array('codigo'=>'C&oacute;digo','nombre'=>'Nombre'),
		'retornar'=>array('codigo'=>'ingreso','nombre'=>'ingresod'),
		'titulo'  =>'Buscar Ingreso',
		);
		$botonI =$this->datasis->modbus($mBOTR);
		
		$edit = new DataEdit("Conceptos", "icon");
		
		$edit->back_url = site_url("inventario/concepto/filteredgrid");
				
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->rule 		= "trim|required";		
		$edit->codigo->size 		= 10;
		$edit->codigo->maxlength	= 6;
			
		$edit->concepto = new inputField("Conceptos", "concepto");
		$edit->concepto->rule 		= "trim|required";
		$edit->concepto->size 		= 30;
		$edit->concepto->maxlength 	= 30;
		
		$edit->tipo = new  dropdownField ('Tipo', 'tipo');
		$edit->tipo->option('','Elija tipo');
		$edit->tipo->option('I','Ingreso');
		$edit->tipo->option('E','Egreso');
		$edit->tipo->style='width:80px;';
		$edit->tipo->rule= "trim|required";
		
		$edit->gasto = new inputField("Gastos", "gasto");
		$edit->gasto->size 			= 10;
		$edit->gasto->maxlength 	= 6;
		$edit->gasto->rule 			= "trim";
		$edit->gasto->append($botonE);
		
		$edit->gastode = new inputField("Gasto denominaci&oacute;n", "gastode");		
		$edit->gastode->size 		= 30;
		$edit->gastode->maxlength 	= 30;
		$edit->gastode->rule 		= "trim";
		//$edit->gastode->readonly  =true;
		
		$edit->ingreso = new inputField("Ingreso", "ingreso");
		$edit->ingreso->size 		= 10;
		$edit->ingreso->maxlength 	= 5;
		$edit->ingreso->rule 		= "trim";
		$edit->ingreso->readonly  =true;
		$edit->ingreso->append($botonI);
		
		$edit->ingresod = new inputField("Ingreso denominaci&oacute;n", "ingresod"); 
		$edit->ingresod->size 		= 30;
		$edit->ingresod->maxlength 	= 30;
		$edit->ingresod->rule 		= "trim";
		//$edit->ingresod->readonly  =true;
		
		$edit->buttons("modify", "save", "undo", "back");		
		$edit->build();
				
		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_icon', $conten,true);           
    	$data['title']   = "<h1>Otros Conceptos</h1>";        
    	$data["head"]    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
    	$this->load->view('view_ventanas', $data);  
    }
}
?>