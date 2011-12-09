<?php
//notaentrega
class Snte extends Controller {

	function snte(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(107,1);
		$this->back_dataedit='ventas/snte';
	}

	function index() {
		if ( !$this->datasis->iscampo('snte','id') ) {
			$this->db->simple_query('ALTER TABLE snte DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE snte ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE snte ADD UNIQUE INDEX numero (numero)');
		}
		$this->datasis->modulo_id(107,1);
		$this->snteextjs();
	}

	function filteredgrid(){

		$this->rapyd->load("datagrid","datafilter");
		
		$atts = array(
		'width'      => '800',
		'height'     => '600',
		'scrollbars' => 'yes',
		'status'     => 'yes',
		'resizable'  => 'yes',
		'screenx'    => '0',
		'screeny'    => '0'
		);
		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli'),
		'titulo'  =>'Buscar Cliente');

		$boton=$this->datasis->modbus($scli);
		
		$filter = new DataFilter("Filtro de Nota Entrega");
		$filter->db->select('fecha,numero,cod_cli,nombre,stotal,gtotal,impuesto,tipo, factura, usuario, estampa, transac');
		$filter->db->from('snte');
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d"); 
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";

		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size = 30;

		$filter->factura = new inputField("Factura", "factura");
		$filter->factura->size = 30;

		$filter->cliente = new inputField("Cliente","cod_cli");
		$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');

		$uri_3  = "<a href='javascript:void(0);' onclick='javascript:sntefactura(\"<#numero#>\")'>";
		$propiedad = array('src' => 'images/engrana.png', 'alt' => 'Modifica Nro de Factura', 'title' => 'Modifica Nro. de Factura','border'=>'0','height'=>'12');
		$uri_3 .= img($propiedad);
		$uri_3 .= "</a>";

		$uri = anchor('ventas/snte/dataedit/show/<#numero#>','<#numero#>');

		$url = "<a href=\"#\" onclick=\"window.open('".base_url()."formatos/verhtml/SNTE/<#numero#>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'')\"; heigth=\"600\" >";
		$url .="<img src='".base_url()."images/html_icon.gif'/></a>";

		$grid = new DataGrid();
		$grid->order_by("numero","desc");
		$grid->per_page = 15;  

