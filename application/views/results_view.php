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

            <h2 id="sec0">Turnīra satistika</h2>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <td>Vieta</td>
                        <td>Komanda</td> 
                        <td>Punkti</td>
                        <td>Spēļu skaits</td>
                        <td>Uzvaras</td>
                        <td>Zaudējumi</td>
                        <td>Uzvaras pap.</td>
                        <td>Zaudējumi pap.</td>
                        <td>Vārti</td>
                        <td>Ielaisti vārti</td>
                        <td>Spēlētāju skaits</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($teams as $team) {
                        $i++;
                        $gamesCount = $team->winsComm + $team->loseComm + $team->winsExtra + $team->loseExtra;
                        ?>                    <tr>
                            <td><?php echo $i . '.'; ?></td>
                            <td><a href="<?php echo base_url("results/command/$team->Id"); ?>"><?php echo $team->Name; ?></a></td> 
                            <td><?php echo $team->points; ?></td>
                            <td><?php echo $gamesCount; ?></td>
                            <td><?php echo $team->winsComm; ?></td>
                            <td><?php echo $team->loseComm; ?></td>
                            <td><?php echo $team->winsExtra; ?></td>
                            <td><?php echo $team->loseExtra; ?></td>
                            <td><?php echo $team->goals; ?></td>
                            <td><?php echo $team->goalsLose; ?></td>
                            <td><?php echo $team->playersCount; ?></td>
                        </tr> <?php }
                    ?>
                </tbody>
            </table>
        </div> 
    </div>
</div>