<?php

namespace Anis\Database;

use PDO;
use Anis\Config;
use Anis\Database\Query;
use Anis\Database\Driver;
use Anis\Database\ConnectionInterface;

abstract class Model
{
    protected $table;

    protected $stmt;

    protected $numOfRows;

    protected $page;

    protected $limit;

    protected $error = false;

    /**
     * The PDO instance.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Create a new QueryBuilder instance.
     *
     * @param PDO $pdo
     */
    

    public function __construct()
    {
        $this->pdo = $this->make(
            Driver::driver(Config::get('driver'))
        );
    }
    
    public function error()
    {
        return $this->error;
    }

    public function make(ConnectionInterface $database)
    {
        return $database->connect();
    }

    /**
     * get the latest result order by created_at field
     *         
     * @param  string $column 
     * @return $this|static
     */
    public static function latest($column = 'created_at')
    {
        return (new static)->orderBy($column);
    }

    /**
     * Get all result
     * 
     * @return array
     */
    public static function all()
    {
        return (new static)->selectAll();
    }

    /**
     * Get Order by query
     * 
     * @param  string $column 
     * @return $this
     */
    protected function orderBy($column = 'created_at')
    {
        $sql = "select * from {$this->getTable()} order by {$column} desc";

        return $this->query($sql);
    }

    /**
     * Select all records from a database table.
     *
     * @param string $table
     */
    public function selectAll()
    {
        $ins = new static;
        $stmt = $ins->pdo->prepare("select * from {$ins->getTable()}");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Anis\\' . $ins->getClassName());
    }

    /**
     * Database query field result exist or not
     * @param  array
     * @return bool
     */
    public function exists($data)
    {
        $field = array_keys($data)[0];

        return $this->where($field, '=', $data[$field])->count() ? true : false;
    }

    /**
     * Find result by field value pair
     * 
     * @param  string $field
     * @param  string $operator
     * @param  mixed $value
     * @return $this
     */
    protected function where($field, $operator, $value)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE {$field} {$operator} ?";
        $this->stmt = $this->pdo->prepare($sql);
        $this->stmt->execute([$value]);

