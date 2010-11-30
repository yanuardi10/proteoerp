<?php
class barraspos extends Controller {
	function barraspos(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		//$this->datasis->modulo_id(312,1);
		redirect("inventario/barraspos/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");

		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');
		
		$script='
		<script language="javascript" type="text/javascript">
		$(function(){
			$(".inputnum").numeric(".");
		});
		</script>
		';

		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		$bSPRV=$this->datasis->modbus($mSPRV);

		$mSINV=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;odigo',
			'descrip'=>'Descripci&oacute;n',
			'descrip2'=>'Descripci&oacute;n 2'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar Codigo');
		$bSINV=$this->datasis->modbus($mSINV);

		$js='function depto(){
			if($("#depto").val()!=""){
				$("#nom_depto").attr("disabled","disabled");
			}else{
				$("#nom_depto").attr("disabled","");
			}
		}
		function linea(){
			if($("#linea").val()!=""){
				$("#nom_linea").attr("disabled","disabled");
			}else{
				$("#nom_linea").attr("disabled","");
			}
		}
		function grupo(){
			if($("#grupo").val()!=""){
				$("#nom_grupo").attr("disabled","disabled");
			}else{
				$("#nom_grupo").attr("disabled","");
			}
		}';

		$select=array('a.codigo as codigo','a.suplemen','b.descrip','b.marca','b.precio1','b.depto','b.grupo','b.linea');

		$filter = new DataFilter2('Filtro de Codigo de Barras');
		$filter->script($js);
		$filter->db->select($select);
		$filter->db->from('barraspos AS a');
		$filter->db->join('sinv AS b','a.codigo=b.codigo');

		$filter->codigo = new inputField('C&oacute;digo de producto', 'codigo');
		$filter->codigo->db_name   ='a.codigo';
		$filter->codigo->size      = 15;
		$filter->codigo->maxlength = 15;
		$filter->codigo->append($bSINV);

		$filter->proveed = new inputField('Proveedor', 'proveed');
		$filter->proveed->append($bSPRV);
		$filter->proveed->db_name='b.prov1';
		$filter->proveed->size=25;

		$filter->depto = new dropdownField('Departamento','depto');
		$filter->depto->db_name='b.depto';
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="b.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");
		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name='c.grupo';
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
			$filter->grupo->options("SELECT grupo, nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$filter->grupo->option('','Seleccione un Departamento primero');
		}

		$filter->marca = new dropdownField('Marca', 'marca');
		$filter->marca->db_name='b.marca';
		$filter->marca->option('','Todas');
		$filter->marca->options('SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca');
		$filter->marca->style='width:220px;';

		$filter->buttons('reset','search');
		$filter->build();

		$link=anchor('/inventario/barraspos/dataedit/show/<#codigo#>/<#suplemen#>','<#codigo#>');
		$grid = new DataGrid('Lista de Productoss');
		$grid->order_by('codigo','asc');
		$grid->per_page = 15;

		$grid->column_orderby('C&oacute;digo'   ,$link     ,'codigo');
		$grid->column_orderby('Descripci&oacute;n', 'descrip' ,'descrip');
		$grid->column_orderby('Marca', 'marca' ,'marca');
		$grid->column_orderby('Barras','suplemen','suplemen','align="right"');
		//$grid->column_orderby('F.Desde'        ,'<dbdate_to_human>fechad</dbdate_to_human>','fechad');
		//$grid->column_orderby('F.Hasta'        ,'<dbdate_to_human>fechah</dbdate_to_human>','fechah');

		$grid->add('inventario/barraspos/dataedit/create');
		$grid->build();

		$this->rapyd->jquery[]='
		$("select:not(#margen)").change( function() {
			$.ajax({ 
				type: "POST",
				url: "'.site_url('inventario/sinvpromo/cmargen').'",
				data: "cod="+$(this).attr("name")+"&margen="+$(this).val(),
				success: function(msg){
					if (msg=="0"){
						alert("Error al actualizar");
					}
				 } });
		});';

		$this->rapyd->jquery[]='
		
		$("#depto").change(function(){
			depto();
			$.post("'.$link2.'",{ depto:$(this).val() },function(data){$("#linea").html(data);})
			$.post("'.$link3.'",{ linea:"" },function(data){$("#grupo").html(data);})
		});
		$("#linea").change(function(){
			linea();
			$.post("'.$link3.'",{ linea:$(this).val() },function(data){$("#grupo").html(data);})
		});
		
		$("#grupo").change(function(){
			grupo();
		});
		depto();linea();grupo();';

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Adicionar codigo de barras</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit() {
		$this->rapyd->load('dataedit');
		
		$script='
		<script language="javascript" type="text/javascript">
		$(function(){
			$(".inputnum").numeric(".");
		});
		</script>
		';

		$mSPRV=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;odigo',
			'descrip'=>'Descripci&oacute;n',
			'descrip2'=>'Descripci&oacute;n 2'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar Codigo');
		$bSPRV=$this->datasis->modbus($mSPRV);


		$edit = new DataEdit("barras de Inventario", "barraspos");
		$edit->back_url = site_url("inventario/barraspos/filteredgrid/");

		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size       =  15;
		$edit->codigo->maxlength  =  15;
		$edit->codigo->rule 			= "required";
		$edit->codigo->append($bSPRV);


		$edit->barras = new inputField("Barras", "suplemen");
		$edit->barras->css_class ='inputnum';
		$edit->barras->size      =  15;
		$edit->barras->maxlength =  15;
		$edit->barras->rule      =  "required";

		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Adicionar codigo de barras</h1>";
		
		$data["head"]    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().$script;
		$this->load->view('view_ventanas', $data);
	}

	function instala(){
		$mSQL="CREATE TABLE IF NOT EXISTS `barraspos` (
  			`codigo` char(15) NOT NULL DEFAULT '',
  			`suplemen` char(15) NOT NULL DEFAULT '',
  		PRIMARY KEY (`codigo`,`suplemen`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1
		";
		$this->db->query($mSQL);

	}
}
?>