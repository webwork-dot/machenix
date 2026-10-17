<?php
/**
 * Recursive access tree node renderer.
 * Expects: $node, $selected_access, $depth
 */
if (!isset($selected_access) || !is_array($selected_access)) {
    $selected_access = array();
}
if (!isset($depth)) {
    $depth = 0;
}

$node_id = (int)$node['id'];
$is_checked = in_array($node_id, $selected_access, true);
$has_tabs = !empty($node['tabs']);
$has_columns = !empty($node['columns']);
$has_actions = !empty($node['actions']);
$has_datatypes = !empty($node['datatypes']);
$has_content = $has_tabs || $has_columns || $has_actions;
$type = $node['type'];
$is_module = ($type === 'module');
$is_tab = ($type === 'tabs');

$selected_datatype_id = 0;
if ($has_datatypes) {
    foreach ($node['datatypes'] as $dt) {
        $dt_id = (int)$dt['id'];
        if (in_array($dt_id, $selected_access, true)) {
            $selected_datatype_id = $dt_id;
            break; // single select — first match only
        }
    }
}
?>

<div class="am-node <?= $is_module ? 'am-module' : ($is_tab ? 'am-tab' : 'am-item'); ?>" data-id="<?= $node_id; ?>" data-depth="<?= (int)$depth; ?>">
    <div class="am-node-header">
        <label class="am-check">
            <input
                type="checkbox"
                class="am-checkbox am-node-check"
                name="access_id[]"
                value="<?= $node_id; ?>"
                id="access_<?= $node_id; ?>"
                data-id="<?= $node_id; ?>"
                <?= $is_checked ? 'checked' : ''; ?>
            >
            <span class="am-check-box"></span>
            <span class="am-title"><?= htmlspecialchars($node['name']); ?></span>
        </label>

        <?php if ($has_datatypes): ?>
            <div class="am-datatype-dd" data-node="<?= $node_id; ?>">
                <select
                    class="am-datatype-select <?= $selected_datatype_id ? 'has-value' : ''; ?>"
                    name="access_id[]"
                    data-parent="<?= $node_id; ?>"
                >
                    <option value="">Data Type</option>
                    <?php foreach ($node['datatypes'] as $dt): ?>
                        <?php $dt_id = (int)$dt['id']; ?>
                        <option value="<?= $dt_id; ?>" <?= ($selected_datatype_id === $dt_id) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($dt['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if (!$is_module): ?>
            <span class="am-type-badge am-type-<?= htmlspecialchars($type); ?>">
                <?= $is_tab ? 'Tab' : ucfirst($type); ?>
            </span>
        <?php endif; ?>
        <?php if ($has_content): ?>
            <button type="button" class="am-toggle" aria-label="Toggle" data-target="am-body-<?= $node_id; ?>">
                <i class="fa fa-chevron-down"></i>
            </button>
        <?php endif; ?>
    </div>

    <?php if ($has_content): ?>
        <div class="am-node-body" id="am-body-<?= $node_id; ?>">

            <?php if ($has_tabs): ?>
                <div class="am-tabs-wrap">
                    <?php foreach ($node['tabs'] as $tab): ?>
                        <?php
                            $this->load->view('backend/inventory/partials/access_tree_node', array(
                                'node' => $tab,
                                'selected_access' => $selected_access,
                                'depth' => $depth + 1,
                            ));
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>

                <?php if ($has_columns): ?>
                    <div class="am-group am-group-columns">
                        <div class="am-group-head">
                            <span class="am-group-label"><i class="fa fa-columns"></i> Columns</span>
                            <label class="am-select-all">
                                <input type="checkbox" class="am-group-toggle" data-group="columns-<?= $node_id; ?>">
                                <span>Select all</span>
                            </label>
                        </div>
                        <div class="am-chip-grid" data-group="columns-<?= $node_id; ?>">
                            <?php foreach ($node['columns'] as $col): ?>
                                <?php $col_id = (int)$col['id']; ?>
                                <label class="am-chip">
                                    <input
                                        type="checkbox"
                                        class="am-checkbox am-leaf"
                                        name="access_id[]"
                                        value="<?= $col_id; ?>"
                                        id="access_<?= $col_id; ?>"
                                        data-parent="<?= $node_id; ?>"
                                        <?= in_array($col_id, $selected_access, true) ? 'checked' : ''; ?>
                                    >
                                    <span><?= htmlspecialchars($col['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($has_actions): ?>
                    <div class="am-group am-group-actions">
                        <div class="am-group-head">
                            <span class="am-group-label"><i class="fa fa-bolt"></i> Actions</span>
                            <label class="am-select-all">
                                <input type="checkbox" class="am-group-toggle" data-group="actions-<?= $node_id; ?>">
                                <span>Select all</span>
                            </label>
                        </div>
                        <div class="am-chip-grid" data-group="actions-<?= $node_id; ?>">
                            <?php foreach ($node['actions'] as $act): ?>
                                <?php $act_id = (int)$act['id']; ?>
                                <label class="am-chip am-chip-action">
                                    <input
                                        type="checkbox"
                                        class="am-checkbox am-leaf"
                                        name="access_id[]"
                                        value="<?= $act_id; ?>"
                                        id="access_<?= $act_id; ?>"
                                        data-parent="<?= $node_id; ?>"
                                        <?= in_array($act_id, $selected_access, true) ? 'checked' : ''; ?>
                                    >
                                    <span><?= htmlspecialchars($act['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
