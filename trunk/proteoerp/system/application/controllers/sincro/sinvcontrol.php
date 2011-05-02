<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
class sinvcontrol extends validaciones {

	function sinvcontrol(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('924',1);
		$this->sucu=$this->datasis->traevalor('NROSUCU');
		$sucu = $this->db->escape($this->sucu);
		$this->prefijo = $this->datasis->dameval("SELECT prefijo FROM sucu WHERE codigo=$sucu");
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$cpre=$this->input->post('pros');
		if($cpre!==false){
			$msj=$this->_cprecios();
		}else{
			$msj='';
		}

		$this->rapyd->load('datafilter2','datagrid');
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

		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');

		$script='
		$(document).ready(function(){
			$(".inputnum").numeric(".");
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
			$("#sinvprecioc").submit(function() {
				return confirm("Se van a actualizar todos los precios en pantalla \nEstas seguro de que quieres seguir??");
			});
			depto();
			linea();
			grupo();
		});

		function depto(){
			if($("#depto").val()!=""){
				$("#nom_depto").attr("disabled","disabled");
			}
			else{
				$("#nom_depto").attr("disabled","");
			}
		}

		function linea(){
			if($("#linea").val()!=""){
				$("#nom_linea").attr("disabled","disabled");
			}
			else{
				$("#nom_linea").attr("disabled","");
			}
		}

		function grupo(){
			if($("#grupo").val()!=""){
				$("#nom_grupo").attr("disabled","disabled");
			}
			else{
				$("#nom_grupo").attr("disabled","");
			}
		}';

		$filter = new DataFilter2('Filtro por Producto');

		$select=array(
			'IF(a.formcal=\'U\',a.ultimo,IF(a.formcal=\'P\',a.pond,IF(a.formcal=\'S\',a.standard,GREATEST(a.ultimo,a.pond)))) AS costo',
			'a.existen','a.marca','a.tipo','a.id',
			'TRIM(a.codigo) AS codigo',
			'a.descrip','precio1','precio2','precio3','precio4','b.nom_grup','b.grupo',
			'c.descrip AS nom_linea','c.linea','d.descrip AS nom_depto','d.depto AS depto',
			'a.base1','a.base2','a.base3','a.base4','e.sucursal','e.precio','e.id AS idcontrol'
		);

		$filter->db->select($select);
		$filter->db->from('sinv AS a');
		$filter->db->join('grup AS b','a.grupo=b.grupo');
		$filter->db->join('line AS c','b.linea=c.linea');
		$filter->db->join('dpto AS d','c.depto=d.depto');
		$filter->db->join('sinvcontrol AS e','e.codigo=a.codigo','left');
		//$filter->db->where('a.activo','S');
		$filter->script($script);

		$filter->codigo = new inputField('C&oacute;digo', 'codigo');
		$filter->codigo->db_name='a.codigo';
		$filter->codigo-> size=15;
		$filter->codigo->group = 'Uno';

		$filter->descrip = new inputField('Descripci&oacute;n', 'descrip');
		$filter->descrip->db_name='CONCAT_WS(" ",a.descrip,a.descrip2)';
		$filter->descrip-> size=30;
		$filter->descrip->group ='Uno';

		$filter->tipo = new dropdownField('Tipo', 'tipo');
		$filter->tipo->db_name='a.tipo';
		$filter->tipo->option('','Todos');
		$filter->tipo->option('Articulo' ,'Art&iacute;culo');
		$filter->tipo->option('Servicio' ,'Servicio');
		$filter->tipo->option('Descartar','Descartar');
		$filter->tipo->option('Consumo'  ,'Consumo');
		$filter->tipo->option('Fraccion' ,'Fracci&oacute;n');
		$filter->tipo->style='width:120px;';
		$filter->tipo->group = 'Uno';

		$filter->clave = new inputField('Clave', 'clave');
		$filter->clave->size  = 15;
		$filter->clave->group = 'Uno';

		$filter->proveed = new inputField('Proveedor', 'proveed');
		$filter->proveed->append($bSPRV);
		$filter->proveed->db_name='CONCAT_WS("-",`a`.`prov1`, `a`.`prov2`, `a`.`prov3`)';
		$filter->proveed -> size=10;
		$filter->proveed->group = 'Dos';

		$filter->depto2 = new inputField('Departamento', 'nom_depto');
		$filter->depto2->db_name='d.descrip';
		$filter->depto2 -> size=5;
		$filter->depto2->group = 'Dos';

		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, CONCAT(depto,'-',descrip) descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$filter->depto->in="depto2";
		$filter->depto->group = "Dos";
		$filter->depto->style='width:190px;';

		$filter->linea = new inputField("Linea", "nom_linea");
		$filter->linea->db_name="c.descrip";
		$filter->linea -> size=5;
		$filter->linea->group = "Dos";

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="c.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");
		$filter->linea2->in="linea";
		$filter->linea2->group = "Dos";
		$filter->linea2->style='width:190px;';

		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, CONCAT(linea,'-',descrip) descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo2 = new inputField("Grupo", "nom_grupo");
		$filter->grupo2->db_name="b.nom_grup";
		$filter->grupo2 -> size=5;
		$filter->grupo2->group = "Dos";

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$filter->grupo->in="grupo2";
		$filter->grupo->group = "Dos";
		$filter->grupo->style='width:190px;';

		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
			$filter->grupo->options("SELECT grupo, CONCAT(grupo,'-',nom_grup) nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$filter->grupo->option("","Seleccione un Departamento primero");
		}

