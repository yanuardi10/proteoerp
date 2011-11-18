<?php
class Recep extends Controller {
	var $titp   = 'Movimientos de Mercancia';
	var $tits   = 'Movimientos';
	var $url    = 'inventario/recep/';

	function Recep(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->serial_repetidos=array();
		//$this->datasis->modulo_id(135,1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter2','datagrid');

		$filter = new DataFilter2('');

		//$filter->db->select(array("b.cuenta","a.comprob","a.fecha","a.origen","a.debe","a.haber","a.status","a.descrip","a.total"));
		$filter->db->from('recep a');

		$filter->recep = new inputField('Numero', 'recep');
		$filter->recep->size  =10;
		$filter->recep->db_name='a.recep';

		/*$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ="where";
		$filter->fechad->db_name =$filter->fechah->db_name="a.fecha";
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";

		//$filter->fecha = new dateonlyField("Fecha", "fecha");
		//$filter->fecha->size=12;

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name="a.descrip";

		$filter->descripd = new inputField("Concepto Detalle", "descripd");
		$filter->descripd->db_name="b.concepto";

		$filter->cuenta = new inputField("Cuenta", "cuenta");
		$filter->cuenta->db_name="b.cuenta";

		$filter->status = new dropdownField("Status", "status");
		$filter->status->db_name="a.status";
		$filter->status->option('','Todos');
		$filter->status->option('A',"Actualizado");
		$filter->status->option('D','Diferido');

		$filter->vdes = new checkboxField("Ver solo asientos descuadrados","vdes",'S','N');
		$filter->vdes->insertValue='N';
		$filter->vdes->clause='';*/

		$filter->buttons('reset','search');

		$filter->build();
		$uri = anchor($this->url.'dataedit/show/<#recep#>','<#recep#>');

		function tipo($tipo){
			switch($tipo){
				case 'E':return 'Entrega';break;
				case 'R':return 'Recepci&oacute;n';break;
			}
		}

		$grid = new DataGrid('');
		$grid->order_by('a.recep','desc');
		$grid->per_page = 20;
		$grid->use_function('substr','str_pad');

		$grid->column_orderby('Numero Recepci&oacute;n',$uri,'numero');
		$grid->column_orderby('Fecha'                  ,'<dbdate_to_human><#fecha#></dbdate_to_human>'  ,'fecha'    ,'align=\'center\''      );
		$grid->column_orderby('Or&iacute;gen'          ,'origen'                                        ,'origen'   ,'align=\'center\''      );
		$grid->column_orderby('Tipo'                   ,'<tipo><#tipo#></tipo>'                         ,'tipo'     ,'align=\'center\''      );
		$grid->column_orderby('Cod.Proveedor/Cliente'  ,'clipro'                                        ,'cod_prov' ,'align=\'center\''      );
		$grid->column_orderby('Observacion'            ,'observa'                                       ,'observa'  ,'align=\'left\'  NOWRAP');

		$grid->add($this->url.'dataedit/create');
		$grid->build();
		//echo $grid->db->last_query();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['script']  = script('jquery.js');
		$data['title']   = heading($this->titp);
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		/*$mSPRV=array(
			'tabla'   =>'view_clipro',
			'columnas'=>array(
			    'codigo' =>'C&oacute;digo',
			    'tipo'    =>'Tipo',
			    'rif'     =>'RIF/CI',
			    'nombre'  =>'Nombre'),
			'filtro'  =>array(
			    'tipo'    =>'Tipo',
			    'codigo' =>'C&oacute;digo',
			    'rif'     =>'RIF/CI',
			    'nombre'  =>'Nombre'),
			'retornar'=>array('codigo'=>'clipro','nombre'=>'nombre'),
			'script'  =>array('human_traslate()'),
			'titulo'  =>'Buscar Proveedor / Cliente');
		$bSPRV=$this->datasis->p_modbus($mSPRV,'proveed');*/

		$sprvbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  => array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=> array('proveed'=>'clipro', 'nombre'=>'nombre'),
			'script'  => array('_post_modbus()'),
			'titulo'  =>'Buscar Proveedor');
		$bSPRV=$this->datasis->p_modbus($sprvbus,'proveed');

		$do = new DataObject('recep');
		$do->rel_one_to_many('seri', 'seri', array('recep'=>'recep'));

