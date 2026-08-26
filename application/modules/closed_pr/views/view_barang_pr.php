<div class="mb-3">
    <table class="table table-sm table-borderless" style="width: auto;">
        <tr>
            <td class="fw-bold">No. PR</td>
            <td class="text-center fw-bold">:</td>
            <td class="fw-bold"><?= $no_pr ?></td>
        </tr>
    </table>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Material</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Qty Packing</th>
                <th class="text-center">Unit</th>
                <th class="text-center">Unit Packing</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($list_barang as $item) {
                $konversi = ($item->nilai_konversi <= 0) ? 1 : $item->nilai_konversi;
                $qty = $item->qty;
                $qty_packing = ($item->qty / $konversi);
                if ($item->kategori_pr == 'PR Stok') {
                    $qty_packing = $qty_packing;
                }

                echo '<tr>';
                echo '<td class="text-center">' . $no . '</td>';
                echo '<td>' . $item->nm_barang . '</td>';
                echo '<td class="text-end">' . number_format($qty, 2) . '</td>';
                echo '<td class="text-end">' . number_format($qty_packing, 2) . '</td>';
                echo '<td class="text-center">' . ucfirst($item->unit) . '</td>';
                echo '<td class="text-center">' . ucfirst($item->unit_packing) . '</td>';
                echo '</tr>';
                $no++;
            }
            ?>
        </tbody>
    </table>
</div>
