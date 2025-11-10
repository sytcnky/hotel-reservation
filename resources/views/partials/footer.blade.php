<footer class="bg-light-subtle py-4">
    <div class="container">
        <div class="row pt-3">
            <!-- Logo ve Hakkında -->
            <div class="col-xl-3 mb-4">
                <a href="/" class="text-primary text-decoration-none fw-bold fs-2 d-block mb-2">
                    <span class="text-primary">Icmeler</span><span class="text-warning">Online</span>
                </a>
                <p class="small fst-italic">30 yılı aşkın süredir İçmeler’e hizmet veren lider turizm acentası.</p>
                <div class="small align-items-center"><img src="/images/tursab-logo.png" width="96"> üyesidir.</div>

                <!--
                <div class="p-3 bg-success text-white rounded mb-3 d-flex gap-3 align-items-center">
                    <img src="/images/wps.webp" width="66" class=" rounded-circle">
                    <div class="small">
                        Whasptapp Destek Hattı
                        <i class="fi fi-brands-whatsapp"></i>
                        <h5>+90 555 111 22 33</h5>
                    </div>
                </div>
                -->
            </div>

            <!-- Menü -->
            <div class="col-xl-2 col-6 mb-4 d-none d-lg-block">
                <ul class="list-unstyled">
                    <li>
                        <a class="text-decoration-none {{ request()->routeIs('hotels') ? 'active' : '' }}"
                           href="{{ route('hotels') }}">Oteller</a>
                    </li>
                    <li class="nav-item">
                        <a class="text-decoration-none {{ request()->routeIs('transfers') ? 'active' : '' }}"
                           href="{{ route('transfers') }}">Havalimanı Transferi</a>
                    </li>
                    <li><a class="text-decoration-none" href="#">Kiralık Villalar</a></li>
                    <li><a class="text-decoration-none" href="#">Günlük Turlar</a></li>
                </ul>
            </div>

            <!-- Site -->
            <div class="col-xl-2 col-6 mb-4 d-none d-lg-block">
                <ul class="list-unstyled">
                    <li><a href="#" class="text-decoration-none">Anasayfa</a></li>
                    <li><a href="#" class="text-decoration-none">Ödeme Yap</a></li>
                    <li><a href="#" class="text-decoration-none">Gezi Rehberi</a></li>
                    <li><a href="#" class="text-decoration-none">SSS</a></li>
                    <li><a href="#" class="text-decoration-none">İletişim</a></li>
                </ul>
            </div>

            <!-- Sosyal Medya -->
            <div class="col-xl-5 mb-4">
                <div class="d-xl-flex gap-3 justify-content-between">
                    <div class="mb-3 d-flex gap-3 align-items-center">
                        <div class="border border-primary-subtle text-primary p-2 rounded">
                            <i class="fi fi-rr-phone-flip fs-4"></i>
                        </div>
                        <div class="small text-secondary">
                            Marmaris Ofis
                            <h6>+90 252 455 3617</h6>
                        </div>
                    </div>
                    <div class="mb-3 d-flex gap-3 align-items-center">
                        <div class="border border-primary-subtle text-primary p-2 rounded">
                            <i class="fi fi-rr-phone-flip fs-4"></i>
                        </div>
                        <div class="small text-secondary">
                            Londra Ofis
                            <h6>+44 0779 875 1413</h6>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <a href="#" target="_blank"
                       class="border p-2 border-primary-subtle rounded text-center text-decoration-none mb-1 small flex-fill">
                        <i class="fi fi-brands-facebook fs-2 align-middle"></i>
                        <div class="opacity-50 d-none d-xl-block">Facebook</div>
                        <div class="d-none d-xl-block">@icmeleronline</div>
                    </a>
                    <a href="#" target="_blank"
                       class="border p-2 border-danger-subtle rounded text-center text-danger text-decoration-none mb-1 small flex-fill">
                        <i class="fi fi-brands-youtube fs-2 align-middle"></i>
                        <div class="opacity-50 d-none d-xl-block">Youtube</div>
                        <div class="d-none d-xl-block">@icmeleronline</div>
                    </a>
                    <a href="#" target="_blank"
                       class="border p-2 border-warning rounded text-center text-warning text-decoration-none mb-1 small flex-fill">
                        <i class="fi fi-brands-instagram fs-2 align-middle"></i>
                        <div class="opacity-50 d-none d-xl-block">Instagram</div>
                        <div class="d-none d-xl-block">@icmeleronline</div>
                    </a>
                </div>
            </div>
        </div>

        <hr>
        <div class="row">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small">
                <div class="text-center text-md-start mb-2 mb-md-0">© 2025 ICR Travel İçmeler Otel Apart ve Tatil Rezervasyon Merkezi</div>
                <div class="d-flex gap-3">
                    <a href="#" class="text-decoration-none">Gizlilik Politikası</a>
                    <a href="#" class="text-decoration-none">Çerezler</a>
                    <a href="#" class="text-decoration-none">Kullanım Koşulları</a>
                </div>
            </div>
        </div>
    </div>
</footer>