		$edit = new DataDetails($this->tits, $do);
		$edit->back_url = site_url($this->url.'filteredgrid');
		$edit->set_rel_title('itcasi','Rubro <#o#>');

		$edit->pre_process( 'insert','_pre_insert' );
		$edit->pre_process( 'update','_pre_update' );
		$edit->pre_process( 'delete','_pre_delete' );
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->recep = new inputField('N&uacute;mero', 'recep');
		$edit->recep->mode ='autohide';
		$edit->recep->when=array('show','modify');

		$edit->clipro = new inputField('Cliente/Proveedor', 'clipro');
		$edit->clipro->size=5;
		$edit->clipro->rule='callback_chclipro|required';
		$edit->clipro->type='inputhidden';
		$edit->clipro->readonly=true;
		$edit->clipro->append($bSPRV);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->type ='inputhidden';

		$tipo=array('E'=>'Entrega','R'=>utf8_encode('Recepción'));
		if($edit->_status=='show'){
			$edit->tipo = new dropdownField('Tipo','tipo');
			$edit->tipo->option('E','Entrega');
			$edit->tipo->option('R','Recepci&oacute;n');
		}else{
			$edit->tipo = new inputField('Tipo','tipo');
			$edit->tipo->rule = 'enum[R,E]';
			$edit->tipo->type ='inputhidden';
			$edit->tipo->insertValue='R';
		}

		$origen=array('scst'=>'Compra','sfac'=>'Factura');
		if($edit->_status=='show'){
			$edit->origen = new dropdownField('Or&iacute;gen', 'origen');
			$edit->origen->options($origen);
		}else{
			$edit->origen = new inputField('Or&iacute;gen', 'origen');
			$edit->origen->rule = 'enum[sfac,scst]';
			$edit->origen->insertValue='scst';
			$edit->origen->type ='inputhidden';
		}

		$tipo_ref=array('F'=>'F','FC'=>'F','NC'=>'D','D'=>'D');
		$edit->tipo_refe = new inputField('Tipo de referencia', 'tipo_refe');
		$edit->tipo_refe->rule = 'enum[FC,NC,F,D]';
		$edit->tipo_refe->type ='inputhidden';

		$edit->refe = new inputField('Referencia', 'refe');
		$edit->refe->rule='max_length[8]';
		$edit->refe->size=10;
		$edit->refe->maxlength=8;
		$edit->refe->append('N&uacute;mero de referencia cuando es ventas o n&uacute;mero de compra');

		$edit->fecha = new  dateonlyField('Fecha','fecha');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule= 'required|chfecha';
		$edit->fecha->size= 10;

		$edit->observa = new textAreaField('Observaci&oacute;n', 'observa');
		$edit->observa->cols = 60;
		$edit->observa->rows = 1;
		$edit->observa->style = 'width:100%;';

		//******************************
		//     Inicio del detalle
		//******************************
		$edit->itbarras = new inputField('(<#o#>) Barras', 'it_barras_<#i#>');
		$edit->itbarras->rule         ='trim|required|callback_chbarras[<#i#>]|strtoupper';
		$edit->itbarras->size         =20;
		$edit->itbarras->db_name      ='barras';
		$edit->itbarras->rel_id       ='seri';
		$edit->itbarras->autocomplete =false;

		$edit->itcodigo = new inputField('(<#o#>) C&oacute;digo', 'it_codigo_<#i#>');
		$edit->itcodigo->rule         ='trim|required';
		$edit->itcodigo->size         =10;
		$edit->itcodigo->db_name      ='codigo';
		$edit->itcodigo->rel_id       ='seri';
		$edit->itcodigo->autocomplete =false;
		$edit->itcodigo->type         ='inputhidden';

		$edit->itdescri = new inputField('(<#o#>) Descripci&oacute;n', 'it_descri_<#i#>');
		$edit->itdescri->rule         ='trim|required';
		$edit->itdescri->size         =40;
		$edit->itdescri->db_name      ='descrip';
		$edit->itdescri->rel_id       ='seri';
		$edit->itdescri->autocomplete =false;
		$edit->itdescri->type         ='inputhidden';

