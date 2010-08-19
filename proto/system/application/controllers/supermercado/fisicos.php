<?php
class Fisicos extends Controller {
	
	function Fisicos(){
		parent::Controller(); 
		$this->load->helper('text');
		$this->load->library("rapyd");
		//$this->rapyd->load_db();
	}
	
	function index(){
		$this->datasis->modulo_id(317,1);
		redirect("inventario/fisicos/filteredgrid");
	}
 
	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid2');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n',
				'precio1'=>'Precio 1',
				'precio2'=>'Precio 2',
				'precio3'=>'Precio 3',
				'precio4'=>'Precio 4'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar en inventario');
		
		$boton=$this->datasis->modbus($modbus);
		
		$filter = new DataFilter("Kardex de Inventario");
		//$filter->codigo = new inputField("C&oacute;digo De Producto", "codigo");
		//$filter->codigo->append($boton);  

		$filter->ubica = new dropdownField("Almacen", "ubica");  
		$filter->ubica->option("","Todos");
		$filter->ubica->db_name='a.ubica';
		$filter->ubica->options("SELECT ubica,CONCAT(ubica,' ',ubides) descrip FROM caub WHERE gasto='N' ");
		$filter->ubica->operator="=";
		$filter->ubica->clause="where";
		
		$filter->fecha = new dropdownField("Fecha", "fecha");  
		$filter->fecha->db_name='a.fecha';
		$filter->fecha->options("SELECT SQL_BIG_RESULT fecha,fecha descrip FROM maesfisico GROUP BY fecha ORDER BY fecha DESC LIMIT 5 ");
		$filter->fecha->operator="=";
		$filter->fecha->clause="where";
		
		$filter->buttons("reset","search");
		$filter->build();

		$data['lista'] =  $filter->output;
		if(isset($_POST['codigo'])){
			$code=$_POST['codigo'];
			$mSQL="SELECT CONCAT(descrip,' ',descrip2) descrip FROM sinv WHERE codigo='$code'";
			$query = $this->db->query($mSQL);
			$descrip='';
			if ($query->num_rows() > 0){
				$row = $query->row();
				$descrip=$row->descrip;
			}
			
			$link="/inventario/kardex/grid/<#origen#>/<dbdate_to_human><#fecha#>|Ymd</dbdate_to_human>/<str_replace>/|:slach:|<#codigo#></str_replace>/<#ubica#>";
			$grid = new DataGrid2("($code) $descrip");
			$grid->agrupar('Almacen: ', 'almacen');
			$grid->use_function('convierte','number_format','str_replace');
			$grid->db->select=array("IFNULL(b.ubides,a.ubica) almacen","a.ubica","a.fecha","a.venta","a.cantidad","a.saldo","a.monto","a.salcant","a.codigo","a.origen","a.promedio");
			$grid->db->from('costos a');
			$grid->db->join('caub b ','b.ubica=a.ubica','LEFT');
			$grid->db->orderby('almacen, fecha, origen');
			$grid->per_page = 20;
			$grid->column("Fecha"        ,"<dbdate_to_human><#fecha#></dbdate_to_human>");
			$grid->column("Or&iacute;gen","<convierte><#origen#>|$link</convierte>"          ,'align=left');
			$grid->column("Cantidad"     ,"<number_format><#cantidad#>|2|,|.</number_format>",'align=right');
			$grid->column("Acumulado"    ,"<number_format><#salcant#>|2|,|.</number_format>" ,'align=right');
			$grid->column("Monto"        ,"<number_format><#monto#>|2|,|.</number_format>"   ,'align=right');
			$grid->column("Saldo"        ,"<number_format><#saldo#>|2|,|.</number_format>"   ,'align=right');
			$grid->column("Costo Prom."  ,"<number_format><#promedio#>|2|,|.</number_format>",'align=right');
			$grid->column("Ventas"       ,"<number_format><#venta#>|2|,|.</number_format>"   ,'align=right');
			$grid->build();
			$data['lista'] .= $grid->output;
			//echo $grid->db->last_query();
		}
		$data['forma'] ='';
		
