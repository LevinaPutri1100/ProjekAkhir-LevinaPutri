<?php

$nama_input = $nohp_input = $voucher_txt = "";
$jarak_input = 0;
$layanan_sel = $metode_sel = "";
$errors = [];

$subtotal = $diskonMember = $diskonVoucher = $biayaAdmin = $totalBayar = $poinBaru = 0;
$pelanggan1 = $layanan1 = $metodeBayar = $voucher1 = $transaksi1 = null;

class User{
    public $nama;
    public $noHp;
    public const STATUS = "Member";

    public function __construct($nama, $noHp){
        $this->nama = $nama;
        $this->noHp = $noHp;
    }

    public function getNama(){
        return " Nama: " . $this->nama;
    }

    public function getNoHp(){
        return " No hp: " . $this->noHp;
    }

    public function getStatus(){
        return " Status: " . self::STATUS;
    }
}

class Driver extends User{
    private $rating;
    private $total;

    public function __construct($nama, $noHp, $rating, $total){
        parent::__construct($nama, $noHp);
        $this->rating = $rating;
        $this->total = $total;
    }

    public function getNama(){
        return $this->nama;
    }

    public function getTotal(){
        return $this->total;
    }
}

class Pelanggan extends User{
    private $poin;

    public function __construct($nama, $noHp, $poin = 0){
        parent::__construct($nama, $noHp);
        $this->poin = $poin;
    }

    public function getStatus(){
        return " Status: Pelanggan " . self::STATUS;
    }

    public function getPoin(){
        return $this->poin;
    }

    public function tambahPoint($totalPembayaran){
        $poinBaru = floor($totalPembayaran / 10000);
        $this->poin += $pouBaru;
        return $poinBaru;
    }
}

class Layanan{
    public const GORIDEREGULER = 2500;
    public const GORIDEPRIORITAS = 3000;
    public const GOCAR = 4000;
    public const GOCARXL = 6000;
    public const GOFOOD = 2000;
    protected $jenis;
    private $tarif;

    public function __construct($jenis){
        $this->jenis = $jenis;

        if($jenis=="GORIDEREGULER"){
            $this->tarif=self::GORIDEREGULER;
        }
        elseif($jenis=="GORIDEPRIORITAS"){
            $this->tarif=self::GORIDEPRIORITAS;
        }
        elseif($jenis=="GOCAR"){
            $this->tarif=self::GOCAR;
        }
        elseif($jenis=="GOCARXL"){
            $this->tarif=self::GOCARXL;
        }
        elseif($jenis=="GOFOOD"){
            $this->tarif=self::GOFOOD;
        }
        else {
            $this->tarif = 0;
        }
    }

    public function getTarif(){
        return $this->tarif;
    }

    public function getJenisLayanan(){
        return $this->jenis;
    }
}

class Voucher{
    public $kodeVoucher;
    public $diskonPersen = 0;

    public function __construct($kodeVoucher){
        $this->kodeVoucher = $kodeVoucher;
        $this->hitungDiskon();
    }

    public function hitungDiskon(){
        if($this->kodeVoucher=="HEMAT10"){
            $this->diskonPersen = 10;
        }
        elseif($this->kodeVoucher=="HEMAT20"){
            $this->diskonPersen = 20;
        }
        elseif($this->kodeVoucher=="HEMAT30"){
            $this->diskonPersen = 30;
        }
        else{
            $this->diskonPersen = 0;
        }
    }
}

class Pembayaran{
    public function getMetode(){
        return " Belum memilih Metode ";
    }
    public function getBiayaAdmin(){
        return 0;
    }
}

class EWallet extends Pembayaran{
    public function getMetode(){
        return " E-Wallet ";
    }
    public function getBiayaAdmin(){
        return 1000;
    }
}

class TransferBank extends Pembayaran{
    public function getMetode(){
        return " Transfer Bank ";
    }
    public function getBiayaAdmin(){
        return 2500;
    }
}

class Cash extends Pembayaran{
    public function getMetode(){
        return " Cash ";
    }
    public function getBiayaAdmin(){
        return 0;
    }
}

class Transaksi{
    public $pelanggan;
    public $layanan;
    public $pembayaran;
    public $voucher;
    public $jarakTempuh;
    private static $totalTransaksi = 0;

    public function __construct($pelanggan, $layanan, $pembayaran, $voucher, $jarakTempuh){
        $this->pelanggan = $pelanggan;
        $this->layanan = $layanan;
        $this->pembayaran = $pembayaran;
        $this->voucher = $voucher;
        $this->jarakTempuh = $jarakTempuh;
        self::$totalTransaksi++;
    }

    public static function getTotalTransaksi(){
        return self::$totalTransaksi;
    }

    public function hitungSubTotal(){
        return $this->jarakTempuh * $this->layanan->getTarif();
    }

    public function hitungDiskonMember(){
        $subTotal = $this->hitungSubTotal();
        if($subTotal > 50000){
            return $subTotal * 0.05;
        } 
        return 0;
    }

    public function hitungDiskonVoucher(){
        return $this->hitungSubTotal() * ($this->voucher->diskonPersen / 100);
    }

    public function hitungBiayaAdmin(){
        return $this->pembayaran->getBiayaAdmin();
    }

