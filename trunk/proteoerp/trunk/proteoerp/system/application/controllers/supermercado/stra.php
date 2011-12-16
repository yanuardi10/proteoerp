<?php
class Stra extends Controller {


	function stra(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(302,1);
	}
  function index(){
    redirect("supermercado/stra/filteredgrid");
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
		
		$uri = anchor('supermercado/stra/dataedit/show/<#numero#>','<#numero#>');
		
		$grid = new DataGrid("Lista de transferencias");
		$grid->order_by("numero","desc");
		$grid->per_page = 5;
		$grid->use_function("substr");
		
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Env&iacute;a","envia","envia");
		$grid->column("Recibe","recibe");
		$grid->column("Observaci&oacute;n","observ1");
		//echo $grid->db->last_query();
		$grid->add("supermercado/stra/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Transferencias</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		$modbus=array(
			'tabla'   =>'maes',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n',
				'peso'=>'Peso'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'maesdescrip_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Producto en inventario');
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');
		
		$script="
		function post_add_itstra(id){
			$('#cantidad_'+id).numeric(".");
			return true;
		}";
		
		$do = new DataObject("stra");
		$do->rel_one_to_many('itstra', 'itstra', 'numero');
		$do->rel_pointer('itstra','maes','itstra.codigo=maes.codigo','maes.descrip as maesdescrip');
		
		$edit = new DataDetails("Transferencia", $do);
		$edit->back_url = site_url("supermercado/stra/filteredgrid");
		$edit->set_rel_title('itstra','Producto <#o#>');

		$edit->script($script,'create');
		$edit->script($script,'modify');
		
		$edit->pre_process('insert' ,'_pre_insert');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->numero= new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size=10;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');
		
		$edit->fecha = new  dateonlyField("Fecha",  "fecha");
		$edit->fecha->insertValue = date("Y-m-d");  
		$edit->fecha->size =12;
		
		$edit->envia = new dropdownField("Env&iacute;a", "envia");  
		$edit->envia->option("","Seleccionar");  
		$edit->envia->options("SELECT ubica,ubides FROM caub ORDER BY ubica");
		$edit->envia->rule ="required";
		$edit->envia->style="width:200px;";
		
		$edit->recibe = new dropdownField("Recibe", "recibe");  
		$edit->recibe->option("","Seleccionar");  
		$edit->recibe->options("SELECT ubica,ubides FROM caub ORDER BY ubica");
		$edit->recibe->rule ="required" ;
		$edit->recibe->style="width:150px;";
		
		$edit->observ1 = new inputField("Observaci&oacute;n ", "observ1");
		$edit->observ1->rule     ="trim";
		$edit->observ1->maxlength=35;
		$edit->observ1->size     =35;
		
		$edit->observ2 = new inputField("..", "observ2");
		$edit->observ2->rule = "trim";
		$edit->observ2->size = 35;
		
		$edit->totalg = new inputField("Peso", "totalg");
		$edit->totalg->mode="autohide";
		$edit->totalg->css_class ='inputnum';
		$edit->totalg->when=array('show','modify');
		$edit->totalg->size      = 17;

		//comienza el detalle
		$edit->codigo = new inputField("C&oacute;digo <#o#>", "codigo_<#i#>");
		$edit->codigo->db_name='codigo';
		$edit->codigo->append($btn);
		$edit->codigo->rule = "trim|required";
		$edit->codigo->rel_id='itstra';
		$edit->codigo->maxlength=15;
		$edit->codigo->size     =15;

		$edit->descrip = new inputField("Descripci&oacute;n", "maesdescrip_<#i#>");
		$edit->descrip->db_name='maesdescrip';
		$edit->descrip->pointer=true;
		$edit->descrip->rel_id='itstra';
		$edit->descrip->maxlength=45;
		$edit->descrip->size     =53;
		
		$edit->cantidad = new inputField("Cantidad", "cantidad_<#i#>");
		$edit->cantidad->db_name  ='cantidad';
		$edit->cantidad->css_class='inputnum';
		$edit->cantidad->rel_id   ='itstra';
//		$edit->cantidad->rule     ='numeric';
		$edit->cantidad->maxlength=10;
		$edit->cantidad->size     =10;
		
		$edit->buttons("modify", "save", "undo", "delete", "back","add_rel"); 
		$edit->build();
		$conten["form"]  =&  $edit;
		//$data['content'] = $edit->output;
		$data['content'] = $this->load->view('view_straa', $conten,true);
		$data['title']   = "<h1>Transferencias de inventario</h1>";
		$data["head"]    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data); 
  }
  
  function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('nstra');
		$do->set('numero',$numero);
		$do->pk['numero'] = $numero; //Necesario cuando la clave primara se calcula por secuencia
		return true;
	}
	
	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('stra',"TRANSFERENCIA $codigo CREADO");
		$query='select sum(b.cantidad * c.peso) as valor 
				from stra as a
				join itstra as b on a.numero=b.numero
				join maes as c on c.codigo=b.codigo
				where a.numero="'.$codigo.'"';
		$mSQL_1 = $this->db->query($query);
		$resul = $mSQL_1->row();
		$valor=$resul->valor;
		$query='update stra set totalg="'.$valor.'" where numero="'.$codigo.'" ';
		$this->db->query($query);
		
	}
	
	function _post_update($do){
		$codigo=$do->get('numero');

		logusu('stra',"TRANSFERENCIA $codigo MODIFICADO");
		
		$query='select sum(b.cantidad * c.peso) as valor 
				from stra as a
				join itstra as b on a.numero=b.numero
				join maes as c on c.codigo=b.codigo
				where a.numero="'.$codigo.'"';
		$mSQL_1 = $this->db->query($query);
		$resul = $mSQL_1->row();
		$valor=$resul->valor;
		$query='update stra set totalg="'.$valor.'" where numero="'.$codigo.'" ';
		$this->db->query($query);
	}
	
	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('stra',"TRANSFERENCIA $codigo ELIMINADO");
	}

	}
?>
