<?php
//plancuenta
class Cpla extends Controller {
	function cpla(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(604,1);
	}

	function index() {
		$this->rapyd->load("datagrid","datafilter");

		$filter = new DataFilter("Filtro de Plan de cuentas",'cpla');

		$filter->codigo   = new inputField("C&oacute;digo","codigo");
		$filter->codigo->like_side='after';
		$filter->codigo->size=15;

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('contabilidad/cpla/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid();
		$grid->order_by("codigo","asc");
		$grid->per_page = 15;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Usa Departamento","departa","align='center'");
		$grid->column("Cuenta Monetaria","moneta" ,"align='center'");

		$grid->add("contabilidad/cpla/dataedit/create");
		$grid->build();

		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Plan de Cuentas</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function dataedit(){
 		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Plan de cuenta","cpla");
		$edit->back_url = "contabilidad/cpla";
		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->rule= "trim|required|callback_chcodigo";
		$edit->codigo->mode="autohide";
		$edit->codigo->size=20;
		$edit->codigo->maxlength =15 ;

		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->rule= "strtoupper|required";
		$edit->descrip->size=45;
		$edit->descrip->maxlength =35;

		$edit->departa = new dropdownField("Usa departamento", "departa");
		$edit->departa->option("N","No");
		$edit->departa->option("S","Si");
		$edit->departa->style='width:80px';

		$edit->moneta = new dropdownField("Cuenta Monetaria", "moneta");
		$edit->moneta->option("N","No");
		$edit->moneta->option("S","Si");
		$edit->moneta->style='width:80px';

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Plan de Cuentas</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function chcodigo($codigo){
		if (preg_match("/^[0-9]+(\.[0-9]+)*$/",$codigo)>0){
			$formato=$this->datasis->dameval('SELECT formato FROM cemp LIMIT 1');
			$farr=explode('.',$formato);
			$carr=explode('.',$codigo);
			$max =count($carr);
			$mmac=count($farr);
			if($mmac>=$max){
				for($i=0;$i<$max;$i++){
					if(strlen($farr[$i])!=strlen($carr[$i])){
						$this->validation->set_message('chcodigo',"El c&oacute;digo dado no coincide con el formato: $formato");
						return false;
					}
				}
			}else{
				$this->validation->set_message('chcodigo',"El c&oacute;digo dado no coincide con el formato: $formato");
				return false;
			}
			$pos=strrpos($codigo,'.');
			if($pos!==false){
				$str=substr($codigo,0,$pos);
				$cant=$this->datasis->dameval("SELECT COUNT(*) FROM cpla WHERE codigo='$str'");
				if($cant==0){
					$this->validation->set_message('chcodigo',"No existe la cuenta padre ($str) para registrar esa cuenta");
					return false;
				}
			}
		}else{
			$this->validation->set_message('chcodigo',"El c&oacute;digo parece tener formato invalido");
			return false;
		}
		return true;
	}

	function autocomplete($campo,$cod=FALSE){
		if($cod!==false){
			$cod=$this->db->escape_like_str($cod);
			$qformato=$this->datasis->formato_cpla();
			$data['codigo']="SELECT codigo AS c1 ,descrip AS c2 FROM cpla WHERE $campo LIKE '$cod%' AND codigo LIKE '$qformato' ORDER BY $campo LIMIT 10";
			if(isset($data[$campo])){
				$query=$this->db->query($data[$campo]);
				if($query->num_rows() > 0){
					foreach($query->result() AS $row){
						echo $row->c1.'|'.$row->c2."\n";
					}
				}
			}
		}
	}

	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('descrip');
		logusu('cpla',"PLAN DE CUENTA $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('descrip');
		logusu('cpla',"PLAN DE CUENTA $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('descrip');
		logusu('cpla',"PLAN DE CUENTA $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function _pre_del($do) {
		$codigo=$do->get('codigo');
		$chek =   $this->datasis->dameval("SELECT COUNT(*) FROM cpla WHERE codigo LIKE '$codigo.%'");
		$chek +=  $this->datasis->dameval("SELECT COUNT(*) FROM itcasi WHERE cuenta='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Plan de Cuenta tiene derivados o movimientos';
			return False;
		}
		return True;
	}
}
?>
