<?php
require_once(__DIR__ . '/session.php');
$current_page = 'important_contacts';
require_once(__DIR__ . '/inc/header.php');
?>

<style>
.contact-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.82rem;
    flex-shrink: 0;
    color: #fff;
    letter-spacing: 0.5px;
}
.contact-item {
    transition: background 0.15s;
}
.contact-item:hover {
    background: #f8f9fa;
}
.section-header {
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem 0.5rem 0 0;
    color: #fff;
}
.wa-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #25d366;
    color: #fff;
    font-size: 1rem;
    text-decoration: none;
    flex-shrink: 0;
    transition: background 0.15s, transform 0.15s;
    box-shadow: 0 2px 6px rgba(37,211,102,0.35);
}
.wa-btn:hover {
    background: #1da851;
    color: #fff;
    transform: scale(1.12);
}
.contact-card {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.09);
    overflow: hidden;
}
.contact-name {
    font-size: 0.88rem;
    font-weight: 600;
    margin-bottom: 1px;
}
.contact-number {
    font-size: 0.78rem;
    color: #6c757d;
}
</style>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Important Contacts</h5>

                    <div class="row g-4">

                        <!-- Social Media -->
                        <div class="col-md-4">
                            <div class="contact-card card h-100">
                                <div class="section-header" style="background: linear-gradient(135deg, #0d6efd, #3d8bfd);">
                                    <i class="bi bi-megaphone-fill me-2"></i>Social Media
                                </div>
                                <div class="card-body p-0">
                                    <div class="contact-item d-flex align-items-center px-3 py-3 border-bottom">
                                        <div class="contact-avatar me-3" style="background: #0d6efd;">SH</div>
                                        <div class="flex-grow-1">
                                            <div class="contact-name">Shabbir Bs bin Ubai Bs</div>
                                            <div class="contact-number">+91 81287 55144</div>
                                        </div>
                                        <a href="https://wa.me/918128755144" target="_blank" class="wa-btn" title="Open WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                    <div class="contact-item d-flex align-items-center px-3 py-3">
                                        <div class="contact-avatar me-3" style="background: #0d6efd;">MU</div>
                                        <div class="flex-grow-1">
                                            <div class="contact-name">M Murtaza Mullajiwala</div>
                                            <div class="contact-number">+91 90048 81852</div>
                                        </div>
                                        <a href="https://wa.me/919004881852" target="_blank" class="wa-btn" title="Open WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ITNC - Shaadi Mutalliq -->
                        <div class="col-md-4">
                            <div class="contact-card card h-100">
                                <div class="section-header" style="background: linear-gradient(135deg, #6f42c1, #9b6dff);">
                                    <i class="bi bi-people-fill me-2"></i>ITNC - Shaadi Mutalliq
                                </div>
                                <div class="card-body p-0">
                                    <div class="contact-item d-flex align-items-center px-3 py-3 border-bottom">
                                        <div class="contact-avatar me-3" style="background: #6f42c1;">BU</div>
                                        <div class="flex-grow-1">
                                            <div class="contact-name">M Burhanuddin Kathawala</div>
                                            <div class="contact-number">+91 88668 07939</div>
                                        </div>
                                        <a href="https://wa.me/918866807939" target="_blank" class="wa-btn" title="Open WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                    <div class="contact-item d-flex align-items-center px-3 py-3">
                                        <div class="contact-avatar me-3" style="background: #6f42c1;">AD</div>
                                        <div class="flex-grow-1">
                                            <div class="contact-name">M Adnan Bhanpurawala</div>
                                            <div class="contact-number">+91 77389 25152</div>
                                        </div>
                                        <a href="https://wa.me/917738925152" target="_blank" class="wa-btn" title="Open WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BQI Back Office -->
                        <div class="col-md-4">
                            <div class="contact-card card h-100">
                                <div class="section-header" style="background: linear-gradient(135deg, #198754, #28c76f);">
                                    <i class="bi bi-building me-2"></i>BQI Back Office
                                </div>
                                <div class="card-body p-0">
                                    <div class="contact-item d-flex align-items-center px-3 py-3 border-bottom">
                                        <div class="contact-avatar me-3" style="background: #198754;">MF</div>
                                        <div class="flex-grow-1">
                                            <div class="contact-name">Shk Mufadddal Shakir</div>
                                            <div class="contact-number">+91 96194 90152</div>
                                        </div>
                                        <a href="https://wa.me/919619490152" target="_blank" class="wa-btn" title="Open WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                    <div class="contact-item d-flex align-items-center px-3 py-3 border-bottom">
                                        <div class="contact-avatar me-3" style="background: #198754;">AB</div>
                                        <div class="flex-grow-1">
                                            <div class="contact-name">Shk Aliasger Bhanpurawala</div>
                                            <div class="contact-number">+91 98927 26516</div>
                                        </div>
                                        <a href="https://wa.me/919892726516" target="_blank" class="wa-btn" title="Open WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                    <div class="contact-item d-flex align-items-center px-3 py-3 border-bottom">
                                        <div class="contact-avatar me-3" style="background: #198754;">AH</div>
                                        <div class="flex-grow-1">
                                            <div class="contact-name">Shk Aliasger Hamid</div>
                                            <div class="contact-number">+91 81530 19172</div>
                                        </div>
                                        <a href="https://wa.me/918153019172" target="_blank" class="wa-btn" title="Open WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                    <div class="contact-item d-flex align-items-center px-3 py-3 border-bottom">
                                        <div class="contact-avatar me-3" style="background: #198754;">MA</div>
                                        <div class="flex-grow-1">
                                            <div class="contact-name">M Mansoor Shakir</div>
                                            <div class="contact-number">+91 99306 56777</div>
                                        </div>
                                        <a href="https://wa.me/919930656777" target="_blank" class="wa-btn" title="Open WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                    <div class="contact-item d-flex align-items-center px-3 py-3">
                                        <div class="contact-avatar me-3" style="background: #198754;">MO</div>
                                        <div class="flex-grow-1">
                                            <div class="contact-name">M Moiz Nasir</div>
                                            <div class="contact-number">+91 99796 60952</div>
                                        </div>
                                        <a href="https://wa.me/919979660952" target="_blank" class="wa-btn" title="Open WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once(__DIR__ . '/inc/footer.php'); ?>