		$filter->marca = new dropdownField('Marca', 'marca');
		$filter->marca->option('','Todas');
		$filter->marca->options("SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca"); 
		$filter->marca->style='width:220px;';
		$filter->marca->group = "Dos";

		$filter->fijado = new dropdownField('Solos precios no fijos ', 'fijado');
		//$filter->fijado->clause='';
		$filter->fijado->db_name='e.precio';
		$filter->fijado->option('','No');
		$filter->fijado->option('N' ,'Si');
		$filter->fijado->style='width:120px;';
		$filter->fijado->group = 'Uno';

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');

		$ggrid='';
		if($filter->is_valid()){
			$fijado=$filter->fijado->newValue;
			if($fijado=='S'){
			
			}
			
			
			$ggrid ='';
			foreach ($filter->_fields as $field_name => $field_copy){
				$ggrid.= form_hidden($field_copy->id, $field_copy->value);
			}

			$grid = new DataGrid('Art&iacute;culos de Inventario');
			$grid->order_by('codigo','asc');
			$grid->per_page = 15;
			$link2 = anchor('sincro/sinvcontrol/dataedit/modify/<#idcontrol#>','<#codigo#>');
			$link1 = anchor('sincro/sinvcontrol/dataedit/<raencode><#codigo#></raencode>/create/','<#codigo#>');

			$grid->column_orderby('C&oacute;digo',"<siinulo><#sucursal#>|$link1|$link2</siinulo>",'codigo');
			$grid->column_orderby('Descripci&oacute;n','descrip','descrip');
			$grid->column_orderby('Marca','marca','marca');
			for($i=1;$i<5;$i++){
				$obj='precio'.$i;
				$grid->column("Precio $i",$obj,'align=right');
			}
			$grid->column('Costo'     ,'<nformat><#costo#></nformat>'  ,'align=right');
			$grid->column('Existencia','<nformat><#existen#></nformat>','align=right');
			$grid->column('Sucursal'  ,'<sinulo><#sucursal#>|Todas</sinulo>');
			$grid->column('F. Precio'  ,'<sinulo><#precio#>|S</sinulo>');

			$grid->build();
			$ggrid.=$grid->output;

			//echo $this->db->last_query();
		}

		$data['content'] = '<div class="alert">'.$msj.'</div>';
		$data['content'].= $ggrid;
		$data['filtro']  = $filter->output;
		$data['title']   = heading('Control de cambio de precios');
		$data['head']    = $this->rapyd->get_head().script('jquery.pack.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data);
	}


	function dataedit($codigo){
		$this->rapyd->load('dataedit');
		$this->rapyd->uri->keep_persistence();
		$sucu=$this->db->escape($this->sucu);

		$edit = new DataEdit('Contro de precio', 'sinvcontrol');
		$edit->back_save  =true;
		$edit->back_cancel =true;
		$edit->back_cancel_save=true;
		$edit->back_url='sincro/sinvcontrol/index';

		$edit->codigo = new inputField('codigo','codigo');
		$edit->codigo->rule='max_length[15]|required';
		$edit->codigo->size =17;
		$edit->codigo->insertValue=$codigo;
		$edit->codigo->maxlength =15;

		$edit->sucursal = new dropdownField('Sucursal', 'sucursal');
		$edit->sucursal->rule='max_length[2]|required';
		$edit->sucursal->style='width:100px;';
		$edit->sucursal->option('','Selecionar');
		$edit->sucursal->options("SELECT codigo, sucursal  FROM sucu WHERE codigo <> $sucu AND CHAR_LENGTH(url)>0");

		$edit->precio = new dropdownField('Fijar precio','precio');
		$edit->precio->rule='max_length[1]|required';
		$edit->precio->option('','Selecionar');
		$edit->precio->option('S','Si');
		$edit->precio->option('N','No');
		$edit->precio->style='width:100px;';
		$edit->precio->append('"S&iacute;" para fijar este precio en la sucursal, "No" para permitir el cambio de precio en la sucursal');

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Control de precios en sucursales');
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `sinvcontrol` (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`sucursal` VARCHAR(2) NOT NULL,
			`codigo` VARCHAR(15) NOT NULL,
			`precio` CHAR(1) NOT NULL COMMENT 'S modifica el precio N no modifica el precio',
			PRIMARY KEY (`id`),
			UNIQUE INDEX `sucursal_codigo` (`sucursal`, `codigo`)
		)
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";
		var_dump($this->db->simple_query($mSQL));
	}
}