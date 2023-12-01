<?php 
	/**
	* Database Connection
	*/
	class DbConnect {
		private $server = 'localhost';
		private $dbname = 'bytecipher_payaki';
		private $user = 'bytecipher_payaki';
		private $pass = 'Gh7RhCZzZE';

		public function connect() {
			try {
				$conn = new PDO('mysql:host=' .$this->server .';dbname=' . $this->dbname, $this->user, $this->pass);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				return $conn;
			} catch (\Exception $e) {
				echo "Database Error: " . $e->getMessage();
			}
		}
	}
 ?>
