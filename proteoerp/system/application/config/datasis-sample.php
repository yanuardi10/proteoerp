<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//**************************************
// 
//**************************************

//definicion de las cajas
$config['cajas']['cobranzas'] = '99'; //Ventas y cobranzas
$config['cajas']['efectivo']  = 'S0'; //Efectivo
$config['cajas']['valores']   = 'VC'; //Cesta tickes y cheques
$config['cajas']['tarjetas']  = '98'; //Tarjetas de credito y debito
$config['cajas']['gastos']    = '96'; //Gastos por justificar

//Los tipos de pagos que deberian ir a las cajas
$config['fpago']['efectivo']  =  'EF';
$config['fpago']['valores']   =  'CT|CH';
$config['fpago']['tarjetas']  =  'TD|TC';