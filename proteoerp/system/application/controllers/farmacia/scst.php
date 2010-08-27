<?php
class Scst extends Controller {

	function scst(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(201,1);
	}

	function index() {
		redirect('farmacia/scst/datafilter');
	}

	function datafilter(){
		$this->rapyd->set_connection('farmax');
		$this->rapyd->load_db();

		$this->rapyd->load("datagrid","datafilter");
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

		$filter = new DataFilter("Filtro de Compras");
		$filter->db->select=array('numero','fecha','vence','nombre','montoiva','montonet','proveed','control');
		$filter->db->from('scst');

		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";

		//$filter->fecha_recep = new dateonlyField("Fecha Recepci&oacute;n", "fecha",'d/m/Y');
		//$filter->fecha_recep->clause  =$filter->fecha->clause="where";
		//$filter->fecha_recep->db_name =$filter->fecha->db_name="recep";
		//$filter->fecha_recep->insertValue = date("Y-m-d"); 
		//$filter->fecha_recep->size=10;
		//$filter->fecha_recep->operator="=";
		//$filter->fechah->group="Fecha Recepci&oacute;n";
		//$filter->fechad->group="Fecha Recepci&oacute;n";

		$filter->numero = new inputField("Factura", "numero");
		$filter->numero->size=20;

		$filter->proveedor = new inputField("Proveedor", "proveed");
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = "proveed";
		$filter->proveedor->size=20;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('farmacia/scst/dataedit/show/<#control#>','<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/COMPRA/<#control#>',"Ver HTML",$atts);

		$grid = new DataGrid();
		$grid->order_by("fecha","desc");
		$grid->per_page = 15;

		$grid->column_orderby("Factura",$uri,'control');
		$grid->column_orderby("Fecha"  ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
		$grid->column_orderby("Vence"  ,"<dbdate_to_human><#vence#></dbdate_to_human>",'vence',"align='center'");
		$grid->column_orderby("Nombre" ,"nombre",'nombre');
		$grid->column_orderby("IVA"    ,"montoiva" ,'montoiva' ,"align='right'");
		$grid->column_orderby("Monto"  ,"montonet" ,'montonet',"align='right'");
		$grid->column_orderby("Control",'pcontrol' ,'pcontrol',"align='right'");

		$grid->add("compras/agregar");
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

		$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
		$this->rapyd->uri->keep_persistence();

		$uri=site_url("/contabilidad/casi/dpto/");

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

		$edit = new DataEdit("Compras","scst");

		$edit->back_url = "farmacia/scst/datafilter/";

		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 10;

		$edit->vence = new DateonlyField("Vence", "vence","d/m/Y");
		$edit->vence->insertValue = date("Y-m-d");
		$edit->vence->size = 10;

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 15;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;

		$edit->proveedor = new inputField("Proveedor", "proveed");
		$edit->proveedor->size = 10;
		$edit->proveedor->maxlength=5;

		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;

		$edit->cfis = new inputField("C.fis", "nfiscal");
		$edit->cfis->size = 15;
		$edit->cfis->maxlength=8;

		$edit->almacen = new inputField("Almacen", "depo");
		$edit->almacen->size = 15;
		$edit->almacen->maxlength=8;

		$edit->tipo = new dropdownField("Tipo", "tipo_doc");  
		$edit->tipo->option("FC","FC");
		$edit->tipo->option("NC","NC");
		$edit->tipo->option("NE","NE");
		$edit->tipo->rule = "required";
		$edit->tipo->size = 20;
		$edit->tipo->style='width:150px;';

		$edit->peso  = new inputField2("Peso", "peso");
		$edit->peso->size = 20;
		$edit->peso->css_class='inputnum';

		$edit->orden  = new inputField("Orden", "orden");
		$edit->orden->size = 15;

		$edit->credito  = new inputField("Cr&eacute;dito", "credito");
		$edit->credito->size = 20;
		$edit->credito->css_class='inputnum';

		$edit->subt  = new inputField("Subt", "montotot");
		$edit->subt->size = 20;
		$edit->subt->css_class='inputnum';

		$edit->iva  = new inputField("IVA", "montoiva");
		$edit->iva->size = 20;
		$edit->iva->css_class='inputnum';

		$edit->total  = new inputField("Total", "montonet");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';

		$edit->anticipo  = new inputField("Anticipo", "anticipo");
		$edit->anticipo->size = 20;
		$edit->anticipo->css_class='inputnum';

		$edit->contado  = new inputField("Contado", "inicial");
		$edit->contado->size = 20;
		$edit->contado->css_class='inputnum';

		$edit->rislr  = new inputField("R.ISLR", "reten");
		$edit->rislr->size = 20;
		$edit->rislr->css_class='inputnum';

		$edit->riva  = new inputField("R.IVA", "reteiva");
		$edit->riva->size = 20;
		$edit->riva->css_class='inputnum';

		$edit->pcontrol  = new inputField('Control', 'pcontrol');
		$edit->pcontrol->size = 12;

		$edit->monto  = new inputField("Monto US $", "mdolar");
		$edit->monto->size = 20;
		$edit->monto->css_class='inputnum';

		$numero =$edit->_dataobject->get('control');
		$proveed=$this->db->escape($edit->_dataobject->get('proveed'));

		//Campos para el detalle
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
		$detalle->column("Precio"            ,"<#ultimo#>"  ,"align='right'");
		$detalle->column("Importe"           ,"<#importe#>" ,"align='right'");
		$detalle->column("Acciones "         ,"<exissinv><#sinv#>|<#dg_row_id#></exissinv>","bgcolor='#D7F7D7' align='center'");
		$detalle->build();
		//echo $detalle->db->last_query();

		$script='
		function pcrear(id){
			var pasar=["barras","descrip","ultimo","iva","codigo","pond"];
			var url  = "'.site_url('inventario/sinv/dataedit/create').'";
			form_virtual(pasar,id,url);
		}

		function pasig(id){
			var pasar=["barras","proveed"];
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

		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);
		$accion="javascript:window.location='".site_url('farmacia/scst/cargar'.$edit->pk_URI())."'";
		$pcontrol=$edit->_dataobject->get('pcontrol');
		if(is_null($pcontrol)) $edit->button_status('btn_cargar','Cargar',$accion,'TR','show');
		$edit->buttons('save','undo','back');

		$edit->script($script,'show');
		$edit->build();

		$smenu['link']=barra_menu('201');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_farmax_compras', $conten,true); 
		$data['head']    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Compras Descargadas</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dpto() {
		$this->rapyd->load("dataform");
		$campo='ccosto'.$this->uri->segment(4);
		$script='
		function pasar(){
			if($F("departa")!="-!-"){
				window.opener.document.getElementById("'.$campo.'").value = $F("departa");
				window.close();
			}else{
				alert("Debe elegir un departamento");
			}
		}';

		$form = new DataForm('');
		$form->script($script);
		$form->fdepar = new dropdownField("Departamento", "departa");
		$form->fdepar->option('-!-','Seleccion un departamento');
		$form->fdepar->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
		$form->fdepar->onchange='pasar()';
		$form->build_form();

		$data['content'] =$form->output;
		$data['head']    =script('prototype.js').$this->rapyd->get_head();
		$data['title']   ='<h1>Seleccione un departamento</h1>';
		$this->load->view('view_detalle', $data);
	}

	function asignarfiltro(){
		$this->rapyd->load("datagrid","datafilter");
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

		$filter = new DataFilter('Filtro de asignaci&oacute;n de productos','farmaxasig');

		$filter->proveedor = new inputField("Proveedor", "proveed");
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = "proveed";
		$filter->proveedor->size=20;

		$filter->buttons("reset","search");
		$filter->build();
 
		$grid = new DataGrid();
		$grid->order_by("id","desc");
		$grid->per_page = 15;

		$uri=anchor('farmacia/scst/asignardataedit/show/<#id#>','<#id#>');
		$grid->column_orderby('Id'       ,$uri     ,'id'     );
		$grid->column_orderby('Proveedor','proveed','proveed');
		$grid->column_orderby('Barras'   ,'barras' ,'barras' );
		$grid->column_orderby('Mapeado a','abarras','abarras');

		$grid->add("farmacia/scst/asignardataedit/create");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='<h1>Reasignar C&oacute;digo</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function asignardataedit(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load("dataedit");

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'barras' =>'C&oacute;digo barras',
				'descrip'=>'descrip'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			'retornar'=>array('codigo' =>'abarras'),
			//'where'   =>'LENGTH(barras)>0',
			'titulo'  =>'Buscar Art&iacute;culo');
		$boton=$this->datasis->modbus($modbus);

		$edit = new DataEdit('Reasignaciones de c&oacute;digo','farmaxasig');
		$edit->back_url = "farmacia/scst/asignarfiltro";

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

		$edit->abarras = new inputField('Barras en sistema','abarras');
		$edit->abarras->rule = 'required|trim|callback_siexiste';
		$edit->abarras->size = 50;
		$edit->abarras->maxlength=250;
		$edit->abarras->append($boton);

		$edit->buttons('modify','save','delete','undo','back');
		$edit->build();

		$data['content'] =$edit->output;
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
		$data['content'] = $this->_cargar($control).br().anchor('farmacia/scst/dataedit/show/'.$control,'Regresar');
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = '<h1>Cargar compra '.$control.'</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function _cargar($control){
		$control =$this->db->escape($control);
		$farmaxDB=$this->load->database('farmax',TRUE);
		$farmaxdb=$farmaxDB->database;
		$localdb =$this->db->database;
		$retorna='';

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

					$row=$query->row_array();
					$row['control']=$lcontrol;
					$row['transac']=$transac;
					unset($row['pcontrol']);
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
