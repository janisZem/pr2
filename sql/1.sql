select 
    case
        when
            (select 
                    count(1)
                from
                    Teams t,
                    players p,
                    Goals o
                where
                    o.Games_Id = 1 and o.Players_Id = p.Id
                        and p.Teams_Id = t.id
                        and t.id = 1) > (select 
                    count(1)
                from
                    Teams t,
                    players p,
                    Goals o
                where
                    o.Games_Id = 1 and o.Players_Id = p.Id
                        and p.Teams_Id = t.id
                        and t.id = (select 
                            ifnull(Teams_Id1, Teams_Id)
                        from
                            Games
                        where
                            Id = 1 and Teams_Id1 != 1
                                and Teams_Id != 1))
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
                                        o.Games_Id = 1 and o.Players_Id = p.Id
                                            and p.Teams_Id = t.id
                                            and t.id = 1) < '60:00'
                            then
                                '5'
                            else 3
                        end
                from dual)
        else '1'
    end
from dual;

