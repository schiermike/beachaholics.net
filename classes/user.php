<?php
	require_once "database.php";

	class User
	{
		public static $ROLE_ADMIN        = 0x20;
		public static $ROLE_VORSTAND     = 0x10;
		public static $ROLE_INDOOR_MEN   = 0x04;
		public static $ROLE_INDOOR_WOMEN = 0x08;
		public static $ROLE_BEACHAHOLIC  = 0x02;
		public static $ROLE_MEMBER       = 0x01;
		public static $ROLE_NONE         = 0x00;  // jeder
		
		public static $PIC_WIDTH = 90;
		public static $PIC_HEIGHT = 136;
		
		public $id;
		public $lastName;
		public $firstName;
		public $nickName;
		public $md5Pass;
		public $roles;
		
		private $gbEntriesPerPage;
		
		public function getGbEntriesPerPage()
		{
			return $this->gbEntriesPerPage;
		}
		
		public function setGbEntriesPerPage($numEntries)
		{
			$this->gbEntriesPerPage = $numEntries;
			getDB()->query("UPDATE Spieler SET gbEntriesPerPage=$numEntries WHERE SpielerID=".$this->id);
		}
		 
		public static function getRoles()
		{
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
		public static function roleToString($role)
		{
			if($role == User::$ROLE_ADMIN) return "Administrator";
			if($role == User::$ROLE_VORSTAND) return "Vorstand";
			if($role == User::$ROLE_INDOOR_MEN) return "Hallenspieler";
			if($role == User::$ROLE_INDOOR_WOMEN) return "Hallenspielerin";
			if($role == User::$ROLE_BEACHAHOLIC) return "Beachaholic";
			if($role == User::$ROLE_MEMBER) return "Benutzer";
			return "Jeder";
		}
		
		public function __construct($id = NULL)
		{
			if($id == NULL)
				$id = User::getGuestId();
				
			$result = getDB()->query("SELECT Rights, Nachname, Vorname, Nick, MD5(Password) AS MD5Pass, gbEntriesPerPage FROM Spieler WHERE SpielerID=".$id);
			$row = mysql_fetch_assoc($result);
			$this->id = $id;
			$this->lastName = $row['Nachname'];
			$this->firstName = $row['Vorname'];
			$this->nickName = $row['Nick'];
			$this->md5Pass = $row['MD5Pass'];
			$this->roles = $row['Rights'];
			$this->gbEntriesPerPage = $row['gbEntriesPerPage'];
		}
		
		public static function getGuestId()
		{
			return 1000;
		}
		
		public function isMember()
		{
			return $this->isAuthorized(User::$ROLE_MEMBER);
		}
		
		public function isGuest()
		{
			return User::getGuestId() == $this->id;
		}
		
		public function getName()
		{
			return $this->lastName . " " . $this->firstName;
		}
		
		/**
		 * Main authorization check mechanisms
		 * @param $required
		 * @return true when rights of user are sufficient
		 */
		public function isAuthorized($required)
		{
			return User::authorized($required, $this->roles);
		}
		
		/**
		 * Main authorization check mechanisms
		 * @param $required
		 * @param $available
		 * @return true when rights of user are sufficient
		 */
		public static function authorized($required, $roles)
		{
			if($required == 0)
				return true;
				
			// ensure that we deal with integers and not strings
			$roles = (int)$roles;
				
			return ( $required & $roles ) > 0;
		}
		
		public function isVorstand()
		{
			return $this->isAuthorized(User::$ROLE_VORSTAND);
		}
		
		public function isItMe()
		{
			return $this->id == 1;
		}
		
		public function isAdmin()
		{
			return $this->isAuthorized(User::$ROLE_ADMIN);
		}
	}
?>
