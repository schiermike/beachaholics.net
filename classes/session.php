<?php
require_once "database.php";
require_once "user.php";

function getSession() {
	return $_SESSION['session'];
}
 
function getUser() {
	return getSession()->user;
}

function getDB() {
	return getSession()->db;
}

class Session {
	public $sessionId;
	public $user = NULL;
	public $db = NULL;
	
	private $clientResolution = NULL;
	
	public static function initialize() {	
		date_default_timezone_set('Europe/Vienna');
		setlocale(LC_TIME, 'de_AT.utf8');
		setlocale(LC_MONETARY, 'de_AT.utf8');
		
		srand(microtime()*1000000);

		ini_set('SMTP','beachaholics.net');
		ini_set('sendmail_from', 'no-reply@beachaholics.net');
		
		// start the session
		if (session_id() == "")
			session_start();
		
		// init session object
		if (!isset($_SESSION['session']))
			$_SESSION['session'] = new Session();

		getSession()->update();
	}
	
	public function __construct() {
		$this->db = new DB("127.0.0.1:3306", "beachaholics", "nöm3Fru4Fru66", "beachaholics");
		$this->sessionId = session_id();
	}
	
	public function getClientResolution() {
		return $this->clientResolution;
	}
	
	public function setClientResolution($w, $h) {
		$this->clientResolution = array($w, $h);
	}
	
	/**
	 * Try to login using credentials provided by the user.
	 * On success, the session data is initialized.
	 *
	 * @param userid
	 */
	public function login($userId) {
		$this->user = new User($userId);	
	}
	
	/**
	 * Logs out the current user
	 * @return unknown_type
	 */
	public function logout() {
		session_unset();
		session_destroy();	
	}
	
	/**
	 * Should be called every time a page is displayed (per user interaction)
	 * updates the timing information about the user currenty logged in or creates a new session when an autologin cookie is available.
	 */
	private function update() {
		if ($this->user == NULL)
			$this->user = new User();
		
		if (!getUser()->isGuest()) {
			$sql = "UPDATE user SET last_contact=NOW(), last_ip='".$_SERVER['REMOTE_ADDR']."' WHERE id=".getUser()->id;
			$request = getDB()->query($sql);
		}
	}
}
?>
