<?php
namespace Anis\Database;

use Anis\Config;

class MSSQL implements ConnectionInterface
{
    public function connect()
    {
        try {
            $db = new \PDO(
                'sqlsrv:server=' . Config::get('mssql/host') . ';Database=' . Config::get('mssql/db'),
                Config::get('mssql/username'),
                Config::get('mssql/password')
            );
            $db->setAttribute(
                \PDO::ATTR_ERRMODE,
                \PDO::ERRMODE_EXCEPTION
            );
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
        return $db;
    }
}
