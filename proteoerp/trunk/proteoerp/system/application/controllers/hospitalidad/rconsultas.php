<?php
class rconsultas extends Controller {
	function rconsultas(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		//redirect("inventario/rconsultas/filteredgrid");
	}

	function precios(){

		$barras = array(
			'name'      => 'barras',
			'id'        => 'barras',
			'value'     => '',
			'maxlength' => '15',
			'size'      => '16',
			//'style'     => 'display:none;',
		);

		$out  = form_open('inventario/rconsultas/precios');
		$out .= form_input($barras);//form_submit('mysubmit', 'Consultar!');
		$out .= form_close();

		$link=site_url('inventario/rconsultas/rprecios');

		$data['script']= <<<script
		<script type="text/javascript">
		$(document).ready(function(){
			$("#resp").hide();
			$("#barras").attr("value", "");
			$("#barras").focus();
			$("form").submit(function() {
				mostrar();
				return false;
			});

		});

		function mostrar(){
			$("#resp").hide();
			var url = "$link";
			$.ajax({
				type: "POST",
				url: url,
				data: $("input").serialize(),
				success: function(msg){ 
					$("#resp").html(msg).fadeIn("slow");
					$("#barras").attr("value", "");
					$("#barras").focus();
				}
			});
		}
		</script>
script;
		$data['content'] = '<div id="resp" style=" width: 100%; height: 300px" >&nbsp;</div>';
		$data['title']   = "<h1><center>$out</center></h1>";
		$data["head"]    = script("jquery-1.2.6.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);
	}

	function rprecios($descri1=NULL){
		if(empty($descri1)){
			$descri1=$this->input->post('descri1');
			if ($descri1===false){
				echo 'Debe introducir un c&oacute;digo de barras';
				return 0;
			}
		}
		$mSQL_p='SELECT codigo,precio, barras,descri1 FROM menu';
		$mSQL  =$mSQL_p." WHERE descri1='$descri1'";
		$query = $this->db->query($mSQL);
		$query = $this->db->query($mSQL);
			if ($query->num_rows()== 0){
				echo 'Producto no registrado';
				return 0;
			}
		$row = $query->row();
		$data['precio']  = number_format($row->precio,2,',','.');
		$data['descrip'] = $row->descri1;
		$data['codigo']  = $row->codigo;
		$data['barras']  = $row->barras;
		$data['moneda']  = 'Bs.F.';
		$this->load->view('view_rprecios', $data);
		return 1;
	}
}
?>