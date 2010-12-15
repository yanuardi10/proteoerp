<?php
class Sfacfiscal extends Controller{

	function Sfacfiscal(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid');

		$link=site_url('supervisor/sfacfiscal/arreglaserial');
		$link2=site_url('supervisor/sfacfiscal/arreglanfiscal');
		$jquery='
		function riega(id){
			$.post("'.$link.'",{ cajero:$("#cajero").val(),fecha:$("#fecha").val(), serial:id },
			function(data){
				alert(data);
			});
		}

		function nfiscal(id){
			$.post("'.$link2.'",{ cajero:$("#cajero").val(),fecha:$("#fecha").val(), numero:id },
			function(data){
				alert(data);
			});
		}';

		function exissinv($cen,$t){
			if(!empty($cen)){
				if($t==1)
					$rt=form_button('asignar',$cen,'onclick="riega(\''.$cen.'\');"');
				else
					$rt=form_button('asignar',$cen,'onclick="nfiscal(\''.$cen.'\');"');
			}else{
				$rt='--';
			}
			return $rt;
		}

		$filter = new DataFilter('Clientes inconsistentes','sfac');
		$filter->script($jquery);

		$filter->fecha  = new dateonlyField('Desde','fecha');
		$filter->fecha->clause  ='where';
		$filter->fecha->db_name ='fecha';
		$filter->fecha->insertValue = date("Y-m-d");
		$filter->fecha->operator='=';
		$filter->fecha->rule    ='required';

		$filter->cajero = new dropdownField('Cajero', 'cajero');
		$filter->cajero->option('','Seleccionar');
		$filter->cajero->option(' ','Creditos');
		$filter->cajero->options('SELECT cajero, nombre FROM scaj ORDER BY cajero');

		$filter->buttons('reset','search');
		$filter->build();

		if($this->rapyd->uri->is_set('search') AND $filter->is_valid()){
			$fecha=$filter->fecha->newValue;
			$llink=anchor('supervisor/sfacfiscal/editsfac/show/<#tipo_doc#>/<#numero#>','<#numero#>');

			$grid = new DataGrid('Almacenes inconsistentes');
			$grid->use_function('exissinv');
			$grid->per_page = 30;
			$grid->db->orderby('numero');
			$grid->column('Fecha'      ,'<dbdate_to_human><#fecha#></dbdate_to_human>' ,'fecha');
			$grid->column('Nombre'     ,'nombre');
			$grid->column('Referencia' ,$llink);
			$grid->column('N.Fiscal'   ,'<exissinv><#nfiscal#>|2</exissinv>'  );
			$grid->column('Serial Maq.','<exissinv><#maqfiscal#>|1</exissinv>');
			$grid->build();
			//echo $grid->db->last_query();

			$tabla=$grid->output;
		}else{
			$tabla='';
		}

		$data['content']  = $filter->output.$tabla;
		$data['title']    = "<h1>Arreglos de consistencias fiscal en facturas</h1>";
		$data['head']     = $this->rapyd->get_head().script('jquery.js');
		$this->load->view('view_ventanas', $data);
	}

	function editsfac(){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Ajustes fiscales', 'sfac');
		$edit->back_url = site_url('supervisor/sfacfiscal/index');

		$edit->nfiscal =  new inputField('Numero fiscal','nfiscal');
		$edit->nfiscal->size = 15;
		$edit->nfiscal->maxlength=30;
		$edit->nfiscal->rule = 'trim|strtoupper|required';

		$edit->maqfiscal =  new inputField('Serial de la maquina fiscal', 'maqfiscal');
		$edit->maqfiscal->size     =15;
		$edit->maqfiscal->maxlength=20;
		$edit->maqfiscal->rule = 'trim|strtoupper|required';

		$edit->buttons('modify','save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Ajustes fiscales</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function arreglaserial(){
		$cajero=$this->input->post('cajero');
		$fecha =human_to_dbdate($this->input->post('fecha'));
		$serial=$this->input->post('serial');
		$mSQL='UPDATE sfac SET maqfiscal='.$this->db->escape($serial).' WHERE cajero='.$this->db->escape($cajero).' AND fecha='.$this->db->escape($fecha);
		if(!$this->db->simple_query($mSQL))
			echo 'Hubo un problema en el cambio';
		else
			echo 'Cambio realizado';
	}

	function arreglanfiscal(){
		$cajero=$this->db->escape($this->input->post('cajero'));
		$fecha =$this->db->escape(human_to_dbdate($this->input->post('fecha')));
		$numero=trim($this->input->post('numero'));

		$nnumero=ltrim($numero,'0');
		$mSQL="SELECT TRIM(nfiscal) AS nfiscal,tipo_doc,numero FROM (`sfac`) WHERE `fecha` = $fecha AND `cajero` = $cajero ORDER BY `numero`";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$c=false;
			foreach ($query->result() as $row){
				if($numero==$row->nfiscal){ 
					$c=true;
					continue;
				}
				if($c){
					$nnumero++;
					$nnnumero=str_pad($nnumero,8 , '0', STR_PAD_LEFT);
					$data = array('nfiscal' => $nnnumero);
					$where = 'tipo_doc = '.$this->db->escape($row->tipo_doc).' AND numero='.$this->db->escape($row->numero);
					$str = $this->db->update_string('sfac', $data, $where); 
					$this->db->simple_query($str);
				}
			}
		}

		$c=false;
		$nnumero=ltrim($numero,'0');
		$mSQL="SELECT TRIM(nfiscal) AS nfiscal,tipo_doc,numero FROM (`sfac`) WHERE `fecha` = $fecha AND `cajero` = $cajero ORDER BY `numero` DESC";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$c=false;
			foreach ($query->result() as $row){
				if($numero==$row->nfiscal){ 
					$c=true;
					continue;
				}
				if($c){
					$nnumero--;
					$nnnumero=str_pad($nnumero,8 , '0', STR_PAD_LEFT);
					$data = array('nfiscal' => $nnnumero);
					$where = 'tipo_doc = '.$this->db->escape($row->tipo_doc).' AND numero='.$this->db->escape($row->numero);
					$str = $this->db->update_string('sfac', $data, $where); 
					$this->db->simple_query($str);
				}
			}
		}
		echo 'Cambio realizado';
	}
}
