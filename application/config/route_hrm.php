<?php

/* HR */
$route[$r_hr . '/dashboard']      = 'hr/dashboard';
$route[$r_hr . '/old-calls']      = 'hr/old_calls';
$route[$r_hr . '/calls']          = 'hr/calls';
$route[$r_hr . '/calls/add']      = 'hr/calls_form/calls_add';
$route[$r_hr . '/filter-leads']   = 'hr/filter_leads';
$route[$r_hr . '/today-followup'] = 'hr/today_followup';
$route[$r_hr . '/other-followup'] = 'hr/other_followup';
$route['ajax-candidate-list']     = 'hr/ajax_candidate_list';
$route['ajax-pure-candidate-list']= 'hr/ajax_pure_candidate_list';

$route[$r_hr . '/unshortlist'] = 'hr/candidate_unshortlist';
$route[$r_hr . '/shortlist'] = 'hr/shortlist';
$route[$r_hr . '/interview-schedule'] = 'hr/interview_schedule';
//$route[$r_hr . '/my-staff']  = 'hr/my_staff';
//$route[$r_hr . '/exit-form'] = 'hr/exit_form';

$route[$r_hr . '/candidate']          = 'hr/candidate';
$route[$r_hr . '/candidate/edit/(:any)']          = 'hr/candidate_form/candidate_edit/$1';
$route[$r_hr . '/candidate/documents/(:any)']          = 'hr/candidate_form/candidate_document/$1';

$route[$r_hr . '/documentation']          = 'hr/documentation';
$route[$r_hr . '/approved-documentation'] = 'hr/approved_documentation';
$route[$r_hr . '/verified-documentation'] = 'hr/verified_documentation';
$route[$r_hr . '/candidate-details/(:num)'] = 'hr/candidate_details/$1';
$route[$r_hr . '/update-staff/(:num)'] = 'hr/candidate_form/update_staff/$1';


$route['cd/(:any)'] = 'Candidate_front/check_candidate_document_link/$1';
$route['candidate/documentation/(:any)'] = 'Candidate_front/documentation/$1';
$route['candidate/thank-you'] = 'Candidate_front/thank_you';
$route['candidate/exit-form/(:any)'] = 'Candidate_front/exit_form/$1';

/*HR Head*/
$route[$r_hr . '/assign-salary'] = 'hr/assign_salary';
$route[$r_hr . '/update-salary/(:num)'] = 'hr/update_salary/$1';
$route[$r_hr . '/update-staff-details/(:num)'] = 'hr/update_staff_details/$1';

$route[$r_hr . '/staff'] = 'hr/staff_list';

$route[$r_hr . '/holidays'] 			= 'hr/holidays';
$route[$r_hr . '/holiday/add']   	    = 'hr/holidays_form/add';
$route[$r_hr . '/holiday/edit/(:num)']  = 'hr/holidays_form/edit/$1';

$route[$r_hr . '/left-staff'] 			= 'hr/left_staff';
/*hr head ends*/

/*Attendance Starts*/
$route[$r_hr . '/staff-upcoming-birthday'] = 'attendance/staff_upcoming_birthday';  
/*Attendance Ends*/




/*salary staff starts*/

//Manage Attendance
$route[$r_hr . '/import-attendance'] = 'attendance/attendance_form/import-attendance';
$route[$r_hr . '/attendance-list']   = 'attendance/attendance_form/attendance-list';
$route[$r_hr . '/generate-salary']   = 'attendance/attendance_form/generate-salary';
$route[$r_hr . '/salary-report']     = 'attendance/attendance_form/salary-report';


$route[$r_hr . '/icici-salary-report']   = 'attendance/attendance_form/icici-salary-report';
$route[$r_hr . '/hdfc-salary-report']    = 'attendance/attendance_form/hdfc-salary-report';
$route[$r_hr . '/sbi-salary-report']     = 'attendance/attendance_form/sbi-salary-report';
$route[$r_hr . '/other-bank-salary-report']     = 'attendance/attendance_form/other-bank-salary-report';
$route[$r_hr . '/hold-salary']      = 'attendance/attendance_form/hold-salary';
$route[$r_hr . '/ff-salary-report'] = 'attendance/attendance_form/ff-salary-report';

$route[$r_hr . '/loans'] 		      = 'attendance/loans';
$route[$r_hr . '/loans/add']   	     = 'attendance/loans_form/add';
$route[$r_hr . '/loans/edit/(:num)'] = 'attendance/loans_form/edit/$1';


$route[$r_hr . '/advance'] 		     = 'attendance/advance';
$route[$r_hr . '/advance/add']   	  	 = 'attendance/advance_form/add';
$route[$r_hr . '/advance/edit/(:num)'] = 'attendance/advance_form/edit/$1';

$route[$r_hr . '/adjustment'] 		     = 'attendance/adjustment';
$route[$r_hr . '/adjustment/add']   	  	 = 'attendance/adjustment_form/add';
$route[$r_hr . '/adjustment/edit/(:num)'] = 'attendance/adjustment_form/edit/$1';

$route[$r_hr . '/paidleave'] 		     = 'attendance/paidleave';
$route[$r_hr . '/paidleave/add']   	  	 = 'attendance/paidleave_form/add';
$route[$r_hr . '/paidleave/edit/(:num)'] = 'attendance/paidleave_form/edit/$1';

$route['excel/sample-attendance-excel'] = 'phpspreadsheet/sample_attendance_excel';

$route[$r_hr . '/candidate-details/(:num)'] = 'hr/candidate_details/$1';


$route[$r_hr . '/tds'] 		     = 'attendance/tds';
$route[$r_hr . '/tds/add']   	  	 = 'attendance/tds_form/add';
$route[$r_hr . '/tds/edit/(:num)'] = 'attendance/tds_form/edit/$1';

/*salary staff ends*/
