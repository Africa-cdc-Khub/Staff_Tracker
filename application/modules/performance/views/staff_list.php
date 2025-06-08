<div class="container-fluid py-3 px-4">
  
<div class="card shadow-sm border-0">
    <div class="card-body">
    <h5><?=$title?></h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle table-bordered table-striped mydata" id="staffStatsTable">
          <thead class="table-light text-center">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>SAPNO</th>
            
              <th>Status</th>
              <?php if ($type === 'with_pdp'): ?>
                <th>Recommended Trainings</th>
              <?php endif; ?>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php $counter = 1; ?>
            <?php foreach ($staff_list as $staff): ?>
              <tr>
                <td class="text-center"><?= $counter++ ?></td>
                <td><?= $staff->fname . ' ' . $staff->lname ?></td>
                <td><?= $staff->SAPNO ?></td>
                <td><?= $staff->status ?></td>
                <?php if ($type === 'with_pdp'): ?>
                  <td>
                    <?php if (!empty($staff->training_skills)): ?>
                      <ul class="mb-0">
                        <?php foreach ($staff->training_skills as $skill): ?>
                          <li><?= htmlspecialchars($skill, ENT_QUOTES, 'UTF-8') ?>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php else: ?>
                      <span class="text-muted">None</span>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
                <td class="text-center">
                  <?php if (!empty($staff->entry_id)): ?>
                    <a href="<?= base_url('performance/view_ppa/' . $staff->entry_id . '/' . $staff->staff_id) ?>"
                       class="btn btn-sm btn-outline-primary" target="_blank">
                      <i class="fa fa-eye"></i> View PPA
                    </a>
                    
                    
                  


                  <?php else: ?>
                    <span class="text-muted">No PPA</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
