<?php
class Consulcajas extends Controller {
	function Consulcajas(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id('119',1);
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
		
		$filter = new DataForm('supermercado/consulcajas/index/process');
		$filter->title('Seleccione un producto');
				
		$filter->codigo = new inputField("Codigo", "codigo");
		$filter->codigo->append($boton);  
		$filter->codigo->size=10;
		
		$filter->button("btnsubmit", "Consultar", '', $position="BL");
		$filter->build_form();
 		$data[]=array('caja'=>'SERVIDOR','ubica'=>'SERVIDOR');
		$mSQL="SELECT caja, ubica FROM (caja) WHERE ubica REGEXP '^([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.(([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){2}([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])$' ORDER BY caja";
		$query = $this->db->query($mSQL);		
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result_array() as $row)
		   {
		      $data[]=$row;
		   }
		}
		$grid = new DataGrid("Cajas disponibles<div id='prser'></div>",$data);		
		//$grid->per_page = 5;  
		//$grid->db->select(array('caja','ubica'));
		//$grid->db->from("caja");
		//$grid->db->where("ubica REGEXP  '^([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.(([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.){2}([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])$' ");
		//$grid->db->orderby('caja');  
		
		$grid->column("Caja", '<b><#caja#></b>'); 
		$grid->column("Numero IP",  "ubica");
		$grid->column("Precios 1-5",'<div id="pr<#caja#>">Seleccione un producto y Precione Consultar</div>','align="center"');
		
		$grid->build(); 
		$ddata=json_encode($grid->data);
		//echo $grid->db->last_query();
		
		$link=site_url('supermercado/consulcajas/preciocaj');
		$link2=site_url('supermercado/consulcajas/precioser');
		$script=<<<script
		<script type='text/javascript'>
		json_datos=$ddata;
		$(document).ready(function() {
			
			$("input[name='btnsubmit']").click(function () {
				//$("input[name='btnsubmit']").attr("disabled","disabled");
			  
			  var producto=jQuery.trim($('#codigo').val());
			  $("#prser").text("");
				if (producto.length==0){
					alert('Debe introducir un producto');
					return false;
				}
				$(this).attr("disabled","disabled");
				$.each(json_datos, function(i,obj){
			    if(json_datos.length==i+1)
			    	$("#pr"+obj.caja).load("$link"+'/'+obj.ubica+'/'+producto, function () { $("input[name='btnsubmit']").removeAttr("disabled"); });
			    else
			    	$("#pr"+obj.caja).load("$link"+'/'+obj.ubica+'/'+producto);
			  });
			  $("#prser").load("$link2"+'/'+producto);

			});
		});

		</script>
script;

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Consulta de productos en cajas</h1>";
		$data["head"]    = $this->rapyd->get_head().script("jquery.js").script("plugins/jquery.json.min.js").$script;
		$this->load->view('view_ventanas', $data);
	}
	
	function club(){
		$this->datasis->modulo_id('11C',1);
		$this->rapyd->load("dataform","datatable",'datagrid');
		$this->load->library('table');
		
		$modbus=array(
			'tabla'   =>'club',
			'columnas'=>array(
				'cedula'  =>'C&eacute;dula',
				'nombres'  =>'Nombre',
				'apellidos'=>'Apellido',
				'empresa' =>'Empresa'),
			'filtro'  =>array('cedula'=>'C&eacute;dula','nombres'=>'Nombre','apellidos'=>'Apellido'),
			'retornar'=>array('cedula'=>'C&eacute;dula'),
			'titulo'  =>'Buscar en Club de Compra');
		
		$boton=$this->datasis->modbus($modbus);
		$boton='';
		
		$filter = new DataForm('supermercado/consulcajas/club/process');
		$filter->title('Seleccione un cliente');
				
		$filter->cedula = new inputField("C&eacute;dula", "cedula");
		$filter->cedula->append($boton);  
		$filter->cedula->size=15;
		
		$filter->submit("btnsubmit","Cosulta");
		//$filter->button("btnsubmit", "Consultar", '', $position="BL");
		$filter->build_form();
 		$data[]=array('caja'=>'SERVIDOR','ubica'=>'SERVIDOR');
		$mSQL="SELECT caja, ubica FROM (caja) WHERE ubica REGEXP '^([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.(([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){2}([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])$' ORDER BY caja";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			foreach ($query->result_array() as $row){
				$data[]=$row;
			}
		}
		$grid = new DataGrid("Cajas disponibles",$data);		 
		
		//$grid->column("Caja", '<b><#caja#>/<#ubica#></b>'); 
		//$grid->column("Numero IP",  "ubica");
		$grid->column("Clientes",'<div id="pr<#caja#>">Caja <#caja#> URL:<#ubica#></div>','align="center"');
		
		$grid->build(); 
		$ddata=json_encode($grid->data);
		//echo $grid->db->last_query();
		
		$link=site_url('supermercado/consulcajas/clubcaj');
		$script=<<<script
		<script type='text/javascript'>
		json_datos=$ddata;
		$(document).ready(function() {
			$("#df1").submit(function () {
			  var cod_tar=jQuery.trim($('#cedula').val());
			  $("#prser").text("");
				if (cod_tar.length==0){
					alert('Debe introducir una cedula');
					return false;
				}
				$(this).attr("disabled","disabled");
				$.each(json_datos, function(i,obj){
			    if(json_datos.length==i+1)
			    	$("#pr"+obj.caja).load("$link"+'/'+obj.ubica+'/'+cod_tar, function () { $("input[name='btnsubmit']").removeAttr("disabled"); });
			    else
			    	$("#pr"+obj.caja).load("$link"+'/'+obj.ubica+'/'+cod_tar);
			  });
			  return false;
			});
		});

		</script>
