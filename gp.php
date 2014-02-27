<?php
include_once ($argv[1] . "/config.php");
include_once (dirname (__FILE__) . "/out_c.php");
include_once (dirname (__FILE__) . "/out_cs.php");


function rsubstr($str,$pos,$len)
{
	return substr($str,strlen($str)-1-$pos,$len);
}
function pathlink($str0,$str1)
{
	if (0!=strcmp(rsubstr($str0,0,1),'\\'))
	{
		$str0 .= '\\';
	}
	$str0 .= $str1;
	return $str0;
}
	class GenPack
	{
		public $config;
		public $packs;

		// Construct
		public function __construct($config) 
		{					
			$this->config = $config;
		}
		
		// Scans the *.pack in InDir
		public function scans_packs($pathname)
		{			
			$last_char = substr($pathname,strlen($pathname)-1,1);
			if (0!=strcmp("\\",$last_char))
			{
				$pathname .= "\\";			
			}
			$pathname .= "*";
			$packs = array();
			foreach( glob($pathname) as $filename )
			{
				if(!is_dir($filename))
				{
					array_push($packs,$filename);	
				}
			}
			return $packs;
		}
		
		public function work()
		{	
			// show config
			echo "\n\nConfig:\n";				
			print_r($this->config);		
			
			// scans packs
			echo "\n\n scanning packets"; 
			$this->packs = $this->scans_packs($this->config["InPath"]);			
			echo "\npacks:";
			print_r($this->packs);
			
			
			// out c
			if (array_key_exists("Out_C_IncludePackets",$this->config))
			{
				echo "\n\n out c include packets";
				Out_C_IncludePackets($this->config['Out_C_IncludePackets'],$this->packs);
			}
			
	
			// out c server
			if (array_key_exists("Out_C_Server_PacketIds",$this->config))
			{
				echo "\n\n out c packetids for server";
				Out_C_PacketIds($this->config['Out_C_Server_PacketIds'],$this->packs);
			}
			
			if (array_key_exists("Out_C_Server_PacketIds",$this->config))
			{	
				echo "\n\n out c server_regist for server  ";
				Out_C_Server_Regist($this->config['Out_C_Server_Regist'],$this->packs);
			}
						
			if (array_key_exists("Out_C_Client_Regist",$this->config))
			{	
				echo "\n\n out c regist for client";
				Out_C_Client_Regist($this->config['Out_C_Client_Regist'],$this->packs);
			}
			
			// out cs
			if (array_key_exists("Out_CS_Client_PacketIds",$this->config))
			{
				echo "\n\n out c sharp packetids";
				Out_CS_PacketIds($this->config['Out_CS_Client_PacketIds'],$this->packs,$this->config);
			}
			
			if (array_key_exists("Out_CS_Client_Regist",$this->config))
			{	
				echo "\n\n out cs regist for client";
				Out_CS_Client_Regist($this->config['Out_CS_Client_Regist'],$this->packs,$this->config);
			}
			
			
			
				
			// deal all packets
			echo "\n\n deal all packets";
			foreach($this->packs as $pack)
			{
				$this->dealOne($pack);				
			}
		}
		
	
		
		public function dealOne($packname)
		{
			$pos0 = strrpos($packname,"\\Packet_");
			$pos1 = strrpos($packname,".");			
			echo "\nparsing pack:".substr($packname,$pos0+8,$pos1-$pos0-8);
			
			$packInfo = $this->parsing_pack($packname);
			
			//echo "\n\npackinfo:\n";
			//print_r($packInfo);
			
			

			Out_C_Packet($packInfo);

			Out_CS_Packet($packInfo,$this->config);
		
			if ($packInfo["isServerPack"])
			{
				Out_C_Server_Handler($packInfo);
			}
			else if($packInfo["isClientPack"])
			{
				if (array_key_exists("Out_C_Client_Handler",$this->config))
				{
					Out_C_Client_Handler($packInfo);	
				}
				if (array_key_exists("Out_CS_Client_Handler",$this->config))
				{	
					Out_CS_Client_Handler($packInfo,$this->config);
				}
			}
			
		}
		
		public function parsing_pack($packname)
		{
			$status = "unknown status";
			
			$packInfo = array();
			$packInfo["pathfile"]= $packname;
			$pos0 = strrpos($packname,"\\Packet_");
			$pos1 = strrpos($packname,".");
			$packInfo["name"]= substr($packname,$pos0+8,$pos1-$pos0-8);
			$packInfo["direction"]= substr($packInfo["name"],0,2);
			$packInfo["purename"]= $packname;
			$packInfo["packname"]= "Packet_".$packInfo["name"];
			$packInfo["idname"]= "ID_".$packInfo["name"];
			$packInfo["factname"]= "PacketFactory_".$packInfo["name"];
			$packInfo["handname"]= "OnPacket_".$packInfo["name"];
			$packInfo["isServerPack"]= (0==strcmp($packInfo["direction"],"CS"));
			$packInfo["isClientPack"]= (0==strcmp($packInfo["direction"],"SC"));
			$packInfo["pack_c_h"] = pathlink($this->config["Out_C_Packets"],"Packet_".$packInfo["name"].".h");
			$packInfo["pack_c_cpp"] = pathlink($this->config["Out_C_Packets"],"Packet_".$packInfo["name"].".cpp");
			if ($packInfo["isServerPack"])
			{
				$packInfo["hand_c_cpp"] = pathlink($this->config["Out_C_Server_Handler"],"OnPacket_".$packInfo["name"].".cpp");
			}
			else
			{
				if (array_key_exists("Out_C_Client_Handler",$this->config))
				{
					$packInfo["hand_c_cpp"] = pathlink($this->config["Out_C_Client_Handler"],"OnPacket_".$packInfo["name"].".cpp");
				}
				
				if (array_key_exists("Out_CS_Client_Handler",$this->config))
				{
					$packInfo["hand_cs"] = pathlink($this->config["Out_CS_Client_Handler"],"OnPacket_".$packInfo["name"].".cs");
				}
			}
			$packInfo['pack_cs'] = pathlink($this->config["Out_CS_Packet"],"Packet_".$packInfo["name"].".cs");

			
			$file = fopen ( $packname, "r" );
			//print_r($file);

			$props = array ();
			$prop_index = 0;
			while ( ! feof ( $file ) ) {
				$lines = fgets ( $file );
				// remove the comment at the line end.
				$lines = trim ( $lines, "\n" );
				$pos = strrpos( $lines, "//");
				$comment = $pos?substr($lines,$pos,strlen($lines)-$pos):"";
				if ($pos) $lines = substr($lines,0,$pos);
				
				$lines = trim ( $lines, "\t" );
				$lines = trim ( $lines, "\n" );
				$lines = trim ( $lines, " " );
				$lines = preg_replace ( '/\s+/', ' ', $lines );
				$lines = trim ( $lines, "\n" );
				$lines = trim ( $lines, " " );
				$lines = trim ( $lines, ";" );
				
				//echo "\n".$lines."\n";
				if (false !== strpos ( $lines, "{" )) {
					//echo "enter property status\n";
					$status = "property status";
					continue;
				}
				
				if (false !== strpos ( $lines, "}" )) {
					//echo "leave property status\n";
					$status = "unknown status";
					continue;
				}
				
				if ("property status" == $status) {
					
					$pos = strrpos ( $lines, " " );
					
					if ($pos !== false) {
						//echo "pos:".$pos."\n";
						$type = substr ( $lines, 0, $pos );
						$isarray = strpos($type,"[");
						$isfixedarray = !strpos($type,"[]");						
						$array_content;
						if ($isfixedarray)
						{
							$tmppos = strpos($type,"]");
							$array_content = substr($type,$isarray+1,$tmppos-$isarray-1);
						}
						if ($isarray)
						{
							$type = substr($type,0,$isarray);
						}
						trim ( $type );
						$name = substr ( $lines, $pos + 1 );
						$_pos = strpos ( $name, "[" );
						
						$purename = ($_pos != false) ? substr ( $name, 0, $_pos ) : $name;
						//echo "type:".$type."   name:".$name. "   purename:".$purename."\n";
						

						$props [$prop_index] ["type"] = $type;
						$props [$prop_index] ["name"] = $name;
						$props [$prop_index] ["purename"] = $purename;
						$props [$prop_index] ["comment"] = $comment;
						
						$props [$prop_index] ["isarray"] = $isarray;
						$props [$prop_index] ["isfixedarray"] = $isfixedarray;
						$props [$prop_index] ["array_content"] = $array_content;

						$prop_index ++;
					}
				}
			}
			$packInfo['props'] = $props;
			fclose($file);
			return $packInfo;
		}
		


		
		

	}
	
	$gp = new GenPack($config);
	$gp->work();
	//echo "aaa";

?>