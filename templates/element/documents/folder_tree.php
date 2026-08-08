<?php
/**
 * Recursive folder tree element.
 * Variables: $nodes (array), $projectId (int), $activeFolderId (int|null)
 */
foreach ($nodes as $node): ?>
<div class="dms-tree-node <?php echo ($node['id'] == $activeFolderId) ? 'active' : ''; ?>"
     onclick="window.location.href='<?php echo HTTP_ROOT; ?>documents/repository/<?php echo (int)$projectId; ?>/<?php echo (int)$node['id']; ?>'">
    <i class="material-icons"><?php echo (!empty($node['children'])) ? 'folder_open' : 'folder'; ?></i>
    <?php echo h($node['name']); ?>
    <?php if (!empty($node['children'])): ?>
        <i class="material-icons" style="margin-left:auto; font-size:14px; color:#bbb;">chevron_right</i>
    <?php endif; ?>
</div>
<?php if (!empty($node['children'])): ?>
<div class="dms-tree-children">
    <?php echo $this->element('documents/folder_tree', [
        'nodes' => $node['children'],
        'projectId' => $projectId,
        'activeFolderId' => $activeFolderId,
    ]); ?>
</div>
<?php endif; ?>
<?php endforeach; ?>
