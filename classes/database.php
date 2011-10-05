<?php

require_once("log.php");

function esc($query, $addQuotes = true) {
	return getDB()->escape($query, $addQuotes);	
}

class DB {
	private $connection;
	private $host;
	private $username;
	private $password;
	private $database;
	
	function __construct($host, $username, $password, $database) {
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->connection = NULL;
	}
	
	public function removePassFromString($string) {
		return str_replace($this->password, "*******", $string);
	}

	public function escape($string, $addQuotes = true) {
		$this->getConnection(); // the subsequent call requires an open connection
		$string = mysql_real_escape_string($string);
		if ($addQuotes)
			$string = "'" . $string . "'";
		return $string;
	}
	
	/**
	 * Tries to get a valid DB Connection to the server defined in HOST, USER, PASS.
	 * @return the connectionID, otherwise dies with an error and logs the error.
	 */
	public function getConnection() {
		if ($this->connection != NULL && mysql_ping($this->connection))
			return $this->connection;
		
		$this->connection = mysql_connect($this->host, $this->username, $this->password);
			
		if ($this->connection == NULL)
			die("DB::getConnection: ".mysql_error());
		
		if (!mysql_select_db($this->database, $this->connection))
			die("DB::getConnection: ".mysql_error());
				
		// use utf-8 charset for queries
		mysql_query("SET names 'utf8'", $this->connection);

		return $this->connection;
	}
	
	/**
	 * Send a SQL query to the DB server, logs an error when problems occur
	 * @param the query to be executed
	 * @return the result
	 */
    public function query($query) {
    	$result = mysql_query($query, $this->getConnection());
    	if ($result === FALSE)
    		Log::error("DB::query: ".mysql_error().", query: $query");

    	return $result;
    }
}

?>
