   <!-- Main content end -->

   </div>
   </div>
   </div> <!-- /.pc-content -->
   </div> <!-- /.pc-container -->

   <footer class="pc-footer">
     <div class="footer-wrapper container-fluid">
       <div class="row">
         <div class="col my-1">
           <p class="m-0">
             Copyright © <?= date('Y'); ?>
             <a href="<?= site_url(); ?>"><?= isset($idt->nm_perusahaan) ? $idt->nm_perusahaan : 'not-set'; ?></a>
           </p>
         </div>
         <div class="col-auto my-1">
           <p class="m-0">
             Page rendered in <strong>{elapsed_time}</strong> seconds
           </p>
         </div>
       </div>
     </div>
   </footer>

   </div> <!-- /.app-content -->
   </div> <!-- /.app-shell -->

   <div id="Processing"></div>
   <div id="ajaxFailed"></div>

   <!-- =========================
       BERRY REQUIRED JS
       ========================= -->
   <script src="<?= base_url('assets/berry/js/plugins/popper.min.js'); ?>"></script>
   <script src="<?= base_url('assets/berry/js/plugins/simplebar.min.js'); ?>"></script>

   <!-- Bootstrap 5 -->
   <script src="<?= base_url('assets/berry/js/plugins/bootstrap.min.js'); ?>"></script>

   <!-- Berry core -->
   <script src="<?= base_url('assets/berry/js/fonts/custom-font.js'); ?>"></script>
   <script src="<?= base_url('assets/berry/js/script.js'); ?>"></script>
   <script src="<?= base_url('assets/berry/js/theme.js'); ?>"></script>
   <script src="<?= base_url('assets/berry/js/plugins/feather.min.js'); ?>"></script>

   <script>
     // opsional: set default layout/preset (boleh kamu hapus kalau tidak perlu)
     if (typeof layout_change === 'function') layout_change('light');
     if (typeof font_change === 'function') font_change('Roboto');
     if (typeof change_box_container === 'function') change_box_container('false');
     if (typeof layout_caption_change === 'function') layout_caption_change('true');
     if (typeof layout_rtl_change === 'function') layout_rtl_change('false');
     if (typeof preset_change === 'function') preset_change('preset-1');
   </script>

   <!-- =========================
       PLUGIN JS EXISTING (dipertahankan dulu)
       ========================= -->
   <!-- Select2 -->
   <script src="<?= base_url('assets/plugins/select2/select2.full.min.js'); ?>"></script>

   <!-- Multi Select -->
   <script src="<?= base_url('assets/plugins/multiselect/multiselect.min.js'); ?>"></script>

   <!-- DataTables FixedHeader -->
   <script src="https://cdn.datatables.net/fixedheader/3.1.7/js/dataTables.fixedHeader.min.js"></script>

   <!-- Custom JS milikmu -->
   <script src="<?= base_url('assets/js/custome_ddr.js'); ?>" type="text/javascript"></script>

   <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
   <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

   <script type="text/javascript">
     $(document).ready(function() {
       $('.chosen-select').select2();
     });

     function getNum(val) {
       if (isNaN(val) || val == '') {
         return 0;
       }
       return parseFloat(val);
     }

     function updateGreetingAndClock() {
       const now = new Date();

       // Greeting berdasarkan jam
       const hour = now.getHours();
       let greeting = "Hello";
       if (hour >= 5 && hour < 12) greeting = "Good Morning";
       else if (hour >= 12 && hour < 15) greeting = "Good Afternoon";
       else if (hour >= 15 && hour < 18) greeting = "Good Evening";
       else greeting = "Good Night";

       // Nama hari + bulan (Indonesia)
       const hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
       const bulan = [
         "Januari", "Februari", "Maret", "April", "Mei", "Juni",
         "Juli", "Agustus", "September", "Oktober", "November", "Desember"
       ];

       const dayName = hari[now.getDay()];
       const dateNum = now.getDate();
       const monthName = bulan[now.getMonth()];
       const yearNum = now.getFullYear();

       // Jam format HH:mm:ss
       const hh = String(now.getHours()).padStart(2, "0");
       const mm = String(now.getMinutes()).padStart(2, "0");
       const ss = String(now.getSeconds()).padStart(2, "0");

       // WIB label (karena sistem kamu WIB)
       const fullDateTime = `${dayName}, ${dateNum} ${monthName} ${yearNum} • ${hh}:${mm}:${ss} WIB`;

       // Inject ke HTML
       const greetingEl = document.getElementById("greetingText");
       const dtEl = document.getElementById("liveDateTime");

       if (greetingEl) greetingEl.textContent = greeting;
       if (dtEl) dtEl.textContent = fullDateTime;
     }

     document.addEventListener("DOMContentLoaded", function() {
       updateGreetingAndClock();
       setInterval(updateGreetingAndClock, 1000);
     });
   </script>

   <!-- Floating Widget Definisi -->
   <?php include APPPATH . '../themes/admin/views/widget_definisi.php'; ?>

   </body>

   </html>