<?php
include('pChart/pData.class');
include('pChart/pChart.class');

class imgraf{

	function imgraf(){
		
	}

	function pie($valores,$label,$titulo){
		$nombre=tempnam('/tmp', 'g').'.png';

		$DataSet = new pData;
		$DataSet->AddPoint($valores,'Serie1');
		$DataSet->AddPoint($label  ,'Serie2');
		$DataSet->AddAllSeries(); 
		$DataSet->SetAbsciseLabelSerie('Serie2');

		$Test = new pChart(300,200);
		$Test->setFontProperties(APPPATH.'libraries/pChart/Fonts/tahoma.ttf',8);
		$Test->drawFilledRoundedRectangle(7,7,293,193,5,240,240,240);
		$Test->drawRoundedRectangle(5,5,295,195,5,230,230,230);

		$Test->AntialiasQuality = 0;
		$Test->setShadowProperties(2,2,200,200,200);
		$Test->drawFlatPieGraphWithShadow($DataSet->GetData(),$DataSet->GetDataDescription(),120,100,60,PIE_PERCENTAGE,8);
		$Test->clearShadow();
		$Test->drawPieLegend(210,30,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);
		$Test->drawTitle(10,22,$titulo,50,50,50,300);
		$Test->Render($nombre);
		return $nombre;
	}
}
