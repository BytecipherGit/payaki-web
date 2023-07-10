<?php

namespace Classes;

use \PDO;

/**
 * Handle the interaction with the Database.
 *
 * @author Massimo Piedimonte
 */
class DB
{
	/**
	 * Connect to the database.
	 */
	private static function _connect()
	{
		// $pdo = new PDO('mysql:host=localhost;dbname=payaki;charset=UTF8', 'root', '');
		$pdo = new PDO('mysql:host=localhost;dbname=bytecipher_payaki;charset=UTF8', 'bytecipher_payaki', 'Gh7RhCZzZE');
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $pdo;
	}

	/**
	 * Query & Fetch
	 */
	public static function _query($query, $params = [])
	{
		$stmt = self::_connect()->prepare($query);
		$stmt->execute($params);

		if(explode(' ', $query)[0] === 'SELECT') {
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}
	}

	public static function recordExists($tableName, $conditions) {
		$query = "SELECT COUNT(*) FROM $tableName WHERE $conditions";
		$stmt = self::_connect()->prepare($query);
		$count = $stmt->fetchColumn();
		return $count > 0;
	  }
}