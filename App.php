<?php

	class App {
		protected $c, $a;
		public function run() 
		{
			$c = isset($_GET['c']) ? $_GET['c'] : 'Index';
			$a = isset($_GET['a']) ? $_GET['a'] : 'default';
			if(empty($_GET['c'])){
				$c = 'Index';
			} 
			if(empty($_GET['a'])){
				$a = 'default';
			}
			$c = ucfirst($c); 
			$a = strtolower($a);
			if(file_exists(__DIR__.'/../' . APP_PATH .'/controller/'.$c.'.php')){
				require_once(__DIR__.'/../' . APP_PATH .'/controller/'.$c.'.php');
			}
			if(class_exists($c) && method_exists($c, $a))
			{
				require_once('Request.php');
				require_once('Db.php');
				require_once('./app/config/Config.php');
				$o = new $c(new Request(), Db::getInstance(new Config()));
				$res = $o->$a();
				echo $res;
			}
			else
			{
				echo <<<EOF
			<div style="color:#4c4b4c;display:flex;justify-content:center;align-items:center;height:30rem">
				<h1>404,Controler or Action is not exists!</h1>
				<h5>@微格子科技weicellphp</h5>
			</div>
			
EOF;
			}
		}
	}