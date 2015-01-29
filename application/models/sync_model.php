<?php

class sync_model extends CI_model {

    function __construct() {
        parent::__construct();
    }

    public function findTeam($teamName) {
        $query = $this->db->select('Id')
                ->from('Teams')
                ->where('Name', $teamName)
                ->limit(1, 0);
        $results = $query->get()->result();
        if (count($results) > 0) {
            return $results[0]->Id;
        }
        return false;
    }

    public function findReferee($name, $surname) {
        $query = $this->db->select('Id')
                ->from('Referees')
                ->where('Name', $name)
                ->where('Surname', $surname)
                ->limit(1, 0);
        $results = $query->get()->result();
        if (count($results) > 0) {
            return $results[0]->Id;
        }
        return false;
    }

    public function findPlayer($number, $teamId) {
        $query = $this->db->select('Id')
                ->from('Players')
                ->where('Number', $number)
                ->where('Teams_Id', $teamId)
                ->limit(1, 0);
        $results = $query->get()->result();
        if (count($results) > 0) {
            return $results[0]->Id;
        }
        return false;
    }

    public function findGame($team1, $team2, $date) {
        $query = $this->db->query(""
                . "select g.Id 
                from Games g 
                WHERE (g.Teams_Id = $team1
                       and g.Teams_Id1 = $team2)
                or (g.Teams_Id = $team2 
                    and g.Teams_Id1 = $team1)
                and g.Date = '$date'");
        if (empty($query->result())) {
            return false;
        }
        return $query->result()[0]->Id;
    }

    function saveTeam($name) {
        $this->db->insert('Teams', array('Name' => $name));
        return $this->db->insert_id();
    }

    function saveReferee($name, $surname) {
        $this->db->insert('Referees', array('Name' => $name, 'Surname' => $surname));
        return $this->db->insert_id();
    }

    function saveGame($game, $teams, $referees) {
        $this->db->insert('Games', array(
            'Date' => $game['Date'],
            'Place' => $game['Place'],
            'Audience' => $game['Audience'],
            'Referees_Id' => $referees['VT'],
            'Referees_Id1' => $referees['T1'],
            'Referees_Id2' => $referees['T2'],
            'Teams_Id' => $teams[0],
            'Teams_Id1' => $teams[1]));
        return $this->db->insert_id();
    }

    function savePlayer($player, $teamId) {
        $this->db->insert('Players', array(
            'Name' => (string) $player['Vards'],
            'Surname' => (string) $player['Uzvards'],
            'Number' => (string) $player['Nr'],
            'Role' => (string) $player['Loma'],
            'Teams_Id' => $teamId));
        return $this->db->insert_id();
    }

    function saveBasic($player, $team, $game) {
        $this->db->insert('Basics', array(
            'Players_Id' => $this->findPlayer($player, $team),
            'Games_Id' => $game));
    }

    function saveGoal($VG, $gameId, $team) {
        $p1Id = null;
        $p2Id = null;
        $id = $this->findPlayer((string) $VG->P[0]['Nr'], $team);
        if ($id) {
            $p1Id = $id;
        }
        $id = $this->findPlayer((string) $VG->P[1]['Nr'], $team);
        if ($id) {
            $p2Id = $id;
        }
        $this->db->insert('Goals', array(
            'Games_Id' => $gameId,
            'Players_Id' => $this->findPlayer((string) $VG['Nr'], $team),
            'Type' => (string) $VG['Sitiens'],
            'Date' => (string) $VG['Laiks'],
            'Players_Id1' => $p1Id,
            'Players_Id2' => $p2Id,
            'Teams_Id' => $team
        ));
    }

    function saveFine($fine, $game, $team) {
        $this->db->insert('Fines', array(
            'Players_Id' => $this->findPlayer((string) $fine['Nr'], $team),
            'Games_Id' => $game,
            'Date' => (string) $fine['Laiks']));
    }

    function saveChange($fine, $game, $team) {
        $this->db->insert('Changes', array(
            'Games_Id' => $game,
            'Players_Id' => $this->findPlayer((string) $fine['Nr1'], $team),
            'Players_Id1' => $this->findPlayer((string) $fine['Nr2'], $team),
            'Date' => (string) $fine['Laiks']));
    }

}
