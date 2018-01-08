<?php
namespace Anis\Database;

use PDO;
use Anis\Config;
use Anis\Database\ConnectionInterface;

class SQLite implements ConnectionInterface
{
    public function connect()
    {
        $db = new PDO('sqlite:' . Config::get('sqlite/db'));
        $db->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
        return $db;
    }
}
