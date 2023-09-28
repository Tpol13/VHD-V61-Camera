<?php

require_once("config/autoload.php");

function isPortOpen($ip, $port) {
  $socket = socket_create(AF_INET, SOCK_STREAM, 0);
  $result = socket_connect($socket, $ip, $port);
  socket_close($socket);
  return $result;
}

# On supprime les WARNINGs !
error_reporting(E_ALL & ~E_WARNING);

if (isset($_SERVER["SERVER_ADDR"]))
{
	$ip	= $_SERVER["SERVER_ADDR"]?$_SERVER["SERVER_ADDR"]:"127.0.0.1";
}
else
{
	$ip	= "127.0.0.1";
}
if (isPortOpen($ip, 8082)) {
  echo "pong";
} else {
	#echo "close";
	http_response_code(501);
}

?>
