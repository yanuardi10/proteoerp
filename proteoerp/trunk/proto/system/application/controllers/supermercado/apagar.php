<?php
class Apagar extends Controller {
	var $dftp;
	
	function Apagar(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->dftp='/srv/ftp/caja<#numero#>/entrada/apagar.ctr';
	}

	function index(){
		$this->datasis->modulo_id('11E',1);
		$this->rapyd->load("fields","datatable");

		$table = new DataTable(null);
		$table->cell_attributes = 'style="vertical-align:middle;"';

		$table->db->select(array('caja','ubica'));
		$table->db->from("caja");
		$table->db->where("ubica REGEXP  '^([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\\.([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])){3}$' ");
		$table->db->orderby('caja');

		$table->per_row  = 5;
		$table->per_page = 15;

		//$table->cell_template = "<a href='".site_url('/supermercado/envivo/caja/<#ubica#>')."' target='vencaja' >". image('caja_abierta.gif',"Caja  <#caja#>", array('border'=>0,'align'=>'center')).'</a>'.'<br>Caja <#caja#>';
		$table->cell_template = image('caja_abierta.gif',"Caja  <#caja#>", array('border'=>0,'align'=>'center','id'=>'<#caja#>')).'<br>Caja <#caja#>';
		$table->build();

		$link=site_url('supermercado/apagar/capaga');
		$data['script'] = "
		<script type='text/javascript'>
			$(document).ready(function() {
				$('img').click(function () { 
					if (confirm('Seguro que desea apagar la caja '+this.id+'?')) { 
						$.ajax({
							url: '$link'+'/'+this.id,
							success: function(msg){
								alert(msg);
							}
						});
					}
				});

			});
		</script>
		";
		$data['content'] = '<center>'.$table->output.'</center>';
		$data['title']   = "<h1>Apagar las Cajas</h1>";
		$data["head"]    = $this->rapyd->get_head().script('jquery.js');
		$this->load->view('view_ventanas', $data);
	}

	function capaga($caja=NULL){
		$this->load->helper('file');
		$data  = 'archivo bandera para apagar las cajas';                                
		$nombre=str_replace('<#numero#>',$caja,$this->dftp);
		
		if (!write_file($nombre, $data)){
		     echo "Error dando la orden de apagado a la caja $caja";
		}else{
		     echo "Ya fue dada la orden de apagado a la caja $caja";
		}
	}
	

}
?>
