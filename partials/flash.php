<?php if (!empty($flash)): ?>
    <div class="container mt-4">
        <?php foreach ($flash as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?= htmlspecialchars($type) ?> flash-message shadow-sm" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
