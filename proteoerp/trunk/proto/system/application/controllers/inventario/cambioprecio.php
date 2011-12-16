<?php
class cambioprecio extends Controller{

	function cambioprecio() {
		parent::Controller(); 
		$this->load->library("rapyd");
	}
	function index(){		
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		$dolar=$this->datasis->dameval("SELECT valor FROM valores WHERE nombre='dolar'");

		$filter = new DataForm("inventario/cambioprecio/consulta/");
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('¿Desea cambiar el precio de sus art&iacute;culos en el inventario seg&uacute;n el precio del d&oacute;lar?');         
		
		$filter->dolar = new inputField("Precio del D&oacute;lar", "dolar");
		$filter->dolar->size=12;
		$filter->dolar->maxlength=10;
		$filter->dolar->insertValue =$dolar;

		$filter->desc2 = new inputField("Descuento Precio2", "desc2");
		$filter->desc2->size=12;
		$filter->desc2->maxlength=10;
		$filter->desc2->insertValue ='3';
		      
		$filter->desc3 = new inputField("Descuento Precio3", "desc3");
		$filter->desc3->size=12;
		$filter->desc3->maxlength=10;
		$filter->desc3->insertValue ='5';
		        
		$filter->desc4 = new inputField("Descuento Precio4", "desc4");
		$filter->desc4->size=12;
		$filter->desc4->maxlength=10;
		$filter->desc4->insertValue ='7';

		$filter->button("btnsubmit", "Aceptar", form2uri(site_url('inventario/cambioprecio/cambio'),array('dolar','desc2','desc3','desc4')), $position="BL");
		$filter->build_form();                   
		
		$data['content']  = $filter->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = "<h1>Cambio de Precio</h1>";
		$this->load->view('view_ventanas', $data);

	}
	function cambio($dolar='',$desc2='',$desc3='',$desc4=''){
		
		if(empty($dolar) or empty($desc2)or empty($desc3)or empty($desc4)){

	  $cgrid='';
		$data['content'] = '<b style="color:red;">Tiene valores en 0 y/o vacios</b>';
		$anchor= '<pre>'.anchor("inventario/cambioprecio","Regresar al Modulo").'</pre>';

		}else{
			
		$atts = array(
      'width'      => '800',
      'height'     => '600',
      'scrollbars' => 'yes',
      'status'     => 'yes',
      'resizable'  => 'yes',
      'screenx'    => '0',
      'screeny'    => '0'
    ); 
    	
		function colum($margen1){
			if ($margen1<=0)
				return ('<b style="color:red;">'.$margen1.'</b>');
			else
				return ('<b style="color:green;">'.$margen1.'</b>');
		}
		function colum2($precio1){
			if ($precio1>0)
				return ('<b style="color:#7D4394;">'.$precio1.'</b>');
			else
				return ('<b style="color:red;">'.$precio1.'</b>');
		}
		
		function colum3($ultimo){
			if ($ultimo>0)
				return ('<b style="color:#171D94">'.$ultimo.'</b>');
			else
				return ('<b style="color:red;">'.$ultimo.'</b>');
		}
		
		$this->rapyd->load("datagrid");
		
		$mSQL_1 =  $this->db->query("SELECT codigo,dolar,iva,IF(formcal='U',ultimo,pond)as costo FROM sinv");
		$data['result']=$mSQL_1->result();
		foreach ($data['result'] AS $items){
			
			$codigo=$items->codigo;
			$cdolar=$items->dolar;
			$costo=$items->costo;
			$iva=$items->iva;
			
			$precio1=$cdolar*$dolar;
			$base1=$precio1/(($iva*1/100)+1);
			$margen1=(($base1-$costo)*100)/$costo;
			
			$cdolar2=$cdolar-($cdolar*$desc2/100);
			$precio2=$cdolar2*$dolar;
			$base2=$precio2/(($iva*1/100)+1);
			$margen2=(($base2-$costo)*100)/$costo;
			
			$cdolar3=$cdolar2-($cdolar2*$desc2/100);
			$precio3=$cdolar3*$dolar;
			$base3=$precio3/(($iva*1/100)+1);
			$margen3=(($base3-$costo)*100)/$costo;

			$cdolar4=$cdolar3-($cdolar3*$desc3/100);
			$precio4=$cdolar4*$dolar;
			$base4=$precio4/(($iva*1/100)+1);
			$margen4=(($base4-$costo)*100)/$costo;
		
					  
  	  $sql="UPDATE sinv set dolar3='$cdolar3',dolar4='$cdolar4',dolar2='$cdolar2',precio1='$precio1',base1='$base1',margen1='$margen1',precio2='$precio2',base2='$base2',margen2='$margen2',precio3='$precio3',base3='$base3',margen3='$margen3',precio4='$precio4',base4='$base4',margen4='$margen4'WHERE codigo='$codigo'";
		  $update = $this->db->query($sql);
		  //echo '<pre>';
			//print_r($sql);
			//echo '</pre>'; 
			
		}
		
		$grid = new DataGrid("Lista de de Art&iacute;culos");
		$select=array("dolar","dolar2","dolar3","dolar4","descrip","codigo","IF(formcal='U',ultimo,pond)as costo","margen1","base1","precio1","margen2","base2","precio2","margen3","base3","precio3","margen4","base4","precio4");     		
		$grid->db->select($select);  
		$grid->db->from("sinv");
		
		$grid->use_function('colum');
		$grid->use_function('colum2');
		$grid->use_function('colum3');
		$grid->order_by("codigo","desc");                          
		$grid->per_page = 15;

		$grid->column("Codigo","codigo","align='left'");
		$grid->column("Descripcion","descrip","align='left'");
		$grid->column("Costo", "<colum3><#costo#></colum3>",'align=right');
		$grid->column("Dolar1", "<colum3><#dolar#></colum3>",'align=right');
		$grid->column("Margen1","<colum><#margen1#></colum>",'align=right');
		$grid->column("Base1","<colum><#base1#></colum>",'align=right');
		$grid->column("Cred. 60d","<colum2><#precio1#></colum2>",'align=right');
		$grid->column("Dolar2", "<colum3><#dolar2#></colum3>",'align=right');
		$grid->column("Margen2","<colum><#margen2#></colum>",'align=right');
		$grid->column("Base2","<colum><#base2#></colum>",'align=right');
		$grid->column("Cred. 30d","<colum2><#precio2#></colum2>",'align=right');
		$grid->column("Dolar3", "<colum3><#dolar3#></colum3>",'align=right');
		$grid->column("Margen3","<colum><#margen3#></colum>",'align=right');
		$grid->column("Base3","<colum><#base3#></colum>",'align=right');
		$grid->column("Contado","<colum2><#precio3#></colum2>",'align=right');
		$grid->column("Dolar4", "<colum3><#dolar4#></colum3>",'align=right');
		$grid->column("Margen4","<colum><#margen4#></colum>",'align=right');
		$grid->column("Base4","<colum><#base4#></colum>",'align=right');
		$grid->column("C.15.000$","<colum2><#precio4#></colum2>",'align=right');

		$grid->build();
		
		$data['content'] = '<b style="color:green;">Cambio Exitoso</b>';
		$anchor= '<pre>'.anchor_popup("reportes/ver/SINVENT","Imprimir Listado",$atts).'</pre>';
		$cgrid=$grid->output;
	}

		$data['content'] .= '<pre><b style="color:#16487E;">Dolar:</b>'.$dolar.'</pre>';
		$data['content'] .= '<pre><b style="color:#16487E;">Descuento Precio2:</b>'.$desc2.'</pre>';
		$data['content'] .= '<pre><b style="color:#16487E;">Descuento Precio3:</b>'.$desc3.'</pre>';
		$data['content'] .= '<pre><b style="color:#16487E;">Descuento Precio4:</b>'.$desc4.'</pre>';
		$data['content'] .= $anchor;
		$data['content'] .= $cgrid;
		$data['title']   = "<h1>Inventario</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
}
?>
