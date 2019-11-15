<?php
	class Controller
	{
		public function json($data, $code=200)
		{
			http_response_code($code);
			header('Content-type: application/json');
			if(empty($data['err'])){
				$data['code'] = '0';
				$data['msg'] = 'OK';
			}else{
				$data['data'] = '';
				$data['code'] = $data['err']['code'];
				$data['msg'] = $data['err']['msg'];
				unset($data['err']);
			}
			return json_encode($data, JSON_UNESCAPED_UNICODE);
		}
	}