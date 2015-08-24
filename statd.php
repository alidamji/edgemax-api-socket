<?php
/*
  sudo php-cgi -f <filename>

*/

while (true) {
  # Initiate a SESSION
  $options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => 'username=ADMIN&password=PASSWORD',
    ),
  );
  $context  = stream_context_create($options);
  $result = file_get_contents('https://IP-OF-EDGEROUTER/', false, $context);

  $cookies = array();
  foreach ($http_response_header as $hdr) {
      if (preg_match('/^Set-Cookie:\s*([^;]+)/', $hdr, $matches)) {
          parse_str($matches[1], $tmp);
          $cookies[]= $tmp["PHPSESSID"];
      }
  }

  # Initiate SOCKET Connection
  $sock = socket_create(AF_UNIX, SOCK_STREAM, 0);
  $sockaa = socket_connect($sock,'/tmp/ubnt.socket.statsd');
  echo $errno.$errstr;
  $msg='{"SUBSCRIBE":[{"name":"export"}],"UNSUBSCRIBE":[],"SESSION_ID":"'.$cookies[0].'"}';
  //$msg='{"SUBSCRIBE":[{"name":"interfaces"},{"name":"export"},{"name":"discover"},{"name":"system-stats"},{"name":"num-routes"},{"name":"config-change"},{"name":"users"}],"UNSUBSCRIBE":[],"SESSION_ID":"'.$cookies[0].'"}';
  echo strlen($msg).": $msg";
  socket_write($sock, strlen($msg)."\r\n".$msg);

  $len=socket_read($sock, 38192);
  $data=socket_read($sock, 38192);
  echo $len.":".$data;

  $options = array(
      'http' => array(
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
      'method'  => 'POST',
      'content' => 'stats='.$data,
      ),
  );
  $context  = stream_context_create($options);
  $result = file_get_contents('http://<IP OF YOUR SERVER>/<YOUR PHP SCRIPT TO RECEIVE DATA>', false, $context);
  echo $result;

  socket_close($sock);
  sleep(120);
}
?>
