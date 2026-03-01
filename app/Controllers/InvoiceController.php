<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use Atgp\FacturX\Writer;

class InvoiceController extends Controller
{
    private string $lastMailError = '';

    public function generate($id = 0)
    {
        $this->requireLogin();

        $id_prestation = (int)$id;
        if ($id_prestation <= 0) {
            redirect('clients.index');
            exit;
        }

        $pdo = Database::getConnection();
        $this->ensureInvoiceNumberingSchema($pdo);

        // Déterminer le groupe de prestations à facturer:
        // 1) query `group` (depuis l'historique fusionné)
        // 2) session `facture_groupe` (création immédiate depuis le formulaire)
        // 3) fallback sur la prestation demandée
        $groupeIds = [];
        $groupQuery = trim((string)($_GET['group'] ?? ''));
        if ($groupQuery !== '') {
            foreach (explode(',', $groupQuery) as $rawId) {
                $gid = (int)trim($rawId);
                if ($gid > 0) {
                    $groupeIds[$gid] = $gid;
                }
            }
            $groupeIds = array_values($groupeIds);
            if (!in_array($id_prestation, $groupeIds, true)) {
                array_unshift($groupeIds, $id_prestation);
            }
        } elseif (!empty($_SESSION['facture_groupe']) && is_array($_SESSION['facture_groupe'])) {
            $groupeIds = array_values(array_unique(array_map('intval', $_SESSION['facture_groupe'])));
            unset($_SESSION['facture_groupe']);
        } else {
            $groupeIds = [$id_prestation];
        }

        // Récupérer toutes les prestations du groupe
        $placeholders = implode(',', array_fill(0, count($groupeIds), '?'));
        $query = $pdo->prepare("
            SELECT p.*, a.id_animal, a.nom_animal, a.espece,
                   prop.nom, prop.prenom, prop.telephone, prop.id_proprietaire,
                   prop.adresse
            FROM Prestations p
            JOIN Animaux a ON p.id_animal = a.id_animal
            JOIN Proprietaires prop ON a.id_proprietaire = prop.id_proprietaire
            WHERE p.id_prestation IN ($placeholders)
            ORDER BY p.id_prestation ASC
        ");
        $query->execute($groupeIds);
        $allPrestations = $query->fetchAll(PDO::FETCH_ASSOC);

        if (empty($allPrestations)) die("Prestation introuvable.");

        // La première prestation sert de référence (client, date)
        $data = $allPrestations[0];

        // Compatibilité vue facture : l'adresse est stockée en une seule colonne.
        $data['rue'] = '';
        $data['code_postal'] = '';
        $data['ville'] = '';
        $adresseBrute = trim((string)($data['adresse'] ?? ''));
        if ($adresseBrute !== '') {
            $lignesAdresse = preg_split('/\r\n|\r|\n/', $adresseBrute);
            $data['rue'] = trim($lignesAdresse[0] ?? '');

            $ligneCpVille = trim($lignesAdresse[1] ?? '');
            if ($ligneCpVille !== '' && preg_match('/^(\d{4,5})\s+(.*)$/', $ligneCpVille, $m)) {
                $data['code_postal'] = trim($m[1]);
                $data['ville'] = trim($m[2]);
            } else {
                $data['ville'] = $ligneCpVille;
                if ($data['ville'] === '' && preg_match('/(\d{4,5})\s+(.+)$/', $adresseBrute, $m2)) {
                    $data['code_postal'] = trim($m2[1]);
                    $data['ville'] = trim($m2[2]);
                }
            }
        }

        // Calculer le prix total (somme de toutes les prestations du groupe)
        $prixTotal = 0;
        foreach ($allPrestations as $p) {
            $prixTotal += (float)$p['prix'];
        }
        $numeroFacture = $this->resolveInvoiceNumber($pdo, $groupeIds);
        $numeroFactureFormate = $this->formatInvoiceNumber($numeroFacture);

        $data['prix_total'] = $prixTotal;
        $data['prestations'] = $allPrestations;
        $data['is_multi'] = count($allPrestations) > 1;
        $data['numero_facture'] = $numeroFacture;
        $data['numero_facture_formate'] = $numeroFactureFormate;

        // Lire code.env à la racine
        $apiKey = null;
        $apiUrl = "https://api.n2f.com/v1/expenses/upload";
        $envPath = __DIR__ . '/../../code.env';

        if (file_exists($envPath)) {
            $content = file_get_contents($envPath);
            if (preg_match('/N2F_API_KEY\s*=\s*([^\s#]+)/', $content, $m)) $apiKey = trim($m[1], "\"' ");
            if (preg_match('/N2F_API_URL\s*=\s*([^\s#]+)/', $content, $m)) $apiUrl = trim($m[1], "\"' ");
        }
        if (!$apiKey) die("Erreur : API Key manquante.");

        $projectRoot = realpath(__DIR__ . '/../../') ?: dirname(__DIR__, 2);
        $publicDirs = [];
        $pushPublicDir = static function ($dir) use (&$publicDirs): void {
            if (!$dir) {
                return;
            }
            $real = realpath($dir);
            if ($real && is_dir($real) && !in_array($real, $publicDirs, true)) {
                $publicDirs[] = $real;
            }
        };
        $pushPublicDir($projectRoot . '/public');
        $pushPublicDir($projectRoot . '/public_html');
        $pushPublicDir($_SERVER['DOCUMENT_ROOT'] ?? null);

        // PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', $projectRoot);
        $dompdf = new Dompdf($options);

        // Charger Beloved Script côté DomPDF (compatible public et public_html)
        $belovedFontPath = null;
        $fontNames = [
            'beloved-script.ttf',
            'Beloved-script.ttf',
            'beloved-scrip.ttf',
            'BelovedScript.ttf',
            'BelovedScript.otf',
        ];
        foreach ($publicDirs as $publicDir) {
            foreach ($fontNames as $fontName) {
                $candidate = $publicDir . '/assets/fonts/' . $fontName;
                $real = realpath($candidate);
                if ($real && is_file($real) && is_readable($real)) {
                    $belovedFontPath = $real;
                    break 2;
                }
            }
        }

        if ($belovedFontPath && is_readable($belovedFontPath)) {
            $fontUrl = 'file://' . str_replace(DIRECTORY_SEPARATOR, '/', $belovedFontPath);
            $fontMetrics = $dompdf->getFontMetrics();
            $fontMetrics->registerFont(
                ['family' => 'Beloved Script', 'weight' => 'normal', 'style' => 'normal'],
                $fontUrl
            );
            $fontMetrics->registerFont(
                ['family' => 'Beloved Script', 'weight' => 'bold', 'style' => 'normal'],
                $fontUrl
            );
        }

        ob_start();
        include __DIR__ . '/../Views/facture_view.php';
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        // XML Factur-X
        $dateSoin  = date('Ymd', strtotime($data['date_soin']));
        $idFacture = $numeroFactureFormate;
        $prix      = number_format($prixTotal, 2, '.', '');

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100" xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100" xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100">
  <rsm:ExchangedDocumentContext>
    <ram:GuidelineSpecifiedDocumentContextParameter>
      <ram:ID>urn:factur-x.eu:1p0:minimum</ram:ID>
    </ram:GuidelineSpecifiedDocumentContextParameter>
  </rsm:ExchangedDocumentContext>
  <rsm:ExchangedDocument>
    <ram:ID>$idFacture</ram:ID>
    <ram:TypeCode>380</ram:TypeCode>
    <ram:IssueDateTime>
      <udt:DateTimeString format="102">$dateSoin</udt:DateTimeString>
    </ram:IssueDateTime>
  </rsm:ExchangedDocument>
  <rsm:SupplyChainTradeTransaction>
    <ram:ApplicableHeaderTradeAgreement>
      <ram:SellerTradeParty>
        <ram:Name>SweetyDog41</ram:Name>
        <ram:SpecifiedLegalOrganization>
          <ram:ID schemeID="0002">12345678901234</ram:ID>
        </ram:SpecifiedLegalOrganization>
        <ram:PostalTradeAddress>
          <ram:CountryID>FR</ram:CountryID>
        </ram:PostalTradeAddress>
      </ram:SellerTradeParty>
      <ram:BuyerTradeParty>
        <ram:Name>{$data['prenom']} {$data['nom']}</ram:Name>
      </ram:BuyerTradeParty>
    </ram:ApplicableHeaderTradeAgreement>
    <ram:ApplicableHeaderTradeDelivery/>
    <ram:ApplicableHeaderTradeSettlement>
      <ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>
      <ram:SpecifiedTradeSettlementHeaderMonetarySummation>
        <ram:TaxBasisTotalAmount currencyID="EUR">$prix</ram:TaxBasisTotalAmount>
        <ram:GrandTotalAmount currencyID="EUR">$prix</ram:GrandTotalAmount>
        <ram:DuePayableAmount currencyID="EUR">$prix</ram:DuePayableAmount>
      </ram:SpecifiedTradeSettlementHeaderMonetarySummation>
    </ram:ApplicableHeaderTradeSettlement>
  </rsm:SupplyChainTradeTransaction>
</rsm:CrossIndustryInvoice>
XML;

        $writer = new Writer();
        $factureHybride = $writer->generate($pdfOutput, $xml);

        $nomFichier = "Facture_SweetyDog_" . $numeroFactureFormate . ".pdf";
        $dir = __DIR__ . '/../../Factures/' . date('Y') . '/' . date('m') . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $chemin = $dir . $nomFichier;

        file_put_contents($chemin, $factureHybride);

        // Upload N2F
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => new CURLFile($chemin, 'application/pdf', $nomFichier)]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: ApiKey $apiKey"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);

        // Rediriger selon l'origine de l'appel
        if (!empty($_GET['from']) && $_GET['from'] === 'download') {
            $downloadQuery = [];
            $groupQuery = trim((string)($_GET['group'] ?? ''));
            if ($groupQuery !== '') {
                $downloadQuery['group'] = $groupQuery;
            }
            redirect('invoices.download', ['id' => $id_prestation], $downloadQuery);
        } elseif (!empty($_GET['from']) && $_GET['from'] === 'email') {
            $emailQuery = [];
            $groupQuery = trim((string)($_GET['group'] ?? ''));
            if ($groupQuery !== '') {
                $emailQuery['group'] = $groupQuery;
            }

            $returnContext = trim((string)($_GET['return'] ?? ''));
            if ($returnContext !== '') {
                $emailQuery['from'] = $returnContext;
            }

            $clientId = (int)($_GET['client'] ?? 0);
            if ($clientId > 0) {
                $emailQuery['client'] = $clientId;
            }

            redirect('invoices.email', ['id' => $id_prestation], $emailQuery);
        } elseif (!empty($_GET['from']) && $_GET['from'] === 'facturation') {
            $id_proprio = (int)($data['id_proprietaire'] ?? 0);
            redirect('facturation.index', [], ['client' => $id_proprio, 'success' => 1]);
        } else {
            redirect('animals.tracking', ['id' => $data['id_animal']], ['success' => 1]);
        }
    }

