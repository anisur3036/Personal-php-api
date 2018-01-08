<?php
namespace Anis\Database;

use Anis\Config;
use Anis\Database\ConnectionInterface;

class MySQL implements ConnectionInterface
{
    public function connect()
    {
        try {
            $db = new \PDO(
                'mysql:host=' . Config::get('mysql/host') . ';dbname=' . Config::get('mysql/db'),
                Config::get('mysql/username'),
                Config::get('mysql/password')
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
