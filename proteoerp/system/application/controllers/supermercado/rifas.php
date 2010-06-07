<?php
class Cupones extends Controller {

	function Cupones(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		$this->rapyd->load('dataform','datagrid');
		$form = new DataForm("supermercado/cupones/index/process");

		$form->fecha = new dateonlyField("Fecha", "fecha",'d/m/Y');
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->size=11;
		$form->fecha->rule = "required";

		$form->monto= new inputField("Monto por cupon", "monto");
		$form->monto->size = 15;
		$form->monto->rule = "required"; 
  	$form->monto->insertValue = 100;
  	
		$form->caja = new dropdownField("Caja", "ipcaj");                                                                                                                             
		$form->caja->rule = "required";                                                                                                                                               
		$form->caja->options("SELECT caja AS value, caja FROM caja ORDER BY caja");

		$form->submit("reset","Resetear");  
		$form->submit("btnsubmit","Buscar");
   	$form->build_form();
		$ggri='';
		if ($form->on_success()){
			$query = $this->db->query("SELECT caja,cliente, TRUNCATE(SUM(gtotal)/{$form->monto->newValue},0) cupones FROM viefac WHERE fecha={$form->fecha->newValue} AND caja='{$form->caja->newValue}' group by  caja,cliente WITH ROLLUP HAVING cliente IS NULL");
			
			$arreglo=array();
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row)
					$arreglo[]=array('caja'=>$row->caja ,'cupones'=> $row->cupones);
			}
			$grid = new DataGrid("Lista de Cupones",$arreglo);
			$grid->per_page = count($arreglo);
			$grid->column("Caja"   ,'caja');
			$grid->column("Cupones","cupones");
			$grid->build();
			$ggri=$grid->output;
		}

		$data['content'] =  $form->output.$ggri;
		$data['title']   = "<h1>Tikes repartidos</h1>";
		$data['head']  = $this->rapyd->get_head().script("jquery-1.2.6.js");
		$this->load->view('view_ventanas', $data);
	}
	
	function cupon($monto,$fecha){
		$mSQL="SELECT caja,cliente, TRUNCATE(SUM(gtotal)/$monto,0) cupones FROM viefac WHERE fecha='$fecha' AND caja=1 group by  caja,cliente WITH ROLLUP HAVING cliente IS NULL";
		$query = $this->db->query($mSQL);
		$arreglo=array();
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$arreglo[]=array('caja'=>$row->caja ,'cliente'=> $row->cupones);
			}
		}
		
		$grid = new DataGrid("Lista de Cupones",$arreglo);
		//$grid->per_page = 10;
		$grid->column("Caja"   ,'caja');
		$grid->column("Cupones","cupones");
		$grid->build();
	}
	function ntikes($fecha='', $mficha=''){
		$fecha='20080812';
		$mficha='100';
		//SELECT caja,cliente, TRUNCATE(SUM(gtotal)/100,0) cupones FROM viefac WHERE fecha=20080812 AND caja=1 group by  caja,cliente WITH ROLLUP HAVING cliente IS NULL
		$query = $this->db->query("SELECT cliente,gtotal FROM viefac WHERE fecha=$fecha");
		if ($query->num_rows() > 0){
			$pivocli='';
			$tickes=$monto=0;
			foreach ($query->result() as $row){
				if ($pivocli!=$row->cliente){
						$pivocli=$row->cliente;
				}
				$monto+=$row->gtotal;
			}
		}
	}
}
?>