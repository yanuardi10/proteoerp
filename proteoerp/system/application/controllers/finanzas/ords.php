<?php	//ordenservicio
class Ords extends Controller {

	function ords(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(522,1);
	}
	
	function index() {		
		//redirect('finanzas/ords/filteredgrid');
		if ( !$this->datasis->iscampo('ords','id') ) {
			$this->db->simple_query('ALTER TABLE ords DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE ords ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE ords ADD UNIQUE INDEX numero (numero)');
		}
		$this->ordsextjs();
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
		
		$modbusp=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;digo Proveedor',
			'nombre'=>'Nombre',
			'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		
		$boton=$this->datasis->modbus($modbusp);
		
		$filter = new DataFilter("Filtro de Orden de Servicio");
		$filter->db->select('numero,fecha,nombre,totiva,totbruto,proveed');
		$filter->db->from('ords');
		
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
    $filter->numero->size=20;

		$filter->proveedor = new inputField("Proveedor", "proveed");
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = "proveed";
    $filter->proveedor->size=20;

		$filter->buttons("reset","search");
		$filter->build();
    
		$uri = anchor('finanzas/ords/dataedit/show/<#numero#>','<#numero#>');
    $uri2 = anchor_popup('formatos/verhtml/ORDS/<#numero#>',"Ver HTML",$atts);
    
		$grid = new DataGrid();
		$grid->order_by("numero","desc");
		$grid->per_page = 15;
		
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Nombre","nombre");
		$grid->column("IVA"  ,"totiva"  ,"align='right'");
		$grid->column("Monto" ,"totbruto" ,"align='right'");
		$grid->column("Vista",$uri2,"align='center'");	
		//$grid->add("finanzas/egresos/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Orden de Servicio</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function dataedit(){
 		$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
 		
 		$formato=$this->datasis->dameval('SELECT formato FROM cemp LIMIT 0,1');
 		$qformato='%';
 		for($i=1;$i<substr_count($formato, '.')+1;$i++) $qformato.='.%';
 		$this->qformato=$qformato;
 		
		$modbusp=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;digo Proveedor',
			'nombre'=>'Nombre',
			'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');

			$boton=$this->datasis->modbus($modbusp);
    
			$modbus=array(
				'tabla'   =>'sinv',
				'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'descrip'),
				'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
				//'retornar'=>array('codigo'=>'codigo<#i#>','precio1'=>'precio1<#i#>','precio2'=>'precio2<#i#>','precio3'=>'precio3<#i#>','precio4'=>'precio4<#i#>','iva'=>'iva<#i#>','pond'=>'costo<#i#>'),
				'retornar'=>array('codigo'=>'codigo<#i#>'),
				'p_uri'=>array(4=>'<#i#>'),
				'titulo'  =>'Buscar Articulo');
 		
				//Script necesario para totalizar los detalles
 		
			$fdepar = new dropdownField("ccosto", "ccosto");    
			$fdepar->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
			$fdepar->status='create';
			$fdepar->build();
			$dpto=$fdepar->output;
		
			$dpto=trim($dpto);
			$dpto=preg_replace('/\n/i', '', $dpto);
 		
			$uri=site_url("/contabilidad/casi/dpto/");

