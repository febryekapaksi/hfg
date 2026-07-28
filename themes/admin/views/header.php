<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />

  <title>
    <?= isset($idt->nm_perusahaan) ? $idt->nm_perusahaan : 'not-set'; ?>
    <?= isset($template['title']) ? ' | ' . $template['title'] : ''; ?>
  </title>

  <!-- Favicon (pakai milikmu) -->
  <link rel="shortcut icon" href="<?= base_url('assets/images/iconhfg.png'); ?>" />

  <!-- =========================
       BERRY CSS (Bootstrap 5)   
       ========================= -->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" id="main-font-link" />

  <!-- Icons (Berry) -->
  <link rel="stylesheet" href="<?= base_url('assets/berry/fonts/tabler-icons.min.css'); ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/berry/fonts/feather.css'); ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/berry/fonts/fontawesome.css'); ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/berry/fonts/material.css'); ?>" />

  <!-- Customer Theme -->
  <link rel="stylesheet" href="<?= base_url('assets/berry/css/custom-theme.css'); ?>">
  <!-- <link rel="stylesheet" href="<?= base_url('assets/berry/css/custom-dark.css'); ?>"> -->


  <!-- Template CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/berry/css/plugins/animate.min.css'); ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/berry/css/style.css'); ?>" id="main-style-link" />
  <link rel="stylesheet" href="<?= base_url('assets/berry/css/custom.css'); ?>" id="main-style-link" />
  <link rel="stylesheet" href="<?= base_url('assets/berry/css/style-preset.css'); ?>" />

  <!-- =========================
       CSS PLUGIN LAMA (sementara dipertahankan)
       ========================= -->
  <link rel="stylesheet" href="<?= base_url('assets/dist/sweetalert.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('assets/plugins/daterangepicker/daterangepicker.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('assets/jquery-ui-1.12.1/jquery-ui-1.12.1/jquery-ui.min.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('assets/plugins/timepicker/bootstrap-timepicker.min.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('assets/plugins/datetimepicker/bootstrap-datetimepicker.css'); ?>">
  <!-- <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css"> -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">


  <!-- =========================
       JS yang kamu pakai di banyak halaman (dipertahankan dulu)
       ========================= -->
  <script src="<?= base_url('assets/plugins/jQuery/jquery-2.2.3.min.js'); ?>"></script>

  <!-- Date/Time libs (existing) -->
  <script src="<?= base_url('assets/plugins/daterangepicker/moment.min.js'); ?>"></script>
  <script src="<?= base_url('assets/plugins/daterangepicker/daterangepicker.js'); ?>"></script>
  <script src="<?= base_url('assets/jquery-ui-1.12.1/jquery-ui-1.12.1/jquery-ui.min.js'); ?>"></script>
  <script src="<?= base_url('assets/plugins/timepicker/bootstrap-timepicker.min.js'); ?>"></script>
  <script src="<?= base_url('assets/plugins/datetimepicker/bootstrap-datetimepicker.js'); ?>"></script>
  <script src="<?= base_url('assets/plugins/datetimepicker/moment-with-locales.js'); ?>"></script>

  <!-- Sweet Alert -->
  <script src="<?= base_url('assets/dist/sweetalert.min.js'); ?>"></script>

  <!-- Form Jquery -->
  <script src="<?= base_url('assets/plugins/jqueryform/jquery.form.js'); ?>"></script>

  <!-- (SlimScroll biasanya AdminLTE, tapi biarin dulu kalau masih kepakai di halaman tertentu) -->
  <script src="<?= base_url('assets/plugins/slimScroll/jquery.slimscroll.min.js'); ?>"></script>

  <script src="<?= base_url('assets/js/scripts.js'); ?>" type="text/javascript"></script>

  <script type="text/javascript">
    var baseurl = "<?= base_url(); ?>";
    var siteurl = "<?= site_url(); ?>";
    var base_url = siteurl;
    var active_controller = "<?= $this->uri->segment(1); ?>/";
    var active_function = "<?= $this->uri->segment(2); ?>/";

    window.addEventListener("load", function() {
      const loader = document.getElementById("app-loader");
      if (!loader) return;

      loader.classList.add("hide");
      setTimeout(() => loader.remove(), 320);
    });

    // fail safe: kalau load stuck, loader hilang 6 detik
    setTimeout(() => {
      const loader = document.getElementById("app-loader");
      if (loader) loader.remove();
    }, 6000);
  </script>

  <style>
    /* Paksa modal bisa scroll */
    #dialog-popup .modal-content {
      max-height: calc(100vh - 60px);
    }

    #dialog-popup .modal-body {
      max-height: calc(100vh - 220px);
      /* header+footer kira2 */
      overflow-y: auto !important;
      -webkit-overflow-scrolling: touch;
    }

    .b-title {
      font-style: italic;
      font-weight: bold;
      color: #d4a017;
      /* fallback */

      /* efek emas */
      background: linear-gradient(180deg, #ffd76a 0%, #d4a017 45%, #b8860b 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;

      /* sedikit bayangan supaya lebih jelas */
      text-shadow: 0 1px 1px rgba(0, 0, 0, 0.15);
    }

    .swal2-container {
      z-index: 9999 !important;
    }
  </style>
</head>

<body>
  <!-- [ Pre-loader ] start -->
  <div id="app-loader" class="app-loader">
    <div class="app-loader__card">
      <div class="app-loader__spinner"></div>

      <div class="app-loader__text">
        Loading
        <span class="loader-dots">
          <span></span><span></span><span></span>
        </span>
      </div>
    </div>
  </div>

  <!-- [ Pre-loader ] End -->


  <!-- =========================
       SIDEBAR BERRY (ASLI)
       ========================= -->
  <nav class="pc-sidebar">
    <div class="navbar-wrapper">
      <div class="m-header">
        <a href="<?= site_url(); ?>" class="b-brand text-decoration-none">
          <img src="<?= base_url('assets/images/iconhfg.png'); ?>" alt="logo" style="height:50px; width:auto;">

          <span class="b-title"><?= isset($idt->nm_perusahaan) ? $idt->nm_perusahaan : 'not-set'; ?></span>
        </a>
      </div>

      <div class="navbar-content">
        <?= $this->menu_generator->build_menus(); ?>
      </div>
    </div>
  </nav>

  <!-- =========================
       HEADER TOPBAR BERRY
       ========================= -->
  <header class="pc-header">
    <div class="header-wrapper px-4">
      <!-- left -->
      <div class="me-auto">
        <ul class="list-unstyled mb-0 d-flex align-items-center">

          <!-- MOBILE: buka sidebar overlay -->
          <li class="pc-h-item d-lg-none">
            <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
              <i data-feather="menu"></i>
            </a>
          </li>

          <!-- DESKTOP: collapse/minify sidebar -->
          <li class="pc-h-item d-none d-lg-inline-flex">
            <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
              <i data-feather="menu"></i>
            </a>
          </li>

        </ul>
      </div>

      <!-- right -->
      <div class="ms-auto">
        <ul class="list-unstyled">

          <!-- NOTIFICATION (optional / dummy) -->
          <li class="dropdown pc-h-item">
            <a class="pc-head-link head-link-secondary dropdown-toggle arrow-none me-0"
              data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
              <i class="ti ti-bell"></i>
            </a>

            <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
              <div class="dropdown-header">
                <a href="#!" class="link-primary float-end text-decoration-underline">Mark as all read</a>
                <h5 class="mb-0">
                  All Notification
                  <span class="badge bg-warning rounded-pill ms-1">01</span>
                </h5>
              </div>

              <div class="dropdown-header px-0 text-wrap header-notification-scroll position-relative"
                style="max-height: calc(100vh - 215px)" data-simplebar="init">
                <div class="list-group list-group-flush w-100">

                  <div class="list-group-item list-group-item-action">
                    <div class="d-flex">
                      <div class="flex-shrink-0">
                        <div class="user-avtar bg-light-success">
                          <i class="ti ti-building-store"></i>
                        </div>
                      </div>
                      <div class="flex-grow-1 ms-2">
                        <span class="float-end text-muted small">3 min ago</span>
                        <h6 class="mb-1">System Info</h6>
                        <p class="text-body fs-6 mb-1">Welcome back to dashboard.</p>
                        <span class="badge rounded-pill bg-light-danger">Unread</span>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              <div class="dropdown-divider"></div>
              <div class="text-center py-2">
                <a href="#!" class="link-primary">View all</a>
              </div>
            </div>
          </li>

          <!-- USER PROFILE -->
          <li class="dropdown pc-h-item header-user-profile">
            <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0"
              data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">

              <img
                src="<?= (isset($userData->photo) && file_exists('assets/images/users/' . $userData->photo))
                        ? base_url('assets/images/users/' . $userData->photo)
                        : base_url('assets/images/male-def.png'); ?>"
                alt="user-image"
                class="user-avtar" />

              <span>
                <i class="ti ti-settings"></i>
              </span>
            </a>

            <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
              <div class="dropdown-header">

                <h4 class="mb-0">
                  <span id="greetingText">Hello</span>,
                  <span class="small text-muted">
                    <?= isset($userData->nm_lengkap) ? ucwords($userData->nm_lengkap) : $userData->username; ?>
                  </span>
                </h4>

                <p class="text-muted mb-2 d-flex align-items-center gap-2">
                  <i class="ti ti-calendar"></i>
                  <span id="liveDateTime">--</span>
                </p>

                <p class="text-muted mb-2">
                  <?= isset($userData->groups) ? $userData->groups : '-'; ?>
                </p>

                <hr>

                <div class="profile-notification-scroll position-relative"
                  style="max-height: calc(100vh - 280px)" data-simplebar="init">

                  <a href="#" class="dropdown-item">
                    <i class="ti ti-user"></i>
                    <span>Profile</span>
                  </a>

                  <a href="#" class="dropdown-item">
                    <i class="ti ti-settings"></i>
                    <span>Account Settings</span>
                  </a>

                  <a href="<?= site_url('users/logout'); ?>" class="dropdown-item text-danger">
                    <i class="ti ti-logout"></i>
                    <span>Logout</span>
                  </a>

                </div>
              </div>
            </div>
          </li>

        </ul>
      </div>

    </div>
  </header>

  <!-- =========================
       CONTENT AREA BERRY
       ========================= -->
  <div class="pc-container">
    <div class="pc-content">

      <!-- Page header -->
      <div class="card mb-3">
        <div class="card-body py-3">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

            <!-- LEFT: Title -->
            <div>
              <h3 class="mb-0"><?= isset($template['title']) ? $template['title'] : 'Dashboard'; ?></h3>
            </div>

            <!-- RIGHT: Breadcrumb -->
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0 align-items-center">
                <li class="breadcrumb-item">
                  <a href="<?= site_url(); ?>" class="text-decoration-none d-flex align-items-center gap-1">
                    <i class="ti ti-home"></i> Home
                  </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                  <?= isset($template['title']) ? $template['title'] : 'Dashboard'; ?>
                </li>
              </ol>
            </nav>

          </div>
        </div>
      </div>


      <!-- ✅ MAIN CONTENT START -->
      <div class="row">
        <div class="col-12">