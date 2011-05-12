<?php
class tiketmaes extends Controller {
	 	
	function tiketmaes(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
 
 	function index(){
		redirect('supervisor/tiketmaes/filteredgrid');
	}
  
	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();
		
		$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
				'cliente' =>'C&oacute;digo Socio',
				'nombre'=>'Nombre', 
				'cirepre'=>'Rif/Cedula',
				'dire11'=>'Direcci&oacute;n'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Socio','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cliente'),
			'titulo'  =>'Buscar Socio');
			
		$boton =$this->datasis->modbus($mSCLId);

		$filter = new DataFilter('Filtro de Clientes');
		$select=array("a.id as idc","a.cliente","a.url","c.nombre","b.estado as estado","b.estampa as estampa","b.actualizado as actual","count(*) as cant"); 
		$filter->db->select($select);
		$filter->db->from('tiketconec AS a'); 
		$filter->db->join('scli AS c','c.cliente=a.cliente');
		$filter->db->join("tiketc AS b","b.sucursal=a.id");  
		
		//$filter->db->where(array("b.estado !="=>"R","b.estado !="=>'C'));
		$filter->db->where("b.estado !=","R");
		$filter->db->where("b.estado !=","C");
		$filter->db->groupby("a.id");
		

		$filter->cliente = new inputField('Cliente','cliente');
		$filter->cliente->db_name="a.cliente";
		$filter->cliente->size=20;
		$filter->cliente->append($boton);
		
		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->db_name="b.nombre";
		$filter->nombre->size=40;

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');
		
		
		$ver=anchor('supervisor/tiketmaes/ver_cliente/<#cliente#>','<#cliente#>');
		$uri = anchor('supervisor/conec/dataedit/show/<#idc#>','<#cliente#>');
		$uri2 = anchor('supervisor/tiket/traertiket/<#cliente#>','Traer Ticket');
		$ticket = anchor('supervisor/tiket/traertiket/','Traer Todos los Ticket');
		
		$uri_3  = anchor('supervisor/tiketmaes/ver_cliente/<#cliente#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Ver','height'=>'12')));

		$ticket = anchor('supervisor/tiket/traertiket/','Traer Todos los Ticket');
		$ticketc = anchor('supervisor/tiketc/filteredgrid','Ver Ticket de Clientes');
				
		$grid = new DataGrid('Lista de  clientes --> '.$ticket);
		$grid->order_by('a.cliente','asc');
		$grid->per_page = 20;

		$grid->column('Acci&oacute;n',$uri_3,'align=center');
		$grid->column_orderby('Cliente',$ver,'cliente');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Cantidad ','cant','cant');
		$grid->column_orderby('Fecha','<dbdate_to_human><#actual#></dbdate_to_human>','estampa');
		$grid->column_orderby("Url","url",'url');
		
		//$grid->column('Ticket',$uri2);
						
		
		$grid->build('datagridST');
		
		
		//************ SUPER TABLE ************* 
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
	headerRows : 1,
	onStart : function () {	this.start = new Date();},
	onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
	});
})();
//]]>
</script>
';
		$style ='
