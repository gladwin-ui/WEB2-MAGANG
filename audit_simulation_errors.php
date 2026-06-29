<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Bug;

$bugs = Bug::where('import_job_id', 55)->orderBy('id')->get();

$spamKeywords = [
    'viagra', 'casino', 'slot', 'judi', 'credit', 'loan',
    'pinjol', 'gratis', 'free', 'promo', 'discount', 'diskon',
    'click here', 'klik disini', 'visit now', 'kunjungi', 'daftar sekarang', 'bonus',
    'win', 'menang', 'dapatkan', 'hadiah', 'prize', 'cash', 'uang',
    'jual', 'beli', 'harga', 'sewa'
];

$outOfContextKeywords = [
    'saham', 'investasi', 'trading', 'forex', 'crypto', 'kripto', 'reksa dana', 'reksadana', 'obligasi', 'bursa',
    'sushi', 'kuliner', 'restoran', 'makanan', 'masakan', 'resep', 'kue', 'brownies', 'roti', 'baking',
    'wisata', 'liburan', 'traveling', 'travelling', 'hotel', 'tiket', 'penerbangan', 'destinasi', 'lombok', 'jepang', 'yogyakarta',
    'loker', 'lowongan', 'karir', 'recruit', 'marketing', 'sales',
    'skincare', 'makeup', 'kosmetik', 'jerawat', 'kulit', 'wajah', 'shampo', 'sabun',
    'diet', 'gym', 'fitness', 'kalori', 'suplemen', 'protein', 'otot',
    'zodiak', 'ramalan', 'bintang', 'horoskop', 'shio', 'keberuntungan',
    'gosip', 'artis', 'selebritis', 'menikah', 'pacaran', 'viral',
    'bpjs', 'asuransi', 'klaim', 'premi', 'kesehatan',
    'burung', 'ternak', 'pakan', 'kenari', 'lovebird', 'hewan', 'kucing', 'anjing',
    'tanaman', 'monstera', 'pupuk', 'pot', 'kebun', 'taman', 'hias',
    'film', 'movie', 'nonton', 'netflix', 'sinema', 'bioskop', 'drama', 'reels', 'tiktok', 'instagram', 'youtube',
    'sekolah', 'kuliah', 'beasiswa', 'universitas', 'akademik', 'kursus', 'les', 'desainer', 'portfolio', 'cv', 'fresh graduate',
    'tutorial', 'tips', 'panduan', 'cara membuat', 'cara budidaya', 'cara ternak', 'cara download', 'cara mendaftar'
];

$contextKeywords = [
    'posko', 'lapangan', 'fungsi', 'berfungsi', 'nyala', 'hidup', 'jalan', 'running', 
    'operasi', 'operasional', 'log', 'logger', 'arus', 'tegangan', 'solder', 'solderan', 
    'kabel', 'konektor', 'connector', 'pcb', 'mainboard', 'board', 'power', 'baterai', 
    'battery', 'charger', 'charge', 'sensor', 'suhu', 'temperature', 'humidity', 'kelembapan', 
    'lcd', 'display', 'screen', 'layar', 'oled', 'tft', 'led', 'indikator', 'indicator', 
    'relay', 'kontaktor', 'switch', 'tombol', 'button', 'keypad', 'buzzer', 'alarm', 
    'sirine', 'fuse', 'sekring', 'mcb', 'breaker', 'dcdc', 'converter', 'regulator', 
    'ldo', 'buck', 'boost', 'mcu', 'cpu', 'soc', 'stm32', 'esp32', 'arduino', 'raspberry', 
    'pi', 'avr', 'pic', 'ram', 'flash', 'eeprom', 'spi', 'i2c', 'uart', 'usart', 
    'rs485', 'rs232', 'can bus', 'modbus', 'ethernet', 'wifi', 'bluetooth', 'rfid', 
    'gps', 'antena', 'sinyal', 'signal', 'firmware', 'software', 'program', 'code', 
    'error', 'fault', 'bug', 'crash', 'hang', 'freeze', 'reset', 'reboot', 'boot', 
    'bootloader', 'sistem', 'system', 'aplikasi', 'app', 'database', 'db', 'server', 
    'client', 'api', 'http', 'mqtt', 'tcp', 'udp', 'ip', 'port', 'koneksi', 
    'connection', 'network', 'jaringan', 'cloud', 'dashboard', 'ui', 'ux', 'web', 
    'website', 'mobile', 'android', 'ios', 'baut', 'sekrup', 'screw', 'nut', 'ring', 
    'washer', 'spacer', 'standoff', 'chassis', 'casing', 'enclosure', 'box', 'panel', 
    'rack', 'mount', 'bracket', 'hinge', 'engsel', 'lensa', 'kamera', 'camera', 
    'optik', 'optical', 'laser', 'led', 'diode', 'transistor', 'mosfet', 'igbt', 
    'capacitor', 'kapasitor', 'resistor', 'induktor', 'inductor', 'coil', 'trafo', 
    'transformer', 'motor', 'servo', 'stepper', 'solenoid', 'valve', 'pump', 'pompa', 
    'fan', 'kipas', 'blower', 'heatsink', 'pendingin', 'cooler', 'thermal', 'panas', 
    'dingin', 'suhu', 'temp', 'derajat', 'celcius', 'vibrasi', 'vibration', 'getaran', 
    'shock', 'benturan', 'drop', 'jatuh', 'pressure', 'tekanan', 'flow', 'aliran', 
    'level', 'jarak', 'distance', 'proximity', 'limit', 'switch', 'encoder', 'potensio', 
    'potentiometer', 'adc', 'dac', 'pwm', 'dma', 'interrupt', 'timer', 'clock', 
    'crystal', 'osilator', 'oscillator', 'pll', 'watchdog', 'wdt', 'brownout', 'bod', 
    'reset', 'por', 'lvd', 'pvd', 'sleep', 'wake', 'standby', 'power down', 'low power', 
    'wear leveling', 'bad block', 'ecc', 'checksum', 'crc', 'parity', 'noise', 'ripple', 
    'spike', 'surge', 'esd', 'emi', 'emc', 'shield', 'shielding', 'ground', 'gnd', 
    'earth', 'short', 'hubung singkat', 'open', 'putus', 'loose', 'longgar', 'kendor', 
    'retak', 'crack', 'patah', 'pecah', 'bocor', 'leak', 'rembes', 'karat', 'corrosion', 
    'korosi', 'oksidasi', 'debu', 'dust', 'air', 'water', 'moisture', 'embun', 'fog', 
    'kondensasi', 'condensation', 'kelembaban', 'humidity', 'panas', 'heat', 'overheat', 
    'overtemperature', 'cold', 'dingin', 'freeze', 'beku', 'salju', 'snow', 'es', 
    'ice', 'angin', 'wind', 'hujan', 'rain', 'petir', 'lightning', 'surge', 'esd', 
    'calibrat', 'kalibrasi', 'ukur', 'measurement', 'uji', 'pengujian', 'testing', 
    'test', 'coba', 'cobain', 'run', 'running', 'jalan', 'start', 'stop', 'pause', 
    'resume', 'reset', 'clear', 'clean', 'format', 'load', 'write', 'read', 'tulis', 
    'baca', 'save', 'simpan', 'kirim', 'send', 'receive', 'terima', 'data', 'file', 
    'memori', 'memory', 'sd card', 'flash', 'eeprom', 'produksi'
];

