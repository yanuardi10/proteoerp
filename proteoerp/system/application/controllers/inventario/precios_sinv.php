<?php  require_once(BASEPATH.'application/controllers/inventario/common.php');

class precios_sinv extends Controller{
	function precios_sinv(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('301',1);
	}

	function index(){
		redirect('inventario/precios_sinv/precios');
	}

	function precios(){
		$this->rapyd->load('dataedit','dataform');

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

		$pr='$(document).ready(function(){
				$(".button").click(function(){
					if($("#codigo").val() == "" ){
						alert("Debe ingresar un codigo de producto");
						return false;
					}
				});
				$(".inputnum").numeric(".");
				$("#codigo").val("");
			});';

		$form = new DataForm('inventario/precios_sinv/precios/modifica');
		$form->script($pr);
		$form->codigo = new inputField2('Consultar Productos', 'codigo');
		$form->codigo->size = 15;
		$form->codigo->maxlength=15;
		$form->codigo->append($bSINV);
		$form->codigo->rule = 'trim|required';

		$form->submit('btnsubmit','Cambiar');
		$form->build_form();
		$band=0;
		$msj="";
		if ($form->on_success()){
			$codigo= $form->codigo->newValue;

			$mSQL_p = 'SELECT id FROM sinv';
			$bbus   = array('codigo','barras','alterno');
			$suple  = null;
			$query  = Common::_gconsul($mSQL_p,$codigo,$bbus,$suple);
			if($query!==false){
				$row = $query->row();
				redirect('inventario/precios_sinv/dataedit/modify/'.$row->id);
			}else{
				$msj.= 'No se encontro el producto';
			}

			/*$resul1=$this->db->query("SELECT count(*) AS cant,id FROM sinv WHERE codigo='$codigo' ");
			$row1=$resul1->row();
			if($row1->cant > 0){
				$band=1;
				redirect("inventario/precios_sinv/dataedit/modify/$row1->id");
			}
			if($band == 0){
				$resul1=$this->db->query("SELECT count(*) AS cant,id FROM sinv WHERE barras='$codigo' ");
				$row1=$resul1->row();
				if($row1->cant > 0){
					$band=1;
					redirect("inventario/precios_sinv/dataedit/modify/$row1->id");
				}
			}
			if($band == 0){
				$resul1=$this->db->query("SELECT count(*) AS cant,id FROM sinv where alterno='$codigo' ");
				$row1=$resul1->row();
				if($row1->cant > 0){
					$band=1;
					redirect("inventario/precios_sinv/dataedit/modify/$row1->id");
				}
			}
			if($band==0){
				$resul1=$this->db->query("SELECT count(*) AS cant,codigo FROM barraspos WHERE suplemen='$codigo' ");
				$row1=$resul1->row();
				if($row1->cant > 0){
					$resul2=$this->db->query("SELECT count(*) AS cant,id FROM sinv WHERE codigo='$row1->codigo' ");
					$row2=$resul2->row();
					if($row2->cant > 0){
						redirect("inventario/precios_sinv/dataedit/modify/$row2->id");
					}
				}
			}
			if ($band==0){
				$msj.= "No se encontro el producto";
			}*/
		}

		$data['content'] =$form->output.$msj;
		$data['head']    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head();
		$data['title']   = '<h1>Cambiar Precios</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$script='$(function(){
			$(".inputnum").numeric(".");
			$("form").submit(function() {  
				v1=$("#precio1").val();
				v2=$("#precio2").val();
				v3=$("#precio3").val();
				v4=$("#precio4").val();
				if (v1 != "" && v2 != "" && v3 != "" && v4 != "") {
					return true
				} else {
					alert("Debe ingresar todos los precios");
					return false
				}
			});
		});';

		$edit = new DataEdit('Cambios de precios','sinv');
		
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save=true;
		$edit->pre_process( 'update','_pre_update');
		$edit->pre_process( 'create','_pre_create');
		$edit->post_process('update','_pos_update');
		$edit->back_url = site_url('inventario/precios_sinv');
		$edit->script($script,'modify');

		$edit->codigo = new inputField('C&oacute;digo','codigo' );
		$edit->codigo->mode='autohide';

		$edit->descrip = new inputField('Descripci&oacute;n','descrip' );
		$edit->descrip->mode='autohide';

		for($i=1;$i<5;$i++){
			$obj='precio'.$i;
			$edit->$obj = new inputField('Precio '.$i, $obj);
			$edit->$obj->css_class='inputnum';
			$edit->$obj->rule ='numeric|trim|required';
			$edit->$obj->size = 10;
			$edit->$obj->maxlength=10;
			$edit->$obj->group='Precios Asignados';
		}

		$edit->buttons('modify','back','undo','save');
		$edit->build();

		$data['content'] =$edit->output;
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head();
		$data['title']   = '<h1>Cambiar Precio</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function _pos_update($do){
		$codigo= $do->get('codigo');
		$precio4= $do->get('precio4');
		logusu('SINV',"PRECIOS del producto $codigo MODIFICADO, precio4 $precio4");
	}

	function _pre_create($do){
		$do->error_message_ar['pre_upd'] = 'No se puede crear un registro por este modulo';
		return false;
	}

	function _pre_update($do){
		for($i=1;$i<5;$i++){
			$prec='precio'.$i;
			$$prec=round($do->get($prec),2); //optenemos el precio
		}

		if($precio1>=$precio2 && $precio2>=$precio3 && $precio3>=$precio4){
			$formcal= $do->get('formcal');
			$iva= $do->get('iva');
			$costo=($formcal=='U')? $do->get('ultimo'):($formcal=='P')? $do->get('pond'):($do->get('pond')>$do->get('ultimo'))? $do->get('pond') : $do->get('ultimo');

			for($i=1;$i<5;$i++){
				$prec='precio'.$i;
				$base='base'.$i;
				$marg='margen'.$i;

				$$base=$$prec*100/(100+$iva);   //calculamos la base
				$$marg=100-($costo*100/$$base); //calculamos el margen

				$do->set($prec,round($$prec,2));
				$do->set($base,round($$base,2));
				$do->set($marg,round($$marg,2));
			}
			return true;
		}else{
			$do->error_message_ar['pre_upd'] = 'Los precios deben cumplir con:<br> Precio 1 mayor o igual al Precio 2 mayor o igual al  Precio 3 mayor o igual al Precio 4';
			return false;
		}
	}
}