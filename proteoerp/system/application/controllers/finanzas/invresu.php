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

		$filter = new DataFilter('Filtro del libro de inventario','view_invresutotal');
		$filter->error_string=$error;

		$filter->fecha = new inputField('A&ntilde;o', 'anno');
		$filter->fecha->size     = 4;
		$filter->fecha->operator = '=';
		$filter->fecha->clause   = 'where';

		$filter->buttons('reset','search');
		$filter->build();

		$monto = new inputField('Monto', 'monto');
		//$monto->db_name  ='final';
		$monto->grid_name='monto[<#anno#>][<#mes#>]';
		//$monto->status   ='modify';
		$monto->size     =14;
		$monto->css_class='inputnum';
		$monto->autocomplete=false;

		//$uri = anchor('finanzas/bmov/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>','<#numero#>');
		$grid = new DataGrid('Lista');
		$grid->per_page = 12;

		$uri2  =  anchor('finanzas/invresu/calcula/<#anno#><#mes#>',img(array('src'=>'images/engrana.png','border'=>'0','alt'=>'Editar')));
		$uri2 .= "&nbsp;&nbsp;";
		$uri2 .= anchor('#',img(array('src'=>'images/refresh.png','border'=>'0','alt'=>'PDF')),array('onclick'=>'foo(\''.base_url().'/finanzas/invresu/recalcula/<#anno#><#mes#>\');return false;'));

		$grid->column('A&ntilde;o','anno' ,'align="center"');
		$grid->column('Mes'       ,'mes'  ,'align="center"');
		$grid->column('Inicial'   ,'<nformat><#inicial#></nformat>'  ,'align=\'right\'');
		$grid->column('Compras'   ,'<nformat><#compras#></nformat>'  ,'align=\'right\'');
		$grid->column('Ventas'    ,'<nformat><#ventas#></nformat>'   ,'align=\'right\'');
		$grid->column('Retiros'   ,'<nformat><#retiros#></nformat>'  ,'align=\'right\'');
		$grid->column('Por Despachar' ,'<nformat><#despachar#></nformat>','align=\'right\'');
		$grid->column('Final'     ,'<nformat><#final#></nformat>'    ,'align=\'right\'');
		$grid->column('Accion',$uri2, 'align=\'center\'');
		//$grid->column('Ajuste',$monto,'align=\'right\'');
		//$grid->submit('pros', 'Guardar','BR');
		
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
		function foo(url){
			valor=$("#porcent").val();
			uurl=url+"/"+valor;
			//alert(uurl);
			$.get(uurl, function(data) {
				alert(data);
			});
			return false;
		}
		</script>';
		$porcent = "<div align='right'>Porcentaje de Variacion ";
		$porcent .= form_input(array('name'=>'porcent','id'=>'porcent','value'=>'0','size'=>'10','style'=>'text-align:right' ) );
		$porcent .= "</div>";
		$data['content'] = $filter->output.$porcent.$ggrid;
		$data['title']   = heading('Libro de inventario');
		$data['script']  = $script;
		$data['head']    = $this->rapyd->get_head().script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data);
	}

	function calcula(){
		$meco = $this->uri->segment(4);
		$ano = substr($meco,0,4)*100;
		while ( $meco-$ano < 13 ) {
			$this->db->simple_query("CALL sp_invresu(".$meco.")");
			$meco++;
		}
		redirect('finanzas/invresu');
	}

	function recalcula(){
		$meco = $this->uri->segment(4);
		$porcent = $this->uri->segment(5);
		$ano = substr($meco,0,4)*100;
		if ( abs($porcent) > 0  ) {
			$this->db->simple_query("CALL sp_invresufix(".$meco.",".$porcent.")");
			$meco++;
			// debe pasar los saldos a las siguientes meses
			while ( $meco-$ano < 13 ) {
				$this->db->simple_query("CALL sp_invresusum(".$meco.")");
				$meco++;
			};
			echo "Recalculo Concluido";
		} else {
			echo "Debe colocar un porcentaje";
		};

	}

}