<?php
	class Request
	{
		//返回请求参数Array
		public function params($param='')
		{
			$query_string =  $_SERVER['QUERY_STRING'];
			$params = [];
			$qs_arr = explode('&', $query_string);
			foreach ($qs_arr as $qs) {
				$a = explode('=', $qs);
				$params[$a[0]] = $a[1];
			}
			$json_data = json_decode(file_get_contents('php://input'), true);
			if(isset($json_data) and count($json_data) > 0) {
				$params = array_merge($params, $json_data);
			}
			if(!empty($param)){
				return $params[$param];
			}
			$params = $this->stripslashes_deep($params);
			$params = $this->trim_deep($params);
			return $params;
		}
		//返回token
		public function getToken()
		{
			return array_key_exists('HTTP_TOKEN', $_SERVER) ? $_SERVER['HTTP_TOKEN'] : false;
		}
		//判断请求变量是否存在及是否为空
		public function has($param)
		{
			if(array_key_exists($param, $this->params()) and !empty($this->params($param))){
				return true;
			}
			return false;
		}
		//去除转义反斜线
		private static function stripslashes_deep($value)
		{
			$value = is_array($value) ? array_map(__METHOD__, $value)
				: stripslashes($value);
			return $value;
		}
		//去除空格
		private static function trim_deep($value)
		{
			$value = is_array($value) ? array_map(__METHOD__, $value)
				: trim($value);
			return $value;
		}
		public function isGet()
		{
			return $_SERVER['REQUEST_METHOD'] == 'GET';
		}
		public function isPost()
		{
			return $_SERVER['REQUEST_METHOD'] == 'POST';
		}
		public function isPut()
		{
			return $_SERVER['REQUEST_METHOD'] == 'PUT';
		}
		public function isDelete()
		{
			return $_SERVER['REQUEST_METHOD'] == 'DELETE';
		}
	}
