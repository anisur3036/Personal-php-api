<?php

namespace Anis\Database;

use PDO;
use Anis\Config;
use Anis\Database\Driver;
use Anis\Database\ConnectionInterface;

abstract class QueryBuilder
{
    protected $table;

    protected $stmt;

    public $numOfRows;

    private $page;

    public $limit;

    /**
     * The PDO instance.
     *
     * @var PDO
     */
    public $pdo;

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
    
    public function make(ConnectionInterface $database)
    {
        return $database->connect();
    }

    /**
     * Select all records from a database table.
     *
     * @param string $table
     */
    public function selectAll()
    {
        $stmt = $this->pdo->prepare("select * from {$this->getTable()}");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Anis\\' . $this->getClassName());
    }

    public function exists($data)
    {
        $field = array_keys($data)[0];

        return $this->where($field, '=', $data[$field])->count() ? true : false;
    }

    public function table($table)
    {
        $this->table = $table;
        
        return $this;
    }
    public function where($field, $operator, $value)
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
        $this->query = sprintf(
            'insert into %s (%s) values (%s)',
            $this->getTable(),
            implode(', ', array_keys($params)),
            ':' . implode(', :', array_keys($params))
        );
        try {
            $stmt = $this->pdo->prepare($this->query);

            $stmt->execute($params);
        } catch (\PDOException $e) {
            //
        }
    }

    public function query($query)
    {
        $this->stmt = $this->pdo->prepare($query);
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

    public function execute()
    {
        $this->stmt->execute();
    }

    public function get()
    {
        $this->execute();

        return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Anis\\' . $this->getClassName());
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function single()
    {
        $this->execute();

        return $this->stmt->fetch(PDO::FETCH_CLASS, 'Anis\\' . $this->getClassName());
    }

    public function totalRecords($query)
    {
        $this->query($query);
        $this->execute();

        return $this->stmt->rowCount();
    }

    public function paging($query, $limit, $extra = [])
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

        // debugging
        /* echo '$total: '.$total.' | '; //total rows
         echo '$row_start: '.$rowStart.' | '; //total rows
         echo '$limit: '.$this->limit.' | '; //total rows per query
         echo '$start: '.$start.' | '; //start printing links from
         echo '$end: '.$end.' | '; //end printing links at
         echo '$last: '.$last.' | '; //last page
         echo '$page: '.$this->page.' | '; //current page
         echo '$links: '.$links.' <br /> '; //links */

        //ul boot strap class - "pagination pagination-sm"
        $html = '<ul class="' . $listClass . '">';

        $class = ($this->page == 1) ? "disabled" : ""; //disable previous page link <<<

        //create the links and pass limit and page as $_GET parameters

        //$this->_page - 1 = previous page (<<< link )
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

    public function paginate($limit, $extra = [])
    {
        $this->limit = $limit;
        $query = "SELECT * FROM {$this->getTable()}";
        $recordPerPage = (isset($_GET['limit'])) ? $_GET['limit'] : $this->limit; //movies per page
        $page = (isset($_GET['pg'])) ? $_GET['pg'] : 1; //starting page
        //$links = 1;
        $pagingQuery = $this->paging($query, $recordPerPage, $extra);
        $this->query($pagingQuery);

        return $this->get();
    }

    public function links($links, $listClass)
    {
        return $this->createLinks($links, $listClass);
    }

    public function getTable()
    {
        if (! isset($this->table)) {
            return strtolower(str_replace('\\', '', class_basename($this))) . 's';
        }

        return $this->table;
    }
    
    public function getClassName()
    {
        return str_replace('\\', '', class_basename($this));
    }
}
