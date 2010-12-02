<?php
class Prenom extends Controller {

	function Prenom(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->titulo='Generar Prenomina';
	}

	function index(){
		$this->rapyd->load("dataform");
		$form = new DataForm('nomina/prenom/index/process');

		$form->contrato = new dropdownField("Contrato", "contrato");
		$form->contrato->option("","Seleccionar");
		$form->contrato->options("SELECT codigo,nombre FROM noco ORDER BY nombre");
		$form->contrato->rule='required';

		$form->fechac = new dateonlyField("Fecha de corte", "fechac");
		$form->fechac->rule='required|chfecha';
		$form->fechac->insertValue = date("Y-m-d");
		$form->fechac->size=12;

		$form->fechap = new dateonlyField("Fecha de pago", "fechap");
		$form->fechap->rule='required|chfecha';
		$form->fechap->insertValue = date("Y-m-d");
		$form->fechap->size=12;

		$form->submit("btnsubmit","Generar");
		$form->build_form();

		if ($form->on_success()){
			$this->load->dbforge();

			$tabla   ='prenom';
			$tablap  ='pretab';
			$this->db->simple_query("TRUNCATE $tabla");
			$this->db->simple_query("TRUNCATE $tablap");
			$contrato=$this->db->escape($form->contrato->newValue);
			$fechac  =$form->fechac->newValue;
			$fechap  =$form->fechap->newValue;

			$mSQL  = "INSERT IGNORE INTO $tabla (contrato, codigo,nombre, concepto, grupo, tipo, descrip, formula, monto, fecha, fechap,cuota,cuotat,pprome,trabaja) ";
			$mSQL .= "SELECT $contrato, b.codigo, CONCAT(RTRIM(b.apellido),'/',b.nombre) nombre, ";
			$mSQL .= "a.concepto, a.grupo, a.tipo, a.descrip, a.formula, 0, $fechac, $fechap , 0, 0, 0, $contrato ";
			$mSQL .= "FROM conc a JOIN itnoco c ON a.concepto=c.concepto ";
			$mSQL .= "JOIN pers b ON b.contrato=c.codigo WHERE c.codigo=$contrato AND b.status='A' ";

			var_dum($this->db->simple_query($mSQL));

			$fields = $this->db->list_fields($tablap);
			$ii=count($fields);
			for($i=5;$i<$ii;$i++)
				$this->dbforge->drop_column($tablap,$fields[$i]);
			unset($fields);

			$query = $this->db->query("SELECT concepto FROM itnoco WHERE codigo=$contrato ORDER BY concepto");
			foreach ($query->result() as $row){
				$ind    = 'c'.trim($row->concepto);
				$fields[$ind]=array('type' => 'decimal(17,2)','default' => 0);
			}

			$this->dbforge->add_column($tablap, $fields);
			unset($fields);

			$frec=$this->datasis->dameval("SELECT tipo FROM noco WHERE codigo=$contrato");
			$query = $this->db->query("SELECT codigo,CONCAT(RTRIM(apellido),'/',nombre) AS nombre FROM pers WHERE contrato=$contrato");
			foreach ($query->result() as $row){
				$data['codigo'] = $row->codigo;
				$data['frec']   = $frec;
				$data['fecha']  = $fechac;
				$data['nombre'] = $row->nombre;
				$data['total']  = 0;
				$mSQL = $this->db->insert_string($tablap, $data);
				var_dum($this->db->simple_query($mSQL));
				redirect('nomina/prenom/montos');
			}

			/*$query = $this->db->query("SELECT FROM pers JOIN ON WHERE");
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					$data['contrato'] ='';
					$data['codigo']   ='';
					$data['nombre']   ='';
					$data['concepto'] ='';
					$data['tipo']     ='';
					$data['descrip']  ='';
					$data['grupo']    ='';
					$data['formula']  ='';
					$data['monto']    ='';
					$data['fecha']    ='';
					$data['cuota']    ='';
					$data['cuotat']   ='';
					$data['valor']    ='';
					$data['adicional']='';
					$data['fechap']   ='';
					$data['trabaja']  ='';
					$data['pprome']   ='';
				}
			}*/
		}

		$data['content'] = $form->output;
		$data['title']   = '<h1>'.$this->titulo.'</h1>';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function montos(){
		$this->rapyd->load('datagrid','fields','datafilter');

		$error='';
		if($this->input->post('pros')!==FALSE){
			$concepto =$this->db->escape($this->input->post('concepto'));
			$pmontos  =$this->input->post('monto');

			$this->load->library('pnomina');
			$formula=$this->datasis->dameval("SELECT formula FROM conc WHERE concepto=$concepto");

			foreach($pmontos AS $cod=>$cant){
				if(!is_numeric($cant)){
					$error.="$cant no es un valor num&erico;rico<br>";
				}else{
					$this->pnomina->CODIGO=$cod;
					$this->pnomina->MONTO =$cant;
					//$valor=0;
					$valor=$this->pnomina->evalform($formula);

					$cod = $this->db->escape($cod);
					$data  = array('monto' => $cant,'valor'=>$valor);
					$where = "codigo = $cod  AND concepto =$concepto ";
					$mSQL  = $this->db->update_string('prenom', $data, $where);
					var_dum($this->db->simple_query($mSQL));
				}
			}
		}

		$filter = new DataFilter("&nbsp;", 'prenom');
		$filter->error_string=$error;

		$filter->concepto = new dropdownField("Concepto", "concepto");
		$filter->concepto->option("","Seleccionar");
		$filter->concepto->options("SELECT concepto,descrip FROM prenom GROUP BY concepto ORDER BY descrip");
		$filter->concepto->clause  ="where";
		$filter->concepto->operator="=";
		$filter->concepto->rule    = "required";

		$filter->buttons("reset","search");
		$filter->build();

		$ggrid='';
		if ($filter->is_valid()){
			$ggrid =form_open('/nomina/prenom/montos/search/osp');
			$ggrid.=form_hidden('concepto', $filter->concepto->newValue);

			$monto = new inputField("Monto", "monto");
			$monto->grid_name='monto[<#codigo#>]';
			$monto->status   ='modify';
			$monto->size     =12;
			$monto->css_class='inputnum';

			$grid = new DataGrid("Concepto (".$filter->concepto->newValue.") ".$filter->concepto->options[$filter->concepto->newValue]);
			//$grid->per_page = $filter->db->num_rows() ;
			$grid->column("C&oacute;digo", "codigo");
			$grid->column("Nombre", "nombre");
			$grid->column("Monto" , $monto  ,'align=\'right\'');
			$grid->column("Valor" , 'valor' ,'align=\'right\'');
			$grid->submit('pros', 'Guardar',"BR");
			$grid->build();
			$ggrid.=$grid->output;
			$ggrid.=form_close();

		}
		$script ='
		<script type="text/javascript">
		$(function() {
			$(".inputnum").numeric(".");
		});
		</script>';
		$data['content'] = $filter->output.$ggrid;
		$data['title']   = '<h1>Asignaci&oacute;n de montos</h1>';
		$data['script']  = $script;
		$data["head"]    = $this->rapyd->get_head().script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$this->load->view('view_ventanas', $data);
	}

	function formulas(){
		$this->load->library('pnomina');
		$this->pnomina->CODIGO='002';
		$this->pnomina->MONTO =2500;

		$query = $this->db->query('SELECT * FROM conc');
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				echo $row->formula." = ";
				echo $this->pnomina->evalform($row->formula);
				echo "\n";
				//echo $this->pnomina->_traduce($row->formula)."\n\n";
			}
		}
	}

	function tabla(){
		$this->rapyd->load('datagrid','fields');
		$contrato="'DIR01'";

		$ggrid =form_open('/nomina/prenom/montos/search/osp');
		$ggrid.=form_hidden('concepto', 'alguno');

		$grid = new DataGrid("Asignaciones",'pretab');

		$grid->column("C&oacute;digo", "codigo");
		$grid->column("Nombre", "nombre");

		$query = $this->db->query("SELECT descrip,concepto FROM itnoco WHERE codigo=$contrato ORDER BY concepto");
		foreach ($query->result() as $row){
			$ind = 'c'.trim($row->concepto);

			$campo = new inputField("Campo", $ind);
			$campo->grid_name=$ind.'[<#codigo#>]';
			$campo->status   ='modify';
			$campo->size     =12;
			$campo->css_class='inputnum';

			$grid->column($row->descrip , $campo,'align=\'center\'');
		}
		$grid->submit('pros', 'Guardar',"BR");
		$grid->build();

		$ggrid.=$grid->output;
		$ggrid.=form_close();

		$data['content'] = $ggrid;
		$data['title']   = '<h1>Tabla de montos</h1>';
		//$data['script']  = $script;
		$data["head"]    = $this->rapyd->get_head().script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$this->load->view('view_ventanas', $data);

	}

	function prueba(){
		function &a(){
			return array(1,2,3);
		}

		//echo a()[0];
		$fdesde='2010-06-01';
		$fhasta='2010-07-20';
		
		$dsemana=1; //1 para lunes, 2 para martes .... 7 domingo
		$dated = new DateTime($fdesde);
		$dateh = new DateTime($fhasta);
		$dias  = 0;
		$intervalo='P1D';

		while($dated<=$dateh){
			if(date('N',$dated->getTimestamp())==$dsemana) {
				$dias++;
				$intervalo='P7D';
			}
			$dated->add(new DateInterval($intervalo));
		}
		echo 'Hay '.$dias." Lunes \n";
	}

}
?>