		$grid->column_orderby("Acciones",$url,"align='center'");
		$grid->column_orderby("N&uacute;mero"	,$uri,'numero');
		$grid->column_orderby("Fecha"		,"<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
		$grid->column_orderby("Cliente"		,"cod_cli",'cod_cli');
		$grid->column_orderby("Nombre"		,"nombre",'nombre');
		$grid->column_orderby("Tipo"		,"tipo",'tipo');
		$grid->column_orderby("Factura"		,$uri_3."<#factura#>",'factura');
		$grid->column_orderby("Sub.Total"	,"<number_format><#stotal#>|2</number_format>",'stotal',"align=right");
		$grid->column_orderby("IVA"		,"<number_format><#impuesto#>|2</number_format>",'iva',"align=right");
		$grid->column_orderby("Total"		,"<number_format><#gtotal#>|2</number_format>",'gtotal',"align=right");
		
		$grid->add("ventas/snte/dataedit/create");
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
    width: 740px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>	
';
//****************************************
		
$script = '
<script type="text/javascript">
function sntefactura(mnumero){
	//var mserie=Prompt("Numero de Factura");
	//jAlert("Cancelado","Informacion");
	
	jPrompt("Numero de Factura","" ,"Cambio de Factura", function(mfactura){
		if( mfactura==null){
			jAlert("Cancelado","Informacion");
		} else {
			$.ajax({ url: "'.site_url().'ventas/snte/sntefactura/"+mnumero+"/"+mfactura,
				success: function(msg){
					jAlert("Cambio Finalizado "+msg,"Informacion");
					location.reload();
					}
			});
		}
	})

}
</script>';

		
		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['style']	.= style("jquery.alerts.css");

		$data['extras']  = $extras;		


		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;

		$data['title']   = heading('Notas de Entrega ');
	
		$data['script']  = $script;
		$data['script'] .= script('jquery.js');
		$data["script"] .= script("jquery.alerts.js");
		$data['script'] .= script('superTables.js');
		
		$data['head']    = $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
				
		$modbus=array(
		'tabla'   =>'sinv',
		'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
		'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
		'retornar'=>array(
			'codigo' =>'codigo_<#i#>',
			'descrip'=>'desca_<#i#>',
			'base1'  =>'precio1_<#i#>',
			'base2'  =>'precio2_<#i#>',
			'base3'  =>'precio3_<#i#>',
			'base4'  =>'precio4_<#i#>',
			'iva'    =>'itiva_<#i#>',
			'peso'   =>'sinvpeso_<#i#>',
			'tipo'   =>'sinvtipo_<#i#>',
		),
		'p_uri'=>array(4=>'<#i#>'),
		'where'   => '`activo` = "S" AND `tipo` = "Articulo"',
		'script'  => array('post_modbus_sinv(<#i#>)'),
		'titulo'  =>'Buscar Articulo');
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');
		
		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre', 
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n',
			'tipo'=>'Tipo'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre',
						  'dire11'=>'dir_cli','tipo'=>'sclitipo'),
		'titulo'  =>'Buscar Cliente',
		'script'  => array('post_modbus_scli()'));
		$btnc =$this->datasis->modbus($mSCLId);
		
		$do = new DataObject("snte");
		$do->rel_one_to_many('itsnte', 'itsnte', 'numero');
		$do->pointer('scli' ,'scli.cliente=snte.cod_cli','scli.tipo AS sclitipo','left');
		$do->rel_pointer('itsnte','sinv','itsnte.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');
		
		$edit = new DataDetails('Nota de entrega', $do);
		$edit->back_url = site_url('ventas/snte/filteredgrid');
		$edit->set_rel_title('itsnte','Producto <#o#>');
		
		$edit->back_url = $this->back_dataedit;
		
		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->vende = new  dropdownField ('Vendedor', 'vende');
		$edit->vende->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vende->style='width:200px;';
		$edit->vende->size = 5;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->maxlength=5;
		$edit->cliente->rule = 'required';
		$edit->cliente->append($btnc);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;

		$edit->factura = new inputField('Factura', 'factura');
		$edit->factura->size = 10;
		$edit->factura->when=array('show');

		$edit->almacen = new  dropdownField ('Almac&eacute;n', 'almacen');
		$edit->almacen->options('SELECT ubica, CONCAT(ubica,\' \',ubides) nombre FROM caub ORDER BY ubica');
		$edit->almacen->rule = 'required';
		$edit->almacen->style='width:200px;';
		$edit->almacen->size = 5;

		$edit->orden = new inputField("Orden", "orden");
		$edit->orden->size = 10;

		$edit->observa = new inputField("Observaci&oacute;n", "observa");
		$edit->observa->size = 37;

		$edit->dir_cli = new inputField("Direcci&oacute;n","dir_cli");
		$edit->dir_cli->size = 37;

		//$edit->dir_cl1 = new inputField(" ","dir_cl1");
		//$edit->dir_cl1->size = 55; 

		//Para saber que precio se le va a dar al cliente
		$edit->sclitipo = new hiddenField('', 'sclitipo');
		$edit->sclitipo->db_name     = 'sclitipo';
		$edit->sclitipo->pointer     = true;
		$edit->sclitipo->insertValue = 1;

		//Campos para el detalle
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		//$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itsnte';
		$edit->codigo->rule     = 'required';
		$edit->codigo->append($btn);
		$edit->codigo->style    = 'width:80%';

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size=36;
		$edit->desca->db_name='desca';
		$edit->desca->maxlength=50;
		$edit->desca->readonly  = true;
		$edit->desca->rel_id='itsnte';
		$edit->desca->style    = 'width:98%';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name  = 'cana';
		$edit->cana->css_class= 'inputnum';
		$edit->cana->rel_id   = 'itsnte';
		$edit->cana->maxlength= 10;
		$edit->cana->size     = 6;
		$edit->cana->rule     = 'required|positive';
		$edit->cana->autocomplete=false;
		$edit->cana->onkeyup  ='importe(<#i#>)';
		$edit->cana->style    = 'width:98%';

		$edit->precio = new inputField('Precio <#o#>', 'precio_<#i#>');
		$edit->precio->db_name   = 'precio';
		$edit->precio->css_class = 'inputnum';
		$edit->precio->rel_id    = 'itsnte';
		$edit->precio->size      = 10;
		$edit->precio->rule      = 'required|positive|callback_chpreca[<#i#>]';
		$edit->precio->readonly  = true;
		$edit->precio->style    = 'width:98%';

		$edit->importe = new inputField('Importe <#o#>', 'importe_<#i#>');
		$edit->importe->db_name='importe';
		$edit->importe->size=10;
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   ='itsnte';
		$edit->importe->style    = 'width:98%';

		for($i=1;$i<=4;$i++){
			$obj='precio'.$i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj.'_<#i#>');
			$edit->$obj->db_name   = 'sinv'.$obj;
			$edit->$obj->rel_id    = 'itsnte';
			$edit->$obj->pointer   = true;
		}
		$edit->itiva = new hiddenField('', 'itiva_<#i#>');
		$edit->itiva->db_name  = 'iva';
		$edit->itiva->rel_id   = 'itsnte';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name   = 'sinvpeso';
		$edit->sinvpeso->rel_id    = 'itsnte';
		$edit->sinvpeso->pointer   = true;

		$edit->sinvtipo = new hiddenField('', 'sinvtipo_<#i#>');
		$edit->sinvtipo->db_name   = 'sinvtipo';
		$edit->sinvtipo->rel_id    = 'itsnte';
		$edit->sinvtipo->pointer   = true;
		//fin de campos para detalle

		$edit->impuesto  = new hiddenField('Impuesto', 'impuesto');
		$edit->impuesto->size = 20;
		$edit->impuesto->css_class='inputnum';

		$edit->stotal  = new hiddenField('Sub.Total', 'stotal');
		$edit->stotal->size = 20;
		$edit->stotal->css_class='inputnum';

		$edit->gtotal  = new hiddenField('Total', 'gtotal');
		$edit->gtotal->size = 20;
		$edit->gtotal->css_class='inputnum';

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;

		$data['content'] = $this->load->view('view_snte', $conten,true);

		$data['title']   = heading('Nota de Entrega No. '.$edit->numero->value);

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= phpscript('nformat.js');

		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');

		$this->load->view('view_ventanas', $data);
		
	}

	function sntefactura(){
		$factura = $this->uri->segment($this->uri->total_segments());
		$numero  = $this->uri->segment($this->uri->total_segments()-1);
		$cod_cli = $this->datasis->dameval("SELECT cod_cli FROM snte WHERE numero='$numero'");
		$fecha   = $this->datasis->dameval("SELECT fecha FROM snte WHERE numero='$numero'");
		
		//revisa si elimina el nro
		if ($factura == 0) {
			$this->db->simple_query("UPDATE snte SET factura='', fechafac=0 WHERE numero='$numero'");
			logusu('SNTE',"Quita Nro. Factura $numero  ");
			echo "Nro de Factura eliminado";
		} else {
			if ($this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE tipo_doc='F' AND numero='$factura' AND cod_cli='$cod_cli'")==1)
			{
				$fechafac=$this->datasis->dameval("SELECT fecha FROM sfac WHERE tipo_doc='F' AND numero='$factura' AND cod_cli='$cod_cli'");
				$this->db->simple_query("UPDATE snte SET factura='$factura', fechafac=$fechafac WHERE numero='$numero'");
				logusu('SNTE',"Cambia Nro. Factura $numero -> $factura ");
				echo "Nro de Factura Cambiado ";
			} else {
				echo "Esa Factura no corresponde ";
			}
		}
	}

	function _pre_insert($do){
		$numero = $this->datasis->fprox_numero('nsnte');
		$transac= $this->datasis->fprox_numero('ntransa');
		$fecha  = $do->get('fecha');
		$vende  = $do->get('vende');
		$usuario= $do->get('usuario');
		$estampa= date('Ymd');
		$hora   = date("H:i:s");

		$iva=$stotal=0;
		$cana=$do->count_rel('itsnte');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itsnte','cana',$i);
			$itprecio  = $do->get_rel('itsnte','precio',$i);
			$itiva     = $do->get_rel('itsnte','iva',$i);
			$itimporte = $itprecio*$itcana;
			$iiva      = $itimporte*($itiva/100);

			$do->set_rel('itsnte','importe'  ,$itimporte,$i);
			$do->set_rel('itsnte','mostrado' ,$itimporte+$iiva,$i);

			$iva    +=$iiva ;
			$stotal +=$itimporte;
		}
		$gtotal=$stotal+$iva;
		$do->set('estampa' ,$estampa);
		$do->set('hora'    ,$hora);
		$do->set('transac' ,$transac);
		$do->set('stotal'  ,round($stotal,2));
		$do->set('gtotal'  ,round($gtotal,2));
		$do->set('impuesto',round($iva   ,2));

		return true;
	}

	function _post_insert($do){
		$codigo = $do->get('numero');
		$almacen= $do->get('almacen');

		$mSQL='UPDATE sinv JOIN itsnte ON sinv.codigo=itsnte.codigo SET sinv.existen=sinv.existen-itsnte.cana WHERE itsnte.numero='.$this->db->escape($codigo);
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'snte'); }

		$mSQL='UPDATE itsinv JOIN itsnte ON itsinv.codigo=itsnte.codigo SET itsinv.existen=itsinv.existen-itsnte.cana WHERE itsnte.numero='.$this->db->escape($codigo).' AND itsinv.alma='.$this->db->escape($almacen);
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'snte'); }

		$codigo=$do->get('numero');
		logusu('snte',"Nota entrega $codigo CREADO");
	}

	function chpreca($preca,$ind){
		$codigo  = $this->input->post('codigo_'.$ind);
		$precio4 = $this->datasis->dameval('SELECT base4 FROM sinv WHERE codigo='.$this->db->escape($codigo));
		if($precio4<0) $precio4=0;

		if($preca<$precio4){
			$this->validation->set_message('chpreca', 'El art&iacute;culo '.$codigo.' debe contener un precio de al menos '.nformat($precio4));
			return false;
		}else{
			return true;
		}
	}

	function _pre_update($do){
		return false;
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('snte',"Nota Entrega $codigo ELIMINADO");
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'snte');
	
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('snte');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		if ( $sort == '') $this->db->order_by( 'numero', 'desc' );

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}
		$sql = $this->db->_compile_select($this->db->_count_string . $this->db->_protect_identifiers('numrows'));
		$results = $this->datasis->dameval($sql);
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$arr = $this->datasis->codificautf8($query->result_array());

		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}


	function tabla() {
		$id   = isset($_REQUEST['id'])  ? $_REQUEST['id']   :  0;
		$cliente = $this->datasis->dameval("SELECT cod_cli FROM snte WHERE id='$id'");
		$mSQL = "SELECT cod_cli, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM smov WHERE cod_cli='$cliente' AND abonos<>monto AND tipo_doc<>'AB' ORDER BY fecha ";
		$query = $this->db->query($mSQL);
		$salida = '';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento en Cuentas X Cobrar</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']-$row['abonos']).   "</td>";
				$salida .= "</tr>";
				if ( $row['tipo_doc'] == 'FC' or $row['tipo_doc'] == 'ND' or $row['tipo_doc'] == 'GI' )
					$saldo += $row['monto']-$row['abonos'];
				else
					$saldo -= $row['monto']-$row['abonos'];
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		$query->free_result();


/*
		// Revisa formas de pago sfpa
		$mSQL = "SELECT codbanc, numero, monto FROM bmov WHERE transac='$transac' ";
		$query = $this->db->query($mSQL);
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento en Caja o Banco</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Bco</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['codbanc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table>";
		}
*/
		echo $salida;
	}

	function griditsnte(){
		$numero   = isset($_REQUEST['numero'])  ? $_REQUEST['numero']   :  0;
		if ($numero == 0 ) $numero = $this->datasis->dameval("SELECT MAX(numero) FROM snte")  ;

		$mSQL = "SELECT * FROM itsnte a JOIN sinv b ON a.codigo=b.codigo WHERE a.numero='$numero' ORDER BY a.codigo";
		$query = $this->db->query($mSQL);
		$results =  0; 
		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function sclibu(){
		$numero = $this->uri->segment(4);
		$id = $this->datasis->dameval("SELECT b.id FROM snte a JOIN scli b ON a.cod_cli=b.cliente WHERE numero='$numero'");
		redirect('ventas/scli/dataedit/show/'.$id);
	}

	function snteextjs() {
		$encabeza='NOTAS DE ENTREGA';

		$modulo = 'snte';
		$urlajax = 'ventas/snte/';
		
		$listados= $this->datasis->listados($modulo);
		$otros=$this->datasis->otros($modulo, $urlajax);


		$columnas = "
		{ header: 'Numero',      width: 60, sortable: true, dataIndex: 'numero' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Fecha',       width: 70, sortable: true, dataIndex: 'fecha' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Cliente',     width: 60, sortable: true, dataIndex: 'cod_cli' , field: { type: 'textfield' }, filter: { type: 'string' }, renderer: renderScli},
		{ header: 'Nombre',      width:200, sortable: true, dataIndex: 'nombre' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Base',        width: 90, sortable: true, dataIndex: 'stotal' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'IVA',         width: 90, sortable: true, dataIndex: 'impuesto' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Total',       width: 90, sortable: true, dataIndex: 'gtotal' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Vende',       width: 60, sortable: true, dataIndex: 'vende' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Almacen',     width: 60, sortable: true, dataIndex: 'almacen' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Factura',     width: 60, sortable: true, dataIndex: 'factura' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Direccion 1', width:200, sortable: true, dataIndex: 'dir_cli' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Direccion 2', width:200, sortable: true, dataIndex: 'dir_cl1' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Orden',       width: 60, sortable: true, dataIndex: 'orden' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Observa',     width:200, sortable: true, dataIndex: 'observa' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'F.Factura',   width: 60, sortable: true, dataIndex: 'fechafac' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Tipo',        width: 60, sortable: true, dataIndex: 'tipo' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Peso',        width: 60, sortable: true, dataIndex: 'peso' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Transaccion', width: 60, sortable: true, dataIndex: 'transac' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Modificado',  width: 60, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Id',         width:  60, sortable: true,  dataIndex: 'id' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},";

		$coldeta = "
	var Deta1Col = [
		{ header: 'Codigo',       width: 90, sortable: true, dataIndex: 'codigo',    field: { type: 'textfield' }, filter: { type: 'string' }, renderer: renderSinv},
		{ header: 'Descripcion',  width:200, sortable: true, dataIndex: 'desca',     field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cantidad',     width: 60, sortable: true, dataIndex: 'cana',      field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Precio',       width: 90, sortable: true, dataIndex: 'precio',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Importe',      width: 90, sortable: true, dataIndex: 'importe',   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'iva',          width: 90, sortable: true, dataIndex: 'iva',       field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Entregado',    width: 60, sortable: true, dataIndex: 'entregado', field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Tipo',         width: 60, sortable: true, dataIndex: 'tipo',      field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'id',           width: 60, sortable: true, dataIndex: 'id',        field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0000')},
		{ header: 'Modificado',   width: 70, sortable: true, dataIndex: 'modificado',field: { type: 'date' }, filter: { type: 'date' }},
]";

		$variables='';
		
		$valida="		{ type: 'length', field: 'numero',  min:  1 }";
		

		$funciones = "
function renderScli(value, p, record) {
	var mreto='';
	if ( record.data.cod_cli == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlAjax+'sclibu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.numero );
}


function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}

	";

		$campos = $this->datasis->extjscampos($modulo);

		$stores = "
	Ext.define('It".$modulo."', {
		extend: 'Ext.data.Model',
		fields: [".$this->datasis->extjscampos("it".$modulo)."],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlAjax + 'gridit".$modulo."',
				method: 'POST'
			},
			reader: {
				type: 'json',
				root: 'data',
				successProperty: 'success',
				messageProperty: 'message',
				totalProperty: 'results'
			}
		}
	});

	//////////////////////////////////////////////////////////
	// create the Data Store
	var storeIt".$modulo." = Ext.create('Ext.data.Store', {
		model: 'It".$modulo."',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});
	
	//////////////////////////////////////////////////////////
	//
	var gridDeta1 = Ext.create('Ext.grid.Panel', {
		width:   '100%',
		height:  '100%',
		store:   storeIt".$modulo.",
		title:   'Detalle de la NE',
		iconCls: 'icon-grid',
		frame:   true,
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		columns: Deta1Col
	});

	var ".$modulo."TplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR NOTA DE ENTREGA</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/verhtml/SNTE/{numero}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/ver/SNTE/{numero}\',     \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',   'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',		
		'</table>','nanai'
	];

	// Al cambiar seleccion
	gridMaest.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridMaest.down('#delete').setDisabled(selectedRecord.length === 0);
			gridMaest.down('#update').setDisabled(selectedRecord.length === 0);
			numero = selectedRecord[0].data.numero;
			gridDeta1.setTitle(selectedRecord[0].data.numero+' '+selectedRecord[0].data.nombre);
			storeIt".$modulo.".load({ params: { numero: numero }});
			var meco1 = Ext.getCmp('imprimir');
			Ext.Ajax.request({
				url: urlAjax +'tabla',
				params: { numero: numero, id: selectedRecord[0].data.id },
				success: function(response) {
					var vaina = response.responseText;
					".$modulo."TplMarkup.pop();
					".$modulo."TplMarkup.push(vaina);
					var ".$modulo."Tpl = Ext.create('Ext.Template', ".$modulo."TplMarkup );
					meco1.setTitle('Imprimir Compra');
					".$modulo."Tpl.overwrite(meco1.body, selectedRecord[0].data );
				}
			});
		}
	});
