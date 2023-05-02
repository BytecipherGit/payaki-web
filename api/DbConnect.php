<?php
class DbConnect
{
    private $server = 'localhost';
    private $dbname = 'payaki';
    private $user = 'root';
    private $pass = '';

    public function connect()
    {
        try {
            $connection = new PDO('mysql:host=' . $this->server . ';dbname=' . $this->dbname, $this->user, $this->pass);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $connection;
        } catch (Exception $e) {
            echo "Database Error:" . $e->getMessage();
        }

    }
}

$db = new DbConnect();
$db->connect();
