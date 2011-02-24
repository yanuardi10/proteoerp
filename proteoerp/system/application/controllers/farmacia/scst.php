<?php require_once(APPPATH.'/controllers/inventario/consultas.php');

class Scst extends Controller {

	function scst(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(201,1);
	}

	function index() {
		redirect('farmacia/scst/datafilter');
	}

	function datafilter(){
		$this->rapyd->set_connection('farmax');
		$this->rapyd->load_db();

		$this->rapyd->load('datagrid','datafilter');
		$this->rapyd->uri->keep_persistence();

		$atts = array(
				'width'      => '800',
				'height'     => '600',
				'scrollbars' => 'yes',
				'status'     => 'yes',
				'resizable'  => 'yes',
				'screenx'    => '0',
				'screeny'    => '0'
			);

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');

		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter('Filtro de Compras');
		$filter->db->select=array('numero','fecha','vence','nombre','montoiva','montonet','proveed','control');
		$filter->db->from('scst');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>='; 
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('Factura', 'numero');
		$filter->numero->size=20;

		$filter->proveedor = new inputField('Proveedor', 'proveed');
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = 'proveed';
		$filter->proveedor->size=20;

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('farmacia/scst/dataedit/show/<#control#>','<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/COMPRA/<#control#>','Ver HTML',$atts);

		$grid = new DataGrid();
		$grid->order_by('fecha','desc');
		$grid->per_page = 15;

		$grid->column_orderby('Factura',$uri,'control');
		$grid->column_orderby('Fecha'  ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha',"align='center'");
		$grid->column_orderby('Vence'  ,'<dbdate_to_human><#vence#></dbdate_to_human>','vence',"align='center'");
		$grid->column_orderby('Nombre' ,'nombre','nombre');
		$grid->column_orderby('IVA'    ,'montoiva' ,'montoiva',"align='right'");
		$grid->column_orderby('Monto'  ,'montonet' ,'montonet',"align='right'");
		$grid->column_orderby('Control','pcontrol' ,'pcontrol',"align='right'");

		//$grid->add('compras/agregar');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] =$filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='<h1>Compras</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->set_connection('farmax');
		$this->rapyd->load_db();

		$this->rapyd->load('dataedit','datadetalle','fields','datagrid');
		$this->rapyd->uri->keep_persistence();

		function exissinv($cen,$id=0){
			if(empty($cen)){
				$id--;
				$rt =form_button('create' ,'Crear','onclick="pcrear('.$id.');"');
				$rt.=form_button('asignar','Asig.','onclick="pasig('.$id.');"');
			}else{
				$rt='--';
			}
			return $rt;
		}

		$edit = new DataEdit('Compras','scst');
		$edit->back_url = 'farmacia/scst/datafilter/';

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->mode='autohide';
		$edit->fecha->size = 10;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 15;
		$edit->numero->rule= 'required';
		$edit->numero->mode= 'autohide';
		$edit->numero->maxlength=8;

		$edit->proveedor = new inputField('Proveedor', 'proveed');
		$edit->proveedor->size = 10;
		$edit->proveedor->maxlength=5;

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;

		$edit->almacen = new inputField('Almac&eacute;n', 'depo');
		$edit->almacen->size = 15;
		$edit->almacen->maxlength=8;

		$edit->tipo = new dropdownField('Tipo', 'tipo_doc');
		$edit->tipo->option('FC','FC');
		$edit->tipo->rule = 'required';
		$edit->tipo->size = 20;
		$edit->tipo->style='width:150px;';

		$edit->subt  = new inputField('Sub-total', 'montotot');
		$edit->subt->size = 20;
		$edit->subt->css_class='inputnum';

		$edit->iva  = new inputField('Impuesto', 'montoiva');
		$edit->iva->size = 20;
		$edit->iva->css_class='inputnum';

		$edit->total  = new inputField('Total global', 'montonet');
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';

		$edit->pcontrol  = new inputField('Control', 'pcontrol');
		$edit->pcontrol->size = 12;

		$numero =$edit->_dataobject->get('control');
		$proveed=$this->db->escape($edit->_dataobject->get('proveed'));

		$atts = array(
			'width'     => '250',
			'height'    => '250',
			'scrollbars'=> 'no',
			'status'    => 'no',
			'resizable' => 'no',
			'screenx'   => "'+((screen.availWidth/2)-175)+'",
			'screeny'   => "'+((screen.availHeight/2)-175)+'"
		);
		$llink=anchor_popup('farmacia/scst/reasignaprecio/modify/<#id#>', '<b><#precio1#></b>', $atts);

		//Campos para el detalle
		$this->_autoasignar($numero);
		$tabla=$this->db->database;
		$detalle = new DataGrid('');
		$select=array('a.*','a.codigo AS barras','a.costo AS pond','COALESCE( b.codigo , c.abarras) AS sinv');
		$detalle->db->select($select);
		$detalle->db->from('itscst AS a');
		$detalle->db->where('a.control',$numero);
		$detalle->db->join($tabla.'.sinv AS b','a.codigo=b.codigo','LEFT');
		$detalle->db->join($tabla.'.farmaxasig AS c',"a.codigo=c.barras AND c.proveed=$proveed",'LEFT');
		$detalle->use_function('exissinv');
		$detalle->column("Barras"            ,"<#codigo#>" );
		$detalle->column("Descripci&oacute;n","<#descrip#>");
		$detalle->column("Cantidad"          ,"<#cantidad#>","align='right'");
		$detalle->column("PVP"               ,$llink  ,"align='right'");
		$detalle->column("Costo"             ,"<#ultimo#>"  ,"align='right'");
		$detalle->column("Importe"           ,"<#importe#>" ,"align='right'");
		$detalle->column("Acciones "         ,"<exissinv><#sinv#>|<#dg_row_id#></exissinv>","bgcolor='#D7F7D7' align='center'");
		$detalle->build();
		//echo $detalle->db->last_query();

		$script='
		function pcrear(id){
			var pasar=["barras","descrip","ultimo","iva","codigo","pond","precio1","precio2","precio3","precio4"];
			var url  = "'.site_url('farmacia/sinv/dataedit/create').'";
			form_virtual(pasar,id,url);
		}

		function pasig(id){
			var pasar=["barras","proveed","descrip"];
			var url  = "'.site_url('farmacia/scst/asignardataedit/create').'";
			form_virtual(pasar,id,url);
		}

		function form_virtual(pasar,id,url){
			var data='.json_encode($detalle->data).';
			var w = window.open("'.site_url('farmacia/scst/dummy').'","asignar","width=800,height=600,scrollbars=Yes,status=Yes,resizable=Yes,screenx="+((screen.availWidth/2)-400)+",screeny="+((screen.availHeight/2)-300)+"");

			var fform  = document.createElement("form");
			fform.setAttribute("target", "asignar");
			fform.setAttribute("action", url );
			fform.setAttribute("method", "post");

			for(i=0;i<pasar.length;i++){
				Val=eval("data[id]."+pasar[i]);
				iinput = document.createElement("input");
				iinput.setAttribute("type", "hidden");
				iinput.setAttribute("name", pasar[i]);
				iinput.setAttribute("value", Val);
				fform.appendChild(iinput);
			}

			var cuerpo = document.getElementsByTagName("body")[0];
			cuerpo.appendChild(fform);
			fform.submit();
			w.focus();
			cuerpo.removeChild(fform);
		}';

		$edit->detalle=new freeField('detalle', 'detalle',$detalle->output);
		$accion="javascript:window.location='".site_url('farmacia/scst/cargar'.$edit->pk_URI())."'";
		$pcontrol=$edit->_dataobject->get('pcontrol');
		if(is_null($pcontrol)) $edit->button_status('btn_cargar','Cargar',$accion,'TR','show');
		$edit->buttons('save','undo','back');

		$edit->script($script,'show');
		$edit->build();

		$this->rapyd->jquery[]='$("#dialog").dialog({
			autoOpen: false,
			show: "blind",
			hide: "explode"
		});