function simulateIsSpam($text, $spamKeywords, $outOfContextKeywords, $contextKeywords, &$matchedDetails) {
    if (!$text || !is_string($text)) return true;
    
    $textLower = strtolower(trim($text));
    if (strlen($textLower) < 5) return true;
    
    $hasHex = preg_match('/0x[0-9a-f]+/i', $textLower);
    if (!$hasHex && preg_match('/([a-zA-Z\.\-\*\_])\1{4,}/', $textLower)) {
        $matchedDetails = 'repeated character';
        return true;
    }
    
    if (preg_match('/^[0-9\W_]+$/', $textLower)) {
        $matchedDetails = 'digits/symbols';
        return true;
    }
    
    $cleanedText = trim(preg_replace('/[^a-z\s]/', '', $textLower));
    $testWords = ['test', 'testing', 'coba', 'cobain', 'asdf', 'qwerty', 'dummy', 'hello world', 'tes', 'tess', 'coba coba', 'dummy data'];
    if (in_array($cleanedText, $testWords)) {
        $matchedDetails = 'dummy test';
        return true;
    }
    
    $hasUrl = preg_match('/https?:\/\/\S+|www\.\S+|\b\w+\.(com|net|org|info|xyz|biz|cc|co\.id)\b/', $textLower);
    $hasClickCall = false;
    foreach (['click here', 'klik disini', 'visit now', 'kunjungi', 'daftar sekarang', 'register now', 'join now', 'gabung sekarang'] as $phrase) {
        if (strpos($textLower, $phrase) !== false) { $hasClickCall = true; break; }
    }
    if ($hasUrl || $hasClickCall) {
        $matchedDetails = 'url/click call';
        return true;
    }
    
    // 1. OUT OF CONTEXT KEYWORDS
    foreach ($outOfContextKeywords as $kw) {
        if (preg_match('/\b' . preg_quote($kw, '/') . '\b/', $textLower)) {
            $matchedDetails = 'out of context: ' . $kw;
            return true;
        }
    }
    
    // 2. SPAM KEYWORDS
    foreach ($spamKeywords as $kw) {
        if (preg_match('/\b' . preg_quote($kw, '/') . '\b/', $textLower)) {
            $matchedDetails = 'spam keyword: ' . $kw;
            return true;
        }
    }
    
    // 3. CONTEXT KEYWORDS
    $contextMatches = 0;
    $matchedContext = [];
    foreach ($contextKeywords as $kw) {
        if (preg_match('/\b' . preg_quote($kw, '/') . '\b/', $textLower)) {
            $contextMatches++;
            $matchedContext[] = $kw;
        }
    }
    
    if ($contextMatches >= 1) {
        $matchedDetails = 'ham context: ' . implode(', ', $matchedContext);
        return false;
    }
    
    $matchedDetails = 'zero context matches';
    return true;
}

echo "=== DETAILED AUDIT FOR SIMULATED MISCLASSIFICATIONS ===\n";
foreach ($bugs as $b) {
    $fullText = implode(' ', array_filter([
        $b->description,
        $b->reproduce_steps,
        $b->expected_result,
        $b->environment,
    ]));
    
    $details = '';
    $simSpam = simulateIsSpam($fullText, $spamKeywords, $outOfContextKeywords, $contextKeywords, $details);
    
    $isActuallySpam = ($b->id >= 1106); // 1107 to 1157 are the 50 spam reports
    
    if ($simSpam !== $isActuallySpam) {
        $type = $isActuallySpam ? "SPAM -> HAM (False Negative)" : "HAM -> SPAM (False Positive)";
        echo "ID: {$b->id} | Title: {$b->title} | TYPE: {$type} | Details: {$details}\n";
    }
}
