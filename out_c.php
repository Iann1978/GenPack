<?php
include_once ($argv[1] . "/config.php");
function Out_C_Packet($packInfo)
{
	out_pack_c_h($packInfo);
	out_pack_c_cpp($packInfo);
	
}
function Out_C_Server_Handler($packInfo)
{
	$pathname = $packInfo['hand_c_cpp'];
	if (file_exists($pathname))
	{
		return;
	}
	
	if (DEBUG_OUT_FILE_NAME)
	{
		echo "\n".$pathname;	
	}	

	$str = "//OnPacket_".$packInfo['name'];
	$str .= "\n#include \"tIocpServer.h\"";
	$str .= "\n#include \"tLogger.h\"";
	$str .= "\n#include \"tPlayer.h\"";
	$str .= "\n#include \"tQueryPool.h\"";
	$str .= "\n#include \"server_config.h\"";
	$str .= "\n#include \"packs/Packet_".$packInfo['name'].".h\"";
	$str .= "\n\n";
	
	
	
	$str .= "\nint OnPacket_".$packInfo['name']."(tConnection *con, tPacket *pack)";
	$str .= "\n{";
	$str .= "\n\ttIocpServer& server=tIocpServer::Ins();";
	$str .= "\n\ttQuery *q = newquery();";
	$str .= "\n\tPacket_".$packInfo['name']." &req = *(Packet_".$packInfo['name']."*)pack;";
	$str .= "\n\t//Packet_SCMessage &res = *(Packet_SCMessage *)server.newPacket(PacketIds::SCMessage);";
	$str .= "\n\tfreequery(q);";
	$str .= "\n\treturn 0;";
	$str .= "\n}";
	
	$file = fopen ( $pathname, "w" );
	fwrite($file,$str);
	fclose($file);
	
}

function Out_C_Client_Handler($packInfo)
{
	$pathname = $packInfo['hand_c_cpp'];
	echo "\n".$pathname;	
		
	$str = "//OnPacket_".$packInfo['name'];
	$str .= "\nint OnPacket_".$packInfo['name']."(tPacket *pack)";
	$str .= "\n{";
	$str .= "\n}";
	
	$file = fopen ( $pathname, "w" );	
	fwrite($file,$str);
	fclose($file);
}

function Out_C_IncludePackets($pathname,$packs)
{
	echo "\n".$pathname;
	
	$str = "//IncludePackets.h";
	$str .= "\n#pragma once";

	foreach($packs as $pack)
	{
		$pos0 = strrpos($pack,"\\Packet_");
		$pos1 = strrpos($pack,".");
		$name = substr($pack,$pos0+8,$pos1-$pos0-8);
		$str .= "\n#include \"packs/Packet_".$name.".h\"";
	}

	
	$file = fopen ( $pathname, "w" );
	fwrite($file,$str);
	fclose($file);
	
}

function Out_C_PacketIds($pathname,$packs)
{
	echo "\n".$pathname;

	
	$str = "//PacketIds.h";
	$str .= "\n#pragma once";
	$str .= "\nnamespace PacketIds";
	$str .= "\n{";
	$str .= "\n\tenum PacketIds";
	$str .= "\n\t{";
	foreach($packs as $pack)
	{
		$pos0 = strrpos($pack,"\\Packet_");
		$pos1 = strrpos($pack,".");
		$name = substr($pack,$pos0+8,$pos1-$pos0-8);
		$str .= "\n\t\t".$name.",";	
	}
	$str .= "\n\t};";
	$str .= "\n}";
	
	$file = fopen ( $pathname, "w" );
	fwrite($file,$str);
	fclose($file);
	
}

function Out_C_Server_Regist($pathname,$packs)
{
	echo "\n".$pathname;

	
	$str = "// ServerRegist";
	$str .= "\n#include \"tIocpServer.h\"";
	$str .= "\n#include \"IncludePackets.h\"";
	$str .= "\nvoid ServerRegist(tIocpServer& server)";
	$str .= "\n{";
	
	foreach($packs as $pack)
	{
		$pos0 = strrpos($pack,"\\Packet_");
		$pos1 = strrpos($pack,".");
		$name = substr($pack,$pos0+8,$pos1-$pos0-8);
		if (0==strcmp(substr($name,0,2),"CS"))
		{
			$str .= "\n\tint OnPacket_".$name."(tConnection* con, tPacket* pack);";
			$str .= "\n\tserver.attachPacketFactoryAndHandler(new PacketFactory_".$name."(),OnPacket_".$name.");";	
		}
		else
		{
			$str .= "\n\tserver.attachPacketFactoryAndHandler(new PacketFactory_".$name."());";
		}
		
		//$str .= "\n\tint OnPacket_".$name."(tConnection* con, tPacket* pack)";	
	}
	$str .= "\n}";


	$file = fopen ( $pathname, "w" );
	fwrite($file,$str);
	fclose($file);
}

