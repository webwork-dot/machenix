<?php
$staff_id = $param2;
$staff = $this->db->get_where('sys_users', ['id' => $staff_id])->row_array();

if (empty($staff)) {
    echo '<div class="alert alert-danger">Staff member not found.</div>';
    return;
}

// Get access (designation/staff type) name
$access_name = '-';
if (!empty($staff['staff_access'])) {
    $access = $this->db->get_where('access', ['id' => $staff['staff_access']])->row_array();
    $access_name = $access['name'] ?? '-';
}

// Get company names
$company_names = [];
if (!empty($staff['company_id'])) {
    $comp_ids = explode(',', $staff['company_id']);
    foreach ($comp_ids as $c_id) {
        $c_name = $this->common_model->selectByidParam($c_id, 'company', 'name');
        if ($c_name) {
            $company_names[] = $c_name;
        }
    }
}
$company_names_str = !empty($company_names) ? implode(', ', $company_names) : '-';
?>

<div class="row py-1">
  <div class="col-md-4 text-center mb-2">
    <?php if (!empty($staff['profile_img'])) { ?>
      <img src="<?php echo base_url() . $staff['profile_img']; ?>" class="img-fluid rounded img-thumbnail" style="max-height: 200px; object-fit: cover;" alt="Profile Image">
    <?php } else { ?>
      <div class="rounded bg-light d-flex align-items-center justify-content-center mx-auto" style="height: 180px; width: 180px; border: 1px solid #dee2e6;">
        <i class="fa fa-user fa-5x text-secondary"></i>
      </div>
    <?php } ?>
    <h4 class="mt-2 mb-0"><strong><?php echo htmlspecialchars($staff['first_name'] . ' ' . ($staff['last_name'] ?? '')); ?></strong></h4>
    <span class="badge badge-light-primary mt-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($access_name); ?></span>
  </div>

  <div class="col-md-8">
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-sm">
        <tbody>
          <tr>
            <th style="width: 30%;"><strong>Email:</strong></th>
            <td><?php echo htmlspecialchars($staff['email'] ?? '-'); ?></td>
          </tr>
          <tr>
            <th><strong>Mobile No:</strong></th>
            <td><?php echo htmlspecialchars($staff['phone'] ?? '-'); ?></td>
          </tr>
          <tr>
            <th><strong>Staff Type:</strong></th>
            <td><?php echo htmlspecialchars($access_name); ?></td>
          </tr>
          <tr>
            <th><strong>Company:</strong></th>
            <td><?php echo htmlspecialchars($company_names_str); ?></td>
          </tr>
          <tr>
            <th><strong>Address:</strong></th>
            <td><?php echo nl2br(htmlspecialchars($staff['address'] ?? '-')); ?></td>
          </tr>
          <tr>
            <th><strong>Aadhar No:</strong></th>
            <td>
              <?php echo htmlspecialchars($staff['aadhar_no'] ?? '-'); ?>
              <?php if (!empty($staff['aadhar_photo'])) { ?>
                <div class="mt-1">
                  <a href="<?php echo base_url() . $staff['aadhar_photo']; ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="fa fa-eye"></i> View Aadhar Photo</a>
                </div>
              <?php } ?>
            </td>
          </tr>
          <tr>
            <th><strong>PAN No:</strong></th>
            <td>
              <?php echo htmlspecialchars($staff['pan_no'] ?? '-'); ?>
              <?php if (!empty($staff['pan_photo'])) { ?>
                <div class="mt-1">
                  <a href="<?php echo base_url() . $staff['pan_photo']; ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="fa fa-eye"></i> View PAN Photo</a>
                </div>
              <?php } ?>
            </td>
          </tr>
          <tr>
            <th><strong>Remark:</strong></th>
            <td><?php echo nl2br(htmlspecialchars($staff['remark'] ?? '-')); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
