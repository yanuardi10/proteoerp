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

		$filter = new DataFilter('Filtro del libro de inventario','view_invresutotal');
		//$filter->error_string=$error;

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

		//$uri2  =  anchor('finanzas/invresu/calcula/<#anno#><#mes#>',img(array('src'=>'images/engrana.png','border'=>'0','alt'=>'Editar')));
		$uri2 = anchor('#',img(array('src'=>'images/engrana.png','border'=>'0','alt'=>'Calcula')),array('onclick'=>'bobo(\''.base_url().'finanzas/invresu/calcula/<#anno#><#mes#>\');return false;'));
		$uri2 .= "&nbsp;&nbsp;";
		$uri2 .= anchor('#',img(array('src'=>'images/refresh.png','border'=>'0','alt'=>'Rebaja')),array('onclick'=>'foo(\''.base_url().'finanzas/invresu/recalcula/<#anno#><#mes#>\');return false;'));

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
			uurl=url+"/"+valor;'."
			$.blockUI({
				message: $('#displayBox'), 
				css: { 
				top:  ($(window).height() - 400) /2 + 'px', 
				left: ($(window).width() - 400) /2 + 'px', 
				width: '400px' 
				}".' 			
			}); 
			$.get(uurl, function(data) {
				setTimeout($.unblockUI, 2); 
				alert(data);
			});
			return false;
		}
		function bobo(url){'."
			$.blockUI({
				message: $('#displayBox'), 
				css: { 
				top:  ($(window).height() - 400) /2 + 'px', 
				left: ($(window).width() - 400) /2 + 'px', 
				width: '400px' 
				}".' 			
			}); 
			$.get(url, function(data) {
				setTimeout($.unblockUI, 2); 
				alert(data);
			});
			return false;
		}
		</script>';
		$espera = '<div id="displayBox" style="display:none" ><p>Espere.....</p><img  src="'.base_url().'images/doggydig.gif" width="131px" height="79px"  /></div>';
		$porcent = "<div align='right'>Porcentaje de Variacion ";
		$porcent .= form_input(array('name'=>'porcent','id'=>'porcent','value'=>'0','size'=>'10','style'=>'text-align:right' ) );
		$porcent .= "</div>";
		$data['content'] = $filter->output.$porcent.$ggrid.$espera;
		
		$data['title']   = heading('Libro de inventario');
		
		$data['script']  = script("jquery.js");
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('plugins/jquery.blockUI.js');
		$data['script'] .= $script;

		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function calcula(){
		$meco = $this->uri->segment(4);
		$ano = substr($meco,0,4)*100;
		while ( $meco-$ano < 13 ) {
			$this->db->simple_query("CALL sp_invresu(".$meco.")");
			$meco++;
		}
		echo "Calculo Concluido";
		//redirect('finanzas/invresu');
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