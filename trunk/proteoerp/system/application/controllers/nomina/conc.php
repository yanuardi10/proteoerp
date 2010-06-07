<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//concepto
class Conc extends validaciones{

	function conc(){
		parent::Controller(); 
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
	}

	function index(){
		$this->datasis->modulo_id(704,1);
		redirect("nomina/conc/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por Descripci&oacute;n", 'conc');
		
		$filter->concepto = new inputField("Concepto", "concepto");
		$filter->concepto->size = 5;
		
		$filter->descrip  = new inputField("Descripci&oacute;n", "descrip");
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('nomina/conc/dataedit/show/<#concepto#>','<#concepto#>');

		$grid = new DataGrid("Lista de Conceptos");
		$grid->order_by("concepto","asc");
		$grid->per_page = 20;

		$grid->column("Concepto",$uri);
		$grid->column("Tipo","tipo");
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Tipoa","tipoa");
		//$grid->column("F&oacute;rmula","formula");
		
		$grid->add("nomina/conc/dataedit/create");
		$grid->build();
	
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Conceptos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function getctade($tipoa=NULL){
		$this->rapyd->load("fields");
		$uadministra = new dropdownField("ctade", "ctade");
		$uadministra->status = "modify";
		$uadministra->style ="width:400px;";
		//echo 'de nuevo:'.$tipoa;
		if ($tipoa!==false){
		if($tipoa=='P'){
					$uadministra->options("SELECT proveed,nombre FROM sprv ORDER BY proveed");
			}else{
				if($tipoa=='G'){
					$uadministra->options("SELECT codigo,descrip FROM mgas ORDER BY codigo");
				}else{
					$uadministra->options("SELECT cliente,nombre FROM sprv ORDER BY cliente");
				}
			}
		}else{
 				$uadministra->option("Seleccione un opcion");
		}
		$uadministra->build(); 
		echo $uadministra->output;
	}
	function dataedit(){
		$this->rapyd->load("dataobject","dataedit2");
			
		$qformato=$this->qformato=$this->datasis->formato_cpla();
		
		$modbus=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'<#i#>'),
			'titulo'  =>'Buscar Cuenta',
			'where'=>"codigo LIKE \"$qformato\"",
			'p_uri'=>array(4=>'<#i#>')
		);
		
		$bcuenta  =$this->datasis->p_modbus($modbus ,'cuenta');
		$bcontra  =$this->datasis->p_modbus($modbus ,'contra');
		
		$edit = new DataEdit2("Conceptos", "conc");
		$edit->back_url = site_url("nomina/conc/filteredgrid");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->concepto = new inputField("Concepto", "concepto");
		$edit->concepto->rule = "required|callback_chexiste";
		$edit->concepto->mode = "autohide";
		$edit->concepto->maxlength= 4;
		$edit->concepto->size = 7;
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->style ="width:100px;";
		$edit->tipo->option("","");
		$edit->tipo->options(array("A"=> "Asignaci&oacute;n","O"=>"Otros","D"=> "Deducci&oacute;n"));
		
		$edit->descrip =  new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size =45;
		$edit->descrip->maxlength=35;
		$edit->descrip->rule = "strtoupper|required";
		
		$edit->grupo = new inputField("Grupo", "grupo");
		$edit->grupo->size =7;
		$edit->grupo->maxlength=4;
		
		$edit->encab1 = new inputField("Encabezado 1", "encab1");
		$edit->encab1->size = 22;
		$edit->encab1->maxlength=12;
		
		$edit->encab2 =   new inputField("Encabezado 2&nbsp;", "encab2");
		$edit->encab2->size = 22;
		$edit->encab2->maxlength=12;
				
		$edit->formula = new textareaField("F&oacute;rmula","formula");
		$edit->formula->rows = 4;
		$edit->formula->cols=90;
		
		$edit->cuenta = new inputField("Debe", "cuenta");
		$edit->cuenta->size =19;
		$edit->cuenta->maxlength=15;
		$edit->cuenta->group="Enlase Contable";
		$edit->cuenta->rule='callback_chcuentac';
		$edit->cuenta->append($bcuenta);
		
		$edit->contra =  new inputField("Haber", "contra"); 
		$edit->contra->size = 19;   
		$edit->contra->maxlength=15;
		$edit->contra->group="Enlase Contable";
		$edit->contra->rule='callback_chcuentac';
		$edit->contra->append($bcontra);
			
