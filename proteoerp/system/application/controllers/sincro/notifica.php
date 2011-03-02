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
		$this->datasis->modulo_id('923',1);
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
		$this->datasis->modulo_id('923',1);
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
		$this->datasis->modulo_id('923',1);
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

	function eventos(){
		$this->datasis->modulo_id('923',1);

		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Eventos', 'eventos');

		$filter->nombre = new inputField('Nombre del evento', 'nombre');
		$filter->nombre->size=15;
		$filter->nombre->maxsize=12;

		$filter->activo = new dropdownField('Activos','activo');
		$filter->activo->option('','Todos');
		$filter->activo->option('S','S&iacute;' );
		$filter->activo->option('N','No');

		$filter->buttons('reset','search');
		$filter->build();

		$grid = new DataGrid('Cheques emitidos');
		$grid->per_page = 10;

		$uri = anchor('sincro/notifica/dataediteventos/show/<#id#>','<#nombre#>');
		$grid->column_orderby('Nombre'      ,$uri,'nombre');
		$grid->column_orderby('Activo'      ,'activo','activo');
		$grid->column_orderby('Fecha'        ,'<dbdate_to_human><#fechahora#></dbdate_to_human>','fechahora');
		$grid->column_orderby('Concurrencia' ,'concurrencia'  ,'concurrencia');
		$grid->column_orderby('U. Ejecuci&oacute;n','disparo','disparos');
		$grid->add('sincro/notifica/dataediteventos/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Gestor de eventos');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function ejecutor(){
		if($this->secu->es_shell()){
			$mSQL='SELECT *,UNIX_TIMESTAMP(`disparo`) AS utime FROM eventos WHERE activo="S"';
			$query = $this->db->query($mSQL);
			$time=time();
			$not=0;

			if ($query->num_rows() > 0){
				foreach ($query->result() as $__row){
					switch($__row->concurrencia){
						case 'D':
							$tt=24*3600;
							break;
						case 'S':
							$tt=7*24*3600;
							break;
						case 'M':
							$tt=30*24*3600;
							break;
						case 'A':
							$tt=365*24*3600;
							break;
						default:
							$tt=0;
					}

					if($time-$__row->utime>=$tt){
						$pte=explode(' ',$__row->fechahora);
						$pte=explode(':',$pte[1]);
						$updfecha=$this->db->escape(date('Y-m-d H:i:s',mktime($pte[0], $pte[1], $pte[2])));
						$this->db->simple_query("UPDATE eventos SET disparo=$updfecha WHERE id=".$__row->id);

						$activa=$this->meval($__row->activador);
						if($activa){
							$msj=$this->meval($__row->accion);

							preg_match_all("/(?<para>[0-9]{4}\-[0-9]{7})/" ,$__row->para,$matches);
							$telefonos= $matches['para'];
							if(count($telefonos)>0){
								foreach($telefonos AS $telefono){
									$telef=explode('-',$telefono);
									$rt=$this->_enviar($telef[0],$telef[1],$msj);
									if(!$rt){
										echo "Error enviando mensaje al telefono $telef[0]-$telef[1] \n";
									}
								}
							}

							preg_match_all("/(?<para>[\w-\.]+@([\w-]+\.)+[\w-]{2,4})/" ,$__row->para,$matches);
							$correos  = $matches['para'];
							$titulo=$this->datasis->traevalor('TITULO1');
							if(count($correos)>0){
								foreach($correos AS $correo){
									$rt=$this->_mail($correo,'Notificacion ProteoERP::'.$titulo,$msj);
									if(!$rt){
										echo "Error enviando correo $correo \n";
									}
								}
							}
							$not++;
							//echo $msj.' '.strlen($msj);
						}
					}
				}
			}
			echo "Se enviaron $not notificaciones \n";
		}
	}

	function meval($code){
		return eval($code);
	}

	function dataediteventos(){
		$this->datasis->modulo_id('923',1);
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Programador de eventos', 'eventos');
		$edit->pre_process('insert','_pre_insert');
		$edit->back_url = site_url('sincro/notifica/eventos');

		$edit->nombre = new inputField('Nombre del evento','nombre');
		$edit->nombre->rule='max_length[100]';
		$edit->nombre->maxlength =100;

		$edit->comenta = new inputField('Comentario','comenta');
		$edit->comenta->rule='max_length[100]';
		$edit->comenta->maxlength =100;

		$edit->activo = new dropdownField('Activo','activo');
		$edit->activo->rule = 'required|max_length[1]';
		$edit->activo->option('','Seleccionar');
		$edit->activo->option('S','S&iacute;' );
		$edit->activo->option('N','No');

		$edit->fechahora = new dateField('Fecha de arranque','fechahora','d/m/Y H:i:s');
		$edit->fechahora->rule='chfecha|max_length[19]';
		$edit->fechahora->size =20;
		$edit->fechahora->insertValue=date("Y-m-d H:i:s");
		$edit->fechahora->calendar=false;
		$edit->fechahora->maxlength =19;
		$edit->fechahora->autocomplete=false;
		$edit->fechahora->append('Si el evento tiene concurrencia se va a ejecutar desde la fecha data a la misma hora');

		$edit->concurrencia = new dropdownField('Concurrencia','concurrencia');
		$edit->concurrencia->option('D','Diaria' );
		$edit->concurrencia->option('S','Semanal');
		$edit->concurrencia->option('M','Mensual');
		$edit->concurrencia->option('A','Anual'  );
		$edit->concurrencia->option('F','Funci&oacute;n activadora');
		$edit->concurrencia->rule='max_length[1]';

		$edit->activador = new textareaField('Funcion activadora','activador');
		$edit->activador->cols = 70;
		$edit->activador->rows = 4;

		$edit->para = new textareaField('Destinatarios','para');
		$edit->para->cols = 70;
		$edit->para->rows = 3;
		$edit->para->append('Correos electr&oacute;nicos o n&uacute;mero de telefonos 9999-9999999');

		$edit->accion = new textareaField('Acci&oacute;n','accion');
		$edit->accion->cols = 70;
		$edit->accion->rows = 8;

		$edit->disparo = new dateField('Fecha de la &uacute;ltima ejecuci&oacute;n','disparo','d/m/Y H:i:s');
		$edit->disparo->rule = 'chfecha|max_length[19]';
		$edit->disparo->size = 20;
		$edit->disparo->insertValue=date("Y-m-d H:i:s");
		$edit->disparo->calendar  = false;
		$edit->disparo->maxlength = 19;
		$edit->disparo->autocomplete=false;
		$edit->disparo->when=array('show','modify');

		$edit->usuario  = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->estampa  = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();
		/*$this->rapyd->jquery[]='$("#fechahora").AnyTime_picker({
			format: "%d/%m/%Y %H:%i:%s%#",
			formatUtcOffset: "%: (%@)",
			hideInput: true,
			labelHour: "Horas",
			labelMinute: "Minutos",
			labelMonth: "Mes",
			labelSecond: "Segundos",
			labelYear: "A&ntilde;o",
			labelDayOfMonth: "D&iacute;a del mes",
			dayAbbreviations:Array("Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa", "Do"),
			monthAbbreviations: Array("Ene", "Feb", "Mar", "Abr", "Mayo", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"),
			monthNames: Array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"),
			placement: "inline",
			labelTitle: "Seleccione el d&iacute;a y la hora"
		});';*/

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head().style('anytimec.css').script('plugins/anytimec.js');
		$data['title']   = heading('Programador de eventos');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		$codigo = $do->get('nombre');
		$fecha  = $do->get('fechahora');
		$do->set('disparo',$fecha );
		logusu('notifica',"EVENTO $codigo CREADO");
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

	function instalar(){
		$mSQL="CREATE TABLE `eventos` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`nombre` varchar(100) NOT NULL,
		`comenta` varchar(100) NOT NULL COMMENT 'Comentario del evento',
		`fechahora` datetime NOT NULL,
		`activador` tinytext NOT NULL COMMENT 'Funcion a evaluar, si devuelve verdadero se dispara',
		`concurrencia` char(1) NOT NULL COMMENT 'S semanal, D diario, H cada hora,',
		`para` tinytext NOT NULL COMMENT 'a quienes se les notifica',
		`accion` text NOT NULL,
		`disparo` datetime NOT NULL,
		`activo` char(1) NOT NULL DEFAULT 'N',
		`usuario` varbinary(10) NOT NULL,
		`estampa` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`),
		UNIQUE KEY `nombre` (`nombre`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Tabla que guarda las acciones por eventos'";
		$this->db->simple_query($mSQL);
	}
}
