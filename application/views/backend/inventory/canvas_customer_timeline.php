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
    border-radius: 0px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.04);
    overflow: hidden;
    margin: 0 !important;
  }
  .history-card .card-body{ padding: 12px; }

  .history-meta{
    font-size: 12px;
    color: #6c757d;
  }
  .history-title{
    font-weight: 600;
    color: #111827;
    margin: 2px 0 6px;
  }
  .history-desc{
    color: #374151;
    font-size: 13px;
    margin: 0;
  }
  .history-pill{
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 999px;
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

<div class="history-item">
  <div class="card history-card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="badge bg-<?php echo $label['badge']; ?> history-pill"><?php echo $label['message']; ?></span>
        <small class="history-meta"><?php echo formatHistoryTime($history['added_date']); ?></small>
      </div>

      <?php if($history['action'] == "create" || $history['action'] == "follow" || $history['action'] == "lost"){ ?>
        <div class="history-title">Added By: <span class="text-primary"><?php echo $history['added_by_name']; ?></span></div>
      <?php } elseif($history['action'] == "reassign" || $history['action'] == "update") { ?>
        <div class="history-title">Updated By: <span class="text-primary"><?php echo $history['added_by_name']; ?></span></div>
      <?php } elseif($history['action'] == "assign") { ?>
        <div class="history-title">Assigned To: <span class="text-primary"><?php echo $json['added_by_name']; ?></span></div>
      <?php } elseif($history['action'] == "move") { ?>
        <div class="history-title">Moved By: <span class="text-primary"><?php echo $history['added_by_name']; ?></span></div>
      <?php } ?>

    </div>
  </div>
</div>

  
<?php } ?>