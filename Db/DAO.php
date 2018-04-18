<?php

/**
 * 封装数据库操作类
 *
 * LICENSE:
 * @author mbg
 * @version 1.0
 * @copyright  zol shop
 * @todo
 * 2012-07-02 add by mbg
 */
class Db_DAO extends ZOL_Abstract_Pdo {

    protected static $DB = null;      // 数据库连接
    protected static $deBug = false;     // 调试是否开启 默认关闭
    protected static $sqlStack = array();   // 调试sql存放数组
    private static $sql = '';        //  单条sql

    /**
     * 设置数据库链接
     * @param  String        $DBName         数据库链接名
     * return   Void
     */

    protected static function setDB($DBName = null) {
        if (!isset(self::$_instance[$DBName])) {
            self::$DB = self::$_instance[$DBName] = new $DBName();
        } else {
            self::$DB = self::$_instance[$DBName];
        }
    }

    /**
     * 获取数据库记录
     * @param   String       $tableName         表名
     * @param   Array        $col               获取字段数据
     * @param   String       $where             查询条件
     * @param   Array        $orderby           排序数据
     * @param   Integer      $start             开始位置
     * @param   Integer      $number            返回长度
     * @return  Object/Array
     */
    public static function getData($tableName, $col = array(), $where = '', $orderby = array(), $start = 0, $number = 0) {
        $data = null;
        if ($tableName) {
            $colStr = empty($col) ? '*' : self::arrayToSelectSql($col);     // 格式化字段数据
            $DBName = get_called_class();
            self::setDB($DBName);
            self::$sql = "SELECT " . $colStr . " FROM " . $tableName;
            if ('' != $where) {
                  self::$sql .= " WHERE " . $where;
            }

            // 格式化排序数据
            if ('' != $orderby) {
                $orderbyStr = self::arrayToOrderSql($orderby);
                if ($orderbyStr) {
                    self::$sql .= " ORDER BY " . $orderbyStr;
                }
            }
            if ($number > 0) {
                self::$sql .= " LIMIT " . $start . "," . $number;
            }

            //调试模试
            if (true == self::$deBug) {
                self::$sqlStack[] = self::$sql;
                return true;
            }
            if (1 == $number) {
                $data = self::$DB->getRow(self::$sql);
            } else {
                $data = self::$DB->getAll(self::$sql);
            }
        }
        self::$sql;
        return $data;
    }
    /**
     * 获取连表分店订单列表 add lisq 2016-07-13
     * @param   String       $tableName         表名
     * @param   Array        $col               获取字段数据
     * @param   String       $where             查询条件
     * @param   Array        $orderby           排序数据
     * @param   Integer      $start             开始位置
     * @param   Integer      $number            返回长度
     * @return  Object/Array
     */
    public static function getJoinData($tableName, $tableName1, $tableName2,$col = array(), $where = '', $orderby = array(), $start = 0, $number = 0, $isNewList = 0) {
        $data = null;
        if ($tableName) {

            $colStr = empty($col) ? '*' : self::arrayToSelectSql($col);     // 格式化字段数据
            $DBName = get_called_class();
            self::setDB($DBName);
            if($isNewList){
                self::$sql = "SELECT " . $colStr . ", b.order_from FROM " . $tableName ." as a LEFT JOIN ". $tableName1 . " as b ON a.order_id=b.order_id ";
            }else{
                self::$sql = "SELECT " . $colStr . ", b.order_from FROM " . $tableName ." as a LEFT JOIN ". $tableName1 . " as b ON a.order_id=b.order_id LEFT JOIN ". $tableName2 ." as c ON b.order_id=c.order_id ";
            }

            if ('' != $where) {
                self::$sql .= " WHERE " . $where;
            }

            // 格式化排序数据
            if ('' != $orderby) {
                $orderbyStr = self::arrayToOrderSql($orderby);

                if ($orderbyStr) {
                    self::$sql .= " ORDER BY " . $orderbyStr;
                }
            }
            if ($number > 0) {
                self::$sql .= " LIMIT " . $start . "," . $number;
            }

            //调试模试
            if (true == self::$deBug) {
                self::$sqlStack[] = self::$sql;
                return true;
            }
            if (1 == $number) {
                $data = self::$DB->getRow(self::$sql);
            } else {
                $data = self::$DB->getAll(self::$sql);
            }
        }
//        echo  self::$sql;
        self::$sql;
        return $data;
    }