<style type="text/css">
.fakeContainer { /* The parent container */
    margin: 5px;
    padding: 0px;
    border: none;
    width: 620px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>	
';
//****************************************
		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['extras']  = $extras;	
		
		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		
		$data['script'] = script('jquery.js');
		$data["script"].= script('superTables.js');
		
		$data['title']  = heading('Tickets de Clientes');
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	
	function ver_cliente($cli){
		function ractivo($estado,$numero){
		 if($estado=='R'){
		 		$retorna= array(
    			'name'        => $numero,
    			'id'          => $estado,
    			'value'       => 'accept',
    			'checked'     => TRUE,
    		);
		 	
			}else{
				$retorna = array(
    			'name'        => $numero,
    			'id'          => $estado,
    			'value'       => 'accept',
    			'checked'     => FALSE,
    		);
			}
			return form_checkbox($retorna);
		}
		$this->rapyd->load("datafilter","datagrid2");
		
		$cliente=$this->datasis->dameval("Select nombre from scli where cliente='".$cli."'");
		//echo $cliente;
		 		
		$filter = new DataFilter("Filtro de Control de Tiket");
		$select=array("a.id as idm","a.sucursal","a.asignacion","a.idt as idt","a.padre","a.pertenece","a.prioridad","a.usuario as usuarios","a.contenido","a.estampa","a.actualizado","a.estado as testado","IF(a.estado='N','Nuevo',IF(a.estado='R','Resuelto',IF(a.estado='P','Pendiente','En Proceso')))as estado",
		"b.id","b.cliente as clientes","b.url","b.basededato","b.puerto","b.puerto","b.usuario","b.clave","b.observacion","c.nombre","a.minutos"); 
		$filter->db->select($select);
		$filter->db->from("tiketc AS a");   
		$filter->db->join("tiketconec AS b","a.sucursal=b.id");
		$filter->db->join("scli AS c","b.cliente=c.cliente");
		$filter->db->where("b.cliente","$cli");
		$filter->db->where("a.estado !=","R");
		$filter->db->where("a.estado !=","C");

		$filter->estado = new dropdownField("Estado", "estado");
		$filter->estado->option("P","Pendiente");

		$filter->prioridad = new dropdownField("Prioridad", "prioridad");
		$filter->prioridad->option("","Todos");
		
		$filter->asignacion = new dropdownField("Asignacion","asignacion");
		$filter->asignacion->option("","Todos");
		$filter->asignacion->options("SELECT codigo, codigo as value FROM tiketservi");
		$filter->asignacion->style = "width:150px";
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=40;
		
		$filter->usuario = new inputField("Usuario","usuario");
		$filter->usuario->size=20;
		
		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');
		
		$ticket = anchor('supervisor/tiket/traertiket/','Traer Todos los Ticket');
		$atras = anchor('supervisor/tiketmaes/',img(array('src'=>'images/regresar.jpg','border'=>'0','alt'=>'Editar','height'=>'40')));
		$uri2 = anchor('supervisor/tiketmaes/traertiket/'.$cli,'Traer Ticket');
		$uri_3  = anchor("supervisor/tiketc/dataedit/modify/<#idm#>",img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));
		
		$grid = new DataGrid2($atras.'<br><center>Lista de Ticket Pendientes y Nuevos -->'.$uri2."</center>");
		$grid->order_by("a.id","desc");
		$grid->per_page = 10;
		
		$link=anchor("supervisor/tiketc/dataedit/modify/<#idm#>", "<#idm#>");
		$grid->use_function('ractivo');

		$grid->column('Acci&oacute;n',$uri_3,'align=center');
		$grid->column("Resuelto", "<ractivo><#testado#>|<#idm#></ractivo>",'align="center"');
		
		$grid->column_orderby("Id",$link,'idm');
		$grid->column_orderby("Numero tiket","idt",'idt');
		$grid->column_orderby("Fecha de Ingreso","<dbdate_to_human><#estampa#>|d/m/Y h:m:s</dbdate_to_human>",'estampa');
		$grid->column_orderby("Cliente","nombre",'nombre');
		$grid->column_orderby("Estado","estado","estado");
		$grid->column("Usuario","usuarios");
		$grid->column("Contenido","contenido");
		$grid->column("Prioridad","prioridad");
		$grid->column("Asignacion","asignacion");
   
		$resp = '<a href="http://<#url#>/proteoerp/supervisor/tiket/dataedit/pertenece/<#idt#>/create">Ir</a>';

		$grid->column("Responder",$resp);
		
		$grid->totalizar('minutos');
		$grid->build('datagridST');
		//echo $grid->db->last_query();

		//************ SUPER TABLE ************* 
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
	headerRows : 1,
	onStart : function () {	this.start = new Date();},
	onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
	});
})();
//]]>
</script>
';
		$style ='