			$script='
				function totalizar(){
					monto=debe=haber=0;
					amonto=$$(\'input[id^="monto"]\');
					for(var i=0; i<amonto.length; i++) {
						valor=parseFloat(amonto[i].value);
						if (isNaN(valor))
							valor=0.0;
						if (valor>0)
							haber=haber+valor;
						else{
							valor=valor*(-1);
							debe=debe+valor;
						}
						$("haber").value=haber;
						$("debe").value=debe;
						$("total").value=haber-debe;
					}
				}
				function departa(i){
					ccosto=$F(\'ccosto\'+i.toString())
					if (ccosto==\'S\'){
						//alert("come una matina");
						departamen=window.open("'.$uri.'/"+i.toString(),"buscardeparta","width=500,height=200,scrollbars=Yes,status=Yes,resizable=Yes,screenx=5,screeny=5,top="+ ((screen.height - 200) / 2) + ",left=" + ((screen.width - 500) / 2)); 
						departamen.focus();
						//new Insertion.Before(\'departa\'+i.toString(), \''.$dpto.'\')
					}
				}
	';
 		
		$edit = new DataEdit("Orden de Servicio","ords");
		
		$edit->post_process("insert","_guarda_detalle");
		$edit->post_process("update","_actualiza_detalle");
		$edit->post_process("delete","_borra_detalle");
		$edit->pre_process('insert','_pre_insert');
		
		$edit->back_url = "finanzas/ords";
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 20;
			
		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";

		$edit->numero1 = new inputField("N&uacute;mero", "cheque");
		$edit->numero1->size = 10;
		$edit->numero1->rule= "required";
		$edit->numero1->mode="autohide";
		
		$edit->proveedor = new inputField("Proveedor", "proveed");
		$edit->proveedor->size = 10; 
		$edit->proveedor->append($boton);       
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=30;   
		
		$edit->banco = new dropdownField("Banco", "codban");
		$edit->banco->option("BM","BM");
		$edit->banco->option("BV","BV");
		$edit->banco->option("99","99");
	  $edit->banco->style='width:70px;';
    $edit->banco->size =10;
		
		$edit->tipo = new dropdownField("Tipo", "tipo_op");  
	  $edit->tipo->option("CH","CH");
	  $edit->tipo->option("ND","ND");
	  $edit->tipo->size = 10;  
	  $edit->tipo->style='width:70px;';
	
		$edit->comprob  = new inputField2("Comprobante", "comprob");
		$edit->comprob->size = 20;
		
		$edit->beneficiario  = new inputField("Beneficiario", "benefi");
		$edit->beneficiario->size = 30;
	
		$edit->condiciones  = new inputField("Condiciones", "condi");
		$edit->condiciones->size = 35;
		
	  $edit->anticipo  = new inputField("Anticipo", "anticipo");
		$edit->anticipo->size = 20;
		$edit->anticipo->css_class='inputnum';
				
		$edit->impuesto  = new inputField("Impuesto", "totiva");
		$edit->impuesto->size = 20;
		$edit->impuesto->css_class='inputnum';
		
		$edit->total  = new inputField("Total", "totbruto");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';
		
		$edit->subtotal  = new inputField("SubTotal", "totpre");
		$edit->subtotal->size = 20;
		$edit->subtotal->css_class='inputnum';
				
		$numero=$edit->_dataobject->get('numero');
		
		$detalle = new DataDetalle($edit->_status);
		
		//Campos para el detalle
		
		$detalle->db->select('codigo,descrip,precio,iva,importe');
		$detalle->db->from('itords');
		$detalle->db->where("numero='$numero'");
		
		$detalle->codigo = new inputField("C&oacute;digo", "codigo<#i#>");
		$detalle->codigo->size=10;
		$detalle->codigo->db_name='codigo';
		$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$detalle->codigo->readonly=TRUE;
		
		$detalle->descripcion = new inputField("Descripci&oacute;n", "descrip<#i#>");
		$detalle->descripcion->size=30;
		$detalle->descripcion->db_name='descrip';
		$detalle->descripcion->maxlength=12;
		
		$detalle->impuesto = new inputField("Impuesto", "iva<#i#>");
		$detalle->impuesto->size=20;
		$detalle->impuesto->db_name='iva';
		$detalle->impuesto->maxlength=60;
		$detalle->impuesto->css_class='inputnum';  
		
		$detalle->precio = new inputField("Precio", "precio<#i#>");
		$detalle->precio->css_class='inputnum';
		$detalle->precio->onchange='totalizar()';
		$detalle->precio->size=20;
		$detalle->precio->db_name='precio';
		
		$detalle->importe = new inputField2("Importe", "importe<#i#>");
		$detalle->importe->db_name='importe';
		$detalle->importe->css_class='inputnum';
		$detalle->importe->size=20;

		//fin de campos para detalle
		
		$detalle->onDelete('totalizar()');
		$detalle->onAdd('totalizar()');
		$detalle->script($script);
		$detalle->style="width:110px";
		
		//Columnas del detalle
		
		$detalle->column("C&oacute;digo"    ,  "<#codigo#>");
		$detalle->column("Descripci&oacute;n", "<#descripcion#>");
		$detalle->column("Precio"     , "<#precio#>");
		$detalle->column("Impuesto"  ,  "<#impuesto#>");
		$detalle->column("Importe"    , "<#importe#>");
	
		$detalle->build();	
		$conten["detalle"] = $detalle->output;
		
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons( "save", "undo","back");
		$edit->build();
		
		$smenu['link']=barra_menu('522');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_ordenservicio', $conten,true); 
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Orden de Servicios</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function dpto() {		
		$this->rapyd->load("dataform");
		$campo='ccosto'.$this->uri->segment(4);
 		$script='
 		function pasar(){
			if($F("departa")!="-!-"){
				window.opener.document.getElementById("'.$campo.'").value = $F("departa");
				window.close();
			}else{
				alert("Debe elegir un departamento");
			}
		}';
		
		$form = new DataForm('');
		$form->script($script);
		$form->fdepar = new dropdownField("Departamento", "departa");
		$form->fdepar->option('-!-','Seleccion un departamento');
		$form->fdepar->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
		$form->fdepar->onchange='pasar()';
		$form->build_form();
		
		$data['content'] =$form->output;
		$data["head"]    =script('prototype.js').$this->rapyd->get_head();
		$data['title']   ='<h1>Seleccione un departamento</h1>';
		$this->load->view('view_detalle', $data);
	}

	function _guarda_detalle($do) {
		$cant=$this->input->post('cant_0');
		$i=$o=0;
		while($o<$cant){
			if (isset($_POST["codigo$i"])){
				if($this->input->post("codigo$i")){
						
					$sql = "INSERT INTO itords (fecha,numero,proveed,codigo,descrip,precio,importe,unidades,fraccion,almacen,sucursal,departa) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
					//$haber=($this->input->post("monto$i") < 0)? $this->input->post("monto$i")*(-1) : 0;
					$llena=array(
							0=>$do->get('fecha'),
							1=>$do->get('numero'),
							2=>$do->get('proveed'),
							3=>$this->input->post("codigo$i"),
							4=>$this->input->post("descrip$i"),
							5=>$this->input->post("precio$i"),
							6=>$this->input->post("importe$i"),
							7=>$this->input->post("unidades$i"),
							8=>$this->input->post("fraccion$i"),
							9=>$this->input->post("almacen$i"),
						 10=>$this->input->post("sucursal$i"),
						 11=>$this->input->post("departa$i"),
							);
					$this->db->query($sql,$llena);
				}
				$o++;
			}
			$i++;
		}
	}
	function _actualiza_detalle($do){
		$this->_borra_detalle($do);
		$this->_guarda_detalle($do);
	}
	function _borra_detalle($do){
		$numero=$do->get('numero');
		$sql = "DELETE FROM itords WHERE numero='$numero'";
		$this->db->query($sql);
	}
	function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
		$query  =$this->db->query($sql);
		$transac=$this->db->insert_id();
    
		$sql    = 'INSERT INTO nrds (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
		$query  =$this->db->query($sql);
		$control =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
    
		$do->set('numero', $numero);
		$do->set('transac', $transac);
		$do->set('estampa', 'CURDATE()', FALSE);
		$do->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->set('usuario', $this->session->userdata('usuario'));
	}

////////////////////////////
///////////////////////////
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 30;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'ords');
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('ords');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		if ( $sort == '') $this->db->order_by( 'id', 'desc' );

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

