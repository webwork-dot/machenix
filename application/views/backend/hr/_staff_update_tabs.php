
<div class="row"> 
  <div class="col-12 mx-tabs">
   <div class="card-body nev-card"> 
      <ul class="nav nav-tabs nav-tabs-solid mb-0" id="scroll-1">  
         <li class="nav-item"><a class="nav-link <?php if($page_name == 'update_salary_details') echo 'active'; ?>" href="<?= base_url().'hr/update-salary/'.$id;?>">Payroll Details</a></li>
         <li class="nav-item"><a class="nav-link <?php if($page_name == 'update_staff_details') echo 'active'; ?>" href="<?= base_url().'hr/update-staff-details/'.$id;?>">Other Details</a></li>
     
     </ul>
   </div>
  </div> 
</div>