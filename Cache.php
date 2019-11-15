<?php
	require_once('./lib/predis/autoload.php');
	require_once('./app/config/Config.php');
	class Cache {
		const FILE_PATH = './' . CACHE_PATH . '/';

		public static function set($key, $value, $exp = 0) 
		{
			if(empty($value)) Cache::throwError('请设置缓存的值');
			$v = [ 'value' => $value, 'create_time' => time(), 'exp' => $exp];
			$v = json_encode($v);
			$res = file_put_contents(Cache::FILE_PATH.$key, $v);
			return $res;
		}

		public static function get($key)
		{
			$file_name = Cache::FILE_PATH . $key;
			if(!file_exists($file_name)) return false;
			$v = file_get_contents($file_name);
			$v = json_decode($v, true);
			$create_time = $v['create_time'];
			$get_time = time();
			if( ($get_time - $create_time) > $v['exp'] ){
				//缓存过期
				Cache::del($file_name);
				return false;
			}
			//更新缓存
			//$new_value = $v['value'];
			//Cache::set($key, $new_value, $v['exp']);
			return $v['value'];
		}

		public static function del($file_name)
		{
			//$file_name = Cache::FILE_PATH . $key;
			if(!file_exists($file_name)) return false;
			$res = unlink($file_name);
			return $res;

		}
		//使用redis缓存
		public static function redis()
		{
		      try{	
                       $client = new Predis\Client(
				Config::REDIS
			);
			return $client;
                        }
                      catch(PDOException $e){
                          return false;
                      }
		}

		public static function throwError($msg)
		{
			echo '<p style="color:red">发生错误:'. $msg .'</p>';
		}



	}







