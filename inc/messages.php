<?php
if (isset($message['tag']) && isset($message['text']) && $message['text'] !== '') {
?>
    <div class="alert alert-<?= $message['tag'] ?> alert-dismissible fade show" role="alert">
        <?= $message['text'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
}
