<?php
class lfallasdeinventario extends Controller {

	function lfallasdeinventario() {
		parent::Controller();
	}
	function index() {
		$this->load->library('mtable');
		$this->load->view('table_def.inc');
		
		$obj = new mtable("Listado de Fallas de Inventario",8);
		$obj->AddPage();
		$obj->AddCol('codigo'  ,20,'C&oacute;digo', 'L',5);
		$obj->AddCol('grupo' ,10,'Grupo', 'L',6);
		$obj->AddCol('descrip'   ,50,'Descripci&oacute;n', 'L',6);		
	  $obj->AddCol('existen',15,'Existencia','R',6);
	  $obj->AddCol('exmin',15,'M&iacute;nima','R',6);
	  $obj->AddCol('falta',15,'Falta','R',6);
	  $obj->AddCol('exord',15,'Orden','R',6);
	  $obj->AddCol('exdes',15,'P Cliente','R',6);

		$obj->Table("SELECT codigo,grupo, descrip,existen,exmin,existen-exmin falta,exord,exdes FROM sinv order by codigo" );
		$obj->Output();
	}
}
?>