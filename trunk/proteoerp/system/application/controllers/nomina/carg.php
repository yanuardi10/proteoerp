<?php
//cargos
class Carg extends Controller {
	
	function carg(){
		parent::Controller(); 
		$this->load->library("rapyd");
  }

   function index(){
  	$this->datasis->modulo_id(701,1);
  	redirect("nomina/carg/filteredgrid");
  }

	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter2("Filtro por Cargo",'carg');
		
		$filter->cargo   = new inputField("C&oacute;digo", "cargo");
		$filter->cargo->size=3;
		$filter->cargo->clause = "likerigth";
		
		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->cargo->clause = "likerigth";
		
		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');
 
		$uri = anchor('nomina/carg/dataedit/show/<#cargo#>','<#cargo#>');
		$uri_2  = anchor('nomina/carg/dataedit/modify/<#cargo#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));
		
		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."nomina/carg/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";

		$grid = new DataGrid($mtool);
		$grid->order_by("cargo","asc");
		$grid->per_page = 50;
		
		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("Cargo",$uri,'cargo');
		$grid->column_orderby("Descripci&oacute;n","descrip","descrip");
		$grid->column_orderby("Sueldo"               ,"<number_format><#sueldo#>|2|,|.</number_format>",'sueldo',"align='right'");
		
		//$grid->add("nomina/carg/dataedit/create");
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
    width: 290px; /* Required to set */
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
		
		$data['title']  = heading('Cargos');
		$data['script'] = script('jquery.js');
		$data["script"].= script('superTables.js');
		$data['head']   = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	

	}
		function dataedit(){
 		$this->rapyd->load("dataedit");
  	
  	$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
  	
		$edit = new DataEdit("Cargos","carg");
		$edit->back_url = "nomina/carg/filteredgrid";
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->cargo = new inputField("Cargo", "cargo");
		$edit->cargo->rule= "required|callback_chexiste";
		$edit->cargo->mode="autohide";
		$edit->cargo->maxlength=8;
		$edit->cargo->size=10;
		
		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size=40;
		$edit->descrip->maxlength=30;
		$edit->descrip->rule= "strtoupper|required";
		
		$edit->sueldo  = new inputField("Sueldo", "sueldo");
		$edit->sueldo->size=20;
		$edit->sueldo->rule= "required|callback_positivo";
		$edit->sueldo->css_class='inputnum';
		
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Cargos</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	
	function _pre_del($do) {
		$codigo=$do->get('cargo');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE cargo='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Cliente con Movimiento no puede ser Borrado';
			return False;
		}
		return True;
	}
	function _post_insert($do){
		$codigo=$do->get('cargo');
		$nombre=$do->get('descrip');
		logusu('carg',"CARGO $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('cargo');
		$nombre=$do->get('descrip');
		logusu('carg',"CARGO $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('cargo');
		$nombre=$do->get('descrip');
		logusu('carg',"CARGO $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('cargo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM carg WHERE cargo='$codigo'");
		if ($chek > 0){
			$cargo=$this->datasis->dameval("SELECT descrip FROM carg WHERE cargo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el cargo $cargo");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	function instalar(){
		$mSQL="ALTER TABLE carg ADD PRIMARY KEY (cargo);";
		$this->db->simple_query($mSQL);	
	}
	
	function positivo($valor){
		if ($valor <= 0){
			$this->validation->set_message('positivo',"El campo Sueldo debe ser positivo");
			return FALSE;
		}
  	return TRUE;
	}
}
?>