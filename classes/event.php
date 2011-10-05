<?php
class Event {
	public static $INDOOR_MEN = 0;
	public static $INDOOR_WOMEN = 3;
	public static $INDOOR_WOMEN2 = 7;
	public static $GAME_MEN = 1;
	public static $GAME_WOMEN = 4;
	public static $GAME_WOMEN2 = 8;
	public static $INDOOR_KIDS = 5;
	
	public static $BEACH = 9;
	public static $OTHER = 2;
	public static $EXTERN = 6;
	
	public static function getEvents() {
		return array(Event::$INDOOR_MEN, Event::$INDOOR_WOMEN, Event::$INDOOR_WOMEN2, Event::$INDOOR_KIDS, Event::$GAME_MEN, Event::$GAME_WOMEN, Event::$GAME_WOMEN2, Event::$BEACH, Event::$OTHER, Event::$EXTERN);
	}
	
	/**
	 * These settings shall never be changed because all subscriptions base on these settings
	 */
	public static function isSubscribeEvent($event) {
		switch ($event) {
			case Event::$INDOOR_MEN: return false;
			case Event::$INDOOR_WOMEN: return false;
			case Event::$INDOOR_WOMEN2: return false;
			case Event::$GAME_MEN: return false;
			case Event::$GAME_WOMEN: return false;
			case Event::$GAME_WOMEN2: return false;
			case Event::$BEACH: return true;
			case Event::$OTHER: return true;
			default: return true;
		}
	}
	
	public static function userCanModify($event) {
		if (getUser()->isAdmin())
			return true;
		switch ($event) {
			case Event::$BEACH:
				return getUser()->isAuthorized(User::$ROLE_BEACHAHOLIC);
#			case Event::$INDOOR_WOMEN:
#			case Event::$GAME_WOMEN:
#				return getUser()->isAuthorized(User::$ROLE_INDOOR_WOMEN);
#			case Event::$INDOOR_WOMEN2:
#			case Event::$GAME_WOMEN2:
#				return getUser()->isAuthorized(User::$ROLE_INDOOR_WOMEN2);
			default:
				return false;
		}
	}
	
	/**
	 * return all roles which are allowed to join the event
	 */
	public static function getAllowedRolesForEvent($event) {
		$roles = 0x00;
		foreach (User::getRoles() as $role)
			if (Event::userCanJoin($event, $role))
				$roles |= $role;
		return $roles;	
	}
	
	public static function userCanJoin($event, $roles = NULL) {
		if ($roles == NULL)
			$roles = getUser()->roles;
		
		switch ($event) {
			case Event::$BEACH:
				return User::authorized(User::$ROLE_BEACHAHOLIC, $roles);
			case Event::$OTHER:
				return !getUser()->isGuest();
			case Event::$INDOOR_MEN:
			case Event::$GAME_MEN:
				return User::authorized(User::$ROLE_INDOOR_MEN, $roles);
			case Event::$INDOOR_WOMEN:
			case Event::$GAME_WOMEN:
				return User::authorized(User::$ROLE_INDOOR_WOMEN, $roles);
			case Event::$INDOOR_WOMEN2:
			case Event::$GAME_WOMEN2:
				return User::authorized(User::$ROLE_INDOOR_WOMEN2, $roles);
			case Event::$EXTERN:
			default:
				return false;
		}
	}
	
	public static function getDeadline($event) {
		switch ($event) {
			case Event::$INDOOR_MEN:
			case Event::$INDOOR_WOMEN:
			case Event::$INDOOR_WOMEN2: 
				return 2*3600;
			case Event::$GAME_MEN:
			case Event::$GAME_WOMEN:
			case Event::$GAME_WOMEN2:
				return 24*3600;
			case Event::$OTHER:
				return 6*3600;
			case Event::$BEACH:
				return 30*60;
		}
		return 0;
	}
	
	public static function toString($event) {
		switch ($event) {
			case Event::$INDOOR_MEN: return "Herren Training";
			case Event::$GAME_MEN: return "Herren Meisterschaftsspiel";
			case Event::$INDOOR_WOMEN: return "Damen Training";
			case Event::$INDOOR_WOMEN2: return "Damen Amateure Training";
			case Event::$GAME_WOMEN: return "Damen Meisterschaftsspiel";
			case Event::$GAME_WOMEN2: return "Damen Amateure Meisterschaftsspiel";
			case Event::$INDOOR_KIDS: return "Kinder Training Halle";
			case Event::$OTHER: return "Sonstiges";
			case Event::$EXTERN: return "Externes";
			case Event::$BEACH: return "Beachen";
			
			default: return "Unbekannter EventTyp";
		}
	}
	
	public static function toClass($event) {
		switch ($event) {
			case Event::$INDOOR_MEN: return "indoor_men";
			case Event::$INDOOR_WOMEN: return "indoor_women";
			case Event::$INDOOR_WOMEN2: return "indoor_women2";
			case Event::$INDOOR_KIDS: return "indoor_kids";
			case Event::$GAME_MEN: return "game_men";
			case Event::$GAME_WOMEN: return "game_women";
			case Event::$GAME_WOMEN2: return "game_women2";
			case Event::$BEACH: return "beach";
			case Event::$OTHER: return "misc";
			case Event::$EXTERN: return "external";
		}
	}
	
	public static function isJoinable($event) {
		switch ($event) {
			case Event::$INDOOR_MEN: return true;
			case Event::$INDOOR_WOMEN: return true;
			case Event::$INDOOR_WOMEN2: return true;
			case Event::$INDOOR_KIDS: return false;
			case Event::$GAME_MEN: return true;
			case Event::$GAME_WOMEN: return true;
			case Event::$GAME_WOMEN2: return true;
			case Event::$BEACH: return true;
			case Event::$OTHER: return true;
			case Event::$EXTERN: return false;
			default: return false;
		}
	}
}
?>
