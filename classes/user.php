<?php
	require_once "database.php";

	class User {
		public static $ROLE_ADMIN        = 0x20;
		public static $ROLE_VORSTAND     = 0x10;
		public static $ROLE_INDOOR_MEN   = 0x04;
		public static $ROLE_INDOOR_WOMEN = 0x08;
		public static $ROLE_BEACHAHOLIC  = 0x02;
		public static $ROLE_MEMBER       = 0x01;
		public static $ROLE_NONE         = 0x00;  // jeder
		
		public static $GUEST_ID = 1000;
		
		public static $PIC_WIDTH = 90;
		public static $PIC_HEIGHT = 136;
		
		public $id;
		public $lastName;
		public $firstName;
		public $nickName;
		public $md5Pass;
		public $roles;
		
		private $gbEntriesPerPage;
		
		public function getGbEntriesPerPage() {
			return $this->gbEntriesPerPage;
		}
		
		public function setGbEntriesPerPage($numEntries) {
			$this->gbEntriesPerPage = $numEntries;
			getDB()->query("UPDATE user SET gb_entries_per_page=$numEntries WHERE id=".$this->id);
		}
		 
		public static function getRoles() {
			return array(
				User::$ROLE_ADMIN, 
				User::$ROLE_VORSTAND, 
				User::$ROLE_INDOOR_MEN, 
				User::$ROLE_INDOOR_WOMEN,
				User::$ROLE_BEACHAHOLIC,
				User::$ROLE_MEMBER,
				User::$ROLE_NONE
				);
		}
		
		/**
		 * @param $privilege
		 * @return a string representation of the numeric privilege
		 */
		public static function roleToString($role) {
			switch ($role) {
				case User::$ROLE_ADMIN:
					return "Administrator";
				case User::$ROLE_VORSTAND:
					return "Vorstand";
				case User::$ROLE_INDOOR_MEN:
					return "Hallenspieler";
				case User::$ROLE_INDOOR_WOMEN:
					return "Hallenspielerin";
				case User::$ROLE_BEACHAHOLIC:
					return "Beachaholic";
				case User::$ROLE_MEMBER:
					return "Benutzer";
				default:
					return "Jeder";
			}
		}
		
		public function __construct($id = NULL) {
			if($id == NULL)
				$id = User::$GUEST_ID;
				
			$result = getDB()->query("SELECT lastname, firstname, nickname, MD5(password) AS md5_password, roles, gb_entries_per_page FROM user WHERE id=".$id);
			$row = mysql_fetch_assoc($result);
			$this->id = $id;
			$this->lastName = $row['lastname'];
			$this->firstName = $row['firstname'];
			$this->nickName = $row['nickname'];
			$this->md5Pass = $row['md5_password'];
			$this->roles = $row['roles'];
			$this->gbEntriesPerPage = $row['gb_entries_per_page'];
		}
		
		public function isMember() {
			return $this->isAuthorized(User::$ROLE_MEMBER);
		}
		
		public function isGuest() {
			return User::$GUEST_ID == $this->id;
		}
		
		public function getName() {
			return $this->lastName . " " . $this->firstName;
		}
		
		/**
		 * Main authorization check mechanisms
		 * @param $required
		 * @return true when rights of user are sufficient
		 */
		public function isAuthorized($required) {
			return User::authorized($required, $this->roles);
		}
		
		/**
		 * Main authorization check mechanisms
		 * @param $required
		 * @param $available
		 * @return true when rights of user are sufficient
		 */
		public static function authorized($required, $roles) {
			if ($required == 0)
				return true;
				
			// ensure that we deal with integers and not strings
			$roles = (int)$roles;
				
			return ( $required & $roles ) > 0;
		}
		
		public function isVorstand() {
			return $this->isAuthorized(User::$ROLE_VORSTAND);
		}
		
		public function isItMe() {
			return $this->id == 1;
		}
		
		public function isAdmin() {
			return $this->isAuthorized(User::$ROLE_ADMIN);
		}
	}
?>
