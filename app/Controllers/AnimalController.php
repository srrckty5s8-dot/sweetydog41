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

        $nom_animal = trim($_POST['nom_animal'] ?? '');
        $espece     = trim($_POST['espece'] ?? '');
        $race       = trim($_POST['race'] ?? '');
        $poids      = ($_POST['poids'] ?? '') !== '' ? (float)$_POST['poids'] : null;
        $steril     = isset($_POST['steril']) ? (int)$_POST['steril'] : 0;

        if ($nom_animal === '') {
            redirect('animals.edit', ['id' => $id]);
            exit;
        }

        Animal::update($id, [
            'nom_animal' => $nom_animal,
            'espece'     => $espece,
            'race'       => $race,
            'poids'      => $poids,
            'steril'     => $steril,
        ]);

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

        $this->view('suivi_toilettage_view', compact('animal', 'historique'));
        exit;
    }
}
