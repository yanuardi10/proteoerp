<?php
	class Presup extends Controller {

	function Presup(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(104,1);
	}

	function index() {
		$this->rapyd->load("datagrid","datafilter");

		$scli=array(
			'tabla'   =>'scli',
			'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'contacto'=>'Contacto'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cod_cli'),
			'titulo'  =>'Buscar Cliente');

		$boton=$this->datasis->modbus($scli);

		$filter = new DataFilter("Filtro de Presupuestos");
		$filter->db->select('fecha,numero,cod_cli,nombre,totals,totalg,iva');
		$filter->db->from('spre');

		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d");
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";

		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size = 30;

		$filter->cliente = new inputField("Cliente", "cod_cli");
		$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/presup/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid();
		$grid->order_by("fecha","desc");
		$grid->per_page = 15;

		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Nombre","nombre");
		$grid->column("Sub.Total","<number_format><#totals#>|2</number_format>","align=right");
		$grid->column("IVA","<number_format><#iva#>|2</number_format>","align=right");
		$grid->column("Total","<number_format><#totalg#>|2</number_format>","align=right");

		$grid->add("ventas/presup/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Presupuesto</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load("dataobject","datadetails");

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'descrip'=>'descrip'),
			'filtro'  =>array('codigo' =>'C&acute;digo','descrip'=>'descrip'),
			//'retornar'=>array('codigo'=>'codigo<#i#>','precio1'=>'precio1<#i#>','precio2'=>'precio2<#i#>','precio3'=>'precio3<#i#>','precio4'=>'precio4<#i#>','iva'=>'iva<#i#>','pond'=>'costo<#i#>'),
			'retornar'=>array('codigo'=>'codigo<#i#>','descrip'=>'sinvdescrip<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Articulo');

		$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','cirepre'=>'rifci','dire11'=>'direc'),
			'titulo'  =>'Buscar Cliente');

		$boton =$this->datasis->modbus($mSCLId);

		$do = new DataObject("spre");
		//$do->rel_one_to_many('itspre', 'itspre', array('numero'=>'numero','tipo_doc'=>'tipo'));
		$do->rel_one_to_many('itspre', 'itspre', array('numero'=>'numero'));
		$do->pointer('scli' ,'scli.cliente=spre.cod_cli','scli.nombre as sclinombre','LEFT');
		$do->rel_pointer('itspre','sinv','itspre.codigo=sinv.codigo','sinv.descrip as sinvdescrip');

		$edit = new DataDetails("presupuestos",$do);

		//$edit->post_process("insert","_guarda_detalle");
		//$edit->post_process("update","_actualiza_detalle");
		//$edit->post_process("delete","_borra_detalle");
		//$edit->pre_process('insert','_pre_insert');

		$edit->back_url = site_url("ventas/presup");

		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 10;

		$edit->vende = new  dropdownField ("Vendedor", "vd");
		$edit->vende->options("SELECT vendedor, CONCAT(vendedor,' ',nombre) nombre FROM vend ORDER BY vendedor");
		$edit->vende->size = 5;

		$edit->peso = new inputField("Peso", "peso");
		$edit->peso->size = 10;

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;

		$edit->tipo = new inputField("Tipo", "tipo_doc");
		$edit->tipo->size = 5;

		$edit->iva  = new inputField("IVA", "iva");
		$edit->iva->size = 20;
		$edit->iva->css_class='inputnum';

		$edit->subtotal  = new inputField("Sub.Total", "totals");
		$edit->subtotal->size = 20;
		$edit->subtotal->css_class='inputnum';

		$edit->total  = new inputField("Total", "totalg");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';

		$edit->inicial  = new inputField("Inicial", "inicial");
		$edit->inicial->size = 20;
		$edit->inicial->css_class='inputnum';

		$edit->cliente = new inputField("Cliente","cod_cli");
		$edit->cliente->size = 10;
		$edit->cliente->maxlength=5;
		$edit->cliente->append($boton);

		$edit->nombre = new inputField("Nombre", "sclinombre");
		$edit->nombre->pointer = TRUE;
		$edit->nombre->size      = 55;
		$edit->nombre->maxlength = 40;
		$edit->nombre->in        = 'cliente';

		$edit->rifci   = new inputField("RIF/CI","rifci");
		$edit->rifci->size = 20;
		$edit->rifci->rule= "required";

		$edit->direc = new inputField("Direcci&oacute;n","direc");
		$edit->direc->size = 55;
		$edit->direc->rule= "required";

		$edit->dire1 = new inputField(" ","dire1");
		$edit->dire1->size = 55;

		$edit->condi1 = new inputField("Condici&oacute;n","condi1");
		$edit->condi1->size = 55;

		$edit->condi2 = new inputField(" ","condi2");
		$edit->condi2->size = 55;

		//Campos para el detalle
		$edit->codigo = new inputField("C&oacute;digo", "codigo<#i#>");
		$edit->codigo->size   =18;
		$edit->codigo->db_name='codigo';
		$edit->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$edit->codigo->readonly=TRUE;
		$edit->codigo->rel_id  = 'itspre';

		$edit->descripcion = new inputField("Descripci&oacute;n", "sinvdescrip<#i#>");
		$edit->descripcion->size=30;
		$edit->descripcion->db_name='sinvdescrip';
		//$edit->descripcion->maxlength=12;
		$edit->descripcion->pointer  =true;
		$edit->descripcion->rel_id   ='itspre';

		$edit->cantidad = new inputField("Cantidad", "cana<#i#>");
		$edit->cantidad->size=10;
		$edit->cantidad->db_name='cana';
		$edit->cantidad->maxlength=60;
		$edit->cantidad->css_class='inputnum';
		$edit->cantidad->rel_id  = 'itspre';

		$edit->precio = new inputField("Precio", "preca<#i#>");
		$edit->precio->css_class='inputnum';
		$edit->precio->onchange='totalizar()';
		$edit->precio->size=20;
		$edit->precio->db_name='preca';
		$edit->precio->rel_id  = 'itspre';

		$edit->importe = new inputField2("Importe", "totaorg<#i#>");
		$edit->importe->db_name='totaorg';
		$edit->importe->size=20;
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   = 'itspre';
		//fin de campos para detalle

		$edit->buttons("save", "undo", "delete", 'modify',"back","add_rel");
		$edit->build();

		/*print_r($do->_pointer_data);
		print_r($do->_rel_pointer_data);*/

		$smenu['link']   = barra_menu('104');
		$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['content'] = $edit->output;
		$data["head"]    = script("jquery.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Presupuesto</h1>';
		$this->load->view('view_ventanas', $data);
	}
}
?>