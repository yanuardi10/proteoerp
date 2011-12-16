<?php 
class dine extends Controller{
		function dine(){
			parent::Controller();
			$this->load->library("rapyd");
			//$this->datasis->modulo_id(103,1);
		}
		function index(){
			redirect("ventas/dine/filteredgrid");	
		}
		function filteredgrid(){
			$this->rapyd->load("datafilter","datagrid");
			
			$filter = new DataFilter("Filtro de Arqueo de Caja",'dine');
					  
		  $filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		  $filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		  $filter->fechad->db_name =$filter->fechah->db_name="fecha";
		  $filter->fechad->clause=$filter->fechah->clause="where";
		  $filter->fechah->size=$filter->fechad->size=12;		  
		  $filter->fechad->operator=">="; 
		  $filter->fechah->operator="<=";
		  $filter->fechah->group=$filter->fechad->group="Fecha";
		  
		  $filter->numero= new inputField("Numero","numero");
			$filter->numero->size=13;
			$filter->numero->maxlength=8;
			
			$filter->cajero = new dropdownField("Cajero", "cajero");
			$filter->cajero->option("","");
		  $filter->cajero->options("SELECT cajero,cajero as valor FROM dine GROUP BY cajero ORDER BY cajero");	
		  $filter->cajero->style='width:100px;';
		  
		  $filter->caja = new dropdownField("Caja", "caja");
		  $filter->caja->option("","");
		  $filter->caja->options("SELECT caja, caja as valor FROM dine GROUP BY caja ORDER BY caja");
		  $filter->caja->style='width:100px;';
		  
		  $filter->buttons("reset","search");
			$filter->build();
		  
		  $uri = anchor('ventas/dine/dataedit/show/<#numero#>','<#numero#>');
		  
		  $grid = new DataGrid("Lista de Arqueo de Caja");
			$grid->per_page=15;
			
			$grid->column("Numero",    $uri);
			$grid->column("Cajero",   "cajero");
			$grid->column("Caja",     "caja");
			$grid->column("Fecha",    "fecha");

			$grid->column("Monedas",     "<number_format><#monedas#>|2</number_format>",   "align=right");
			$grid->column("Recibido",    "<number_format><#recibido#>|2</number_format>",  "align=right");
			$grid->column("Computacion", "<number_format><#computa#>|2</number_format>",   "align=right");
			$grid->column("Diferencia",  "<number_format><#diferen#>|2</number_format>",   "align=right");
			
			//$grid->add("ventas/dine/dataedit/create");
			$grid->build();		  
		   
		  $data['content'] = $filter->output.$grid->output; 
		  $data['title']   = "<h1>Arqueo de Caja</h1>";        
      $data["head"]    = $this->rapyd->get_head();	    
      $this->load->view('view_ventanas', $data);
    }
    function dataedit(){
			$this->rapyd->load("dataedit","datadetalle","fields","datagrid");								
			
			$edit = new DataEdit("Arqueo de Caja","dine");
			$edit->back_url = site_url("ventas/dine/filteredgrid");			
			
  		$edit->post_process("delete","_borra_detalle");
  		$edit->post_process('delete','_post_delete');
					
			$edit->fecha = new DateonlyField("Fecha", "fecha");
			$edit->fecha->size = 12;
			
			$edit->numero = new inputField("N&uacute;mero", "numero");
			$edit->numero->size = 15;
			
			$edit->caja = new inputField("Caja", "caja");
			$edit->caja->size = 10;
			
			$edit->cajero = new inputField("Cajero", "cajero");
			$edit->cajero->size = 10;
			
			$edit->monedas = new inputField("Monedas","monedas");
			$edit->monedas->size =20;
			
			$edit->recibido = new inputField("Recibido","recibido");
			$edit->recibido->size = 20;
			
			$edit->computa = new inputField("Computador","computa");
			$edit->computa->size = 20;
			
			$edit->diferen = new inputField("Diferencia", "diferen");
			$edit->diferen->size = 20;
			
			$numero=$edit->_dataobject->get('numero');
			
			$detalle = new DataDetalle($edit->_status);
					
			$detalle->db->select('numero,tipo,referen,cantidad,denomi,compuca,compumo,total');
			$detalle->db->from('itdine');
			$detalle->db->where("numero='$numero'");
			
			$detalle->tipo = new inputField("Tipo", "tipo<#i#>");
			$detalle->tipo->size=7;
			$detalle->tipo->db_name='tipo';
			$detalle->tipo->readonly=TRUE;
			
			$detalle->referen = new inputField("Referencia", "referen<#i#>");
			$detalle->referen->size=30;
			$detalle->referen->db_name='referen';
			
			$detalle->cantidad = new inputField("Cantidad", "cantidad<#i#>");
			$detalle->cantidad->size=10;
			$detalle->cantidad->db_name='cantidad';
			
			$detalle->denomi = new inputField("Denominacion","denomi<#i#>");
			$detalle->denomi->size=20;
			$detalle->denomi->db_name='denomi';

			$detalle->compuca = new inputField("Cant. Computador","compuca<#i#>");
			$detalle->compuca->size=20;
			$detalle->compuca->db_name='compuca';
			
			$detalle->compumo = new inputField("Monto Computador","compumo<#i#>");
			$detalle->compumo->size=20;
			$detalle->compumo->db_name='compumo';
			
			$detalle->total = new inputField("Total","total<#i#>");
			$detalle->total->size=20;
			$detalle->total->db_name='total';			
		
			//fin de campos para detalle
			
			$detalle->onDelete('totalizar()');
			$detalle->onAdd('totalizar()');
			$detalle->style="width:110px";
			
			//Columnas del detalle
			$detalle->column("Tipo"             , "<#tipo#>");
			$detalle->column("Referencia"       , "<#referen#>");
			$detalle->column("Cantidad"         , "<#cantidad#>");
			$detalle->column("Denominacion"     , "<#denomi#>");
			$detalle->column("Cant. Computador" , "<#compuca#>");
			$detalle->column("Monto Computador" , "<#compumo#>");
			$detalle->column("Total"            , "<#total#>");
			
			$detalle->build();	
			$conten["detalle"] = $detalle->output;
			
			$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);
			
			$edit->buttons("delete", "back");
			$edit->build();
	
  		$conten["form"]  =&  $edit;
			$data['content'] = $this->load->view('view_dine', $conten,true);
			$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
			$data['title']   = '<h1>Arqueo de Caja</h1>';
			$this->load->view('view_ventanas', $data);
    }
    function _borra_detalle($do){
			$numero=$do->get('numero');
			$sql = "DELETE FROM itdine WHERE numero='$numero'";
	 		$this->db->query($sql);
	  }
	  function _post_delete($do){
				$codigo=$do->get('numero');				
				logusu('dine',"Arqueo de caja '$numero' ELIMINADO");
	  }
}
?>