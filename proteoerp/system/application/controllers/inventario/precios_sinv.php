<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
class precios_sinv extends validaciones{

	function precios_sinv(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->library("pi18n");
		//$this->datasis->modulo_id(302,1);
		//define ("THISFILE",   APPPATH."controllers/inventario/". $this->uri->segment(2).EXT);
	}

	function index(){
		redirect("inventario/precios_sinv/precios");
	}


	function precios(){
		$this->rapyd->load("dataedit","dataform");
		$script='
		<script language="javascript" type="text/javascript">
		$(function(){
				$(".inputnum").numeric(".");
		});
		</script>';

		$mSINV=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;odigo',
				'descrip'=>'Descripci&oacute;n',
		),
			'filtro'  =>array('codigo'=>'C&oacute;digo','barras'=>'Barras','alterno'=>'Alterno','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar Producto');
		$bSINV=$this->datasis->modbus($mSINV);

		$pr='
			$(document).ready(function(){
				$(".button").click(function(){
						
						if($("#codigo").val() == "" ){
							alert("Debe ingresar un codigo de producto");
							return false;
						}
					}
					);
			});
			
		';

		$form = new DataForm("inventario/precios_sinv/precios/modifica");
		$form->script($pr);
		$form->codigo = new inputField2("Consultar Productos", "codigo");
		$form->codigo->size = 15;
		$form->codigo->maxlength=15;
		$form->codigo->append($bSINV);
		$form->codigo->rule = 'trim';

		$form->submit("btnsubmit","CONSULTAR");
		$form->build_form();
		$band=0;
		$msj="";
		if ($form->on_success()){
			set_time_limit(600);
			$codigo= $this->input->post("codigo");

			$resul1=$this->db->query("SELECT count(*) as cant,id from sinv	where codigo='$codigo' ");
			$row1=$resul1->row();
			if($row1->cant > 0){
				$band=1;
				redirect("inventario/precios_sinv/dataedit/modify/$row1->id");
			}
			if($band == 0){
				$resul1=$this->db->query("SELECT count(*) as cant,id from sinv	where barras='$codigo' ");
				$row1=$resul1->row();
				if($row1->cant > 0){
					$band=1;
					redirect("inventario/precios_sinv/dataedit/modify/$row1->id");
				}
			}
			if($band == 0){
				$resul1=$this->db->query("SELECT count(*) as cant,id from sinv	where alterno='$codigo' ");
				$row1=$resul1->row();
				if($row1->cant > 0){
					$band=1;
					redirect("inventario/precios_sinv/dataedit/modify/$row1->id");
				}
			}
			if($band==0){
				$resul1=$this->db->query("SELECT count(*) as cant,codigo from barraspos	where suplemen='$codigo' ");
				$row1=$resul1->row();
				if($row1->cant > 0){
					$resul2=$this->db->query("SELECT count(*) as cant,id from sinv	where codigo='$row1->codigo' ");
					$row2=$resul2->row();
					if($row2->cant > 0){
						redirect("inventario/precios_sinv/dataedit/modify/$row2->id");
					}
				}
			}
			if ($band==0){
				$msj.= "NO se encontro el producto  <br>";
				$atras=site_url('inventario/precios_sinv/precios');
				$data['smenu']="<a href=".$atras.">ATRAS</a>";
			}
		}
			



		$data['content'] =$form->output.$msj;
		$data['head']    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().$script;

		$data['title']   = '<h1>Cambiar Precios</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){

		$this->rapyd->load("dataedit");

		$edit = new DataEdit('Cambios de precios',"sinv");
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('update','_modifica');

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

		for($i=1;$i<5;$i++){
			$obj='precio'.$i;
			$edit->$obj = new inputField('Precio '.$i, $obj);
			$edit->$obj->css_class='inputnum';
			$edit->$obj->rule ='numeric|trim|required';
			$edit->$obj->size = 10;
			$edit->$obj->maxlength=10;
		}

		$edit->buttons('modify','save');
		$edit->build();

		$atras=site_url('inventario/precios_sinv/precios');
		$data['smenu']="<a href=".$atras.">ATRAS</a>";
		$data['content'] =$edit->output;
		$data['head']    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().$script;

		$data['title']   = '<h1>Cambiar Precio</h1>';
		$this->load->view('view_ventanas', $data);


	}

	function _modifica($do){

		$codigo=$do->get('codigo');
		$precio1=$do->get('precio1');
		$precio2=$do->get('precio2');
		$precio3=$do->get('precio3');
		$precio4=$do->get('precio4');

		//base=precio*100/(100+iva)
		//margen=100-(costo*100/base)
		//		$query1="UPDATE sinv SET
		//				precio1=$precio1,
		//				precio2=$precio2,
		//				precio3=$precio3,
		//				precio4=$precio4
		//        WHERE $campo='$codigo' ";
		//		$this->db->query($query1);

		$query2="UPDATE sinv SET
				base1=precio1*100/(100+iva),
                base2=precio2*100/(100+iva),
                base3=precio3*100/(100+iva),
                base4=precio4*100/(100+iva)
                WHERE codigo='$codigo'
                ";
		$this->db->query($query2);

		$query3="UPDATE sinv SET
                margen1=100-(pond*100/base1),
                margen2=100-(pond*100/base2),
                margen3=100-(pond*100/base3),
                margen4=100-(pond*100/base4)
                WHERE codigo='$codigo'
                ";
		$this->db->query($query3);

	}

	function _pre_update($do){
		//		print("<pre>");
		//		print_R($do);

		$p1=$do->get('precio1');
		$p2=$do->get('precio2');
		$p3=$do->get('precio3');
		$p4=$do->get('precio4');
		$do->error_message_ar['pre_upd'] = 'Los precios deben cumplir con:<br> Precio 1 > Precio 2 > Precio 3 > Precio 4';
		if($p1>$p2 && $p2>$p3 && $p3>$p4){

			return true;
		}else{

			return false;
		}
	}
}
?>

