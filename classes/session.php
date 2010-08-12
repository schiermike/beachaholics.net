<?php
	require_once "database.php";
	require_once "user.php";
	
	function getSession()
    {
    	return $_SESSION['session'];
    }
    
	function getUser()
	{
		return getSession()->user;
	}
	
	function getDB()
    {
    	return getSession()->db;
    }

	class Session
	{
		private static $LOGIN_TIMEOUT = 604800; // 1 Woche
		
		public $user = NULL;
		public $db = NULL;
		public $debug = false;
		
		private $userPicRefreshs = array();
		
		public static function initialize()
		{	
			date_default_timezone_set('Europe/Vienna');
			setlocale(LC_TIME, 'de_AT.utf8');
			setlocale(LC_MONETARY, 'de_AT.utf8');
			
			srand(microtime()*1000000);
	
			ini_set('SMTP','beachaholics.net');
			ini_set('sendmail_from', 'no-reply@beachaholics.net');
			
			// start the session
			if(session_id() == "")
				session_start();
			
			// init session object
			if(!isset($_SESSION['session']))
    			$_SESSION['session'] = new Session();
		}
		
		public function __construct()
		{
			$this->db = new DB("localhost", "beachaholics", "nöm3Fru4Fru66", "beachaholics");
		}
		
		
		
	    /**
	     * Try to login using credentials provided by the user.
	     * On success, the session data is initialized.
	     *
	     * @param userid
	     * @param password
	     * @return true on success, else false
	     */
		public function login($userId, $pass)
		{
			if($userId < 0)
				return ;
				
			if($pass==NULL || $userId==NULL)
			{
				// is the user already logged in?
				return !$this->user->isGuest();
			}
			
			$hash = md5(uniqid(rand()));
			$sql = "UPDATE Spieler SET LoginHash='".$hash."' WHERE SpielerID=".$userId." AND (Password='".$pass."' OR MD5(Password)='".$pass."')";
			$request = getDB()->query($sql);
			if(mysql_affected_rows()!=1)
				return false;
			setcookie("autologin", $hash, time() + Session::$LOGIN_TIMEOUT);
			$this->user = new User($userId);
			return true;	
		}
		
		/**
		 * Logs out the user with the userid - deletes the loginHash from DB and unsets the session.
		 * @return unknown_type
		 */
		public function logout()
		{
			if(getUser()->isGuest())
				return false;
				
			$sql = "UPDATE Spieler SET LoginHash=NULL WHERE SpielerID=".getUser()->id;
			getDB()->query($sql);
			$this->user = new User();
			return true;
		}
		
		/**
		 * Should be called every time a page is displayed (per user interaction)
		 * updates the timing information about the user currenty logged in or creates a new session when an autologin cookie is available.
		 */
		public function update()
		{
			if($this->user == NULL)
				$this->user = new User();
			
			// attribute which every valid session must have
			if( isset($_COOKIE['autologin']) && getUser()->isGuest() )
			{	
				$result = getDB()->query("SELECT SpielerID FROM Spieler WHERE LoginHash='".$_COOKIE['autologin']."'");
				if(mysql_num_rows($result)==1)
				{
					$row = mysql_fetch_assoc($result);
					$this->user = new User($row['SpielerID']);
				}
			}
			
			if(!getUser()->isGuest())
			{
				$sql = "UPDATE Spieler SET LastTimeStamp=NOW(), LastUsedIP='".$_SERVER['REMOTE_ADDR']."' WHERE SpielerID=".getUser()->id;
				$request = getDB()->query($sql);
			}
			
			global $debug;
			switch($debug)
			{
				case 'off':
					$this->debug=false;
					break;
				case 'on':
					$this->debug=true;
					break;
				case 'clear':
					Log::clear();
					break;
				case 'delete':
					global $logId;
					Log::delete($logId);
					break;
			}
		}
		
		/**
		 * is called when a user picture gets changed to reload the browser's cache
		 * @param int $userId
		 */
		public function refreshUserPic($userId)
		{
			if(array_search($userId, $this->userPicRefreshs) !== FALSE)
				return;

			$this->userPicRefreshs[] = $userId;
		}
		
		/**
		 * tells the client whether to use a cached user pic or not
		 * @param int $userId
		 * @return boolean
		 */
		public function useCachedUserPic($userid)
		{
			$search = array_search($userid, $this->userPicRefreshs);
			if($search === FALSE)
				return true;
				
			// remove the entry
			unset($this->userPicRefreshs[$search]);
			return false;
		}
	}
?>