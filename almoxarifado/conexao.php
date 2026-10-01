<?php
$host = "192.168.10.13";
$usuario = "postgres";
$senha = "97314341";
$banco = "almoxarifado";

$pdo = new PDO (
    "pgsql:host=$host;port=5432;dbname=$banco",$usuario,$senha
);
?>