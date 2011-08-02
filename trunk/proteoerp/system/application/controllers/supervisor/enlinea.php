<?php
class Enlinea extends Controller{
	
	function Enlinea(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
            
        }
        
        function pasillo(){
            $this->rapyd->load("datagrid");

            $uri = anchor_popup('http://<#url#>/<#proteo#>','<#sucursal#>');
    
            function linea($url,$proteoerp){
                $senal = http_get("http://$url"."/".$proteoerp."/supervisor/enlinea/senal",array("timeout"=>5,"connecttimeout"=>5));
                if($senal=='SI'){
                    return 'SI';
                }else{
                    return 'NO';
                }
            }
    
            $grid = new DataGrid("Lista de Sucursales");
            $grid->db->from('sucu');
            $grid->order_by("codigo","asc");
            $grid->per_page=15;
            $grid->use_function('linea');
    
            $grid->column("Sucursal"    ,$uri                                     );
            $grid->column("Ver"         ,'<linea><#url#>|<#proteoerp#></linea>'   );
            
            $grid->build();
    
            $data['content'] = $grid->output;
            $data['title']   = heading('Sucursal');
            $data['head']    = $this->rapyd->get_head();
            $this->load->view('view_ventanas', $data);
        }
        
        function ventana(){
            echo "HOLA";
        }
        
        function senal(){
            echo "SI";
        }
}
?>