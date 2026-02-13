<header>
    <div class="logo">
        <?php
        if ($data) {
        ?>
            <img style="max-width:70px" src="https://www.talabulilm.com/mumin_images/<?= $data['its_id'] ?>.png" alt="Profile" class="rounded-circle">
        <?php
        }
        ?>
    </div>
    <div class="school-header">
        <div class="mamur-details">
            <p>KG NAME: <?= $data['fullname'] ?></p>
        </div>
        <div class="mamur-details">
            <p>ITS ID : <?= $data['its_id'] ?></p>
            <p>Miqaat Mauze : <?= $data['miqaat_mauze'] ?></p>
            <p>Zone : <?= $data['zone'] ?></p>
        </div>
    </div>

</header>
<section class="student-details">
    <div class="section-header">CLOSURE REPORT</div>
</section>