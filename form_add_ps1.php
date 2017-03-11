<?php
    include "config/database.php";
    date_default_timezone_set("Asia/Jakarta");
    if(isset($_POST["add"])):

    $stmt1 = $db->prepare("INSERT INTO tb_pemain(nama, alamat, jml_pemain) VALUES (?,?,?)");
    $stmt2 = $db->prepare("INSERT INTO tb_billing(id_unit, tanggal, jam_mulai, status) VALUES (?,?,?,?)");

    $nama       = $_POST['nama'];
    $alamat     = $_POST['alamat'];
    $jml_pemain = $_POST['jml_pemain'];
    $id_unit    = $_POST['id_unit'];
    $tanggal    = date('d-m-y');
    $jam_mulai  = $_POST['jam_mulai'];
    $status     = $_POST['status'];

    $stmt1->bind_param("ssi", $nama, $alamat, $jml_pemain);
    $stmt2->bind_param("isss", $id_unit, $tanggal, $jam_mulai, $status);

    if ($stmt1->execute() == false)
        {
            echo 'First query failed: ' . $mysqli->error;
    }
        $stmt1->close();
    if ($stmt2->execute() == false)
        {
            echo 'Second query failed: ' . $mysqli->error;
    } else {
        header('location:add_ps1.php');
    }
    $stmt2->close();
    $db->close();
        
    endif;
