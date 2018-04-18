<?php
class Libs_Site {
    public static function getTable() {
        $tableName = 'site';

        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `username` varchar(30) NOT NULL,
            `password` varchar(255) NOT NULL,
            `add_time`  int(10) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment='测试数据表' ";
        $res = Db_Site::execSql($sql);
        if(!$res) {
            $tableName = '';
        }

        return $tableName;
    }

    public static function getData($col = array(), $where = '', $orderby = array(), $start = 0, $number = 0) {
        $tableName = self::getTable();
        $res = Db_Site::getData($tableName, $col, $where, $orderby, $start, $number);

        return $res;
    }
}
