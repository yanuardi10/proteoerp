<?php
class carpetas extends Controller {  
  
	function carpetas() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');

	}  
	function index(){
		redirect ('ventas/carpetas/filteredgrid');
	}
	function filteredgrid(){
					
		$this->rapyd->load("datafilter2","datagrid");
		
		$link2=site_url('inventario/common/get_zona');
		$link3=site_url('inventario/common/get_estados');
				
		$filter = new DataFilter2("Filtro");
		$select=array("a.rifci","a.cliente","a.nombre","a.contacto","a.pais","a.zona","a.estado","a.municipio","a.telefono");  
		$filter->db->select($select);
		$filter->db->from("scli as a");
		$filter->db->join("pfac as b","a.cliente=b.cod_cli");
		$filter->db->groupby("a.cliente");
		
		$filter->cliente = new inputField("C&oacute;digo", "cliente");
		$filter->cliente->size=10;
		$filter->cliente->db_name="a.cliente";
		
		$filter->nombre= new inputField("Nombre","nombre");
		$filter->nombre->size=30;
		$filter->nombre->db_name="a.nombre";
		
		$filter->contacto = new inputField("Contacto", "contacto");
		$filter->contacto->size=30;
		$filter->contacto->db_name="a.contacto";
		
		$filter->pais = new dropdownField("Pa&iacute;s","pais");
		$filter->pais->style = "width:150px";
		$filter->pais->option("","Seleccionar");
		$filter->pais->options("SELECT codigo, nombre FROM pais ORDER BY codigo");
		$filter->pais->group = "Ubicaci&oacute;n";
		$filter->pais->onchange = "get_zona();";
		$filter->pais->db_name="a.pais";
		
		$filter->zona = new dropdownField("Zona", "zona");
		$filter->zona->style = "width:150px";
		$filter->zona->option("","Seleccionar");
		$filter->zona->options("SELECT codigo, nombre FROM zona ORDER BY nombre");
		$filter->zona->group = "Ubicaci&oacute;n";
		$filter->zona->onchange = "get_estados();";
		$filter->zona->db_name="a.zona";
	
		$filter->estado = new dropdownField("Estado","estado");
		$filter->estado->style = "width:150px";
		$filter->estado->option("","Seleccione una Zona");
		$filter->estado->options("SELECT codigo, nombre FROM estado ORDER BY codigo");
		$filter->estado->group = "Ubicaci&oacute;n";
		$filter->estado->onchange = "get_municipios();";
		$filter->estado->db_name="a.estado";
	
		$filter->municipios = new dropdownField("Municipio","municipio");
		$filter->municipios->style = "width:180px";
		$filter->municipios->option("","Seleccione una Modelo");
		$filter->municipios->options("SELECT codigo, nombre FROM municipio ORDER BY codigo");
		$filter->municipios->group = "Ubicaci&oacute;n";
		$filter->municipios->db_name="a.municipio";
					
		$filter->buttons("reset","search");
		$filter->build();

		$uri2 = anchor('ventas/carpetas/pedidos/<#cliente#>',"Ir");

		$grid = new DataGrid("Lista de Clientes");
		$grid->order_by("nombre","asc");
		$grid->per_page=15;
		
		$grid->column("Cliente","cliente");
		$grid->column("Nombre","nombre","nombre");
		$grid->column("RIF/CI","rifci");
		$grid->column("Contacto","contacto","align='left'");
		$grid->column("Telefono","telefono","align='left'");
		$grid->column("Pedidos",$uri2,"align='center'");
		
		$grid->add("ventas/sclifyco/dataedit/create");
		$grid->build();
		
		$link=site_url('ventas/sclifyco/get_municipios');
		$link1=site_url('ventas/sclifyco/get_estados');
		$link2=site_url('ventas/sclifyco/get_zona');
		$data['script']  =<<<script
		<script type="text/javascript" charset="utf-8">
		function get_zona(){
			var pais=$("#pais").val();
			$.ajax({
				url: "$link2"+'/'+pais,
				success: function(msg){
					$("#td_zona").html(msg);
					//alert(pais);
				}
			});
			get_estados();
		}
		function get_estados(){
			var zona=$("#zona").val();
			$.ajax({
				url: "$link1"+'/'+zona,
				success: function(msg){
					$("#td_estado").html(msg);
					//alert(zona);
				}
			});
			get_municipios();
		}
		function get_municipios(){
			var estado=$("#estado").val();
			$.ajax({
				url: "$link"+'/'+estado,
				success: function(msg){
					$("#td_municipio").html(msg);
				}
			});
		}
		</script>
script;
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Carpeta de Clientes</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$data["head"]   .= $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function get_zona($pais=null){
		$this->rapyd->load("fields");
		
		$zona = new dropdownField("Zona","zona");
		$zona->option("","Seleccione una Zona");
		$zona->status = "modify";
		$zona->onchange = "get_estados();";
		$zona->options("SELECT codigo, nombre FROM zona WHERE pais='$pais' ORDER BY codigo");
		$zona->style="width:350px";
		$zona->build();
		
		echo $zona->output;
	}
	function get_municipios($estado=null){
		$this->rapyd->load("fields");
		
		$municipios = new dropdownField("Municipio", "municipio");
		$municipios->option("","Seleccione un Municipio");
		$municipios->status = "modify";
		$municipios->options("SELECT codigo, nombre FROM municipio WHERE estado='$estado' ORDER BY codigo");
		$municipios->style="width:350px";
		$municipios->build();
		
		echo $municipios->output;
	}
	function get_estados($zona=null){
		$this->rapyd->load("fields");
		
		$estado = new dropdownField("Estados","estado");
		$estado->option("","Seleccione un Estado");
		$estado->status = "modify";
		$estado->onchange = "get_municipios();";
		$estado->options("SELECT codigo, nombre FROM estado WHERE zona='$zona' ORDER BY codigo");
		$estado->style="width:350px";
		$estado->build();
		
		echo $estado->output;
	}
	function pedidos($cliente=''){
		$this->rapyd->load("datagrid");
		
		$atts = array(
              'width'      => '800',
              'height'     => '600',
              'scrollbars' => 'yes',
              'status'     => 'yes',
              'resizable'  => 'yes',
              'screenx'    => '0',
              'screeny'    => '0'
            );
						  								
		$grid = new DataGrid("Lista de Pedidos");
		$select=array("numero","fecha","totals","iva","totalg","status");  
		$grid->db->select($select);
		$grid->db->from("pfac");
		$grid->db->where('cod_cli',$cliente);
		
		$uri=anchor_popup('ventas/pfacfyco/dataedit/show/<#numero#>','<#numero#>',$atts);  
		$uri2=anchor_popup('ventas/carpetas/cabonos/<#numero#>',"Abonos",$atts);
		$uri3=anchor_popup('ventas/carpetas/despacho/<#numero#>/',"Despacho",$atts);
		$uri4=anchor("formatos/ver/CARPETA/<#numero#>/$cliente","Imprimir");   
    
    $grid->column("Numero",$uri);
    $grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Sub-Total","<nformat><#totals#></nformat>","align=right");
		$grid->column("Impuesto","<nformat><#iva#></nformat>","align=right");
		$grid->column("Total","<nformat><#totalg#></nformat>","align=right");
		$grid->column("Status","status","align='center'");
		$grid->column("",$uri2);
		$grid->column("",$uri3);
		$grid->column("",$uri4);
			
		$grid->build();
		//echo $grid->db->last_query();
			
		$uri=anchor("ventas/carpetas/filteredgrid","Regresar");
				
		$data['content'] = '<pre>'.$uri.'</pre>'.$grid->output;
  	$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").phpscript('nformat.js').$this->rapyd->get_head();		
		$data['title']   = $this->rapyd->get_head()."<h1></h1>";
		$this->load->view('view_ventanas', $data);
	}	
	function cabonos($numero=''){
		
		$delete=$this->db->query("DELETE FROM abonos WHERE numero='$numero'");
		
		$mSQL=$this->db->query("SELECT montinicial,cinicial,montcuota,cuota FROM pfac WHERE numero='$numero'");
		$row = $mSQL->row();
		
		$cinicial=$row->cinicial;
		$montinicial=$row->montinicial;
		$montcuota=$row->montcuota;
		$cuota=$row->cuota;
		
		$i=$o=1;
		
		while($o<=$cinicial){	
				//$mSQL_1 ="";
				$mSQL_1=$this->db->query("INSERT INTO abonos set numero='$numero',ncuota='$i',mdolar='$montinicial'");
				Echo 'a'.$mSQL_1;
				$i++;
				$o++;		
		}
		
		$cant=$cinicial+$cuota;
		
		while($cinicial<$cant){		
			  $ncuota=$cinicial+1;
				$mSQL_2=$this->db->query("INSERT IGNORE INTO abonos set numero='$numero',ncuota='$ncuota',mdolar='$montcuota'");
				Echo 'b'.$mSQL_2;
				$i++;
				$cinicial++;
		}
		redirect("ventas/carpetas/abonos/$numero");	
	}
	function abonos($numero=''){
		
		$this->rapyd->load("datagrid2");
								  								
		$grid = new datagrid2("Lista de Abonos del Pedido $numero");
		$select=array("a.numero","a.ncuota","a.mdolar","SUM(b.dmonto*IF(a.ncuota=b.ncuota, 1, 0)) AS dmonto","SUM(b.bmonto*IF(a.ncuota=b.ncuota, 1, 0)) AS bmonto","a.mdolar-SUM(b.dmonto*IF(a.ncuota=b.ncuota, 1, 0)) as resta");  
		$grid->db->select($select);
		$grid->db->from("abonos as a");
		$grid->db->join("itabonos as b","a.numero=b.numero","LEFT");
		$grid->db->where('a.numero',$numero);
		$grid->db->groupby("a.numero,a.ncuota");
	
		$uri=anchor('ventas/carpetas/dabonos/<#numero#>/<#ncuota#>',"Detallado");
		$uri1=anchor("ventas/carpetas/general/$numero/","Pagos General");
    
    $grid->column("Nº Cuota","ncuota");
    $grid->column("Monto$","<nformat><#mdolar#></nformat>","align='center'");
		$grid->column("Monto Bs","<nformat><#bmonto#></nformat>","align=right");
		$grid->column("Abono","<nformat><#dmonto#></nformat>","align=right");
		$grid->column("Resta","<nformat><#resta#></nformat>","align=right");
		$grid->column("Ver",$uri,"align='center'");
		
		$grid->totalizar('mdolar','bmonto','dmonto','resta');
		
		//$grid->add("ventas/carpetas/dataedit/$numero/create");
		$grid->build();		
		//Echo $grid->db->last_query();
			
		$data['content'] = '<pre>'.$uri1.'</pre>'.$grid->output;
  	$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").phpscript('nformat.js').$this->rapyd->get_head();		
		$data['title']   = $this->rapyd->get_head()."<h1></h1>";
		$this->load->view('view_ventanas', $data);		
	}
	function dabonos($numero='',$ncuota=''){
		
		$this->rapyd->load("datagrid2");
								  								
		$grid = new DataGrid2("Lista de Detalles del Pedido $numero Cuota Número $ncuota");
		$select=array("fecha","ndeposito","dolar","dmonto","bmonto","ncuota","npago","id");  
		$grid->db->select($select);
		$grid->db->from("itabonos");
		$grid->db->where('numero',$numero);
		$grid->db->where('ncuota',$ncuota);
		
		$uri1=anchor("ventas/carpetas/dataedit/$numero/$ncuota/modify/<#id#>","<#npago#>");
	    
	  $grid->column("Nº Pago",$uri1); 
	  $grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
    $grid->column("Deposito","ndeposito","align='center'");
		$grid->column("Dolar$","<nformat><#dolar#></nformat>","align='right'");
    $grid->column("Monto$","<nformat><#dmonto#></nformat>","align='right'");
		$grid->column("Monto Bs","<nformat><#bmonto#></nformat>","align=right");
		//$grid->column("Resta","<nformat><#resta#></nformat>","align=right");
		
		$grid->totalizar('bmonto','dmonto');
		
		$grid->add("ventas/carpetas/dataedit/$numero/$ncuota/create");
		$grid->build();		
		//echo $grid->db->last_query();
		
		$uri=anchor("ventas/carpetas/abonos/$numero","Regresar");
			
		$data['content'] = '<pre>'.$uri.'</pre>'.$grid->output;
  	$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").phpscript('nformat.js').$this->rapyd->get_head();		
		$data['title']   = $this->rapyd->get_head()."<h1></h1>";
		$this->load->view('view_ventanas', $data);		
	}
	
	function general($numero=''){
		
		$this->rapyd->load("datagrid2");
								  								
		$grid = new DataGrid2("Lista de Pagos del Pedido $numero");
		$select=array("numero","fecha","ndeposito","dolar","dmonto","bmonto","ncuota","npago","id");  
		$grid->db->select($select);
		$grid->db->from("itabonos");
		$grid->db->where('numero',$numero);
		$grid->db->orderby('ncuota');
		
		$uri1=anchor("ventas/carpetas/dataedit/<#numero#>/<#ncuota#>/modify/<#id#>","<#npago#>");
	    
		$grid->column("Nº Cuota","ncuota");
	  $grid->column("Nº Pago",$uri1); 
	  $grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
    $grid->column("Deposito","ndeposito","align='center'");
    $grid->column("Dolar$","<nformat><#dolar#></nformat>","align='right'");
    $grid->column("Monto$","<nformat><#dmonto#></nformat>","align='right'");
		$grid->column("Monto Bs","<nformat><#bmonto#></nformat>","align=right");
		//$grid->column("Resta","<nformat><#resta#></nformat>","align=right");
		
		$grid->totalizar('bmonto','dmonto');
		
		//$grid->add("ventas/carpetas/dataedit/$numero/$ncuota/create");
		$grid->build();		
		//Echo $grid->db->last_query();
		
		$uri=anchor("ventas/carpetas/abonos/$numero/$ncuota","Regresar");
			
		$data['content'] = '<pre>'.$uri.'</pre>'.$grid->output;
  	$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").phpscript('nformat.js').$this->rapyd->get_head();		
		$data['title']   = $this->rapyd->get_head()."<h1></h1>";
		$this->load->view('view_ventanas', $data);		
	}		
	function dataedit($numero='',$ncuota=''){
			
	$this->rapyd->load("dataedit");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
		
		$edit = new DataEdit("Agregar Abono","itabonos");
		$edit->back_url = site_url("ventas/carpetas/dabonos/$numero/$ncuota");
		$edit->script($script, "create");
		$edit->script($script, "modify");
				
		$edit->numero = new inputField("Nº Pedido","numero");
		$edit->numero->maxlength=12;
		$edit->numero->size=12;
		$edit->numero->mode="autohide";
		$edit->numero->rule= "required";
		$edit->numero->css_class='inputnum';
		$edit->numero->insertValue =$numero; 
		
		$edit->ncuota = new inputField("NºCuota","ncuota");
		$edit->ncuota->maxlength=5;
		$edit->ncuota->size=5;
		$edit->ncuota->rule = "numeric|required";
		$edit->ncuota->css_class='inputnum';
		$edit->ncuota->insertValue =$ncuota; 
		
		$edit->npago = new inputField("NºPago","npago");
		$edit->npago->maxlength=5;
		$edit->npago->size=5;
		$edit->npago->rule = "numeric|required";
		$edit->npago->css_class='inputnum';
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->size = 12;
		$edit->fecha->rule= "required";
		
		$edit->ndeposito = new inputField("NºDeposito","ndeposito");
		$edit->ndeposito->maxlength=40;
		$edit->ndeposito->size=30;
		$edit->ndeposito->rule = "numeric|required";
		$edit->ndeposito->css_class='inputnum';
		
		$edit->dolar = new inputField("Dolar","dolar");
		$edit->dolar->maxlength=8;
		$edit->dolar->size=10;
		$edit->dolar->css_class='inputnum';
		$edit->dolar->rule='numeric|required';
			
		$edit->dmonto = new inputField("Monto Dolar","dmonto");
		$edit->dmonto->maxlength=25;
		$edit->dmonto->size=25;
		$edit->dmonto->css_class='inputnum';
		$edit->dmonto->rule='numeric|required';
		
		$edit->bmonto = new inputField("Monto Bolivares","bmonto");
		$edit->bmonto->maxlength=25;
		$edit->bmonto->size=25;
		$edit->bmonto->css_class='inputnum';
		$edit->bmonto->rule='numeric|required';
				
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Abonos al Pedido $numero</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data); 	
	}
	function despacho($numero=''){
		
		$this->rapyd->load("datagrid2");
		
		$monto=$this->datasis->dameval("SELECT SUM(mdolar) FROM abonos WHERE numero='$numero'");
		$credito=$this->datasis->dameval("SELECT credito FROM pfac WHERE numero='$numero'");
		$mcredito=$monto*$credito/100;
		$cdespachada=$this->datasis->dameval("SELECT SUM(mdolar)FROM itpfac WHERE numa='$numero'");
		$abonos=$this->datasis->dameval("SELECT SUM(dmonto)FROM itabonos WHERE numero ='$numero'");
		$total=$cdespachada-$abonos;
		$totald=$mcredito-$total;
								  								
		$grid = new DataGrid2("Control de Despacho del Pedido $numero");
		$select=array("mbolivar","a.mdolar","a.ultimdolar","b.codigo","a.codigoa","a.desca","a.cana","a.despacha","a.udespacha","a.cdespacha","a.fdespacha","a.ultidespachado","b.unidad","b.dolar","(a.cana - a.cdespacha)pordespacho");  
		$grid->db->select($select);
		$grid->db->from("itpfac as a");
		$grid->db->join("sinv as b","a.codigoa=b.codigo");
		$grid->db->where('a.numa',$numero);
			    
		$grid->column("Codigo","codigoa","align='center'");
	  $grid->column("Descripcion","desca","align='left'"); 
	  $grid->column("Cant","cana","align='center'");
	  $grid->column("Unidad","unidad","align='center'");
	  $grid->column("Despachado","cdespacha","align='center'");
	  $grid->column("Monto$","mdolar","align='right'");
	  //$grid->column("Ulti. Despacho","ultimdolar","align='right'");
	  $grid->column("MontoBs","mbolivar","align='right'");
	  $grid->column("Usuario","udespacha","align='center'");
	  $grid->column("Fecha","<dbdate_to_human><#fdespacha#></dbdate_to_human>","align='center'");
	  $grid->column("Ulti. Despacho","ultidespachado","align='center'");
	  $grid->column("Por Despacho","pordespacho","align='center'");
		
		$grid->totalizar('cana','cdespacha','mdolar','mbolivar','pordespacho');
		
		//$grid->add("ventas/carpetas/dataedit/$numero/$ncuota/create");
		$grid->build();		
		//Echo $grid->db->last_query();
					
		$data['content'] =  '<pre><b style="color:#2067B5;">'."Credito de $credito% por un monto de $mcredito$".'</b></pre>';
		$data['content'] .= '<pre><b style="color:#2067B5;">'."Monto Despachado $total$".'</b></pre>';
		$data['content'] .= '<pre><b style="color:#FE0E0A;">'."Credito Disponible para Despachar $totald$".'</b></pre>'.$grid->output;
  	$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").phpscript('nformat.js').$this->rapyd->get_head();		
		$data['title']   = $this->rapyd->get_head()."<h1></h1>";
		$this->load->view('view_ventanas', $data);		
	}
}   
?>  