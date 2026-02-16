<div class="main-menu-content">
    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
        <li class="nav-item <?php if ($page_name == 'dashboard')echo 'active';?>">
        	<a class="d-flex align-items-center" href="<?php echo site_url('hr/dashboard'); ?>">
        	<i data-feather="home"></i>
        	<span class="menu-title text-truncate fw-bolder" data-i18n="Dashboards">Dashboard</span>
            </a>
        </li>
       
		<li class="navigation-header"><span>Ongoing Process</span></li>
		
        <li class="nav-item <?php if($page_name == 'calls' || $page_name == 'calls_add' || $page_name == 'calls_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/calls'); ?>">
               <i data-feather="phone-call"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Calls">Calls</span>
           </a>
        </li>
        
        <li class="nav-item <?php if($page_name == 'today_followup' ||  $page_name == 'other_followup') echo 'active'; ?>">
           <a class="d-flex align-items-center" href="#">
               <i data-feather='calendar'></i>
               <span class="menu-title text-truncate" data-i18n="Orders">Followup Calender</span>
           </a>
           <ul class="menu-content">
              <li class="nav-item <?php if($page_name == 'today_followup') echo 'active'; ?>">
                  <a class="d-flex align-items-center" href="<?php echo site_url('hr/today-followup'); ?>">
                      <i data-feather="circle"></i>
                      <span class="menu-item text-truncate" data-i18n="Today Followup">Today Followup</span>
                  </a>
              </li>
              <li class="nav-item <?php if($page_name == 'other_followup') echo 'active'; ?>">
                  <a class="d-flex align-items-center" href="<?php echo site_url('hr/other-followup'); ?>">
                      <i data-feather="circle"></i>
                      <span class="menu-item text-truncate" data-i18n="Other Followup">Other Followup</span>
                  </a>
              </li>
           </ul>
        </li> 
        
        <li class="nav-item <?php if($page_name == 'candidate') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/candidate'); ?>">
               <i data-feather="users"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Candidate List">Candidate List</span>
           </a>
        </li>
 
  
		
		
      <li class="nav-item <?php if($page_name == 'shortlist' || $page_name == 'shortlist_add' || $page_name == 'shortlist_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/shortlist'); ?>">
               <i data-feather='paperclip'></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Shortlist">Shortlist</span>
           </a>
        </li>
        
       <li class="nav-item <?php if($page_name == 'interview_schedule' || $page_name == 'interview_schedule_add' || $page_name == 'interview_schedule_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/interview-schedule'); ?>">
               <i data-feather='clock'></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Interview Schedule">Interview Scheduled</span>
           </a>
        </li>
           

		<li class="nav-item <?php if($page_name == 'documentation' || $page_name == 'approved_documentation' || $page_name == 'verified_documentation') echo 'active'; ?>">
           <a class="d-flex align-items-center" href="#">
               <i data-feather='calendar'></i>
               <span class="menu-title text-truncate" data-i18n="Orders">Documentation</span>
           </a>
           <ul class="menu-content">
              <li class="nav-item <?php if($page_name == 'documentation'  || $page_name == 'verified_documentation') echo 'active'; ?>">
                  <a class="d-flex align-items-center" href="<?php echo site_url('hr/documentation'); ?>">
                      <i data-feather="circle"></i>
                      <span class="menu-item text-truncate" data-i18n="Today Followup">Pending</span>
                  </a>
              </li>
              <li class="nav-item <?php if($page_name == 'approved_documentation') echo 'active'; ?>">
                  <a class="d-flex align-items-center" href="<?php echo site_url('hr/approved-documentation'); ?>">
                      <i data-feather="circle"></i>
                      <span class="menu-item text-truncate" data-i18n="Other Followup">Approved</span>
                  </a>
              </li>
           </ul>
        </li>
        
 		
		
		<li class="navigation-header"><span>Operations</span></li>
		
		
		<li class="nav-item <?php if($page_name == 'holidays_add' || $page_name == 'holidays' || $page_name == 'holidays_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/holidays'); ?>">
               <i data-feather='calendar'></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Exit Form">Manage Holidays</span>
           </a>
        </li>  


        <li class="nav-item <?php if ($page_name == 'update_salary_details' ||  $page_name == 'assign_salary' || $page_name == 'update_staff_details' || $page_name == 'candidate_details')echo 'active';?>">
        	<a class="d-flex align-items-center" href="<?php echo site_url('hr/assign-salary'); ?>">
        	<i class="feather icon-briefcase"></i>
        	<span class="menu-title text-truncate fw-bolder" data-i18n="Assign Salary">Assign Salary</span>
            </a>
       </li>  

	   
        <li class="nav-item <?php if ($page_name == 'staff_list')echo 'active';?>">
        	<a class="d-flex align-items-center" href="<?php echo site_url('hr/staff'); ?>">
        	<i class="feather icon-users"></i>
        	<span class="menu-title text-truncate fw-bolder" data-i18n="Staff"> Manage Staff</span>
           
            </a>
       </li>
 

         <li class="nav-item <?php if($page_name == 'left_staff') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/left-staff'); ?>">
               <i class="fa fa-blind"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Left Staff">Left Staff</span>
           </a>
        </li> 
		
		
		<li class="nav-item <?php if($page_name == 'loans_add' || $page_name == 'loans' || $page_name == 'loans_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/loans'); ?>">
               <i data-feather='credit-card'></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Manage Loans">Manage Loans</span>
           </a>
        </li> 

		<li class="nav-item <?php if($page_name == 'advance_add' || $page_name == 'advance' || $page_name == 'advance_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/advance'); ?>">
               <i data-feather='credit-card'></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Manage Advance">Manage Advance</span>
           </a>
        </li> 

 		<li class="nav-item <?php if($page_name == 'adjustment_add' || $page_name == 'adjustment' || $page_name == 'adjustment_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/adjustment'); ?>">
               <i data-feather='credit-card'></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Manage Adjustment">Manage Adjustment</span>
           </a>
        </li>    

		<li class="nav-item <?php if($page_name == 'tds_add' || $page_name == 'tds' || $page_name == 'tds_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/tds'); ?>">
               <i data-feather='credit-card'></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Manage TDS">Manage TDS</span>
           </a>
        </li>   

		<li class="nav-item <?php if($page_name == 'paidleave_add' || $page_name == 'paidleave' || $page_name == 'paidleave_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('hr/paidleave'); ?>">
               <i data-feather='users'></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="Manage Adjustment">Manage Paid Leave</span>
           </a>
        </li>   
  
		   
		<li class="navigation-header"><span>Manage Attendance</span> </li>
		 
		 <li class="nav-item <?php if($page_name == 'import_attendance') echo 'active'; ?>">
                  <a class="d-flex align-items-center" href="<?php echo site_url('hr/import-attendance'); ?>">
                      <i class="feather icon-upload"></i>
                      <span class="menu-item text-truncate" data-i18n="Import Attendance">Import Attendance</span>
                  </a>
              </li>
              <li class="nav-item <?php if($page_name == 'attendance_list') echo 'active'; ?>">
                  <a class="d-flex align-items-center" href="<?php echo site_url('hr/attendance-list'); ?>">
                      <i class="feather icon-list"></i>
                      <span class="menu-item text-truncate" data-i18n="Other Followup">Attendance List</span>
                  </a>
              </li>   
			  <li class="nav-item <?php if($page_name == 'generate_salary') echo 'active'; ?>">
                  <a class="d-flex align-items-center" href="<?php echo site_url('hr/generate-salary'); ?>">
                      <i class="fa fa-money"></i>
                      <span class="menu-item text-truncate" data-i18n="Other Followup">Generate Salary</span>
                  </a>
              </li>	

		

			  <li class="nav-item <?php if($page_name == 'hold_salary') echo 'active'; ?>">
                  <a class="d-flex align-items-center" href="<?php echo site_url('hr/hold-salary'); ?>">
                      <i class="fa fa-money"></i>
                      <span class="menu-item text-truncate" data-i18n="">Hold Salary</span>
                  </a>
             </li>	

			  <li class="nav-item <?php if($page_name == 'salary_report') echo 'active'; ?>">
                  <a class="d-flex align-items-center" href="<?php echo site_url('hr/salary-report'); ?>">
                      <i class="fa fa-file-excel-o"></i>
                      <span class="menu-item text-truncate" data-i18n="Salary Report">Salary Report</span>
                  </a>
              </li>
			  
		
			  

	<br/>
	<br/>
	<br/>
    </ul>
</div>   

