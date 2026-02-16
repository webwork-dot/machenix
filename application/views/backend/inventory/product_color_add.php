
  <style type="text/css">
    .select2{
      padding: 0px;
      height: auto !important;
    }
    .select2-selection{
      min-height: auto !important;
    }
    .error{font-color: 12px; color: red;}           
     #email-err, #mobile-number-err{
      color:red;
      font-color:14px;
    }
  </style>

<div class="mobile_view home">

<div class="content-body">
<div class="row">
  <div class="col-md-12">
    <div class="card">

      <div class="card-body"> 
        <form action="<?php echo site_url('inventory/product_color/add_post');?>" method="post" class="form  add-ajax-form" enctype="multipart/form-data">
          <div class="section">
            <div class="section-body ">
               
              <div class="col-md-4 mt-10">
              <div class="form-group">
                <label class="control-label">Name*:-</label>
                  <input type="text" name="name" id="name" class="form-control" placeholder="Name" required>
                </div>
              </div>
       
              <div class="col-md-4">        
              <div class="form-group">
                   <label for="code">Color Code</label>
                   <input type="color" class="form-control" name="color_code" id="code" placeholder="Code">
                </div>
              </div>   
                
            <div class="col-md-4">
              <div class="form-group">
               <label class="control-label">Status:-</label>
               <select id="status" class="form-control" name="status">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
             </div>
            </div>

              <div class="col-md-12" style="margin-bottom: 20px;">
              <div class="form-group">
                <button type="submit" class="btn btn-primary btn_verify" name= "btn_verify">Submit</button>
                </div>
              </div>
              
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>
  </div>
</div>

