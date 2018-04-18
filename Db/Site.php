<?php
class Db_Site extends Db_DAO {
    protected $servers = array(
        'username' => DB_USERNAME,
        'password' => DB_PASSWORD,
        'master'   => array(
            'host'      => 'localhost',
            'database'  => 'test'
        ),
        'slave'   => array(
            'host'      => 'localhost',
            'database'  => 'test'
        ),
    );
}