    public function email($id = 0)
    {
        $this->requireLogin();

        $id_prestation = (int)$id;
        if ($id_prestation <= 0) {
            redirect('facturation.index', [], ['mail' => 'invalid']);
        }

        $pdo = Database::getConnection();
        $this->ensureInvoiceNumberingSchema($pdo);

        $query = $pdo->prepare("
            SELECT p.id_prestation, p.id_animal, p.date_soin, p.type_soin,
                   a.nom_animal,
                   prop.id_proprietaire, prop.nom, prop.prenom, prop.email,
                   f.numero_facture
            FROM Prestations p
            JOIN Animaux a ON p.id_animal = a.id_animal
            JOIN Proprietaires prop ON a.id_proprietaire = prop.id_proprietaire
            LEFT JOIN Factures f ON f.id_prestation = p.id_prestation
            WHERE p.id_prestation = ?
            LIMIT 1
        ");
        $query->execute([$id_prestation]);
        $prestation = $query->fetch(PDO::FETCH_ASSOC);

        if (!$prestation) {
            $this->redirectAfterEmail($prestation, 'not_found');
        }

        $groupQuery = trim((string)($_GET['group'] ?? ''));
        $returnContext = trim((string)($_GET['from'] ?? ''));
        $returnClientId = (int)($_GET['client'] ?? 0);

        $emailTo = trim((string)($prestation['email'] ?? ''));
        if ($emailTo === '' || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
            $this->redirectAfterEmail($prestation, 'no_email');
        }

        $nomFichier = '';
        $cheminFacture = '';
        $numeroFacture = (int)($prestation['numero_facture'] ?? 0);
        if ($numeroFacture > 0) {
            $numeroFormate = $this->formatInvoiceNumber($numeroFacture);
            $nomFichier = "Facture_SweetyDog_{$numeroFormate}.pdf";
            $cheminFacture = $this->findInvoiceFilePath($nomFichier);
        }

        if ($cheminFacture === '' || !file_exists($cheminFacture)) {
            $annee = date('Y', strtotime((string)$prestation['date_soin']));
            $nomFichierLegacy = "Facture_SweetyDog_{$annee}-{$id_prestation}.pdf";
            $cheminLegacy = $this->findInvoiceFilePath($nomFichierLegacy);
            if ($cheminLegacy !== '') {
                $nomFichier = $nomFichierLegacy;
                $cheminFacture = $cheminLegacy;
            }
        }

        if ($cheminFacture === '' || !file_exists($cheminFacture)) {
            $generateQuery = ['from' => 'email'];
            if ($groupQuery !== '') {
                $generateQuery['group'] = $groupQuery;
            }
            if ($returnContext !== '') {
                $generateQuery['return'] = $returnContext;
            }
            if ($returnClientId > 0) {
                $generateQuery['client'] = $returnClientId;
            }
            redirect('invoices.generate', ['id' => $id_prestation], $generateQuery);
        }

        $clientNom = trim((string)($prestation['prenom'] ?? '') . ' ' . (string)($prestation['nom'] ?? ''));
        $dateFacture = date('d/m/Y', strtotime((string)$prestation['date_soin']));
        $subject = "Votre facture SweetyDog du {$dateFacture}";

        $body = "Bonjour {$clientNom},\n\n";
        $body .= "Veuillez trouver en pièce jointe votre facture de toilettage";
        if (!empty($prestation['nom_animal'])) {
            $body .= " pour " . $prestation['nom_animal'];
        }
        $body .= ".\n\n";
        $body .= "Cordialement,\nSweetyDog";

        $mailSent = $this->sendInvoiceEmail(
            $emailTo,
            $subject,
            $body,
            $cheminFacture,
            $nomFichier !== '' ? $nomFichier : basename($cheminFacture)
        );

        if ($mailSent) {
            unset($_SESSION['mail_error_detail']);
        } else {
            $_SESSION['mail_error_detail'] = $this->lastMailError;
        }

        $this->redirectAfterEmail($prestation, $mailSent ? 'sent' : 'send_error');
    }

    /**
     * Télécharge une facture de manière sécurisée
     * Vérifie l'authentification avant de servir le PDF
     */
    public function download($id = 0)
    {
        // 🔒 SÉCURITÉ : Vérifier que l'utilisateur est connecté
        $this->requireLogin();
        $pdo = Database::getConnection();
        $this->ensureInvoiceNumberingSchema($pdo);

        $id_prestation = (int)$id;
        if ($id_prestation <= 0) {
            http_response_code(404);
            die("Facture introuvable");
        }

        // Vérifier que la prestation existe + récupérer le numéro facture lié
        $query = $pdo->prepare("
            SELECT p.date_soin, f.numero_facture
            FROM Prestations p
            LEFT JOIN Factures f ON f.id_prestation = p.id_prestation
            WHERE p.id_prestation = ?
        ");
        $query->execute([$id_prestation]);
        $prestation = $query->fetch(PDO::FETCH_ASSOC);

        if (!$prestation) {
            http_response_code(404);
            die("Prestation introuvable");
        }

        // Nouveau nom: Facture_SweetyDog_000000001.pdf
        $nomFichier = '';
        $cheminFacture = '';
        $numeroFacture = (int)($prestation['numero_facture'] ?? 0);
        if ($numeroFacture > 0) {
            $numeroFormate = $this->formatInvoiceNumber($numeroFacture);
            $nomFichier = "Facture_SweetyDog_{$numeroFormate}.pdf";
            $cheminFacture = $this->findInvoiceFilePath($nomFichier);
        }

        // Fallback ancien format pour compatibilité des anciennes factures
        if ($cheminFacture === '' || !file_exists($cheminFacture)) {
            $annee = date('Y', strtotime($prestation['date_soin']));
            $nomFichierLegacy = "Facture_SweetyDog_{$annee}-{$id_prestation}.pdf";
            $cheminLegacy = $this->findInvoiceFilePath($nomFichierLegacy);
            if ($cheminLegacy !== '') {
                $nomFichier = $nomFichierLegacy;
                $cheminFacture = $cheminLegacy;
            }
        }

        // Vérifier que le fichier existe
        if (!file_exists($cheminFacture)) {
            $groupQuery = trim((string)($_GET['group'] ?? ''));
            $generateQuery = ['from' => 'download'];
            if ($groupQuery !== '') {
                $generateQuery['group'] = $groupQuery;
            }

            redirect('invoices.generate', ['id' => $id_prestation], $generateQuery);
        }

        // 🔒 SÉCURITÉ SUPPLÉMENTAIRE (optionnel) : 
        // Vérifier que l'utilisateur connecté a le droit d'accéder à cette facture
        // Par exemple, vérifier qu'il s'agit bien de son client
        // À implémenter selon vos besoins

        // Servir le PDF de manière sécurisée
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nomFichier . '"');
        header('Content-Length: ' . filesize($cheminFacture));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        // Lire et envoyer le fichier
        readfile($cheminFacture);
        exit;
    }

    private function ensureInvoiceNumberingSchema(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS Factures (
                id_facture INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                id_prestation INT NOT NULL,
                numero_facture INT UNSIGNED NOT NULL,
                date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_factures_prestation (id_prestation),
                KEY idx_factures_numero (numero_facture)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS InvoiceSequence (
                id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
                next_num INT UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $pdo->exec("INSERT INTO InvoiceSequence (id, next_num) VALUES (1, 1) ON DUPLICATE KEY UPDATE next_num = next_num");

        $maxNumero = (int)$pdo->query("SELECT COALESCE(MAX(numero_facture), 0) FROM Factures")->fetchColumn();
        $minNext = max(1, $maxNumero + 1);
        $stmt = $pdo->prepare("UPDATE InvoiceSequence SET next_num = GREATEST(next_num, :min_next) WHERE id = 1");
        $stmt->execute(['min_next' => $minNext]);
    }

    private function resolveInvoiceNumber(PDO $pdo, array $groupeIds): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $groupeIds), static fn($v) => $v > 0)));
        if (empty($ids)) {
            throw new RuntimeException('Aucune prestation pour la numérotation de facture.');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT MIN(numero_facture) FROM Factures WHERE id_prestation IN ($placeholders)");
        $stmt->execute($ids);
        $existing = (int)$stmt->fetchColumn();

        if ($existing > 0) {
            $this->linkInvoiceNumberToPrestations($pdo, $ids, $existing);
            return $existing;
        }

        $pdo->beginTransaction();
        try {
            $lockStmt = $pdo->query("SELECT next_num FROM InvoiceSequence WHERE id = 1 FOR UPDATE");
            $next = (int)$lockStmt->fetchColumn();
            if ($next <= 0) {
                $next = 1;
            }

            $update = $pdo->prepare("UPDATE InvoiceSequence SET next_num = :next WHERE id = 1");
            $update->execute(['next' => $next + 1]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        $this->linkInvoiceNumberToPrestations($pdo, $ids, $next);
        return $next;
    }

    private function linkInvoiceNumberToPrestations(PDO $pdo, array $ids, int $numeroFacture): void
    {
        $stmt = $pdo->prepare("
            INSERT INTO Factures (id_prestation, numero_facture, date_creation)
            VALUES (:id_prestation, :numero_facture, NOW())
            ON DUPLICATE KEY UPDATE numero_facture = VALUES(numero_facture)
        ");

        foreach ($ids as $idPrest) {
            $stmt->execute([
                'id_prestation' => (int)$idPrest,
                'numero_facture' => $numeroFacture,
            ]);
        }
    }

    private function formatInvoiceNumber(int $numero): string
    {
        return str_pad((string)max(1, $numero), 9, '0', STR_PAD_LEFT);
    }

    private function findInvoiceFilePath(string $fileName): string
    {
        if ($fileName === '') {
            return '';
        }

        $baseDirs = [
            __DIR__ . '/../../Factures',
            __DIR__ . '/../../factures',
        ];

        foreach ($baseDirs as $baseDir) {
            $flatPath = $baseDir . '/' . $fileName;
            if (is_file($flatPath)) {
                return $flatPath;
            }

            $nestedMatches = glob($baseDir . '/*/*/' . $fileName) ?: [];
            foreach ($nestedMatches as $candidate) {
                if (is_file($candidate)) {
                    return $candidate;
                }
            }
        }

        return '';
    }

    private function sendInvoiceEmail(
        string $to,
        string $subject,
        string $body,
        string $attachmentPath,
        string $attachmentName
    ): bool {
        $this->lastMailError = '';

        if (!is_file($attachmentPath) || !is_readable($attachmentPath)) {
            $this->lastMailError = 'attachment_unreadable';
            return false;
        }

        $mailConfig = $this->loadMailConfig();
        $fromName = $mailConfig['MAIL_FROM_NAME'] ?? 'SweetyDog';
        $fromEmailRaw = trim((string)($mailConfig['MAIL_FROM_EMAIL'] ?? ''));
        $transport = strtolower(trim((string)($mailConfig['MAIL_TRANSPORT'] ?? 'mail')));

        $safeFromEmail = filter_var($fromEmailRaw, FILTER_VALIDATE_EMAIL) ? $fromEmailRaw : 'sweetydog41@gmail.com';
        $safeFromName = trim((string)$fromName) !== '' ? trim((string)$fromName) : 'SweetyDog';
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->lastMailError = 'recipient_invalid';
            return false;
        }

        if ($transport === 'smtp') {
            $smtpHost = trim((string)($mailConfig['MAIL_HOST'] ?? ''));
            $smtpPort = (int)($mailConfig['MAIL_PORT'] ?? 587);
            $smtpUser = trim((string)($mailConfig['MAIL_USERNAME'] ?? ''));
            $smtpPass = preg_replace('/\s+/', '', trim((string)($mailConfig['MAIL_PASSWORD'] ?? ''))) ?? '';
            $smtpEncryption = strtolower(trim((string)($mailConfig['MAIL_ENCRYPTION'] ?? 'tls')));

            if ($smtpHost === '' || $smtpPort <= 0 || $smtpUser === '' || $smtpPass === '') {
                $this->lastMailError = 'smtp_config_missing';
                return false;
            }

            if ($fromEmailRaw === '' && filter_var($smtpUser, FILTER_VALIDATE_EMAIL)) {
                $safeFromEmail = $smtpUser;
            }

            $verifyPeerRaw = strtolower(trim((string)($mailConfig['MAIL_SMTP_VERIFY_PEER'] ?? '0')));
            $verifyPeer = in_array($verifyPeerRaw, ['1', 'true', 'yes', 'on'], true);

            $payload = $this->buildInvoiceMailPayload(
                $to,
                $safeFromEmail,
                $safeFromName,
                $subject,
                $body,
                $attachmentPath,
                $attachmentName
            );

            return $this->sendMailViaSmtp(
                $to,
                $safeFromEmail,
                $payload['smtp'],
                $smtpHost,
                $smtpPort,
                $smtpUser,
                $smtpPass,
                $smtpEncryption,
                $verifyPeer
            );

            // Fallback utile sur certains hébergements: si 587/TLS échoue, tenter 465/SSL
            if (!$sent && $smtpPort === 587 && $smtpEncryption === 'tls') {
                $sent = $this->sendMailViaSmtp(
                    $to,
                    $safeFromEmail,
                    $payload['smtp'],
                    $smtpHost,
                    465,
                    $smtpUser,
                    $smtpPass,
                    'ssl',
                    $verifyPeer
                );
            }

            return $sent;
        }

        $payload = $this->buildInvoiceMailPayload(
            $to,
            $safeFromEmail,
            $safeFromName,
            $subject,
            $body,
            $attachmentPath,
            $attachmentName
        );

        $sent = @mail($to, $payload['subject_for_mail'], $payload['body_for_mail'], $payload['headers_for_mail']);
        if (!$sent) {
            $this->lastMailError = 'php_mail_failed';
        }
        return $sent;
    }

    private function loadMailConfig(): array
    {
        $envPath = __DIR__ . '/../../code.env';
        if (!is_file($envPath) || !is_readable($envPath)) {
            return [];
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return [];
        }

        $config = [];
        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim((string)$key);
            $value = trim((string)$value);
            if ($key === '') {
                continue;
            }
            if (
                strlen($value) >= 2 &&
                (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $config[$key] = $value;
        }

        return $config;
    }

    private function buildInvoiceMailPayload(
        string $to,
        string $fromEmail,
        string $fromName,
        string $subject,
        string $body,
        string $attachmentPath,
        string $attachmentName
    ): array {
        $safeTo = str_replace(["\r", "\n"], '', trim($to));
        $safeFromEmail = str_replace(["\r", "\n"], '', trim($fromEmail));
        $safeFromName = str_replace(["\r", "\n"], '', trim($fromName));
        $safeAttachmentName = str_replace(["\r", "\n", '"'], ['', '', ''], trim($attachmentName));

        $subjectEncoded = function_exists('mb_encode_mimeheader')
            ? mb_encode_mimeheader($subject, 'UTF-8', 'Q')
            : $subject;
        $fromNameEncoded = function_exists('mb_encode_mimeheader')
            ? mb_encode_mimeheader($safeFromName, 'UTF-8', 'Q')
            : $safeFromName;

        $boundary = '=_SweetyDog_' . md5((string)microtime(true));
        $fileData = chunk_split(base64_encode((string)file_get_contents($attachmentPath)));

        $multipartBody = '';
        $multipartBody .= '--' . $boundary . "\r\n";
        $multipartBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $multipartBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $multipartBody .= $body . "\r\n\r\n";
        $multipartBody .= '--' . $boundary . "\r\n";
        $multipartBody .= 'Content-Type: application/pdf; name="' . $safeAttachmentName . '"' . "\r\n";
        $multipartBody .= "Content-Transfer-Encoding: base64\r\n";
        $multipartBody .= 'Content-Disposition: attachment; filename="' . $safeAttachmentName . '"' . "\r\n\r\n";
        $multipartBody .= $fileData . "\r\n";
        $multipartBody .= '--' . $boundary . "--\r\n";

        $domain = 'localhost';
        if (strpos($safeFromEmail, '@') !== false) {
            [, $mailDomain] = explode('@', $safeFromEmail, 2);
            if (trim($mailDomain) !== '') {
                $domain = trim($mailDomain);
            }
        }
        try {
            $messageToken = bin2hex(random_bytes(8));
        } catch (Throwable $e) {
            $messageToken = md5(uniqid('swd', true));
        }
        $messageId = sprintf('<%s@sweetydog.%s>', $messageToken, preg_replace('/[^a-z0-9.-]/i', '', $domain));
        $dateHeader = date('r');

        $headersForMail = [];
        $headersForMail[] = 'From: ' . $fromNameEncoded . ' <' . $safeFromEmail . '>';
        $headersForMail[] = 'Reply-To: ' . $safeFromEmail;
        $headersForMail[] = 'MIME-Version: 1.0';
        $headersForMail[] = 'Date: ' . $dateHeader;
        $headersForMail[] = 'Message-ID: ' . $messageId;
        $headersForMail[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

        $smtpHeaders = [];
        $smtpHeaders[] = 'From: ' . $fromNameEncoded . ' <' . $safeFromEmail . '>';
        $smtpHeaders[] = 'To: <' . $safeTo . '>';
        $smtpHeaders[] = 'Subject: ' . $subjectEncoded;
        $smtpHeaders[] = 'Date: ' . $dateHeader;
        $smtpHeaders[] = 'Message-ID: ' . $messageId;
        $smtpHeaders[] = 'MIME-Version: 1.0';
        $smtpHeaders[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

        return [
            'subject_for_mail' => $subjectEncoded,
            'headers_for_mail' => implode("\r\n", $headersForMail),
            'body_for_mail' => $multipartBody,
            'smtp' => implode("\r\n", $smtpHeaders) . "\r\n\r\n" . $multipartBody,
        ];
    }

    private function sendMailViaSmtp(
        string $to,
        string $fromEmail,
        string $smtpData,
        string $host,
        int $port,
        string $username,
        string $password,
        string $encryption,
        bool $verifyPeer = false
    ): bool {
        $encryption = $encryption === 'ssl' ? 'ssl' : 'tls';
        $remoteHost = $encryption === 'ssl' ? ('ssl://' . $host) : $host;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => $verifyPeer,
                'verify_peer_name' => $verifyPeer,
                'allow_self_signed' => !$verifyPeer,
            ],
        ]);

        $socket = @stream_socket_client(
            $remoteHost . ':' . $port,
            $errno,
            $errstr,
            20,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if (!$socket) {
            $this->lastMailError = 'smtp_connect_failed:' . $errno . ':' . $errstr;
            return false;
        }

        stream_set_timeout($socket, 20);

        if (!$this->smtpExpect($socket, [220])) {
            $this->lastMailError = 'smtp_greeting_failed';
            fclose($socket);
            return false;
        }

        $helo = gethostname();
        if (!is_string($helo) || trim($helo) === '') {
            $helo = 'localhost';
        }

        if (!$this->smtpCommand($socket, 'EHLO ' . $helo, [250])) {
            if (!$this->smtpCommand($socket, 'HELO ' . $helo, [250])) {
                $this->lastMailError = 'smtp_helo_failed';
                fclose($socket);
                return false;
            }
        }

        if ($encryption === 'tls') {
            if (!$this->smtpCommand($socket, 'STARTTLS', [220])) {
                $this->lastMailError = 'smtp_starttls_failed';
                fclose($socket);
                return false;
            }

            $cryptoEnabled = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($cryptoEnabled !== true) {
                $this->lastMailError = 'smtp_tls_handshake_failed';
                fclose($socket);
                return false;
            }

            if (!$this->smtpCommand($socket, 'EHLO ' . $helo, [250])) {
                $this->lastMailError = 'smtp_ehlo_after_tls_failed';
                fclose($socket);
                return false;
            }
        }

        if (!$this->smtpCommand($socket, 'AUTH LOGIN', [334])) {
            $this->lastMailError = 'smtp_auth_login_failed';
            fclose($socket);
            return false;
        }
        if (!$this->smtpCommand($socket, base64_encode($username), [334])) {
            $this->lastMailError = 'smtp_auth_username_failed';
            fclose($socket);
            return false;
        }
        if (!$this->smtpCommand($socket, base64_encode($password), [235])) {
            $this->lastMailError = 'smtp_auth_password_failed';
            fclose($socket);
            return false;
        }

        if (!$this->smtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', [250])) {
            $this->lastMailError = 'smtp_mail_from_failed';
            fclose($socket);
            return false;
        }
        if (!$this->smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251])) {
            $this->lastMailError = 'smtp_rcpt_to_failed';
            fclose($socket);
            return false;
        }
        if (!$this->smtpCommand($socket, 'DATA', [354])) {
            $this->lastMailError = 'smtp_data_failed';
            fclose($socket);
            return false;
        }

        $smtpData = preg_replace('/(?m)^\./', '..', $smtpData) ?? $smtpData;
        $written = fwrite($socket, $smtpData . "\r\n.\r\n");
        if ($written === false) {
            $this->lastMailError = 'smtp_write_data_failed';
            fclose($socket);
            return false;
        }

        if (!$this->smtpExpect($socket, [250])) {
            $this->lastMailError = 'smtp_send_failed';
            fclose($socket);
            return false;
        }

        $this->smtpCommand($socket, 'QUIT', [221]);
        $this->lastMailError = '';
        fclose($socket);
        return true;
    }

    private function smtpCommand($socket, string $command, array $expectedCodes): bool
    {
        $written = fwrite($socket, $command . "\r\n");
        if ($written === false) {
            return false;
        }
        return $this->smtpExpect($socket, $expectedCodes);
    }

    private function smtpExpect($socket, array $expectedCodes): bool
    {
        [$code] = $this->smtpReadResponse($socket);
        if ($code <= 0) {
            return false;
        }
        return in_array($code, $expectedCodes, true);
    }

    private function smtpReadResponse($socket): array
    {
        $response = '';
        $code = 0;

        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }

            $response .= $line;
            if (strlen($line) >= 3 && ctype_digit(substr($line, 0, 3))) {
                $code = (int)substr($line, 0, 3);
            }

            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }

        return [$code, $response];
    }

    private function redirectAfterEmail($prestation, string $status): void
    {
        $from = trim((string)($_GET['from'] ?? ''));
        $clientId = (int)($_GET['client'] ?? 0);

        if (is_array($prestation)) {
            if ($clientId <= 0) {
                $clientId = (int)($prestation['id_proprietaire'] ?? 0);
            }
        }

        if ($from === 'facturation') {
            $query = ['mail' => $status];
            if ($clientId > 0) {
                $query['client'] = $clientId;
            }
            redirect('facturation.index', [], $query);
        }

        if (is_array($prestation)) {
            $animalId = (int)($prestation['id_animal'] ?? 0);
            if ($animalId > 0) {
                redirect('animals.tracking', ['id' => $animalId], ['mail' => $status]);
            }
        }

        redirect('declaration.invoices', [], ['mail' => $status]);
    }
}
