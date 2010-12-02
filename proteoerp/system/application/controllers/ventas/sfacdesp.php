<?php
class sfacdesp extends Controller {

	function sfacdesp()
	{
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(111,1);
		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/ventas/". $this->uri->segment(2).EXT);
	}

	function index(){
		redirect("ventas/sfacdesp/filteredgrid");
	}

	function filteredgrid(){
		//filteredgrid//
		$this->rapyd->load("datafilter","datagrid");
		$this->load->library('encrypt');
		//$this->rapyd->uri->keep_persistence();

		//filter
		$filter = new DataFilter("Filtro");
		$select=array("a.cod_cli as cliente","a.fecha","if(a.referen='C','Cred','Cont') referen","a.numero","a.nombre","a.totalg as total","a.vd","d.nombre as vendedor");
		$select[]="GROUP_CONCAT(e.despacha) LIKE '%S%' AS parcial";
		$filter->db->select($select);
		$filter->db->from('sitems AS e');
		$filter->db->join('itsnot AS c' ,'c.factura=e.numa AND c.codigo=e.codigoa','LEFT');
		$filter->db->join('sfac AS a','e.numa=a.numero AND e.tipoa=a.tipo_doc');
		//$filter->db->join('snot AS c' ,'a.numero=c.factura','LEFT');
		$filter->db->join('vend AS d' ,'a.vd=d.vendedor');
		//$filter->db->join('sfac AS f','a.numero=e.factura','LEFT');
		$filter->db->groupby('e.numa');
		$filter->db->where('a.fdespacha IS NULL');
		$filter->db->where('a.tipo_doc','F');
		$filter->db->where('c.factura IS NULL');
		$filter->db->orderby("a.fecha DESC, a.numero");
		$filter->db->_escape_char='';
		$filter->db->_protect_identifiers=false;

		$filter->fechad = new dateonlyField("Desde", "fechad");
		$filter->fechah = new dateonlyField("Hasta", "fechah");
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="a.fecha";
		$filter->fechad->insertValue = date("Y-m-d");
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";

		$filter->numero = new inputField("N&uacute;mero", "a.numero");
		$filter->numero->size = 20;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = "ventas/cajeros/dataedit/show/<#cajero#>";

		if(!$this->rapyd->uri->is_set("search")) $filter->db->where('a.fecha','CURDATE()');

		function descheck($numero){
			$data = array(
			  'name'    => 'despacha[]',
			  'id'      => $numero,
			  'value'   => $numero,
			  'checked' => FALSE);
			return form_checkbox($data);
		}

		$seltodos='Seleccionar <a id="todos" href=# >Todos</a> <a id="nada" href=# >Ninguno</a> <a id="alter" href=# >Invertir</a>';
		//grid
		$grid = new DataGrid("$seltodos");
		$grid->use_function('descheck');
		$grid->use_function('colum');
		$grid->use_function('parcial');

		function colum($tipo_doc) {
			if ($tipo_doc=='Anulada')
				return ('<b style="color:red;">'.$tipo_doc.'</b>');
			else
				return ($tipo_doc);
		}

		function parcial($parcial) {
			if ($parcial)
				return '*';
			else
				return '';
		}

		$link=anchor('ventas/sfacdesp/parcial/<#numero#>','<#numero#>');
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>");
		$grid->column("Tipo","referen");
		$grid->column("N&uacute;mero",'<parcial><#parcial#></parcial>'.$link);
		$grid->column("Cliente","cliente");
		$grid->column("Nombre","nombre");
		$grid->column("Total","<number_format><#total#>|2</number_format>","align=right");
		$grid->column("Vendedor","(<#vd#>) <#vendedor#>","align=center");
		//$grid->column("Status","<colum><#tipo_doc#></colum>");
		//$grid->column("Parcial","<parcial><#parcial#></parcial>","align=center");
		$grid->column("Despachado","<descheck><#numero#></descheck>","align=center");
		$grid->build();
		//echo $grid->db->last_query();

		$script ='<script type="text/javascript">
		$(document).ready(function() {
			$("#todos").click(function() { $("#adespacha").checkCheckboxes();   });
			$("#nada").click(function()  { $("#adespacha").unCheckCheckboxes(); });
			$("#alter").click(function() { $("#adespacha").toggleCheckboxes();  });
		});
		</script>';
		$consulta =$grid->db->last_query();
		$mSQL = $this->encrypt->encode($consulta);

		$campo="<form action='/../../proteoerp/xlsauto/repoauto2/'; method='post'>
 		<input size='100' type='hidden' name='mSQL' value='$mSQL'>
 		<input type='submit' value='Descargar a Excel' name='boton'/>
 		</form>";

		$attributes = array('id' => 'adespacha');
		$data['content'] =  $filter->output.$campo;
		if($grid->recordCount>0)
		$data['content'] .=form_open('ventas/sfacdesp/procesar',$attributes).$grid->output.form_submit('mysubmit', 'Aceptar').form_close().$script;
		$data['title']   =  "<h1>Despacho Express</h1>";
		$data["head"]    =  script("jquery-1.2.6.pack.js");
		$data["head"]    .= script("plugins/jquery.checkboxes.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);

	}
	function procesar(){
		//print_r($_POST);
		foreach($_POST['despacha'] as $fila){
			$usuario = $this->session->userdata('usuario');
			$mSQL="UPDATE sitems SET despacha='S', fdespacha=CURDATE(), udespacha='$usuario' WHERE numa='$fila' AND tipoa='F' ";
			$this->db->simple_query($mSQL);
			$mSQL="UPDATE sfac SET fdespacha=CURDATE(), udespacha='$usuario' WHERE numero='$fila' AND tipo_doc='F' ";
			$this->db->simple_query($mSQL);
		}
		redirect("ventas/sfacdesp/filteredgrid/search/osp");
	}

	function activar(){

		$numero  = $this->db->escape($this->input->post('numa'));
		$codigo  = $this->db->escape($this->input->post('codigoa'));
		$usuario = $this->db->escape($this->session->userdata('usuario'));

		$mSQL="UPDATE sitems SET despacha=if(despacha='S','I','S'), fdespacha=if(despacha='S',CURDATE(),null), udespacha=$usuario WHERE codigoa=$codigo AND numa=$numero AND tipoa='F' ";
		$a   = $this->db->simple_query($mSQL);
		$mSQL="SELECT COUNT(*) FROM sitems AS a LEFT JOIN `itsnot` AS b ON `b`.`factura`=`a`.`numa` AND b.codigo=a.codigoa WHERE numa=$numero AND tipoa='F'  AND b.codigo IS NULL AND despacha<>'S'";
		$can = $this->datasis->dameval($mSQL);
		if($can==0){
			$mSQL="UPDATE sfac SET fdespacha=CURDATE(), udespacha=$usuario WHERE numero=$numero AND tipo_doc='F'";
			$this->db->simple_query($mSQL);
		}
		//$mSQL="UPDATE sfac SET fdespacha=CURDATE(), udespacha='$usuario' WHERE numero='$numero' AND tipo_doc='F' ";
		//$b=$this->db->simple_query($mSQL);

	}
	function parcial($numero){
		$this->rapyd->load("datafilter","datagrid");

		function ractivo($despacha,$numero,$codigoa){
		 $retorna= array(
			'name'  => $numero,
			'id'    => $codigoa,
			'value' => 'accept'
			);
			if($despacha=='S'){
				$retorna['checked']= TRUE;
			}else{
				$retorna['checked']= FALSE;
			}
			return form_checkbox($retorna);
		}

		function colum($tipo_doc) {
			if ($tipo_doc=='Anulada')
				return ('<b style="color:red;">'.$tipo_doc.'</b>');
			else
				return ($tipo_doc);
		}

		$grid = new DataGrid("Despacho parcial");
		$grid->db->_escape_char='';
		$grid->db->_protect_identifiers=false;

		$grid->db->select(array('a.codigoa','a.desca','a.cana','a.preca','a.tota','a.despacha','a.numa'));
		$grid->db->from('sitems AS a');
		$grid->db->join('itsnot AS b','a.codigoa=b.codigo AND a.numa=b.factura','LEFT');
		$grid->db->where('a.tipoa'   ,'F');
		$grid->db->where('a.numa'    ,$numero);
		$grid->db->where('b.factura IS NULL');

		$grid->use_function('ractivo');
		$grid->use_function('colum');

		$grid->column("C&oacute;digo"     ,"codigoa");
		$grid->column("Descripci&oacute;n","desca");
		$grid->column("Cantidad","cana","align=right");
		$grid->column("Precio","<nformat><#preca#></nformat>");
		$grid->column("Total" ,"<nformat><#tota#></nformat>","align=right");
		$grid->column("Despachado", "<ractivo><#despacha#>|<#numa#>|<#codigoa#></ractivo>",'align="center"');
		$grid->build();
		$tabla=$grid->output;
		//echo $grid->db->last_query();

		$script='';
		$url=site_url('ventas/sfacdesp/activar');
		$data['script']='<script type="text/javascript">
			$(document).ready(function() {
				$("form :checkbox").click(function () {
						$.ajax({
						  type: "POST",
						  url: "'.$url.'",
						  data: "numa="+this.name+"&codigoa="+this.id,
						  success: function(msg){
						  //alert(msg);
						  }
						});
					}).change();
			});
			</script>';

		$attributes = array('id' => 'adespacha');
		$data['content'] =  '';
		//if($grid->recordCount>0)
		$atras=anchor("ventas/sfacdesp/filteredgrid/search/osp",'Regresar');
		$data['content'] .=form_open('').$grid->output.form_close().$script.$atras;
		$data['title']   =  "<h1>Despacho Parcial</h1>";
		$data["head"]    =  script("jquery-1.2.6.pack.js");
		$data["head"]    .= script("plugins/jquery.checkboxes.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}
?>