script;

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Consulta de clientes en cajas</h1>";
		$data["head"]    = $this->rapyd->get_head().script("jquery.js").script("plugins/jquery.json.min.js").$script;
		$this->load->view('view_ventanas', $data);
	}

	function precioser($codigo=FALSE){
		if(empty($codigo)) return false;
		error_reporting(0);
		ini_set('display_errors','Off');
		ini_set('mysql.connect_timeout','1');

		$link = @mysql_connect($this->db->hostname, $this->db->username, $this->db->password) or die('Error de coneccion');
		mysql_select_db($this->db->database) or die('Base de datos no seleccionable');
		$query = "SELECT precio1,precio2,precio3,precio4,precio5, descrip, barras FROM maes WHERE codigo='$codigo'";
		$result = mysql_query($query) or die('Consulta fallida ' . mysql_error());

		if (mysql_num_rows($result) > 0){
			$row = mysql_fetch_row($result);
			echo 'EN SERVIDOR: Precio 1: '.number_format($row[0],2);
			echo '|Precio 2: '.number_format($row[1],2);
			echo '|Precio 3: '.number_format($row[2],2);
			echo '|Precio 4: '.number_format($row[3],2);
			echo '|Precio 5: '.number_format($row[4],2);
		}else{
			echo 'Producto no existe';
		}
		mysql_free_result($result);
		mysql_close($link);
	}

	function preciocaj($ip=NULL,$codigo=FALSE){
		if(empty($ip) or empty($codigo)) return false;
		error_reporting(0);
		ini_set('display_errors','Off');
		ini_set('mysql.connect_timeout','1');

		if($ip=='SERVIDOR')
			$link = @mysql_connect($this->db->hostname, $this->db->username, $this->db->password) or die('Error de coneccion');
		else
			$link = mysql_connect($ip, 'datasis') or die('Maquina fuera de linea');
		
		mysql_select_db('datasis') or die('Base de datos no seleccionable');
		$query = "SELECT precio1,precio2,precio3,precio4,precio5, descrip, barras FROM maes WHERE codigo='$codigo'";
		$result = mysql_query($query) or die('Consulta fallida ' . mysql_error());

		if (mysql_num_rows($result) > 0){
			$row = mysql_fetch_row($result);
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
		mysql_free_result($result);
		mysql_close($link);
	}

	function clubcaj($ip=NULL,$codigo=FALSE){
		if(empty($ip) or empty($codigo)) return false;
		error_reporting(0);
		ini_set('display_errors','Off');
		ini_set('mysql.connect_timeout','1');

		if($ip=='SERVIDOR') {
			$link = @mysql_connect($this->db->hostname, $this->db->username, $this->db->password) or die('Error de coneccion');
			$mCaja = $ip;
		} else {
			$link  = mysql_connect($ip, 'datasis') or die('Maquina fuera de linea');
			$mCaja = "Caja: ".$this->datasis->dameval("SELECT caja FROM caja WHERE ubica='$ip'")." url: $ip";
		}
		
		mysql_select_db('datasis') or die('Base de datos no seleccionable');
		$query = "SELECT cedula,nombres,apellidos,direc1,direc2,empresa, cod_tar, IF(modifi IS NULL,'0000-00-00',modifi ) modifi FROM club WHERE cedula LIKE '$codigo%' ORDER BY cedula LIMIT 10";
		memowrite($query,'');
		$result = mysql_query($query) or die('Consulta fallida ' . mysql_error());

		if (mysql_num_rows($result) > 0){
			
			echo '<table border=1 width="95%">';
			echo '<tr><td colspan=4 bgcolor="#AABBCC"><b>'.$mCaja.'</b></td></tr>';
			echo '<tr>';
			echo ' <td><b>Tarjeta </b> </td>';
			echo ' <td><b>C&eacute;dula</b></td>';
			echo ' <td><b>Nombres</b></td>';
			echo ' <td><b>Modificado</b></td>';
			echo '</tr>';
			while($row=mysql_fetch_array($result)) {
				echo '<tr>';
				echo ' <td>'.$row[6].'</td>';
				echo ' <td>'.$row[0].'</td>';
				echo ' <td>'.$row[1].' '.$row[2].'</td>';
				echo ' <td>'.$row[7].'</td>';
				echo '</tr>';
			}
			Echo '</table>';
		}else{
			echo 'C&eacute;dula no existe';
		} 
		mysql_free_result($result);
		mysql_close($link);
	}	
}
?>