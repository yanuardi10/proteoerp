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
			'codigo' =>'C&oacute;odigo','barras'=>'Barras','alterno'=>'Alterno',
			'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','barras'=>'Barras','alterno'=>'Alterno','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo','barras'=>'barras','alterno'=>'alterno'),
			'titulo'  =>'Buscar Producto');
		$bSINV=$this->datasis->modbus($mSINV);


		$mSINVP=array(
			'tabla'   =>'sinvpromo',
			'columnas'=>array(
			'codigo' =>'C&oacute;odigo','margen'=>'Margen','cantidad'=>'Cantidad'),
			'filtro'  =>array('codigo'=>'C&oacute;digo'),
			'retornar'=>array('codigo'=>'promo'),
			'titulo'  =>'Buscar Codigo Promocion');
		$bSINVP=$this->datasis->modbus($mSINVP);
		$pr='
			$(document).ready(function(){
				$(".button").click(function(){
						
						if($("#codigo").val() == "" && $("#barras").val() == ""  && $("#alterno").val() == ""  && $("#promo").val() == ""){
							alert("Debe ingresar al menos un codigo de producto");
							return false;
						}
					}
					);
			});
			
		';

		$form = new DataForm("inventario/precios_sinv/precios/modifica");
		$form->script($pr);
		$form->codigo = new inputField2("Consultar por C&oacute;digo de Producto", "codigo");
		$form->codigo->size = 15;
		$form->codigo->maxlength=15;
		$form->codigo->append($bSINV);
		$form->codigo->rule = 'trim';

		$form->barras = new inputField2("Consultar por C&oacute;digo de Barras", "barras");
		$form->barras->size = 15;
		$form->barras->maxlength=15;
		$form->barras->css_class = 'inputnum';
		$form->barras->append($bSINV);
		$form->barras->rule = 'trim';

		$form->alterno = new inputField2("Consultar por C&oacute;digo Alterno", "alterno");
		$form->alterno->size = 15;
		$form->alterno->maxlength=15;
		$form->alterno->rule = 'trim';
		$form->alterno->append($bSINV);

		$form->promo = new inputField2("Consultar por C&oacute;digo de Promosi&oacute;n", "promo");
		$form->promo->size = 15;
		$form->promo->maxlength=15;
		$form->promo->rule = 'trim';
		$form->promo->append($bSINVP);

		$form->submit("btnsubmit","CONSULTAR");
		$form->build_form();
		$msj="";
		if ($form->on_success()){
			set_time_limit(600);
			$codigo= $this->input->post("codigo");
			$barras= $this->input->post("barras");
			$alterno= $this->input->post("alterno");
			$promo= $this->input->post("promo");
			$campo="";
			$valor="";
			$band=1;

			if($codigo !="" && $band==1){
				$resul1=$this->db->query("SELECT count(*) as cant,id from sinv	where codigo='$codigo' ");
				$row1=$resul1->row();
				if($row1->cant > 0){
					$band=0;
					redirect("inventario/precios_sinv/dataedit/modify/$row1->id");
				}
			}

			if($barras != "" && $band==1){
				$resul2=$this->db->query("SELECT count(*) as cant,id from sinv	where barras='$barras'" );
				$row2=$resul2->row();
				if($row2->cant > 0 ){
					redirect("inventario/precios_sinv/dataedit/modify/$row2->id");
					$band=0;
				}
			}
			if($alterno !="" && $band==1){
				$resul3=$this->db->query("SELECT count(*) as cant,id from sinv	where alterno='$alterno'" );
				$row3=$resul3->row();
				if($row3->cant > 0 ){
					redirect("inventario/precios_sinv/dataedit/modify/$row3->id");
					$band=0;
				}
			}
			if($promo !="" && $band==1){
				$resul3=$this->db->query("SELECT count(*) as cant,a.codigo as codigo,b.id as id
											from sinvpromo as a
											join sinv as b on b.codigo=a.codigo 
											where a.codigo='$promo'" );
				$row3=$resul3->row();
				if($row3->cant > 0 ){
					redirect("inventario/precios_sinv/dataedit/modify/$row3->id");
					$band=0;
				}
			}
			$msj.= "NO se encontro el producto  <br>";
			$atras=site_url('inventario/precios_sinv/precios');
			$data['smenu']="<a href=".$atras.">ATRAS</a>";

		}


		$data['content'] =$form->output.$msj;
		$data['head']    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().$script;

		$data['title']   = '<h1>Cambiar Precios</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->pi18n->cargar('sinv','dataedit');
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

