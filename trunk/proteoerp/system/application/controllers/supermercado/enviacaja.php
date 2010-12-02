<?php
class enviacaja extends Controller {

	var $url;

	function enviacaja(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->url='supermercado/enviacaja/';
	}

	function index(){
		$this->rapyd->load('dataform');

		$lscst=site_url('compras/scst/autocomplete/control');
		$script ='
		function formato(row) {
			return row[0]+" "+row[1]+" "+row[3];
		}

		$(function() {
			$("#control").autocomplete("'.$lscst.'",{
				delay:10,
				//minChars:2,
				matchSubset:1,
				matchContains:1,
				cacheLength:10,
				formatItem:formato,
				width:450,
				autoFill:true
				}
			);
		});';

		$form = new DataForm($this->url.'index/process');
		$form->title('Pasar precios a cajas');
		$form->script($script);

		$form->control = new inputField('Control de compra', 'control');
		$form->control->rule = 'required|callback_chcontrol';
		$form->control->size = 12;
		$form->control->maxlength = 10;
		$form->control->append('Dejar vacio para enviar un masivo');

		$form->submit("btnsubmit","Pasar precios a caja");
		$form->build_form();

		$error='';
		if ($form->on_success()){
			$control  = $form->control->newValue;
			$usr=$this->session->userdata('usuario');
			$join='';
			if(strlen($control>0))
				$join='JOIN `itscst` AS b ON a.codigo=b.codigo AND b.codigo='.$this->db->escape($control);
			$mSQL="INSERT INTO `enviapos` (codigo,numero,precio1,fecha,hora,usuario)
				  SELECT a.codigo,'INVENTAR',a.precio1,CURDATE(),CURTIME(),'$usr' FROM `maes` AS a $join";
			$ban = $this->db->simple_query($mSQL);
			if(!$ban) {
				$error.="Hubo problemas al pasar los precios, comuniquese con servicio t&eacute;cnico";
				memowrite($mSQL,'ENVIACAJA');
			}else{
				logusu('ENVIACAJA',"Fue enviado una actualizacion de precios");
			}
		}

		$data['content'] = "<div class='alert'>$error</div>";
		$data['content'].= $form->output;
		$data['title']   = '<h1>Env&iacute;o de precios para las cajas</h1>';
		$data["head"]    = script('jquery.pack.js').script('plugins/jquery.autocomplete.js').style('jquery.autocomplete.css').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function chcontrol($control){
		$cana=$this->datasis->dameval("SELECT COUNT(*) FROM scst WHERE control='$control'");
		if ($cana == 0){
			$this->validation->set_message('chcontrol', "No existe compra con el control dado");
			return FALSE;
		}
		return TRUE;
	}

}
?>