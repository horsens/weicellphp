<?php
class Db
{
	public  $tbname, $pdo, $conditions, $where_join_condition, $base_sql, $limit, $desc, 
			$tb_alias, $field_str, $join_type, $join_tbname, $join_condition;
	public  $temp_values=[];
	public  $join_temps = [];
	private static  $instance = NULL;

	private function __construct(Config $config)
	{
		$db_config = $config->getDbConfig();
		$dbms=$db_config['type'];     //数据库类型
		$host=$db_config['hostname']; //数据库主机名
		$dbName=$db_config['dbname'];    //使用的数据库
		$user=$db_config['username'];      //数据库连接用户名
		$pass=$db_config['password'];          //对应的密码
		$dsn="$dbms:host=$host;dbname=$dbName";


		try {
		  //echo $dsn;
		    $pdo = new PDO($dsn, $user, $pass); //初始化一个PDO对象
		    $this->pdo = $pdo;
		    return true;
		   
		} catch (PDOException $e) {
           	//return false;
           	
		    throw new Exception ("数据库连接失败:" . $e->getMessage() . "<br/>");
		}
	}

	private function __clone()
	{

	}

	function __destruct()
	{
		//echo 'clear';
		$this->pdo = NULL;

	}
	public static function getInstance($config)
	{
		if(self::$instance == NULL){
			self::$instance = new self($config);
			return self::$instance;
		}
		return self::$instance;

	}
	public function connect()
	{
		$dbms=Config::DB['type'];     //数据库类型
		$host=Config::DB['hostname']; //数据库主机名
		$dbName=Config::DB['dbname'];    //使用的数据库
		$user=Config::DB['username'];      //数据库连接用户名
		$pass=Config::DB['password'];          //对应的密码
		$dsn="$dbms:host=$host;dbname=$dbName";


		try {
		  //echo $dsn;
		    $pdo = new PDO($dsn, $user, $pass); //初始化一个PDO对象
		    $this->pdo = $pdo;
		    return true;
		   
		} catch (PDOException $e) {
           	return false;
		    //die ("数据库连接失败:" . $e->getMessage() . "<br/>");
		}
	}
	public function close()
	{
		//echo 'clear db';
		$this->pdo = null;
	}
	/**
	 *执行一条sql语句
	 *@sql string sql语句
	 */
	public static function query($sql)
	{
		
	}
	/**
	 *执行插入操作
	 *@data Array('key' => 'value')
	 */
	public  function insert($data)
	{
		#插入一条数据，并返回最后插入的数据ID
		$sql = 'insert into ' . $this->tbname . ' set ';
		foreach ($data as $key => $value) {
			$sql .= $key . '=:' . $key . ','; 	
		}
		$sql = substr_replace($sql, '', -1);
		//echo $sql;
		$stmt = $this->pdo->prepare($sql);
		//var_dump($stmt);
		foreach ($data as $key => $value) {
			$stmt->bindValue(':'.$key, $value);
		}
		$stmt->execute();
		
		return $this->pdo->lastInsertId();
	}
	/**
	 *执行删除操作
	 *@data Array('key' => 'value')
	 */
	public function delete()
	{
		if($this->conditions){
			$sql = 'delete from ' . $this->tbname . $this->conditions;
			$stmt = $this->pdo->prepare($sql);
			foreach ($this->temp_values as $key => $value) {
				$stmt->bindValue(':'.$key, $value);
			}
			$stmt->execute();
			$rowCount = $stmt->rowCount();
			return $rowCount;
		}
	
		return false;
		
	}
	/**
	 *执行修改操作
	 *@data Array('key' => 'value')
	 */
	public  function update($data)
	{
		$sql = 'update '. $this->tbname . ' set ';
		foreach ($data as $key => $value) {
			$sql .= $key . '=:' . $key . ',';
			$this->temp_values[$key] = $value;
		}
		$sql = substr_replace($sql, '', -1);
		if($this->conditions){
			$sql .= ' ' . $this->conditions;
		}
		
		$stmt = $this->pdo->prepare($sql);
		//var_dump($stmt);
		foreach ($this->temp_values as $key => $value) {
			$stmt->bindValue(':'.$key, $value);
		}
		$stmt->execute();
		$rowCount = $stmt->rowCount();
		
		return $rowCount;
	}
	/**
	 *设定操作的数据表
	 *@tbname 数据表名
	 */
	public function table($tbname)
	{
		//$this->connect();
		$this->tbname = $tbname;
		return  $this;
	}
	/**
	 *设定查询条件
	 *@key 主键
	 *@symbol 运输符号
	 *@value 值
	 */
	public  function where($key, $symbol, $value)
	{
		if($this->conditions){
			$this->conditions .= ' and '. $key . $symbol . ':'.$key;
			$this->temp_values[$key] = $value;
			return $this;
		}
		$this->conditions = ' where ' . $key . $symbol . ':'.$key;
		$this->temp_values[$key] = $value;
		return $this;
	}
	/**
	 *设定表连接时的查询条件
	 *@key 主键
	 *@symbol 运输符号
	 *@value 值
	 */
	public  function whereJoin($key, $symbol, $value)
	{
		$this->where_join_condition = ' where ' . $key . $symbol . $value;
		return $this;
	}
	/**
	 *设定查询条件OR
	 *@key 主键
	 *@symbol 运输符号
	 *@value 值
	 */
	public  function whereOr($key, $symbol, $value)
	{
		if($this->conditions){
			$this->conditions = ' where ' . $key . $symbol . ':'.$key;
			$this->temp_values[$key] = $value;
			return $this;
		}
		return false;
	}
	/**
	 *限制查询的数量
	 *@count Number
	 */
	public  function limit($count)
	{
		try{
			if(!$count or !is_numeric($count)){
				throw new Exception('limit的参数必须是数字');
			}else{
				$this->limit = ' limit '. $count;
				return $this;
			}
			
		}catch(Exception $e){
			echo $e->getMessage();
		}
		
	}
	/**
	 *设定查询字段
	 *@field_str 查询字段
	 */
	public  function field($field_str)
	{
		$this->field_str = $field_str;
		return $this;
	}
	/**
	 *设定查询表的别名
	 *@join_tbname 连接表的名称及别名
	 *@join_condition 连接条件
	 *@join_type 连接类型
	 */
	public  function join($join_tbname, $join_condition, $join_type = 'INNER JOIN')
	{
		$join_info = [
			'join_tbname' => $join_tbname,
			'join_condition' => $join_condition,
			'join_type' => $join_type
		];
		array_push($this->join_temps, $join_info);
		return $this;
	}
	/**
	 *设定查询表的别名
	 *@tb_alias 别名
	 */
	public  function alias($tb_alias)
	{
		$this->tb_alias = $tb_alias;
		return $this;
	}
	/**
	 *设定排序条件
	 *@key 排序关键字段
	 *@is_disc 是否是降序
	 */
	public  function desc($key, $is_disc=true)
	{
		$this->desc = ' order by ' . $key;
		$this->desc .= $is_disc ? ' desc' : ' asc';
		return $this;
	}
	/**
	 *查询所有符合条件的记录
	 */
	public  function findAll()
	{
		if(!$this->tbname){
			return false;
		}
		$field_str = $this->field_str ? $this->field_str : '*';
		
		$sql = 'select ' . $field_str . ' from ' . $this->tbname . ' ';
		$sql .= $this->tb_alias . ' ';
		if(count($this->join_temps) > 0){
			//连接表查询
			foreach ($this->join_temps as $join_info) {
				$sql .= ' ' . $join_info['join_type'] . ' ' . $join_info['join_tbname'] . ' ON ' . $join_info['join_condition'];
			}
			
		}
		
		$sql .=$this->conditions . $this->where_join_condition . $this->desc . $this->limit;
		
		$stmt = $this->pdo->prepare($sql);
		//var_dump($stmt);
		if(count($this->temp_values) > 0){
			foreach ($this->temp_values as $key => $value) {
				$stmt->bindValue(':'.$key, $value);
			}
		}
		$stmt->execute();
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;
	}
	/**
	 *查询所有符合条件的第一条记录
	 */
	public  function find()
	{
		if(!$this->tbname){
			return false;
		}
		$field_str = $this->field_str ? $this->field_str : '*';
		$sql = 'select ' . $field_str . ' from ' . $this->tbname . ' ';
		$sql .= $this->tb_alias . ' ';
		if(count($this->join_temps) > 0){
			//连接表查询
			foreach ($this->join_temps as $join_info) {
				$sql .= ' ' . $join_info['join_type'] . ' ' . $join_info['join_tbname'] . ' ON ' . $join_info['join_condition'];
			}
			
		}
		
		$sql .=$this->conditions . $this->where_join_condition . $this->desc . $this->limit;
		$stmt = $this->pdo->prepare($sql);
		//var_dump($stmt);
		if(count($this->temp_values) > 0){
			foreach ($this->temp_values as $key => $value) {
				$stmt->bindValue(':'.$key, $value);
			}
		}
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		return $res;

	}

}
