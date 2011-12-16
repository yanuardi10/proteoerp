<?php
class Consulcajas extends Controller {
	function Consulcajas(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		//$this->datasis->modulo_id(123,1);
		$this->rapyd->load("dataform","datatable");
		$this->load->library('table');
		
		$modbus=array(
			'tabla'   =>'maes',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n',
				'precio1'=>'Precio 1',
				'precio2'=>'Precio 2',
				'precio3'=>'Precio 3',
				'precio4'=>'Precio 4',
				'precio5'=>'Precio 5'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar en inventario');
		
		$boton=$this->datasis->modbus($modbus);
		
		$filter = new DataForm('supermercado/consulcajas/index/process');
		$filter->title('Seleccione un producto');
				
		$filter->codigo = new inputField("Codigo", "codigo");
		$filter->codigo->append($boton);  
		$filter->codigo->size=10;
		
		$filter->button("btnsubmit", "Consultar", '', $position="BL");
		$filter->build_form();
    
		$table = new DataTable(null);
		$table->cell_attributes = 'style="vertical-align:middle;"';
    
		$table->db->select(array('caja','ubica'));
		$table->db->from("caja");
		$table->db->where("ubica REGEXP  '^([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.(([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.){2}([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])$' ");
		$table->db->orderby('caja');
    
		$table->per_row  = 2;
		$table->per_page = 30;
    

		$celda = array(
			array(image('caja_abierta.gif',"Caja  <#caja#>", array('border'=>0,'align'=>'center')).'<br>Caja <#caja#>','<div name="cc<#caja#>" id="<#ubica#>"></div>' )
			);
    
    
		$table->cell_template = $this->table->generate($celda);
		$table->build();
		
		$link=site_url('supermercado/consulcajas/preciocaj');
		$script=<<<script
		<script type='text/javascript'>

		$(document).ready(function() {
			$("input[name='btnsubmit']").click(function () { 
			  
			  var producto=jQuery.trim($('#codigo').val());
				if (producto.length==0){
					alert('Debe introducir un producto');
					return false;
				}
				$("div[name^='cc']").each(function (i) {
			    $(this).load("$link"+'/'+this.id+'/'+producto);
			  });
			  
			});
		});

		</script>
script;

		$data['content'] = $filter->output.$table->output;
		$data['title']   = "<h1>Consulta de productos en cajas</h1>";
		$data["head"]    = $this->rapyd->get_head().script("jquery.js").$script;
		$this->load->view('view_ventanas', $data);
	}
	
	function preciocaj($ip=NULL,$codigo=FALSE){
		if(empty($ip) or empty($codigo)) return false;
		error_reporting(0);
		ini_set('display_errors','Off');
		ini_set('mysql.connect_timeout','0.5');

		$link = mysql_connect($ip, 'datasis') or die('Maquina fuera de linea');
		mysql_select_db('datasis') or die('Base de datos no seleccionable');
		$query = "SELECT precio1,precio2,precio3,precio4,precio5 FROM maes WHERE codigo='$codigo'";
		$result = mysql_query($query) or die('Consulta fallida ' . mysql_error());

		if (mysql_num_rows($result) > 0){
			$row = mysql_fetch_row($result);
			echo 'Precio 1: '.$row[0].'<br>';
			echo 'Precio 2: '.$row[1].'<br>';
			echo 'Precio 3: '.$row[2].'<br>';
			echo 'Precio 4: '.$row[3].'<br>';
			echo 'Precio 5: '.$row[4].'<br>';
		}else{
			echo 'Producto no existe';
		}
		mysql_free_result($result);
		mysql_close($link);
	}
}
?>