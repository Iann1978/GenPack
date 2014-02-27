<?php
define('PACKET_C_H',1);
define('PACKET_C_CPP',2);
define('HANDLER_SERVER_C',3);
define('HANDLER_CLIENT_C',3);
define('DEBUG_OUT_FILE_NAME',false);

$config = array(
	"InPath" => 'C:\proj\message_system_for_packetbaby\packets_src',//文件所在目录
	
	/* out c */
	//{
		"Out_C_Packets" => "C:\proj\message_system_for_packetbaby\server_for_packetbaby\packs",
		"Out_C_IncludePackets" => "C:\proj\message_system_for_packetbaby\server_for_packetbaby\gen\IncludePackets.h",
		
		/* out c client */
		//{
			//"Out_C_Client_PacketIds"
			//"Out_C_Client_Regist" => "C:\proj\message_system_for_packetbaby\packets_for_c\client_regist.cpp",
			//"Out_C_Client_Handler" => "C:\proj\message_system_for_packetbaby\packets_for_c",
		//} for out c client
		
		/* out c server */
		//{
			"Out_C_Server_Handler" => "C:\proj\message_system_for_packetbaby\server_for_packetbaby\hands",
			"Out_C_Server_PacketIds" => "C:\proj\message_system_for_packetbaby\server_for_packetbaby\packs\packetids.h",
			"Out_C_Server_Regist" => "C:\proj\message_system_for_packetbaby\server_for_packetbaby\gen\server_regist.cpp",			
		//} for out c server
		
	//} for out c

	/* out cs */
	"Out_CS_Packet" => "C:\proj\message_system_for_packetbaby\clientdemo_for_packetbaby\packs",
	
  /* out cs client */
  //{		
		"Out_CS_Client_PacketIds" => "C:\proj\message_system_for_packetbaby\clientdemo_for_packetbaby\packs\packetids.cs",
		"Out_CS_Client_Regist" => "C:\proj\message_system_for_packetbaby\clientdemo_for_packetbaby\RegistPacketAndHandler.cs",
		"Out_CS_Client_Handler" => "C:\proj\message_system_for_packetbaby\clientdemo_for_packetbaby\hands",
		"CS_Client_Handler_Packet" => "clientdemo_for_packetbaby",
	//}
	
	/* debug out */
	"Debug_OutFile_Name" => true,
	
	// out cs server
	);

?>