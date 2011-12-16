<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Mgas extends validaciones {

	function mgas(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id(511,1);
		redirect("finanzas/mgas/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$modbus=array(
		  'tabla'   =>'grcl',
		  'columnas'=>array('grupo' =>'Codigo',
		  'gr_desc'=>'Descripci&oacute;n'),
		  'filtro'  =>array('grupo'=>'C&oacute;digo','gr_desc'=>'Descripci&oacute;n'),
		  'retornar'=>array('grupo'=>'grupo'),
		'titulo'  =>'Buscar Grupo');

		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter("Filtro Maestro de Gastos", 'mgas');

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->size=20;

		$filter->cuenta = new inputField('Cuenta contable', 'cuenta');
		$filter->cuenta->like_side='after';

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->size=20;
		
		$filter->grupo = new inputField("Grupo", "grupo");
		$filter->grupo->size=20;
		$filter->grupo->append($boton);

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/mgas/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Maestro de Gastos");
		$grid->order_by("codigo","asc");
		$grid->per_page = 15;

		$grid->column_orderby("C&oacute;digo",$uri ,'codigo');
		$grid->column("Tipo","tipo");
		$grid->column_orderby("Descripci&oacute;n","descrip",'descrip');
		$grid->column_orderby("Grupo","grupo",'grupo');
		$grid->column_orderby('Nombre del Grupo','nom_grup','nom_grup');

		$grid->add("finanzas/mgas/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Maestro de Gastos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit(){
		$this->rapyd->load("dataedit");
		$link=site_url('finanzas/mgas/ultimo');

		$script ='
		function ultimo(){
			$.ajax({
				url: "'.$link.'",
				success: function(msg){
				  alert( "El ultimo codigo ingresado fue: " + msg );
			}
		});
		}
		$(function() {
			$(".inputnum").numeric(".");
			$("#grupo").change(function(){grupo();}).change();
		});
		function grupo(){
			t=$("#grupo").val();
			a=$("#grupo :selected").text();
			$("#nom_grup").val(a);
		}';

		$mCPLA=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta'),
			'titulo'  =>'Buscar Cuenta',
		);
		$bcpla =$this->datasis->modbus($mCPLA);

		$atts = array(
				'width'     =>'800',
				'height'    =>'600',
				'scrollbars'=>'yes',
				'status'    =>'yes',
				'resizable' =>'yes',
				'screenx'   =>'5',
				'screeny'   =>'5');

		$edit = new DataEdit("Maestro de Gastos", "mgas");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		$edit->back_url = site_url("finanzas/mgas/filteredgrid");

		$ultimo='<a href="javascript:ultimo();" title="Consultar ultimo codigo ingresado" onclick="">Consultar ultimo codigo</a>';
		$edit->codigo= new inputField("C&oacute;digo", "codigo");
		$edit->codigo->mode="autohide";
		$edit->codigo->size = 12;
		$edit->codigo->maxlength = 6;
		$edit->codigo->rule = "trim|required|callback_chexiste";
		$edit->codigo->append($ultimo);

		$edit->descrip= new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size = 35;
		//$edit->descrip->readonly=true;

		$edit->tipo= new dropdownField("Tipo", "tipo");
		$edit->tipo->style ="width:100px;";
		$edit->tipo->option("G","Gasto");
		$edit->tipo->option("G","Gasto");
		$edit->tipo->option("I","Inventario");
		$edit->tipo->option("S","Suministro");
		$edit->tipo->option("A","Activo");

		$edit->grupo= new dropdownField("Grupo", "grupo");
		$edit->grupo->options('SELECT grupo, nom_grup from grga order by nom_grup');
		$edit->grupo->style ="width:250px;";
		$edit->grupo->onchange ="grupo();";

		$edit->nom_grup  = new inputField("nom_grup", "nom_grup");

		$edit->iva       = new inputField("Iva", "iva");
		$edit->iva->css_class='inputnum';//no sirve
		$edit->iva->size =12;
		$edit->iva->maxlength =5;
		$edit->iva->rule ="trim";

		$edit->medida    = new inputField("Unidad Medida", "medida");
		$edit->medida->size = 10;  

		$edit->fraxuni   = new inputField("Unidad Fracc.", "fraxuni");
		$edit->fraxuni->size = 10;

		$edit->minimo    = new inputField("Existencia M&iacute;nima", "minimo");
		$edit->minimo->size = 10;

		$edit->maximo    = new inputField("Existencia M&aacute;xima", "maximo");
		$edit->maximo->size = 10;

		$edit->ultimo    = new inputField("Ultima Venta", "ultimo");
		$edit->ultimo->size = 15;

		$edit->promedio  = new inputField("Promedio", "promedio");
		$edit->promedio->size = 15;

		$edit->unidades  = new inputField("Unidades", "unidades");
		$edit->unidades->size = 5;

		$edit->fraccion  = new inputField("Fracci&oacute;n", "fraccion");
		$edit->fraccion->size = 5;

		$lcuent=anchor_popup("/contabilidad/cpla/dataedit/create","Agregar Cuenta Contable",$atts);
		$edit->cuenta    = new inputField("Cuenta Contable #", "cuenta");
		$edit->cuenta->size = 12;
		$edit->cuenta->maxlength = 15;
		$edit->cuenta->rule = "trim|callback_chcuentac";
		$edit->cuenta->append($bcpla);
		$edit->cuenta->append($lcuent);
		$edit->cuenta->readonly=true;

		$edit->tasa1     = new inputField("Tasa1", "tasa1");
		$edit->tasa1->size = 5;

		$edit->base1     = new inputField("Base1", "base1");
		$edit->base1->size = 12;

		$edit->desde1    = new inputField("Desde1", "desde1");
		$edit->desde1->size = 12;

		$edit->tasa2     = new inputField("Tasa2", "tasa2");
		$edit->tasa2->size = 5;

		$edit->base2     = new inputField("Base2", "base2");
		$edit->base2->size = 12;

		$edit->desde2    = new inputField("Desde2", "desde2");
		$edit->desde2->size = 12;

		$edit->tasa3     = new inputField("Tasa3", "tasa3");
		$edit->tasa3->size = 5;

		$edit->base3     = new inputField("Base3", "base3");
		$edit->base3->size = 12;

		$edit->desde3    = new inputField("Desde3", "desde3");
		$edit->desde3->size = 12;

		$edit->tasa4     = new inputField("Tasa4", "tasa4");
		$edit->tasa4->size = 5;

		$edit->base4     = new inputField("Base4", "base4");
		$edit->base4->size = 12;

		$edit->desde4    = new inputField("Desde4", "desde4");
		$edit->desde4->size = 12;

		$edit->amorti    = new inputField("Amort/Dep","amorti");
		$edit->amorti->size =15;

		$edit->dacumu    = new inputField("D.Acum","dacumu");
		$edit->dacumu->size =15;

		$codigo=$edit->_dataobject->get("codigo");
		$edit->almacenes = new containerField('almacenes',$this->_detalle($codigo));
		$edit->almacenes->when = array("show","modify");

		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();

		//echo $edit->codigo->value;
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_maestrodegasto', $conten,true);
		$data["head"]    =script("tabber.js").script("prototype.js").script("sinvmaes.js").script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Maestro de Gasto</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function chexiste(){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM mgas WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT descrip FROM mgas WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el gasto $nombre");
			return FALSE;
		}
	}

	function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT codigo FROM mgas ORDER BY codigo DESC");
		echo $ultimo;
	}

	function _detalle($codigo){
		$salida='';
		/*
		if(!empty($codigo)){
			$this->rapyd->load('dataedit','datagrid'); 

			$grid = new DataGrid('Cantidad por almac&eacute;n');
			$grid->db->select('ubica,locali,cantidad,fraccion');
			$grid->db->from('ubic');
			$grid->db->where('codigo',$codigo);
			
			$grid->column("Almacen"          ,"ubica" );
			$grid->column("Ubicaci&oacute;n" ,"locali");
			$grid->column("Cantidad"         ,"cantidad",'align="RIGHT"');
			$grid->column("Fracci&oacute;n"  ,"fraccion",'align="RIGHT"');
			
			$grid->build();
			$salida=$grid->output;
		}*/
		return $salida;
	}
  
