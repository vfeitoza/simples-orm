<?php

require_once './Frutas.php';

$frutas = new Frutas();

$frutas->nome = "Maça";
$frutas->vitamina = "B";
$frutas->save();

$frutas->nome = "Manga";
$frutas->vitamina = "C";
$frutas->save();

$frutas->nome = "Laranja";
$frutas->vitamina = "D";
$frutas->save();

$frutas->nome = "Uva";
$frutas->vitamina = "A";
$frutas->save();

echo $frutas->count("nome") . PHP_EOL;

$frutas->find(1);
echo $frutas->nome .PHP_EOL;
echo $frutas->vitamina .PHP_EOL;

$frutas->findByNome("Laranja") . PHP_EOL;
echo $frutas->nome .PHP_EOL;
echo $frutas->vitamina .PHP_EOL;

$frutas->vitamina = "A";
$frutas->save();

$frutas->findByNome("Laranja") . PHP_EOL;
echo $frutas->nome .PHP_EOL;
echo $frutas->vitamina .PHP_EOL;

$frutas->delete(4);

echo $frutas->count("nome") . PHP_EOL;