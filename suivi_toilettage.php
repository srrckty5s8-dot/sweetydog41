<?php

// Compatibilité legacy : rediriger vers la route MVC de suivi.
$idAnimal = $_GET['id'] ?? null;


$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = $scriptPath !== '/' ? $scriptPath : '';

if ($idAnimal) {
    header('Location: ' . $basePath . '/animals/' . urlencode($idAnimal) . '/tracking');
    exit;

if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: index.php');
    exit();
}

$id_animal = $_GET['id'] ?? null;

if (!$id_animal) {
    header('Location: liste_clients.php');
    exit();
}

// 1. RÉCUPÉRATION DES DONNÉES DE L'ANIMAL ET DU PROPRIÉTAIRE
$query = $bdd->prepare("
    SELECT a.*, p.nom, p.prenom, p.telephone 
    FROM Animaux a 
    INNER JOIN Proprietaires p ON a.id_proprietaire = p.id_proprietaire 
    WHERE a.id_animal = :id
");
$query->execute(['id' => $id_animal]);
$animal = $query->fetch(PDO::FETCH_ASSOC);

if (!$animal) { die("Animal introuvable."); }

// 2. RÉCUPÉRATION DE L'HISTORIQUE
$query_hist = $bdd->prepare("SELECT * FROM Prestations WHERE id_animal = ? ORDER BY date_soin DESC");
$query_hist->execute([$id_animal]);
$historique = $query_hist->fetchAll(PDO::FETCH_ASSOC);
// 3. LOGIQUE POUR LE TÉLÉCHARGEMENT AUTOMATIQUE
$download_link = null;
$nomFichierPDF = null;

if (isset($_GET['success']) && $_GET['success'] == 1 && !empty($historique)) {
    // On récupère la prestation la plus récente qui vient d'être encaissée
    $last_prestation = $historique[0]; 
    $idFacture = date('Y') . '-' . $last_prestation['id_prestation'];
    $nomFichierPDF = "Facture_SweetyDog_" . $idFacture . ".pdf";
    
    if (file_exists(__DIR__ . '/factures/' . $nomFichierPDF)) {
        $download_link = 'factures/' . $nomFichierPDF;
    }
main
}

header('Location: ' . $basePath . '/clients');
exit;
