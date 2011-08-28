<?php
class Formatos extends Controller{

	var $_direccion;
	function Formatos(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->load->library("numletra");
		$this->load->plugin('numletra');
		$this->load->helper('string');
		//$protocolo=explode('/',$_SERVER['SERVER_PROTOCOL']);
		//$this->_direccion=$protocolo[0].'://'.$_SERVER['SERVER_NAME'].'/'.trim_slashes($this->config->item('base_url'));
	}

	function index(){
		
	}

	function ver(){
		$parametros= func_get_args();
		$this->_direccion='http://localhost/'.trim_slashes($this->config->item('base_url'));
		if (count($parametros)>0){
			$_arch_nombre=implode('-',$parametros);
			$_fnombre=array_shift($parametros);
			$this->load->plugin('html2pdf');
			$query = $this->db->query("SELECT proteo FROM formatos WHERE nombre='$_fnombre'");
			if ($query->num_rows() > 0){
				$row = $query->row();
				ob_start();
					echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
					$_html=ob_get_contents();
				@ob_end_clean();
				if(strlen($_html)>0)
					pdf_create($_html, $_arch_nombre);
				else
					echo 'Formato no definido';
				
			}else{
				echo 'Formato no existe';
			}
		}else{
			echo 'Faltan parametros';
		}
	}

	function ver2(){
		$parametros= func_get_args();
		$this->_direccion='http://localhost/'.trim_slashes($this->config->item('base_url'));
		if (count($parametros)>0){
			$_arch_nombre=implode('-',$parametros);
			$_fnombre=array_shift($parametros);
			$this->load->library('dompdf/cidompdf');
			$query = $this->db->query("SELECT proteo FROM formatos WHERE nombre='$_fnombre'");
			if ($query->num_rows() > 0){
				$row = $query->row();
				ob_start();
					echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
					$_html=ob_get_contents();
				@ob_end_clean();
				if(strlen($_html)>0)
					$this->cidompdf->html2pdf($_html,$_arch_nombre);
				else
					echo 'Formato no definido';
			}else{
				echo 'Formato no existe';
			}
		}else{
			echo 'Faltan parametros';
		}
	}

	function verhtml(){
		$parametros= func_get_args();
		$this->_direccion='/'.trim_slashes($this->config->item('base_url'));
		if (count($parametros)>0){
			$_fnombre=array_shift($parametros);
			$query = $this->db->query("SELECT proteo FROM formatos WHERE nombre='$_fnombre'");
			if ($query->num_rows() > 0){
				$row = $query->row();
				eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
			}else{
				echo 'Formato no existe';
			}
		}else{
			echo 'Faltan parametros';
		}
	}

	function enlistar(){

		$l[]='formatos/verhtml/PRESUP/00004320';
		$l[]='formatos/verhtml/PRESUP/00004318';
		$l[]='formatos/verhtml/PRESUP/00004319';

		$this->rapyd->jquery[]='$(\'#tabs\').tabs();';
		
		$out='
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1">Compra</a></li>
				<li><a href="#tabs-2">Retencion</a></li>
			</ul>
			<div id="tabs-1">
				<iframe src="html_intro.asp" width="100%" height="300">
					<p>Your browser does not support iframes.</p>
				</iframe>
			</div>
			<div id="tabs-2">
				<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
			</div>
			<div id="tabs-3">
				<p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
				<p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>
			</div>
		</div>';
		

		$data['content'] = $out;
		$data['title']   = heading('Lista reportes');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);
	}

	function instalar(){
		$mSQL="ALTER TABLE `formatos` ADD `proteo` TEXT NULL AFTER `forma`";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `formatos` ADD `harbour` TEXT NULL AFTER `proteo`";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `formatos`  ADD COLUMN `tcpdf` TEXT NULL AFTER `forma`";
		$this->db->simple_query($mSQL);
	}
}