?>
<?php
    date_default_timezone_set("Asia/Jakarta");
    include "config/database.php";
    $sql = "SELECT tb_billing.id_billing, tb_billing.tanggal, tb_billing.status, tb_billing.jam_mulai, tb_billing.jam_selesai, tb_billing.id_pemain, tb_billing.durasi, tb_unit.unit, tb_pemain.nama FROM tb_billing INNER JOIN tb_pemain ON tb_billing.id_pemain = tb_pemain.id_pemain INNER JOIN tb_unit ON tb_billing.id_unit = tb_unit.id_unit WHERE tb_billing.id_unit='1'";
    $res = $db->query($sql);
    if ($res->num_rows > 0){
            // output data of each row
    while($row  = $res->fetch_assoc()) {
    $strStart   = $row['jam_mulai'];
    $strEnd     = $row['jam_selesai'];
    $h          = date("H:i:s");
    $var_tarif  = 500;
    $dteStart   = new DateTime($strStart);
    $dteEnd     = new DateTime($strEnd);
    $hi         = new DateTime ($h);
    $wkt_nol    = "00:00:00";
    $exp_hi     = explode(':', $hi->format("%H:%I:%S")); //exlpode timenow
    $diff_harga = explode(':', $dteEnd->diff($dteStart)->format("%H:%I:%S")); //exlpode dif harga
    $exp_dteend = explode(':', $dteEnd->format("%H:%I:%S")); // exlpode end time
    $waktu_sisa = explode(':', $hi->diff($dteStart)->format("%H:%I:%S")); //set coutup
    if ($dteEnd < $hi){
        $wkt_sisa   = $wkt_nol;
    }else{
        $wkt_sisa   = explode(':', $dteEnd->diff($hi)->format("%H:%I:%S")); //set countdown
    };
    //start - set value countup//
        $mtd        = $waktu_sisa[1] * 60;
        $jtd        = $waktu_sisa[0] * 3600;
        $tot_dtk    = $jtd + $mtd + $waktu_sisa[2];
    //end - set value countup//

    //start - set harga countdown//
        $jtm1       = $diff_harga[0] * 60;
        $total1     = $jtm1 + $diff_harga[1];
        $totharga1  = round($total1/15)*$var_tarif;
    //end - set harga countdown//

    //start - set harga countup//
        $jtm        = $waktu_sisa[0] * 60;
        $total      = $jtm + $waktu_sisa[1];
        $totharga   = ceil($total/15)*$var_tarif;
    //end - set harga countup//
?>
<script>
    // <!-- >>>** count up - start **<<<< --> //
        var timerVar = setInterval(countTimer, 1000);
        var totalSeconds = <?php echo $tot_dtk?>;
        function countTimer() {
           ++totalSeconds;
           var hour     = Math.floor(totalSeconds /3600);
           var minute   = Math.floor((totalSeconds - hour*3600)/60);
           var seconds  = totalSeconds - (hour*3600 + minute*60);
           var h        = hour < 10 ? "0"+hour : hour;
           var m        = minute < 10 ? "0"+minute : minute;
           var s        = seconds < 10 ? "0"+seconds : seconds;

           document.getElementById("timer").value = h + ":" + m + ":" + s;
        }
    // <!-- >>>** count up - end **<<<< --> //

    // <!-- >>>** countdown - start **<<<< --> //
        var win         = new Audio('audio/file.mp3');
        var detik       = <?php echo $wkt_sisa[2]?>;
        var menit       = <?php echo $wkt_sisa[1]?>;
        var jam         = <?php echo $wkt_sisa[0]?>;
        var c           = 0;
        var timer_is_on = 0;
        var t;
        timedCount();
        function pad(d){
            return (d < 10 ? "0" + d.toString() : d.toString());
        }
        function timedCount() {
            $('#hasil1').val(pad(jam) + ':' + pad(menit) + ':' + pad(detik));
            t = setTimeout(function(){ timedCount() }, 1000);
            detik --;
            if(detik < 0){
                detik = 59;
                menit --;
                if(menit < 0){
                    menit = 59;
                    jam --;
                    if(jam < 0){
                        stopCount();
                        win.play();
                    }
                } 
            }
        }
        function stopCount() {
            clearTimeout(t);
            timer_is_on = 0;
        }
    // <!-- >>>** countdown - end **<<<< --> //
</script>
<td class="featured">
    <form method="post">
        Nama
        <input name="id_billing" type="text" onblur="this.value=(this.value=='') ? '<?= $row["nama"]?>' : this.value;" readonly=""value="<?= $row["nama"]?>" /> 
        Jam Mulai
        <input name="jam_mulai" type="text" onblur="this.value=(this.value=='') ? '<?= $row["jam_mulai"]?>' : this.value;" readonly=""value="<?= $row["jam_mulai"]?>" />  
        Jam Selesai
        <input type="text" name="jam_selesai" id="jam_selesai" onblur="this.value=(this.value=='') ? '<?= $row["jam_selesai"]?>' : this.value;" readonly="" value='<?= $row["jam_selesai"]?>' />
        Durasi/Sisa
        <input type="text" id="timer" style="display: none;"><input type="text" id="hasil1" style="display: none;" /> 
        Harga
        <input type="text" name = "harga" id="harga"/>
        <input type="submit" name="edit" id='btn' class="bton bton-dark-blue" value="Stop">
    </form>
</td>
<script type="text/javascript">
    if ($("#jam_selesai").val()) {
        $("#harga").val("<?php echo $totharga1?>");
    }
       else { 
        $("#harga").val("<?php echo $totharga?>"); 
    };

    if ($("#jam_selesai").val()) {
        $("#hasil1").show();
    }
       else { 
        $("#timer").show(); 
    };
</script>
<?php 
    }
    } else {
?>
<td>
    <form method="post">
    <input name="status" type="hidden" value="Online"/>
    <input name="id_unit" type="hidden" value="1"/>
    <input name="nama" type="text" onblur="this.value=(this.value=='') ? 'Nama' : this.value;" onfocus="this.value=(this.value=='Nama') ? '' : this.value;" value="Nama" />
    <input name="alamat" type="text" onblur="this.value=(this.value=='') ? 'Alamat' : this.value;" onfocus="this.value=(this.value=='Alamat') ? '' : this.value;" value="Alamat" /> 
    <input name="jml_pemain" type="text" onblur="this.value=(this.value=='') ? 'Jumlah' : this.value;" onfocus="this.value=(this.value=='Jumlah') ? '' : this.value;" value="Jumlah" />
    <input name="jam_mulai" type="text" id="t1" readonly="" />
    <input type="submit" name="add" id='btn' class="bton bton-dark" value="Start">
    </form>
</td>
<?php    
    }
?>