/*    
	function sinvlineas(){  
		$this->rapyd->load("fields");
		$where = "";
		$sql = "SELECT linea,descrip FROM line ";
		$linea = new dropdownField("Linea", "linea");
		$dpto=$this->input->post('dpto');

		if ($dpto){
		  $where = "WHERE depto = ".$this->db->escape($dpto);
		  $sql = "SELECT linea,descrip FROM line $where ORDER BY descrip";
		  $linea->option("","");
			$linea->options($sql);
		}else{
			 $linea->option("","Seleccione Un Departamento");
		} 
		$linea->status   = "modify";
		$linea->onchange = "get_grupo();";
		$linea->build();
		echo $linea->output;
	}
	function sinvgrupos(){
		$this->rapyd->load("fields");  
		$where = "";  
		$line=$this->input->post('line');
		$dpto=$this->input->post('dpto'); 
		
		$grupo = new dropdownField("Grupo", "grupo");
		if ($line AND $dpto AND !(empty($line) OR empty($dpto))) {
			$where .= "WHERE depto = ".$this->db->escape($dpto);
			$where .= "AND linea = ".$this->db->escape($line);
			$sql = "SELECT grupo, nom_grup FROM grup $where";
			$grupo->option("","");
			$grupo->options($sql);
		}else{
			$grupo->option("","Seleccione una linea"); 
		} 
		$grupo->status = "modify";  
		$grupo->build();
		echo $grupo->output; 
	}*/
}
