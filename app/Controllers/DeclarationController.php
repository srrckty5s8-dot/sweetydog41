<?php

class DeclarationController extends Controller
{
    public function index()
    {
        $this->requireLogin();

        require_once __DIR__ . '/../../config/db.php';

        // Mois et année sélectionnés (par défaut : mois courant)
        $mois  = isset($_GET['mois'])  ? (int)$_GET['mois']  : (int)date('n');
        $annee = isset($_GET['annee']) ? (int)$_GET['annee'] : (int)date('Y');

        // Borner les valeurs
        if ($mois < 1 || $mois > 12) $mois = (int)date('n');
        if ($annee < 2020 || $annee > 2040) $annee = (int)date('Y');

        // Récupérer les données
        $caParType     = Declaration::getCAParType($mois, $annee);
        $caTotal       = Declaration::getCATotalMois($mois, $annee);
        $nbPrestations = Declaration::getNbPrestationsMois($mois, $annee);

        $this->view('declaration_view', compact(
            'mois', 'annee', 'caParType', 'caTotal', 'nbPrestations'
        ));
    }

    public function invoices()
    {
        $this->requireLogin();

        $groupedInvoices = $this->collectInvoicesByYearMonth();
        $selectedYear = isset($_GET['annee']) ? (int)$_GET['annee'] : 0;
        $availableYears = array_keys($groupedInvoices);
        $isFromFacturation = trim((string)($_GET['from'] ?? '')) === 'facturation';
        $returnClientId = isset($_GET['client']) ? (int)$_GET['client'] : 0;

        $navigationQuery = [];
        if ($isFromFacturation) {
            $navigationQuery['from'] = 'facturation';
            if ($returnClientId > 0) {
                $navigationQuery['client'] = $returnClientId;
            }
        }

        if ($selectedYear > 0 && isset($groupedInvoices[(string)$selectedYear])) {
            $groupedInvoices = [(string)$selectedYear => $groupedInvoices[(string)$selectedYear]];
        } else {
            $selectedYear = 0;
        }

        $totalInvoices = 0;
        foreach ($groupedInvoices as $months) {
            foreach ($months as $files) {
                $totalInvoices += count($files);
            }
        }

        $this->view('declaration_factures_view', compact(
            'groupedInvoices',
            'selectedYear',
            'availableYears',
            'totalInvoices',
            'isFromFacturation',
            'returnClientId',
            'navigationQuery'
        ));
    }

    public function monthlyRevenue()
    {
        $this->requireLogin();

        $annee = isset($_GET['annee']) ? (int)$_GET['annee'] : (int)date('Y');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $annee = isset($_POST['annee']) ? (int)$_POST['annee'] : $annee;
        }

        if ($annee < 2020 || $annee > 2040) {
            $annee = (int)date('Y');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $statuts = isset($_POST['statut']) && is_array($_POST['statut']) ? $_POST['statut'] : [];
            Declaration::saveUrssafStatutsParAnnee($annee, $statuts);
            redirect('declaration.monthly', [], ['annee' => $annee, 'saved' => 1]);
            return;
        }

        $caMensuel = Declaration::getCAMensuel($annee);
        $statuts = Declaration::getUrssafStatutsParAnnee($annee);

        $rows = [];
        for ($m = 1; $m <= 12; $m++) {
            $rows[] = [
                'mois' => $m,
                'ca' => (float)($caMensuel[$m] ?? 0),
                'statut' => (string)($statuts[$m] ?? 'en_cours'),
            ];
        }

        $caAnnuel = array_sum($caMensuel);
        $saved = isset($_GET['saved']) && (string)$_GET['saved'] === '1';

