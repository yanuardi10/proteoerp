<?php
// Es necesario para que funciona las siguiesnte slibrerias de pear
// pear install Mail
// pear install Net_SMTP

class notifica extends controller {

	function notifica(){
		parent::Controller();
		$this->config->load('notifica');
		$this->load->library('rapyd');
		$this->error='';
	}

	function index(){
		$list[]=anchor('sincro/notifica/sms','Mensajes SMS');
		$list[]=anchor('sincro/notifica/email','E-Mail');
		$attributes = array('class' => 'boldlist','id'    => 'mylist');
		$out=ul($list, $attributes);

		$data['content'] = $out;
		$data['title']   = '<h1>Env&iacute;os de mensajes</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function sms(){
		$this->rapyd->load('dataform');

		$form = new DataForm('sincro/notifica/sms/process');

		$form->codigo = new dropdownField('N&uacute;mero', 'codigo');
		$form->codigo->option('414','0414');
		$form->codigo->option('424','0424');
		$form->codigo->rule = 'required';
		$form->codigo->style = 'width:80px';

		$form->numero = new inputField('N&uacute;mero', 'numero');
		$form->numero->rule = 'numeric|required|max_length[7]|min_length[7]';
		$form->numero->maxlength =7;
		$form->numero->size =10;
		$form->numero->in = 'codigo';

		$form->msg = new textareaField('Mensaje', 'msg');
		$form->msg->rule = 'required|max_length[104]';
		$form->msg->rows = 6;
		$form->msg->cols = 20;

		$form->submit('btnsubmit','Enviar');
		$form->build_form();

		if ($form->on_success()){
			$cod=$form->codigo->newValue;
			$num=$form->numero->newValue;
			$msg=$form->msg->newValue;

			$rt=$this->_enviar($cod,$num,$msg);

			if(!$rt){
				$form->error_string=$this->error;
				$form->build_form();
				$salida=$form->output.br();
			}else{
				$salida=$form->output.br().'Tu mensaje ha sido enviado satisfactoriamente';
			}
		}else{
			$salida=$form->output;
		}

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $salida;
		$data['title']   = '<h1>Env&iacute;os de mensajes de texto SMS</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function email(){
		$this->rapyd->load('dataform');

		$form = new DataForm('sincro/notifica/email/process');

		$form->to = new inputField('Destinatario', 'to');
		$form->to->rule = 'valid_emails|required';
		//$form->to->maxlength =7;
		//$form->to->size =20;

		$form->subject = new inputField('Asunto', 'subject');
		//$form->subject->size =20;

		$form->body = new textareaField('Mensaje', 'body');
		$form->body->rule = 'required';

		$form->submit('btnsubmit','Enviar');
		$form->build_form();

		if ($form->on_success()){
			$to      = $form->to->newValue;
			$subject = (empty($form->subject->newValue)) ? 'Sin asunto' : $form->subject->newValue;
			$body    = $form->body->newValue;

			$rt=$this->_mail($to,$subject,$body);

			if(!$rt){
				$form->error_string=$this->error;
				$form->build_form();
				$salida=$form->output.br();
			}else{
				$salida=$form->output.br().'Tu mensaje ha sido enviado satisfactoriamente';
			}
		}else{
			$salida=$form->output;
		}

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $salida;
		$data['title']   = '<h1>Env&iacute;os de correos electr&oacute;nicos</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _enviar($cod,$num,$msg){
		if($cod=='414' or $cod=='424'){
			$rt=$this->_movistar($cod,$num,$msg);
		}elseif($cod=='412'){
			$rt=$this->_digitel($cod,$num,$msg);
		}elseif($cod=='416' or $cod=='426'){
			$rt=$this->_movilnet($cod,$num,$msg);
		}else{
			$this->error='M&eacute;todo no definido para ese c&oacute;digo';
			$rt=false;
		}
		return $rt;
	}

	function _movistar($codigo,$numero,$msg){
		$host ='https://sms.mipunto.com/servlet/ServletAutenticacion';

		$usr  = $this->config->item('movistar_usr'); //usuario en mipunto.com
		$clave= $this->config->item('movistar_pwd'); //clave   en mipunto.com

		$data = array(
			'urlExito'         => '/punto_movil/sms/expressSend.do?option=showExpressSend&urlExito=expressSend.do?option=showExpressSend',
			'urlError'         => '/punto_movil/sms/index.jsp',
			'urlAutenticacion' => '/punto_movil/sms/index.jsp',
			'option'           => '',
			'login'            => $usr,
			'password'         => $clave,
			'login_cookie'     => '1',
			'from'             => 'index',
			'imageField.x'     => '28',
			'imageField.y'     => '7'
		);

		$cookie = tempnam(sys_get_temp_dir(), 'mipunto');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $host);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_COOKIEJAR , $cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3');
		$output = curl_exec($ch);
		$error  = curl_errno($ch);
		$this->error=curl_error($ch);
		curl_close($ch);

		if($error==0){
			if (preg_match("/Ha ocurrido el siguiente error/i", $output)) {
				$this->error='Usuario o clave incorrecta';
				$error=1;
			}
		}
		//$info  = curl_getinfo($ch);

		if($error==0){
			$data = array(
				'exMessage'     => substr($msg,0,103),
				'exCountryCode' => '+58',
				'exCityCode'    => $codigo,
				'exCellNumber'  => $numero
			);
			$data['count']=strlen($data['exMessage']);

			$host ='https://sms.mipunto.com/punto_movil/sms/expressSend.do?option=expressSend';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);

			$output = curl_exec($ch);
			$error  = curl_errno($ch);
			$this->error=curl_error($ch);
			curl_close($ch);
			unlink($cookie);

			if($error==0){
				if (preg_match("/Tu mensaje ha sido enviado satisfactoriamente/i", $output)) {
					return true;
				}else{
					$this->error='Tu mensaje no se pudo enviar, por favor intente mas tarde';
				}
			}
			//echo '<pre>'.htmlspecialchars($output).'</pre>';
		}
		return false;
	}

	//Funcion que notifica a los usuarios de un evento dado
	function enventos(){
	
	}


	function _movilnet($codigo,$numero,$msg){
		$this->error='M&eacute;todo no definido';
		return false;
	}

	function _digitel($codigo,$numero,$msg){
		$this->error='M&eacute;todo no definido';
		return false;
	}

	function _mail($to,$subject,$body){
		if(!@require_once 'Mail.php'){
			$this->error='Problemas al cargar la clase Mail, probablemente sea necesario instalarla desde PEAR, comuniquese con soporte t&eacute;cnico';
			return false;
		}

		$from = $this->config->item('mail_smtp_from');
		$host = $this->config->item('mail_smtp_host');
		$port = $this->config->item('mail_smtp_port');
		$user = $this->config->item('mail_smtp_usr');
		$pass = $this->config->item('mail_smtp_pwd');

		$headers = array (
			'From'    => $from,
			'To'      => $to,
			'Subject' => $subject
		);
		$parr=array (
			'host'     => $host,
			'port'     => $port,
			'auth'     => true,
			'username' => $user,
			'password' => $pass
		);
		$body.="\n\nEsta es una cuenta de correo no monitoreada. Por favor no responda o reenvíe mensajes a esta cuenta.";

		$smtp = Mail::factory('smtp',$parr);
		$mail = $smtp->send($to, $headers, $body);
		if (PEAR::isError($mail)) {
			$this->error=$mail->getMessage();
			return false;
		} else {
			return true;
		}
	}
}