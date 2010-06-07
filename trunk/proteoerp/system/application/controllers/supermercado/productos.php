<?php
class Productos extends Controller {  
	
	function Productos() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
	}
	function index(){
		redirect('/supermercado/productos/anuales');
	}
	function diarias (){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'cprv1'),
			'titulo'  =>'Buscar Proveedor');
 
		$bSPRV=$this->datasis->modbus($mSPRV);		

		if(isset($_POST['anio']))  $anio=$_POST['anio'];
		if(isset($_POST['mes']))   $mes=$_POST['mes'];       else $mes='01';
		if(isset($_POST['ubica'])) $ubica=$_POST['ubica'];   else $ubica=NULL;
		if(isset($_POST['cprv1'])) $proveed=$_POST['cprv1']; else $cprv1=NULL;
		
		if(empty($mes)) $mes=date("m");
		if(empty($anio)) $anio=date("Y");
		
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
				
		$sinv=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;digo Producto',
			'descrip'  =>'Nombre'),
			'filtro'  =>array('codigo'=>'C&oacute;digo Producto','descrip'=>'Nombre'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar Vendedor');
		
		$cboton=$this->datasis->modbus($sinv);
		               
		$filter = new DataForm('supermercado/productos/diarias');
		//$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de Ventas Diarias');
		
		$filter->mes = new dropdownField("Mes/A&ntilde;o", "mes");  
		
		for($i=1;$i<13;$i++) 
		$filter->mes->option(str_pad($i, 2, '0', STR_PAD_LEFT),str_pad($i, 2, '0', STR_PAD_LEFT));  
		$filter->mes->size=2;
		$filter->mes->style='';
		$filter->mes->insertValue=$mes;	
		
		//cprv1
		$filter->almacen = new dropdownField("Almacen", "ubica");  
		$filter->almacen->option('','Todos');  
		$filter->almacen->options('SELECT ubica, ubides FROM caub WHERE gasto="N"');
		$filter->almacen->dbname='a.ubica';
		$filter->almacen->insertValue=$ubica;

		$filter->proveed = new inputField("Proveedor", "cprv1");
		$filter->proveed->db_name="b.cprv1";
		$filter->proveed->append($bSPRV);
		
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->in='mes';
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->rule = "max_length[4]"; 

		//$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/productos/diarias/'),array('anio','mes','ubica','cprv1')), $position="BL");
		$filter->submit("btnsubmit", "Buscar");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.codigo AS codigo","b.descrip AS nombre","SUM(a.cantidad) AS cantidad",
		"SUM(a.venta)AS grantotal", 
		"COUNT(*) AS numfac");  
		    		
		$grid->db->select($select);  
		$grid->db->from("costos AS a");
		$grid->db->join("maes AS b" ,"a.codigo=b.codigo");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);
		$grid->db->where("origen IN ('3I','3M','3R')");  
		if(!empty($ubica))   $grid->db->where('a.ubica',$ubica);
		if(!empty($proveed)) $grid->db->where('b.cprv1',$proveed);
		$grid->db->groupby("b.codigo");
		$grid->db->orderby("cantidad",'desc');
		$grid->db->limit(500);
		
		$grid->column("Codigo"            , "codigo","align='left'");
		$grid->column("Descripci&oacute;n", "nombre");
		$grid->column("Total"             , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Cantidad"          , "<number_format><#cantidad#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cant. Fact"        , "numfac",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
		//echo $grid->db->last_query();
		
		$data['content'] =  $filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = $this->rapyd->get_head()."<h1>Ventas Diarias</h1>";
		$this->load->view('view_ventanas', $data);
	}
}
?>