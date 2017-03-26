<?php
/**
 * PDO操作类
 * @date 2014-3-31 15:25:21
 * @author harryxlb
 */

class DB {
    private $dbms, $dbhost, $dbuser, $dbpassword, $dbname, $dbcharset;
    static $PDO = NULL;
    static $instance = NULL;
    
    function __construct() {
		require_once(dirname(__FILE__) . '/../config/config.php');
        $this->dbms = $config['dbms'];
        $this->dbhost = $config['dbhost'];
        $this->dbuser = $config['dbuser'];
        $this->dbpassword = $config['dbpassword'];
        $this->dbname = $config['dbname'];
        $this->dbcharset =$config['dbcharset'];
        
        $this->connect();
    }
    
    static function getInstance(){
        if (is_null(self::$instance)) {
            self::$instance = new DB();
            return self::$instance;
        }
        return self::$instance;
    }
    
    /**
     * 连接数据库
     * @return PDO
     * @throws Exception
     */
    public function connect(){
        try{
            self::$PDO = new PDO($this->dbms . ':host=' . $this->dbhost . '; dbname=' . $this->dbname, $this->dbuser, $this->dbpassword);
//            echo '连接成功。';
            self::$PDO->exec('SET NAMES ' . $this->dbcharset);
            return self::$PDO;
        } catch (PDOException $ex){
//            $this->showError('连接数据库失败 <br />',   $ex->getMessage());
              throw new  Exception('连接数据库失败 <br />' . $ex->getMessage());
        }
    }

    /**
     * 查询请求
     * @param string $sql
     * @param PDO::FETCH_ASSOC $FETCH_TYPE
     * @return queryObject
     */
    public function query($sql, $FETCH_TYPE = PDO::FETCH_ASSOC){
        if (!self::$PDO) self::$PDO = self::getInstance();
        $this->queryObj = self::$PDO->query($sql);
        $this->queryObj->setFetchMode($FETCH_TYPE);
        return $this->queryObj;
    }
    
    public function fetch($sql){
        $queryObj = $this->query($sql);
        return $queryObj->fetch();
    }
    
    public function fetchAll($sql){
        $queryObj = $this->query($sql);
        return $queryObj->fetchAll();
    }
    
    public function exec($sql){
        if (!self::$PDO) self::$PDO = self::getInstance();
        $queryObj = self::$PDO->exec($sql);
        return $queryObj;
    }
    
    public function execute($sql){
        $stmt = self::$PDO->prepare($sql);
        return $stmt->execute();
    }
    
    public function insert($table, $data){
        if (!is_array($data)) return false;
        foreach ($data as $key => $val){
            $keys[] = '`' . $key . '`';
            $vals[] = "'" . $val . "'";
        }
        $keystr = implode(',', $keys);
        $valstr = implode(',', $vals);
        $sql = "INSERT INTO `$table` ( {$keystr} ) VALUES ( " . $valstr . ")";
        return $this->execute($sql);
    }
    
    public function update($table, $data, $condition = ''){
        if (!is_array($data)) return false;
        foreach ($data as $key => $val){
            $setlist[] = "`$key` = '$val'";
        }
        $setstr = implode(',', $setlist);
        $sql = "UPDATE `$table` SET $setstr";
        $sql .= $this->where($condition);
        return $this->execute($sql);
    }

    /**
     * 删除操作
     * @param array/string $condition
     */
    public function delete($table, $condition = ''){
        $sql = "DELETE FROM `$table`" . $this->where($condition);
        return $this->exec($sql);
    }
    
    /**
     * 混合条件查询
     * @param array/string $whereCondi
     * @return string
     */
    public function where($whereCondi, $logicOper = 'AND'){
        if (empty($whereCondi)) return;
        if (!is_array($whereCondi)) return " WHERE $whereCondi";
        if (is_array($whereCondi)){
            $operlist = array('>', '<', '=', '<>');
            foreach ($whereCondi as $key => $val){
                $val = is_numeric($val) ? $val : "'$val'";
                if (in_array($key, $operlist)) $condlist[] = $key . $val;
                else $condlist[] = "`$key` = $val";
            }
            $condstr = " WHERE " . implode(' ' . $logicOper . ' ', $condlist);
            return $condstr;
        }
        return;
    }

    /**
     * 数据库错误处理
     * @param type $errTitle
     * @param type $errMessage
     */
    public function showError($errTitle, $errMessage){
    $errorHtml = <<<EOD
            <div style="width: 80%; height: 80%; margin: 0 auto; padding: 20px 30px; border: 1px solid #ddd; background: #EEC469; font-size: 12px; font-family: verdana; color: red;">
                <H1>$errTitle</H1>
                $errMessage
            </div>
EOD;
    exit($errorHtml);
}
    
}

function __destruct(){
   if (self::$PDO)
       self::$PDO = NULL;
}

