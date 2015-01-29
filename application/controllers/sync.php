<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class sync extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('sync_model');
    }

    public function index() {
        $this->load->view('header');
        $this->load->view('sync_view');
        $this->load->view('footer');
    }

    public function syncXml() {
        if (!$this->input->post('folder')) {
            $this->sync_complite("Lūdzu norādiet XML failu direktoriju");
            return;
        }
        $files = $this->readDir($this->input->post('folder'));
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $this->pharseXml($this->input->post('folder') . '/' . $file);
            }
        }
        $this->sync_complite();
    }

    public function sync_complite($msg = "") {
        $this->load->view('header');
        $data['msg'] = $msg;
        $this->load->view('cync_complite', $data);
        $this->load->view('footer');
    }

    private function readDir($dirName) {
        $files = scandir($dirName);
        return $files;
    }

    private function pharseXml($fileName = "") {
        $file = simplexml_load_file($fileName);
        $team[] = $this->saveTeam($this->getTeamsName($file)['team1']);
        $team[] = $this->saveTeam($this->getTeamsName($file)['team2']);
        echo $this->loadGameXml($file)['Date'].'</br>';
        
        if ($this->sync_model->findGame($team[0], $team[1], $this->loadGameXml($file)['Date'])) {
            return; //this game alreay saved, important for saveBasics()
        }
        $referees['VT'] = $this->saveReferee($this->getReferees($file)['VT']);
        $referees['T1'] = $this->saveReferee($this->getReferees($file)['T1']);
        $referees['T2'] = $this->saveReferee($this->getReferees($file)['T2']);
        $gameId = $this->saveGame($team, $referees, $file);
        $this->savePlayers($file);
        $this->saveBasics($file, $gameId);
        $this->saveGoals($file, $gameId);
        $this->saveFines($file, $gameId);
        $this->saveChanges($file, $gameId);
        //print_r($this->getTeamsName($file));
    }

    // return array[team1], array[team2], team name 1 and 2 */
    private function getTeamsName($file) {
        return $array = array(
            "team1" => (string) $file->Komanda[0]["Nosaukums"],
            "team2" => (string) $file->Komanda[1]["Nosaukums"],
        );
    }

    private function getReferees($file) {
        return $array = array(
            "VT" => array('name' => (string) $file->VT['Vards'], 'surname' => (string) $file->VT['Uzvards']),
            "T1" => array('name' => (string) $file->T[0]['Vards'], 'surname' => (string) $file->T[0]['Uzvards']),
            "T2" => array('name' => (string) $file->T[1]['Vards'], 'surname' => (string) $file->T[1]['Uzvards'])
        );
    }

    //Save both teams players if do not exist in databse
    private function savePlayers($file) {
        $teamId = $this->sync_model->findTeam($this->getTeamsName($file)['team1']);
        foreach ($file->Komanda[0]->Speletaji->Speletajs as $player) {
            if (!$this->sync_model->findPlayer($player["Nr"], $teamId)) {
                $this->sync_model->savePlayer($player, $teamId);
            }
        }
        $teamId1 = $this->sync_model->findTeam($this->getTeamsName($file)['team2']);
        foreach ($file->Komanda[1]->Speletaji->Speletajs as $player) {
            if (!$this->sync_model->findPlayer($player["Nr"], $teamId1)) {
                $this->sync_model->savePlayer($player, $teamId1);
            }
        }
    }

    //save both teams players basics
    private function saveBasics($file, $game) {
        $teamId = $this->sync_model->findTeam($this->getTeamsName($file)['team1']);
        foreach ($file->Komanda[0]->Pamatsastavs->Speletajs as $player) {
            $this->sync_model->saveBasic((string) $player['Nr'], $teamId, $game);
        }
        $teamId1 = $this->sync_model->findTeam($this->getTeamsName($file)['team2']);
        foreach ($file->Komanda[1]->Pamatsastavs->Speletajs as $player) {
            $this->sync_model->saveBasic((string) $player['Nr'], $teamId1, $game);
        }
    }

    private function saveGoals($file, $gameId) {
        $teamId = $this->sync_model->findTeam($this->getTeamsName($file)['team1']);
        if ($file->Komanda[0]->Varti->VG) { //only if any goals
            foreach ($file->Komanda[0]->Varti->VG as $VG) {
                $this->sync_model->saveGoal($VG, $gameId, $teamId);
            }
        }
        $teamId1 = $this->sync_model->findTeam($this->getTeamsName($file)['team2']);
        if ($file->Komanda[1]->Varti->VG) { //if any goals
            foreach ($file->Komanda[1]->Varti->VG as $VG) {
                $this->sync_model->saveGoal($VG, $gameId, $teamId1);
            }
        }
    }

    private function saveFines($file, $gameId) {
        $teamId = $this->sync_model->findTeam($this->getTeamsName($file)['team1']);
        if ($file->Komanda[0]->Sodi->Sods) {
            foreach ($file->Komanda[0]->Sodi->Sods as $fine) {
                $this->sync_model->saveFine($fine, $gameId, $teamId);
            }
        }
        $teamId1 = $this->sync_model->findTeam($this->getTeamsName($file)['team2']);
        if ($file->Komanda[1]->Sodi->Sods) {
            foreach ($file->Komanda[1]->Sodi->Sods as $fine) {
                $this->sync_model->saveFine($fine, $gameId, $teamId1);
            }
        }
    }

    private function saveChanges($file, $gameId) {
        $teamId = $this->sync_model->findTeam($this->getTeamsName($file)['team1']);
        if ($file->Komanda[0]->Mainas->Maina) {
            foreach ($file->Komanda[0]->Mainas->Maina as $change) {
                $this->sync_model->saveChange($change, $gameId, $teamId);
            }
        }
        $teamId1 = $this->sync_model->findTeam($this->getTeamsName($file)['team2']);
        if ($file->Komanda[1]->Mainas->Maina) {
            foreach ($file->Komanda[1]->Mainas->Maina as $change) {
                $this->sync_model->saveChange($change, $gameId, $teamId1);
            }
        }
    }

    private function saveTeam($teamName) {
        if ($teamName == "") {
            return;
        }
        $id = $this->sync_model->findTeam($teamName);
        if (!$id) {
            $id = $this->sync_model->saveTeam($teamName);
        }
        return $id;
    }

    private function saveReferee($referee) {
        if ($referee['name'] == "" || $referee['surname'] == "") {
            return;
        }
        $id = $this->sync_model->findReferee($referee['name'], $referee['surname']);
        if (!$id) {
            $id = $this->sync_model->saveReferee($referee['name'], $referee['surname']);
        }
        return $id;
    }

    private function saveGame($teams, $referees, $file) {
        if (!$teams[0] || !$teams[1]) {
            return;
        }
        $id = $this->sync_model->findGame($teams[0], $teams[1], $this->loadGameXml($file)['Date']);        
        if (!$id) {
            $id = $this->sync_model->saveGame($this->loadGameXml($file), $teams, $referees);
        }
        return $id;
    }

    private function loadGameXml($file) {
        return array(
            "Date" => (string) $file["Laiks"],
            "Place" => (string) $file["Vieta"],
            "Audience" => (string) $file["Skatitaji"]
        );
    }

}
