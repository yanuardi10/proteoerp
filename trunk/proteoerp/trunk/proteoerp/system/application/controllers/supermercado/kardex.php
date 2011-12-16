<?php
class Kardex extends Controller {
	
	function Kardex(){
		parent::Controller(); 
		$this->load->helper('text');
		$this->load->library("rapyd");
		//$this->rapyd->load_db();
	}
	
	function index(){
		$this->datasis->modulo_id(300,1);
		redirect("supermercado/kardex/filteredgrid");
	}
 
	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid2');
		$atts = array(
        'width'     =>'800',
        'height'    =>'600',
        'scrollbars'=>'yes',
        'status'    =>'yes',
        'resizable' =>'yes',
        'screenx'   =>'5',
        'screeny'   =>'5');
		function convierte($par,$link){
			$atts = array(
				'width'     =>'800',
				'height'    =>'600',
				'scrollbars'=>'yes',
				'status'    =>'yes',
				'resizable' =>'yes',
				'screenx'   =>'5',
				'screeny'   =>'5');
		 switch ($par) {
      case '3I': return(anchor_popup($link,'Ventas Caja'   ,$atts)); break;
      case '3M': return(anchor_popup($link,'Ventas Mayor'  ,$atts)); break;
      case '1T': return(anchor_popup($link,'Transferencias',$atts)); break;
      case '2C': return(anchor_popup($link,'Compras'       ,$atts)); break;
      case '4N': return(anchor_popup($link,'Nota/Entrega'  ,$atts)); break;
      case '6C': return('Conversiones'); break;
      case '5A': return('Ajustes'); break;
      case '0F': return('Inventario'); break;
      case '9F': return('Inventario'); break;
      default:   return($par); };	
		}
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
		
		$filter = new DataFilter("Kardex de Inventario (".anchor_popup('/supermercado/lfisico','Resumen de inventarios'   ,$atts).')');
		$filter->codigo = new inputField("C&oacute;digo De Producto", "codigo");
		$filter->codigo->append($boton);  

		$filter->ubica = new dropdownField("Almacen", "ubica");  
		$filter->ubica->option("","Todos");
		$filter->ubica->db_name='a.ubica';
		$filter->ubica->options("SELECT ubica,CONCAT(ubica,' ',ubides) descrip FROM caub WHERE gasto='N' ");
		$filter->ubica->operator="=";
		$filter->ubica->clause="where";