     /**
     * 获取连表分店订单数量 add lisq 2016-07-13
     * @param   String       $tableName         表名
     * @param   Array        $col               获取字段数据
     * @param   String       $where             查询条件
     * @param   Array        $orderby           排序数据
     * @param   Integer      $start             开始位置
     * @param   Integer      $number            返回长度
     * @return  Object/Array
     */
    public static function getJoinNumber($tableName, $tableName1, $tableName2, $where = '') {
        $number = 0;
        if ($tableName) {
            $DBName = get_called_class();
            self::setDB($DBName);
            self::$sql = "SELECT COUNT(*) FROM " . $tableName ." as a LEFT JOIN ". $tableName1 . " as b ON a.order_id=b.order_id LEFT JOIN ". $tableName2 ." as c ON b.order_id=c.order_id ";
            if ('' != $where) {
                self::$sql .= " WHERE " . $where;
            }

            //调试模试
            if (true == self::$deBug) {
                self::$sqlStack[] = self::$sql;
                return true;
            }
            $number = self::$DB->getOne(self::$sql);
        }
        return $number;
    }
    /**
     * 获得一条记录的一个字段
     * @param   String       $tableName         表名
     * @param   String       $fileName          字段名
     * @param   String       $where             查询条件
     * @param   Array        $orderby           排序数据
     * @return  String
     */
    public static function getOneField($tableName, $fileName = '', $where = '', $orderby = array()) {
        $result = "";
        if ($tableName) {
            $DBName = get_called_class();
            self::setDB($DBName);
            if ($fileName) {
                self::$sql = "SELECT " . $fileName . " FROM " . $tableName;
                if ('' != $where) {
                    self::$sql .= " WHERE " . $where;
                }
                // 格式化排序数据
                $orderbyStr = self::arrayToOrderSql($orderby);
                if ($orderbyStr) {
                    self::$sql .= " ORDER BY " . $orderbyStr;
                }
                //调试模试
                if (true == self::$deBug) {
                    self::$sqlStack[] = self::$sql;
                    return true;
                }
                $result = self::$DB->getOne(self::$sql);
            }
        }
        return $result;
    }

    /**
     * 获取数据库记录
     * @param   String       $tableName         表名
     * @param   String       $where             查询条件
     * @return  Integer
     */
    public static function getCount($tableName, $where = '') {
        $number = 0;
        if ($tableName) {
            $DBName = get_called_class();
            self::setDB($DBName);
            self::$sql = "SELECT COUNT(*) FROM " . $tableName;
            if ('' != $where) {
                self::$sql .= " WHERE " . $where;
            }

            //调试模试
            if (true == self::$deBug) {
                self::$sqlStack[] = self::$sql;
                return true;
            }
            $number = self::$DB->getOne(self::$sql);
        }
        return $number;
    }

