<?php
class Usol extends Controller {
	function Usol(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
 
	function index(){
		redirect("inventario/usol/filteredgrid");
	}

	function filteredgrid(){
	
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de Unidad Solicitante", 'usol');
		
		$filter->codigo = new inputField("C&oacute;digo", "codigo");	
		$filter->codigo->size=20;		
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=20;

		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');
		
		$uri = anchor('inventario/usol/dataedit/show/<#codigo#>','<#codigo#>');
		$uri_2  = anchor('inventario/usol/dataedit/show/<#codigo#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));
	
		$grid = new DataGrid("Lista de Conceptos");
		$grid->order_by("codigo","asc");
		$grid->per_page = 50;

		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("C&oacute;digo",$uri,'codigo');
		$grid->column_orderby("Nombre","nombre",'nombre');
		$grid->column_orderby("Gasto" ,"gasto",'gasto');
		$grid->column_orderby("Departamento","depto",'depto');
		$grid->column_orderby("Sucursal", "sucursal",'sucursal');
		
		$grid->add("inventario/usol/dataedit/create");
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
    width: 540px; /* Required to set */
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

		$data['title']  = heading('Unidad Solicitante');
		$data['head']   = script('jquery.js');
		$data["head"]  .= script('superTables.js');
		$data['head']  .= $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Unidad Solicitante", "usol");
		
		$edit->back_url = site_url("inventario/usol/filteredgrid");
				
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->rule 		= "trim|required";		
		$edit->codigo->size 		= 10;
		$edit->codigo->maxlength	= 6;
			
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->rule 		= "trim|required";
		$edit->nombre->size 		= 30;
		$edit->nombre->maxlength 	= 30;
		
		$edit->depto = new  dropdownField ('Departamento', 'depto');
		$edit->depto->option('','');
		$edit->depto->options('select depto,concat(depto,"-",descrip)as descrip from dpto where tipo="G"');
		$edit->depto->style='width:170px;';
		$edit->depto->rule= "trim|required";
		
		$edit->sucu = new  dropdownField ('Sucursal', 'sucursal');
		$edit->sucu->option('','');
		$edit->sucu->options('select codigo,concat(codigo,"-",sucursal)as sucu from sucu');
		$edit->sucu->style='width:140px;';
		
		
		$edit->gasto = new inputField("Gastos", "gasto");
		$edit->gasto->size 			= 10;
		$edit->gasto->maxlength 	= 6;
		$edit->gasto->rule 			= "trim";
		
		
		$edit->buttons("modify", "save", "undo", "back");		
		$edit->build();
				
		$conten['form']  =&  $edit;
		$data['content'] = $edit->output;           
    	$data['title']   = "<h1>Unidad Solicitante</h1>";        
    	$data["head"]    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
    	$this->load->view('view_ventanas', $data);  
    }
}
?>