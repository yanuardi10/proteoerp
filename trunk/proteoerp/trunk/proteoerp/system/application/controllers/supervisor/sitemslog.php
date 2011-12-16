<?php require_once(BASEPATH.'application/controllers/ia.php');
//class sitemslog extends Controller {
class sitemslog extends ia {
	function sitemslog(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('925',1);
	}

	function index(){
		redirect('supervisor/sitemslog/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter('Filtro de B&uacute;squeda','sitemslog');

		$filter->fechad = new dateonlyField('Rango de Fechas', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechad->size=$filter->fechah->size=12;
		//$filter->fechad->insertValue = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-1,   date("Y")));
		$filter->fechad->insertValue = date('Y-m-d');
		$filter->fechah->insertValue = date('Y-m-d');
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';
		$filter->fechah->in='fechad';

		$filter->cajero = new dropdownField('Cajero','cajero');
		$filter->cajero->option('','Todos');
		$filter->cajero->options('SELECT cajero, nombre AS value FROM scaj ORDER BY cajero');
		$filter->cajero->style='width:150px;';

		$filter->vendedor = new dropdownField('Vendedor','vendedor');
		$filter->vendedor->option('','Todos');
		$filter->vendedor->options('SELECT vendedor, nombre FROM vend ORDER BY vendedor');
		$filter->vendedor->style='width:150px;';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 15;
		$filter->numero->maxlength=15;

		$filter->descrip = new inputField('Descripci&oacute;n', 'desca');

		$filter->usuario = new  dropdownField('Usuario','usuario');
		$filter->usuario->option('','Todos');
		$filter->usuario->options('SELECT us_codigo,CONCAT_WS(\'-\',us_codigo,us_nombre) AS val FROM usuario ORDER BY us_codigo');
		$filter->usuario->style='width:150px;';

		$filter->tipo = new  dropdownField('Operaci&oacute;n','tipo');
		$filter->tipo->option('','Todos');
		$filter->tipo->option('ABORTADO' ,'ABORTADO' );
		$filter->tipo->option('AGREGA'   ,'AGREGA'   );
		$filter->tipo->option('ELIMINADO','ELIMINADO');
		$filter->tipo->option('MODIFICA' ,'MODIFICA' );
		$filter->tipo->style='width:150px;';

		$filter->buttons('reset','search');
		$filter->build();

		if($this->rapyd->uri->is_set('search') AND $filter->is_valid()){
			$grid = new DataGrid('Resultados');
			$grid->per_page = 15;
			$grid->column_orderby('Operaci&oacute;n','tipo','tipo','align=\'left\'');
			$grid->column_orderby('Numero'     ,'<b><#tipoa#><#numa#></b>','numa','align=\'left\'');
			$grid->column_orderby('Fecha'      ,'<dbdate_to_human><#fecha#></dbdate_to_human> <#hora#>','fecha','align=\'center\'');
			$grid->column_orderby('C&oacute;digo'     ,'codigoa' ,'codigoa','align=\'left\'');
			$grid->column_orderby('Descripci&oacute;n','desca'   ,'desca'  ,'align=\'left\'');
			$grid->column_orderby('Cantidad'   ,'<nformat><#cana#></nformat>','cana' ,'align=\'right\'');
			$grid->column_orderby('Precio'     ,'<format><#preca#></nformat>','preca','align=\'right\'');
			$grid->column_orderby('Total'      ,'<nformat><#tota#></nformat>','tota' ,'align=\'right\'');
			$grid->column_orderby('Vendedor'   ,'vendedor','vendedor' ,'align=\'center\'');
			$grid->column_orderby('Cajero'     ,'cajero'  ,'cajero'   ,'align=\'center\'');
			$grid->column_orderby('Usuario'    ,'usuario' ,'usuario'  );
			$grid->build();
			$tabla=$grid->output;

			$sq = preg_replace('/LIMIT +[0-9]+[, ]*[0-9]*/', '', $grid->db->last_query());
			$sq = raencode(base64_encode($this->encrypt->encode($sq)));
			$tabla .= anchor('xlsauto/repo64/'.$sq,'Descargar a Excell');
		}else{
			$tabla='';
		}

		$data['content'] = $filter->output.$tabla;
		$data['title']   = heading('Bit&aacute;cora de Facturaci&oacute;n');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function detalle($numero=''){
		$this->rapyd->load('datagrid2');

		$grid = new DataGrid2('Detalle');
		$grid->db->select(array('codigoa','desca','cana','preca','iva','tota','detalle','combo','bonifica','costo'));
		$grid->db->from('sitemslog');
		$grid->db->where('id',$numero);
		$grid->per_page=20;

		$grid->column("Codigo"   ,"codigoa" ,"align=left");
		$grid->column("Descripci&oacute;n"  ,"desca","align=left");
		$grid->column("Cantidad" ,"cana"    ,"align=center");
		$grid->column("Precio  " ,"preca"   ,"align=right");
		$grid->column("Iva"      ,"iva"     ,"align=right");
		$grid->column("Total"    ,"tota"    ,"align=right");
		$grid->column("Detalle"  ,"detalle" ,"align=right");
		$grid->column("combo"    ,"combo"   ,"align=right");
		$grid->column("Bonifica" ,"bonifica","align=right");
		$grid->column("Costo"    ,"costo"   ,"align=right");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $grid->output;
		$data['title']   = heading('Detalle');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function analisis($mes=null){
		if(empty($mes)) return '';
		$this->load->helper('date');
		$udia=days_in_month(substr($mes,4),substr($mes,0,4));
		$fdesde=$mes.'01';
		$fhasta=$mes.$udia;

		$mSQL="SELECT a.usuario,COUNT(*) AS reg , a.tipo FROM sitemslog AS a
		WHERE a.fecha BETWEEN $fdesde AND $fhasta
		GROUP BY a.usuario,a.tipo
		UNION ALL
		SELECT usuario,COUNT(*) AS reg,'FACTURADO' FROM sitems 
		WHERE fecha BETWEEN $fdesde AND $fhasta GROUP BY usuario";

		$query = $this->db->query($mSQL);
		foreach ($query->result() as $row){
			$campo= strtolower($row->tipo);
			$usr  = $this->db->escape($row->usuario);
			$reg  = $this->db->escape($row->reg);

			$sql="INSERT INTO sitemsest (periodo,usuario,$campo) VALUES ($mes,$usr,$reg) ON DUPLICATE KEY UPDATE $campo=$reg";
			$ban=$this->db->simple_query($sql);
		}
	}

	function estudio(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter(' ', 'sitemsest');

		$filter->periodo = new inputField('Per&iacute;odo','periodo');
		$filter->periodo->rule      ='max_length[6]';
		$filter->periodo->size      =8;
		$filter->periodo->maxlength =6;

		$filter->buttons('reset', 'search');
		$filter->build();

		function estu($p1,$p2,$p3,$p4){
			$CI =& get_instance();
			$neuro=$CI->neurona('sitemslog',$p1,$p2,$p3,$p4);
			if($neuro>0){
				$rt='<b style="color:red">Activo</b>';
			}else{
				$rt='<b style="color:green">Inactivo</b>';
			}
			return $rt;
		}

		$grid = new DataGrid('');
		$grid->use_function('estu');
		$grid->order_by('usuario');
		$grid->per_page = 40;

		$url=anchor('supervisor/sitemslog/maprende/<#id#>','Aprender');
		$grid->column_orderby('Usuario'  ,'usuario','usuario','align="left"');
		$grid->column_orderby('Per&iacute;odo'  ,'periodo','periodo','align="left"');
		$grid->column_orderby('Agregado'  ,'<nformat><#agrega#></nformat>','agrega','align="right"');
		$grid->column_orderby('Eliminado' ,'<nformat><#eliminado#></nformat>','eliminado','align="right"');
		$grid->column_orderby('Modificado','<nformat><#modifica#></nformat>','modifica','align="right"');
		$grid->column_orderby('Facturado' ,'<nformat><#facturado#></nformat>','facturado','align="right"');
		$grid->column('Activo' ,'<estu><#agrega#>|<#eliminado#>|<#modifica#>|<#facturado#></estu>','align="center"');
		$grid->column('Entrenar' ,$url,'align="center"');
		$grid->build();

		//$data['filtro']  = $filter->output;
		$data['content'] = $filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading('Estudio del movimiento de cajas');
		$this->load->view('view_ventanas', $data);
	}

	function maprende($id){
		$this->E=0.5;
		$url='supervisor/sitemslog/estudio';
		$this->rapyd->uri->keep_persistence();
		$persistence = $this->rapyd->session->get_persistence($url, $this->rapyd->uri->gfid);
		$back= (isset($persistence['back_uri'])) ?$persistence['back_uri'] : $url;
		$row=$this->datasis->damerow('SELECT * FROM sitemsest WHERE id='.$this->db->escape($id));

		$neuro=$this->neurona('sitemslog',$row['agrega'],$row['eliminado'],$row['modifica'] ,$row['facturado']);
		$learn = ($neuro>0)? -1: 1;

		$this->aprende('sitemslog',array($row['agrega'],$row['eliminado'],$row['modifica'],$row['facturado']),$learn);

		redirect($back);
	}

	function instalar(){
		if(!$this->db->table_exists('sitemsest')){
			$mSQL="CREATE TABLE `sitemsest` (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`usuario` VARCHAR(50) NOT NULL DEFAULT '0',
			`periodo` VARCHAR(6) NOT NULL DEFAULT '0',
			`agrega` INT(10) NOT NULL DEFAULT '0',
			`eliminado` INT(10) NOT NULL DEFAULT '0',
			`modifica` INT(10) NOT NULL DEFAULT '0',
			`facturado` INT(10) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			UNIQUE INDEX `usuario_periodo` (`usuario`, `periodo`)
			)
			COMMENT='Resumen de sitems log'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}
	}
}
