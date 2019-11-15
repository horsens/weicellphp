<?php
	class Session
	{
		public function __construct()
		{

		}
		public static function set($sid, $value)
		{
			session_start();
			$_SESSION[$sid] = $value;
			session_write_close();
			return true;
		}
		public static function get($sid)
		{
			if(!isset($_SESSION[$sid])){
				return false;
			}
			return $_SESSION[$sid];
		}
		public static function del($sid)
		{
			if(!isset($_SESSION[$sid])){
				return false;
			}
			unset($_SESSION[$sid]);
			return true;
		}
	}

	