<style type="text/css">
.fakeContainer { /* The parent container */
    margin: 5px;
    padding: 0px;
    border: none;
    width: 740px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>	
';
//****************************************
		
		
		$script='';
		$url=site_url('supervisor/tiketc/activar');
		$data['script']='<script type="text/javascript">
			$(document).ready(function() {
				$("form :checkbox").click(function () {
    	       $.ajax({
						  type: "POST",
						  url: "'.$url.'",
						  data: "numero="+this.name+"&estado="+this.id,
						  success: function(msg){
						  //alert(msg);						  	
						  }
						});
    	    }).change();
			});
			</script>';
		
		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['extras']  = $extras;	
		
		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		
		$data['script'].= script('jquery.js');
		$data["script"].= script('superTables.js').script("jquery-1.2.6.pack.js").script("plugins/jquery.checkboxes.pack.js");

		//$data['content']= form_open('').$grid->output.form_close().$script;
		$data['title']  = "<h1>Ticket $cliente</h1>";
		$data["head"]   = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	
	}
	
	function traertiket($codigoc=null){
		//$this->datasis->modulo_id($this->modulo,1);
		//$this->load->helper('url');
		if(empty($codigoc)){
			$where='';
		}else{
			$where="WHERE cliente=".$this->db->escape($codigoc);
		}
		$mSQL="SELECT cliente,url,sistema,id FROM tiketconec ".$where;
		$host=$this->db->query($mSQL);
		foreach($host->result() as  $row){
			
			if(!empty($row->sistema)) $ruta=trim_slashes($row->sistema.'/rpcserver'); else $ruta='rpcserver';
			if(!empty($row->phtml))   $url=trim_slashes($row->url).':'.$row->phtml ; else $url=trim_slashes($row->url);
			$sucursal=$row->id;
			$cliente=$row->cliente;
			
			$server_url =$url.'/'.reduce_double_slashes($ruta);

			//$server_url = site_url('rpcserver');
			echo '<pre>'."\n";
			echo '('.$row->cliente.')-'.$server_url."\n";

			$fechad=$this->datasis->dameval('SELECT MAX(a.estampa) FROM tiketc AS a JOIN tiketconec AS b  ON a.sucursal=b.id  WHERE b.cliente='.$this->db->escape($cliente));
			if(empty($fechad)) $fechad=date('Ymd');
			echo $this->_traeticketrpc($server_url,array($fechad),$row->id);
			echo '</pre>'."\n";
		}
			$link=anchor("supervisor/tiketmaes/ver_cliente/$codigoc", "Regresar a Tikets");
			echo $link."\n";
	}
	
	function _traeticketrpc($server_url,$parametros,$sucursal='N/A'){
		$this->load->library('xmlrpc');
		$this->xmlrpc->xmlrpc_defencoding=$this->config->item('charset');

		$this->xmlrpc->server($server_url, 80);
		$this->xmlrpc->method('ttiket');

		$request = $parametros;
		$this->xmlrpc->request($request);

		$error=0;
		if (!$this->xmlrpc->send_request()){
			$rt=$this->xmlrpc->display_error();
		}else{
			$respuesta=$this->xmlrpc->display_response();
			foreach($respuesta AS $res){
				$arr=unserialize($res);
				foreach($arr AS $i=>$val)
				    $arr[$i]=base64_decode($val);
				$arr['idt']       =$arr['id'];
				$arr['sucursal']  =$sucursal;
				//$arr['asignacion']='KATHI';
				unset($arr['id']);

				$mSQL = $this->db->insert_string('tiketc', $arr);
				$rt=$this->db->simple_query($mSQL);
				if($rt===FALSE){ $error++; memowrite($mSQL,'tiketc');}
			}
			if($error==0) $rt="<b style='color:green;'>Transferencia Correcta</b>"; else $rt="<b style='color:red;'>Hubo algunos problemas en la insercion se genero un centinela</b>";
		}
		return $rt;
	}
	
}