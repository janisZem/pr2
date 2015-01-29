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
            <h2 id="sec0">Norādīt XML failu atrašanās vietu</h2>
            <hr class="col-md-12">
            <form action="<?php echo base_url('sync/syncXml'); ?>" method="POST">
                <div class="form-group">
                    <label for="folder">Direktorijas atrašanās vieta</label>
                    <input type="text" class="form-control" name="folder" id="folder" value="xml">
                </div> 
                <button type="submit" class="btn btn-default">Sinhronizēt</button>
            </form>
        </div> 
    </div>
</div>