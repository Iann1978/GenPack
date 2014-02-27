<?php

            
function Out_CS_Client_Handler($packInfo,$config)
{
	$pathname = $packInfo['hand_cs'];
	if (file_exists($pathname))
	{
		return;
	}
	
	if (DEBUG_OUT_FILE_NAME)
	{
		echo "\n".$pathname;	
	}	

	
	$str = "\nusing System;";	
	$str .= "\nusing System.Collections.Generic;";
	$str .= "\nusing System.Linq;";	
	$str .= "\nusing System.Text;";	
	//$str .= "\nusing UnityEngine;";	
	$str .= "\nnamespace ".$config['CS_Client_Handler_Packet'];
	$str .= "\n{";	
	$str .= "\n\tclass OnPacket_".$packInfo['name']." : tHandler";	
	$str .= "\n\t{";	
	$str .= "\n\t\tpublic void onPacket(tPacket packet)";	
	$str .= "\n\t\t{";	
	$str .= "\n\t\t\tPacket_".$packInfo['name']." pack = (Packet_".$packInfo['name'].") packet;";
	//$str .= "\n\t\t\tDebug.Log(\"OnSCPacket_".$packInfo['name']."\");";		
	$str .= "\n\t\t}";
	

	$str .= "\n\t}";
	$str .= "\n}";
	
	$file = fopen ( $pathname, "w" );
	fwrite($file,$str);
	fclose($file);
	
}

function out_client_hand_cs($packInfo)
{
	$pathname = $packInfo['hand_c_cpp'];
	echo "\out_client_hand_c_cpp pathname:".$pathname;	
		
	$str = "//OnPacket_".$packInfo['name'];
	$str .= "\nint OnPacket_".$packInfo['name']."(tPacket *pack)";
	$str .= "\n{";
	$str .= "\n}";
	
	$file = fopen ( $pathname, "w" );	
	fwrite($file,$str);
	fclose($file);	
}

function Out_CS_Packet($packInfo,$config)
{


	$pathname = $packInfo['pack_cs'];
	if (DEBUG_OUT_FILE_NAME)
	{
		echo "\n".$pathname;	
	}
		
	$str = "//" . $packInfo['packname'] . ".cs";
	$str .= "\nusing System;";
	$str .= "\nusing System.Collections.Generic;";
	$str .= "\nusing System.Linq;";
	$str .= "\nusing System.Text;";
	$str .= "\nusing System.IO;";

	$str .= "\nnamespace ".$config['CS_Client_Handler_Packet'];
	$str .= "\n{";
  $str .= "\nclass " . $packInfo['packname'] . " : tPacket";
  $str .= "\n{";
  
  // properties
  foreach( $packInfo['props'] as $prop)
	{
		if ($prop['type']=='string')
		{
			$str .= "\n\tpublic string ".$prop['name'].";";
		}
		else
		{
			$str .= "\n\tpublic ".$prop['type']." ".$prop['name'].";";
		}		
	}
	$str .= "\n";
	
	// function read
  $str .= "\n    public int read(byte[] s)";
  $str .= "\n    {";
  $str .= "\n        int readed = 0;";
  foreach ( $packInfo['props'] as $values ) 
  {
  	$str .= "\n\t\t{";
		if ($values['type']=='string')
		{
			$str .= "\n\t\t\tshort len = BitConverter.ToInt16(s, readed); readed += 2;";
  		$str .= "\n\t\t\t" . $values ["purename"] . " = System.Text.Encoding.Default.GetString(s, readed, len);";
  		$str .= "\n\t\t\treaded += " . $values ["purename"] . ".Length;";
		}
		else if ($values['type']=='int')
		{
			$str .= "\n\t\t\t" . $values ["purename"] . " = BitConverter.ToInt32(s,readed); readed += 4;";
		}
		else
		{
			$str .= "\tPACKREAD(" . $values ["purename"] . ");\n";
		}
		$str .= "\n\t\t}";
	} 
  $str .= "\n        return readed;";
  $str .= "\n    }";
  $str .= "\n";
  
  // function write
  $str .= "\n    public int write(Stream s)";
  $str .= "\n    {";
  $str .= "\n        int writed = 0;";
  $str .= "\n";
  foreach ( $packInfo['props'] as $values ) 
  {
  	$str .= "\n\t\t{";
		if ($values['type']=='string')
		{
			$str .= "\n\t\t\tbyte[] bytes = System.Text.Encoding.Default.GetBytes(" . $values ["purename"] . ");";
  		$str .= "\n\t\t\tbyte[] len = BitConverter.GetBytes((short)bytes.Length);";
  		$str .= "\n\t\t\ts.Write(len, 0, len.Length); writed += len.Length;";
  		$str .= "\n\t\t\ts.Write(bytes, 0, bytes.Length); writed += bytes.Length;";
		}
		else if ($values['type']=='int')
		{
			$str .= "\n\t\t\tbyte[] bytes = BitConverter.GetBytes(" . $values ["purename"] . ");";
			$str .= "\n\t\t\ts.Write(bytes, 0, 4);writed += bytes.Length;";
		}
		else
		{
			$str .= "\tPACKREAD(" . $values ["purename"] . ");\n";
		}
		$str .= "\n\t\t}";
	}

  $str .= "\n        return writed;";
  $str .= "\n    }";
  $str .= "\n";
  
  // function length
  $str .= "\n    public int length()";
  $str .= "\n    {";
  $str .= "\n\t\tint retlen = 0;";
	foreach ( $packInfo['props'] as $values ) 
	{
		$str .= "\n\t\t{";
		if ($values['type']=='string')
		{
			$str .= "\n\t\t\tbyte[] bytes = System.Text.Encoding.Default.GetBytes(" . $values ["purename"] . ");";
  		$str .= "\n\t\t\tbyte[] len = BitConverter.GetBytes((short)bytes.Length);";
  		$str .= "\n\t\t\tretlen+=bytes.Length+len.Length;";
		}
		else if ($values['type']=='int')
		{
			$str .= "\n\t\t\tretlen+=4;";
		}
		else
		{
			$str .= "\n\t\t+ sizeof(" . $values ["purename"] . ")";
		}
		$str .= "\n\t\t}";
	}
  $str .= "\n        return retlen;";
  $str .= "\n    }";
  $str .= "\n";
  $str .= "\n    public int id() { return (int)packetids." . $packInfo['name'] . "; }";    
  $str .= "\n";
  $str .= "\n    public string desc()";
  $str .= "\n    {";
  $str .= "\n        return \"CSPacket_" . $packInfo['name'] . "\";";
  $str .= "\n    }";
  $str .= "\n}";
  $str .= "\nclass PacketFactory_" . $packInfo['name'] . " : tPacketFactory";
  $str .= "\n{";
  $str .= "\n\tpublic int id() { return (int)packetids." . $packInfo['name'] . "; }";
  $str .= "\n\tpublic tPacket create() { return new Packet_" . $packInfo['name'] . "(); }";
  $str .= "\n};";
	$str .= "\n}";
	
	
	


	$file = fopen ( $pathname, "w" );
	fwrite($file,$str);
	fclose($file);
	
}

