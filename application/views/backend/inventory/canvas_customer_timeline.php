<?php 

$customers = $this->common_model->getRowById('customer', '*', ['id' => $param2]); 
$customer_history = $this->common_model->getResultById('customer_log', '*', ['customer_id' => $param2]); 
// echo json_encode($customer_history); exit;
$customer_history = array_reverse($customer_history);
?>

<style>
  .history-item:last-child{ margin-bottom: 0; }
  .history-card{
    border: 1px solid #edf0f2;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    overflow: hidden;
    margin: 0 !important;
  }
  .history-card .card-body{ padding: 12px; }

  .history-meta{
    font-size: 12px;
    color: #6c757d;
    white-space: nowrap;
  }
  .history-label{
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
    display: block;
  }
  .history-value{
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    display: block;
    word-break: break-word;
  }
  .history-desc{
    color: #374151;
    font-size: 13px;
    margin-top: 8px;
    background: #f8f9fa;
    padding: 6px 10px;
    border-radius: 4px;
    border-left: 3px solid #7367f0;
  }
  .history-pill{
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 999px;
    white-space: nowrap;
  }
</style>

<?php 
  foreach($customer_history as $history){ 
    $json = [];
    $label = [];
    if($history['json']) {
      $json = json_decode($history['json'], true);
      $label = json_decode($history['label'], true);
    }
?>

<div class="history-item mb-1">
  <div class="card history-card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap" style="gap: 4px;">
        <span class="badge bg-<?php echo isset($label['badge']) ? $label['badge'] : 'secondary'; ?> history-pill"><?php echo isset($label['message']) ? $label['message'] : ''; ?></span>
        <small class="history-meta"><?php echo formatHistoryTime($history['added_date']); ?></small>
      </div>

      <div class="row my-1">
        <div class="col-6">
          <small class="history-label">
            <?php 
              if($history['action'] == "reassign" || $history['action'] == "update") { echo "Updated By"; }
              elseif($history['action'] == "assign") { echo "Assigned To"; }
              elseif($history['action'] == "move") { echo "Moved By"; }
              else { echo "Added By"; }
            ?>
          </small>
          <span class="history-value text-primary">
            <?php 
              if($history['action'] == "assign") {
                echo isset($json['added_by_name']) ? $json['added_by_name'] : $history['added_by_name'];
              } else {
                echo $history['added_by_name'];
              }
            ?>
          </span>
        </div>

        <?php if(!empty($json['status_date']) && strtotime($json['status_date']) > 0){ ?>
          <div class="col-6">
            <small class="history-label">Follow Up Date</small>
            <span class="history-value text-dark">
              <?php echo date('d M, Y h:i A', strtotime($json['status_date'])); ?>
            </span>
          </div>
        <?php } ?>
      </div>

      <?php if(!empty($json['remark'])){ ?>
        <div class="history-desc"><b>Remark:</b> <?php echo nl2br(htmlspecialchars($json['remark'])); ?></div>
      <?php } ?>

    </div>
  </div>
</div>

  
<?php } ?>