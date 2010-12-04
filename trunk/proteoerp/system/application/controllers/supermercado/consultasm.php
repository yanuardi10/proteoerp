<?php
class Consultasm extends Controller {

	var $url='supermercado/consultasm/';
	function Consultasm(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		redirect($this->url."precios");
	}

	function precios(){

		$barras = array(
			'name'      => 'barras',
			'id'        => 'barras',
			'value'     => '',
			'maxlength' => '15',
			'size'      => '16',
			//'style'     => 'display:none;',
		);


                $sinv=array('tabla'   =>'maes',
	                    'columnas'=>array('codigo' =>'C&oacute;digo',
			                      'descrip'=>'Descripcion',
					      'marca'  =>'Marca',
					      'referen'=>'Referencia'),
			                      'filtro'  =>array('codigo' =>'C&oacute;digo',
			                      'descrip'=>'Descripcion',
					      'marca'  =>'Marca',
					      'referen'=>'Referencia',),
			                      'retornar'=>array('codigo'=>'barras'),
			                      'titulo'  =>'Buscar Articulo');
				    
		$out  = form_open($this->url.'precios');
		$out .= "Introduzca un C&oacute;digo ";
		$out .= form_input($barras);
		$out .= $this->datasis->modbus($sinv);
		$out .= form_close();

		$link=site_url($this->url.'rprecios');

		$data['script']= <<<script
		<script type="text/javascript">
		$(document).ready(function(){
			$("#resp").hide();
			$("#barras").attr("value", "");
			$("#barras").focus();
			$("form").submit(function() {
				mostrar();
				return false;
			});

		});

		function mostrar(){
			$("#resp").hide();
			var url = "$link";
			$.ajax({
				type: "POST",
				url: url,
				data: $("input").serialize(),
				success: function(msg){ 
					$("#resp").html(msg).fadeIn("slow");
					$("#barras").attr("value", "");
					$("#barras").focus();
				}
			});
		}
		</script>
script;
		$data['content'] = '<div id="resp" style=" width: 100%; height: 300px" >&nbsp;</div>';
//		echo base_url()."images/logopm.jpg";
		$data['logo']   = "<img src='".base_url()."images/logopm.jpg' width=150>";
		$data['title']   = "<h1>$out</h1>";
		$data["head"]    = script("jquery-1.2.6.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);
	}

	function rprecios($cod_bar=NULL){
		if(empty($cod_bar)){
			$cod_bar=$this->input->post('barras');
			if ($cod_bar===false){
				echo 'Debe introducir un c&oacute;digo de barras';
				return 0;
			}
		}
		$mSQL_p='SELECT empaque,dempaq,mempaq,fracxuni,ensambla,depto,familia,grupo,tamano,medida,serial,maximo,minimo,codigo, referen, barras, descrip, corta, codigo, marca, precio1, precio2, precio3, precio4,base1,base2,base3,base4, dvolum1, dvolum2, existen, mempaq, dempaq FROM maes';
		$mSQL  =$mSQL_p." WHERE barras='$cod_bar'";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() == 0){
			$mSQL  =$mSQL_p." WHERE codigo='$cod_bar'";
			$query = $this->db->query($mSQL);
			if ($query->num_rows()== 0){
			    $mSQL  =$mSQL_p." WHERE referen='$cod_bar'";
			    $query = $this->db->query($mSQL);
			    if ($query->num_rows()== 0){
				// Busca por suplementarios
				$mSQL_p='SELECT a.suplemen barras, b.empaque,b.dempaq,b.mempaq,b.fracxuni,b.ensambla,b.depto,b.familia,b.grupo,b.tamano,b.medida,b.serial,b.maximo,b.minimo,b.codigo, b.referen, b.descrip, b.corta, b.codigo, b.marca, b.precio1, b.precio2, b.precio3, b.precio4,b.base1,b.base2,b.base3,b.base4, b.dvolum1, b.dvolum2, b.existen, b.mempaq, b.dempaq FROM suple a JOIN maes b ON a.codigo=b.codigo ';
				$mSQL  =$mSQL_p." WHERE a.suplemen='$cod_bar'";
	    			$query = $this->db->query($mSQL);
				if ($query->num_rows()== 0){
				    echo 'Producto no registrado';
				    return 0;
				}
			    }
			}
		}
		
		
		
		$row = $query->row();
		$data['depto']   = "(".$row->depto  .")".$this->datasis->dameval("SELECT descrip  FROM dpto WHERE depto =".$this->db->escape($row->depto  ));
		$data['familia'] = "(".$row->familia.")".$this->datasis->dameval("SELECT descrip  FROM fami WHERE familia=".$this->db->escape($row->familia)." AND depto=".$this->db->escape($row->depto  ));
		$data['grupo']   = "(".$row->grupo  .")".$this->datasis->dameval("SELECT nom_grup FROM grup WHERE grupo=".$this->db->escape($row->grupo  )." AND depto=".$this->db->escape($row->depto  )." AND familia=".$this->db->escape($row->familia));
		$data['tamano'] = $row->tamano;
		$data['medida'] = $row->medida;
		$data['serial'] = $row->serial;
		$data['maximo'] = $row->maximo;
		$data['minimo'] = $row->minimo;
		$data['dempaq'] = $row->dempaq;
		$data['mempaq'] = $row->mempaq;
		$data['fracxuni'] = $row->fracxuni;
		$data['ensambla'] = $row->ensambla;
		$data['empaque']  = $row->empaque;
;
				
		$data['precio1']  = number_format($row->precio1,2,',','.');
		$data['precio2']  = number_format($row->precio2,2,',','.');
		$data['precio3']  = number_format($row->precio3,2,',','.');
		$data['precio4']  = number_format($row->precio4,2,',','.');
		$data['base1']  = number_format($row->base1,2,',','.');
		$data['base2']  = number_format($row->base2,2,',','.');
		$data['base3']  = number_format($row->base3,2,',','.');
		$data['base4']  = number_format($row->base4,2,',','.');
		
		$data['dvolum1'] = $row->dvolum1;
		$data['dvolum2'] = $row->dvolum2;

		$data['descrip'] = $row->descrip;

		$data['corta'] = $row->corta;

		$data['referen'] = $row->referen;
		
		$data['codigo']  = $row->codigo;
		$data['marca']   = $row->marca;
		
		$data['existen'] = $this->datasis->dameval("SELECT sum(a.cantidad*b.fracxuni+a.fraccion) FROM ubic a JOIN maes b ON a.codigo=b.codigo WHERE a.codigo='".$row->codigo."' AND a.ubica IN ('DE00','DE01')");
		//$row->existen;
		$data['barras']  = $row->barras;
		$data['moneda']  = 'Bs.F.';
		
		$query2="SELECT c.sucursal,b.ubica,a.codigo,cantidad,fraccion FROM maes a JOIN ubic b ON a.codigo = b.codigo JOIN caub c ON b.ubica = c.ubica WHERE b.codigo=$row->codigo AND c.invfis = 'N' AND c.gasto = 'N' ";
		//$row2  = $this->db->query($query2);

		$data['almacenes']  = $query2;//$row2->result();
		
		$this->load->view('view_rpreciosm', $data);
		return 1;
	}

}
?>