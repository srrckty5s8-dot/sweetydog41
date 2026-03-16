<?php

class AnimalController extends Controller
{
    public function edit($id = 0)
    {
        $this->requireLogin();

        $id = (int)$id;
        if ($id <= 0) {
            redirect('clients.index');
            exit;
        }

        $animal = Animal::findWithOwner($id);
        if (!$animal) {
            redirect('clients.index');
            exit;
        }

        $this->view('modifier_animal_view', compact('animal'));
    }

    public function update($id = 0)
    {
        $this->requireLogin();

        $id = (int)$id;
        if ($id <= 0) {
            redirect('clients.index');
            exit;
        }

        $nom_animal     = trim($_POST['nom_animal'] ?? '');
        $espece         = trim($_POST['espece'] ?? '');
        $race           = trim($_POST['race'] ?? '');
        $poids          = ($_POST['poids'] ?? '') !== '' ? (float)$_POST['poids'] : null;
        $steril         = isset($_POST['steril']) ? (int)$_POST['steril'] : 0;
        $sexe           = trim($_POST['sexe'] ?? '');
        $date_naissance = trim($_POST['date_naissance'] ?? '');

        if ($nom_animal === '') {
            redirect('animals.edit', ['id' => $id]);
            exit;
        }

        Animal::update($id, [
            'nom_animal'     => $nom_animal,
            'espece'         => $espece,
            'race'           => $race,
            'poids'          => $poids,
            'steril'         => $steril,
            'sexe'           => $sexe ?: null,
            'date_naissance' => $date_naissance ?: null,
        ]);

        redirect('clients.index');
        exit;
    }

    public function delete($id = 0)
    {
        $this->requireLogin();

        $id = (int)$id;
        if ($id <= 0) {
            redirect('clients.index');
            exit;
        }

        Animal::delete($id);
        redirect('clients.index');
        exit;
    }

    public function tracking($id = 0)
    {
        $this->requireLogin();

        $id = (int)$id;
        if ($id <= 0) {
            redirect('clients.index');
            exit;
        }

        $animal = Animal::findWithOwner($id);
        if (!$animal) {
            redirect('clients.index');
            exit;
        }

        // TODO: remplacer par la vraie requête historique
        $historique = Soin::findByAnimal($id);
        $prochains_rdv = RendezVous::getUpcomingByAnimal($id, 6);

        $this->view('suivi_toilettage_view', compact('animal', 'historique', 'prochains_rdv'));
        exit;
    }

    public function updateComment($id = 0)
    {
        $this->requireLogin();

        header('Content-Type: application/json; charset=utf-8');

        $id = (int)$id;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID animal invalide']);
            exit;
        }

        $animal = Animal::findWithOwner($id);
        if (!$animal) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Animal introuvable']);
            exit;
        }

        $commentaire = trim((string)($_POST['commentaire'] ?? ''));

        try {
            $ok = Animal::updateComment($id, $commentaire);
            if (!$ok) {
                throw new RuntimeException('Échec de la sauvegarde');
            }

            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Impossible de sauvegarder le commentaire',
                'error' => $e->getMessage(),
            ]);
        }

        exit;
    }
}
