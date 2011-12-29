<?php
class gpt_pro extends Controller {
	var $tits='Gu&iacute;a de productos farmaceuticos';
	var $titp='Gu&iacute;a de especialidades farmaceuticas';
	var $url ='farmacia/gpt_pro/';

	function gpt_pro(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('20F',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter('', 'gpt_pro');

		$filter->parr = new inputField('Par&aacute;metro', 'parr');
		$filter->parr->rule   = 'trim|required';
		$filter->parr->clause = '';

		//$filter->submit = new submitField('Buscar', 'submitbtn');
		//$filter->submit->in='parr';

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id_pro#></raencode>','<#nom_pro#>','target="framedetrepo" onclick="$(\'#cajafiltro\').hide();"');
		//$uri = anchor($this->url.'dataedit/show/<raencode><#id_pro#></raencode>','<#nom_pro#>');

		$grid = new DataGrid('Especialidades farmaceuticas');
		if (strlen($filter->parr->newValue)>0){
			$dbparr=$this->db->escape($filter->parr->newValue);
			$grid->db->where('MATCH(nom_pro,pres_pro,lab_pro,gen_pro,mono_pro) AGAINST ('.$dbparr.')');
		}
		$grid->order_by('nom_pro');
		$grid->per_page = 15;

		$grid->column_orderby('Nombre',$uri,'nom_pro','align="left"');
		$grid->column_orderby('Presentaci&oacute;n','pres_pro','pres_pro','align="left"');
		$grid->column_orderby('Laboratorio','lab_pro','lab_pro','align="left"');
		//$grid->column_orderby('Cod. Pro','cod_pro','cod_pro','align="left"');
		//$grid->column_orderby('Gen. Pro','gen_pro','gen_pro','align="left"');

		$grid->build();

		$data['filtro'] = $filter->output.$grid->output;
		$data['content'] = '<script type="text/javascript"> $(function(){ $("#cajafiltro").show(); }); </script>';
		//$data['content'].= $acti->output;
		$data['content'].= '<IFRAME src="'.site_url('farmacia/gpt_pro/dummy').'" width="100%" height="500" scrolling="auto" frameborder="0" name="framedetrepo">iframe no son soportados</IFRAME>';
		$data['head']    = $this->rapyd->get_head().script('jquery.js').script('jquery.highlight.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function dummy(){
		return true;
	}

	function dataedit(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataedit','datagrid');

		$edit = new DataEdit('Detalle del producto', 'gpt_pro');

		$edit->back_url = site_url($this->url.'filteredgrid/process');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		/*$edit->n1_pro = new inputField('N1_pro','n1_pro');
		$edit->n1_pro->rule='max_length[2]';
		$edit->n1_pro->size =4;
		$edit->n1_pro->maxlength =2;

		$edit->n2_pro = new inputField('N2_pro','n2_pro');
		$edit->n2_pro->rule='max_length[6]';
		$edit->n2_pro->size =8;
		$edit->n2_pro->maxlength =6;

		$edit->n3_pro = new inputField('N3_pro','n3_pro');
		$edit->n3_pro->rule='max_length[8]';
		$edit->n3_pro->size =10;
		$edit->n3_pro->maxlength =8;

		$edit->n4_pro = new inputField('N4_pro','n4_pro');
		$edit->n4_pro->rule='max_length[12]';
		$edit->n4_pro->size =14;
		$edit->n4_pro->maxlength =12;

		$edit->n5_pro = new inputField('N5_pro','n5_pro');
		$edit->n5_pro->rule='max_length[16]';
		$edit->n5_pro->size =18;
		$edit->n5_pro->maxlength =16;*/

		/*$edit->nom_pro = new inputField('Nombre','nom_pro');
		$edit->nom_pro->rule='max_length[200]';
		$edit->nom_pro->maxlength =200;

		$edit->pres_pro = new inputField('Presentaci&oacute;n','pres_pro');
		$edit->pres_pro->rule='max_length[200]';
		$edit->pres_pro->maxlength =200;

		$edit->lab_pro = new inputField('Laboratorio','lab_pro');
		$edit->lab_pro->rule='max_length[200]';
		$edit->lab_pro->maxlength =200;

		$edit->cod_pro = new inputField('Cod.Pro','cod_pro');
		$edit->cod_pro->rule='max_length[200]';
		$edit->cod_pro->maxlength =200;

		$edit->gen_pro = new inputField('Gen&eacute;ricos','gen_pro');
		$edit->gen_pro->rule='max_length[300]';
		$edit->gen_pro->maxlength =300;*/

		//$edit->mono_pro = new textareaField(' ','mono_pro');
		//$edit->mono_pro = new htmlField(' ','mono_pro');
		//$edit->mono_pro = new editorField(' ','mono_pro');
		
		//$edit->mono_pro->cols = 70;
		//$edit->mono_pro->rows = 4;
		$memo=$edit->getval('mono_pro');
		//$memo=htmlentities($memo);
		$memo=str_replace('<br/>','</p><p>',$memo);
		/*$memo=highlight_phrase($memo,'Composici&oacute;n:'  ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Advertencias:'        ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Reacciones Adversas:' ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Interacciones:'       ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Precauciones:'        ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Composicion:'         ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Contra indicaciones:' ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Indicaciones:'        ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Posolog&iacute;a:'    ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Posologia:'           ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Presentaci&oacute;n:' ,'</p><p><b>','</b>');
		$memo=highlight_phrase($memo,'Presentacion:'        ,'</p><p><b>','</b>');*/
		$edit->container = new containerField('alert','<p>'.$memo.'</p>');

		//$memo=$edit->getval('mono_pro');
		//$edit->container = new containerField("alert",htmlspecialchars($memo));

		$dbnom_pro=$this->db->escape($edit->getval('nom_pro'));
		$grid = new DataGrid('Productos relacionados','sinv');
		$grid->db->where('MATCH(descrip) AGAINST ('.$dbnom_pro.')');

		$grid->order_by('descrip');
		$grid->per_page = 40;

		$grid->column('C&oacute;digo'       ,'codigo' ,'align="left"');
		$grid->column('Descripci&oacute;n'  ,'descrip','align="left"');
		$grid->column('&Uacute;ltima compra','<dbdate_to_human><#fechac#></dbdate_to_human>','align="center"');
		$grid->column('Existencia'          ,'<nformat><#existen#></nformat>','align="right"');
		$grid->column('PVP'                 ,'<nformat><#precio1#></nformat>','align="right"');
		$grid->build();

		$edit->build();
		$data['content'] = $edit->output;
		if($grid->recordCount>0)
			$data['content'] .= $grid->output;
		$data['head']    = $this->rapyd->get_head();
		//$data['title']   = heading($this->tits);
		$data['title']   = '';
		$this->load->view('view_ventanas_sola', $data);
	}

	function cargamdb(){
		$path=reduce_double_slashes(FCPATH.'/uploads/traspasos');

		$dirlocal = $path.'/gpt.mdb';

		$mdb = mdb_open($dirlocal);
		if ($mdb === false) {
			return false;
		}

		//$tablas=mdb_tables($mdb);
		$tablas=array('pro');
		foreach($tablas as $tabla){
			//echo $tabla.br();
			$sistab="gpt_${tabla}";
			$this->db->simple_query("TRUNCATE $sistab");

			$tbl = mdb_table_open($mdb, $tabla);
			if ($tbl === false) continue;

			while ($row = mdb_fetch_assoc($tbl)){
				$sql = $this->db->insert_string($sistab, $row);
				$ban=$this->db->simple_query($sql);
				if($ban==false){
					$error++;memowrite($sql,'gpt_prod');
				}
			}
		}
	}

	function cargatxt(){
		$path=reduce_double_slashes(FCPATH.'/uploads/traspasos');
		$sistab='gpt_pro';
		$this->db->simple_query("TRUNCATE $sistab");
		$dirlocal = $path.'/data.txt';

		$arch = fopen($dirlocal, 'r');
		while (!feof($arch)){
			$lline = chop(fgets($arch));
			$data  = unserialize($lline);

			if(is_array($data)){
				$sql = $this->db->insert_string($sistab , $data);
				$ban=$this->db->simple_query($sql);
				if(!$ban) echo $sql."/n";
			}
		}
		fclose($arch);
	}


	function _pre_insert($do){
		return false;
	}

	function _pre_update($do){
		return false;
	}

	function _pre_delete($do){
		return false;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}

	function instalar(){
		if (!$this->db->table_exists('gpt_pro')) {
			$mSQL="CREATE TABLE `gpt_pro` (`id_pro` INT(10) NOT NULL DEFAULT '0',`n1_pro` VARCHAR(2) NULL DEFAULT NULL,`n2_pro` VARCHAR(6) NULL DEFAULT NULL,`n3_pro` VARCHAR(8) NULL DEFAULT NULL,`n4_pro` VARCHAR(12) NULL DEFAULT NULL,`n5_pro` VARCHAR(16) NULL DEFAULT NULL,`nom_pro` VARCHAR(200) NULL DEFAULT NULL,`pres_pro` VARCHAR(200) NULL DEFAULT NULL,`lab_pro` VARCHAR(200) NULL DEFAULT NULL,`cod_pro` VARCHAR(200) NULL DEFAULT NULL,
			`gen_pro` VARCHAR(300) NULL DEFAULT NULL,
			`mono_pro` LONGTEXT NULL,
			`logo_pro` VARCHAR(100) NULL DEFAULT NULL,
			PRIMARY KEY (`id_pro`),
			FULLTEXT INDEX `nom_pro_pres_pro_lab_pro_gen_pro` (`nom_pro`, `pres_pro`, `lab_pro`, `gen_pro`)
			)
			COMMENT='Guia Medica de Productos'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}

		//$mSQL="ALTER TABLE `gpt_pro`  DROP INDEX `nom_pro_pres_pro_lab_pro_gen_pro`,  ADD FULLTEXT INDEX `nom_pro_pres_pro_lab_pro_gen_pro_mono_pro` (`nom_pro`, `pres_pro`, `lab_pro`, `gen_pro`, `mono_pro`)";
		//$this->db->simple_query($mSQL);
	}
}