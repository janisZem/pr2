<!-- Begin Body -->
<div class="container">
    <div class="row">
        <!--      <div class="col-md-3" id="leftCol">

                  <div class="well"> 
                      <ul class="nav nav-stacked" id="sidebar">
                          <li><a href="#sec1">Section 1</a></li>
                          <li><a href="#sec2">Section 2</a></li>
                          <li><a href="#sec3">Section 3</a></li>
                          <li><a href="#sec4">Section 4</a></li>
                      </ul>
                  </div>

              </div> -->  
        <div class="col-md-9">

            <h2 id="sec0">Komandas satistika</h2>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <td>Vieta</td>
                        <td>Vārds</td> 
                        <td>Uzvārds</td>
                        <td>Numurs</td>
                        <td>Spēlēts kopā</td>      
                        <td>Spēlēts papildlaikā</td>
                        <td>Nospēlētais laiks</td>
                        <td>Vārtu skaits</td>
                        <td>Piespēles</td>
                        <td>Dzeltenās kartītes</td>
                        <td>Sarkanās kartītes</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($players as $player) {
                        if ($player->Role == 'V') {
                            continue;
                        }
                        if ($player->minutes == ':0') {
                            $player->minutes = '00:00';
                        }
                        $i++;
                        ?>                    <tr>
                            <td><?php echo $i . '.'; ?></td>
                            <td><?php echo $player->Name; ?></td> 
                            <td><?php echo $player->Surname; ?></td>
                            <td><?php echo $player->Number; ?></td>
                            <td><?php echo $player->total; ?></td>   
                            <td><?php echo $player->basic; ?></td> 
                            <td><?php echo $player->minutes; ?></td> 
                            <td><?php echo $player->goals; ?></td> 
                            <td><?php echo $player->pass; ?></td> 
                            <td><?php echo $player->yellow; ?></td> 
                            <td><?php echo $player->red; ?></td> 
                        </tr> <?php }
                    ?>
                </tbody>
            </table>
        </div> 
        <div class="col-md-9">

            <h2 id="sec0">Vartusargu satistika</h2>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <td>Vieta</td>
                        <td>Vārds</td> 
                        <td>Uzvārds</td>
                        <td>Numurs</td>
                        <td>Spēlēts kopā</td>      
                        <td>Spēlēts papildlaikā</td>
                        <td>Nospēlētais laiks</td>
                        <td>Ielaistie vārti</td>
                        <td>Koificents</td>
                        <td>Dzeltenās kartītes</td>
                        <td>Sarkanās kartītes</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($players as $player) {
                        if ($player->Role != 'V') {
                            continue;
                        }
                        $i++;
                        if ($player->total) {
                            $coif = $player->goalsIn / $player->total;
                        } else {
                            $coif = 0;
                        }
                        if ($player->minutes == ':0') {
                            $player->minutes = '00:00';
                        }
                        ?>                    <tr>
                            <td><?php echo $i . '.'; ?></td>
                            <td><?php echo $player->Name; ?></td> 
                            <td><?php echo $player->Surname; ?></td>
                            <td><?php echo $player->Number; ?></td>
                            <td><?php echo $player->total; ?></td>   
                            <td><?php echo $player->basic; ?></td> 
                            <td><?php echo $player->minutes; ?></td> 
                            <td><?php echo $player->goalsIn; ?></td> 
                            <td><?php echo $coif; ?></td> 
                            <td><?php echo $player->yellow; ?></td> 
                            <td><?php echo $player->red; ?></td> 
                        </tr> <?php }
                    ?>
                </tbody>
            </table>
        </div> 
    </div>
</div>