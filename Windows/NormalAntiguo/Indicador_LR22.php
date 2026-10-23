<?php
     
	//Windows Indicador LR22
    
    define("TIPO", "IndicadorLR22");// YaouaA12
    $callback ='mycallback';
  
    
    if(isset($_GET['mycallback']))  {
        $callback = $_GET['mycallback'];
    }
    $arr_peso =array();


	 $COM = 'COM1';
	 $puerto_com = shell_exec('powershell Get-ItemProperty -Path "HKLM:\hardware\devicemap\serialcomm"');
	 //$COM = substr($puerto_com, strpos($puerto_com, "COM"),4);
	 $raw_res = shell_exec('powershell $port= new-Object System.IO.Ports.SerialPort '.$COM.',1200,None,8,one;Try{$port.Open()}Catch{echo "-1000";$port = 0}Finally{if ($port -ne 0){for ($i=1; $i -le 5; $i++){$port.ReadLine()}$port.Close()}};');

	$resultado = explode("\n", $raw_res);

	if($resultado[0] === "-1000"){
		$arr_peso['estado']="estable";
		$arr_peso['peso']=17.630;
	}else{
		$result = preg_replace('/\D/',"", $resultado[1]);
		//$result = preg_replace('/[^0-9.]*/',"", $resultado[1]);
		//if(strrpos($resultado[1], "KGM")){
		if(preg_match('/^D|KG/',$aux[1])){
			$arr_peso['estado']="estable";
		}else{
			$arr_peso['estado']="inestable";
		}
		//$arr_peso['peso']=$result;
		$arr_peso['peso']= preg_match('/KG/',$aux[1])?preg_replace('/[^0-9.]*/',"",$aux[1]):(float)substr($result,0,3).'.'.substr($result,3,3);
	}
	echo $callback.'(' . json_encode($arr_peso) . ')';
	
	
    echo $callback.'(' . json_encode($arr_peso) . ')';

    // If you want to change the configuration, the device must be closed.
    $serial->deviceClose();
}
?>