<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class results extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('results_model');
    }

    public function index() {
        $this->load->view('header');
        $teams = $this->results_model->getAllTeams();
        for ($i = 0; $i < count($teams); $i++) {
            $points = $this->getTeamTotalPoints($teams[$i]->Id);
            $teams[$i]->points = $points['points'];
            $teams[$i]->winsComm = $points['winsComm'];
            $teams[$i]->loseComm = $points['loseComm'];
            $teams[$i]->winsExtra = $points['winsExtra'];
            $teams[$i]->loseExtra = $points['loseExtra'];
            $teams[$i]->goals = $this->results_model->getTeamsGoals($teams[$i]->Id);
            $teams[$i]->goalsLose = $points['goalsLose'];
            $teams[$i]->playersCount = $this->results_model->getPlayersCount($teams[$i]->Id);
        }
        $teams = $this->sortByValue($teams, 'points');
        $data['teams'] = $teams;
        $this->load->view('results_view', $data);
        $this->load->view('footer');
    }

    private function sortByValue($data, $val) {
        for ($i = 0; $i < count($data); $i++) {
            for ($j = $i; $j < count($data); $j++) {
                if ($data[$i]->$val < $data[$j]->$val) {
                    $tmp = $data[$j];
                    $data[$j] = $data[$i];
                    $data[$i] = $tmp;
                }
            }
        }
        return $data;
    }

    private function getTeamTotalPoints($team) {
        $data['points'] = 0;
        $data['winsComm'] = 0;
        $data['loseComm'] = 0;
        $data['winsExtra'] = 0;
        $data['loseExtra'] = 0;
        $data['goalsLose'] = 0;
        foreach ($this->results_model->getTeamGames($team) as $game) {
            $thisPoints = $this->results_model->getGamePoints($team, $game->Id);
            $data['points'] += $thisPoints;
            switch ($thisPoints) {
                case 1:
                    $data['loseComm'] ++;
                    break;
                case 2:
                    $data['loseExtra'] ++;
                    break;
                case 3:
                    $data['winsExtra'] ++;
                    break;
                case 5:
                    $data['winsComm'] ++;
                    break;
            }
            $data['goalsLose'] += $this->results_model->goalsLose($team, $game->Id);
        }
        return $data;
    }

    public function players() {
        $this->load->view('header');
        $data['players'] = $this->results_model->getPlayersResults();
        $this->load->view('results_p_view', $data);
        $this->load->view('footer');
    }

    public function guards() {
        $this->load->view('header');
        $data['players'] = $this->results_model->getGuards();
        foreach ($data['players'] as $guard) {
            $guard->coif = $this->getGoalsIn($guard->Id); //overwirte new coif            
        }
        $this->load->view('results_g_view', $data);
        $this->load->view('footer');
    }

    public function rudePlayers() {
        $this->load->view('header');
        $data['players'] = $this->results_model->getRudePlayers();
        $this->load->view('results_r_view', $data);
        $this->load->view('footer');
    }

    public function command($cId = 1) {
        $this->load->view('header');
        $data['players'] = $this->results_model->getCommand($cId);
        for ($i = 0; count($data['players']) > $i; $i++) {
            $data['players'][$i]->minutes = $this->getPlayedTime($data['players'][$i]->Id);
            $data['players'][$i]->goals = $this->results_model->getPlayerGoals($data['players'][$i]->Id);
            $data['players'][$i]->pass = $this->results_model->getPass($data['players'][$i]->Id);
            $data['players'][$i]->yellow = $this->getYellow($data['players'][$i]->Id);
            $data['players'][$i]->red = $this->getRed($data['players'][$i]->Id);
            $data['players'][$i]->goalsIn = $this->getGoalsIn($data['players'][$i]->Id);
        }
        //print_r($data);
        $this->load->view('results_c_view', $data);
        $this->load->view('footer');
    }

    private function getPlayedTime($player) {
        $time = 0;
        //basic time
        $time = $this->results_model->getPlayerGamesBasic($player) * 6000; //speeles laiks var buut garaaks 60+
        $bonusTime = $this->results_model->getBonusTime($player);
        foreach ($bonusTime as $bonus) {
            if (str_replace(':', '', $bonus->count) - 6000 > 0) {
                $time+=str_replace(':', '', $bonus->count) - 6000;
            }
        }
        //time minus
        $changes = $this->results_model->playerChanges($player);
        foreach ($changes as $change) {
            if (str_replace(':', '', $change->maxGoal) < 6000) {
                $time-=6000 - str_replace(':', '', $change->Date);
            } else {
                $time-=str_replace(':', '', $change->maxGoal) - str_replace(':', '', $change->Date);
            }
        }
        //get time plus
        $changes = $this->results_model->playerChangesTo($player);
        foreach ($changes as $change) {
            if (str_replace(':', '', $change->maxGoal) < 6000) {
                $time+=6000 - str_replace(':', '', $change->Date);
            } else {
                $time+=str_replace(':', '', $change->maxGoal) - str_replace(':', '', $change->Date);
            }
        }
        return substr($time, 0, strlen($time) - 2) . ':' . substr($time, -2, 2);
    }

    private function getYellow($player) {
        $count = 0;
        $fines = $this->results_model->getFines($player);
        foreach ($fines as $fine) {
            if ($fine->count == 1) {
                $count++;
            }
        }
        return $count;
    }

    private function getRed($player) {
        $count = 0;
        $fines = $this->results_model->getFines($player);
        foreach ($fines as $fine) {
            if ($fine->count == 2) {
                $count++;
            }
        }
        return $count;
    }

    private function getGoalsIn($player) {
        $count = 0;
        $goals = $this->results_model->getGoalsByPlayer($player);
        $changes = $this->results_model->getChanges($player);
        foreach ($goals as $goal) {
            $count++;
            foreach ($changes as $change) {
                if ($goal->oId == $change->cId) {
                    if (str_replace(':', '', $goal->goalDate) > str_replace(':', '', $change->changeDate)) {
                        $count--; //speeleetaajs nebija uz laukuma
                        continue;
                    }
                }
            }
        }
        $changes = $this->results_model->getChangesTo($player);
        $goals = $this->results_model->getAllGoals($player);

        foreach ($changes as $change) {
            foreach ($goals as $goal) {
                if ($goal->oId == $change->cId) {
                    if (str_replace(':', '', $goal->goalDate) > str_replace(':', '', $change->changeDate)) {
                        $count++; //player was changed                       
                        continue;
                    }
                }
            }
        }
        return $count;
    }

    public function referees() {
        $this->load->view('header');
        $data['referees'] = $this->results_model->getRefereesStat();
        $this->load->view('results_f_view', $data);
        $this->load->view('footer');
    }

}
