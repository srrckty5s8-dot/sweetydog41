<?php

class SettingsController extends Controller
{
    public function index()
    {
        $this->requireLogin();

        $message = null;
        $erreur = null;
        $userId = (int)($_SESSION['admin_id'] ?? 0);

        if ($userId <= 0) {
            redirect('login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ancien = $_POST['ancien_mdp'] ?? '';
            $nouveau = $_POST['nouveau_mdp'] ?? '';
            $confirm = $_POST['confirm_mdp'] ?? '';

            if ($nouveau === '' || $confirm === '') {
                $erreur = "Veuillez remplir tous les champs.";
            } elseif ($nouveau !== $confirm) {
                $erreur = "Le nouveau mot de passe et la confirmation ne correspondent pas.";
            } else {
                $user = User::findById($userId);
                if (!$user || !password_verify($ancien, $user['mot_de_passe'])) {
                    $erreur = "Ancien mot de passe incorrect.";
                } else {
                    $hash = password_hash($nouveau, PASSWORD_DEFAULT);
                    User::updatePassword($userId, $hash);
                    $message = "Mot de passe mis à jour avec succès !";
                }
            }
        }

        $this->view('parametres_view', compact('message', 'erreur'));
    }
}