    /**
     * 检测是否存在
     * @param   String       $tableName         表名
     * @param   String       $where             查询条件
     * @return  Boolean
     */
    public static function checkIt($tableName, $where = '') {

        $flag = false;
        if ($tableName) {
            $DBName = get_called_class();
            self::setDB($DBName);
            self::$sql = "SELECT 'X' FROM " . $tableName;

            if ('' != $where) {
                self::$sql .= " WHERE " . $where;
            }
            self::$sql .= " Limit 1";

            if (true == self::$deBug) {
                self::$sqlStack[] = self::$sql;

                return true;
            }
            if (self::$DB->getOne(self::$sql)) {
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * 插入数据(用于有唯一索引的情况)
     * @param   String       $tableName         表名
     * @param   Array        $col               插入字段数据
     * @param   Boolean      $isInsertId        是否返回最后插入记录id 默认false
     * @return  Boolean/Integer
     */
    public static function insertIgnoreData($tableName, $col = array(), $isInsertId = false) {
        $flag = false;
        if ($tableName) {
            // 格式化插入数据
            $insertStr = self::arrayToInsertSql($col);
            if ($insertStr) {
                $DBName = get_called_class();
                self::setDB($DBName);
                self::$sql = "INSERT ignore INTO " . $tableName . " " . $insertStr;
                if (true == self::$deBug) {
                    self::$sqlStack[] = self::$sql;
                    return true;
                }
                $flag = self::$DB->query(self::$sql);
                if ($isInsertId) {
                    $flag = self::$DB->lastInsertId();
                }
            } else {
                if (true == self::$deBug) {
                    echo "col is empty";
                }
            }
        }
        return $flag;
    }

    /**
     * 插入数据
     * @param   String       $tableName         表名
     * @param   Array        $col               插入字段数据
     * @param   Boolean      $isInsertId        是否返回最后插入记录id 默认false
     * @return  Boolean/Integer
     */
    public static function insertData($tableName, $col = array(), $isInsertId = false) {
        $flag = false;
        if ($tableName) {
            // 格式化插入数据
            $insertStr = self::arrayToInsertSql($col);
            if ($insertStr) {
                $DBName = get_called_class();
                self::setDB($DBName);
                self::$sql = "INSERT INTO " . $tableName . " " . $insertStr;
                if (true == self::$deBug) {
                    self::$sqlStack[] = self::$sql;
                    return true;
                }
                $flag = self::$DB->query(self::$sql);
                if ($isInsertId) {
                    $flag = self::$DB->lastInsertId();
                }
            } else {
                if (true == self::$deBug) {
                    echo "col is empty";
                }
            }
        }
        return $flag;
    }

    /**
     * 更新数据
     * @param   String       $tableName         表名
     * @param   Array        $col               更新字段数据
     * @param   String       $where             查询条件
     * @param   Integer      $limit             更新条数 默认更新一条 null 更新所有符合条件数据
     * @return  Boolean/Integer
     */
    public static function updateData($tableName, $col = array(), $where = '', $limit = 1) {
        $flag = false;
        if (!empty($tableName)) {
            if (('' == $where) && (true == self::$deBug)) {
                echo "update WHERE in not null";
            }

            // 格式化更新数据
            $updateStr = self::arrayToUpdateSql($col);
            if ($updateStr) {
                $DBName = get_called_class();

                self::setDB($DBName);
                self::$sql = "UPDATE " . $tableName . " SET " . $updateStr . " WHERE  " . $where;
                if ($limit) {
                    self::$sql .= " limit " . $limit;
                }
                if (true == self::$deBug) {
                    self::$sqlStack[] = self::$sql;
                    return true;
                }
                self::$sql;
                $flag = self::$DB->query(self::$sql);
            } else {
                if (true == self::$deBug) {
                    echo "updateSql in not null";
                }
            }
        }
        return $flag;
    }

    /**
     * 更新数据 replace into形式
     *
     * @param       string      $tableName      表名
     * @param       array       $col            更新字段名称
     * @access      puclic
     * @return      boolean
     */
    public static function replaceData($tableName, $col) {
        $flag = true;
        if ($tableName) {
            // 格式化插入数据
            $replaceStr = self::arrayToInsertSql($col);
            if ($replaceStr) {
                $DBName = get_called_class();
                self::setDB($DBName);
                self::$sql = "REPLACE INTO " . $tableName . " " . $replaceStr;
                if (true == self::$deBug) {
                    self::$sqlStack[] = self::$sql;
                    return true;
                }
                $flag = self::$DB->query(self::$sql);
            }
        }
        return $flag;
    }

    /**
     * 删除数据
     * @param   String       $tableName         表名
     * @param   String       $where             查询条件
     * @param   Integer      $limit             删除条数 默认更新一条 null 删除符合条件全部数据
     * @return  Boolean/Integer
     */
    public static function deleteData($tableName, $where = '', $limit = 1) {
        $flag = false;
        if (('' == $where) && (true == self::$deBug)) {
            echo "DELETE WHERE in not null";
        }
        if ($tableName) {
            $DBName = get_called_class();
            self::setDB($DBName);
            self::$sql = "DELETE FROM " . $tableName . " WHERE " . $where;
            if ($limit) {
                self::$sql .= " limit " . $limit;
            }
            if (true == self::$deBug) {
                self::$sqlStack[] = self::$sql;
                return true;
            }
            $flag = self::$DB->query(self::$sql);
        }
        return $flag;
    }

    /**
     * 格式化查询数据
     * @param   Array       $col                格式化数据数组
     * @return  String
     */
    public static function arrayToSelectSql($col = array()) {
        $selectStr = "";
        if (is_array($col) && !empty($col)) {
            $selectStr = implode(",", $col);
        }
        return $selectStr;
    }

    /**
     * 格式化排序
     * @param   Array       $col                格式化数据数组
     * @return  String
     */
    public static function arrayToOrderSql($col = array()) {
        $sqlStr = "";
        if (is_array($col) && !empty($col)) {
            foreach ($col as $key => $item) {
                $sqlStr .= $key . " " . $item . ",";
            }
            $sqlStr = substr($sqlStr, 0, strlen($sqlStr) - 1) . " ";
        }
        return $sqlStr;
    }

    /**
     * 格式化插入数据
     * @param   Array       $col                格式化数据数组
     * @return  String
     */
    public static function arrayToInsertSql($col = array()) {
        $sqlStr = "";
        //$col = self::delSign($col);
        if (is_array($col) && !empty($col)) {
            $keyStr = implode(",", array_keys($col));
            $valueStr = "'" . implode("','", $col) . "'";
            $sqlStr = "($keyStr) VALUES ({$valueStr})";
        }
        return $sqlStr;
    }

    /**
     * 格式化更新数据
     * @param   Array       $col                格式化数据数组
     * @return  String
     */
    public static function arrayToUpdateSql($col = array()) {
        $sqlStr = "";
        //$col = self::delSign($col);
        if (is_array($col) && !empty($col)) {
            foreach ($col as $key => $val) {
                $sqlStr .= $key . "= '" . $val . "',";
            }
        }
        $sqlStr = substr($sqlStr, 0, -1);
        return $sqlStr;
    }

    /**
     * 调试是否开启
     * @param   Boolean       $deBug                调试开始开关
     * @return  Void
     */
    public static function setDebug($deBug = false) {
        self::$deBug = $deBug;
    }

    /**
     * 显示调试结果
     * @return  Void
     */
    public static function dumpSql() {
        $flag = false;
        if (false == self::$deBug) {
            echo "SYS：[未打开调试模式，该功能不可用]";
        } else {
            if (is_array(self::$sqlStack) && !empty(self::$sqlStack)) {
                foreach (self::$sqlStack as $val) {
                    echo $val . '<br>';
                }
            }
        }
        return $flag;
    }

    /**
     * 过滤 单双引号
     * @param   $param array
     * @return  $param array
     */
    public static function delSign($param) {

        $data = array();
        if (!empty($param)) {
            foreach ($param as $key => $value) {
                $data[$key] = str_replace(array('"', "'"), array('', ''), $value);
            }
            return $data;
        } else {
            return $param;
        }
    }

    /* 获取Sql语句 */

    public static function getSql() {
        return self::$sql;
    }

    /* 直接用sql获取匹配的数据 */

    public static function getResutls($sql = '') {
        $data = array();
        if ($sql) {
            $DBName = get_called_class();
            self::setDB($DBName);
            self::$sql = $sql;
            //调试模试
            if (true == self::$deBug) {
                self::$sqlStack[] = self::$sql;
                return true;
            }
            $data = self::$DB->getAll(self::$sql);
        }
        return $data;
    }

    /* 直接用sql获取单条匹配的数据 */

    public static function getOneResult($sql = '') {
        $data = array();
        if ($sql) {
            $DBName = get_called_class();
            self::setDB($DBName);
            self::$sql = $sql;
            //调试模试
            if (true == self::$deBug) {
                self::$sqlStack[] = self::$sql;
                return true;
            }
            $data = self::$DB->getRow(self::$sql);
        }
        return $data;
    }

    /* 获取数据数量 */

    public static function getVar($sql = '') {
        $number = array();
        if ($sql) {
            $DBName = get_called_class();
            self::setDB($DBName);
            self::$sql = $sql;
            //调试模试
            if (true == self::$deBug) {
                self::$sqlStack[] = self::$sql;
                return true;
            }
            $number = self::$DB->getOne(self::$sql);
        }
        return $number;
    }

    /**
     * 执行一条sql语句 add by weng
     */
    public static function execSql($sql, $isInsertId = false) {
        $flag = false;
        self::$sql = $sql;
        $DBName = get_called_class();
        self::setDB($DBName);
        if (true == self::$deBug) {
            self::$sqlStack[] = self::$sql;
            return true;
        }
        $flag = self::$DB->query(self::$sql);
        if ($isInsertId) {
            $flag = self::$DB->lastInsertId();
        }
        return $flag;
    }

}