    public function hitungTotal(){
        $subTotal = $this->hitungSubTotal();
        $diskonMember = $this->hitungDiskonMember();
        $diskonVoucher = $this->hitungDiskonVoucher();
        $biayaAdmin = $this->hitungBiayaAdmin();

        $total = $subTotal - $diskonMember - $diskonVoucher + $biayaAdmin;
        return ($total < 0 ) ? 0 : $total;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_input = trim($_POST['nama']);
    $nohp_input = trim($_POST['nohp']);
    $layanan_sel = $_POST['layanan'];
    $metode_sel = $_POST['metode'];
    $voucher_txt = trim($_POST['voucher']);
    $jarak_input = floatval($_POST['jarak']);


    if ($nama_input == "") {
        $errors[] = "Nama pelanggan tidak boleh kosong.";
    }
    
   
    if (!is_numeric($nohp_input) || $nohp_input < 1000000000) {
        $errors[] = "No hp kurang dari 10 digit atau bukan angka yang valid.";
    }

    if ($jarak_input <= 0) {
        $errors[] = "Jarak tempuh harus lebih besar dari 0 Km.";
    }

    if (empty($errors)) {
        $pelanggan1 = new Pelanggan($nama_input, $nohp_input);
        $layanan1   = new Layanan($layanan_sel);

        if ($metode_sel == "EWallet") {
            $metodeBayar = new EWallet();
        } elseif ($metode_sel == "TransferBank") {
            $metodeBayar = new TransferBank();
        } else {
            $metodeBayar = new Cash();
        }

        $voucher1   = new Voucher($voucher_txt);
        $transaksi1 = new Transaksi($pelanggan1, $layanan1, $metodeBayar, $voucher1, $jarak_input);

        $subtotal      = $transaksi1->hitungSubTotal();
        $diskonMember  = $transaksi1->hitungDiskonMember();
        $diskonVoucher = $transaksi1->hitungDiskonVoucher();
        $biayaAdmin    = $transaksi1->hitungBiayaAdmin();
        $totalBayar    = $transaksi1->hitungTotal();

        $poinBaru = $pelanggan1->tambahPoint($totalBayar);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Layanan Ojek Online</title>
</head>
<body>

<?php if (!empty($errors)): ?>
        <p>Peringatan:</p>
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="box-form">
    <h2>Order Gojek</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label>Nama Pelanggan:</label>
            <input type="text" name="nama" value="<?php echo htmlspecialchars($nama_input); ?>" required>
        </div>
        <div class="form-group">
            <label>No HP:</label>
            <input type="text" name="nohp" value="<?php echo htmlspecialchars($nohp_input); ?>" required>
        </div>
        <div class="form-group">
            <label>Jenis Layanan:</label>
            <select name="layanan">
                <option value="GORIDEREGULER" <?php if($layanan_sel=="GORIDEREGULER") echo "selected"; ?>>Goride Reguler</option>
                <option value="GORIDEPRIORITAS" <?php if($layanan_sel=="GORIDEPRIORITAS") echo "selected"; ?>>Goride Prioritas</option>
                <option value="GOCAR" <?php if($layanan_sel=="GOCAR") echo "selected"; ?>>Gocar</option>
                <option value="GOCARXL" <?php if($layanan_sel=="GOCARXL") echo "selected"; ?>>Gocar XL</option>
                <option value="GOFOOD" <?php if($layanan_sel=="GOFOOD") echo "selected"; ?>>Gofood</option>
            </select>
        </div>
        <div class="form-group">
            <label>Metode Pembayaran:</label>
            <select name="metode">
                <option value="EWallet" <?php if($metode_sel=="EWallet") echo "selected"; ?>>E-Wallet</option>
                <option value="TransferBank" <?php if($metode_sel=="TransferBank") echo "selected"; ?>>Transfer Bank</option>
                <option value="Cash" <?php if($metode_sel=="Cash") echo "selected"; ?>>Cash</option>
            </select>
        </div>
        <div class="form-group">
            <label>Kode Voucher:</label>
            <input type="text" name="voucher" value="<?php echo htmlspecialchars($voucher_txt); ?>">
        </div>
        <div class="form-group">
            <label>Jarak Tempuh (Km):</label>
            <input type="number" step="0.1" name="jarak" value="<?php echo $jarak_input; ?>" required>
        </div>
        <button type="submit" class="btn-hitung">Hitung Transaksi</button> 
    </form>
</div>

<?php if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($errors) && $transaksi1 !== null): ?>
<div>
    <br><h3 style="text-align:left; margin:0;">Transaksi Gojek</h3><br>
    <?php echo $pelanggan1->getNama(); ?><br>
    <?php echo $pelanggan1->getNoHp(); ?><br>
    <?php echo $pelanggan1->getStatus(); ?><br>
   
    Layanan : <?php echo $layanan1->getJenisLayanan(); ?><br>
    Jarak   : <?php echo $transaksi1->jarakTempuh; ?> Km<br>
    Metode  : <?php echo $metodeBayar->getMetode(); ?><br>
    Voucher : <?php echo $voucher1->kodeVoucher; ?><br>
    
    <br>Subtotal       : Rp. <?php echo number_format($subtotal); ?><br>
    Diskon Member  : Rp. -<?php echo number_format($diskonMember); ?><br>
    Diskon Voucher : Rp. -<?php echo number_format($diskonVoucher); ?><br>
    Biaya Admin    : Rp. <?php echo number_format($biayaAdmin); ?><br>
  
    <br><b>TOTAL BAYAR   : Rp. <?php echo number_format($totalBayar); ?></b><br>
   
    Poin Baru      : +<?php echo $poinBaru; ?><br>
</div>
<?php endif; ?>

</body>
</html>