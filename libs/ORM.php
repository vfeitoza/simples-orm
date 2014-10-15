<?php

require_once __DIR__ . '/DB.php';

/**
 * Simples modelo ORM para pequenos projetos
 */

class ORM {
    /**
     * Conexão com o banco de dados
     * 
     * @var object
     */
    private $db;
    
    /**
     * Array contendo todas as variáveis utilizadas
     * 
     * @var array
     */
    public $variables;

    /**
     * Construtor padrão
     * 
     * @param array $data Conteudo padrão para a classe em questão
     */
    public function __construct($data = array()) {
        $this->db = new DB();
        $this->variables = $data;
    }

    /**
     * Setando variável a partir de um valor passado
     * 
     * @param string $name Nome da variável
     * @param string $value Valor da variável
     */
    public function __set($name, $value) {
        if (strtolower($name) === $this->pk) {
            $this->variables[$this->pk] = $value;
        } else {
            $this->variables[$name] = $value;
        }
    }

    /**
     * Pegando valor da variável
     * 
     * @param string $name Nome da variável
     * @return string Valor da variável
     */
    public function __get($name) {
        if (is_array($this->variables)) {
            if (array_key_exists($name, $this->variables)) {
                return $this->variables[$name];
            }
        }

        $trace = debug_backtrace();
        trigger_error(
                'Undefined property via __get(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'], E_USER_NOTICE);
        
        return null;
    }
    
    /**
     * Pesquisa a partir dos argumentos repassados
     * 
     * @param string $method Por quem desejo pesquisar
     * @param array $condition Qual o nome a ser pesquisado
     * @return object Objeto contendo os valores do resultado
     */
    public function __call($method, $condition) {
        $match = array();
        
        if(preg_match("/(find|first)By([\w]+)/", $method, $match)) {
            $sql = "SELECT * FROM " . $this->table . " WHERE " . $match[2] . "= :" . $match[2] . " LIMIT 1";
            $this->variables = $this->db->row($sql, array($match[2] => $condition[0]));
        } else {
            return false;
        }
    }
    
    /**
     * Insere ou atualiza dados repassados
     * 
     * @param int $id ID a ser utilzado na atualização
     * @return int Colunas afetadas
     */
    public function save($id = "0") {
        if (!empty($id) || !empty($this->variables[$this->pk])) {
            $this->variables[$this->pk] = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];

            $fieldsvals = '';
            $columns = array_keys($this->variables);

            foreach ($columns as $column) {
                if ($column !== $this->pk)
                    $fieldsvals .= $column . " = :" . $column . ",";
            }

            $fieldsvals = substr_replace($fieldsvals, '', -1);

            if (count($columns) > 1) {
                $sql = "UPDATE " . $this->table . " SET " . $fieldsvals . " WHERE " . $this->pk . "= :" . $this->pk;
                $bindings = $this->variables;
            }
        } else {
            $bindings = $this->variables;

            if (!empty($bindings)) {
                $fields = array_keys($bindings);
                $fieldsvals = array(implode(",", $fields), ":" . implode(",:", $fields));
                $sql = "INSERT INTO " . $this->table . " (" . $fieldsvals[0] . ") VALUES (" . $fieldsvals[1] . ")";
            } else {
                $sql = "INSERT INTO " . $this->table . " () VALUES ()";
            }
        }
        
        return $this->db->query($sql, $bindings);
    }

    /**
     * Remove um item do banco de dados
     * 
     * @param int $id Item a ser removido
     * @return int Colunas afetadas
     */
    public function delete($id = "") {
        $id = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];

        if (!empty($id)) {
            $sql = "DELETE FROM " . $this->table . " WHERE " . $this->pk . "= :" . $this->pk . " LIMIT 1";
            return $this->db->query($sql, array($this->pk => $id));
        }
    }

    /**
     * Pesquisa por um ID no banco de dados
     * 
     * @param int $id ID a ser pesquisado
     */
    public function find($id = "") {
        $id = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];

        if (!empty($id)) {
            $sql = "SELECT * FROM " . $this->table . " WHERE " . $this->pk . "= :" . $this->pk . " LIMIT 1";
            $this->variables = $this->db->row($sql, array($this->pk => $id));
        }
    }
    
    /**
     * Retorna todos os valores da tabela
     * 
     * @return array Todos itens encontrados
     */
    public function all() {
        return $this->db->query("SELECT * FROM " . $this->table);
    }

    /**
     * Retorna o menor valor encontrado para o campo repassado
     * 
     * @param string $field Campo a ser verificado
     * @return string Menor valor encontrado
     */
    public function min($field) {
        if ($field)
            return $this->db->single("SELECT min(" . $field . ")" . " FROM " . $this->table);
    }

    /**
     * Retorna o maior valor encontrado para o campo repassado
     * 
     * @param string $field Campo a ser verificado
     * @return string Maior valor encontrado
     */
    public function max($field) {
        if ($field)
            return $this->db->single("SELECT max(" . $field . ")" . " FROM " . $this->table);
    }

    /**
     * Retorna a media do valor encontrado para o campo repassado
     * 
     * @param string $field Campo a ser verificado
     * @return string Média encontrada
     */
    public function avg($field) {
        if ($field)
            return $this->db->single("SELECT avg(" . $field . ")" . " FROM " . $this->table);
    }

    /**
     * Retorna a soma do valor encontrado para o campo repassado
     * 
     * @param string $field Campo a ser verificado
     * @return string Soma encontrada
     */
    public function sum($field) {
        if ($field)
            return $this->db->single("SELECT sum(" . $field . ")" . " FROM " . $this->table);
    }

    /**
     * Retorna quantos elementos foram encontrados para o campo repassado
     * 
     * @param string $field Campo a ser verificado
     * @return string Número de elementos encontrados
     */
    public function count($field) {
        if ($field)
            return $this->db->single("SELECT count(" . $field . ")" . " FROM " . $this->table);
    }
}
