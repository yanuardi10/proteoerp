<?php
class Conci extends Controller {

	function Conci(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function agregar(){
		$this->rapyd->load('dataform');
		print_r($_POST);

		$form = new DataForm('finanzas/conci/agregar/process');

		/*$form->fecha = new dateonlyField('Fecha','fecha','m/Y');
		$form->fecha->insertValue = date('Y-m-d');
		$form->fecha->rule ='required';
		$form->fecha->size =12;*/

		$form->mes = new dropdownField('Fecha', 'mes');
		$form->mes->rule ='required';
		$form->mes->style = 'width:50px';
		for($i=1;$i<=12;$i++){
			$mmes=str_pad($i,2,'0',STR_PAD_LEFT);
			$form->mes->option($mmes,$mmes);
		}

		$form->anio = new inputField('A&ntilde;o', 'anio');
		$form->anio->rule ='required';
		$form->anio->in='mes';
		$form->anio->size =5;

		$form->banco = new dropdownField('Banco', 'banco');
		$form->banco->option('','Seleccionar');
		$form->banco->rule ='required';
		$form->banco->options("SELECT codbanc AS CLAVE, banco FROM banc WHERE CHAR_LENGTH(tbanco)>0 ORDER BY banco"); 

		$form->submit('btnsubmit','Siguiente');
		$form->build_form();

		if ($form->on_success()){
			$this->_paso1();
			return TRUE;
		}

		$data['content'] = $form->output;
		$data['title']   = '<h1>Conciliaciones de Bancos</h1>';
		$data['script']  = '';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _paso1(){

		$this->rapyd->load('datafilter','datagrid');
		$hidden = array(
			'mes'  => $this->input->post('mes'),
			'anio' => $this->input->post('anio'),
			'banco'=> $this->input->post('banco'));

		$fecha=$hidden['anio'].$hidden['mes'].days_in_month($hidden['mes']);

		//$filter = new DataForm('finanzas/conci/agregar/process');
		$filter = new DataFilter('');

		$filter->db->from('bmov');
		$filter->db->where('codbanc'   ,$this->input->post('banco'));
		$filter->db->where('fecha <='  ,$fecha);
		$filter->db->where('anulado !=','S' );
		$filter->db->where('liable  !=','N' );

		$filter->ddata = new containerField("alert",form_hidden($hidden));

		$filter->tipo_op = new dropdownField('Tipo de Operacion', 'tipo_op');
		$filter->tipo_op->option('','Todos');
		$filter->tipo_op->option('NC','Nota de Credito');
		$filter->tipo_op->option('ND','Nota de debito');
		$filter->tipo_op->option('DE','Deposito');


		$filter->buttons('search');
		//$filter->build_form();
		//$filter->submit('btnsubmit','Siguiente');
		$filter->build();

		$grid = new DataGrid('Efectos po conciliar');
		
		/*$grid->db->from('bmov');
		$grid->db->where('codbanc'   ,$this->input->post('banco'));
		$grid->db->where('fecha <='  ,$fecha);
		$grid->db->where('anulado !=','S' );
		$grid->db->where('liable  !=','N' );
		
		$tipo_op=$this->input->post('tipo_op');
		if($tipo_op!==false){
			$grid->db->where('tipo_op',$tipo_op);
		}*/

		$grid->order_by('fecha','desc');
		$grid->per_page = 15;

		$grid->column('Numero','numero');
		$grid->column('Fecha' ,'<dbdate_to_human><#fecha#></dbdate_to_human>','align=\'center\'');
		$grid->column('Banco' ,'banco');
		$grid->column('Nombre','nombre');
		$grid->column('Monto' ,'<nformat><#monto#></nformat>' ,'align=\'right\'');
		$grid->column('Vista' ,'','align=\'center\'');

		$grid->build();
		//echo $grid->db->last_query();


		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Conciliaciones de Bancos</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


	function index(){
		//SELECT * FROM bmov 
		//WHERE codbanc='01' AND EXTRACT(YEAR_MONTH FROM fecha)<=201004 AND anulado!='S' AND liable!='N' AND 
		//(concilia=0 OR EXTRACT(YEAR_MONTH FROM concilia)>=20100430 OR concilia<fecha)
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter('');

		$filter->db->from('bmov');
		$filter->db->where('anulado !=','S' );
		$filter->db->where('liable  !=','N' );

		$filter->mes = new dropdownField('Fecha', 'mes');
		$filter->mes->clause='';
		$filter->mes->rule  ='required';
		$filter->mes->style = 'width:50px';
		for($i=1;$i<=12;$i++){
			$mmes=str_pad($i,2,'0',STR_PAD_LEFT);
			$filter->mes->option($mmes,$mmes);
		}

		$filter->anio = new inputField('A&ntilde;o', 'anio');
		$filter->anio->rule   ='required';
		$filter->anio->clause ='';
		$filter->anio->in     ='mes';
		$filter->anio->size   =5;

		$filter->banco = new dropdownField('Banco', 'codbanc');
		$filter->banco->rule  ='required';
		$filter->banco->clause='where';
		$filter->banco->option('','Seleccionar');
		$filter->banco->options('SELECT codbanc AS CLAVE, banco FROM banc WHERE CHAR_LENGTH(tbanco)>0 ORDER BY banco');
		$filter->banco->operator='=';

		$filter->tipo_op = new dropdownField('Tipo de Operacion', 'tipo_op');
		$filter->tipo_op->option('','Todos');
		$filter->tipo_op->option('NC','Nota de Cr&eacute;dito');
		$filter->tipo_op->option('ND','Nota de Debito');
		$filter->tipo_op->option('DE','Deposito');

		/*$filter->concilia = new dropdownField('Conciliado', 'concilia');
		$filter->concilia->option('','Todos');
		$filter->concilia->option('S','Si');
		$filter->concilia->option('N','No');*/

		//$filter->submit('btnsubmit','Descargar');
		$filter->buttons('search');
		$filter->build();

		if($this->rapyd->uri->is_set('search') AND $filter->is_valid()){
		
			function conci($conci,$codbanc,$tipo_op,$numero,$fecha){
				$fech=explode('-',$conci);
				$arr=array($codbanc,$tipo_op,$numero,$fecha);
				if($fech[0]+$fech[1]+$fech[2]==0){
					return form_checkbox($codbanc.$tipo_op.$numero, serialize($arr));
				}else{
					return form_checkbox($codbanc.$tipo_op.$numero, serialize($arr),TRUE).dbdate_to_human($conci);
				}
			}

			$fecha=$filter->anio->newValue.$filter->mes->newValue.days_in_month($filter->mes->newValue);
			$grid = new DataGrid('Efectos por conciliar');
			$grid->use_function('conci');
			$grid->order_by('fecha','desc');
			$grid->per_page = 15;

			$grid->column_orderby('Fecha'   ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
			$grid->column_orderby('Numero'  ,'numero','numero');
			$grid->column_orderby('Descripci&oacute;n','nombre','nombre');
			$grid->column_orderby('Monto'   ,'<nformat><#monto#></nformat>','monto','align=\'right\'');
			$grid->column_orderby('Conciliado',"<conci><#concilia#>|<#codbanc#>|<#tipo_op#>|<#numero#>|$fecha</conci>",'concilia','align=\'center\'');

			$grid->build();
			//echo $grid->db->last_query();
			$ggrid=$grid->output;
		}else{
			$ggrid='';
		}

		$data['content'] = $filter->output.$ggrid;
		$data['title']   = '<h1>Conciliaciones de Bancos</h1>';
		$data['script']  = '<script language="javascript" type="text/javascript">';
		$data['script'] .= '
		$(document).ready(function(){ 
			$(":checkbox").change(function(){
				name=$(this).attr("name");
				$.post("'.site_url('finanzas/conci/cconci').'",{ data: $(this).val()},
				function(data){
					 if(data=="1"){
						return true;
					 }else{
						$("input[name=\'"+name+"\']").removeAttr("checked");
						alert("Hubo un error, comuniquese con soporte tecnico");
						return false;
					}
				});
			});
		});';
		$data['script'] .= '</script>';
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$this->load->view('view_ventanas', $data);
	}
	
	function cconci(){
		$data=$this->input->post('data');
		if($data!==false){
			$pk=unserialize($data);

			$ddata = array('concilia' => $pk[3]);
			$where  =    ' codbanc = '.$this->db->escape($pk[0]);
			$where .= ' AND tipo_op = '.$this->db->escape($pk[1]);
			$where .= ' AND numero  = '.$this->db->escape($pk[2]);

			$mSQL = $this->db->update_string('bmov', $ddata, $where);
			//echo $mSQL;
			if(var_dum($this->db->simple_query($mSQL))){
				echo '1';
			}else{
				echo '0';
			}
		}
	}
}
