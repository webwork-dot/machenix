<?php
if (!isset($selected_access) || !is_array($selected_access)) {
    $selected_access = array();
}
if (!isset($access_tree) || !is_array($access_tree)) {
    $access_tree = array();
}
?>

<style>
:root {
    --am-border: #e6e9ef;
    --am-bg: #f7f8fb;
    --am-card: #ffffff;
    --am-text: #1f2937;
    --am-muted: #6b7280;
    --am-accent: #1e652e;
    --am-accent-soft: #e8f5eb;
    --am-tab: #2563eb;
    --am-tab-soft: #eff6ff;
    --am-action: #7c3aed;
    --am-action-soft: #f5f3ff;
    --am-datatype: #b45309;
    --am-datatype-soft: #fffbeb;
}

.am-wrap {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.am-col {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.am-wrap > .am-empty {
    flex: 1 1 100%;
}

@media (max-width: 991px) {
    .am-wrap {
        flex-direction: column;
    }
}

.am-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    background: var(--am-bg);
    border: 1px solid var(--am-border);
    border-radius: 8px;
    margin-bottom: 2px;
}

.am-toolbar .am-hint {
    color: var(--am-muted);
    font-size: 12px;
    margin: 0;
}

.am-toolbar-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.am-toolbar-actions .btn {
    border-radius: 6px;
    font-size: 12px;
    padding: 3px 8px;
    line-height: 1.3;
}

.am-node {
    background: var(--am-card);
    border: 1px solid var(--am-border);
    border-radius: 8px;
    overflow: visible;
}

.am-node > .am-node-body {
    overflow: hidden;
    border-radius: 0 0 8px 8px;
}

.am-module {
    box-shadow: none;
}

.am-tabs-wrap {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 2px 0 0;
}

.am-tab {
    background: #fcfcfd;
    border-color: #dbe3f0;
}

.am-node-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    background: linear-gradient(180deg, #fff 0%, #fafbfc 100%);
}

.am-module > .am-node-header {
    background: linear-gradient(180deg, #f4faf5 0%, #fff 100%);
}

.am-tab > .am-node-header {
    background: linear-gradient(180deg, #f5f8ff 0%, #fff 100%);
}

.am-check {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    cursor: pointer;
    flex: 1;
    min-width: 0;
}

.am-checkbox {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.am-check-box {
    width: 15px;
    height: 15px;
    border: 1.5px solid #c5ccd6;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: #fff;
    transition: all .15s ease;
}

.am-check-box::after {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 1px;
    background: transparent;
    transform: scale(0);
    transition: transform .12s ease;
}

.am-checkbox:checked + .am-check-box,
.am-chip input:checked + span {
    border-color: var(--am-accent);
}

.am-checkbox:checked + .am-check-box {
    background: var(--am-accent);
    border-color: var(--am-accent);
}

.am-checkbox:checked + .am-check-box::after {
    background: #fff;
    transform: scale(1);
}

.am-checkbox:indeterminate + .am-check-box {
    background: #fff;
    border-color: var(--am-accent);
}

.am-checkbox:indeterminate + .am-check-box::after {
    width: 8px;
    height: 2px;
    border-radius: 1px;
    background: var(--am-accent);
    transform: scale(1);
}

.am-title {
    font-weight: 600;
    color: var(--am-text);
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.am-type-badge {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .02em;
    padding: 2px 6px;
    border-radius: 999px;
    background: #eef2f7;
    color: #475569;
    text-transform: uppercase;
}

.am-type-module { background: var(--am-accent-soft); color: var(--am-accent); }
.am-type-tabs { background: var(--am-tab-soft); color: var(--am-tab); }

.am-toggle {
    border: 1px solid var(--am-border);
    background: #fff;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    cursor: pointer;
    font-size: 11px;
    transition: transform .2s ease, background .15s ease;
}

.am-toggle.is-collapsed i {
    transform: rotate(-90deg);
}

.am-node-body {
    padding: 0 10px 8px;
    border-top: 1px solid var(--am-border);
}

.am-node-body.is-collapsed {
    display: none;
}

.am-group {
    margin-top: 6px;
    padding: 8px;
    border-radius: 8px;
    background: var(--am-bg);
    border: 1px solid var(--am-border);
}

.am-group-columns { background: #f8fafc; }
.am-group-actions { background: var(--am-action-soft); border-color: #e9e3ff; }

.am-group-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 6px;
}

.am-group-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #475569;
}

.am-group-actions .am-group-label { color: var(--am-action); }

.am-datatype-dd {
    position: relative;
    flex-shrink: 0;
}

.am-datatype-select {
    min-width: 110px;
    max-width: 150px;
    padding: 3px 24px 3px 8px;
    border: 1px solid #fcd34d;
    border-radius: 6px;
    background: var(--am-datatype-soft) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%23b45309' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E") no-repeat right 8px center;
    color: var(--am-datatype);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    outline: none;
    transition: all .15s ease;
}

.am-datatype-select.has-value {
    background-color: #fef3c7;
    border-color: #f59e0b;
}

.am-datatype-select:focus {
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
}

.am-select-all {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin: 0;
    font-size: 11px;
    color: var(--am-muted);
    cursor: pointer;
}

.am-select-all input {
    width: 12px;
    height: 12px;
    accent-color: var(--am-accent);
}

.am-chip-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.am-chip {
    display: inline-flex;
    align-items: center;
    gap: 0;
    margin: 0;
    cursor: pointer;
    user-select: none;
}

.am-chip span {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 999px;
    border: 1px solid #d7dde7;
    background: #fff;
    color: #334155;
    font-size: 11px;
    line-height: 1.25;
    transition: all .15s ease;
}

.am-chip input:checked + span {
    background: var(--am-accent-soft);
    border-color: #9ed0aa;
    color: var(--am-accent);
    font-weight: 600;
}

.am-chip-action input:checked + span {
    background: #ede9fe;
    border-color: #c4b5fd;
    color: var(--am-action);
}

.am-empty {
    padding: 16px;
    text-align: center;
    color: var(--am-muted);
    border: 1px dashed var(--am-border);
    border-radius: 8px;
    background: var(--am-bg);
    font-size: 12px;
}

.am-wrap.mt-2 {
    margin-top: 0.5rem !important;
}

@media (max-width: 767px) {
    .am-node-header {
        flex-wrap: wrap;
    }
    .am-title {
        white-space: normal;
    }
}
</style>

<div class="am-toolbar">
    <p class="am-hint">Select modules, tabs, columns and actions. Parent access is saved automatically.</p>
    <div class="am-toolbar-actions">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="am-expand-all">Expand All</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="am-collapse-all">Collapse All</button>
        <button type="button" class="btn btn-outline-primary btn-sm" id="am-select-all">Select All</button>
        <button type="button" class="btn btn-outline-danger btn-sm" id="am-clear-all">Clear All</button>
    </div>
</div>

<div class="am-wrap mt-2" id="am-tree">
    <?php if (empty($access_tree)): ?>
        <div class="am-empty">No access modules found. Please add entries in access manager first.</div>
    <?php else: ?>
        <?php
            $am_col_left = array();
            $am_col_right = array();
            foreach ($access_tree as $am_i => $am_module) {
                if ($am_i % 2 === 0) {
                    $am_col_left[] = $am_module;
                } else {
                    $am_col_right[] = $am_module;
                }
            }
        ?>
        <div class="am-col am-col-left">
            <?php foreach ($am_col_left as $module): ?>
                <?php
                    $this->load->view('backend/inventory/partials/access_tree_node', array(
                        'node' => $module,
                        'selected_access' => $selected_access,
                        'depth' => 0,
                    ));
                ?>
            <?php endforeach; ?>
        </div>
        <div class="am-col am-col-right">
            <?php foreach ($am_col_right as $module): ?>
                <?php
                    $this->load->view('backend/inventory/partials/access_tree_node', array(
                        'node' => $module,
                        'selected_access' => $selected_access,
                        'depth' => 0,
                    ));
                ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function ($) {
    function updateGroupToggles($scope) {
        $scope.find('.am-group-toggle').each(function () {
            var group = $(this).data('group');
            var $leaves = $scope.find('.am-chip-grid[data-group="' + group + '"] .am-checkbox');
            if (!$leaves.length) return;
            var total = $leaves.length;
            var checked = $leaves.filter(':checked').length;
            this.checked = checked === total;
            this.indeterminate = checked > 0 && checked < total;
        });
    }

    function updateNodeStates($scope) {
        $scope.find('.am-node').each(function () {
            var $node = $(this);
            var $childChecks = $node.find('> .am-node-body .am-checkbox');
            var $headerCheck = $node.find('> .am-node-header .am-node-check').get(0);
            if (!$headerCheck || !$childChecks.length) return;

            var total = $childChecks.length;
            var checked = $childChecks.filter(':checked').length;
            $headerCheck.indeterminate = checked > 0 && checked < total;
            if (checked === total) {
                $headerCheck.checked = true;
                $headerCheck.indeterminate = false;
            } else if (checked === 0) {
                $headerCheck.checked = false;
                $headerCheck.indeterminate = false;
            }
        });
        updateGroupToggles($scope);
    }

    function syncDatatypeSelect($select) {
        $select.toggleClass('has-value', $select.val() !== '');
        // Disable empty option from posting — only real IDs should submit
        if ($select.val() === '') {
            $select.removeAttr('name');
        } else {
            $select.attr('name', 'access_id[]');
        }
    }

    $(document).ready(function () {
        var $tree = $('#am-tree');

        $tree.on('click', '.am-toggle', function (e) {
            e.preventDefault();
            var target = $(this).data('target');
            var $body = $('#' + target);
            $body.toggleClass('is-collapsed');
            $(this).toggleClass('is-collapsed', $body.hasClass('is-collapsed'));
        });

        $tree.on('change', '.am-node-check', function () {
            var checked = this.checked;
            var $node = $(this).closest('.am-node');
            $node.find('.am-checkbox').prop('checked', checked).prop('indeterminate', false);
            if (!checked) {
                $node.find('.am-datatype-select').val('').each(function () {
                    syncDatatypeSelect($(this));
                });
            }
            updateNodeStates($tree);
        });

        $tree.on('change', '.am-datatype-select', function () {
            syncDatatypeSelect($(this));
            // Auto-check parent when a datatype is chosen
            if ($(this).val() !== '') {
                $(this).closest('.am-node').find('> .am-node-header .am-node-check')
                    .prop('checked', true)
                    .prop('indeterminate', false);
            }
            updateNodeStates($tree);
        });

        $tree.on('change', '.am-leaf', function () {
            updateNodeStates($tree);
        });

        $tree.on('change', '.am-group-toggle', function () {
            var group = $(this).data('group');
            var checked = this.checked;
            $tree.find('.am-chip-grid[data-group="' + group + '"] .am-checkbox')
                .prop('checked', checked)
                .prop('indeterminate', false);
            updateNodeStates($tree);
        });

        $('#am-expand-all').on('click', function () {
            $tree.find('.am-node-body').removeClass('is-collapsed');
            $tree.find('.am-toggle').removeClass('is-collapsed');
        });

        $('#am-collapse-all').on('click', function () {
            $tree.find('.am-node-body').addClass('is-collapsed');
            $tree.find('.am-toggle').addClass('is-collapsed');
        });

        $('#am-select-all').on('click', function () {
            $tree.find('.am-checkbox').prop('checked', true).prop('indeterminate', false);
            updateNodeStates($tree);
        });

        $('#am-clear-all').on('click', function () {
            $tree.find('.am-checkbox').prop('checked', false).prop('indeterminate', false);
            $tree.find('.am-datatype-select').val('').each(function () {
                syncDatatypeSelect($(this));
            });
            updateNodeStates($tree);
        });

        $tree.find('.am-datatype-select').each(function () {
            syncDatatypeSelect($(this));
        });

        updateNodeStates($tree);
    });
})(jQuery);
</script>
