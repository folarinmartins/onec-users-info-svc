<?php  
#https://blog.samuel.ninja/the-tutorial-for-php-websockets-that-i-wish-had-existed/
require 'vendor/autoload.php';  
use Ratchet\MessageComponentInterface;  
use Ratchet\ConnectionInterface;

require 'chat.php';

// Run the server application through the WebSocket protocol on port 8080
$app = new Ratchet\App("localhost", 8080, '0.0.0.0', $loop);
$app->route('/chat', new Chat, array('*'));
#If you want to add another app on the same WebSocket you can simply add it to another route in the socket.php file like so.
#$app->route('/foo', new YourClassName, array('*'));`


$app->run();
