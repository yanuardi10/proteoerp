<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
class precios_sinv extends validaciones {

	function precios_sinv(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(302,1);
		//define ("THISFILE",   APPPATH."controllers/inventario/". $this->uri->segment(2).EXT);
	}

	function index(){
		redirect("inventario/precios_sinv/precios");
	}


	function precios(){
		$this->rapyd->load("dataedit","dataform");


		$form = new DataForm("inventario/precios_sinv/precios/modifica");

		$form->codigo = new inputField2("Ingrese producto a consultar", "codigo");
		$form->codigo->size = 15;
		$form->codigo->maxlength=15;
		$form->codigo->rule = 'required';

		$form->submit("btnsubmit","CONSULTAR");
		$form->build_form();
		if ($form->on_success()){
			$codigo=trim($_POST['codigo']);
			redirect("inventario/precios_sinv/verifica/$codigo");
		}

		$data['content'] =$form->output;
		$data['head']    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head();

		$data['title']   = '<h1>Cambiar Precios</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function campo($cod=""){
		$resul1=$this->db->query("SELECT count(*) as cant from sinv
								where codigo='$cod' ");
		$row1=$resul1->row();
		if($row1->cant > 0) return 1;

		$resul2=$this->db->query("SELECT count(*) as cant from sinv
								where barras='$cod'" );
		$row2=$resul2->row();
		if($row2->cant > 0 ) return 2;

		$resul3=$this->db->query("SELECT count(*) as cant from sinv
								where alterno='$cod'" );
		$row3=$resul3->row();
		if($row3->cant > 0 ) return 3;

		return 0;


	}

	function verifica($cod=''){
		//		$this->rapyd->load("dataedit","dataform");
		$script='
                <script language="javascript" type="text/javascript">
                $(function(){
                        $(".inputnum").numeric(".");
                });
                function valida() {
                	v1=$("#precio1").val();
                	v2=$("#precio2").val();
                	v3=$("#precio3").val();
                	v4=$("#precio4").val();
				  	if (v1 != "" && v2 != "" && v3 != "" && v4 != "") {
					    return true
					} else {
						alert("Debe ingresar todos los campos");
				    	return false
				  	}
				} 
                </script>
                ';
		$out="";
		$camp=$this->campo($cod);
		$js = 'onClick="return valida()"';
		if($camp > 0){
			$precio1 = array('name'      => 'precio1','id'        => 'precio1',
			'value'     => '','size'      => '16','class'=>'inputnum',
			);
			$precio2 = array('name'      => 'precio2','id'        => 'precio2',
			'value'     => '','size'      => '16','class'=>'inputnum',
			);
			$precio3 = array('name'      => 'precio3','id'        => 'precio3',
			'value'     => '','size'      => '16','class'=>'inputnum',
			);
			$precio4 = array('name'      => 'precio4','id'        => 'precio4',
			'value'     => '','size'      => '16','class'=>'inputnum',
			);
			$out  = '<h1>'.form_open('inventario/precios_sinv/modifica');
			$out .= "Introduzca Nuevos Precio:<br> </h1>";
			$out .= "Precio1   ".form_input($precio1)."<br>";
			$out .= "Precio2   ".form_input($precio2)."<br>";
			$out .= "Precio3   ".form_input($precio3)."<br>";
			$out .= "Precio4   ".form_input($precio4)."<br>";
			$out .= form_hidden('tabla', 'sinv');
			$out .= form_hidden('campo', $camp);
			$out .= form_hidden('codigo', $cod);
			$out .= form_submit('btnsubmit','Modificar',$js).form_close().'</h1>';

		}else{
			$resul2=$this->db->query("SELECT count(*) as cant,codigo from sinvpromo where codigo='$cod'");
			$row2=$resul2->row();
			if($row2->cant > 0){
				$precio1 = array('name'      => 'precio1','id'        => 'precio1',
			'value'     => '','size'      => '16',
				);
				$precio2 = array('name'      => 'precio2','id'        => 'precio2',
			'value'     => '','size'      => '16',
				);
				$precio3 = array('name'      => 'precio3','id'        => 'precio3',
			'value'     => '','size'      => '16',
				);
				$precio4 = array('name'      => 'precio4','id'        => 'precio4',
			'value'     => '','size'      => '16',
				);
				$out  = '<h1>'.form_open('inventario/precios_sinv/modifica');
				$out .= "Introduzca Nuevos Precio:<br></h1> ";
				$out .= "Precio1   ".form_input($precio1)."<br>";
				$out .= "Precio2   ".form_input($precio2)."<br>";
				$out .= "Precio3   ".form_input($precio3)."<br>";
				$out .= "Precio4   ".form_input($precio4)."<br>";
				$out .= form_hidden('tabla', 'sinvpromo');
				$out .= form_hidden('campo', '1');
				$out .= form_hidden('codigo', $cod);
				$out .= form_submit('btnsubmit','Modificar').form_close().'</h1>';

			}else{
				$out .= "NO se encontro el producto  $cod<br>";
				$atras=site_url('inventario/precios_sinv/precios');
				$data['smenu']="<a href=".$atras.">ATRAS</a>";
			}
		}
		$data['content'] =$out;
		$data['head']    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().$script;

		$data['title']   = '<h1>Cambiar Precio</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function modifica(){

		$tabla= $this->input->post('tabla');
		$camp=$this->input->post('campo');
		$codigo=$this->input->post('codigo');
		$precio1=$this->input->post('precio1');
		$precio2=$this->input->post('precio2');
		$precio3=$this->input->post('precio3');
		$precio4=$this->input->post('precio4');
		
		$cod=$this->input->post('codigo');
		$campo="";
		switch ($camp){
			case 1:$campo ='codigo';
			break;
			case 2:$campo ='barras';
			break;
			case 3:$campo ='alterno';
			break;
		}
		//base=precio*100/(100+iva)
		//margen=100-(costo*100/base)
		$query1="UPDATE sinv SET 
				precio1=$precio1,
				precio2=$precio2,
				precio3=$precio3,
				precio4=$precio4
        WHERE $campo='$codigo' ";
		$this->db->query($query1);
		
		$query2="UPDATE sinv SET
				base1=precio1*100/(100+iva),
                base2=precio2*100/(100+iva),
                base3=precio3*100/(100+iva),
                base4=precio4*100/(100+iva)
                WHERE $campo='$codigo'
                ";
		$this->db->query($query2);
		
		$query3="UPDATE sinv SET
                margen1=100-(pond*100/base1),
                margen2=100-(pond*100/base2),
                margen3=100-(pond*100/base3),
                margen4=100-(pond*100/base4)
                WHERE $campo='$codigo'
                ";
		$this->db->query($query3);

		$atras=site_url('inventario/precios_sinv/precios');
		$data['content'] ="MODIFICADO PRECIOS";
		$data['smenu']="<a href=".$atras.">ATRAS</a>";
		$data['title']   = "<h1>Actualizaci&oacute;n de Inventario</h1>";
		$data["head"]    = script("jquery.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}
?>

