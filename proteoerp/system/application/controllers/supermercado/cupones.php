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
		$form->fecha->maxlength=11;
		$form->fecha->rule = "trim";
		
		
		/*$form->monto= new inputField("Monto por cupon", "monto");
		$form->monto->size = 15;
		$form->monto->rule = "required"; 
  		$form->monto->insertValue = $this->datasis->traevalor('FMAYCUPON');
		*/  	
  	
		$form->caja = new dropdownField("Caja", "ipcaj");                                                                                                                             
		$form->caja->rule = "required";                                                                                                                                               
		$form->caja->options("SELECT caja AS value, caja FROM caja ORDER BY caja");

		$form->submit("reset","Resetear");  
		$form->submit("btnsubmit","Buscar");
   		$form->build_form();
		$ggri='';
		
		if ($form->on_success()){
			$mSQL="SELECT caja,cliente, gtotal,numero FROM viefac WHERE fecha={$form->fecha->newValue} AND caja='{$form->caja->newValue}'";
			//echo $mSQL;
			$query = $this->db->query($mSQL);
			$cupones=0;
			$facturas=array();
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					if(array_key_exists($row->numero,$facturas))
					    $facturas[$row->numero]+=$row->gtotal;
					else
					    $facturas[$row->numero]=$row->gtotal;
					    
					/*$pivocli='';
					$monto=0;
					foreach ($query->result() as $row){
						if ($pivocli!=$row->cliente){
							$cupones+=floor($monto/$form->monto->newValue);
							$pivocli=$row->cliente;
							$monto=0;
						}
						$monto+=$row->gtotal;
						
					}*/
				}
			}
			$monto=$this->datasis->traevalor('FMAYCUPON');
			foreach($facturas AS $key=>$value)$cupones+=round($facturas[$key]/$monto,0);
			
			$ggri="Fueron entregados <b>$cupones</b> cupones";
		}
		$data['content'] =  $form->output.$ggri;
		$data['title']   = "<h1>Tikes repartidos</h1>";
	  $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();	
		$this->load->view('view_ventanas', $data);
	}
}
?>