<?php
class Bienvenido extends Controller {
	function Bienvenido(){
		parent::Controller();
	}
	
	function index(){
		$this->session->set_userdata('panel', $this->uri->segment(3));
		$data['titulo1']  = '<center>';
		$data['titulo1'] .= '<div id="tumblelog">';
		$data['titulo1'] .= image('portada.jpg');
		$data['titulo1'] .= '<h2>Sistemas Administrativos</h2>';
		$data['titulo1'] .= '<p></p>';
		$data['titulo1'] .= '</div>';

		if ($this->datasis->login())
		$data['titulo1']  .= '<p>&nbsp;</p>';
		$data['titulo1']  .= '</center>';
		$this->layout->buildPage('bienvenido/home', $data);
	}
	
	function autentificar(){
		$usr=sha1($_POST['user']);
		$pws=sha1($_POST['pws']);
		if (!preg_match("/^[^'\"]+$/", $usr)>0){
			$sess_data = array('logged_in'=> FALSE);
			$this->session->set_userdata($sess_data);
			redirect($this->session->userdata('estaba'));
		}
		
		$cursor=$this->db->query("SELECT us_nombre FROM usuario WHERE SHA(us_codigo)='$usr' AND SHA(us_clave)='$pws'");
		if($cursor->num_rows() > 0){
			$rr = $cursor->row_array();
			$sal = each($rr);
			$sess_data = array('usuario' => $_POST['user'],'nombre'  => $sal[1],'logged_in'=> TRUE );
		} else {
			$sess_data = array('logged_in'=> FALSE);
		}
		$this->session->set_userdata($sess_data);
		redirect($this->session->userdata('estaba'));
	}
	
	function cese(){
		$this->session->sess_destroy();
		redirect();
	}
	function ingresar(){
		$viene=$this->session->userdata('estaba');
		$attributes  = array('name' => 'ingresar_form');
		$data['titulo1'] = form_open('bienvenido/autentificar',$attributes);
		$attributes  = array('name' => 'user','size' => '6','autocomplete'=>'off');
		$data['titulo1'] .='<table><tr><td>Usuario: </td><td>'.form_input($attributes).'</td></tr>';
		$attributes  = array('name' => 'pws','size' => '6','type' => 'password','autocomplete'=>'off');
		$data['titulo1'] .='<tr><td> Clave:  </td><td>'.form_input($attributes).'</td></tr>';
		$data['titulo1'] .='<tr><td></td><td>'.form_submit('usr_submit', 'Enviar').form_close().'</td></tr></table>';
		// Build the thing
		$this->layout->buildPage('bienvenido/ingresar', $data);
	}
	
	function ingresarVentana(){
		$viene=$this->session->userdata('estaba');
		$data['estilos'] = style("estilos.css");
		$attributes  = array('name' => 'ingresar_form');
		$data['cuerpo'] = form_open('bienvenido/autentificar',$attributes);
		$attributes  = array('name' => 'user','size' => '6','autocomplete'=>'off');
		$data['cuerpo'] .='<table><tr><td>Usuario: </td><td>'.form_input($attributes).'</td></tr>';
		$attributes  = array('name' => 'pws','size' => '6','type' => 'password','autocomplete'=>'off');
		$data['cuerpo'] .='<tr><td> Clave:  </td><td>'.form_input($attributes).'</td></tr>';
		$data['cuerpo'] .='<tr><td></td><td>'.form_submit('usr_submit', 'Enviar').form_close().'</td></tr></table>';
		// Build the thing
		$this->load->view('ingreso', $data);
	}

	function accordion($pertenece=NULL){
		if(empty($pertenece)) return;
		$out='';
		$arreglo=arr_menu(2,$pertenece);
		$arreglo=arr2panel($arreglo);
		
		if (count($arreglo)>0){
			//$out ='<div id=\'accordion\'>';
			foreach($arreglo as $panel => $opciones ){
				$out .="<div class='myAccordion-declencheur'><h1>".htmlentities($panel)."</h1></div>\n";
				$out .= "<div class='myAccordion-content'><table width='100%' cellspacing='0' border='0'>\n";
				$color = "#FFFFFF";
				foreach ($opciones as $opcion) {
					$out .= "<tr bgcolor='$color'><td>";
					$out .= arr2link($opcion);
					$out .= "</td></tr>\n";
					if ( $color == "#FFFFFF" ) $color = "#F4F4F4"; else  $color = "#FFFFFF";
				}$out .="</table></div>\n";
			}//$out .='</div>';
		}
		echo $out;
	}
	
	function error(){
		$this->layout->buildPage('bienvenido/error');
	}
	
	function cargapanel($pertenece=NULL) {
		if(empty($pertenece)) return;
		$out='';
		$arreglo=arr_menu(2,$pertenece);
		$arreglo=arr2panel($arreglo);
		if (count($arreglo)>0){
			$out  = '<div id=\'tumblelog\'> ';
			$desca  = $this->datasis->dameval("SELECT mensaje FROM intramenu WHERE modulo='".$pertenece."' ");
			$imagen = $this->datasis->dameval("SELECT imagen  FROM intramenu WHERE modulo='".$pertenece."' ");
			$desca  = htmlentities($desca);
			$out .="<div class='col6'> ";
			$out .= "<table ><tr><td>".image($imagen)."</td><td>";
			$out .= "<h2>".$desca."</h2></td></tr></table>";
			$out .= "</div>";
			foreach($arreglo as $panel => $opciones ){
				$out .="<div class='box col1'><h3>".htmlentities($panel)."</h3></h1>\n";
				$out .= "<table width='100%' cellspacing='1' border='0'>\n";
				$color = "#FFFFFF";
				foreach ($opciones as $opcion) {
					$out .= "<tr bgcolor='$color'><td>";
					$out .= arr2link($opcion);
					$out .= "</td></tr>\n";
					if ( $color == "#FFFFFF" ) $color = "#F4F4F4"; else  $color = "#FFFFFF";
				}$out .="</table></div>\n";
			}$out .='</div>';
		}
		echo $out;
	}
	
}
?>