        return $this;
    }

    public function count()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Insert a record into a table.
     *
     * @param  string $table
     * @param  array  $parameters
     */
    public function insert(array $params)
    {
        $sql = sprintf(
            'insert into %s (%s) values (%s)',
            $this->getTable(),
            implode(', ', array_keys($params)),
            ':' . implode(', :', array_keys($params))
        );
        try {
            $stmt = $this->pdo->prepare($sql);

            $stmt->execute($params);
        } catch (\PDOException $e) {
            //
        }
    }

    public function update($id, $fields)
    {
        $set = '';
        $x = 1;

        foreach ($fields as $name => $value) {
            $set .= "{$name} = :{$name}";
            if ($x < count($fields)) {
                $set .= ', ';
            }
            $x++;
        }

        $sql = "UPDATE {$this->getTable()} SET {$set} WHERE id = {$id}";
        
        try {
            $this->query($sql, $fields);
        } catch (\PDOException $e) {
            $e->getMessage();
        }
    }

    public function query($query, $params = [])
    {
        $instance = new static;
        $instance->stmt = $instance->pdo->prepare($query);
        $instance->execute($params);

        return $instance;
    }

    protected function makePaginate($query, $params = [])
    {
        $this->stmt = $this->pdo->prepare($query);

        $this->execute($params);

        return $this;
    }

    //Binds the prep statement
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    protected function execute( $params = [])
    {
        if (!empty($params)) {
            $this->stmt->execute($params);
        }
         $this->stmt->execute();
    }

    public function get()
    {
        return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Anis\\' . $this->getClassName());
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function first()
    {
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    protected function totalRecords($query)
    {
        $this->makePaginate($query);

        return $this->stmt->rowCount();
    }

    protected function paging($query, $limit, $extra = [])
    {
        $this->limit = $limit;
        $starting_position = 0;
        if (isset($_GET['pg'])) {
            $starting_position = ($_GET['pg'] - 1) * $this->limit;
        }
        if (! empty($extra)) {
            return $query . " $extra[0] LIMIT $starting_position, $this->limit";
        }

        return $query . " LIMIT $starting_position, $this->limit";
    }

    public function createLinks($links, $listClass)
    {
        $query = "SELECT * FROM {$this->getTable()}";
        $this->page = 1;
        if (isset($_GET['pg'])) {
            $this->page = $_GET['pg'];
        }
        if ($this->limit == 'all') {
            return '';
        }

        //$rowStart = ( ( $this->page - 1 ) * $this->limit );
        $total = $this->totalRecords($query);

        //if total record  is less than $this->limit or equal
        //then reutrn null;
        if ($total <= $this->limit) {
            return;
        }
        $last = ceil($total / $this->limit);

        //calculate start of range for link printing
        $start = (($this->page - $links) > 0) ? $this->page - $links : 1;

        //calculate end of range for link printing
        $end = (($this->page + $links) < $last) ? $this->page + $links : $last;

        $html = '<ul class="' . $listClass . '">';

        $class = ($this->page == 1) ? "disabled" : ""; 

        $previousPage = ($this->page == 1) ?
            '<a href=""><li class="' . $class . '">&laquo;</a></li>' : //remove link from previous button
            '<li class="' . $class . '"><a href="?limit=' . $this->limit . '&pg=' . ($this->page - 1) . '">&laquo;</a></li>';

        $html .= $previousPage;

        if ($start > 1) { //print ... before (previous <<< link)
            $html .= '<li><a href="?limit=' . $this->limit . '&pg=1">First</a></li>'; //print first page link
            $html .= '<li class="disabled"><span>...</span></li>'; //print 3 dots if not on first page
        }

        //print all the numbered page links
        for ($i = $start; $i <= $end; $i++) {
            $class = ($this->page == $i) ? "active" : ""; //highlight current page
            $html .= '<li class="' . $class . '"><a href="?limit=' . $this->limit . '&pg=' . $i . '">' . $i . '</a></li>';
        }

        if ($end < $last) { //print ... before next page (>>> link)
            $html .= '<li class="disabled"><span>...</span></li>'; //print 3 dots if not on last page
            $html .= '<li><a href="?limit=' . $this->limit . '&pg=' . $last . '">Last</a></li>'; //print last page link
        }

        $class = ($this->page == $last) ? "disabled" : ""; //disable (>>> next page link)

        //$this->_page + 1 = next page (>>> link)
        $nextPage = ($this->page == $last) ?
            '<li class="' . $class . '"><a href="">&raquo;</a></li>' : //remove link from next button
            '<li class="' . $class . '"><a href="?limit=' . $this->limit . '&pg=' . ($this->page + 1) . '">&raquo;</a></li>';
        $html .= $nextPage;
        $html .= '</ul>';

        return $html;
    }

    public function resultSet()
    {
        $this->execute();

        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the pagination
     * 
     * @param  int $limit 
     * @param  array  $extra extra query
     * @return array
     */
    public function paginate($limit, $extra = [])
    {
        $this->limit = $limit;
        $query = "SELECT * FROM {$this->getTable()}";
        $recordPerPage = (isset($_GET['limit'])) ? $_GET['limit'] : $this->limit; //movies per page
        $page = (isset($_GET['pg'])) ? $_GET['pg'] : 1; //starting page
        //$links = 1;
        $pagingQuery = $this->paging($query, $recordPerPage, $extra);
        $this->makePaginate($pagingQuery);

        return $this->get();
    }

    public function links($links, $listClass)
    {
        return $this->createLinks($links, $listClass);
    }

    /**
     * Get the database table name according to calling class's plural virsion
     * 
     * @return string
     */
    public function getTable()
    {
        if (! isset($this->table)) {
            return strtolower(str_replace('\\', '', class_basename($this))) . 's';
        }

        return $this->table;
    }
    
    /**
     * Get the calling class name
     * 
     * @return string 
     */
    public function getClassName()
    {
        return str_replace('\\', '', class_basename($this));
    }

    /**
     * Find result by cloumn name [magically]
     * 
     * @param  string $args [cloumn name]
     * @return $this
     */
    public function find($args)
    {
        $sql = "select * from {$this->getTable()} where {$args['field']} = ?";
        $this->stmt = $this->pdo->prepare($sql);
        $this->stmt->execute([$args['value']]);

        return $this;
    }

    /**
     * 
     * @param  array $where [where parameters]
     * @param  array|string $order order parameters
     * @param  int $limit sql limit
     * @return $this
     */
    public function selectWhere($where = null, $order = null, $limit = null ) 
    {
        $instance = new static;
        $sql = 'SELECT * FROM ' . $instance->getTable();

        if ( $where && $where = $instance->conditions( $where ) ) {
            $sql .= ' WHERE ' . $where->sql;
        }
        if ( $order ) {
            $sql .= ' ORDER BY ' . ( is_array( $order ) ? implode( ', ', $order ) : $order );
        }
        if ( $limit ) {
            $sql .= ' LIMIT ' . $limit;
        }

        return $instance->query( $sql, $where->params );
    }

    /**
     * Get the complex condition result
     * 
     * @param  array  $conditions
     * @return object
     */
    protected static function conditions( array $conditions ) 
    {
        $sql    = array();
        $params = array();
        $i      = 0;
        foreach ( $conditions as $condition => $param ) {
            if ( is_string( $condition ) ) {

                for ( $keys = array(), $n = 0; false !== ( $n = strpos( $condition, '?', $n ) ); $n ++ ) {
                    $condition = substr_replace( $condition, ':' . ( $keys[] = '_' . ++ $i ), $n, 1 );
                }

                if ( ! empty( $keys ) ) {
                    $param = array_combine( $keys, (array) $param );
                }

                $params += (array) $param;

            } else {
                $condition = $param;
            }

            $sql[] = $condition;
        }

        return (object) array( 
            'sql'    => '( ' . implode( ' ) AND ( ', $sql ) . ' )',
            'params' => $params
        );
    }

    /**
     * Handle dynamic method calls into the method
     * @param  string $method 
     * @param  array $parameters   
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (preg_match('/^findBy(.+)$/', $method, $matches)) {
            return static::find([
                'field' => strtolower($matches[1]),
                'value' => $parameters[0]
            ]);
        }

        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        }
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

}