	function griditords(){
		$id   = isset($_REQUEST['id'])  ? $_REQUEST['id']   :  0;
		if ($id == 0 ) $id = $this->datasis->dameval("SELECT MAX(id) FROM ords")  ;
		$numero = $this->datasis->dameval("SELECT numero FROM ords WHERE id=$id");
		$mSQL = "SELECT * FROM itords WHERE numero='$numero' ORDER BY numero ASC";
		$query = $this->db->query($mSQL);
		$results = $query->num_rows() ; 
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function sprvbu(){
		$control = $this->uri->segment(4);
		$id = $this->datasis->dameval("SELECT b.id FROM ords a JOIN sprv b ON a.proveed=b.proveed WHERE control='$control'");
		redirect('compras/sprv/dataedit/show/'.$id);
	}

	function tabla() {
		$id   = isset($_REQUEST['id'])  ? $_REQUEST['id']   :  0;

		$transac = $this->datasis->dameval("SELECT transac FROM ords WHERE id='$id'");

		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM sprm WHERE transac='$transac' ORDER BY cod_prv ";
		$query = $this->db->query($mSQL);
		$codprv = 'XXXXXXXXXXXXXXXX';
		$salida = '';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento en Cuentas X Pagar</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			
			foreach ($query->result_array() as $row)
			{
				if ( $codprv != $row['cod_prv']){
					$codprv = $row['cod_prv'];
					$salida .= "<tr bgcolor='#c7d3c7'>";
					$salida .= "<td colspan=4>".trim($row['nombre']). "</td>";
					$salida .= "</tr>";	
				}
				if ( $row['tipo_doc'] == 'FC' ) {
					$saldo = $row['monto']-$row['abonos'];
				}
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		$query->free_result();
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
		echo $salida;

	}

	function ordsextjs() {
		$encabeza='ORDENES DE SERVICIO';
		$listados= $this->datasis->listados('ords');
		$otros=$this->datasis->otros('ords', 'finanzas/ords');

		$urlajax = 'finanzas/ords/';

		$columnas = "
		{ header: 'Numero',       width: 70, sortable: true, dataIndex: 'numero' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Fecha',        width: 70, sortable: true, dataIndex: 'fecha' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Proveed',      width: 60, sortable: true, dataIndex: 'proveed' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Nombre',       width:200, sortable: true, dataIndex: 'nombre' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Base',         width: 80, sortable: true, dataIndex: 'totpre' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'IVA',          width: 80, sortable: true, dataIndex: 'totiva' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Total',        width: 80, sortable: true, dataIndex: 'totbruto' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Saldo',        width: 80, sortable: true, dataIndex: 'saldo' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Banco',        width: 60, sortable: true, dataIndex: 'codban' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Tipo',         width: 60, sortable: true, dataIndex: 'tipo_op' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cheque',       width:100, sortable: true, dataIndex: 'cheque' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Anticipo',     width: 60, sortable: true, dataIndex: 'anticipo' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Beneficiario', width:100, sortable: true, dataIndex: 'benefi' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Condiciones',  width:200, sortable: true, dataIndex: 'condi' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Status',       width: 60, sortable: true, dataIndex: 'status' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Transacion',   width: 60, sortable: true, dataIndex: 'transac' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Usuario',      width: 80, sortable: true, dataIndex: 'usuario' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Estampa',      width: 70, sortable: true, dataIndex: 'estampa' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Hora',         width: 60, sortable: true, dataIndex: 'hora' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Modificado',   width: 60, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }}";

		$coldeta = "
	var Deta1Col = [
		{ header: 'Codigo',     width: 60, sortable: true, dataIndex: 'codigo',     field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Descrip',    width:200, sortable: true, dataIndex: 'descrip',    field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Precio',     width: 60, sortable: true, dataIndex: 'precio',     field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'IVA',        width: 60, sortable: true, dataIndex: 'iva',        field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Importe',    width: 60, sortable: true, dataIndex: 'importe',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Unidades',   width: 60, sortable: true, dataIndex: 'unidades',   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Fraccion',   width: 60, sortable: true, dataIndex: 'fraccion',   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Almacen',    width: 60, sortable: true, dataIndex: 'almacen',    field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Sucursal',   width: 60, sortable: true, dataIndex: 'sucursal',   field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Depto',      width: 60, sortable: true, dataIndex: 'departa',    field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'id',         width: 60, sortable: true, dataIndex: 'id',         field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Modificado', width: 60, sortable: true, dataIndex: 'modificado', field: { type: 'date'       }, filter: { type: 'date'    }},]";

		$variables='';
		
		$valida="		{ type: 'length', field: 'numero',  min:  1 }";
		

		$funciones = "
function renderSprv(value, p, record) {
	var mreto='';
	if ( record.data.proveed == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'finanzas/ords/sprvbu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.control );
}

function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}
	";

		$campos = $this->datasis->extjscampos('ords');

		$stores = "
	Ext.define('Itords', {
		extend: 'Ext.data.Model',
		fields: [".$this->datasis->extjscampos('gitser')."],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'finanzas/ords/griditords',
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
	var storeItords = Ext.create('Ext.data.Store', {
		model: 'Itords',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});
	
	//////////////////////////////////////////////////////////
	//
	var gridDeta1 = Ext.create('Ext.grid.Panel', {
		width:   '100%',
		height:  '100%',
		store:   storeItords,
		title:   'Detalle del Gasto',
		iconCls: 'icon-grid',
		frame:   true,
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		columns: Deta1Col
	});

	var ordsTplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR ORDEN</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/verhtml/COMPRA/{control}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/ver/COMPRA/{control}\',     \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',   'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',		
		'</table>','nanai'
	];

	// Al cambiar seleccion
	gridMaest.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridMaest.down('#delete').setDisabled(selectedRecord.length === 0);
			gridMaest.down('#update').setDisabled(selectedRecord.length === 0);
			mid = selectedRecord[0].data.id;
			gridDeta1.setTitle(selectedRecord[0].data.numero+' '+selectedRecord[0].data.nombre);
			storeItords.load({ params: { id: mid }});
			var meco1 = Ext.getCmp('imprimir');
			Ext.Ajax.request({
				url: urlApp +'finanzas/ords/tabla',
				params: { id: mid, serie: selectedRecord[0].data.numero },
				success: function(response) {
					var vaina = response.responseText;
					ordsTplMarkup.pop();
					ordsTplMarkup.push(vaina);
					var ordsTpl = Ext.create('Ext.Template', ordsTplMarkup );
					meco1.setTitle('Imprimir Compra');
					ordsTpl.overwrite(meco1.body, selectedRecord[0].data );
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
						window.open(urlApp+'finanzas/ords/dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
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
						window.open(urlApp+'finanzas/ords/dataedit/modify/'+selection.data.control, '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
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

		$final = "storeItords.load();";

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
		
		$data['title']  = heading('Orden de Servicio');
		$this->load->view('extjs/extjsvenmd',$data);


	}

}
?>