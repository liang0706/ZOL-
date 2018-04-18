<?php
/**
*
* @author wiki <wu.kun@zol.com.cn>
* @copyright (c) {date}
* @version v1.0
*
* @changelog by Wangfeifei 2016-06-27
* 1、修改 beginTransaction、  forceReadMaster，使支持事务(需数据库支持，如MySql InnoDb数据引擎)
* 2、添加getExcuteOne、getExcuteRow、getExcuteCol、getExcuteAll方法
*
*/

abstract class ZOL_Abstract_Pdo extends ZOL_Abstract_DBOlder
{
	/**
	* 当前数据库链接
	*
	* @var PDO
	*/
	protected $db;

	/**
	* 主数据库链接
	*
	* @var PDO
	*/
	protected $master;

	/**
	* 从数据库链接
	*
	* @var PDO
	*/
	protected $slave;

	/**
	* 是否强制主库
	*
	* @var boolean
	*/
	protected $forceReadMaster = false;

	/**
	* 数据库字符集
	*
	* @var string
	*/
	protected $charset = '';

	/**
	* 数据库用户名
	*
	* @var string
	*/
	protected $username = 'root';

	/**
	* 数据库密码
	*
	* @var string
	*/
	protected $password;

	/**
	* 数据库引擎
	*
	* @var string
	*/
	protected $engine = 'mysql';

	/**
	* SQL语句注释
	*
	* @var string
	*/
	protected $sqlComment = '';

	/**
	* 重链次数
	*
	* @var integer
	*/
	protected $reconnectNum = 0;

	/**
	* 是否PING
	*
	* @var mixed
	*/
	protected $ping = true;

	/**
	* 数据库单例
	*
	* @var ZOL_Abstract_Pdo
	*/
	protected static $_instance = array();

    /**
     * 获得注释
     */
	public function getSqlComment(){
        if(!$this->sqlComment){

            $typeStr = "WEB";
            $isCli = false;
            switch(ZOL_Request::resolveType()){
                case ZOL_Request::CLI:
                    $typeStr = "CLI";
                    $isCli   = true;
                    break;
                case ZOL_Request::AJAX:
                    $typeStr = "AJAX";
                    break;
                case ZOL_Request::BROWSER:
                default:
                    $typeStr = "WEB";
                    break;
            }

            $request = ZOL_Registry::get('request');
            $c       = $request->getControllerName();
            $a       = $request->getActionName();
            if($isCli){#如果是命令行执行
                $this->sqlComment = "/*".$_SERVER["HOSTNAME"]. ":{$typeStr}:" .APP_NAME.":c={$c}&a={$a}*/";
            }else{
                $this->sqlComment = "/*".$_SERVER["HTTP_HOST"]. ":{$typeStr}:" .APP_NAME.":c={$c}&a={$a}*/";
            }
        }
        return $this->sqlComment;
    }

	public function __construct()
	{
		$this->init();
	}

	private function init()
	{
        if (!empty($this->servers['engine'])) {
			$this->engine = $this->servers['engine'];
		}

		if (defined('DB_CHARSET')) {
			$this->charset = DB_CHARSET;
		}

		if (defined('DB_USERNAME')) {
			$this->username = DB_USERNAME;
		}

		if (defined('DB_PASSWORD')) {
			$this->password = DB_PASSWORD;
		}

		if (!empty($this->servers['charset'])) {
			$this->charset = $this->servers['charset'];
		}

		if (!empty($this->servers['username'])) {
			$this->username = $this->servers['username'];
		}

		if (!empty($this->servers['password'])) {
			$this->password = $this->servers['password'];
		}
	}

	public static function instance($dbName = null)
	{
		$dbName = $dbName ? $dbName : get_called_class();

		if (empty($dbName)) {
			return false;
		}

		if (substr($dbName, 0, 2) != 'Db') {
			return false;
		}

		if (!isset(self::$_instance[$dbName])) {
			self::$_instance[$dbName] = new $dbName();
			#self::$_instance[$dbName]->query('SET SESSION WAIT_TIMEOUT=1');
		}
		return self::$_instance[$dbName];
	}

	/**
	* 强制从写库读取
	* @return ZOL_Abstract_Pdo
	*/
	public function forceReadMaster()
	{
		$this->forceReadMaster = true;
		return $this;
	}

	/**
	* 不允许PING
	* @return ZOL_Abstract_Pdo
	*/
	public function noPing()
	{
		$this->ping = false;
		return $this;
	}

	/**
	* 创建数据库链接
	*
	* @param enum $type {master|slave}
	* @return PDO
	*/
	protected function createDbConn($dbType = 'master')
	{
		if (empty($this->$dbType)) {
			$dns = $this->engine . ':dbname=' . $this->servers[$dbType]['database'] .
			';host=' . $this->servers[$dbType]['host'];
			try {
				$this->$dbType = new PDO($dns, $this->username, $this->password);
				if ($this->charset) {
					$this->$dbType->exec("SET NAMES '{$this->charset}'");
				}
			} catch (PDOException $e) {
				trigger_error($e->getMessage(), E_USER_WARNING);
				return false;
			}
		}
		$this->db =& $this->$dbType;
		return true;
	}

	protected function chooseDbConn($sql)
	{
		if (empty($sql)) {
			return false;
		}
		$sql = trim($sql);

		//检查SQL是否是select查询
		if ('SELECT' == strtoupper(strtok($sql, ' ')) && !$this->forceReadMaster) {
			if (!$this->createDbConn('slave')) {
				$this->createDbConn('master');
			}
		} else {
			$this->createDbConn('master');
		}

		if ($this->ping && !$this->ping() && $this->reconnectNum < 10) {
			unset($this->db, $this->master, $this->slave);
			$this->chooseDbConn($sql);
			$this->reconnectNum ++;
		}

		if (empty($this->db)) {
			if (empty($this->db)) {
				throw new ZOL_Exception('Dose not exist instance of DB server!');
			}
		} else {
			$this->reconnectNum = 0;
		}

		return true;
	}

