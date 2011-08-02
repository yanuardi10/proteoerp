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
                $CI =& get_instance();
                
                $CI->load->library('xmlrpc');
                $CI->xmlrpc->xmlrpc_defencoding=$CI->config->item('charset');
                
                $server_url = reduce_double_slashes("http://$url"."/$proteoerp/".'rpcserver');
                $CI->xmlrpc->server($server_url , 80);
                $CI->xmlrpc->method('ventanainf');
                
                $request = array('a');
                $CI->xmlrpc->request($request);
                
                if(!$CI->xmlrpc->send_request())
                    return 'No Disponible';
                else{
                    $ima="http://".$url."/".$proteoerp."/supervisor/enlinea/ventana";
                    return "<iframe src='$ima' width='500px' height='200px'/></iframe>";
                }
            }
    
            $grid = new DataGrid();
            $grid->db->from('sucu');
            $grid->order_by("codigo","desc");
            $grid->per_page=15;
            $grid->use_function('linea','ver');
    
            $grid->column("Sucursal"    ,$uri                                       );
            $grid->column("En Linea"    ,'<linea><#url#>|<#proteo#></linea>'        );
            
            
            $grid->build();
            
            //$out .= '<div class=\'box col1\'><h3>'.htmlentities($panel).'</h3>';
    
            $conten['grid' ]  =$grid->output;
            $data['content'] = $this->load->view('view_enlinea', $conten,true);
            $data['title']   = heading('Informacion de Sucursales');
            $data["head"]    = $this->rapyd->get_head();
            $this->load->view('view_ventanas', $data);
        }
        
        function ventana(){
            $this->rapyd->load("datagrid");
            
            $sql="
            SELECT 'fecha Ultimo Kardex' descrip,max(fecha) valor FROM costos
            UNION ALL 
            SELECT CONCAT_WS(' ','Vendido hasta el momento del ',(SELECT MAX( fecha) FROM sfac)) descrip, SUM(totals*IF(tipo_doc='D',-1,1)) AS valor FROM sfac WHERE fecha=(SELECT MAX( fecha) FROM sfac) AND tipo_doc<>'X' AND MID(numero,1,1)<>'_' 
            ";
            
            $sql=$this->db->query($sql);
            $arr=$sql->result_array($sql);
            
            $grid = new DataGrid("",$arr);
            
            $grid->order_by("codigo","desc");
            $grid->per_page=15;
            $grid->use_function('linea','ver');
    
            $grid->column("Descripci&oacute;n" ,'descrip'   );
            $grid->column("Valor"              ,'valor'     );

            $grid->build();
            
            $data['content'] =$grid->output;
            $data["head"]    = $this->rapyd->get_head();
            $this->load->view('view_ventanas_sola', $data);
        }
        
        function senal(){
            echo base_url().'images/N.gif';
        }
        
        function prueba(){
            
        }
}
?>