		$edit->itserial = new inputField('(<#o#>) Serial', 'it_serial_<#i#>');
		$edit->itserial->rule         ='trim|callback_chrepetido[<#i#>]|required';
		$edit->itserial->size         =20;
		$edit->itserial->db_name      ='serial';
		$edit->itserial->rel_id       ='seri';
		$edit->itserial->autocomplete =false;

		$edit->itcant = new inputField('(<#o#>) Cantidad', 'it_cant_<#i#>');
		$edit->itcant->rule         = 'trim|numeric|required|positive';
		$edit->itcant->size         = 10;
		$edit->itcant->db_name      = 'cant';
		$edit->itcant->rel_id       = 'seri';
		$edit->itcant->autocomplete = false;
		$edit->itcant->css_class    = 'inputnum';
		$edit->itcant->insertValue  = 1;
		//******************************
		//      Fin del detalle
		//******************************

		$status=$edit->get_from_dataobjetct('status');
		$edit->buttons('modify','save','undo','back','add_rel');
		$edit->build();

		$smenu['link']       = barra_menu('322');
		$data['smenu']       = $this->load->view('view_sub_menu', $smenu,true);
		$conten['jtipo']     = json_encode($tipo);
		$conten['jorigen']   = json_encode($origen);
		$conten['jtipos_ref']= json_encode($tipo_ref);
		$conten['form']     =& $edit;

