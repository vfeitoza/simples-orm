<?php

require_once './libs/ORM.php';

/**
 * Exemplo simples de utilização de um ORM
 */
class Frutas extends ORM {
    /**
     * Nome da tabela a ser utilizada
     * 
     * @var string
     */
    protected $table = "frutas";
    
    /**
     * Chave primária da tabela
     * 
     * @var string
     */
    protected $pk = "id";
}