		$filter->fechad = new dateonlyField("Desde", "fecha","d/m/Y");
		$filter->fechad->operator=">=";
		$filter->fechad->insertValue = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-30,   date("Y")));

		$filter->fechah = new dateonlyField("Hasta", "fechah","d/m/Y");
		$filter->fechah->db_name='fecha';
		$filter->fechah->operator="<=";
		$filter->fechah->insertValue = date("Y-m-d");

		$filter->fechah->clause=$filter->fechad->clause=$filter->codigo->clause="where";
		$filter->fechah->size=$filter->fechad->size=10;
		
		$filter->buttons("reset","search");
		$filter->build();

		$data['content'] =  $filter->output;
		if(isset($_POST['codigo'])){
			$code=$_POST['codigo'];
			$mSQL="SELECT descrip FROM maes WHERE codigo='$code'";
			$query = $this->db->query($mSQL);
			$descrip='';
			if ($query->num_rows() > 0){
				$row = $query->row();
				$descrip=$row->descrip;
			}
			
			$link="/supermercado/kardex/grid/<#origen#>/<dbdate_to_human><#fecha#>|Ymd</dbdate_to_human>/<str_replace>/|:slach:|<#codigo#></str_replace>/<#ubica#>";
			$grid = new DataGrid2("($code) $descrip");
			$grid->agrupar('Almacen: ', 'almacen');
			$grid->use_function('convierte','number_format','str_replace');
			$grid->db->select("IFNULL( b.ubides , a.ubica ) almacen,a.ubica ,a.fecha, a.venta, a.cantidad, a.saldo, a.monto, a.salcant, a.codigo, a.origen, a.promedio");
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
			$data['content'] .= $grid->output;
			//echo $grid->db->last_query();
		}
		$data['title']   = "<h1>Kardex de Inventario</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function grid(){
		$tipo   =$this->uri->segment(4);
		$fecha  =$this->uri->segment(5);
		$codigo =$this->uri->segment(6);
		$almacen=$this->uri->segment(7);
		if ($fecha===FALSE or $codigo===FALSE or $tipo===FALSE or $almacen===FALSE) redirect("supermercado/kardex");		
		$this->rapyd->load('datagrid');

		$grid = new DataGrid();
		$grid->use_function('number_format');
		$grid->per_page = 20;
		
		if($tipo=='3I' or $tipo=='3M'){ //ventas de caja
			$grid->title('Facturas');
			$link=anchor("ventas/factura/dataedit/show/<#tipo_doc#>/<#numa#>","<#numero#>");
			$grid->column("N&uacute;mero",'numa');
			$grid->column("Cliente"      ,"cliente" );
			$grid->column("Cantidad"     ,"<number_format><#cantidad#>|2|,|.</number_format>",'align=right');
			$grid->column("Fecha"        ,"<dbdate_to_human><#fecha#></dbdate_to_human>"   ,'align=center');
			$grid->column("Cajero"       ,"cajero",'align=center');
			$grid->column("Precio"       ,"<number_format><#monto#>|2|,|.</number_format>",'align=right');
			$grid->column("Total"        ,"<number_format><#tota#>|2|,|.</number_format>" ,'align=right');
			$grid->db->select(array('a.numero AS numa','CONCAT( "(", b.cliente ,") ", b.nombres ) cliente','a.cantidad','a.fecha', 'a.cajero', 'a.monto','monto * cantidad tota', 'MID( a.numero ,1,2) AS tipo_doc'));
			$grid->db->from('vieite a');
			$grid->db->join('viefac b','b.numero=a.numero  AND b.caja=a.caja AND b.cajero=a.cajero AND a.fecha=b.fecha');
			//$grid->db->where("a.fecha=$fecha AND a.codigo='$codigo' AND a.almacen='$almacen'");
			$grid->db->where('a.fecha'  ,$fecha);
			$grid->db->where('a.codigo' ,$codigo);
			$grid->db->where('a.almacen',$almacen);
			$grid->order_by("a.numero","desc");
		}elseif($tipo=='1T'){ //Transferencias
			//$link=anchor("/supermercado/transferencia/dataedit/show/<#numero#>","<#numero#>");
			$link='numero';
			$grid->title('Tranferencias');
			$grid->column("N&uacute;mero",$link);
			$grid->column("Env&iacute;a"      ,"envia" );
			$grid->column("Recibe"            ,"recibe");
			$grid->column("Cantidad"          ,"<number_format><#cantidad#>|2|,|.</number_format>",'align=right');
			$grid->column("Fracción"          ,"<number_format><#totcant#>|2|,|.</number_format>",'align=right');
			$grid->column("Fecha"             ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'align=center');
			$grid->column("Observaci&oacute;n","observ1");
			$grid->column("Costo"             ,"<number_format><#costo#>|2|,|.</number_format>",'align=right');
			$grid->db->select(array('a.numero','b.envia' , 'b.recibe', 'a.cantidad', 'b.fecha', 'b.observ1', 'a.costo','a.totcant'));  
			$grid->db->from('ittran a');
			$grid->db->join('tran b','a.numero=b.numero','LEFT');
			//$grid->db->where("b.fecha=$fecha AND a.codigo='$codigo' ");
			$grid->db->where('b.fecha' ,$fecha);
			$grid->db->where('a.codigo',$codigo);
			$grid->order_by("numero","desc");
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
			$grid->db->select(array('a.numero', 'a.fecha', 'a.proveed', 'a.depo','a.cantidad', 'a.costo', 'a.importe','a.control'));  
			$grid->db->from('itscst a');
			$grid->db->join('scst b','a.control=b.control');
			//$grid->db->where("a.codigo='$codigo' AND b.recep=$fecha AND b.actuali>=b.fecha");
			$grid->db->where('a.codigo',$codigo);
			$grid->db->where('b.recep',$fecha);
			$grid->db->where('b.actuali >= b.fecha');
			$grid->order_by("numero","desc");
		}elseif($tipo=='4N'){ //Nota de entrega
			$link=anchor("ventas/notaentrega/dataedit/show/<#numero#>","<#numero#>");
			$grid->title('Notas de Entrega');
			$grid->column("N&uacute;mero",$link);
			$grid->column("Fecha"    ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'align=center');
			$grid->column("Proveedor","Nombre");
			$grid->column("Cantidad" ,"<number_format><#cana#>|2|,|.</number_format>",'align=right');
			$grid->column("Costo"    ,"<number_format><#precio#>|2|,|.</number_format>",'align=right');
			$grid->column("Importe"  ,"<number_format><#importe#>|2|,|.</number_format>",'align=right');
			$grid->db->select(array('a.numero', 'a.fecha', 'a.nombre', 'b.cana', 'b.precio', 'b.importe'));  
			$grid->db->from('snte a');
			$grid->db->join('itsnte b','a.numero=b.numero');
			//$grid->db->where("b.codigo='$codigo' AND a.fecha=$fecha ");
			$grid->db->where('a.fecha' ,$fecha);
			$grid->db->where('b.codigo',$codigo);
			$grid->order_by("numero","desc");
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