		$data['content'] = $this->load->view('recep', $conten,true);
		$data['title']   = heading($this->tits.' Nro. '.$edit->recep->value);
		$data['head']    = $this->rapyd->get_head(); //style('vino/jquery-ui.css');
		$data['head']   .= script('jquery.js');
		$data['head']   .= script('jquery-ui.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js');
		$data['head']   .= script('plugins/jquery.floatnumber.js');
		$data['head']   .= script('plugins/jquery.meiomask.js');
		$data['head']   .= phpscript('nformat.js');
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
		$this->load->view('view_ventanas', $data);
	}

	//*****************************
	// Chequea que los productos
	//   esten en el documento
	//*****************************
	function chbarras($barras,$i){
		$cod_ind='it_codigo_'.$i;
		$can_ind='it_cant_'.$i;

		$codigo   = $this->input->post($cod_ind);
		$cana     = $this->input->post($can_ind);
		$tipo_ref = $this->input->post('tipo_refe');
		$refe     = $this->input->post('refe');
		$origen   = $this->input->post('origen');
		$clipro   = $this->input->post('clipro');

		if(empty($refe)) return true;

		if(!isset($this->it_detalle)){
			$this->it_detalle=array();
			if($origen=='scst'){
				$this->db->select(array('b.codigo','SUM(b.cantidad) AS cana'));
				$this->db->from('scst AS a');
				$this->db->join('itscst AS b','a.control=b.control');
				$this->db->where('a.proveed' ,$clipro);
				$this->db->where('a.tipo_doc',$tipo_ref);
				$this->db->where('a.numero'  ,$refe);
				$this->db->group_by('b.codigo');
				$query = $this->db->get();
			}elseif($origen=='sfac'){
				$this->db->select(array('b.codigoa AS codigo','SUM(b.cana) AS cana'));
				$this->db->from('sfac AS a');
				$this->db->join('sitems AS b','a.numero=b.numa AND a.tipo_doc=b.tipoa');
				$this->db->where('a.cod_cli' ,$clipro);
				$this->db->where('a.tipo_doc',$tipo_ref);
				$this->db->where('a.numero'  ,$refe);
				$this->db->group_by('b.codigoa');
				$query = $this->db->get();
			}else{
				$query=false;
			}
			if ($query !== false){
				foreach ($query->result() as $row){
					$this->it_detalle[$row->codigo]=$row->cana;
				}
			}
		}
		if(isset($this->it_detalle[$codigo]) && $this->it_detalle[$codigo]>=$cana){
			$this->it_detalle[$codigo]-=$cana;
			return true;
		}else{
			$this->it_detalle[$codigo]=0;
			$this->validation->set_message('chbarras', 'El art&iacute;culo en \'%s\' no esta contenido en el documento al que referencia');
			return false;
		}
	}

	//*****************************
	// Chequea que los proveedor
	//  o cliente sea correcto
	//*****************************
	function chclipro($clipro){
		$origen    = $this->input->post('origen');
		$tipo_ref  = $this->input->post('tipo_refe');
		$refe      = $this->input->post('refe');
		if(empty($refe)) return true;

		$dbclipro  = $this->db->escape($clipro);
		$dbtipo_ref= $this->db->escape($tipo_ref);
		$dbrefe    = $this->db->escape($refe);

		$rt=0;
		if($origen=='scst'){
			$mSQL="SELECT COUNT(*) FROM scst WHERE proveed=$dbclipro AND numero=$dbrefe AND tipo_doc=$dbtipo_ref";
			$rt=$this->datasis->dameval("SELECT COUNT(*) FROM scst WHERE proveed=$dbclipro AND numero=$dbrefe AND tipo_doc=$dbtipo_ref");
		}elseif($origen=='sfac'){
			$rt=$this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE cod_cli=$dbclipro AND numero=$dbrefe AND tipo_doc=$dbtipo_ref");
		}

		$this->validation->set_message('chclipro', 'El cliente o proveedor propuesto no es el mismo del documento referenciado');
		return ($rt>0)? true : false;
	}

	//*****************************
	// Chequea que no exista un
	//     serial repetido
	//*****************************
	function chrepetido($serial,$i){
		$this->validation->set_message('chrepetido', 'Hay un serial repetido para el mismo producto');
		$cod_ind='it_codigo_'.$i;
		$codigo = $this->input->post($cod_ind);

		if(isset($this->serial_repetidos[$codigo])){
			if(in_array($serial,$this->serial_repetidos[$codigo])){
				return false;
			}else{
				$this->serial_repetidos[$codigo][]=$serial;
				return true;
			}
		}else{
			$this->serial_repetidos[$codigo][]=$serial;
			return true;
		}
	}

	function _pre_update($do){
		unset($this->serial_repetidos); //Para ahorrar memoria
		unset($this->it_detalle);       //Para ahorrar memoria

		return true;
	}

	function _pre_insert($do){
		unset($this->serial_repetidos); //Para ahorrar memoria
		unset($this->it_detalle);       //Para ahorrar memoria

		$nrecep= $this->datasis->fprox_numero('nrecep');
		$do->set('recep',$nrecep);
		return true;
	}

	function _pre_delete($do){
		return false;
	}

	function _valida($do){
		return false;
		$error  ='';
		$recep  =$do->get('recep');
		$tipo   =$do->get('tipo');
		$refe   =$do->get('refe');
		$origen =$do->get('origen');
		$refee  =$this->db->escape($refe);

		if(empty($recep)){
			$ntransac = $this->datasis->fprox_numero('nrecep');
			$do->set('recep',$ntransac);
			$do->pk    =array('recep'=>$ntransac);
		}

		//se trae cliente y proveedor depende del numero
		//if($origen=='scst'){
		//	$clipro=$this->datasis->dameval("SELECT proveed FROM scst WHERE control=$refee");
		//}elseif($origen=='sfac'){
		//	$clipro=$this->datasis->dameval("SELECT cod_cli FROM sfac  WHERE numero=$refee AND tipo_doc='F'");
		//}
		//$do->set('clipro',$clipro);

		/*INICIO VALIDA ORIGEN=SFAC Y ENTREGADO
		se trae las cantidad disponibles a despachar por factura
		*/
		$sface=array();
		if($origen=='sfac' && $tipo=='E'){
			$query="SELECT codigo,SUM(cant) cant FROM (
				SELECT codigoa codigo,desca descrip, cana cant
				FROM sitems a WHERE numa=$refee
				UNION ALL 
				SELECT b.codigo,b.descrip,-1*b.cant 
				FROM seri b 
				JOIN recep c ON b.recep=c.recep
				WHERE c.refe=$refee AND c.origen='sfac' AND c.recep<>'$recep'
			)t 
			GROUP BY codigo";
			$sface=$this->datasis->consularray($query);
		}
		/*FIN VALIDA ORIGEN=SFAC Y ENTREGADO*/

		/*INICIO VALIDA ORIGEN=SFAC Y DEVUELVE
		se trae todos los item de la factura con las cantidades entregadas
		*/
		$sfac=array();$sfacs=array();
		if($origen=='sfac' && $tipo=='R'){
			$query="
			SELECT codigo,SUM(cant) cant FROM (
			SELECT codigoa codigo,desca descrip, 0 cant
			FROM sitems a WHERE numa=$refee
			UNION ALL 
			SELECT b.codigo,b.descrip,b.cant 
			FROM seri b 
			JOIN recep c ON b.recep=c.recep
			WHERE c.refe=$refee AND c.origen='sfac' AND c.recep<>'$recep'
			)t 
			GROUP BY codigo";
			$sfac=$this->datasis->consularray($query);

			$query="
			SELECT b.codigo,b.serial
			FROM seri b 
			JOIN recep c ON b.recep=c.recep
			WHERE c.refe=$refee AND c.origen='sfac' AND c.recep<>'$recep'";
			$sfacs=$this->datasis->consularray($query);
		}
		/*FIN VALIDA ORIGEN=SFAC Y DEVUELVE*/

		/*INICIO VALIDA ORIGEN=SCST Y DEVUELVE
		se trae todos los Las cantidades recibidas*/
		$scst=array();$scsts=array();
		if($origen=='scst' && $tipo=='E'){
			$query="
			SELECT b.codigo,SUM(b.cant) cant 
			FROM seri b 
			JOIN recep c ON b.recep=c.recep
			WHERE c.refe=$refee AND c.origen='scst' AND c.recep<>'$recep'
			GROUP BY codigo";
			$scst=$this->datasis->consularray($query);

			//se trae los seriales recibidos para la recepcion 
			$query="
			SELECT b.codigo,b.serial
			FROM seri b 
			JOIN recep c ON b.recep=c.recep
			WHERE c.refe=$refee AND c.origen='scst' AND c.recep<>'$recep'";
			$scst=$this->datasis->consularray($query);
		}
		/*FIN VALIDA ORIGEN=Scst Y DEVUELVE*/

		$se=array();$sinv=0;
		for($i=0;$i < $do->count_rel('seri');$i++){
			$codigo =$do->get_rel('seri','codigo',$i);
			$barras =$do->get_rel('seri','barras',$i);
			$serial =$do->get_rel('seri','serial',$i);
			$descrip=$do->get_rel('seri','descrip',$i);
			$cant   =$do->get_rel('seri','cant',$i);
			$codigoe=$this->db->escape($codigo);
			$barrase=$this->db->escape($barras);
			$seriale=$this->db->escape($serial);

			$where='';
			
			if(!empty($recep)){
				$recepe=$this->db->escape($recep);
				$where=" AND a.recep<>$recepe ";
			}

			if(!($cant>0))
			$error.=" La cantidad debe ser positiva para el codigo $codigo y barras $barras</br>";

			$t=$this->datasis->dameval("SELECT a.tipo FROM recep a JOIN seri b ON a.recep=b.recep WHERE codigo=$codigoe AND serial=$seriale $where ORDER BY a.fecha,a.recep desc LIMIT 1");

			if($tipo=='R'){
				if($t=='E' && empty($t))
				$error.="No se puede recibir debido a que esta recibido</br>";
			}elseif($tipo=='E' ){
				if($t!='R')
				$error.="No se puede entegar debido a que fue entregado o no ha sido recibido</br>";
			}else{
				$error.="ERROR. el tipo no es Entregar, ni Recibir</br>";
			}

			if(empty($error) && $tipo=='E'){
				$t=$this->datasis->dameval("SELECT SUM(IF(tipo='R',b.cant,-1*b.cant)) FROM recep a JOIN seri b ON a.recep=b.recep WHERE codigo=$codigoe AND serial=$seriale $where ORDER BY a.fecha desc LIMIT 1");
				if($cant>$t)
				$error.="La cantidad a entregar es mayor a la existente</br>";
			}

			if(empty($error))
			$sinv=$this->datasis->damerow("SELECT descrip,modelo,marca,clave,unidad,serial FROM sinv WHERE codigo=$codigoe AND barras=$barrase");

			if(count($sinv)>0){
				if($sinv['serial']=='S' && empty($serial)){
					$error.="El serial es obligatorio para el codigo $codigo y barras $barras</br>";
				}else{
					if(strlen($serial)>0)
					$do->set_rel('seri','cant',1,$i);

					if(in_array($codigo.$barras.$serial.$cant,$se)){
						$error.="El Serial $serial ya existe para el codigo $codigo y barras $barras</br>";
					}else{
						$se[]=$codigo.$barras.$serial;
					}
				}
			}else{
				$error.="El Codigo $codigo y barras $barras no existe.</br>";
			}

			/*INICIO VALIDA ORIGEN=SFAC Y ENTREGADO*/

			if($origen=='sfac' && $tipo=='E'){
				echo "aqui";
				if(array_key_exists($codigo,$sface)){

					if($cant>$sface[$codigo])
						$error.="ERROR. la cantidad a despachar del producto $codigo es mayor a la disponible ".nformat($sface[$codigo])." por despachar ";
				}else{
					$error.="ERROR. el producto ($codigo) $descrip no pertenece  la factura $refe</br>";
				}
			}
			/*FIN VALIDA ORIGEN=SFAC Y ENTREGADO*/

			/*INICIO VALIDA ORIGEN=SFAC Y DEVUELVE
			chequea que cada codigo ingresado pertenzca a la factura a devolver*/

			if($origen=='sfac' && $tipo=='R'){
			//print_r($sfac);
				if(array_key_exists($codigo,$sfac)){
					if($cant>$sfac[$codigo])
					$error.="ERROR. la cantidad a devolver del producto $codigo es mayor a la entregada para la factura $refee ".nformat($sface[$codigo])." por despachar ";
				}else{
					$error.="ERROR. el producto ($codigo) $descrip no pertenece  la factura $refe</br>";
				}
				//chequea que el serial ingresado pertenezca a la factura a devolver
				if(!(in_array($serial,$sfacs))){
					$error.="ERROR. el producto ($codigo) $descrip  serial $serial no pertenece  la factura $refe</br>";
				}
			}

			/*FIN VALIDA ORIGEN=SFAC Y DEVUELVE*/

			/*INICIO VALIDA ORIGEN=SCST Y DEVUELVE
			chequea que cada codigo ingresado pertenzca a la factura a devolver*/
			if($origen=='scst' && $tipo=='E'){
				if(array_key_exists($codigo,$scst)){
					if($cant>$scst[$codigo])
					$error.="ERROR. la cantidad a devolver del producto $codigo es mayor a la recibida para la factura $refee ".nformat($scst[$codigo])." ";
				}else{
					$error.="ERROR. el producto ($codigo) $descrip no pertenece a la recepcion de la factura $refe</br>";
				}
				//chequea que el serial ingresado pertenezca a la factura a devolver
				if(!(in_array($serial,$scst))){
					$error.="ERROR. el producto ($codigo) $descrip  serial $serial no pertenece  la factura $refe recibida</br>";
				}
			}
			/*FIN VALIDA ORIGEN=SCST Y DEVUELVE*/
		}

		if(!empty($error)){
			$do->error_message_ar['pre_ins']="<div class='alert'>".$error."</div>";
			$do->error_message_ar['pre_upd']="<div class='alert'>".$error."</div>";
			return false;
		}else{
			$do->set('estampa', 'CURDATE()', FALSE);
			$do->set('user', $this->session->userdata('usuario'));
			//GUARDA EN SNOT E ITSNOT
		}
	}

	function crea_snot($do){
		$refe2   = $do->get('refe2');
		$refe    = $do->get('refe');
		$fecha   = $do->get('fecha');
		$clipro  = $do->get('clipro');
		$origen  = $do->get('origen');
		$recep   = $do->get('recep');
		$tipo    = $do->get('tipo');
		$refee   = $this->db->escape($refe);
		$fechae  = $this->db->escape($fecha);
		$cliproe = $this->db->escape($clipro);

		/*CREA SNOT E ITSNOT CUANDO ES ENTREGA DE FACTURA*/
		if($origen=='sfac' && $tipo=='E'){
			$sfac  =$this->datasis->damerow("SELECT fecha,almacen,nombre FROM sfac WHERE numero=$refee AND tipo_doc='F'");
			if(empty($refe2)){
				$refe2 = $this->datasis->fprox_numero('nsnot');
				$query="INSERT INTO snot (`precio`,`numero`,`fecha`,`factura`,`cod_cli`,`fechafa`,`nombre`,`almaorg`,`almades`)
				VALUES (0,'$refe2',$fechae,$refee,$cliproe,'".$sfac['fecha']."','".$sfac['nombre']."','".$sfac['almacen']."','".$sfac['almacen']."')";
				$this->db->query($query);
			}else{
				$query="UPDATE snot  SET
				fecha=$fechae,
				factura=$refee,
				cod_cli=$cliproe,
				fechafa='".$sfac['fecha']."',
				nombre='".$sfac['nombre']."',
				almaorg='".$sfac['almacen']."',
				almades='".$sfac['almacen']."'
				";
				$this->db->query($query);
			}
			$this->db->query("DELETE FROM itsnot WHERE numero='$refe2'");
			$query="
			INSERT INTO itsnot (`numero`,`codigo`,`descrip`,`cant`,`saldo`,`entrega`,`factura`)
			SELECT '$refe2' numero,codigo,a.descrip,b.cana cant,(b.cana-SUM(a.cant)) saldo,SUM(a.cant) entrega,$refee 
			FROM recep c
			JOIN seri a ON a.recep=c.recep
			JOIN sitems b ON a.codigo=b.codigoa AND c.refe=b.numa 
			WHERE c.recep='$recep' AND b.tipoa='F' AND b.numa=$refee
			GROUP BY codigo";
			$this->db->query($query);
		}
		/*fin CREA SNOT */
	}

	function _post_insert($do){
		$this->crea_snot($do);
		$numero = $do->get('recep');
		logusu('recep',"Creo recepcion  $numero");
		//redirect($this->url."actualizar/$numero");
	}

	function _post_update($do){
		$this->crea_snot($do);
		$numero = $do->get('recep');
		logusu('recep'," Modifico recepcion $numero");
	}

	function _post_delete($do){
		$numero = $do->get('recep');
		logusu('recep'," Elimino recepcion $numero");
	}

	function instalar(){
		if (!$this->db->table_exists('recep')) {
			$mSQL = "CREATE TABLE `recep` (
				`recep` CHAR(8) NOT NULL DEFAULT '',
				`fecha` DATE NULL DEFAULT NULL,
				`clipro` VARCHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(100) NULL DEFAULT NULL,
				`refe` CHAR(8) NULL DEFAULT NULL,
				`tipo_refe` CHAR(2) NULL DEFAULT NULL,
				`tipo` CHAR(2) NULL DEFAULT NULL,
				`observa` TEXT NULL,
				`status` CHAR(2) NULL DEFAULT NULL,
				`user` VARCHAR(50) NULL DEFAULT NULL,
				`estampa` TIMESTAMP NULL DEFAULT NULL,
				`origen` VARCHAR(20) NULL DEFAULT NULL,
				PRIMARY KEY (`recep`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('view_clipro')) {
			$user=$this->db->username;
			$host=$this->db->hostname;

			$mSQL = "CREATE ALGORITHM = UNDEFINED DEFINER= `$user`@`$host` VIEW view_clipro AS
			SELECT 'Proveedor' tipo, b.proveed codigo, b.nombre, b.rif, concat_ws(' ', b.direc1, b.direc2, b.direc3) direc FROM `sprv` b
			UNION ALL
			SELECT 'Cliente' Cliente, a.cliente, a.nombre, a.rifci, concat_ws(' ',a.dire11, a.dire12, a.dire21, a.dire22) direc FROM `scli` a";
			$this->db->simple_query($mSQL);
		}

		$fields = $this->db->list_fields('seri');
		if(!in_array('cant',$fields)){
			$query="ALTER TABLE `seri`  ADD COLUMN `cant` DECIMAL(19,2) NOT NULL DEFAULT '1'";
			$this->db->simple_query($query);
		}
		if(!in_array('recep',$fields)){
			$query="ALTER TABLE `seri` ADD COLUMN `recep` CHAR(8) NOT NULL";
			$this->db->simple_query($query);
		}
		if(!in_array('frecep',$fields)){
			$query="ALTER TABLE `seri` ADD COLUMN `frecep` DATE NOT NULL";
			$this->db->simple_query($query);
		}
		if(!in_array('barras',$fields)){
			$query="ALTER TABLE `seri`  ADD COLUMN `barras` VARCHAR(50) NOT NULL";
			$this->db->simple_query($query);
		}
	}
}