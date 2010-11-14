<?php
class Contra extends Controller {

	function Contra(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->library("iacont");
	}

	function index(){
		$this->rapyd->load('dataform');

		$form = new DataForm("finanzas/contra/index/process");

		$form->cont = new textareaField("Contenido", "cont");
		$form->cont->rule = "required";
		$form->cont->rows = 10;

		$form->submit("btnsubmit","Enviar");
		$form->build_form();

		if ($form->on_success()){
			//echo 'Hola mundo';
			$par=$form->cont->newValue;
			$this->iacont->reconoce($par);
			echo $this->iacont->soy."\n<pre>";
			print_r($this->iacont->data);
			echo '</pre>';
			//$this->_partida($form->cont->newValue);
		}

		$data['content'] = $form->output;
		$data['title']   = "<h1>Bancos y cajas</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


	/*function _partida($par){
		$mSQL="SELECT unidades FROM unidad";
		$query = $this->db->query($mSQL);
		$uni=array();
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$uni[]=preg_quote(trim($row->unidades),'/');
			}
		}
		$uni    =implode('|',$uni);
		//$pattern="/(?<unidad>($uni))/i";

		$pattern="/^(?<partida>\w+) +(?<unidad>\w+) +(?<cantidad>[0-9,\.]+) +(?<precio>[0-9,\.]+)/i";

		$matches=array();
		$con=preg_match_all($pattern,$par,$matches);
		if($con>0){
			$p['partida']  = $matches['partida'][0];
			$p['unidad']   = $matches['unidad'][0];
			$p['cantidad'] = cadAnum($matches['cantidad'][0]);
			$p['precio']   = cadAnum($matches['precio'][0]);
			$p['monto']    = $p['cantidad']*$p['precio'];

			$mSQL='SELECT descrip FROM obpa WHERE codigo='.$this->db->escape($matches['partida'][0]);
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					$p['descrip']  =trim($row->descrip);
				}
			}
			print_r($p);
			return true;
		}
		return false;
	}

	function _certificacion($par){
		$pattern="/^(?<partida>\w+) +(?<monto>[0-9,\.]+)/i";

		$matches=array();
		$con=preg_match_all($pattern,$par,$matches);
		if($con>0){
			$p['partida']  = $matches['partida'][0];
			$p['monto']   = cadAnum($matches['monto'][0]);
			print_r($p);
		}
	}

	function _bitacora($par){
		$p['contenido']=$par;
	}*/
}
