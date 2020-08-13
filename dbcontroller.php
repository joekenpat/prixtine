<?php
class DBController {
	private $host = "localhost";
	private $user = "root";
	private $password = "mysql";
	private $database = "cleaner_db";
	private $conn;

	function __construct() {
		$this->conn = $this->connectDB();
	}

	function connectDB() {
		$conn = mysqli_connect($this->host,$this->user,$this->password,$this->database);
		return $conn;
	}

	function runQuery($query) {
		$result = mysqli_query($this->conn,$query);
		while($row=mysqli_fetch_assoc($result)) {
			$resultset[] = $row;
		}
		if(!empty($resultset))
			return $resultset;
	}

	function numRows($query) {
		$result  = mysqli_query($this->conn,$query);
		$rowcount = mysqli_num_rows($result);
		if (!$result) {
			die('numRows Error: ' . mysqli_error($this->conn));
		} else {
			return $rowcount;
		}
	}

	function updateQuery($query) {
		$result = mysqli_query($this->conn,$query);
		if (!$result) {
			die('Update Error: ' . mysqli_error($this->conn));
		} else {
			return $result;
		}
	}

	function insertQuery($query) {
		$result = mysqli_query($this->conn,$query);
		if (!$result) {
			die('Insert Error: ' . mysqli_error($this->conn));
		} else {
			return $result;
		}
	}

	function deleteQuery($query) {
		$result = mysqli_query($this->conn,$query);
		if (!$result) {
			die('Delete Error: ' . mysqli_error($this->conn));
		} else {
			return $result;
		}
	}
}
?>
