<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class aapan extends validaciones {
	
	function aapan(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id(505,1);
		redirect("finanzas/aapan/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por N&uacute;mero", 'apan');

		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size=12;
		$filter->numero->maxlength=8;

		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=12;
		$filter->nombre->maxlength=30;

		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->option("","");
		$filter->tipo->option("C","C");
		$filter->tipo->option("P","P");
		$filter->tipo->style="width:100px";

		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');

		$uri = anchor('finanzas/aapan/dataedit/show/<#id#>','<#numero#>');
		$uri_2 = anchor('finanzas/aapan/dataedit/show/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));

		$grid = new DataGrid("Lista de Aplicaci&oacute;n de Anticipos");
		$grid->order_by("numero","asc");
		$grid->per_page = 50;

		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("N&uacute;mero",$uri,'numero');
		$grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
		$grid->column_orderby("Tipo","tipo",'tipo');
		$grid->column_orderby("Clipro","clipro",'clipro');
		$grid->column_orderby("Nombre","nombre",'nombre');
		$grid->column_orderby("Observaci&oacute;n 1","observa1",'observa1');
		$grid->column_orderby("Observaci&oacute;n 2","observa2",'observa2');
		$grid->column("Monto","<nformat><#monto#>|2|,|.</nformat>" ,'align=right');
		$grid->column("Reinte","<nformat><#reinte#>|2|,|.</nformat>" ,'align=right');
			
		//$grid->add("finanzas/apan/dataedit/create");
		$grid->build('datagridST');
		
//********** SUPER TABLE ************* 
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

		$data['title']  = heading('Aplicaci&oacute;n de Anticipos');
		$data['head']   = script('jquery.js');
		$data["head"]  .= script('superTables.js');
		$data['head']  .= $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function dataedit()	{
		$this->rapyd->load('dataobject','datadetails');
		
		$do = new DataObject("apan");
		$do->rel_one_to_many('itppro', 'itppro', array('transac'));
		$do->rel_one_to_many('itccli', 'itccli', array('transac'));
		
		$edit = new DataDetails('Aplicaci&oacute;n de Anticipos', $do);
		$edit->back_url = site_url('finanzas/aapan/filteredgrid');
		$edit->set_rel_title('itppro','Anticipo <#o#>');
		$edit->set_rel_title('itccli','Anticipo <#o#>');
		
		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size =12;
		$edit->numero->rule="trim|required";
		$edit->numero->maxlength=8;

		$edit->fecha = new DateonlyField("Fecha", "fecha");
		$edit->fecha->size = 12;
		$edit->fecha->rule="required|chfecha";

		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("C","Cliente");
		$edit->tipo->option("C","Cliente");
		$edit->tipo->option("P","Proveedor");
		$edit->tipo->style="width:100px";
			
		$edit->clipro =new inputField("Codigo", "clipro");
		$edit->clipro->rule='trim|required';
		$edit->clipro->size =12;
		$edit->clipro->readonly=true;

		$edit->nombre =   new inputField("Nombre", "nombre");
		$edit->nombre->size =30;
		$edit->nombre->rule = "trim|strtoupper";
		$edit->nombre->readonly=true;

		$edit->monto =    new inputField("Monto", "monto");
		$edit->monto->size = 12;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric';
		$edit->monto->maxlengxlength=0;

		$edit->reinte =   new inputField("Convertido", "reinte");
		$edit->reinte->rule='trim|required';
		$edit->reinte->size =12;
		$edit->reinte->readonly=true;

		$edit->nombreintes=new inputField("Nombre","nombreintes");
		$edit->nombreintes->size=30;
		$edit->nombreintes->readonly=true;

		$edit->observa1 = new inputField("Observaciones", "observa1");
		$edit->observa1->rule='trim';
		$edit->observa1->size =50;
		$edit->observa1->maxlength=50;

		$edit->observa2 = new inputField("", "observa2");
		$edit->observa2->rule='trim';
		$edit->observa2->size =50;
		$edit->observa2->maxlength=50;
		
		//Detalles itppro
		
		$edit->tipoppro = new inputField("Tipo <#o#>","tipoppro_<#i#>");
		$edit->tipoppro->db_name = "tipoppro";
		$edit->tipoppro->rel_id  = 'itppro'; 
		$edit->tipoppro->rule='trim|required';
		$edit->tipoppro->size =10;
		$edit->tipoppro->readonly=true;
		
		$edit->tipo_doc = new inputField("Tipo Documento <#o#>","tipo_doc_<#i#>");
		$edit->tipo_doc->db_name = "tipo_doc";
		$edit->tipo_doc->rel_id  = 'itppro';
		$edit->tipo_doc->rule='trim|required';
		$edit->tipo_doc->size =10;
		$edit->tipo_doc->readonly=true;
		
		$edit->itnumero = new inputField("N&uacute;mero <#o#>","itnumero_<#i#>");
		$edit->itnumero->db_name = "numero";
		$edit->itnumero->rel_id  = 'itppro';
		$edit->itnumero->rule='trim|required';
		$edit->itnumero->size =10;
		$edit->itnumero->readonly=true;
		
		//Detalles itccli

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Aplicaci&oacute;n de Anticipos</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function instalar(){
		$sql="ALTER TABLE `apan`  ADD COLUMN `id` INT(10) NULL AUTO_INCREMENT AFTER `usuario`,  ADD PRIMARY KEY (`id`)";
		$this->db->query($sql);

	}
}
?>