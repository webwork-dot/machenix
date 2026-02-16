<?php
   $companies = $this->common_model->getSessionCompanies();
   if($companies == '' && $navigation != 'company'){
      $companies = array();
      $this->session->set_flashdata('error_message', 'No company found');
      redirect(site_url('inventory/company'));
   }
   // Get selected company from session
   $selected_company_id = $this->session->userdata('company_id');
   if($selected_company_id == '' || $selected_company_id == null && count($companies) > 0){
      $this->session->set_userdata('company_id', $companies[0]['id']);
      $selected_company_id = $companies[0]['id'];
   }
?>
<style>
  .company-name {
    font-weight: bold;
    color: #000;
  }

  .company-dropdown {
    width: 250px;
  }

  /* 
  .select2-selection.select2-selection--single {
    padding-top: 0;
    padding-bottom: 0;
  } 

  .company-dropdown select {
    padding: 5px 10px;
    border: 1px solid #d8d6de;
    border-radius: 4px;
    background-color: #fff;
    color: #5e5873;
    font-size: 14px;
    cursor: pointer;
    min-width: 150px;
  }
  
  .company-dropdown select:focus {
    outline: none;
    border-color: #7367f0;
  } 
  */

  .header-navbar .navbar-container ul.navbar-nav li {
    line-height: 2.5;
  }

  .bookmark-star {
    margin-right: 20px;
  }
</style>
<nav
  class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light navbar-shadow container-xxl">
  <div class="navbar-container d-flex content">
    <div class="bookmark-wrapper d-flex align-items-center">
      <div class="mr-auto float-left bookmark-wrapper d-flex align-items-center d-md-none d-lg-none">
        <ul class="nav navbar-nav">
          <li class="nav-item mobile-menu d-xl-none mr-auto"><a class="nav-link nav-menu-main menu-toggle" href="#"><i
                class="ficon feather icon-menu"></i></a></li>
        </ul>
        <b><?php echo $page_title; ?></b>

        <div class="company-dropdown">
          <select id="company-select-mobile" class="form-select select2" onchange="handleCompanyChange(this.value)">
            <!-- <option value="" <?php echo (empty($selected_company_id) || $selected_company_id == 0) ? 'selected' : ''; ?>>Select Company</option> -->
            <?php foreach($companies as $company): ?>
            <option value="<?php echo $company['id']; ?>" <?php echo ($selected_company_id == $company['id']) ? 'selected' : ''; ?>><?php echo $company['name']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

      </div>

      <ul class="nav navbar-nav">
        <li class="nav-item d-none d-lg-block">

          <a class="nav-link bookmark-star fw-bolder" style="float: right;">
            <?php echo $page_title; ?></a>
        </li>

      </ul>
    </div>

    <ul class="nav navbar-nav align-items-center ms-auto">
      <li class="nav-item d-none d-lg-block">
        <div class="company-dropdown position-relative">
          <p class="mb-0 position-absolute" style="left: -43%; top: 23%;"><small>Select Company</small></p>
          <select id="company-select-desktop" class="form-select select2 " onchange="handleCompanyChange(this.value)">
            <!-- <option value="" <?php echo (empty($selected_company_id) || $selected_company_id == 0) ? 'selected' : ''; ?>>Select Company</option> -->
            <?php foreach($companies as $company): ?>
            <option value="<?php echo $company['id']; ?>" <?php echo ($selected_company_id == $company['id']) ? 'selected' : ''; ?>><?php echo $company['name']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </li>

      <li class="nav-item d-none d-lg-block hidden"><a class="nav-link nav-link-style"><i class="ficon"
            data-feather="moon"></i></a></li>

      <li class="nav-item dropdown dropdown-user">
        <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="#" data-bs-toggle="dropdown"
          aria-haspopup="true" aria-expanded="false">

          <div class="user-nav d-sm-flex d-none">
            <span class="user-name fw-bolder">
              <?php echo ucwords($this->session->userdata('super_name')); ?>

            </span><span class="user-status">
              <?php if(($this->session->userdata('super_type')) == 'admin'){
					        echo 'Admin';
                     } else if(($this->session->userdata('super_type')) == 'inventory'){
                        echo 'Admin';
                     } else if(($this->session->userdata('super_type')) == 'staff'){
                        echo 'Staff';
                     } 
                     else{
                        echo 'Admin';
                     }
					    ?>
            </span>
          </div>
          <span class="avatar"><img class="round" src="<?php echo base_url('uploads/user_image/placeholder.png'); ?>"
              alt="avatar" height="40" width="40"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-end" style="top: 46px!important;">
          <a style="padding: 10px" class="dropdown-item" href="<?php echo site_url('inventory/system-password/' . $this->session->userdata('super_user_id')); ?>"><i class="feather icon-user"></i> <?php echo get_phrase('change_password'); ?></a>
          
          <?php if($this->session->userdata('super_type') == 'Inventory') {?>
            <a style="padding: 10px" class="dropdown-item" href="<?php echo site_url('inventory/company'); ?>"><i class="feather icon-briefcase"></i> <?php echo get_phrase('company'); ?></a>

            <a style="padding: 10px" class="dropdown-item" href="<?php echo site_url('inventory/category'); ?>"><i class="feather icon-settings"></i> <?php echo get_phrase('settings'); ?></a>
          <?php } ?>

          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="<?php echo site_url('login/logout'); ?>"><i class="feather icon-power"></i>
            Logout</a>
        </div>

      </li>

    </ul>
  </div>
</nav>

<script>
function handleCompanyChange(companyId) {
  // If empty, set to 0
  if (!companyId) {
    companyId = 0;
  }
  
  console.log('Selected Company ID:', companyId);
  
  // Make AJAX call to set company in session
  $.ajax({
    url: '<?php echo site_url("inventory/set_company"); ?>',
    type: 'POST',
    data: {
      company_id: companyId
    },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'success') {
        console.log('Company set successfully');
        // Redirect to inventory dashboard
        window.location.href = '<?php echo site_url("inventory/dashboard"); ?>';
      } else {
        console.error('Error setting company:', response.message);
        alert('Error setting company. Please try again.');
      }
    },
    error: function(xhr, status, error) {
      console.error('AJAX Error:', error);
      alert('Error setting company. Please try again.');
    }
  });
}

</script>