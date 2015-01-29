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

            <h2 id="sec0">Bargākie tiesneši</h2>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <td>Vieta</td>
                        <td>Vārds</td> 
                        <td>Uzvārds</td>
                        <td>Sodu koificents</td>                             
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($referees as $referee) {
                        $i++;
                        ?>                    <tr>
                            <td><?php echo $i . '.'; ?></td>
                            <td><?php echo $referee->Name; ?></td> 
                            <td><?php echo $referee->Surname; ?></td>
                            <td><?php echo $referee->count; ?></td>                        
                        </tr> <?php }
                    ?>
                </tbody>
            </table>
        </div> 
    </div>
</div>