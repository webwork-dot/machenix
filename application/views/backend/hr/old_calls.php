<style>
   .card-body{
       margin-top: 20px;
   }
</style>


<div class="content-body">
<!-- Basic Horizontal form layout section start -->
<section id="basic-horizontal-layouts">
   <div class="row">
      <div class="col-md-12 col-12">
         <div class="card">
            <div class="card-header border-bottom">
               <h4 class="card-title">Manage Calls - <?php echo date("l F Y h:i:s A");?></span></h4>
            </div>
            <div class="row">
            <div class="col-12 col-md-3"></div>
            <!-- standard plan -->
            <div class="col-12 col-md-6 ">
              <div class="card standard-pricing popular align-items-center">
                <div class="card-body border">
                    <p><i data-feather='user'></i> Dr. Name: <b>Ankit Shukla</b></a></p>
                    <p><i data-feather='phone-call'></i> Mobile: <a href="tel:918355886484">8355886484</a></p>
                    <p><i data-feather='message-square'></i></i> Whatsapp No: <a href="tel:918355886484">8355886484</a></p>
                    <p><i data-feather='mail'></i> Mail: <a href="mailto:vyavaharevaibhav@gmail.com">vyavaharevaibhav@gmail.com</a></p>
                    <p><i data-feather='map'></i>  State: Uttar Pradesh</a></p>
                    <p><i data-feather='map-pin'></i> District: Jaunpur</a></p>
                    <fieldset>
                        <div class="input-group">
                          <button type="button" class="dt-button add-new btn btn-primary w-100 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#not_reachable">Not Reachable</a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#not_responding">Not Responding</a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#number_busy">Number Busy </a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#request_a_call_back">Requested A Call Back </a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#not_interested">Not Interested  </a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#never_equired">Never Enquired  </a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#do_not_disturb">Do Not Disturb  </a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#need_time_decide">Needs Time To Decide </a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#never_responding">Never Responding </a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#other_response">Other Response </a>
                          </div>
                        </div>
                    </fieldset>
                </div>
              </div>
            </div>
            <!--/ standard plan -->
            <div class="col-12 col-md-3"></div>
            </div>
         </div>
      </div>
   </div>
</section>
<!-- Basic Horizontal form layout section end -->

<!-- Add Not Reachable Modal  -->
<div class="modal fade" id="not_reachable" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Not Reachable</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label" for="dateOfBirth">Current Date*</label>
            <input type="text" id="fp-default" class="form-control flatpickr-basic" name="date_birth" value="<?php echo date("d-m-y");?>" placeholder="YYYY-MM-DD">
          </div>

          <div class="col-12">
            <label class="form-label" for="modalAddCardCvv">Follow Up Time (24hrs format)*</label>
            <input type="time" id="modalAddCardCvv" class="form-control add-cvv-code-mask" value="<?php echo time("H:i:s");?>" maxlength="3" placeholder="654">
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Not Reachable Modal  -->


<!-- Add Not Responding modal  -->
<div class="modal fade" id="not_responding" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Not Responding</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label" for="dateOfBirth">Current Date*</label>
            <input type="text" id="fp-default" class="form-control flatpickr-basic" name="date_birth" value="<?php echo date("d-m-y");?>" placeholder="YYYY-MM-DD">
          </div>

          <div class="col-12">
            <label class="form-label" for="modalAddCardCvv">Follow Up Time (24hrs format)*</label>
            <input type="time" id="modalAddCardCvv" class="form-control add-cvv-code-mask" maxlength="3" placeholder="654">
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Not Responding Modal  -->


<!-- Add Number Busy modal  -->
<div class="modal fade" id="number_busy" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Number Busy</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label" for="dateOfBirth">Current Date*</label>
            <input type="text" id="fp-default" class="form-control flatpickr-basic" name="date_birth" value="<?php echo date("d-m-y");?>" placeholder="YYYY-MM-DD">
          </div>

          <div class="col-12">
            <label class="form-label" for="modalAddCardCvv">Follow Up Time (24hrs format)*</label>
            <input type="time" id="modalAddCardCvv" class="form-control add-cvv-code-mask" maxlength="3" placeholder="654">
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Number Busy Modal  -->


