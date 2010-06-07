<?php
	class autoservicio extends Controller {
	
	function autoservicio (){
		parent::Controller(); 
		$this->load->library("rapyd");
    //$this->datasis->modulo_id(104,1);   
	}
	function index() {		
		$this->rapyd->load("datagrid","datafilter");
		

		//echo $grid->db->last_query();

		$data['script'] ="<script type='text/javascript'>
			$(function(){

				// Accordion
				$('#accordion').accordion({ header: 'h3' });
	
				// Tabs
				$('#tabs').tabs();
	

				// Dialog			
				$('#dialog').dialog({
					autoOpen: true,
					width: 600,
					buttons: {
						'Aceptar': function() { 
							$(this).dialog('close'); 
						} 
					}
				});
				
				// Dialog Link
				$('#dialog_link').click(function(){
					$('#dialog').dialog('open');
					return false;
				});

				// Datepicker
				$('#datepicker').datepicker({
					inline: true
				});
				
				// Slider
				$('#slider').slider({
					range: true,
					values: [17, 67]
				});
				
				// Progressbar
				$('#progressbar').progressbar({
					value: 20 
				});
				
				//hover states on the static widgets
				$('#dialog_link, ul#icons li').hover(
					function() { $(this).addClass('ui-state-hover'); }, 
					function() { $(this).removeClass('ui-state-hover'); }
				);
				
			});
		</script>";
		$ddata = array(
		          'name'        => 'cliente',
		          'id'          => 'cliente',
		          'value'       => '',
		          'maxlength'   => '100',
		          'size'        => '50',
		        );
		
		

			
		$data['content'] ='<!-- ui-dialog -->
		<div id="dialog" title="Nombre">
			<p><center><h2>Hagase usted mismo su presupuesto!</h2> Ingrese su nombre en el siguiente campo y siga las instrucciones'. form_input($ddata).'</center></p>
		</div>';

		
		//$data['content'] ='';
		$data["head"]    = $this->rapyd->get_head().script('jquery.js').script('jquery-ui.js').style('verde/jquery-ui.css');
		$data['title']   ='<h1>Autoservicio</h1>';
		$this->load->view('view_ventanas_sola', $data);
	}

}
?>

