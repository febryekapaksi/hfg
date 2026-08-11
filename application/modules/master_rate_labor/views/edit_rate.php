<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<input type="hidden" id="rate-id" value="<?= $rate->id ?>">

<div class="mb-3">
    <label class="form-label fw-bold text-secondary">Item Tenaga Kerja</label>
    <input type="text" class="form-control bg-light fw-bold" readonly value="<?= htmlspecialchars($rate->item) ?>">
</div>

<div class="mb-3">
    <label class="form-label fw-bold text-dark">
        Tarif / Rate Nilai 
        <span class="text-danger">*</span>
    </label>
    <div class="input-group">
        <?php if ($rate->type === 'direct'): ?>
            <span class="input-group-text bg-light fw-semibold text-secondary">Rp</span>
            <input type="number" id="rate-val" class="form-control fw-bold" value="<?= (float) $rate->rate ?>" placeholder="0" required>
            <span class="input-group-text bg-light fw-semibold text-secondary">/ Jam</span>
        <?php else: ?>
            <input type="number" id="rate-val" class="form-control fw-bold" value="<?= (float) $rate->rate ?>" step="0.01" min="0" max="100" placeholder="0" required>
            <span class="input-group-text bg-light fw-semibold text-secondary">%</span>
        <?php endif; ?>
    </div>
    <div class="form-text text-muted mt-1">
        <?php if ($rate->type === 'direct'): ?>
            Masukkan nilai rupiah tarif per jam kerja.
        <?php else: ?>
            Masukkan nilai persentase untuk beban tidak langsung.
        <?php endif; ?>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold text-dark">Remark</label>
    <textarea id="remark-val" class="form-control" rows="2" placeholder="Masukkan remark / keterangan"><?= htmlspecialchars($rate->remark ?? '') ?></textarea>
</div>
