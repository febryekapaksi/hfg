<?php
$tanda   = isset($tanda) ? $tanda : null;
$checked = isset($checked) ? $checked : null;

if ($tanda != 'request') { ?>
    <table class="table" width="100%">
        <thead>
            <tr>
                <td class="text-left" width='15%'><b>No PO</b></td>
                <td class="text-left" width='2%'>:</td>
                <td class="text-left"><?= $no_surat; ?></td>
            </tr>
            <tr>
                <td class="text-left"><b>Receipt Date</b></td>
                <td class="text-left">:</td>
                <td class="text-left"><?= $tanggal; ?></td>
            </tr>

            <?php if (!empty($file_incoming_material)) : ?>
                <tr>
                    <td class="text-left"><b>Incoming Material File</b></td>
                    <td class="text-left">:</td>
                    <td class="text-left">
                        <?php
                        $files = explode('|', $file_incoming_material);
                        foreach ($files as $f) :
                            if (file_exists($f)) : ?>
                                <a href="<?= base_url($f); ?>" target="_blank" style="display:block; margin-top: 5px;">
                                    <i class="fa fa-download"></i> <?= basename($f); ?>
                                </a>
                        <?php endif;
                        endforeach; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </thead>
    </table><br>

    <table id="my-grid" class="table table-bordered table-condensed" width="100%">
        <thead class="bg-blue">
            <tr>
                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="3%">No</th>
                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="25%">Product Name</th>
                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="7%">Unit</th>
                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="8%">Qty PO</th>
                <th class="text-center" colspan="4" style="background-color: #69c79d !important;">Data ROS (Packing List)</th>
                <th class="text-center" colspan="2" style="background-color: #f3b44e !important;" width="10%" hidden>Checklist Visual</th>
            </tr>
            <tr>
                <th class="text-center" style="background-color: #69c79d !important;">No. Coil</th>
                <th class="text-center" style="background-color: #69c79d !important;">Gross Weight</th>
                <th class="text-center" style="background-color: #69c79d !important;">Net Weight</th>
                <th class="text-center" style="background-color: #69c79d !important;">Length</th>
                <th class="text-center" style="background-color: #f3b44e !important;" hidden>OK</th>
                <th class="text-center" style="background-color: #f3b44e !important;" hidden>REJECT</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($detail_ros) && !empty($detail_ros)) {
                $grouped_data = [];
                foreach ($detail_ros as $item) {
                    // group by id_po_detail supaya rowspan per material tetap benar
                    $grouped_data[$item['id_po_detail']][] = $item;
                }

                $no = 1;
                foreach ($grouped_data as $id_po_detail => $rows) {
                    foreach ($rows as $index => $valx) {
                        echo "<tr>";

                        if ($index === 0) {
                            $rowspan = count($rows);
                            echo "<td align='center' rowspan='$rowspan' style='vertical-align:middle;'>$no</td>";
                            echo "<td align='left' rowspan='$rowspan' style='vertical-align:middle;'>
                        <b>{$valx['nm_barang']}</b><br>
                        <small>{$valx['id_barang']}</small>
                        " . (!empty($valx['trade_name']) ? "<br><small class='text-muted'>{$valx['trade_name']}</small>" : "") . "
                      </td>";
                            echo "<td align='center' rowspan='$rowspan' style='vertical-align:middle;'>" . ucfirst($valx['unit_measure'] ?? '-') . "</td>";
                            echo "<td align='right' rowspan='$rowspan' style='vertical-align:middle;'>" . number_format($valx['qty_order'] ?? 0, 2) . "</td>";
                        }

                        echo "<td align='center' style='background-color: #f9f9f9;'>{$valx['no_coil']}</td>";
                        echo "<td align='right' style='background-color: #f9f9f9;'>" . number_format($valx['berat_kotor'], 2) . "</td>";
                        echo "<td align='right' style='background-color: #f9f9f9;'>" . number_format($valx['berat_bersih'], 2) . "</td>";
                        echo "<td align='right' style='background-color: #f9f9f9;'>" . number_format($valx['length'], 2) . "</td>";

                        echo "<td align='center' style='background-color: #f9f9f9;' hidden>{$valx['no_coil']}</td>";
                        echo "<td align='center' style='background-color: #f9f9f9;' hidden>{$valx['no_coil']}</td>";

                        echo "</tr>";
                    }
                    $no++;
                }
            } else {
                echo "<tr><td colspan='9' align='center'>Data not found</td></tr>";
            }
            ?>
        </tbody>
    </table>
<?php } ?>