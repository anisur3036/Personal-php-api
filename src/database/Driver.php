<?php
namespace Anis\Database;

class Driver
{
    public static function driver($driver)
    {
        switch ($driver) {
            case 'mysql':
                return new \Anis\Database\MySQL();
                break;
                
            case 'sqlite':
                return new \Anis\Database\SQLite();
                break;

            case 'sqlsrv':
                return new \Anis\Database\MSSQL();
                break;
        }
    }
}
