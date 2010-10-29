<?php
//transferencias
class Stra extends Controller {

	var $data_type = null;
	var $data = null;
	var $modbus=array(
	'tabla'   =>'caub',
	'columnas'=>array(
		'ubica' =>'C&oacute;digo',
		'ubides'=>'Descripci&oacute;n',
		'gasto' =>'Gastos'),
	'filtro'  =>array('ubides'=>'Descripci&oacute;n'),
	'retornar'=>array('ubica'=>'<#retorno#>'),
	'titulo'  =>'Buscar Almac&eacute;n',
	'p_uri'=>array(4=>'<#retorno#>'));

	function stra(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(302,1);
		define ("THISFILE",   APPPATH."controllers/inventario/". $this->uri->segment(2).EXT);
	}

	function index(){
		redirect("inventario/stra/filteredgrid");
	}

	##### utility, show you $_SESSION status #####
	function _session_dump(){
		echo '<div style="height:200px; background-color:#fdfdfd; overflow:auto;">';
		echo '<pre style="font: 11px Courier New,Verdana">';
		var_export($_SESSION);
		echo '</pre>';
		echo '</div>';
	}

	##### callback test (for DataFilter + DataGrid) #####
	function test($id,$const){
		//callbacktest//
		return $id*$const;
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Filtro de Transferencias","stra");

		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size=15;

		$filter->fecha = new dateonlyField("Fecha", "fecha");
		$filter->fecha->size=12;

		$filter->envia = new inputField("Envia", "envia");
		$filter->envia->size=12;

		$filter->recibe = new inputField("Recibe", "recibe");
		$filter->recibe->size=12;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('inventario/stra/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid("Lista de transferencias");
		$grid->order_by("numero","desc");
		$grid->per_page = 5;
		$grid->use_function("substr");

		$grid->column_orderby("N&uacute;mero",$uri,'numero');
		$grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
		$grid->column_orderby("Envia","envia","envia");
		$grid->column_orderby("Recibe","recibe",'recibe');
		$grid->column("Observaci&oacute;n","observ1");
		//echo $grid->db->last_query();
		$grid->add("inventario/stra/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Transferencias</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function loadiframe($data=null, $head="", $resize=""){
		$template['head'] = $head;
		$template['content'] = $data;
		$template['onload'] = "";
		if ($resize!=""){
			$template['onload'] = "autofit_iframe('$resize');";
		}
		$this->load->view('rapyd/iframe', $template);
	}

	function items_grid(){
		 
		$this->rapyd->load("datagrid");

		$art_id = intval($this->uri->segment(4));

		$grid = new DataGrid("Art&iacute;culos","itstra");
		$grid->db->where("numero", $art_id);

		$modify = site_url("inventario/stra/items_edit/$art_id/modify/<#numero#>/<#codigo#>");
		$delete = anchor("inventario/stra/items_edit/$art_id/do_delete/<#numero#>/<#codigo#>","Eliminar");

		$grid->order_by("codigo","desc");
		$grid->per_page = 15;

		$grid->column_detail("N&uacute;mero","numero",$modify);
		$grid->column("C&oacute;digo","codigo");
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Cantidad","cantidad");
		$grid->column("Eliminar", $delete);
		$grid->add("inventario/stra/items_edit/$art_id/create");
		$grid->build();
	  
		$head = $this->rapyd->get_head();
		$this->loadiframe($grid->output, $head, "related");
	}

	function items_edit(){

		$this->rapyd->load("dataedit2");

		//$art_id = intval($this->uri->segment(4));

		$edit = new DataEdit2("", "itsprm");
		echo "asasa";
		//$edit->back_uri = "inventario/stra/items_grid/$art_id/";
		/*
		$edit->numero = new inputField("Numero", "numero");


		$edit->codigo   = new inputField("Codigo",   "codigo");

		$edit->numero->rule = "trim|required|max_length[20]";

		$edit->cantidad = new inputField("Cantidad", "cantidad");

		$edit->precio1  = new inputField("Precio 1", "precio1");

		$edit->precio2  = new inputField("Precio 2", "precio2");

		$edit->precio3  = new inputField("Precio 3", "precio3");

		$edit->precio4  = new inputField("Precio 4", "precio4");


		$edit->aticle_id = new autoUpdateField("article_id",   $art_id);
		$edit->body = new textareaField("Comment", "comment");
		$edit->body->rule = "required";
		$edit->body->rows = 5;

		$edit->back_save = true;
		$edit->back_cancel_save = true;
		$edit->back_cancel_delete = true;
		*/
		$edit->buttons("modify", "save", "undo", "delete", "back");

		$edit->build();


		$data['content'] = $edit->output;
		$data['title']   = "<h1>Transferencias</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}



	function dataedit(){
		$this->rapyd->load("dataedit","datadetalle");

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'descrip'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			'retornar'=>array('codigo'=>'codigo<#i#>','precio1'=>'precio1<#i#>','precio2'=>'precio2<#i#>','precio3'=>'precio3<#i#>','precio4'=>'precio4<#i#>','iva'=>'iva<#i#>','pond'=>'costo<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Articulo');
			
			
		$edit = new DataEdit("Transferencia","stra");

		$edit->_dataobject->db->set('usuario', $this->session->userdata('usuario'));
		$edit->_dataobject->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$edit->_dataobject->db->set('estampa', 'NOW()', FALSE);

		$edit->post_process("insert","_guarda_detalle");
		$edit->post_process("update","_actualiza_detalle");
		$edit->post_process("delete","_borra_detalle");
		$edit->pre_process('delete','_pre_del');
		$edit->pre_process('insert','_pre_insert');


		$edit->back_url = "inventario/stra/";

		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->size = 10;

		$edit->numero = new inputField2("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;
		$edit->numero->readonly=TRUE;

		$edit->observ1  = new inputField("Observaci&oacute;n", "observ1");
		$edit->observ1->maxlength=30;
		$edit->observ1->size =40;

		$edit->observ2  = new inputField("Observaci&oacute;n", "observ2");
		$edit->observ2->maxlength=30;
		$edit->observ2->size =40;

		$edit->envia  = new inputField("Env&iacute;a", "envia");
		$edit->envia->append($this->datasis->p_modbus($this->modbus,'envia'));
		$edit->envia->size=7;
		$edit->envia->maxlength=4;

		$edit->recibe = new inputField("Recibe"      , "recibe");
		$edit->recibe->append($this->datasis->p_modbus($this->modbus,'recibe'));
		$edit->recibe->size=7;
		$edit->recibe->maxlength=4;


		$numero=$edit->_dataobject->get('numero');

		$detalle = new DataDetalle($edit->_status);

		//Campos para el detalle
		$detalle->db->select('codigo,descrip,cantidad, precio1,precio2,precio3,precio4,iva,costo');
		$detalle->db->from('itstra');
		$detalle->db->where("numero='$numero'");
			
		$detalle->codigo = new inputField2("Codigo", "codigo<#i#>");
		$detalle->codigo->size=11;
		$detalle->codigo->db_name='codigo';
		$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$detalle->codigo->readonly=TRUE;
			
		$detalle->descrip = new inputField("Referencia", "descrip<#i#>");
		$detalle->descrip->size=15;
		$detalle->descrip->db_name='descrip';
		$detalle->descrip->maxlength=12;
			
		$detalle->cantidad = new inputField("Monto", "cantidad<#i#>");
		$detalle->cantidad->css_class='inputnum';
		$detalle->cantidad->size=20;
		$detalle->cantidad->db_name='cantidad';
			
		for($i=1;$i<=4;$i++){
			$objeto="precio$i";
			$detalle->$objeto = new inputField2("Precio", "$objeto<#i#>");
			$detalle->$objeto->type='hidden';
			$detalle->$objeto->db_name=$objeto;
		}
		$detalle->iva = new inputField2("IVA", "iva<#i#>");
		$detalle->iva->type='hidden';
		$detalle->iva->db_name='iva';
			
		$detalle->costo = new inputField2("Costo", "costo<#i#>");
		$detalle->costo->type='hidden';
		$detalle->costo->db_name='costo';
		 
		//fin de campos para detalle
			
		//Columnas del detalle
		$detalle->column("C&oacute;digo"     ,"<#codigo#><#precio1#><#precio2#><#precio3#><#precio4#><#iva#><#costo#>");
		$detalle->column("Descripci&oacute;n","<#descrip#>");
		$detalle->column("Cantidad"          ,"<#cantidad#>");
		$detalle->build();
			
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons( "save", "undo","delete", "back");
		$edit->build();

		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_stra', $conten,true);
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Transferencia de Inventario</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function _guarda_detalle($do) {
		$cant=$this->input->post('cant_0');
		$i=$o=0;
		while($o<$cant){
			if (isset($_POST["codigo$i"])){
				if($this->input->post("codigo$i")){

					$sql = "INSERT INTO itstra (numero,codigo,descrip,cantidad,precio1,precio2,precio3,precio4,iva,costo) VALUES(?,?,?,?,?,?,?,?,?,?)";
					$llena=array(
					0 =>$do->get('numero'),
					1 =>$this->input->post("codigo$i"),
					2 =>$this->input->post("descrip$i"),
					3 =>$this->input->post("cantidad$i"),
					4 =>$this->input->post("precio1$i"),
					5 =>$this->input->post("precio2$i"),
					6 =>$this->input->post("precio3$i"),
					7 =>$this->input->post("precio4$i"),
					8 =>$this->input->post("iva$i"),
					9 =>$this->input->post("costo$i"));

					$this->db->query($sql,$llena);
				}
				$o++;
			}
			$i++;
		}
	}

	function _actualiza_detalle($do){
		$this->_borra_detalle($do);
		$this->_guarda_detalle($do);
	}

	function _borra_detalle($do){
		$numero=$do->get('numero');
		$sql = "DELETE FROM itstra WHERE numero='$numero'";
		$this->db->query($sql);
	}

	function _pre_del($do) {
		return False;
	}

	function _pre_insert($do){

		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
		$query  =$this->db->query($sql);
		$transac=$this->db->insert_id();

		$sql    = 'INSERT INTO nstra (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
		$query  =$this->db->query($sql);
		$numero =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT) ;

		$do->set('transac', $transac);
		$do->set('numero' , $numero);
	}


	function dataedit2(){
		if (($this->uri->segment(5)==="1") && ($this->uri->segment(4)==="do_delete"))
		show_error("Please do not delete the first record, it's required by DataObject sample");

		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Transferencia", "stra");
		$edit->back_url = site_url("inventario/stra/filteredgrid");

		$edit->numero   = new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size =10;
		$edit->numero->rule = "required";

		$edit->fecha    = new  dateonlyField("Fecha",  "fecha");
		$edit->fecha->size =12;

		$edit->envia    = new inputField("Envia", "envia");
		$edit->envia->size =4;

		$edit->recibe   = new inputField("Recibe", "recibe");
		$edit->recibe->size = 4;

		$edit->observ1  = new inputField("Observaci&oacute;n 1", "observ1");
		$edit->observ1->size = 35;

		$edit->observ2  = new inputField("..", "observ2");
		$edit->observ2->size = 35;

		$edit->totalg   = new inputField("Total gr.", "totalg");
		$edit->totalg->size = 17;

		$r_uri = "inventario/stra/items_grid/<#numero#>";

		$edit->related = new iframeField("related", $r_uri, "210");
		$edit->related->when = array("show","modify");

		$edit->buttons("modify", "save", "undo", "delete", "back");
		/*
		 $edit->use_function("callback_test");
		 $edit->test = new freeField("Test", "test", "<callback_test><#article_id#>|3</callback_test>");
		 */
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Transferencias</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}
?>