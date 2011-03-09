<?php
class invresu extends Controller {

	function invresu(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('51D',1);
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid','fields');
		$this->rapyd->uri->keep_persistence();

		/*$mSQL="SELECT 
		SUBSTR(`invresu`.`mes`,1,4) AS `anno`, 
		SUBSTR(`invresu`.`mes`,5,2) AS `mes`, 
		SUM(if((`invresu`.`minicial` > 0),`invresu`.`minicial`,0)) AS `inicial`,
		SUM(`invresu`.`mcompras`) AS `compras`,SUM(`invresu`.`mventas`) AS `ventas`,
		((SUM(`invresu`.`fisico`)-SUM(`invresu`.`mtrans`))+SUM(`invresu`.`mnotas`)) AS `retiros`,
		ABS(SUM(IF((`invresu`.`mfinal` < 0),`invresu`.`mfinal`,0))) AS `despachar`,
		SUM(IF((`invresu`.`mfinal` > 0),`invresu`.`mfinal`,0)) AS `final` 
		FROM `invresu` 
		GROUP BY `invresu`.`mes`";*/

		$error='';
		$cerror=0;
		if($this->input->post('pros')!==FALSE){
			$montos=$this->input->post('monto');
			foreach($montos as $anno => $mval){
				foreach($mval AS $mes=>$monto){
					$sqlmes = $anno.$mes;
					$mSQL="CALL sp_invresufix($sqlmes,$monto)";

					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'invresu'); $cerror++; }
				}
				
			}
			if($cerror>0){
				$error='Hubo algunos errores, se genero un centinela';
			}
		}

		$filter = new DataFilter('Filtro del libro de inventario','view_invresu');
		$filter->error_string=$error;

		$filter->fecha = new inputField('A&ntilde;o', 'anno');
		$filter->fecha->size     = 4;
		$filter->fecha->operator = '=';
		$filter->fecha->clause   = 'where';

		$filter->buttons('reset','search');
		$filter->build();

		$monto = new inputField('Monto', 'monto');
		$monto->db_name  ='final';
		$monto->grid_name='monto[<#anno#>][<#mes#>]';
		$monto->status   ='modify';
		$monto->size     =12;
		$monto->css_class='inputnum';
		$monto->autocomplete=false;

		//$uri = anchor('finanzas/bmov/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>','<#numero#>');
		$grid = new DataGrid('Lista');
		$grid->per_page = 12;

		$grid->column_orderby('A&ntilde;o','anno' ,'ano');
		$grid->column_orderby('Mes'       ,'mes'  ,'mes');
		$grid->column_orderby('Inicial'   ,'<nformat><#inicial#></nformat>'  ,'inicial'  ,'align=\'right\'');
		$grid->column_orderby('Compras'   ,'<nformat><#compras#></nformat>'  ,'compras'  ,'align=\'right\'');
		$grid->column_orderby('Ventas'    ,'<nformat><#ventas#></nformat>'   ,'ventas'   ,'align=\'right\'');
		$grid->column_orderby('Retiros'   ,'<nformat><#retiros#></nformat>'  ,'retiros'  ,'align=\'right\'');
		$grid->column_orderby('Despachar' ,'<nformat><#despachar#></nformat>','despachar','align=\'right\'');
		$grid->column_orderby('Final'     ,'<nformat><#final#></nformat>'    ,'final'    ,'align=\'right\'');

		$grid->column('Ajuste',$monto,'align=\'right\'');
		$grid->submit('pros', 'Guardar','BR');
		$grid->build();

		$ggrid =form_open('finanzas/invresu/index/search');
		$ggrid.=form_hidden('fecha', $filter->fecha->newValue);
		$ggrid.=$grid->output;
		$ggrid.=form_close();

		$script ='
		<script type="text/javascript">
		$(function() {
			$(".inputnum").numeric(".");
		});
		</script>';

		$data['content'] = $filter->output.$ggrid;
		$data['title']   = heading('Libro de inventario');
		$data['script']  = $script;
		$data['head']    = $this->rapyd->get_head().script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data);
	}
}