<?php
class Database{
	/**
	 ** The databaseConnect() function is the core function for opening
	 ** up a connection to the mySQL db.
	 ** return $dbc
	 **/
	function connect(){
		try{
			#MySQL connection with PDO_MYSQL
			$dbc = new PDO("mysql:host=localhost;dbname=century", 'robin', '');
			$dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $dbc;
		}
		catch(PDOException $e){
			echo $e;
		}
	}
}
?>