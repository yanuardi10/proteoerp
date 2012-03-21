<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//pagoproveed
class Sprm extends validaciones {

	function sprm(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(500,1);
	}

	function index(){
		if ( !$this->datasis->iscampo('sprm','id') ) {
			$this->db->simple_query('ALTER TABLE sprm DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE sprm ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE sprm ADD UNIQUE INDEX cod_prv (cod_prv, tipo_doc, numero, fecha)');
			echo "Indice ID Creado";
		}
		//redirect($this->url.'filteredgrid');
		$this->sprmextjs();
		//redirect("finanzas/sprm/filteredgrid");
	}

	function filteredgrid(){

		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Pago a Proveedores", "sprm");

		$filter->numero = new inputField("Numero", "numero");
		$filter->numero->size=12;
		$filter->numero->maxlength=8;

		$filter->cod_prv = new inputField("C&oacute;digo Proveedor", "cod_prv");
		$filter->cod_prv->size=12;
		$filter->cod_prv->maxlength=5;

		$filter->tipo_doc = new dropdownField("Tipo de Documento", "tipo_doc");
		$filter->tipo_doc->option('','');
		$filter->tipo_doc->option("AB","Abono");
		$filter->tipo_doc->option("AN","Anticipo");
		$filter->tipo_doc->option("NC","Nota de Cr&eacute;dito");
		$filter->tipo_doc->style ="width:100px";

		$filter->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$filter->fecha->size=12;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/sprm/dataedit/show/<#cod_prv#>/<#tipo_doc#>/<#numero#>/<#fecha#>','<#numero#>');

		$grid = new DataGrid("Lista de Pago a Proveedores");
		$grid->order_by("numero","asc");
		$grid->per_page = 10;

		$grid->column("Numero",$uri);
		$grid->column("Cod. Proveedor","cod_prv");
		$grid->column("Tipo Documento","tipo_doc");
		$grid->column("Fecha","fecha");

		$grid->add("finanzas/sprm/dataedit/create");
		$grid->build();

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		$data['title']   = "<h1>Pago a Proveedores</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit','datadetalle','fields','datagrid');

		$mSPRV=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'=>'Nombre',
		'rif'=>'RIF'),
		'filtro'  =>array('proveed' =>'C&oacute;digo Proveedor','nombre'=>'Nombre','rif'=>'RIF'),
		'retornar'=>array('proveed'=>'cod_prv','nombre'=>'nombre'),
		'titulo'  =>'Buscar Proveedor');
		$bsprv =$this->datasis->modbus($mSPRV);

		$script ='$(function() {
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit("Pago Proveedores", "sprm");
		$edit->back_url = "finanzas/sprm";
		$edit->script($script, "create");
		$edit->script($script, "modify");
		$edit->pre_process('insert','_guarda');
		$edit->pre_process('update','_guarda');

		$edit->numero = new inputField('Numero', 'numero');
		$edit->numero->size=12;
		$edit->numero->maxlength=8;
		$edit->numero->rule='trim|required';

		//$edit->numero = new dropdownField("Numero", "numero");
		//$edit->numero->size=30;
		//$edit->numero->option('','');
		//$edit->numero->options("SELECT codbanc, banco FROM bmov ORDER BY banco");

		$edit->cod_prv = new inputField('C&oacute;digo Proveedor', 'cod_prv');
		$edit->cod_prv->size=12;
		$edit->cod_prv->maxlength=5;
		$edit->cod_prv->rule='trim|required';
		$edit->cod_prv->append($bsprv);
		$edit->cod_prv->readonly=true;

		$edit->nombre = new inputField('Nombre Proveedor', 'nombre');
		$edit->nombre->size=30;
		$edit->nombre->maxlength=40;
		$edit->nombre->rule='trim';


		$edit->tipo_doc = new dropdownField('Tipo de Documento', 'tipo_doc');
		$edit->tipo_doc->option('AB','Abono');
		$edit->tipo_doc->option('AN','Anticipo');
		//$edit->tipo_doc->option('FC','Factura');
		//$edit->tipo_doc->option('JD','JD');
		$edit->tipo_doc->option('NC','Nota de Cr&eacute;dito');
		//$edit->tipo_doc->option('ND','Nota de D&eacute;bito');
		$edit->tipo_doc->style ='width:100px';
		$edit->tipo_doc->rule='required';

		$edit->fecha = new DateonlyField('Fecha', 'fecha','Y/m/d');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size=12;
		$edit->fecha->rule='required|chfecha';

		$edit->monto =new inputField('Monto','monto');
		$edit->monto->size = 12;
		$edit->monto->maxlength = 17;
		$edit->monto->rule = 'trim|required|numeric';
		$edit->monto->css_class='inputnum';

		$edit->impuesto =new inputField('Impuesto','impuesto');
		$edit->impuesto->size = 12;
		$edit->impuesto->maxlength = 17;
		$edit->impuesto->rule = 'trim|required|numeric';
		$edit->impuesto->css_class='inputnum';

		$edit->abonos =new inputField('Abonos','abonos');
		$edit->abonos->size = 12;
		$edit->abonos->maxlength = 17;
		$edit->abonos->rule = 'trim|required|numeric';
		$edit->abonos->css_class='inputnum';

		$edit->vence = new DateonlyField('Vence', 'vence','Y/m/d');
		$edit->vence->size=12;

		$edit->tipo_ref = new dropdownField('Tipo de Referencia', 'tipo_ref');
		$edit->tipo_ref->option('OS','OS');
		$edit->tipo_ref->option('AB','AB');
		$edit->tipo_ref->option('AC','AC');
		$edit->tipo_ref->option('AP','AP');
		$edit->tipo_ref->option('CR','CR');
		$edit->tipo_ref->style ='width:100px';

		$edit->num_ref = new inputField('Num. Referencia', 'num_ref');
		$edit->num_ref->size=12;
		$edit->num_ref->maxlength=8;
		$edit->num_ref->rule='trim';

		$edit->observa1 = new inputField('Observaciones', 'observa1');
		$edit->observa1->size=50;
		$edit->observa1->maxlength=50;
		$edit->observa1->rule='trim';

		$edit->observa2 = new inputField('', 'observa2');
		$edit->observa2->size=50;
		$edit->observa2->maxlength=50;
		$edit->observa2->rule='trim';

		$edit->banco = new dropdownField('Caja/Banco', 'banco');
		$edit->banco->size=30;
		$edit->banco->option('','');
		$edit->banco->options('SELECT codbanc, banco FROM bmov ORDER BY banco');

		$edit->tipo_op = new inputField('Tipo de Operacion', 'tipo_op');
		$edit->tipo_op->size=12;
		$edit->tipo_op->maxlength=2;
		$edit->tipo_op->rule='trim';

		$edit->comprob = new dropdownField('Comprobante', 'comprob');
		$edit->comprob->option('AJUST','REGUALRIZAR F. MAL PROCESADA');
		$edit->comprob->option('BONIP','BONIFICACION DE PROVEEDORES');
		$edit->comprob->option('DECOM','DEVOLUCION EN COMPRAS');
		$edit->comprob->option('DESOP','OTROS DESCUENTOS PROVEEDORES');
		$edit->comprob->option('DESPP','DESCUENTO PRONTO PAGO PROVEEDO');
		$edit->comprob->option('DEVOP','DESCUENTO X VOLUMEN PROVEEDOR');
		$edit->comprob->option('DFCAP','DIFERENCIA /CAMBIO PROVEEDOR');
		$edit->comprob->option('DIFPP','DIFERENCIA /PRECIOS PROVEEDOR');
		$edit->comprob->size=30;

		$edit->numche = new inputField('Numche', 'numche');
		$edit->numche->size=12;
		$edit->numche->maxlength=12;
		$edit->numche->rule='trim';

		$edit->codigo = new inputField('Codigo', 'codigo');
		$edit->codigo->size=12;
		$edit->codigo->maxlength=50;
		$edit->codigo->rule='trim';

		$edit->descrip = new inputField('Descripcion', 'descrip');
		$edit->descrip->size=30;
		$edit->descrip->maxlength=30;
		$edit->descrip->rule='trim';

		$edit->ppago =new inputField('Ppago','ppago');
		$edit->ppago->size = 12;
		$edit->ppago->maxlength = 17;
		$edit->ppago->rule = 'trim|numeric';
		$edit->ppago->css_class='inputnum';

		$edit->nppago = new inputField('NPpago', 'nppago');
		$edit->nppago->size=12;
		$edit->nppago->maxlength=8;
		$edit->nppago->rule='trim';

		$edit->reten =new inputField('Retenci&oacute;n','reten');
		$edit->reten->size = 12;
		$edit->reten->maxlength = 17;
		$edit->reten->rule = 'trim|numeric';
		$edit->reten->css_class='inputnum';

		$edit->nreten = new inputField('Nreten', 'nreten');
		$edit->nreten->size=12;
		$edit->nreten->maxlength=8;
		$edit->nreten->rule='trim';

		$edit->mora =new inputField('Mora','mora');
		$edit->mora->size = 12;
		$edit->mora->maxlength = 17;
		$edit->mora->rule = 'trim|numeric';
		$edit->mora->css_class='inputnum';

		$edit->posdata = new DateonlyField('Posdata', 'posdata','Y/m/d');
		$edit->posdata->size=12;

		$edit->benefi = new inputField('Beneficiario', 'benefi');
		$edit->benefi->size=30;
		$edit->benefi->maxlength=40;
		$edit->benefi->rule='trim';

		$edit->control = new inputField('Control', 'control');
		$edit->control->size=12;
		$edit->control->maxlength=8;
		$edit->control->rule='trim';

		//$edit->transac = new inputField('Transaccion', 'transac');
		//$edit->transac->size=12;
		//$edit->transac->maxlength=8;
		//$edit->transac->rule='trim';

		$edit->cambio =new inputField('Cambio','cambio');
		$edit->cambio->size = 12;
		$edit->cambio->maxlength = 17;
		$edit->cambio->rule = 'trim|numeric';
		$edit->cambio->css_class='inputnum';

		$edit->pmora =new inputField('Pmora','pmora');
		$edit->pmora->size = 12;
		$edit->pmora->maxlength = 6;
		$edit->pmora->rule = 'trim|numeric';
		$edit->pmora->css_class='inputnum';

		$edit->reteiva =new inputField('Retenci&oacute;n de IVA','reteiva');
		$edit->reteiva->size = 12;
		$edit->reteiva->maxlength = 18;
		$edit->reteiva->rule = 'trim|numeric';
		$edit->reteiva->css_class='inputnum';

		$edit->id =new inputField('id','id');//entero
		$edit->id->size = 12;

		$edit->nfiscal = new inputField('Nfiscal', 'nfiscal');
		$edit->nfiscal->size=12;
		$edit->nfiscal->maxlength=8;
		$edit->nfiscal->rule='trim';

		$edit->montasa =new inputField('montasa','montasa');
		$edit->montasa->size = 12;
		$edit->montasa->maxlength = 17;
		$edit->montasa->rule = 'trim|numeric';
		$edit->montasa->css_class='inputnum';

		$edit->monredu =new inputField('monredu','monredu');
		$edit->monredu->size = 12;
		$edit->monredumonredu->maxlength = 17;
		$edit->monredu->rule = 'trim|numeric';
		$edit->monredu->css_class='inputnum';

		$edit->monadic =new inputField('monadic','monadic');
		$edit->monadic->size = 12;
		$edit->monadic->maxlength = 17;
		$edit->monadic->rule = 'trim|numeric';
		$edit->monadic->css_class='inputnum';

		$edit->tasa =new inputField('tasa','tasa');
		$edit->tasa->size = 12;
		$edit->tasa->maxlength = 17;
		$edit->tasa->rule = 'trim|numeric';
		$edit->tasa->css_class='inputnum';

		$edit->reducida =new inputField('reducida','reducida');
		$edit->reducida->size = 12;
		$edit->reducida->maxlength = 17;
		$edit->reducida->rule = 'trim|numeric';
		$edit->reducida->css_class='inputnum';

		$edit->sobretasa =new inputField('sobretasa','sobretasa');
		$edit->sobretasa->size = 12;
		$edit->sobretasa->maxlength = 17;
		$edit->sobretasa->rule = 'trim|numeric';
		$edit->sobretasa->css_class='inputnum';

		$edit->exento =new inputField('exento','exento');
		$edit->exento->size = 12;
		$edit->exento->maxlength = 17;
		$edit->exento->rule = 'trim|numeric';
		$edit->exento->css_class='inputnum';

		$edit->fecdoc = new DateonlyField('Fecha de Doc', 'fecdoc','Y/m/d');
		$edit->fecdoc->size=12;

		$edit->afecta = new inputField('afecta', 'afecta');
		$edit->afecta->size=12;
		$edit->afecta->maxlength=10;
		$edit->afecta->rule='trim';

		$edit->fecapl = new DateonlyField('Fecapl', 'fecapl','Y/m/d');
		$edit->fecapl->size=12;

		$edit->serie = new inputField('serie', 'serie');
		$edit->serie->size=12;
		$edit->serie->maxlength=8;
		$edit->serie->rule='trim';

		$edit->depto = new inputField('serie', 'depto');
		$edit->depto->size=12;
		$edit->depto->maxlength=3;
		$edit->depto->rule='trim';

		$edit->buttons('modify', 'save', 'undo', 'back');
		$edit->build();

		//$smenu['link']=barra_menu('230');
		$data['content'] = $edit->output;
		//$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['title']   = "<h1>Pago a Proveedores</h1>";
		$data['head']    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _guarda($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
		$query  =$this->db->query($sql);

		//$transac=$do->get('transc');
		//$do->db->set('transac', $transac);
		$do->db->set('estampa', 'CURDATE()', FALSE);
		$do->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->db->set('usuario', $this->session->userdata('usuario'));
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'sprm');
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('sprm');

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

		$mSQL = '';

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function griditsprm(){
		$transac   = isset($_REQUEST['transac'])  ? $_REQUEST['transac']  :  0;
		$tipo_doc  = isset($_REQUEST['tipo_doc']) ? $_REQUEST['tipo_doc'] :  '';
		$numero    = isset($_REQUEST['numero'])   ? $_REQUEST['numero']   :  '';
		$cod_prv   = isset($_REQUEST['cod_prv'])  ? $_REQUEST['cod_prv']  :  '';

		if ($transac == 0 ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM sprm ")  ;
			$transac = $this->datasis->dameval("SELECT transac FROM sprm WHERE id=$id ")  ;
		} else
			$id = $this->datasis->dameval("SELECT id FROM sprm WHERE tipo_doc='$tipo_doc' AND numero='$numero' AND cod_prv='$cod_prv'")  ;


		$fecha = $this->datasis->dameval("SELECT estampa FROM sprm WHERE id=$id ");

		$mSQL = "
SELECT
'1' origen, cod_prv, fecha,
IF(tipo_doc='$tipo_doc' AND numero='$numero', tipoppro, tipo_doc) tipo_doc,
IF(tipo_doc='$tipo_doc' AND numero='$numero', numppro, numero) numero,
monto, abono, ppago, reten, reteiva
FROM itppro WHERE transac='$transac' AND estampa='$fecha'
UNION ALL
SELECT
'2' origen, cod_prv, fecha,
IF(tipo_doc='$tipo_doc' AND numero='$numero', tipoppro, tipo_doc) tipo_doc,
IF(tipo_doc='$tipo_doc' AND numero='$numero', numppro, numero) numero,
monto, abono, ppago, reten, reteiva
FROM itppro WHERE cod_prv='$cod_prv' AND numero='$numero' AND tipo_doc='$tipo_doc' AND transac!='$transac'
UNION ALL
SELECT
'3' origen, cod_prv, fecha,
IF(tipo_doc='$tipo_doc' AND numero='$numero', tipoppro, tipo_doc) tipo_doc,
IF(tipo_doc='$tipo_doc' AND numero='$numero', numppro, numero) numero,
monto, abono, ppago, reten, reteiva
FROM itppro WHERE cod_prv='$cod_prv' AND numppro='$numero' AND tipoppro='$tipo_doc' AND transac!='$transac'
UNION ALL
SELECT
'4' origen, b.cliente cod_prv, a.ofecha fecha, 'CR' tipo_doc,
a.numero,  a.monto,  0,0,0,0
FROM itcruc AS a JOIN cruc AS b ON a.numero=b.numero
WHERE b.cliente='$cod_prv' AND a.onumero='$tipo_doc$numero'
UNION ALL
SELECT
IF(a.tipo='ADE','4','5') origen, b.cliente cod_prv, a.ofecha fecha, MID(onumero,1,2) tipo_doc,
MID(onumero,3,8), a.monto,  0,0,0,0
FROM itcruc AS a JOIN cruc AS b ON a.numero=b.numero
WHERE b.transac='$transac'
";


		$query = $this->db->query($mSQL);
		$results =  0;
		$mSQL = '';
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data"'.$mSQL.' ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function sclibu(){
		$control = $this->uri->segment(4);
		$id = $this->datasis->dameval("SELECT b.id FROM sprm a JOIN sprv b ON a.proveed=b.proveed WHERE control='$control'");
		redirect('finanzas/sprv/dataedit/show/'.$id);
	}

	function tabla() {
		$transac  = isset($_REQUEST['transac'])  ? $_REQUEST['transac']  :  0;
		$cod_prv  = isset($_REQUEST['cod_prv'])  ? $_REQUEST['cod_prv']  :  0;
		$numero   = isset($_REQUEST['numero'])   ? $_REQUEST['numero']   :  0;
		$tipo_doc = isset($_REQUEST['tipo_doc']) ? $_REQUEST['tipo_doc'] :  0;

		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos
			FROM sprm WHERE transac='$transac' ORDER BY cod_prv ";
		$query = $this->db->query($mSQL);
		$codcli = 'XXXXXXXXXXXXXXXX';
		$salida = '';
		$saldo  = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				if ( $codcli != $row['cod_prv']){
					$codcli = $row['cod_prv'];
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

		$mSQL = "SELECT cod_cli, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos
			FROM smov WHERE transac='$transac' ORDER BY cod_cli ";
		$query = $this->db->query($mSQL);
		$codcli = 'XXXXXXXXXXXXXXXX';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				if ( $codcli != $row['cod_prv']){
					$codcli = $row['cod_prv'];
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

		//cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos
		//Cruce de Cuentas
		$mSQL = "SELECT b.proveed cod_prv, MID(b.nombre,1,25) nombre, a.monto, b.numero, b.fecha
			FROM itcruc AS a JOIN cruc AS b ON a.numero=b.numero
			WHERE b.cliente='$cod_prv' AND a.onumero='$tipo_doc$numero'
			UNION ALL
			SELECT b.cliente cod_prv, MID(b.nomcli,1,25) nombre, -a.monto, b.numero, b.fecha
			FROM itcruc AS a JOIN cruc AS b ON a.numero=b.numero
			WHERE b.cliente='$cod_prv' AND a.onumero='$tipo_doc$numero'
			ORDER BY numero
			";
		$query = $this->db->query($mSQL);
		$codcli = 'XXXXXXXXXXXXXXXX';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<td colspan=4>Cruce de Cuentas</td>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Codigo</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['cod_prv']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table>";
		}
		echo $salida;
	}


	function sprmextjs() {
		$encabeza='MOVIMIENTO DE PROVEEDORES';
		$listados= $this->datasis->listados('sprm');
		$otros=$this->datasis->otros('sprm', 'finanzas/sprm');

		$modulo = 'sprm';
		$urlajax = 'finanzas/sprm/';

		$columnas = "
			{ header: 'Codigo',    width: 50, sortable: true, dataIndex: 'cod_prv',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Nombre',    width:210, sortable: true, dataIndex: 'nombre' ,   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Tipo',      width: 40, sortable: true, dataIndex: 'tipo_doc',  field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Numero',    width: 60, sortable: true, dataIndex: 'numero',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Fecha',     width: 80, sortable: true, dataIndex: 'fecha',     field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Monto',     width: 90, sortable: true, dataIndex: 'monto',     field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'I.V.A.',    width: 90, sortable: true, dataIndex: 'impuesto',  field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Abonos',    width: 90, sortable: true, dataIndex: 'abonos',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Vence',     width: 80, sortable: true, dataIndex: 'vence',     field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Referen.',  width: 40, sortable: true, dataIndex: 'tipo_ref',  field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'num_ref',   width: 60, sortable: true, dataIndex: 'num_ref',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'observa1',  width:200, sortable: true, dataIndex: 'observa1',  field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'observa2',  width:160, sortable: true, dataIndex: 'observa2',  field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Banco',     width: 60, sortable: true, dataIndex: 'banco',     field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Tipo' ,     width: 60, sortable: true, dataIndex: 'tipo_op',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Comprob',   width: 60, sortable: true, dataIndex: 'comprob',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Nro.Cheque',width: 60, sortable: true, dataIndex: 'numche',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'posdata',   width: 70, sortable: true, dataIndex: 'posdata',   field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Beneficia', width: 90, sortable: true, dataIndex: 'benefi',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Codigo',    width: 60, sortable: true, dataIndex: 'codigo',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Descrip',   width: 60, sortable: true, dataIndex: 'descrip',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'P.Pago',    width: 80, sortable: true, dataIndex: 'ppago',     field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Reten',     width: 60, sortable: true, dataIndex: 'reten',     field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Codigo',    width: 60, sortable: true, dataIndex: 'codigo',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Descrip',   width: 60, sortable: true, dataIndex: 'descrip',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Control',   width: 60, sortable: true, dataIndex: 'control',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Usuario',   width: 60, sortable: true, dataIndex: 'usuario',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Estampa',   width: 70, sortable: true, dataIndex: 'estampa',   field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Hora',      width: 60, sortable: true, dataIndex: 'hora',      field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Transac',   width: 60, sortable: true, dataIndex: 'transac',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Cambio',    width: 60, sortable: true, dataIndex: 'cambio',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Mora',      width: 60, sortable: true, dataIndex: 'mora',      field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Reteiva',   width: 60, sortable: true, dataIndex: 'reteiva',   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Vendedor',  width: 60, sortable: true, dataIndex: 'vendedor',  field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Nfiscal',   width: 60, sortable: true, dataIndex: 'nfiscal',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'MonTasa',   width: 60, sortable: true, dataIndex: 'montasa',   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'MonRedu',   width: 60, sortable: true, dataIndex: 'monredu',   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'MonAdic',   width: 60, sortable: true, dataIndex: 'monadic',   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Tasa',      width: 60, sortable: true, dataIndex: 'tasa',      field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Reducida',  width: 60, sortable: true, dataIndex: 'reducida',  field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Sobretasa', width: 60, sortable: true, dataIndex: 'sobretasa', field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Exento',    width: 60, sortable: true, dataIndex: 'exento',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Fec.Doc',   width: 70, sortable: true, dataIndex: 'fecdoc',    field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Nr.RetIVA', width: 90, sortable: true, dataIndex: 'nroriva',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Emision',   width: 70, sortable: true, dataIndex: 'emiriva',   field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Cli/Pro',   width: 60, sortable: true, dataIndex: 'codcp',     field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Depto',     width: 60, sortable: true, dataIndex: 'depto',     field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'MaqFiscal', width: 60, sortable: true, dataIndex: 'maqfiscal', field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Modificado',width: 90, sortable: true, dataIndex: 'modificado',field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Afecta',    width: 60, sortable: true, dataIndex: 'afecta',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Fecapl',    width: 60, sortable: true, dataIndex: 'fecapl',    field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Serie',     width: 60, sortable: true, dataIndex: 'serie',     field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Nr.Egreso', width: 60, sortable: true, dataIndex: 'negreso',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Nr.Debito', width: 60, sortable: true, dataIndex: 'ndebito',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Nr.Causado',width: 60, sortable: true, dataIndex: 'causado',   field: { type: 'textfield' }, filter: { type: 'string' }},
		";

		$coldeta = "
	var Deta1Col = [
		{ header: 'O',       width: 20, sortable: true, dataIndex: 'origen' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cliente', width: 50, sortable: true, dataIndex: 'cod_prv' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Tipo',    width: 40, sortable: true, dataIndex: 'tipo_doc' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Numero',  width: 70, sortable: true, dataIndex: 'numero' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Fecha',   width: 80, sortable: true, dataIndex: 'fecha' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Monto',   width: 80, sortable: true, dataIndex: 'monto' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Abono',   width: 80, sortable: true, dataIndex: 'abono' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'P.Pago',  width: 80, sortable: true, dataIndex: 'ppago' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Reten',   width: 80, sortable: true, dataIndex: 'reten' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Reteiva', width: 60, sortable: true, dataIndex: 'reteiva' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Nroriva', width: 60, sortable: true, dataIndex: 'nroriva' , field: { type: 'textfield' }, filter: { type: 'string' }},
		]";

		$variables='';

		$valida="		{ type: 'length', field: 'cod_prv',  min:  1 }";

		$funciones = "
		function renderSprv(value, p, record) {
			var mreto='';
			if ( record.data.proveed == '' ){
				mreto = '{0}';
			} else {
				mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'finanzas/sprm/sprvbu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
			}
			return Ext.String.format(mreto,	value, record.data.control );
		}";

		$campos = $this->datasis->extjscampos($modulo);

		$stores = "
	Ext.define('It".$modulo."', {
		extend: 'Ext.data.Model',
		fields: ['origen',".$this->datasis->extjscampos("itppro")."],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlAjax + 'griditsprm',
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
		title:   'Detalle del Movimiento',
		iconCls: 'icon-grid',
		frame:   true,
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		columns: Deta1Col
	});

	var ".$modulo."TplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR DOCUMENTO</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'ventas/sfac_add/dataprint/modify/{id}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'ventas/sfac_add/dataprint/modify/{id}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',  'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',
		'</table>','nanai'
	];



	// Al cambiar seleccion
	gridMaest.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridMaest.down('#delete').setDisabled(selectedRecord.length === 0);
			//gridMaest.down('#update').setDisabled(selectedRecord.length === 0);
			numero   = selectedRecord[0].data.numero;
			cod_prv  = selectedRecord[0].data.cod_prv;
			tipo_doc = selectedRecord[0].data.tipo_doc;
			transac  = selectedRecord[0].data.transac;
			gridDeta1.setTitle( numero+' '+selectedRecord[0].data.nombre);
			storeIt".$modulo.".load({ params: { numero: numero, cod_prv: cod_prv, tipo_doc: tipo_doc, transac: transac }});
			var meco1 = Ext.getCmp('imprimir');
			Ext.Ajax.request({
				url: urlAjax +'tabla',
				params: { numero: numero, cod_prv: cod_prv, tipo_doc: tipo_doc, transac: transac  },
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
				/*{
					iconCls: 'icon-add',
					text: 'Agregar',
					scope: this,
					handler: function(){
						window.open(urlApp+'ventas/sfac_add/dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
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
						window.open(urlApp+'ventas/sfac_add/dataedit/modify/'+selection.data.id, '_blank', 'width=900,height=730,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},*/
				{
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


		$titulow = 'Movimiento';

		$filtros = '';
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

		$data['title']  = heading('Movimiento de Proveedores');
		$this->load->view('extjs/extjsvenmd',$data);
	}
}
