<div class="card">
    <div class="card-body">
        <h5 class="card-title">List of Mumeneen</h5>

        <!-- Pills Tabs -->
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-quran-tab" data-bs-toggle="pill" data-bs-target="#pills-quran" type="button" role="tab" aria-controls="pills-home" aria-selected="true" tabindex="-1">Talim al-Quran</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-deeni-tab" data-bs-toggle="pill" data-bs-target="#pills-deeni" type="button" role="tab" aria-controls="pills-profile" aria-selected="false" tabindex="-1">Deeni Taalim</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-edu-tab" data-bs-toggle="pill" data-bs-target="#pills-edu" type="button" role="tab" aria-controls="pills-edu" aria-selected="false">Secular Education</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-sabaq-tab" data-bs-toggle="pill" data-bs-target="#pills-sabaq" type="button" role="tab" aria-controls="pills-sabaq" aria-selected="false">Not Attending Sabaq</button>
            </li>
        </ul>
        <div class="tab-content pt-2" id="myTabContent">
            <div class="tab-pane fade active show" id="pills-quran" role="tabpanel" aria-labelledby="home-tab">
                <?php require_once(__DIR__ . '/inc/dt-quraan.php') ?>
            </div>
            <div class="tab-pane fade" id="pills-deeni" role="tabpanel" aria-labelledby="profile-deeni">
                <?php require_once(__DIR__ . '/inc/dt-deeni.php') ?>
            </div>
            <div class="tab-pane fade" id="pills-edu" role="tabpanel" aria-labelledby="edu-tab">
                <?php require_once(__DIR__ . '/inc/dt-education.php') ?>
            </div>
            <div class="tab-pane fade" id="pills-sabaq" role="tabpanel" aria-labelledby="sabaq-tab">
                <?php require_once(__DIR__ . '/inc/dt-sabaq.php') ?>
            </div>
        </div><!-- End Pills Tabs -->

    </div>
</div>