		$( "#opener" ).click(function() {
			$( "#dialog" ).dialog( "open" );
			return false;
		});';

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_farmax_compras', $conten,true); 
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = '<h1>Compras Descargadas</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function reasignaprecio(){
		$this->rapyd->set_connection('farmax');
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Cambios de precios','itscst');
		$edit->descrip  = new inputField('Descripci&oacute;n', 'descrip');
		$edit->descrip->mode = 'autohide';

		for($i=1;$i<5;$i++){
			$obj='precio'.$i;
			$edit->$obj = new inputField('Precio '.$i, $obj);
			$edit->$obj->css_class='inputnum';
			$edit->$obj->rule ='numeric';
			$edit->$obj->size = 10;
		}

		$edit->buttons('modify','save');
		$edit->build();
		$this->rapyd->jquery[]='$(window).unload(function() { window.opener.location.reload(); });';
		$data['content'] =$edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='';
		$this->load->view('view_ventanas_sola', $data);
	}


	function asignarfiltro(){
		$this->rapyd->load('datagrid','datafilter');
		$this->rapyd->uri->keep_persistence();

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');

		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter('Filtro de asignaci&oacute;n de productos');
		$select=array('a.proveed','a.abarras','a.barras','c.nombre','b.descrip','a.id','b.codigo');
		$filter->db->select($select);
		$filter->db->from('farmaxasig AS a');
		$filter->db->join('sinv AS b','a.abarras=b.codigo');
		$filter->db->join('sprv AS c','a.proveed=c.proveed');

		$filter->proveedor = new inputField('Proveedor', 'proveed');
		$filter->proveedor->db_name='a.proveed';
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = 'proveed';
		$filter->proveedor->size=20;

		$filter->barras = new inputField('C&oacute;digo seg&uacute;n proveedor', 'barras');
		$filter->barras->db_name='a.barras';
		$filter->barras->append('C&oacute;digo del producto seg&uacute;n el proveedor');

		$filter->abarras = new inputField('C&oacute;digo local', 'abarras');
		$filter->abarras->db_name='a.abarras';

		$filter->buttons('reset','search');
		$filter->build();

		$grid = new DataGrid();
		$grid->order_by('id','desc');
		$grid->per_page = 15;

		$uri=anchor('farmacia/scst/asignardataedit/show/<#id#>','<#barras#>');
		$grid->column_orderby('Proveedor','(<#proveed#>) <#nombre#>' ,'proveed');
		$grid->column_orderby('C&oacute;digo seg&uacute;n proveedor' ,$uri,'barras');
		$grid->column_orderby('Mapeado a','(<#abarras#>) <#descrip#>','abarras');

		$grid->add('farmacia/scst/asignardataedit/create');
		$grid->build();
		echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='<h1>Reasignar C&oacute;digo</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function _autoasignar($control=null){
		if(!empty($control)){
			$dbcontrol=$this->db->escape($control);

			$tabla    = $this->db->database;
			$dbfarmax = $this->load->database('farmax', TRUE);

			$query = $dbfarmax->query('SELECT proveed FROM scst WHERE control='.$dbcontrol);
			if ($query->num_rows() > 0){
				$row = $query->row_array();
				$proveed=$row['proveed'];
			}
			$dbproveed=$this->db->escape($proveed);

			$mSQL="SELECT `a`.`codigo` AS barras FROM (`itscst` AS a) WHERE `a`.`control` = $dbcontrol";
			$query = $dbfarmax->query($mSQL);
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					$qquery=consultas::_gconsul('SELECT codigo  FROM sinv',$row->barras,array('codigo','barras','alterno'));
					if($qquery!==false){
						$rrow   = $qquery->row_array();
						$codigo = $rrow['codigo'];
						$data = array('proveed' => $proveed, 'abarras' =>$rrow['codigo'] , 'barras' => $row->barras);

						$str = $this->db->insert_string('farmaxasig', $data);
						$str = str_replace('INSERT','INSERT IGNORE',$str);
						$this->db->simple_query($str);
					}
        
				}
			}
		}
	}

	function asignardataedit(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataedit','datagrid');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'barras' =>'C&oacute;digo barras',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo' =>'abarras'),
			//'where'   =>'LENGTH(barras)>0',
			'titulo'  =>'Buscar Art&iacute;culo');
		$boton=$this->datasis->modbus($modbus);

		$js='function pasacod(val) { $("#abarras").val(val) }';
		$edit = new DataEdit('Reasignaciones de c&oacute;digo','farmaxasig');
		$edit->back_url = 'farmacia/scst/asignarfiltro';

		$edit->proveedor = new inputField('Proveedor','proveed');
		$edit->proveedor->rule = 'trim|callback_sprvexits|required';
		$edit->proveedor->mode = 'autohide';
		$edit->proveedor->size = 10;
		$edit->proveedor->maxlength=50;

		$edit->barras = new inputField('Barras en el proveedor','barras');
		$edit->barras->rule = 'required|trim|callback_fueasignado|callback_noexiste';
		$edit->barras->mode = 'autohide';
		$edit->barras->size = 50;
		
		$edit->barras->maxlength=250;

		$edit->abarras = new inputField('Producto en sistema','abarras');
		$edit->abarras->rule = 'required|trim|callback_siexiste';
		$edit->abarras->size = 50;
		$edit->abarras->maxlength=250;
		$edit->abarras->append($boton);

		$edit->buttons('modify','save','delete','undo','back');

		$describus=$this->input->post('descrip');
		if($describus!==false){
			//print_r($patrones);
			$grid = new DataGrid('Productos similares a <b>'.$describus.'</b>');
			$grid->per_page = 10;
			$grid->db->select(array('codigo','descrip','precio1'));
			$grid->db->from('sinv');
			$grid->paged=false;

			$patrones = preg_split("/[\s,\-]+/", $describus);
			foreach($patrones AS $pat){
				if(strlen($pat)>3){
					$grid->db->like('descrip',$pat);
				}
			}
			$grid->db->limit(10);
			$url='<a onclick=\'pasacod("<#codigo#>")\'  href=\'#\'><#codigo#></a>';

			$grid->column('C&oacute;digo'     ,$url);
			$grid->column('Descripci&oacute;n','descrip');
			$grid->column('Precio 1'          ,'precio1' ,"align='right'");

			$grid->build();
			$tabla=($grid->recordCount>0)? $grid->output : 'No existe descripci&oacute;n semejante a <b>'.$describus.'</b>';

			$edit->script($js,'create');
			$edit->script($js,'modify');
		}else{
			$tabla='';
		}
		$edit->build();

		$this->rapyd->jquery[]='$(window).unload(function() { window.opener.location.reload(); });';
		$data['content'] =$edit->output.$tabla;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='<h1>Reasignar c&oacute;digo</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function sprvexits($proveed){
		$mSQL='SELECT COUNT(*) FROM sprv WHERE proveed='.$this->db->escape($proveed);
		$cana=$this->datasis->dameval($mSQL);
		if($cana==0){
			$error="El proveedor dado no exite";
			$this->validation->set_message('sprvexits',$error);
			return false;
		}
		return true;
	}

	function noexiste($barras){
		$mSQL='SELECT COUNT(*) FROM sinv WHERE codigo='.$this->db->escape($barras);
		$cana=$this->datasis->dameval($mSQL);
		if($cana!=0){
			$error="El c&oacute;digo de barras '$barras' existe en el iventario, la equivalencia se debe aplicar en un producto que no exista";
			$this->validation->set_message('noexiste',$error);
			return false;
		}
		return true;
	}

	function siexiste($barras){
		$mSQL='SELECT COUNT(*) FROM sinv WHERE codigo='.$this->db->escape($barras);
		$cana=$this->datasis->dameval($mSQL);
		if($cana==0){
			$error="El c&oacute;digo de barras '$barras' no existe en el iventario";
			$this->validation->set_message('siexiste',$error);
			return false;
		}
		return true;
	}

	function fueasignado($barras){
		$proveed=$this->db->escape($this->input->post('proveed'));
		$mSQL='SELECT COUNT(*) FROM farmaxasig WHERE barras='.$this->db->escape($barras).' AND proveed='.$proveed;
		$cana=$this->datasis->dameval($mSQL);
		if($cana>0){
			$error="El c&oacute;digo de barras '$barras' ya fue asignado a otro producto";
			$this->validation->set_message('fueasignado',$error);
			return false;
		}
		return true;
	}


	function cargar($control){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataform');

		$form = new DataForm("farmacia/scst/cargar/$control/process");

		$form->nfiscal = new inputField('Control F&iacute;scal', 'nfiscal');
		$form->nfiscal->rule = 'required|strtoupper';
		$form->nfiscal->rows = 10;

		$form->almacen = new  dropdownField ("Almac&eacute;n", "almacen");
		$form->almacen->option('','Seleccionar');
		$form->almacen->options("SELECT ubica,CONCAT_WS('-',ubica,ubides) AS val FROM caub WHERE gasto='N' and invfis='N' ORDER BY ubides");
		$form->almacen->rule = 'required';

		$form->dias = new inputField('D&iacute;as de cr&eacute;dito', 'dias','d/m/Y');
		$form->dias->insertValue = 21;
		$form->dias->rule = 'required|integer';
		$form->dias->size = 5;

		$form->submit('btnsubmit','Guardar');
		$form->build_form();

		if ($form->on_success()){
			$nfiscal= $form->nfiscal->newValue;
			$almacen= $form->almacen->newValue;
			$dias   = $form->dias->newValue;

			$data['content'] = $this->_cargar($control,$nfiscal,$almacen,$dias).br().anchor('farmacia/scst/dataedit/show/'.$control,'Regresar');
		}else{
			$data['content'] = $form->output;
		}

		$data['head']    = $this->rapyd->get_head();
		$data['title']   = '<h1>Cargar compra '.$control.'</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function _cargar($control,$nfiscal,$almacen,$dias){
		$control =$this->db->escape($control);
		$farmaxDB=$this->load->database('farmax',TRUE);
		$farmaxdb=$farmaxDB->database;
		$localdb =$this->db->database;
		$retorna ='';

		$sql ="SELECT COUNT(*) AS cana 
		  FROM ${farmaxdb}.itscst AS a 
		  LEFT JOIN ${localdb}.sinv AS b ON a.codigo=b.codigo 
		  LEFT JOIN ${localdb}.farmaxasig AS c ON a.codigo=c.barras AND c.proveed=a.proveed 
		WHERE a.control=$control AND b.codigo IS NULL AND c.abarras IS NULL";
		$query=$this->db->query($sql);
		if($query->num_rows()>0){
			$row=$query->row_array();
			if($row['cana']==0){
				$query=$farmaxDB->query("SELECT * FROM scst WHERE control=$control AND pcontrol IS NULL");

				if ($query->num_rows()==1){
					$lcontrol=$this->datasis->fprox_numero('nscst');
					$transac =$this->datasis->fprox_numero('ntransac');
					$contribu=$this->datasis->traevalor('CONTRIBUYENTE');
					$rif     =$this->datasis->traevalor('RIF');

					$row=$query->row_array();
					$numero=$row['numero'];
					$row['serie']   =$numero;
					$row['numero']  =substr($numero,-8);
					$row['control'] =$lcontrol;
					$row['transac'] =$transac;
					$row['nfiscal'] =$nfiscal;
					$row['credito'] =$row['montonet'];
					$row['anticipo']=0;
					$row['inicial'] =0;
					$row['estampa'] =date('Ymd');
					$row['hora']    =date('H:i:s');
					$row['usuario'] =$this->session->userdata('usuario');
					$row['depo']    =$almacen;
					$cd             =strtotime($row['fecha']);
					$row['vence']   =date('Y-m-d', mktime(0,0,0,date('m',$cd),date('d',$cd)+$dias,date('Y',$cd)));

					$mmsql="SELECT iva,SUM(montoiva) AS monto,SUM(importe) AS base FROM itscst WHERE control=$control GROUP BY iva";
					$m_iva=$farmaxDB->query($mmsql);
					$ivas=$this->datasis->ivaplica($row['fecha']);
					$tasa=$redutasa=$sobretasa=$exento=$basetasa=$baseredu=$baseadicio=0;

					foreach ($m_iva->result_array() as $ivarow){
						if($ivarow['iva']==$ivas['redutasa']){
							$redutasa  +=$ivarow['monto'];
							$baseredu  +=$ivarow['base'];
						}elseif($ivarow['iva']==$ivas['tasa']){
							$tasa      +=$ivarow['monto'];
							$basetasa  +=$ivarow['base'];
						}elseif($ivarow['iva']==$ivas['sobretasa']){
							$sobretasa +=$ivarow['monto'];
							$baseadicio+=$ivarow['base'];
						}elseif($ivarow['iva']==0){
							$exento    +=$ivarow['base'];
						}
					}
					$row['reducida'] =$redutasa;
					$row['tasa']     =$tasa;
					$row['sobretasa']=$sobretasa;
					$row['monredu']  =$redutasa;
					$row['montasa']  =$tasa;
					$row['monadic']  =$sobretasa;

					$row['ctotal']   =$row['montonet'];
					$row['cstotal']  =$row['montotot'];
					$row['cexento']  =$exento;
					$row['cimpuesto']=$redutasa+$tasa+$sobretasa;
					$row['cgenera']  =$basetasa;
					$row['civagen']  =$tasa;
					$row['cadicio']  =$baseadicio;
					$row['civaadi']  =$sobretasa;
					$row['creduci']  =$baseredu;
					$row['civared']  =$redutasa;

					unset($row['pcontrol']);
					if($contribu=='ESPECIAL' and strtoupper($rif[0])!='V'){
						$por_rete=$this->datasis->dameval('SELECT reteiva FROM sprv WHERE proveed='.$this->db->escape($row['proveed']));
						if($por_rete!=100){
							$por_rete=0.75;
						}else{
							$por_rete=$por_rete/100;
						}
						$row['reteiva']=round($row['montoiva']*$por_rete,2);
					}

					$mSQL[]=$this->db->insert_string('scst', $row);

					$itquery = $farmaxDB->query("SELECT * FROM itscst WHERE control=$control");
					foreach ($itquery->result_array() as $itrow){
						$itrow['control']=$lcontrol;
						unset($itrow['id']);
						$mSQL[]=$this->db->insert_string('itscst', $itrow);
					}
					foreach($mSQL AS $sql){
						$rt=$this->db->simple_query($sql);
						if(!$rt){ memowrite('scstfarma',$sql);}
					}
					$sql="UPDATE scst SET pcontrol='${lcontrol}' WHERE control=$control";
					$rt=$farmaxDB->simple_query($sql);
					if(!$rt) memowrite('farmaejec',$sql);

					$mSQL="UPDATE 
					  ${localdb}.itscst AS a
					  JOIN ${localdb}.farmaxasig AS b ON a.codigo=b.barras AND a.proveed=b.proveed
					  SET a.codigo=b.abarras
					WHERE a.control='$lcontrol'";
					$rt=$this->db->simple_query($mSQL);
					if(!$rt){ memowrite('farmaejec1',$sql);}

					$retorna='Compra guardada con el control '.anchor("compras/scst/dataedit/show/$lcontrol",$lcontrol);
				}else{
					$retorna="Al parecer la factura fue ya pasada";
				}
			}else{
				$retorna="No se puede pasar porque hay productos que no existen en inventario";
			}
		}else{
			$retorna="Error en la consulta";
		}
		return $retorna;
	}

	function dummy(){
		echo "<p aling='center'>Redirigiendo la p&aacute;gina</p>";
	}

	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `farmaxasig` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`proveed` VARCHAR(50) NOT NULL,
		`barras` VARCHAR(250) NOT NULL,
		`abarras` VARCHAR(250) NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE INDEX `proveed` (`proveed`, `barras`)
		)
		COMMENT='Tabla de equivalencias de productos'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";

		$this->db->simple_query($mSQL);
	}
}
