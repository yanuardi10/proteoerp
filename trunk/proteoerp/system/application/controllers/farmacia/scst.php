<?php
//compras
class Scst extends Controller {

	function scst(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(201,1);
		//$this->rapyd->set_connection('farmax');
		//$this->rapyd->load_db();
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
		$filter->fechah->group="Fecha Recepci&oacute;n";
		$filter->fechad->group="Fecha Recepci&oacute;n";

		//$filter->fecha_recep = new dateonlyField("Fecha Recepci&oacute;n", "fecha",'d/m/Y');
		//$filter->fecha_recep->clause  =$filter->fecha->clause="where";
		//$filter->fecha_recep->db_name =$filter->fecha->db_name="recep";
		//$filter->fecha_recep->insertValue = date("Y-m-d"); 
		//$filter->fecha_recep->size=10;
		//$filter->fecha_recep->operator="="; 

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
		$grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
		$grid->column_orderby("Vence","<dbdate_to_human><#vence#></dbdate_to_human>",'vence',"align='center'");
		$grid->column_orderby("Nombre","nombre",'nombre');
		$grid->column_orderby("IVA"  ,"montoiva" ,'montoiva' ,"align='right'");
		$grid->column_orderby("Monto" ,"montonet" ,'montonet',"align='right'");
		//$grid->column("Vista",$uri2,"align='center'");

		$grid->add("compras/agregar");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Compras</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->set_connection('farmax');
		$this->rapyd->load_db();

		$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
		$this->rapyd->uri->keep_persistence();
		
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

		$edit->back_url = "compras/scst/datafilter";

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
		
		$edit->monto  = new inputField("Monto US $", "mdolar");
		$edit->monto->size = 20;
		$edit->monto->css_class='inputnum';
		
		$numero=$edit->_dataobject->get('control');
		
		//Campos para el detalle
		
		$detalle = new DataGrid('');
		//$detalle->db->select('a.codigo,a.descrip,a.cantidad,a.costo AS ultimo,a.importe,b.codigo AS sinv');
		$detalle->db->select('a.*,a.codigo AS barras,b.codigo AS sinv');
		$detalle->db->from('itscst AS a');
		$detalle->db->where("a.control",$numero);
		$detalle->db->join('elcarmen.sinv AS b','a.codigo=b.codigo','LEFT');
		$detalle->use_function('exissinv');
		$detalle->column("Barras"            ,"<#codigo#>" );
		$detalle->column("Descripci&oacute;n","<#descrip#>");
		$detalle->column("Cantidad"          ,"<#cantidad#>","align='right'");
		$detalle->column("Precio"            ,"<#ultimo#>"   ,"align='right'");
		$detalle->column("Importe"           ,"<#importe#>" ,"align='right'");
		$detalle->column("Acciones "         ,"<exissinv><#sinv#>|<#dg_row_id#></exissinv>","bgcolor='#D7F7D7' align='center'");
		$detalle->build();
		
		$script='
		function pcrear(id){
			var pasar=["barras","descrip","ultimo","iva"];
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

		$edit->buttons("save", "undo", "back");
		$edit->script($script,'show');
		$edit->build();

		$smenu['link']=barra_menu('201');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_compras', $conten,true); 
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
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
		$data["head"]    =script('prototype.js').$this->rapyd->get_head();
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

		$filter = new DataFilter("Filtro de asignaci&oacute;n de productos",'');

		$filter->proveedor = new inputField("Proveedor", "proveed");
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = "proveed";
		$filter->proveedor->size=20;

		$filter->buttons("reset","search");
		$filter->build();
 
		$grid = new DataGrid();
		$grid->order_by("id","desc");
		$grid->per_page = 15;

		$uri=anchor('farmacia/scst/asignardataedit/<#id#>','<#id#>');
		$grid->column_orderby('Id'       ,$uri     ,'id'     );
		$grid->column_orderby('Proveedor','proveed','proveed');
		$grid->column_orderby('Barras'   ,'barras' ,'barras' );
		$grid->column_orderby('Mapeado a','abarras','abarras');

		$grid->add("farmacia/scst/asignardataedit");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Compras</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function asignardataedit(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Reasignaciones de c&oacute;digo","farmaxasig");
		$edit->back_url = "farmacia/asignarfiltro";

		$edit->proveedor = new inputField('Proveedor','proveed');
		$edit->proveedor->rule = 'required';
		$edit->proveedor->size = 10;
		$edit->proveedor->maxlength=50;

		$edit->barras = new inputField('Barras en el proveedor','barras');
		$edit->barras->rule = 'required';
		$edit->barras->size = 50;
		$edit->barras->maxlength=250;

		$edit->abarras = new inputField('Barras en sistema','abarras');
		$edit->abarras->rule = 'required';
		$edit->abarras->size = 50;
		$edit->abarras->maxlength=250;

		$edit->buttons("save", "undo", "back");
		$edit->build();

		$data['content'] =$edit->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Reasignar c&oacute;digo</h1>';
		$this->load->view('view_ventanas', $data);
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
?>