function Out_CS_Client_Regist($pathname,$packs,$config)
{
	echo "\nOut_CS_Client_Regist\n";
	echo $pathname;

	$str = "\nusing System;";
	$str .= "\nusing System.Collections.Generic;";
	$str .= "\nusing System.Linq;";
	$str .= "\nusing System.Text;";

	$str .= "\nnamespace ".$config['CS_Client_Handler_Packet'];
	$str .= "\n{";
	$str .= "\n\tclass RegistPacketAndHandler";
	$str .= "\n\t{";
	$str .= "\n\t\tstatic public void Do()";
	$str .= "\n\t\t{";
	$str .= "\n\t\t\ttNet net = tNet.Ins;";
	foreach($packs as $pack)
	{
		if (strpos($pack,"_SC"))
		{
			$pos0 = strrpos($pack,"\\Packet_");
			$pos1 = strrpos($pack,".");
			$name = substr($pack,$pos0+8,$pos1-$pos0-8);
			$str .= "\n\t\t\tnet.registPacketAndHandler(new PacketFactory_".$name."(), new OnPacket_".$name."());";
		}
	}
	$str .= "\n\t\t}";
	$str .= "\n\t}";
	$str .= "\n}";
	
	$file = fopen ( $pathname, "w" );
	fwrite($file,$str);
	fclose($file);
	
}

function Out_CS_PacketIds($pathname,$packs,$config)
{
	echo "\n".$pathname;
 	
	$str = "//packetids.cs";
	$str .= "\nusing System;";
	$str .= "\nusing System.Collections.Generic;";
	$str .= "\nusing System.Linq;";
	$str .= "\nusing System.Text;";

	$str .= "\nnamespace ".$config['CS_Client_Handler_Packet'];
	$str .= "\n{";
	$str .= "\n\tenum packetids";
	$str .= "\n\t{";
	foreach($packs as $pack)
	{
		$pos0 = strrpos($pack,"\\Packet_");
		$pos1 = strrpos($pack,".");
		$name = substr($pack,$pos0+8,$pos1-$pos0-8);
		$str .= "\n\t\t".$name.",";	
	}
	$str .= "\n\t\tCount,";	
	$str .= "\n\t};";
	$str .= "\n}";
	
	$file = fopen ( $pathname, "w" );
	fwrite($file,$str);
	fclose($file);
	
}


?>