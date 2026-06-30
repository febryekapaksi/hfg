<?php
$ENABLE_ADD    = has_permission('Pengajuan_mutasi.Add');
$ENABLE_MANAGE = has_permission('Pengajuan_mutasi.Manage');
$ENABLE_VIEW   = has_permission('Pengajuan_mutasi.View');
$ENABLE_DELETE = has_permission('Pengajuan_mutasi.Delete');
?>

<style>
	.skeleton {
		border-radius: 4px;
		animation: shimmer 1.5s infinite linear;
		background: linear-gradient(90deg, #f2f2f2 25%, #e0e0e0 50%, #f2f2f2 75%);
		background-size: 200% 100%;
	}

	.skeleton-line {
		height: 20px;
		margin: 8px 0;
		border-radius: 4px;
	}

	.skeleton-line.short {
		width: 60%;
	}

	.skeleton-line.medium {
		width: 80%;
	}

	@keyframes shimmer {
		0% {
			background-position: 200% 0;
		}

		100% {
			background-position: -200% 0;
		}
	}
</style>

<div class="card">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-center">
			<p></p>
			<?php if ($ENABLE_ADD): ?>
				<a href="<?= site_url('pengajuan_mutasi/form/add') ?>" class="btn btn-primary btn-sm">
					<i class="fa-solid fa-plus"></i> Buat Pengajuan Mutasi
				</a>
			<?php endif; ?>
		</div>

		<ul class="nav nav-tabs mb-3" id="MutasiTabs" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="open-tab" data-bs-toggle="tab"
					data-bs-target="#tab-open" type="button" role="tab">
					<i class="fa-solid fa-circle-dot text-primary"></i> Open
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="close-tab" data-bs-toggle="tab"
					data-bs-target="#tab-close" type="button" role="tab">
					<i class="fa-solid fa-check-double text-success"></i> Close
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="cancel-tab" data-bs-toggle="tab"
					data-bs-target="#tab-cancel" type="button" role="tab">
					<i class="fa-solid fa-ban text-danger"></i> Cancel
				</button>
			</li>
		</ul>

		<div class="tab-content" id="MutasiTabsContent">

			<div class="tab-pane fade show active" id="tab-open" role="tabpanel">
				<div id="skeleton-open">
					<div class="skeleton skeleton-line medium"></div>
					<div class="skeleton skeleton-line"></div>
					<div class="skeleton skeleton-line short"></div>
				</div>
				<div id="content-open" style="display:none;"></div>
			</div>

			<div class="tab-pane fade" id="tab-close" role="tabpanel">
				<div id="skeleton-close">
					<div class="skeleton skeleton-line medium"></div>
					<div class="skeleton skeleton-line"></div>
					<div class="skeleton skeleton-line short"></div>
				</div>
				<div id="content-close" style="display:none;"></div>
			</div>

			<div class="tab-pane fade" id="tab-cancel" role="tabpanel">
				<div id="skeleton-cancel">
					<div class="skeleton skeleton-line medium"></div>
					<div class="skeleton skeleton-line"></div>
					<div class="skeleton skeleton-line short"></div>
				</div>
				<div id="content-cancel" style="display:none;"></div>
			</div>

		</div>
	</div>
</div>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>

<script>
	const BASE_URL = siteurl + active_controller;

	function loadTab(tab) {
		const skeleton = $('#skeleton-' + tab);
		const content = $('#content-' + tab);

		if (content.data('loaded')) return;

		skeleton.show();
		content.hide();

		$.get(BASE_URL + '/render_' + tab, function(html) {
			skeleton.hide();
			content.html(html).show();
			content.data('loaded', true);
		});
	}

	$(document).ready(function() {
		// Load tab open pertama kali
		loadTab('open');

		// Load tab lain saat diklik
		$('#close-tab').on('shown.bs.tab', function() {
			loadTab('close');
		});
		$('#cancel-tab').on('shown.bs.tab', function() {
			loadTab('cancel');
		});
	});

	// Reload tab open dari luar (dipanggil setelah save/cancel)
	function reloadTab(tab) {
		$('#content-' + tab).data('loaded', false);
		loadTab(tab);
	}

	// Konfirmasi ajukan
	function confirmSubmit(id) {
		Swal.fire({
			title: 'Ajukan Mutasi?',
			text: 'Data akan dikirim untuk proses approval.',
			icon: 'question',
			showCancelButton: true,
			confirmButtonText: 'Ya, Ajukan',
			cancelButtonText: 'Batal',
            showLoaderOnConfirm: true
		}).then(result => {
			if (result.isConfirmed) {
				$.post(BASE_URL + '/submit/' + id, function(res) {
					if (res.status == 1) {
						Swal.fire({
							title: 'Berhasil',
							text: res.message,
							icon: 'success',
							showConfirmButton: false,
							timer: 1500,
							timerProgressBar: true
						}).then(() => {
							reloadTab('open');
						});
					} else {
						Swal.fire('Gagal', res.message, 'error');
					}
				}, 'json').fail(function() {
					Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
				});
			}
		});
	}

	// Konfirmasi cancel
	function confirmCancel(id) {
		Swal.fire({
			title: 'Batalkan Mutasi?',
			text: 'Masukkan alasan pembatalan mutasi ini:',
			icon: 'warning',
			input: 'text',
			inputPlaceholder: 'Alasan pembatalan wajib diisi...',
			showCancelButton: true,
			confirmButtonText: 'Ya, Batalkan',
			cancelButtonText: 'Kembali',
			confirmButtonColor: '#d33',
			inputValidator: (value) => {
				if (!value || value.trim() === '') {
					return 'Alasan pembatalan tidak boleh kosong!';
				}
			}
		}).then(result => {
			if (result.isConfirmed && result.value) {
				const reason = result.value.trim();

				$.post(BASE_URL + '/cancel/' + id, {
					reject_reason: reason
				}, function(res) {
					if (res.status == 1) {
						Swal.fire({
							title: 'Berhasil',
							text: res.message,
							icon: 'success',
							showConfirmButton: false,
							timer: 1500,
							timerProgressBar: true
						}).then(() => {
							reloadTab('open');
							$('#content-cancel').data('loaded', false);
						});
					} else {
						Swal.fire('Gagal', res.message, 'error');
					}
				}, 'json').fail(function() {
					Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
				});
			}
		});
	}
</script>