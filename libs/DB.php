<?php
/**
 * Uma classe simples para servir de exemplo de utilização da Biblioteca PDO
 * bem como exemplo de um mini ORM para utilização em projetos simples.
 */

class DB {
    /**
     * Objeto PDO para a conexão
     * 
     * @var object
     */
    private $pdo;

    /**
     * Objeto contendo o estado da conexão
     * 
     * @var object
     */
    private $sQuery;

    /**
     * Configurações de acesso ao banco
     * 
     * @var array
     */
    private $settings;

    /**
     * Estado da conexão com o banco
     * 
     * @var boolean
     */
    private $bConnected = false;

    /**
     * Parâmetros para construção da SQL
     * 
     * @var array
     */
    private $parameters;

    /**
     * Construtor da classe
     */
    public function __construct() {
        $this->Connect();
        $this->parameters = array();
    }

    /**
     * Conecta ao banco de dados a partir das configurações existentes
     */
    private function Connect() {
        $this->settings = parse_ini_file("config.ini.php");
        $dsn = 'mysql:dbname=' . $this->settings["dbname"] . ';host=' . $this->settings["host"] . '';
        try {
            $this->pdo = new PDO($dsn, $this->settings["user"], $this->settings["password"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $this->bConnected = true;
        } catch (PDOException $e) {
            echo $this->ExceptionLog($e->getMessage());
            die();
        }
    }

    /**
     * Fecha a conexão com o banco de dados
     */
    public function CloseConnection() {
        $this->pdo = null;
    }

    /**
     * Método para execução de todas as consultas SQL's
     * 
     * @param string $query Query a ser usada na SQL
     * @param array $parameters Parâmetros a serem usados na SQL
     */
    private function Init($query, $parameters = "") {
        if (!$this->bConnected) {
            $this->Connect();
        }
        try {
            $this->sQuery = $this->pdo->prepare($query);

            $this->bindMore($parameters);

            if (!empty($this->parameters)) {
                foreach ($this->parameters as $param) {
                    $parameters = explode("\x7F", $param);
                    $this->sQuery->bindParam($parameters[0], $parameters[1]);
                }
            }

            $this->succes = $this->sQuery->execute();
        } catch (PDOException $e) {
            echo $this->ExceptionLog($e->getMessage(), $query);
            die();
        }

        $this->parameters = array();
    }

    /**
     * Insere os parâmetros utilizado em array
     * 
     * @param string $para Nome do parâmetro
     * @param string $value Valor a ser inserido
     */
    public function bind($para, $value) {
        $this->parameters[sizeof($this->parameters)] = ":" . $para . "\x7F" . $value;
    }

    /**
     * Adiciona parâmetros repassados em forma de array
     * 
     * @param array $parray Parâmetros em forma de array
     */
    public function bindMore($parray) {
        if (empty($this->parameters) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }

    /**
     * Realiza uma instrução SQL retornando um array para SELECT ou SHOW ou
     * número de linhas afetadas para ISNERT, UPDATE e DELETE
     * 
     * @param string $query Instrução SQL
     * @param string $params Parâmetros
     * @param string $fetchmode Modelo de retorno
     * @return array Array ou número de linhas afetadas
     */
    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC) {
        $query = trim($query);

        $this->Init($query, $params);

        $rawStatement = explode(" ", $query);

        $statement = strtolower($rawStatement[0]);

        if ($statement === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return NULL;
        }
    }

    /**
     * Retorna  última linha inserida
     * 
     * @return int Última linha inserida
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Retorna as colunas encontradas
     * 
     * @param string $query Instrução SQL
     * @param string $params Parâmetros utilizados
     * @return array
     */
    public function column($query, $params = null) {
        $this->Init($query, $params);
        $Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);

        $column = null;

        foreach ($Columns as $cells) {
            $column[] = $cells[0];
        }

        return $column;
    }

    /**
     * Retorna um array com a linha encontrada
     * 
     * @param string $query Instrução SQL
     * @param array $params Parâmetro utilizado
     * @param int $fetchmode
     * @return array
     */
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC) {
        $this->Init($query, $params);
        return $this->sQuery->fetch($fetchmode);
    }

    /**
     * Resultado único para o SQL informado
     * 
     * @param string $query Instrução SQL
     * @param array $params Parâmetro utilizado
     * @return string Valor encontrado
     */
    public function single($query, $params = null) {
        $this->Init($query, $params);
        return $this->sQuery->fetchColumn();
    }

    /**
     * Mensagem com o erro ocorrido
     * 
     * @param string $message Mensagem a ser informada
     * @param string $sql Instrução SQL usada
     * @return string Mensagem formatada
     */
    private function ExceptionLog($message, $sql = "") {
        $exception = 'Unhandled Exception. <br />';
        $exception .= $message;

        if (!empty($sql)) {
            $exception .= "<br />Raw SQL : " . $sql;
        }

        return $exception;
    }

}