	protected function ping()
	{
		error_reporting(0);
		try {
			if (!$this->db->query('SELECT 1')) {
				throw new PDOException('db server has gone away!');
			}
		} catch (PDOException $e) {
			return  false;
		}
		return true;
	}

	/**
	* 查询
	*
	* @param string $sql
	* @return PDOStatement
	*/
	public function query($sql = '')
	{
		$this->chooseDbConn($sql);
                $sqlCmm = $this->getSqlComment();
		$query = $this->db->query($sql . $sqlCmm);
		if (empty($query)) {
			$error = $this->errorInfo();
			trigger_error($error[2], E_USER_WARNING);
		}
		$this->ping = true;
		return $query;
	}

	/**
	* 获取一行中第一个字段值
	*
	* @param string $sql
	* @return PDOStatement
	*/
	public function getOne($sql)
	{
		$query = $this->query($sql);
		return ($query instanceof PDOStatement) ? $query->fetchColumn() : null;
	}

	/**
	* 获取一行
	*
	* @param string $sql
	* @param enum $fetchStyle
	* @return PDOStatement
	*/
	public function getRow($sql, $fetchStyle = PDO::FETCH_ASSOC)
	{
		$query = $this->query($sql);
		$row = ($query instanceof PDOStatement) ? $query->fetch($fetchStyle) : null;

		return $row;
	}

	/**
	* 获取全部
	*
	* @param string $sql
	* @param enum $fetchStyle
	* @return PDOStatement
	*/
	public function getAll($sql, $fetchStyle = PDO::FETCH_ASSOC)
	{
		$query = $this->query($sql);
		$result = ($query instanceof PDOStatement) ? $query->fetchAll($fetchStyle) : null;

		return $result;
	}

	public function beginTransaction()
	{

	    if(!$this->master){
	        $this->createDbConn('master');
	    }
		return ($this->master instanceof PDO) ? $this->master->beginTransaction() : false;
	}

	public function commit()
	{
		return ($this->master instanceof PDO) ? $this->master->commit() : false;
	}

	public function errorCode()
	{
		return ($this->db instanceof PDO) ? $this->db->errorCode() : false;
	}

	public function errorInfo()
	{
		return ($this->db instanceof PDO) ? $this->db->errorInfo() : false;
	}

	public function exec($statement = '')
	{
		$this->chooseDbConn($statement);
		$ret = ($this->db instanceof PDO) ? $this->db->exec($statement) : false;
		$this->forceReadMaster = false;

		return $ret;
	}

	public function lastInsertId()
	{
		return ($this->master instanceof PDO) ? $this->master->lastInsertId() : false;
	}

	public function prepare($statement = '', array $options = array())
	{
		$this->chooseDbConn($statement);
		$ret = ($this->db instanceof PDO) ? $this->db->prepare($statement, $options) : false;
		if (true == $this->forceReadMaster)
		{
			$this->forceReadMaster = false;
		}

		return $ret;
	}

	public function quote($string, $parameterType = PDO::PARAM_STR)
	{
		return ($this->db instanceof PDO) ? $this->db->quote($string, $parameterType) : false;
	}

	public function rollBack()
	{
		return ($this->master instanceof PDO) ? $this->master->rollBack() : false;
	}

	public function setAttribute($attribute, $value)
	{
		return ($this->db instanceof PDO) ? $this->db->setAttribute($attribute, $value) : false;
	}

	public function getAvailableDrivers()
	{
		return ($this->db instanceof PDO) ? $this->db->getAvailableDrivers() : false;
	}

	public function getAttribute($attribute)
	{
		return ($this->db instanceof PDO) ? $this->db->getAttribute($attribute) : false;
	}

	/**
	 * 获取一行中第一个字段值(预处理获取数据使用)
	 *
	 * @param PDOStatement &$statement
	 * @return PDOStatement
	 */
	public function getExcuteOne(PDOStatement &$statement)
	{
	    $statement->execute();
	    return $statement->fetchColumn();
	}

	/**
	 * 获取一行(预处理获取数据使用)
	 *
	 * @param PDOStatement &$statement
	 * @param enum $fetchStyle
	 * @return PDOStatement
	 */
	public function getExcuteRow(PDOStatement &$statement, $fetchStyle = PDO::FETCH_ASSOC)
	{
	    $statement->execute();
	    return $statement->fetch($fetchStyle);
	}

	/**
	 * 获取一列(预处理获取数据使用)
	 * @param PDOStatement &$statement
	 * @param string|int $column 获取哪个字段，为数字则按下标提取，为字符则按字段名提取
	 */
	public function getExcuteCol(PDOStatement &$statement, $column = 0)
	{
	    $statement->execute();
	    $fetchStyle = is_numeric($column) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
	    $results = false;
	    while ($row = $statement->fetch($fetchStyle)) {
	        $results[] = $row[$column];
	    }
	    return $results;
	}

	/**
	 * 获取全部 (预处理获取数据使用)
	 *
	 * @param PDOStatement &$statement
	 * @param enum $fetchStyle
	 * @return PDOStatement
	 */
	public function getExcuteAll(PDOStatement &$statement, $fetchStyle = PDO::FETCH_ASSOC)
	{
	    $statement->execute();
	    if ($this->_returnTotal && stripos(trim($sql), 'SELECT') === 0) {
	        $sql = 'SELECT SQL_CALC_FOUND_ROWS ' . substr($sql, 7);
	        $this->_returnTotal = false;
	    }

	    return $statement->fetchAll($fetchStyle);
	}
}