		$data['titulo'] = $this->rapyd->get_head()."<center><h2>Kardex de Inventario</h2></center>";
		$this->layout->buildPage('ventas/view_ventas', $data);

	}
	
	function grid(){
		$tipo   =$this->uri->segment(4);
		$fecha  =$this->uri->segment(5);
		$codigo =$this->uri->segment(6);
		$almacen=$this->uri->segment(7);
		if ($fecha===FALSE or $codigo===FALSE or $tipo===FALSE or $almacen===FALSE) redirect("inventario/kardex");		
		$this->rapyd->load('datagrid');

		$grid = new DataGrid();
		$grid->use_function('number_format');
		$grid->order_by("numero","desc");
		$grid->per_page = 20;
		
		if($tipo=='3I' or $tipo=='3M'){ //ventas de caja
			$grid->title('Facturas');
			$link=anchor("ventas/factura/dataedit/show/<#tipo_doc#>/<#numa#>","<#numero#>");
			$grid->column("N&uacute;mero",$link);
			$grid->column("Cliente"      ,"cliente" );
			$grid->column("Cantidad"     ,"<number_format><#cana#>|2|,|.</number_format>",'align=right');
			$grid->column("Fecha"        ,"<dbdate_to_human><#fecha#></dbdate_to_human>"   ,'align=center');
			$grid->column("vendedor"     ,"vendedor",'align=center');
			$grid->column("Precio"       ,"<number_format><#preca#>|2|,|.</number_format>",'align=right');
			$grid->column("Total"        ,"<number_format><#tota#>|2|,|.</number_format>" ,'align=right');
			
			$grid->db->select=array("a.numa","CONCAT(a.tipoa,a.numa) numero","CONCAT('(',b.cod_cli,')',b.nombre) cliente", "a.cana","a.fecha","a.vendedor","a.preca","a.tota","tipo_doc");
			$grid->db->from('sitems a');
			$grid->db->join('sfac b','b.numero=a.numa  AND b.tipo_doc=a.tipoa');
 			$grid->db->where('a.fecha',$fecha);	
			$grid->db->where('a.codigoa',$codigo);
			$grid->db->where('a.tipoa!=','X');
			$grid->db->where('b.almacen',$almacen);
		}elseif($tipo=='1T'){ //Transferencias
			$link=anchor("/inventario/transferencia/dataedit/show/<#numero#>","<#numero#>");
			$grid->title('Tranferencias');
			$grid->column("N&uacute;mero",$link);
			$grid->column("Env&iacute;a"      ,"envia" );
			$grid->column("Recibe"            ,"recibe");
			$grid->column("Cantidad"          ,"<number_format><#cantidad#>|2|,|.</number_format>",'align=right');
			$grid->column("Fecha"             ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'align=center');
			$grid->column("Observaci&oacute;n","observ1");
			$grid->column("Costo"             ,"<number_format><#costo#>|2|,|.</number_format>",'align=right');
			$grid->db->select=array("a.numero","b.envia "," b.recibe"," a.cantidad"," b.fecha"," b.observ1"," a.costo");
			$grid->db->from('itstra a');
			$grid->db->join('stra b','a.numero=b.numero','LEFT');
			$grid->db->where('b.fecha',$fecha);
			$grid->db->where('a.codigo',$codigo);
		}elseif($tipo=='2C'){ //compras
			$link=anchor("compras/scst/dataedit/show/<#control#>","<#numero#>");
			$grid->title('Compras');
			$grid->column("N&uacute;mero",$link);
			$grid->column("Fecha"    ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'align=center');
			$grid->column("Proveedor","proveed" );
			$grid->column("Deposito" ,"depo");
			$grid->column("Cantidad" ,"<number_format><#cantidad#>|2|,|.</number_format>",'align=right');
			$grid->column("Costo"    ,"<number_format><#costo#>|2|,|.</number_format>",'align=right');
			$grid->column("Importe"  ,"<number_format><#importe#>|2|,|.</number_format>",'align=right');
			
			$grid->db->select=array("a.numero","a.fecha","a.proveed","a.depo","a.cantidad","a.costo","a.importe","a.control");
			$grid->db->from('itscst a');
			$grid->db->join('scst b','a.control=b.control');
			$grid->db->where('a.codigo',$codigo);
			$grid->db->where('b.recep',$fecha);
			$grid->db->where('b.actuali>=','b.fecha');
		}elseif($tipo=='4N'){ //Nota de entrega
			$link=anchor("ventas/notaentrega/dataedit/show/<#numero#>","<#numero#>");
			$grid->title('Notas de Entrega');
			$grid->column("N&uacute;mero",$link);
			$grid->column("Fecha"    ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'align=center');
			$grid->column("Proveedor","Nombre");
			$grid->column("Cantidad" ,"<number_format><#cana#>|2|,|.</number_format>",'align=right');
			$grid->column("Costo"    ,"<number_format><#precio#>|2|,|.</number_format>",'align=right');
			$grid->column("Importe"  ,"<number_format><#importe#>|2|,|.</number_format>",'align=right');
			
			$grid->db->select=array("a.numero","a.fecha","a.nombre","b.cana","b.precio","b.importe");
			$grid->db->from('snte a');
			$grid->db->join('itsnte b','a.numero=b.numero');
			$grid->db->where('a.fecha',$fecha);
			$grid->db->where('b.codigo',$codigo);
		}
		
		$grid->build();
		//echo $grid->db->last_query();
		
		$data["crud"]   = $grid->output;
		$data["titulo"] = '';
		$content["content"]    = $this->load->view('rapyd/crud', $data, true);   
		$content["rapyd_head"] = $this->rapyd->get_head();
		$this->load->view('view_kardex', $content);
	}
	function factura(){
		$tipo   =$this->uri->segment(4);
		$fecha  =$this->uri->segment(5);
		$codigo =$this->uri->segment(6);
		$almacen=$this->uri->segment(7);

		$data["crud"]   = $grid->output;
		$data["titulo"] = '';
		$content["content"]    = $this->load->view('rapyd/crud', $data, true);   
		$content["rapyd_head"] = $this->rapyd->get_head();
		$this->load->view('view_kardex', $content);
	}
}
?>
