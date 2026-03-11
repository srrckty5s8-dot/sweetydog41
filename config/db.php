<?php
$host = 'localhost';
$db   = 'mon_salon';       // ← Remplacer par le nom de votre base o2switch
$user = 'root';    // ← Remplacer par votre utilisateur MySQL o2switch
$pass = 'root';   // ← Remplacer par votre mot de passe MySQL o2switch

try {
    $initCommandAttr = defined('Pdo\\Mysql::ATTR_INIT_COMMAND')
        ? constant('Pdo\\Mysql::ATTR_INIT_COMMAND')
        : PDO::MYSQL_ATTR_INIT_COMMAND;

    $bdd = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        $initCommandAttr => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ]);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
// NE PAS METTRE DE BALISE DE FERMETURE ICI