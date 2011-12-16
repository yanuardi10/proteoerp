<?php
class Consulsucu extends Controller {
	function Consulsucu(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		
	}

	function precios(){
		//$this->datasis->modulo_id(11D,1);
		$this->rapyd->load("dataform","datatable",'datagrid');
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
		
		$filter = new DataForm('supermercado/consulsucu/precios/process');
		$filter->title('Seleccione un producto');
				
		$filter->codigo = new inputField("Codigo", "codigo");
		$filter->codigo->append($boton);  
		$filter->codigo->size=10;
		
		//$filter->button("btnsubmit", "Consultar", '', $position="BL");
		$filter->submit("btnsubmit","Cosulta");
		$filter->build_form();
 		
 		$data=array();
		$mSQL="SELECT sucursal,codigo AS sucu,url FROM sucu WHERE url REGEXP '^([[:alnum:]]+\.{0,1})+$' ORDER BY sucursal";
		$query = $this->db->query($mSQL);		
		if ($query->num_rows() > 0){
			foreach ($query->result_array() as $row){
				$data[]=$row;
			}
		}
		$grid = new DataGrid("Cajas disponibles",$data);
		
		$grid->column("Sucursal"    , '<b><#sucursal#></b>'); 
		$grid->column("URL"         , 'url');
		$grid->column("Precios 1-5" , '<div id="pr<#sucu#>">Seleccione un producto y Precione Consultar</div>','align="center"');
		
		$grid->build(); 
		$ddata=json_encode($grid->data);
		//echo $grid->db->last_query();
		
		$link =site_url('supermercado/consulsucu/preciosucu');
		$script='<script type="text/javascript">
		json_datos='.$ddata.';
		$(document).ready(function() {

			$("#df1").submit(function () {
				$("input[name=\'btnsubmit\']").attr("disabled","disabled");
			  x=0;
			  var producto=jQuery.trim($("#codigo").val());
			  $("#prser").text("");
				if (producto.length==0){
					alert(\'Debe introducir un producto\');
					return false;
				}
				$(this).attr("disabled","disabled");
				$.each(json_datos, function(i,obj){
			    $("#pr"+obj.sucu).load("'.$link.'"+\'/\'+obj.sucu+\'/\'+producto, function () {
			    	x=x+1; 
			    	if (x==json_datos.length)
			    		$("input[name=\'btnsubmit\']").removeAttr("disabled");
			    });
			  });
			  return false;
			});
		});
		</script>';

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Consulta de productos en cajas</h1>";
		$data["head"]    = $this->rapyd->get_head().script("jquery.js").script("plugins/jquery.json.min.js").$script;
		$this->load->view('view_ventanas', $data);
	}
	
	function preciosucu($sucursal=NULL,$codigo=null){
		$this->load->helper('string');
		$host= $this->datasis->dameval("SELECT CONCAT_WS('/',url,proteo) AS valor FROM sucu WHERE codigo='$sucursal'");
		
		if(!empty($codigo)){
			$server_url = "$host/rpcserver";
			$server_url =reduce_double_slashes($server_url);
			
			$this->load->library('xmlrpc');
			$this->xmlrpc->xmlrpcstr['http_error']="No hay conecci&oacute;n con la sucursal";
			
			$this->xmlrpc->server($server_url, 80);
			$this->xmlrpc->method('sprecios');
			
			$request = array($codigo);
			$this->xmlrpc->request($request);	
			
			if (!$this->xmlrpc->send_request()){
				echo $this->xmlrpc->display_error();
			}else{
				$retorno=$this->xmlrpc->display_response();
				if (count($retorno) > 0){
					$row = $retorno;
					echo '<table border=1><tr>';
					echo '<td width="660p" align=center colspan=6>'.$row[6].' '.$row[5].'</td></tr></tr>';
					echo '<td width="110p" align=right> '.number_format($row[0],2).'</td>';
					echo '<td width="110p" align=right> '.number_format($row[1],2).'</td>';
					echo '<td width="110p" align=right> '.number_format($row[2],2).'</td>';
					echo '<td width="110p" align=right> '.number_format($row[3],2).'</td>';
					echo '<td width="110p" align=right> '.number_format($row[4],2).'</td>';
					echo '</tr></table>';
				}else{
					echo 'Producto no existe';
				}
			}
		}else{
			echo 'Faltan parametros';	
		}
	}
}
?>