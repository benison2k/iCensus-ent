<?php
/**
 * Partial view for rendering rows in the 'Pending Review' table.
 * This is included by app/views/residents/index.php when $isPendingView is true.
 */
?>
<?php foreach ($residents as $r): ?>
    <tr data-id="<?= $r['id'] ?>">
        <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
        <td><?= htmlspecialchars($r['age']) ?></td>
        <td><?= htmlspecialchars($r['gender']) ?></td>
        <td><?= htmlspecialchars($r['house_no'] . ' ' . $r['street'] . ', Purok ' . $r['purok']) ?></td>
        <td><?= date('M d, Y h:i A', strtotime($r['created_at'])) ?></td>
        <td>
            <div class="actions-column">
                <a href="<?= $base_url ?>/residents/approve?id=<?= $r['id'] ?>" class="action-btn btn-approve" title="Approve"><span class="material-icons">check</span></a>
                <a href="<?= $base_url ?>/residents/reject?id=<?= $r['id'] ?>" class="action-btn btn-reject" title="Decline" onclick="return confirm('Are you sure you want to reject this entry?');"><span class="material-icons">close</span></a>
                <button class="action-btn btn-view moreBtn" data-id="<?= $r['id'] ?>" title="View Details"><span class="material-icons">visibility</span></button>
            </div>
        </td>
    </tr>
<?php endforeach; ?>