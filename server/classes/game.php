<?php

class Game
{

public $Users;
public $lastaction;
private $carditems=array("Afghanistan", "Albania", "Belarus", "Brazil", "Burkina Faso", "China", "Cuba", "Egypt", "Finland", "Germany", "Greece", "Iran", "Israel", "India", "Libya", "Malta", "Morocco", "Norway", "Poland", "Qatar", "Russia", "Spain", "Sweden", "Thailand", "Zimbabwe");
private $carditemsround;
public $roundid;
public $roundcardname;
public $rounds;
public $roundchoices;

public function __construct()
{
$this->Users=new Users();
}



public function RoundStart()
{
if($this->roundid>=$GLOBALS["maxrounds"]) $this->GameOver();
$GLOBALS["lasttime"]=time();
$this->lastaction='game.round.start';

//get random cardname
$cardname=array_shift($this->carditemsround);

//get data for it
$carddata='Test data fro country "*'.substr($cardname,1).'"';

$this->roundid++;
$this->rounds[$this->roundid]=array(
	"cardname"=>$cardname,
	"carddata"=>$cardata
	);
$this->roundchoices=array();
$this->roundcardname=$cardname;
//send card data
send_message(array('type'=>'game.round.start', 'data'=>array("roundid"=>$this->roundid,"carddata"=>$carddata)));	
}


public function setCard($sessid,$placeid)
{
$ukey=$this->Users->getUserKeyBySessid($sessid);
$userid=$this->Users->data["users"][$ukey]["userid"];
$this->roundchoices[$userid]=$placeid;
}


public function RoundEnd()
{
$GLOBALS["lasttime"]=time();
$this->lastaction='game.round.end';

$choices=array();
$usersmap=$this->Users->getMap();
foreach($this->roundchoices as $userid=>$placeid)
	{
	//get name from user for this pos
	$uind=$usersmap[$userid];
	$cname=$this->Users->data["users"][$uind]["positions"][$placeid]["name"];
	if($cname!=$this->roundcardname)//wrong
		{
		$this->Users->data["users"][$uind]["errors"]++;
		$choices[$userid]=array("placeid"=>$placeid,"result"=>0);
		}
		else//success
		{
		$this->Users->data["users"][$uind]["success"]++;
		if($this->Users->data["users"][$uind]["success"]>=count($this->Users->data["users"][$uind]["positions"]))
			{
			$this->GameOver();
			}
		$this->Users->data["users"][$uind]["positions"][$placeid]["status"]=1;
		$choices[$userid]=array("placeid"=>$placeid,"result"=>1);
		}
	}
send_message(array('type'=>'game.round.end', 'data'=>array("users"=>$this->Users->getData($choices)))); //send data
}




public function Start()
{
$GLOBALS["lasttime"]=time();
$this->lastaction='game.start';
$this->Users->Get();
$users=$this->Users->data["users"];
$placemap=$this->Users->data["placemap"];

//generate cards
foreach($users as $ukey=>$user)
	{
	$items=array_rand($this->carditems,6);
	$positions=array();
	foreach($items as $k=>$vind)
		{
		$v=$this->carditems[$vind];
		$positions[$k]=array(
			"name"=>$v,
			"status"=>0,
			"id"=>$k,
			);
		$placemap[$v][]=array("userid"=>$user["userid"],"pos"=>$k);
		}
	$users[$ukey]["positions"]=$positions;
	}
$this->Users->data=array("users"=>$users,"placemap"=>$placemap);
$this->Users->Save();

//send refresh info
send_message(array('type'=>'game.start', 'data'=>array("users"=>$this->Users->getData()))); //send data
}

public function AttachUser($serverid,$name,$sessid)
{
$this->Users->Add($name,$sessid);
$dtime=time()-$GLOBALS["lasttime"];
if($this->Users->count>1 and $dtime>$GLOBALS["times"]["waitplayers"])
	{
	//start game
	$GLOBALS["lasttime"]=time();
	$this->Start();							
	}
	else
	{
	//refresh users data
	send_message(array('type'=>'game.refresh', 'data'=>array("dtime"=>$GLOBALS["times"]["waitplayers"]-$dtime,"users"=>$this->Users->getData()))); //send data
	}
}



public function GameOver()
{
$GLOBALS["lasttime"]=time();
send_message(array('type'=>'game.end', 'data'=>array("users"=>$this->Users->getData()))); //send data
$this->End();
}

public function End()
{
//reset data
$this->lastaction='game.end';
$this->Users->data=array("users"=>array(),"placemap"=>array());
$this->roundid=0;
$this->roundcardname='';
$this->rounds=array();
$this->carditemsround=$this->carditems;
shuffle($this->carditemsround);
}

}