";

		$acordioni = "{
					layout: 'fit',
					items:[
						{
							name: 'imprimir',
							id: 'imprimir',
							border:false,
							html: 'Para imprimir seleccione una Compra '
						}
					]
				},
";


		$dockedItems = "{
			xtype: 'toolbar',
			items: [
				{
					iconCls: 'icon-add',
					text: 'Agregar',
					scope: this,
					handler: function(){
						window.open(urlAjax+'dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},
				{
					iconCls: 'icon-update',
					text: 'Modificar',
					disabled: true,
					itemId: 'update',
					scope: this,
					handler: function(selModel, selections){
						var selection = gridMaest.getView().getSelectionModel().getSelection()[0];
						gridMaest.down('#delete').setDisabled(selections.length === 0);
						window.open(urlAjax+'dataedit/modify/'+selection.data.id, '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},{
					iconCls: 'icon-delete',
					text: 'Eliminar',
					disabled: true,
					itemId: 'delete',
					scope: this,
					handler: function() {
						var selection = gridMaest.getView().getSelectionModel().getSelection()[0];
						Ext.MessageBox.show({
							title: 'Confirme', 
							msg: 'Seguro que quiere eliminar la compra Nro. '+selection.data.numero, 
							buttons: Ext.MessageBox.YESNO, 
							fn: function(btn){ 
								if (btn == 'yes') { 
									if (selection) {
										//storeMaest.remove(selection);
									}
									storeMaest.load();
								} 
							}, 
							icon: Ext.MessageBox.QUESTION 
						});  
					}
				}
			]
		}		
		";

		$grid2 = ",{
				itemId: 'viewport-center-detail',
				activeTab: 0,
				region: 'south',
				height: '40%',
				split: true,
				margins: '0 0 0 0',
				preventHeader: true,
				items: gridDeta1
			}";


		$titulow = 'Compras';
		
		$filtros = "";
		$features = "
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		plugins: [Ext.create('Ext.grid.plugin.CellEditing', { clicksToEdit: 2 })],
";

		$final = "storeIt".$modulo.".load();";

		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['stores']      = $stores;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		$data['grid2']       = $grid2;
		$data['coldeta']     = $coldeta;
		$data['acordioni']   = $acordioni;
		$data['final']       = $final;
		
		$data['title']  = heading('Notas de Entrega');
		$this->load->view('extjs/extjsvenmd',$data);
		
	}


}