<!-- Add Request A Call Back modal  -->
<div class="modal fade" id="request_a_call_back" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Request A Call Back</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label" for="dateOfBirth">Current Date*</label>
            <input type="text" id="fp-default" class="form-control flatpickr-basic" name="date_birth" value="<?php echo date("d-m-y");?>" placeholder="YYYY-MM-DD">
          </div>

          <div class="col-12">
            <label class="form-label" for="modalAddCardCvv">Follow Up Time (24hrs format)*</label>
            <input type="time" id="modalAddCardCvv" class="form-control add-cvv-code-mask" maxlength="3" placeholder="654">
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Request A Call Back Modal  -->


<!-- Add Not Interested modal  -->
<div class="modal fade" id="not_interested" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Not Interested</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Not Interested Modal  -->

<!-- Add Never Equired modal  -->
<div class="modal fade" id="never_equired" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Never Equired</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Never Equired Modal  -->

<!-- Add Do Not Disturb Modal  -->
<div class="modal fade" id="do_not_disturb" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Do Not Disturb</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>
          
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Do Not Disturb Modal  -->

<!-- Add Need Time Decide modal  -->
<div class="modal fade" id="need_time_decide" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Need Time Decide</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label" for="dateOfBirth">Date*</label>
            <input type="text" id="fp-default" class="form-control flatpickr-basic" name="date_birth" value="<?php echo date("d-m-y");?>" placeholder="YYYY-MM-DD">
          </div>

          <div class="col-12">
            <label class="form-label" for="modalAddCardCvv">Follow Up Time (24hrs format)*</label>
            <input type="time" id="modalAddCardCvv" class="form-control add-cvv-code-mask" maxlength="3" placeholder="654">
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Need Time Decide Modal  -->

<!-- Add Sale modal  -->
<div class="modal fade" id="sale" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Sale</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Sale Modal  -->


<!-- Add Sale Under Progress modal  -->
<div class="modal fade" id="sale_under_progress" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Sale Under Progress</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label" for="dateOfBirth">Date*</label>
            <input type="text" id="fp-default" class="form-control flatpickr-basic" name="date_birth" value="<?php echo date("d-m-y");?>" placeholder="YYYY-MM-DD">
          </div>

          <div class="col-12">
            <label class="form-label" for="modalAddCardCvv">Follow Up Time (24hrs format)*</label>
            <input type="time" id="modalAddCardCvv" class="form-control add-cvv-code-mask" maxlength="3" placeholder="654">
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Sale Under Progress Modal  -->

<!-- Add Partial Sale  -->
<div class="modal fade" id="partial_sale" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Sale</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Partial Sale Modal  -->


<!-- Add Never Responding modal  -->
<div class="modal fade" id="never_responding" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Never Responding</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Never Responding Modal  -->

<!-- Add Other Response modal  -->
<div class="modal fade" id="other_response" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <h4 class="text-center mb-1" id="addNewCardTitle">Add In - Other Response</h4>

        <!-- form -->
        <form id="addNewCardValidation" class="row gy-1 gx-2 mt-75" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" id="address" rows="2" name="address" placeholder="Textarea"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label" for="dateOfBirth">Date*</label>
            <input type="text" id="fp-default" class="form-control flatpickr-basic" name="date_birth" value="<?php echo date("d-m-y");?>" placeholder="YYYY-MM-DD">
          </div>

          <div class="col-12">
            <label class="form-label" for="modalAddCardCvv">Follow Up Time (24hrs format)*</label>
            <input type="time" id="modalAddCardCvv" class="form-control add-cvv-code-mask" maxlength="3" placeholder="654">
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ End Other Response Modal  -->