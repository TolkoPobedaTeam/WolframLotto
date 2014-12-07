<?php

class Db
{
private $filedir='/../filedb/';

private function getPath($serverid,$type)
{
return __DIR__.$this->filedir."server$serverid.$type.json";
}

public function readData($serverid,$type)
{
$filename=$this->getPath($serverid,$type);
$data=array();
if(file_exists($filename)) 
	{
	$data=json_decode(file_get_contents($filename),true);
	}
return $data;
}


public function writeData($serverid,$type,$data)
{
$filename=$this->getPath($serverid,$type);
file_put_contents($filename,json_encode($data));
}

}