        $this->view('declaration_monthly_view', compact('annee', 'rows', 'caAnnuel', 'saved'));
    }

    public function openInvoice()
    {
        $this->requireLogin();

        $token = trim((string)($_GET['f'] ?? ''));
        if ($token === '') {
            http_response_code(404);
            die('Facture introuvable');
        }

        $relativePath = $this->decodeInvoicePathToken($token);
        if ($relativePath === '') {
            http_response_code(400);
            die('Chemin de facture invalide');
        }

        $filePath = $this->resolveInvoicePath($relativePath);
        if ($filePath === '' || !is_file($filePath)) {
            http_response_code(404);
            die('Fichier facture introuvable');
        }

        $fileName = basename($filePath);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($filePath);
        exit;
    }

    private function collectInvoicesByYearMonth(): array
    {
        $grouped = [];
        $roots = $this->getInvoiceRoots();

        if (empty($roots)) {
            return $grouped;
        }

        foreach ($roots as $root) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $fileInfo) {
                if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
                    continue;
                }

                if (strtolower((string)$fileInfo->getExtension()) !== 'pdf') {
                    continue;
                }

                $filename = (string)$fileInfo->getFilename();
                if (strpos($filename, 'Facture_SweetyDog_') !== 0) {
                    continue;
                }

                $absolutePath = (string)$fileInfo->getPathname();
                $relativePath = $this->relativePathFromRoot($root, $absolutePath);
                if ($relativePath === '') {
                    continue;
                }

                $year = 0;
                $month = 0;

                if (preg_match('#^(\d{4})/(\d{2})/#', $relativePath, $m)) {
                    $year = (int)$m[1];
                    $month = (int)$m[2];
                }

                if ($year <= 0 && preg_match('/^Facture_SweetyDog_(\d{4})-\d+\.pdf$/', $filename, $m)) {
                    $year = (int)$m[1];
                }

                $mtime = (int)$fileInfo->getMTime();
                if ($year <= 0) {
                    $year = (int)date('Y', $mtime);
                }
                if ($month <= 0 || $month > 12) {
                    $month = (int)date('n', $mtime);
                }

                $yearKey = (string)$year;
                $monthKey = str_pad((string)$month, 2, '0', STR_PAD_LEFT);

                if (!isset($grouped[$yearKey])) {
                    $grouped[$yearKey] = [];
                }
                if (!isset($grouped[$yearKey][$monthKey])) {
                    $grouped[$yearKey][$monthKey] = [];
                }

                $grouped[$yearKey][$monthKey][] = [
                    'name' => $filename,
                    'relative_path' => $relativePath,
                    'token' => $this->encodeInvoicePathToken($relativePath),
                    'size_kb' => round(((int)$fileInfo->getSize()) / 1024, 1),
                    'mtime' => $mtime,
                    'date_label' => date('d/m/Y H:i', $mtime),
                ];
            }
        }

        krsort($grouped, SORT_NUMERIC);
        foreach ($grouped as &$months) {
            krsort($months, SORT_NUMERIC);
            foreach ($months as &$files) {
                usort($files, static function (array $a, array $b): int {
                    return (int)$b['mtime'] <=> (int)$a['mtime'];
                });
            }
            unset($files);
        }
        unset($months);

        return $grouped;
    }

    private function getInvoiceRoots(): array
    {
        $roots = [];
        $candidates = [
            __DIR__ . '/../../Factures',
            __DIR__ . '/../../factures',
        ];

        foreach ($candidates as $candidate) {
            $resolved = realpath($candidate);
            if ($resolved && is_dir($resolved) && !in_array($resolved, $roots, true)) {
                $roots[] = $resolved;
            }
        }

        return $roots;
    }

    private function relativePathFromRoot(string $root, string $absolutePath): string
    {
        $rootNorm = rtrim(str_replace('\\', '/', $root), '/');
        $pathNorm = str_replace('\\', '/', $absolutePath);

        if (strpos($pathNorm, $rootNorm . '/') !== 0) {
            return '';
        }

        return ltrim(substr($pathNorm, strlen($rootNorm)), '/');
    }

    private function encodeInvoicePathToken(string $relativePath): string
    {
        $encoded = base64_encode($relativePath);
        return rtrim(strtr($encoded, '+/', '-_'), '=');
    }

    private function decodeInvoicePathToken(string $token): string
    {
        $normalized = strtr($token, '-_', '+/');
        $padding = strlen($normalized) % 4;
        if ($padding > 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($normalized, true);
        if (!is_string($decoded) || $decoded === '') {
            return '';
        }

        $decoded = str_replace('\\', '/', $decoded);
        $decoded = ltrim($decoded, '/');
        if (strpos($decoded, '..') !== false || strpos($decoded, "\0") !== false) {
            return '';
        }

        return $decoded;
    }

    private function resolveInvoicePath(string $relativePath): string
    {
        foreach ($this->getInvoiceRoots() as $root) {
            $rootNorm = rtrim(str_replace('\\', '/', $root), '/');
            $candidate = realpath($rootNorm . '/' . $relativePath);
            if (!$candidate || !is_file($candidate)) {
                continue;
            }

            $candidateNorm = str_replace('\\', '/', $candidate);
            if (strpos($candidateNorm, $rootNorm . '/') === 0) {
                return $candidate;
            }
        }

        return '';
    }
}
