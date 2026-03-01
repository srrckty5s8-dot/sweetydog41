<?php

class AppointmentController extends Controller
{
    public function index()
    {
        $this->requireLogin();

        $liste_animaux = Animal::getListForAppointments();
        $events = RendezVous::getCalendarEvents();
        $allowedViews = ['dayGridMonth', 'timeGridWeek', 'timeGridDay'];
        $calendarView = $_GET['calendar_view'] ?? '';
        $calendarDate = $_GET['calendar_date'] ?? '';

        if (!in_array($calendarView, $allowedViews, true)) {
            $calendarView = null;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $calendarDate)) {
            $calendarDate = null;
        }

        $this->view('calendrier_view', compact('liste_animaux', 'events', 'calendarView', 'calendarDate'));
    }

    public function create()
    {
        $this->requireLogin();

        $id_animal = (int)($_POST['id_animal'] ?? 0);
        $titre = trim($_POST['titre'] ?? 'Toilettage');
        $debut = $_POST['date_debut'] ?? null;
        $fin = $_POST['date_fin'] ?? null;

        if ($id_animal <= 0 || !$debut || !$fin) {
            http_response_code(400);
            die("Erreur : Des informations sont manquantes (Animal, date de début ou de fin).");
        }

        RendezVous::create([
            'id_animal' => $id_animal,
            'titre' => $titre,
            'date_debut' => $debut,
            'date_fin' => $fin,
        ]);

        $allowedViews = ['dayGridMonth', 'timeGridWeek', 'timeGridDay'];
        $returnView = $_POST['return_view'] ?? '';
        $returnDate = $_POST['return_date'] ?? '';
        $query = [];

        if (in_array($returnView, $allowedViews, true)) {
            $query['calendar_view'] = $returnView;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $returnDate)) {
            $query['calendar_date'] = $returnDate;
        }

        redirect('appointments.index', [], $query);
    }

    public function update($id = 0)
    {
        $this->requireLogin();

        $id = (int)$id;
        if ($id <= 0) {
            redirect('appointments.index');
            exit;
        }

        $titre = trim($_POST['titre'] ?? 'Toilettage');
        $debut = $_POST['date_debut'] ?? null;
        $fin = $_POST['date_fin'] ?? null;

        if (!$debut || !$fin) {
            http_response_code(400);
            die("Erreur : Des informations sont manquantes (date de début ou de fin).");
        }

        RendezVous::update($id, [
            'titre' => $titre,
            'date_debut' => $debut,
            'date_fin' => $fin,
        ]);

        redirect('appointments.index');
    }

    public function delete($id = 0)
    {
        $this->requireLogin();

        $id = (int)$id;
        if ($id <= 0) {
            redirect('appointments.index');
            exit;
        }

        RendezVous::delete($id);

        redirect('appointments.index');
    }
}
