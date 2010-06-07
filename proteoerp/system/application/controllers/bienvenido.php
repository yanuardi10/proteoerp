<?php
class Bienvenido extends Controller {
	function Bienvenido(){
		parent::Controller();
	}

	function index(){
		$this->session->set_userdata('panel', $this->uri->segment(3));

		$data['titulo1']  = '<center>';
		$data['titulo1']  .= '<h1>'.$this->datasis->traevalor("TITULO1")."</h1>\n";
		$data['titulo1']  .= "<p class='mininegro'>";
		$data['titulo1']  .= substr($this->datasis->traevalor("TITULO2"),0,70)."<br>";
		$data['titulo1']  .= substr($this->datasis->traevalor("TITULO3"),0,55)."<br>";
		$data['titulo1']  .= "RIF: ".substr($this->datasis->traevalor("RIF"),0,15)."<br>";
		$data['titulo1']  .= image('portada.jpg')."</p>\n";
		if ($this->datasis->login())
		//$data['titulo1']  .= "<p><a href='javascript:void(0);' onclick=\"window.open('/proteoerp/chat', 'wchat', 'width=580,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+((screen.availWidth/2)-290)+',screeny='+((screen.availHeight/2)-300)+'');\">Chat</a></p>";
		$data['titulo1']  .= "<p>&nbsp;</p>";
		$data['titulo1']  .= "</center><br>";
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
		$attributes  = array('name' => 'user','size' => '6');
		$data['titulo1'] .='<table><tr><td>Usuario: </td><td>'.form_input($attributes).'</td></tr>';
		$attributes  = array('name' => 'pws','size' => '6','type' => 'password');
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
		$attributes  = array('name' => 'user','size' => '6');
		$data['cuerpo'] .='<table><tr><td>Usuario: </td><td>'.form_input($attributes).'</td></tr>';
		$attributes  = array('name' => 'pws','size' => '6','type' => 'password');
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
			$out ='<div id=\'accordion\'>';
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
			}$out .='</div>';
		}
		echo $out;
	}

	function error(){
		$this->layout->buildPage('bienvenido/error');
	}
}
?>