<?php

class results_model extends CI_model {

    function __construct() {
        parent::__construct();
    }

    //NO ActiveRecords parameters need to be validated
    function getGamePoints($team, $game) {
        return mysql_fetch_row(mysql_query("select 
                    case
                        when
                            (select 
                                    count(1)
                                from
                                    Teams t,
                                    players p,
                                    Goals o
                                where
                                    o.Games_Id = $game and o.Players_Id = p.Id
                                        and p.Teams_Id = t.id
                                        and t.id = $team) > (select 
                                    count(1)
                                from
                                    Teams t,
                                    players p,
                                    Goals o
                                where
                                    o.Games_Id = $game and o.Players_Id = p.Id
                                        and p.Teams_Id = t.id
                                        and t.id = (select 
                                            ifnull(Teams_Id1, Teams_Id)
                                        from
                                            Games
                                        where
                                            Id = $team and (Teams_Id1 != $team
                                                or Teams_Id != $team)))
                        then
                            (select 
                                    case
                                            when
                                                (select 
                                                        max(o.Date)
                                                    from
                                                        Teams t,
                                                        players p,
                                                        Goals o
                                                    where
                                                        o.Games_Id = $game and o.Players_Id = p.Id
                                                            and p.Teams_Id = t.id
                                                            and t.id = $team) < '60:00'
                                            then
                                                '5'
                                            else 3
                                        end
                                from dual)
                        else '1'
                    end
                from dual"))[0];
    }

    function getAllGames() {
        
    }

    function getAllTeams() {
        $query = $this->db->select('*')
                ->from('Teams');
        $results = $query->get()->result();
        return $results;
    }

    function getTeamGames($team) {
        $query = $this->db->select('*')
                ->from('Games')
                ->where('Teams_Id', $team)
                ->or_where('Teams_Id1', $team);
        $results = $query->get()->result();
        return $results;
    }

    function getTeamsGoals($team) {
        $query = $this->db->select('count(1) as count')
                ->from('Goals')
                ->where('Teams_Id', $team);
        $results = $query->get()->result();
        return $results[0]->count;
    }

    //NO ActiveRecords parameters need to be validated
    function goalsLose($team, $game) {
        return mysql_fetch_row(mysql_query("
            select 
                count(1) as count
            from
                Teams t,
                players p,
                Goals o
            where
                o.Games_Id = $game and o.Players_Id = p.Id
                    and p.Teams_Id = t.id
                    and t.id = (select 
                        ifnull(Teams_Id1, Teams_Id)
                    from
                        Games
                    where
                        Id = $game
                and (Teams_Id1 != $team or Teams_Id != $team))"))[0];
    }

    //NO ActiveRecords parameters need to be validated
    function getPlayersResults() {
        $sql = mysql_query("
            select 
                p.Name, 
                    p.Surname,
                    (select count(1) from Goals where p.Id = Players_Id) as goals,
                    (select count(1) from Goals where p.Id = Players_Id1 or  p.Id = Players_Id2) as pass,
                    t.Name as teamName
            from
                Players p, Teams t
            where p.Teams_Id = t.Id
            order by goals desc, pass desc
            limit 10");
        while ($entry = mysql_fetch_object($sql)) {
            $data[] = $entry;
        }
        return $data;
    }

    function getGuards() {
        $sql = mysql_query("
            select 
                p.Id,
                p.Name,
                p.Surname,
                t.name as teamName,
                count(distinct o.Games_Id) goals,
                (select 
                        count(Players_Id)
                    from
                        Basics
                    where
                        Players_Id = p.Id) as count,
                ROUND(count(distinct o.Games_Id) / (select 
                        count(Players_Id)
                    from
                        Basics
                    where
                        Players_Id = p.Id),1) as coif
            from
                Players p,
                Basics b,
                Games g,
                Teams T,
                Goals o
            where
                p.Role = 'V' and p.Id = b.Players_Id
                    and b.Games_Id = g.Id
                    and p.Teams_Id = t.Id
                    and (t.Id = g.Teams_Id or Teams_Id1)
                    and (g.Id = o.Games_Id and o.Teams_Id != T.Id)
            group by p.Name
            order by coif asc
            limit 5");
        while ($entry = mysql_fetch_object($sql)) {
            $data[] = $entry;
        }
        return $data;
    }

    function getRudePlayers() {
        $query = $this->db->select('p.Name, p.Surname, count(f.Players_Id) as count')
                ->from('Players p, Fines f')
                ->where('p.Id = f.Players_Id')
                ->group_by("p.Name")
                ->order_by("count", "desc");
        $results = $query->get()->result();
        return $results;
    }

    function getCommand($command) {
        $sql = mysql_query("
            select 
            distinct p.Name,
            p.Surname,
            p.Number,
            p.Id,
            p.Role,
            (select 
                    count(b.Players_Id)
                from
                    Basics b
                where
                    b.Players_Id = p.Id) + (select 
                    count(c.Players_Id1)
                from
                    Changes c
                where
                    p.Id = c.Players_Id1
                        and p.Id != b.Players_Id) total,
            (select 
                    count(b.Players_Id)
                from
                    Basics b
                where
                    b.Players_Id = p.Id) basic
        from
            Players p,
            Basics b
        where
            p.Teams_Id = $command
        ");
        while ($entry = mysql_fetch_object($sql)) {
            $data[] = $entry;
        }
        return $data;
    }

    function getPlayerGamesBasic($player) {
        $query = $this->db->select('count(distinct g.Id) as count')
                ->from('Basics b, Games g')
                ->where('b.Players_Id', $player)
                ->where('b.Games_Id = g.Id');
        $results = $query->get()->result();
        return $results[0]->count;
    }

    function getBonusTime($player) {
        $data = array();
        $sql = mysql_query("
            select distinct
                (select 
                        max(Date)
                    from
                        Goals
                    where
                        Games_Id = g.Id) as count
            from
                Basics b,
                Games g,
                Goals o
            where
                b.Players_Id = $player and b.Games_Id = g.Id");
        while ($entry = mysql_fetch_object($sql)) {
            $data[] = $entry;
        }
        return $data;
    }

    function playerChanges($player) {
        $data = array();
        $sql = mysql_query("
            select  g.Id,
                    c.Date,
                    (select 
                        max(Date)
                    from
                        Goals
                    where
                        Games_Id = g.Id) maxGoal
            from
                Changes c,
                Games g
            where
                Players_id = $player and c.Games_Id = g.Id");
        while ($entry = mysql_fetch_object($sql)) {
            $data[] = $entry;
        }
        return $data;
    }

    function playerChangesTo($player) {
        $data = array();
        $sql = mysql_query("
            select  g.Id,
                    c.Date,
                    (select 
                        max(Date)
                    from
                        Goals
                    where
                        Games_Id = g.Id) maxGoal
            from
                Changes c,
                Games g
            where
                Players_id1 = $player and c.Games_Id = g.Id");
        while ($entry = mysql_fetch_object($sql)) {
            $data[] = $entry;
        }
        return $data;
    }

    function getPlayerGoals($player) {
        $query = $this->db->select('count(1) as count')
                ->from('Goals')
                ->where('Players_Id', $player);
        $results = $query->get()->result();
        return $results[0]->count;
    }

    function getPass($player) {
        $query = $this->db->select('count(1) as count')
                ->from('Goals')
                ->where('Players_Id1', $player)
                ->or_where('Players_Id2', $player);
        $results = $query->get()->result();
        return $results[0]->count;
    }

    function getFines($player) {
        $data = array();
        $sql = mysql_query("
            select 
                count(f.Players_Id) count
            from
                Players p,
                Teams t,
                Games g,
                Fines f
            where
                p.Id = $player
                    and p.Teams_Id = t.Id
                    and (t.Id = g.Teams_Id or t.Id = g.Teams_Id1)
                    and f.Games_Id = g.Id
                    and f.Players_Id = p.Id
            group by g.Id");
        while ($entry = mysql_fetch_object($sql)) {
            $data[] = $entry;
        }
        return $data;
    }

    function getGoalsByPlayer($player) {
        $query = $this->db->select('o.Date goalDate, g.Id as oId')
                ->from('Players p, Basics b, Games g, Goals o, Teams t')
                ->where('p.Id', $player)
                ->where("p.Role = 'V'")
                ->where('b.Players_Id = p.Id')
                ->where('b.Games_Id = g.Id')
                ->where('o.Games_Id = g.Id')
                ->where('p.Teams_Id = t.Id')
                ->where('t.Id != o.Teams_Id');
        $results = $query->get()->result();
        return $results;
    }

    function getChanges($player) {
        $query = $this->db->select('c.Date changeDate, g.Id as cId, c.Players_Id1 changeTo')
                ->from('Changes c, Games g')
                ->where('c.Players_Id', $player)
                ->where('c.Games_Id = g.Id');
        $results = $query->get()->result();
        return $results;
    }

    /*
      function getChanges($player) {
      $data = array();
      $sql = mysql_query("
      select
      distinct c.Date changeDate, g.Id as cId, c.Players_Id1 changeTo
      from Changes c, Games g, Players p
      where
      c.Players_Id' = $player
      p.Role = 'V' and c.Games_Id = g.Id");
      while ($entry = mysql_fetch_object($sql)) {
      $data[] = $entry;
      }
      return $data;
      } */

    function getChangesTo($player) {
        $query = $this->db->select('c.Date changeDate, g.Id as cId')
                ->from('Changes c, Games g')
                ->where('c.Players_Id1', $player)
                ->where('c.Games_Id = g.Id');
        $results = $query->get()->result();
        return $results;
    }

    /*
      function getChangesTo($player) {
      $data = array();
      $sql = mysql_query("
      select
      distinct c.Date changeDate, g.Id as cId,
      from Changes c, Games g, Players p
      where
      c.Players_Id1' = $player
      p.Role = 'V' and c.Games_Id = g.Id");
      while ($entry = mysql_fetch_object($sql)) {
      $data[] = $entry;
      }
      return $data;
      } */

    function getAllGoals($player) {
        $data = array();
        $sql = mysql_query("
            select 
                o.Date goalDate, g.Id as oId
            from
                Players p,
                Teams T,
                Games g,
                Goals o
            where
                p.Id = $player and p.Role = 'V'
                    and p.Teams_Id = t.Id
                    and (g.Teams_Id = t.Id or g.Teams_Id1 = t.Id)
                    and g.Id = o.Games_Id
                    and t.Id != o.Teams_Id");
        while ($entry = mysql_fetch_object($sql)) {
            $data[] = $entry;
        }
        return $data;
    }

    function getRefereesStat() {
        $data = array();
        $sql = mysql_query("
            select 
                r.Name,
                r.Surname,
                ROUND(count(f.Games_Id) / count(distinct g.Id), 1) as count
            from
                Fines f,
                Games g,
                Referees r
            where
                (r.Id = g.Referees_Id
                    or r.Id = Referees_Id1
                    or r.Id = Referees_Id2)
                    and g.Id = f.Games_Id
            group by r.Id
            order by count desc");
        while ($entry = mysql_fetch_object($sql)) {
            $data[] = $entry;
        }
        return $data;
    }

    function getPlayersCount($team) {
        $query = $this->db->select('count(p.Id) as count')
                ->from('Players p')
                ->where('p.Teams_Id', $team);
        $results = $query->get()->result();
        return $results[0]->count;
    }

}
