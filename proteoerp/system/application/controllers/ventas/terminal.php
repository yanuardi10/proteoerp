<?php


class Terminal extends Controller {

	function Terminal(){
		parent::Controller(); 
		$this->load->library("rapyd");
    
    //I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
    define ("THISFILE",   APPPATH."controllers/ventas/". $this->uri->segment(2).EXT);
	}


  ##### index #####
  function index(){
    redirect("ventas/terminal/despacha");
  }

  ##### DataFilter + DataGrid #####
  function despacha(){
		//filteredgrid//
		$this->rapyd->load("datafilter","datagrid");
		
		//filter  search/osp
		$filter = new DataForm("ventas/terminal/despacha/search");
		$filter->numero = new inputField("Numero", "numero");
		$filter->numero->rule = "required";
		$filter->numero->size = 15;
		//$filter->buttons("search");
		$filter->build_form();
		$sal = $filter->output;
		//_0040024 
		
		if($this->rapyd->uri->is_set("search")  AND $filter->is_valid()){
			$numero=$_POST['numero'];
			$mSQL="SELECT DATE_FORMAT(fecha,'%d/%m/%Y') fecha, cod_cli,nombre, totalg FROM sfac WHERE numero='$numero'";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$this->_procesar($numero);
				$row = $query->row_array();
				$sal .= '<center><table id="tabledespa">';
				$sal .= '<tr><td>N&uacute;mero</td><td align="right">'.$numero.'</td></tr>';
				$sal .= '<tr><td>Fecha        </td><td align="right">'.$row['fecha'].'</td></tr>';
				$sal .= '<tr><td>Cliente      </td><td align="right">'.'('.$row['cod_cli'].') '.$row['nombre'].'</td></tr>';
				$sal .= '<tr><td>Monto        </td><td align="right">'.number_format($row['totalg'],2,',','.').'</td></tr></table></center>';
			}else{
				$sal .='N&uacute;mero de Factura no valido';
			}
			//$sal .= 'Pase Por aqui'.$mSQL;
			//grid
			/*
			$grid = new DataGrid('');
			$grid->order_by("numero","desc");
			$grid->per_page = 10;
			$grid->use_function("substr");
			$grid->column("Numero","numero");
			$grid->column("fecha","fecha");
			$grid->column("Nombre","nombre");
			$grid->column("total","<number_format><#totalg#>|2|,|.</number_format>","align=right");
			$grid->build();
			$sal .= $grid->output;*/
		}
		
		$content["body"] = $sal;
		$content["titu"] = 'Despacho por terminal';
		$content["rapyd_head"]=$this->rapyd->get_head();
		
		$this->load->view('view_terminal', $content);
  }
  function _procesar($fila){
		$usuario = $this->session->userdata('usuario');
		$mSQL="UPDATE sitems SET despacha=IF(despacha='I','S','I'), fdespacha=now(), udespacha='$usuario' WHERE numa=$fila";
		var_dum($this->db->simple_query($mSQL));
		$mSQL="UPDATE sfac SET fdespacha=now(), udespacha='$usuario' WHERE numero=$fila";
		var_dum($this->db->simple_query($mSQL));
	}
}
?>