function Out_C_Client_Regist($pathname,$packs)
{
	echo "\nOut_C_Client_Regist\n";
	echo $pathname;

	$str = "";
	foreach($packs as $pack)
	{
		$pos0 = strrpos($pack,"\\Packet_");
		$pos1 = strrpos($pack,".");
		$name = substr($pack,$pos0+8,$pos1-$pos0-8);
		$str .= "\n".$name;	
	}
	
	$file = fopen ( $pathname, "w" );
	fwrite($file,$str);
	fclose($file);
}

function out_pack_c_cpp($packInfo)
{

	$pathname = $packInfo['pack_c_cpp'];
	if (DEBUG_OUT_FILE_NAME)
	{
		echo "\n".$pathname;	
	}

	
	$str = "//" . $packInfo['packname'] . ".cpp";
	$str .= "\n#include \"" . $packInfo['packname'] . ".h\"";
	$str .= "\n#include \"PacketUtils.h\"";
	$str .= "\n#include \"string.h\"";
	$str .= "\nusing namespace PacketUtils;";
	$str .= "\n\n";

	// read function
	$str .= "int " . $packInfo['packname'] . "::read(tStream& s)\n";
	$str .= "{\n";
	$str .= "\tint readednum = 0;\n";
	foreach ( $packInfo['props'] as $values ) {
		if ($values['type']=='string')
		{
			$str .= "\treadednum += readString(" . $values ["purename"] . ",s);\n";
		}
		else
		{
			$str .= "\tPACKREAD(" . $values ["purename"] . ");\n";
		}
		
	}
	$str .= "\treturn readednum;\n";
	$str .= "}\n";
	

	// write function
	$str .= "int " . $packInfo['packname'] . "::write(tStream& s)\n";
	$str .= "{\n";
	$str .= "\tint writednum = 0;\n";
	foreach ( $packInfo['props'] as $values ) {
		if ($values['type']=='string')
		{
			$str .= "\twritednum += writeString(" . $values ["purename"] . ",s);\n";
		}
		else
		{
			$str .= "\tPACKWRITE(" . $values ["purename"] . ");\n";
		}
		
	}
	$str .= "\treturn writednum;\n";
	$str .= "}\n";
	
	// length function
	$str .= "int  " . $packInfo['packname'] . "::length()\n";
	$str .= "{\n";
	$str .= "\treturn\n";
	foreach ( $packInfo['props'] as $values ) {
		if ($values['type']=='string')
		{
			$str .= "\t\t+ 2 + strlen(" . $values ["purename"] . ")\n";
		}
		else
		{
			$str .= "\t\t+ sizeof(" . $values ["purename"] . ")\n";
		}
	}
	
	$str .= "\t\t;\n";
	$str .= "}\n";
	
	$str .= "char *" . $packInfo['packname'] . "::desc()\n";
	$str .= "{\n";
	$str .= "\treturn \"" . $packInfo['packname'] . "\";\n";
	$str .= "}\n";
	
	$file = fopen ( $pathname, "w" );
	fwrite($file,$str);
	fclose($file);
	
}


function out_pack_c_h($packInfo)
{
	$pathname = $packInfo['pack_c_h'];
	if (DEBUG_OUT_FILE_NAME)
	{
		echo "\n".$pathname;	
	}	
	
	$str = '#pragma once
#include "tPacket.h"
#include "packetids.h"';
	$str .= "\nclass Packet_".$packInfo['name']." : public tPacket";
	$str .= "\n{";
	$str .= "\npublic:";
	foreach( $packInfo['props'] as $prop)
	{
		if ($prop['type']=='string')
		{
			$str .= "\n\tchar* ".$prop['name'].";".$prop['comment'];		
		}
		else if ($prop['isarray'])
		{
			$str .= "\n\t".$prop['type']." ".$prop['name']."[".$prop['array_content']."]".";".$prop['comment'];
		}
		else
		{
			$str .= "\n\t".$prop['type']." ".$prop['name'].";".$prop['comment'];
		}
		
	}
	$str .= "\n\tint read(tStream&);\n";
	$str .= "\tint write(tStream&);\n";
	$str .= "\tint length();\n";
	$str .= "\tint id() { return PacketIds::" . $packInfo['name'] . "; }\n";
	$str .= "\tchar *desc();\n";
	$str .= "};\n";
	$str .= "\n";
	$str .= "\n";
	$str .= "class PacketFactory_" . $packInfo['name'] . " : public tPacketFactory\n";
	$str .= "{\n";
	$str .= "\tint id() { return PacketIds::" . $packInfo['name'] . "; }\n";
	$str .= "\ttPacket *create() { return new Packet_" . $packInfo['name'] . "(); }\n";
	$str .= "};\n";
	
	$file = fopen ( $pathname, "w" );	
	fwrite($file,$str);
	fclose($file);
	
}
?>