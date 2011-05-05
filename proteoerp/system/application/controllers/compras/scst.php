<?php
class Scst extends Controller {

	function scst(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(201,1);
		$this->back_dataedit='compras/scst/datafilter';
	}

	function index() {
		redirect('compras/scst/datafilter');
	}

	function datafilter(){
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
		$filter->fechah->group='Fecha Emisi&oacute;n';
		$filter->fechad->group='Fecha Emisi&oacute;n';

		$filter->numero = new inputField('Factura', 'numero');
		$filter->numero->size=20;

		$filter->proveedor = new inputField('Proveedor','proveed');
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = 'proveed';
		$filter->proveedor->size=20;

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('compras/scst/dataedit/show/<#control#>','<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/COMPRA/<#control#>','Ver HTML',$atts);

		$grid = new DataGrid();
		$grid->order_by('fecha','desc');
		$grid->per_page = 30;

		$grid->column_orderby('Factura',$uri,'numero');
		$grid->column_orderby('Fecha','<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Vence','<dbdate_to_human><#vence#></dbdate_to_human>','vence','align=\'center\'');
		$grid->column_orderby('Proveedor','proveed','proveed');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('IVA'   ,'<nformat><#montoiva#></nformat>','montoiva','align=\'right\'');
		$grid->column_orderby('Monto' ,'<nformat><#montonet#></nformat>','montonet','align=\'right\'');
		$grid->column('Vista',$uri2,'align=\'center\'');

		//$grid->add("compras/agregar");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] =$grid->output;
		$data['filtro'] =$filter->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   =heading('Compras');
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
 		$this->rapyd->load('dataedit','datadetalle','fields','datagrid');

		$formato=$this->datasis->dameval('SELECT formato FROM cemp LIMIT 0,1');
		$qformato='%';
		for($i=1;$i<substr_count($formato, '.')+1;$i++) $qformato.='.%';
		$this->qformato=$qformato;

 		 	$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'descrip'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			//'retornar'=>array('codigo'=>'codigo<#i#>','precio1'=>'precio1<#i#>','precio2'=>'precio2<#i#>','precio3'=>'precio3<#i#>','precio4'=>'precio4<#i#>','iva'=>'iva<#i#>','pond'=>'costo<#i#>'),
			'retornar'=>array('codigo'=>'codigo<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Articulo');

 		//Script necesario para totalizar los detalles

		$fdepar = new dropdownField("ccosto", "ccosto");
		$fdepar->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
		$fdepar->status='create';
		$fdepar->build();
		$dpto=$fdepar->output;

		$dpto=trim($dpto);
		$dpto=preg_replace('/\n/i', '', $dpto);

 		$uri=site_url("/contabilidad/casi/dpto/");

 		$script='
 		function totalizar(){
 			monto=debe=haber=0;
 			amonto=$$(\'input[id^="monto"]\');
			for(var i=0; i<amonto.length; i++) {
    		valor=parseFloat(amonto[i].value);
    		if (isNaN(valor))
					valor=0.0;
				if (valor>0)
    			haber=haber+valor;
    		else{
    			valor=valor*(-1);
    			debe=debe+valor;
    		}
				$("haber").value=haber;
    		$("debe").value=debe;
				$("total").value=haber-debe;
			}
		}
		function departa(i){
			ccosto=$F(\'ccosto\'+i.toString())
			if (ccosto==\'S\'){
				//alert("come una matina");
				departamen=window.open("'.$uri.'/"+i.toString(),"buscardeparta","width=500,height=200,scrollbars=Yes,status=Yes,resizable=Yes,screenx=5,screeny=5,top="+ ((screen.height - 200) / 2) + ",left=" + ((screen.width - 500) / 2));
				departamen.focus();
				//new Insertion.Before(\'departa\'+i.toString(), \''.$dpto.'\')
			}
		}
		';

		$edit = new DataEdit('Compras','scst');

		$edit->post_process('insert','_guarda_detalle');
		$edit->post_process('update','_actualiza_detalle');
		$edit->post_process('delete','_borra_detalle');
		$edit->pre_process('delete','_pre_del');
		$edit->pre_process('insert','_pre_insert');

		$edit->back_url = $this->back_dataedit;

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->mode='autohide';
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

		$edit->monto  = new inputField("Monto US $", "mdolar");
		$edit->monto->size = 20;
		$edit->monto->css_class='inputnum';

		$numero=$edit->_dataobject->get('control');

		$detalle = new DataDetalle($edit->_status);

		//Campos para el detalle

		$detalle->db->select('codigo,descrip,cantidad,costo,importe');
		$detalle->db->from('itscst');
		$detalle->db->where("control='$numero'");

		$detalle->codigo = new inputField("C&oacute;digo", "codigo<#i#>");
		$detalle->codigo->size=10;
		$detalle->codigo->db_name='codigo';
		$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$detalle->codigo->readonly=TRUE;

		$detalle->descripcion = new inputField("Descripci&oacute;n", "descrip<#i#>");
		$detalle->descripcion->size=30;
		$detalle->descripcion->db_name='descrip';
		$detalle->descripcion->maxlength=12;

		$detalle->cantidad = new inputField("Cantidad", "cantidad<#i#>");
		$detalle->cantidad->size=10;
		$detalle->cantidad->db_name='cantidad';
		$detalle->cantidad->maxlength=60;
		$detalle->cantidad->css_class='inputnum';

		$detalle->precio = new inputField("Precio", "costo<#i#>");
		$detalle->precio->css_class='inputnum';
		$detalle->precio->onchange='totalizar()';
		$detalle->precio->size=20;
		$detalle->precio->db_name='costo';

		$detalle->importe = new inputField2("Importe", "importe<#i#>");
		$detalle->importe->db_name='importe';
		$detalle->importe->css_class='inputnum';
		$detalle->importe->size=20;

		//fin de campos para detalle

		$detalle->onDelete('totalizar()');
		$detalle->onAdd('totalizar()');
		$detalle->script($script);
		$detalle->style='width:110px';

		//Columnas del detalle
		$detalle->column("C&oacute;digo"     ,  "<#codigo#>");
		$detalle->column("Descripci&oacute;n", "<#descripcion#>");
		$detalle->column("Cantidad"   ,  "<#cantidad#>");
		$detalle->column("Precio"     , "<#precio#>");
		$detalle->column("Importe"    , "<#importe#>");

		$detalle->build();
		$conten["detalle"] = $detalle->output;

		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons("save", "undo","back");
		$edit->build();

		$smenu['link']=barra_menu('201');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$conten['form']  =&  $edit;
		
		$data['content'] = $this->load->view('view_compras', $conten,true);
		$data['head']    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Compras</h1>';
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
		$data['title']   =heading('Seleccione un departamento');
		$this->load->view('view_detalle', $data);
	}

	function _guarda_detalle($do) {
		$cant=$this->input->post('cant_0');
		$i=$o=0;
		while($o<$cant){
			if (isset($_POST["codigo$i"])){
				if($this->input->post("codigo$i")){

					$sql = "INSERT INTO itscst (fecha,numero,proveed,depo,codigo,descrip) VALUES(?,?,?,?,?,?)";

					//$haber=($this->input->post("monto$i") < 0)? $this->input->post("monto$i")*(-1) : 0;

					$llena=array(
							0=>$do->get('fecha'),
							1=>$do->get('numero'),
							2=>$do->get('proveed'),
							3=>$do->get('depo'),
							4=>$this->input->post("codigo$i"),
							5=>$this->input->post("descrip$i"),
							6=>$this->input->post("codigo$i"),

							);
					$this->db->query($sql,$llena);
				}
				$o++;
			}
			$i++;
		}
	}

	function autocomplete($campo,$cod=FALSE){
		if($cod!==false){
			$cod=$this->db->escape_like_str($cod);
			$qformato=$this->datasis->formato_cpla();
			$data['control']="SELECT control AS c1,fecha AS c2,numero AS c3,nombre AS c4 FROM scst WHERE control LIKE '$cod%' ORDER BY control DESC LIMIT 10";
			if(isset($data[$campo])){
				$query=$this->db->query($data[$campo]);
				if($query->num_rows() > 0){
					foreach($query->result_array() AS $row){
						echo $row['c1'].'|'.dbdate_to_human($row['c2']).'|'.$row['c3'].'|'.$row['c4']."\n";
					}
				}
			}
		}
	}

	function _actualiza_detalle($do){
		$this->_borra_detalle($do);
		$this->_guarda_detalle($do);
	}

	function _borra_detalle($do){
		$control=$do->get('control');
		$sql = "DELETE FROM itscst WHERE control='$control'";
		$this->db->query($sql);
	}

	function _pre_del($do){
		$codigo=$do->get('comprob');
		$chek =   $this->datasis->dameval("SELECT COUNT(*) FROM cpla WHERE codigo LIKE '$codigo.%'");
		$chek +=  $this->datasis->dameval("SELECT COUNT(*) FROM itcasi WHERE cuenta='$codigo'");

		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Plan de Cuenta tiene derivados o movimientos';
			return False;
		}
		return True;
	}

	function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
		$query  =$this->db->query($sql);
		$transac=$this->db->insert_id();

		$sql    = 'INSERT INTO nscst (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
		$query  =$this->db->query($sql);
		$control =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
		$do->set('control', $control);
		$do->set('transac', $transac);
		$do->set('estampa', 'CURDATE()', FALSE);
		$do->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->set('usuario', $this->session->userdata('usuario'));
	}
}