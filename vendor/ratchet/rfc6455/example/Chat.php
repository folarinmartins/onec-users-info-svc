<?php  
use Ratchet\MessageComponentInterface;  
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {  
    public $clients;
    private $logs;
    private $connectedUsers;
    private $connectedUsersNames;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->logs = [];
        $this->connectedUsers = [];
        $this->connectedUsersNames = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        $conn->send(json_encode($this->logs));
        $this->connectedUsers [$conn->resourceId] = $conn;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Do we have a username for this user yet?
        if (isset($this->connectedUsersNames[$from->resourceId])) {
            // If we do, append to the chat logs their message
            $this->logs[] = array(
                "user" => $this->connectedUsersNames[$from->resourceId],
                "msg" => $msg,
                "timestamp" => time()
            );
            $this->sendMessage(end($this->logs));
        } else {
            // If we don't this message will be their username
            $this->connectedUsersNames[$from->resourceId] = $msg;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Detatch everything from everywhere
        $this->clients->detach($conn);
        unset($this->connectedUsersNames[$conn->resourceId]);
        unset($this->connectedUsers[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }

    private function sendMessage($message) {
        foreach ($this->connectedUsers as $user) {
            $user->send(json_encode($message));
        }
    }
}
