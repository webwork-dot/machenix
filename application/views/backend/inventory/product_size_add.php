

<div class="mobile_view home">

<div class="content-body">
<div class="row">
  <div class="col-md-12">
    <div class="card">
     
      <div class="card-body py-1 my-0"> 
        <form action="<?php echo site_url('inventory/product_size/add_post');?>" method="post" class="form  add-ajax-form" enctype="multipart/form-data">
          <div class="section">
            <div class="section-body row">
               
               <div class="col-md-4 mt-10">
              <div class="form-group">
                <label class="control-label">Name *:-</label>
                  <input type="text" name="name" id="name" class="form-control" placeholder="Name" required>
                </div>
              </div> 
              
            <div class="col-md-4 mt-10">
                <div class="form-group">
                    <label class="control-label">ID *:- </label>
                    <input type="text" name="color_code" id="color_code" class="form-control" placeholder="ID" required>
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
              
              

              <div class="col-md-12" style="">
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