		$edit->tipoa = new dropdownField ("Deudor ", "tipoa");  
		$edit->tipoa->style ="width:100px;";
		$edit->tipoa->option(" "," "); 
		$edit->tipoa->option("G","Gasto");    
		$edit->tipoa->option("C","Cliente");  
		$edit->tipoa->option("P","Proveedor");
		$edit->tipoa->group="Enlase Administrativo";
		$edit->tipoa->onchange = "get_ctade();";

		$edit->ctade = new dropdownField("ctade", "ctade");
		$edit->ctade->style ="width:400px;";
		$edit->ctade->group="Enlase Administrativo";
		if($edit->_status=='modify'){
			$tipoa  =$edit->getval("tipoa");
			if($tipoa=='P'){
					$edit->ctade->options("SELECT proveed,nombre FROM sprv ORDER BY proveed");
			}else{
				if($tipoa=='G'){
					$edit->ctade->options("SELECT codigo,descrip FROM mgas ORDER BY codigo");
				}else{
					$edit->ctade->options("SELECT cliente,nombre FROM sprv ORDER BY cliente");
				}
			}
		}else{
			$edit->ctade->option("","Seleccione una Deudor");
		}
			  
		$edit->tipod = new dropdownField ("Acreedor", "tipod");
		$edit->tipod->style ="width:100px;";
		$edit->tipod->option(" "," "); 
		$edit->tipod->option("G","Gasto");
		$edit->tipod->option("C","Cliente");
		$edit->tipod->option("P","Proveedor");
		$edit->tipod->onchange = "get_ctaac();";
		$edit->tipod->group="Enlase Administrativo";
		
		$edit->ctaac =   new dropdownField("ctaac", "ctaac"); 
		$edit->ctaac->style ="width:400px;";     
		$edit->ctaac->group="Enlase Administrativo";
		if($edit->_status=='modify'){
			$tipod  =$edit->getval("tipod");
			if($tipod=='P'){
					$edit->ctaac->options("SELECT proveed,nombre FROM sprv ORDER BY proveed");
			}else{
				if($tipod=='G'){
					$edit->ctaac->options("SELECT codigo,descrip FROM mgas ORDER BY codigo");
				}else{
					$edit->ctaac->options("SELECT cliente,nombre FROM sprv ORDER BY cliente");
				}
			}
		}else{
			$edit->ctaac->option("","Seleccione un Acreedor");
		}
		
		$edit->aplica =   new dropdownField("Aplica para liquidacion", "liquida"); 
		$edit->aplica->style ="width:50px;";     
		$edit->aplica->option("S","S");
		$edit->aplica->option("N","N"); 
    			
		$edit->buttons("modify", "save", "undo", "back","delete");
		$edit->build();
		
		$link=site_url('nomina/conc/getctade');
		$link2=site_url('nomina/conc/getctade');
	$data['script']  =<<<script
		<script type="text/javascript" charset="utf-8">
		function get_ctade(){
				var tipo=$("#tipoa").val();
				$.ajax({
					url: "$link"+'/'+tipo,
					success: function(msg){
						$("#td_ctade").html(msg);								
					}
				});
									//alert(tipo);
			} 
		function get_ctaac(){
				var tipo=$("#tipod").val();
				$.ajax({
					url: "$link2"+'/'+tipo,
					success: function(msg){
						$("#td_ctaac").html(msg);
					}
				});
			} 	
		</script>
script;
	
		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Conceptos</h1>";        
		$data["head"]    = $this->rapyd->get_head();                                                                                         
		$data["head"]   .='<script src="'.base_url().'assets/default/script/jquery.js'.'" type="text/javascript" charset="utf-8"></script>';
		$this->load->view('view_ventanas', $data);  
	}

	function _post_insert($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('conc',"CONCEPTO $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('conc',"CONCEPTO $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('conc',"CONCEPTO $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('concepto');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM conc WHERE concepto='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT descrip FROM conc WHERE concepto='$codigo'");
			$this->validation->set_message('chexiste',"El concepto $codigo nombre $nombre ya existe");
			return FALSE;
		}else {
  			return TRUE;
		}
	}

	function instalar(){
		$mSQL="ALTER TABLE conc ADD PRIMARY KEY (concepto);";
		$this->db->simple_query($mSQL);	
	}
}
?>