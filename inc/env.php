<?php
return [

    /**
     * set the default database driver
     */
    'driver' => 'mysql', //sqlsrv, sqlite

    /**
     * provide mysql related infomation
     */
    'mysql' => array(
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => 50588,
        'db' => 'newblog'
    ),


    /**
     * provide mssql related infomation
     */
    'mssql' => array(
        'host' => '',
        'username' => '',
        'password' => '',
        'db' => ''
    ),

    /**
     * just create database.sqlite file into project root
     */
    'sqlite' => array(
        'db' => __DIR__ . DIRECTORY_SEPARATOR . 'database.sqlite'
    ),



    'remember' => array(
        'cookie_name' => 'hash',
        'cookie_expiry' => 60  // 7 days
    ),
    'session' => array(
        'session_name' => 'user',
        'token_name' => 'token'
    )
];
