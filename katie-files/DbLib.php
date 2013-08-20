<?php

if (!defined('BASEURL')) {
   // exit('No direct calls allowed!');
}

// KatiePHP ORM (Based on PDO) - KatieDB class
// Start of DbLib.php (KatiePHP) /system/libraries
/* * ************************************************************************ */
Class DbLib {

    protected $configData = array();
    protected $configFile = 'database.php';
    // object of the database connection (object)
    protected $dbConnection = null;
    // operating table (string)
    protected $dbTable = '';
    // current operating statement (object)
    protected $dbStmt = null;
    // last operated statement (object)
    protected $dbLastStmt = null;
    // data, you want to select (string)
    protected $dbSelect = '*';
    // data, you want to update or insert  (array)
    protected $dbData = array();
    // data, from where clause (array)
    protected $dbWhere = array();
    //limit the results (string)
    protected $dbLimit = '';
    // order the results (string)
    protected $dbOrder = '';

    public function __construct() {
        require_once $this->configFile;
        $this->configData = $config;
        if ($this->configData['auto_connect']) {
            $this->connect();
        }
    }

    public function __destruct() {
        $this->dbConnection = null;
    }

    /*
     * public function connect
     * connect to specified database
     *  parameters:
     *      @dbServer : string expected
     *      @dbName : string expected
     *      @dbUser : string expected
     *      @dbPassword : string expected
     *      @dbCharset : string expected
     */

    public function connect($dbServer = '', $dbName = '', $dbUser = '', $dbPassword = '', $dbCharset = '') {
        $dbServer = ($dbServer) ? $dbServer : $this->configData['db_server'];
        $dbName = ($dbName) ? $dbName : $this->configData['db_name'];
        $dbUser = ($dbUser) ? $dbUser : $this->configData['db_user'];
        $dbPassword = ($dbPassword) ? $dbPassword : $this->configData['db_password'];
        $dbCharset = ($dbCharset) ? $dbCharset : $this->configData['db_charset'];

        try {
            $dbOptions = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $dbCharset
            );
            $this->dbConnection = new PDO("mysql:host=$dbServer;dbname=$dbName;", $dbUser, $dbPassword, $dbOptions);
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    /*
     * protected function _checkConnection
     * check if a connection to the database is made
     */

    protected function _checkConnection() {
        if (!is_object($this->dbConnection)) {
            exit('Not Connected to the database!');
        }
    }

    /*
     * public function exec
     * execute user defined query
     *  parameters:
     *      @dbQuery : string expected
     */

    public function exec($dbQuery) {
        $this->_checkConnection();
        $this->dbStmt = $this->dbConnection->query($dbQuery);
        return new DbFetchable($this->dbStmt);
    }

    /*
     * public function get
     * make select request
     * by default the request should be  SELECT * FROM `table_name`;
     *  parameters:
     *      @dbTable : string expected (optional)
     *      @dbUnset : boolean expected (optional)
     */

    public function get($dbTable = '', $dbUnset = true) {
        $this->_checkConnection();
        if ($dbTable) {
            $this->setTable($dbTable);
        }
        $this->_buildQuery('select');
        $this->dbStmt->execute();
        $this->dbLastStmt = $this->dbStmt;
        if ($dbUnset) {
            $this->unsetData();
        }
        return new DbFetchable($this->dbLastStmt);
    }

    /*
     * public function insert
     * make insert request
     *  parameters:
     *      @dbTable : string expected (optional)
     *      @dbData : array expected (optional)
     *      @dbUnset : boolean expected (optional)
     */

    public function insert($dbTable = '', $dbData = array(), $dbUnset = true) {
        $this->_checkConnection();
        //change data
        if (!empty($dbData)) {
            $this->setData($dbData);
        }
        //change table
        if ($dbTable) {
            $this->setTable($dbTable);
        }
        $this->_buildQuery('insert');
        $this->dbStmt->execute();

        $this->dbLastStmt = $this->dbStmt;
        if ($dbUnset) {
            $this->unsetData();
        }
        return $this->dbLastStmt->rowCount();
    }

    /*
     * public function update
     * make update request
     *  parameters:
     *      @dbTable : string expected (optional)
     *      @dbData : array expected (optional)
     *      @dbUnset : boolean expected (optional)
     */

    public function update($dbTable = '', $dbData = array(), $dbUnset = true) {
        $this->_checkConnection();
        //change data
        if (!empty($dbData)) {
            $this->setData($dbData);
        }
        //change table
        if ($dbTable) {
            $this->setTable($dbTable);
        }
        $this->_buildQuery('update');
        $this->dbStmt->execute();

        $this->dbLastStmt = $this->dbStmt;
        if ($dbUnset) {
            $this->unsetData();
        }
        return $this->dbLastStmt->rowCount();
    }

    /*
     * public function delete
     * make delete request
     *  parameters:
     *      @dbTable : string expected (optional)
     *      @dbUnset : boolean expected (optional)
     */

    public function delete($dbTable = '', $dbUnset = true) {
        $this->_checkConnection();
        //change table
        if ($dbTable) {
            $this->setTable($dbTable);
        }
        $this->_buildQuery('delete');
        $this->dbStmt->execute();

        $this->dbLastStmt = $this->dbStmt;
        if ($dbUnset) {
            $this->unsetData();
        }
        return $this->dbLastStmt->rowCount();
    }

    /*
     * public function getError
     * return the last error
     *  parameters:
     *      @asArray : pass all the data as array or just a message (optional)
     */

    public function getError($asArray = false) {
        $errArray = $this->dbConnection->errorInfo();
        if ($errArray[0] !== '00000') {
            return ($asArray) ? $errArray : $errArray[2];
        }
        $errArray = $this->dbLastStmt->errorInfo();
        if ($errArray[0] !== '00000') {
            return ($asArray) ? $errArray : $errArray[2];
        }
        return false;
    }

    /*
     * public function getLastQuery
     * return the last executed query
     */

    public function getLastQuery() {
        return $this->dbLastStmt->queryString;
    }

    /*
     * public function lastInsertedId
     * return the last inserted id
     */

    public function lastInsertedId() {
        return $this->dbConnection->lastInsertId();
    }

    /*
     * public function countRows
     * return the number of the affected rows in the last request
     */

    public function affectedRows() {
        return $this->dbLastStmt->rowCount();
    }

    /*
     * public function countRows
     * return the number of rows in the table
     *  parameters:
     *      @dbTable : string expected (optional)
     */

    public function getRowsCount($dbTable = '') {
        $this->_checkConnection();
        if ($dbTable) {
            $this->setTable($dbTable);
        }
        $sql = 'SELECT COUNT(*) FROM `' . $this->dbTable . '`';
        $res = $this->dbConnection->query($sql);
        return $res->fetchColumn();
    }

    /*
     * protected function _buildQuery
     * build the query and prepare it for execution
     *  parameters:
     *      @dbQueryType : string expected
     */

    protected function _buildQuery($dbQueryType = 'select') {
        switch ($dbQueryType) {
            case 'insert':
                $dbQ = 'INSERT INTO `' . $this->dbTable . '` ';
                $this->_buildQueryAddInsertData($dbQ);
                break;
            case 'update':
                $dbQ = 'UPDATE `' . $this->dbTable . '` ';
                $this->_buildQueryAddUpdateData($dbQ);
                $this->_buildQueryAddWhere($dbQ);
                $this->_buildQueryAddLimit($dbQ);
                break;
            case 'delete':
                $dbQ = 'DELETE FROM `' . $this->dbTable . '` ';
                $this->_buildQueryAddWhere($dbQ);
                $this->_buildQueryAddLimit($dbQ);
                break;
            default:
                $dbQ = 'SELECT ' . $this->dbSelect . ' FROM `' . $this->dbTable . '` ';
                $this->_buildQueryAddWhere($dbQ);
                $this->_buildQueryAddOrder($dbQ);
                $this->_buildQueryAddLimit($dbQ);
                break;
        }
        $dbQ .= ';';
        $this->dbStmt = $this->dbConnection->prepare($dbQ);
        if ($dbQueryType == 'insert' || $dbQueryType == 'update') {
            $i = 0;
            foreach ($this->dbData as $column => $value) {
                $i++;
                $this->dbStmt->bindValue(':' . $column, $value);
            }
        }
        if ($dbQueryType != 'insert') {
            foreach ($this->dbWhere as $key => $cArr) {
                $this->dbStmt->bindParam(':' . $cArr['key'] . $key, $cArr['value']);
            }
        }
    }

    /*
     * protected function _buildQueryAddWhere
     * build the query and add it where clause
     *  parameters:
     *      @dbQ : string expected
     */

    protected function _buildQueryAddWhere(& $dbQ) {
        if (!empty($this->dbWhere)) {
            $dbQ .= 'WHERE ';
            for ($i = 0; $i < count($this->dbWhere); $i++) {
                $cArr = $this->dbWhere[$i];
                if ($i > 0) {
                    $dbQ .= $cArr['operator'] . ' ';
                }
                $dbQ .= $cArr['key'] . ' ' . $cArr['cond'] . ' :' . $cArr['key'] . $i . ' ';
            }
        }
    }

    /*
     * protected function _buildQueryAddInsertData
     * build the query and add it data for insert
     *  parameters:
     *      @dbQ : string expected
     */

    protected function _buildQueryAddInsertData(& $dbQ) {
        if (!empty($this->dbData)) {
            $dbQ .= '(';
            $trailer = 'VALUES (';
            $i = 0;
            foreach ($this->dbData as $column => $value) {
                if ($i > 0) {
                    $dbQ.=' ,';
                    $trailer.=', ';
                }
                $i++;
                $dbQ.='`' . $column . '`';
                $trailer.=':' . $column;
            }
            $dbQ .= ') ';
            $trailer.= ')';
            $dbQ .= $trailer;
        }
    }

    /*
     * protected function _buildQueryAddUpdateData
     * build the query and add it data for update
     *  parameters:
     *      @dbQ : string expected
     */

    protected function _buildQueryAddUpdateData(& $dbQ) {
        if (!empty($this->dbData)) {
            $dbQ .= 'SET ';
            $i = 0;
            foreach ($this->dbData as $column => $value) {
                if ($i > 0) {
                    $dbQ.=', ';
                }
                $i++;
                $dbQ.='`' . $column . '` = :' . $column;
            }
            $dbQ.=' ';
        }
    }

    /*
     * protected function _buildQueryAddOrder
     * build the query and add it return order
     *  parameters:
     *      @dbQ : string expected
     */

    protected function _buildQueryAddOrder(& $dbQ) {
        if ($this->dbOrder) {
            $dbQ .= $this->dbOrder . ' ';
        }
    }

    /*
     * protected function _buildQueryAddLimit
     * build the query and add it limit
     *  parameters:
     *      @dbQ : string expected
     */

    protected function _buildQueryAddLimit(& $dbQ) {
        if ($this->dbLimit) {
            $dbQ .= $this->dbLimit;
        }
    }

    /*
     * public function setConfig
     * set configuration for connection
     *  parameters:
     *      @dbNewConfig : array expected
     */

    public function setConfig($dbNewConfigData) {
        $this->configData = array_diff_key($this->configData, $dbNewConfigData) + $dbNewConfigData;
    }

    /*
     * public function setTable
     * set the table for future operations
     *  parameters:
     *      @dbTable : string expected
     */

    public function setTable($dbTable) {
        $this->dbTable = $this->configData['db_prefix'] . $dbTable;
    }

    /*
     * public function setData
     * set data for update or insert
     *  parameters:
     *      @dbParameter : string or array expected
     *      @dbValue : string expected (optional)
     */

    public function setData($dbKey, $dbValue = '') {
        // if the first parameter is array directly add it the other data.
        if (is_array($dbKey)) {
            $newData = $dbKey;
        } else {
            $newData = array($dbKey => $dbValue);
        }
        $this->dbData = array_diff_key($this->dbData, $newData) + $newData;
    }

    /*
     * public function select
     * set data you want to select
     *  parameters:
     *      @dbSelect : string expected
     */

    public function select($dbSelect) {
        $this->dbSelect = $dbSelect;
    }

    /*
     * public function where
     * set where statement
     *  parameters:
     *      @dbKey : string expected
     *      @dbValue : string expected
     *      @dbCond : (condition) string expected (optional)
     */

    public function where($dbKey, $dbValue, $dbCond = '=') {
        $this->_setWhereClause($dbKey, $dbValue, $dbCond, 'AND');
    }

    /*
     * public function andWhere
     * alis to 'where' function
     */

    public function andWhere($dbKey, $dbValue, $dbCond = '=') {
        $this->where($dbKey, $dbValue, $dbCond);
    }

    /*
     * public function orWhere
     * set where statement
     *  parameters:
     *      @dbKey : string expected
     *      @dbValue : string expected
     *      @dbCond : (condition) string expected (optional)
     */

    public function orWhere($dbKey, $dbValue, $dbCond = '=') {
        $this->_setWhereClause($dbKey, $dbValue, $dbCond, 'OR');
    }

    /*
     * protected function _setWhereClause
     * set where statement
     *      @dbKey : string expected
     *      @dbValue : string expected
     *      @dbCond : (condition) string expected
     *      $dbOperator : OR or AND relating different statements
     */

    protected function _setWhereClause($dbKey, $dbValue, $dbCond, $dbOperator) {
        $newData['key'] = $dbKey;
        $newData['value'] = $dbValue;
        $newData['cond'] = $dbCond;
        $newData['operator'] = $dbOperator;
        $this->dbWhere[] = $newData;
    }

    /*
     * public function limit
     * set limit of the results
     *  parameters:
     *      @dbStart : integer expected
     *      @dbOffset : integer expected (optional)
     */

    public function limit($dbStart, $dbOffset = 0) {
        if (is_int($dbStart) && is_int($dbOffset)) {
            $this->dbLimit = 'LIMIT ' . $dbStart;
            if ($dbOffset !== 0) {
                $this->dbLimit .= ', ' . $dbOffset;
            }
        }
    }

    /*
     * public function order
     * set where statement
     *  parameters:
     *      @dbOrder : string expected
     *      @dbDescending : boolean expected (optional)
     */

    public function order($dbOrder, $dbDescending = false) {
        $this->dbOrder = 'ORDER BY ' . $dbOrder;
        if ($dbDescending) {
            $this->dbOrder .= ' DESC';
        }
    }

    /*
     * public function unsetData
     * clean all the data about the statement
     */

    public function unsetData() {
        $this->dbTable = '';
        $this->dbStmt = null;
        $this->dbSelect = '*';
        $this->dbWhere = array();
        $this->dbData = array();
        $this->dbLimit = '';
        $this->dbOrder = '';
    }

}

class DbFetchable {

    protected $pdoStmt;

    public function __construct($pdoStatement) {
        $this->pdoStmt = $pdoStatement;
    }

    /*
     * public function fetchAll
     * returns the requested data in an array full of arrays, objects or both
     *  parameters:
     *      @fetchAs : string expected (optional)
     */

    public function fetchAll($fetchAs = 'both') {
        switch ($fetchAs) {
            case 'array':
                $fetchMode = PDO::FETCH_NUM;
                break;
            case 'assoc':
                $fetchMode = PDO::FETCH_ASSOC;
                break;
            case 'object':
                $fetchMode = PDO::FETCH_OBJ;
                break;
            default:
                $fetchMode = PDO::FETCH_BOTH;
                break;
        }
        return $this->pdoStmt->fetchAll($fetchMode);
    }

    public function fetchAllBoth() {
        return $this->fetchAll('both');
    }

    public function fetchAllArray() {
        return $this->fetchAll('array');
    }

    public function fetchAllAssoc() {
        return $this->fetchAll('assoc');
    }

    public function fetchAllObject() {
        return $this->fetchAll('object');
    }

    /*
     * public function fetch
     * returns the next row of data in an array or object
     *  parameters:
     *      @fetchAs : string expected (optional)
     */

    public function fetch($fetchAs = 'both') {
        switch ($fetchAs) {
            case 'array':
                $fetchMode = PDO::FETCH_NUM;
                break;
            case 'assoc':
                $fetchMode = PDO::FETCH_ASSOC;
                break;
            case 'object':
                $fetchMode = PDO::FETCH_OBJ;
                break;
            default:
                $fetchMode = PDO::FETCH_BOTH;
                break;
        }
        return $this->pdoStmt->fetch($fetchMode);
    }

    public function fetchBoth() {
        return $this->fetch('both');
    }

    public function fetchArray() {
        return $this->fetch('array');
    }

    public function fetchAssoc() {
        return $this->fetch('assoc');
    }

    public function fetchObject() {
        return $this->fetch('object');
    }

    public function affectedRows() {
        return $this->pdoStmt->rowCount();
    }

}

// End of the file DbLib.php