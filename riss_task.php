<?php

define("BIT_MASK", 0xFFFFFFFF);
function read_file($filePath)
{
    if (!file_exists($filePath)) {
        return false;
    }
    return file_get_contents($filePath);
}
function file_to_byte_array($fileContent)
{
    // išskaidom i individualius charus
    $charArray = str_split($fileContent);
    // pritaikom ord() funkcija kiekvinam elementui t.y. paverčiam i ascii
    $byteArray = array_map('ord', $charArray);
    return $byteArray;
}

function process_file($filePath)
{
    return file_to_byte_array(read_file($filePath));
}

function rotr($x, $n)
{
    // pirma i desine per N
    // tada perkeliam tą ka praradom pradžioje naudodami LS
    // sujungiam ir sulyginam rezultatą
    return (($x >> $n) | ($x << (32 - $n))) & BIT_MASK;
}

function sha_256($bytes)
{
    // Pirmų 8 pirminių skaičių (2, 3, ...) šaknies trupmeninės dalies 32 bitai
    // sqrt(2) = 1.41421356237... -> 0.41421356237... -> 0.0110101... -> hex 0x6a09...
    $h = [
        0x6a09e667, 0xbb67ae85, 0x3c6ef372, 0xa54ff53a,
        0x510e527f, 0x9b05688c, 0x1f83d9ab, 0x5be0cd19
    ];
    // Pirmų 64 pirminių skaičių (2, 3, ...) kūbinės šaknies trupmeninės dalies 32 bitai
    // Išesmės tas pats tik, kad su kūbine šaknimi
    $k = [
        0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
        0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74,0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
        0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
        0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351,0x14292967,
        0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
        0xa2bfe8a1, 0xa81a664b, 0xc24b8b70,0xc76c51a3,0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
        0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
        0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2
    ];

    // ilgis
    $length = count($bytes) * 8;

    // Pridedam 0x80 = 0b10000000
    $bytes[] = 0x80;

    // Toliau dedam nulius kol masyvo dydis bus = 56
    while ((count($bytes) % 64) != 56) {
        // echo count($bytes) . " % " . "64 = " . (count($bytes) % 64) . "\n";
        $bytes[] = 0x00;
    }

    // Pridedam orginalu masyvo dydi į galą pvz. petras = 6 raides * 8 bytai = 48
    for ($i = 7; $i >= 0; $i--) {
        // echo decbin($length) . " >> "  . $i * 8 . "\n";
        // echo decbin(($length >> ($i * 8)) & 0xFF) . "\n";
        $bytes[] = ($length >> ($i * 8)) & 0xFF;
    }
    // echo "\n";
    // $len = count($bytes) * 8;

    // for ($i = $len - 8; $i < $len; $i++) {
    //     echo decbin($bytes[$i]) . "\n";
    // }

    // išskaidom į 64 bytų (512 bitų) blokus kuriuos skaičiuosim individualiai
    $chunks = array_chunk($bytes, 64);

    foreach ($chunks as $chunk) {

        $w = [];
        // Sujungiame kas 4 baitus į vieną 32 bitų skaičių žodi
        // pvz. [112,101,116,114] -> 0x70657472
        // 112 -> 0x70 << 24 -> 0x70000000
        // 101 -> 0x65 << 16 -> 0x00650000
        // ir t.t kol galiausiai atliekam OR operacija ir sujungiam viską į viena elementą
        // t.y 0x7065...
        for ($i = 0; $i < 16; $i++) {
            $j = $i * 4;
            $w[$i] =
                ($chunk[$j] << 24) |
                ($chunk[$j + 1] << 16) |
                ($chunk[$j + 2] << 8) |
                $chunk[$j + 3];
            // echo "0x" . dechex($w[$i]) . "\n";
            // echo str_pad(decbin($w[$i] & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";

        }

        // kadangi w0-15 jau turim w16-63 gaunami iš čia
        for ($i = 16; $i < 64; $i++) {

            // sukam i dešinę per 7, sukam i dešine per 18 stumiam į dešinę 3
            $s0 = rotr($w[$i - 15], 7) ^ rotr($w[$i - 15], 18) ^ ($w[$i - 15] >> 3);
            // sukam i dešinę per 17, sukam i dešine per 19 stumiam į dešinę 10
            $s1 = rotr($w[$i - 2], 17) ^ rotr($w[$i - 2], 19) ^ ($w[$i - 2] >> 10);
            // sudedam viską ir sulyginam kad butu 32 bitai
            $w[$i] = ($s1 + $w[$i - 7] + $s0 + $w[$i - 16]) & BIT_MASK;
            // $val1 = $i - 7;
            // $val2 = $i - 16;
            // echo "$i - S1   " . str_pad(decbin($s1 & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            // echo "$i - W$val1   " . str_pad(decbin($w[$i - 7] & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            // echo "$i - S0   " . str_pad(decbin($s0 & BIT_MASK) , 32, '0', STR_PAD_LEFT) . "\n";
            // echo "$i - W$val2   " . str_pad(decbin($w[$i - 16] & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            // echo "$i - W$i  " . str_pad(decbin($w[$i] & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
        }

        list($a, $b, $c, $d, $e, $f, $g, $h0) = $h;

        for ($i = 0; $i < 64; $i++) {

            // sukam i dešinę per 6, sukam i dešine per 11 sukam i dešine per 25
            $S1 = rotr($e, 6) ^ rotr($e, 11) ^ rotr($e, 25);

            // pasirinkimo funkcija                     paprasčiau tai yra tiesiog pažiūrėti
            // ANDinam e ir f                           į e reikšmes kurios pasako iš g ar f imit
            // 1010 & 1100 -> 1000                          e - 1010
            // ~e yra paprastas NOTas 1010 -> 0101          f - 1100
            // ANDinam ~e ir g                              g - 0111
            // 0101 & 0111 -> 0101                          --------
            // ^ yra XOR kurį darom tarp gautų rezultatų    ch - 1101
            // 1000 ^ 0101 -> 1101
            $ch = ($e & $f) ^ ((~$e) & $g);
            // if ($i == 1) {
            //     echo "e  - " . str_pad(decbin($e & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            //     echo "f  - " . str_pad(decbin($f & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            //     echo "g  - " . str_pad(decbin($g & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            //     echo str_repeat("-", 32) . "\n";
            //     echo "ch - " . str_pad(decbin($ch & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            // }
            // sudedam viską  ir sulyginam
            $temp1 = ($h0 + $S1 + $ch + $k[$i] + $w[$i]) & BIT_MASK;

            // sukam i dešinę per 2, sukam i dešine per 13 sukam i dešine per 22
            $S0 = rotr($a, 2) ^ rotr($a, 13) ^ rotr($a, 22);

            // palyginam visus tris ir kurių yra daugiausia pasiliekam
            //                        1010
            //                        0011
            //                        1101
            //                        ----
            //                        1011
            $maj = ($a & $b) ^ ($a & $c) ^ ($b & $c);
            // if ($i == 1) {
            //     echo "a   - " . str_pad(decbin($a & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            //     echo "b   - " . str_pad(decbin($b & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            //     echo "c   - " . str_pad(decbin($c & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            //     echo str_repeat("-", 32) . "\n";
            //     echo "maj - " . str_pad(decbin($maj & BIT_MASK), 32, '0', STR_PAD_LEFT) . "\n";
            // }
            // sudedam viska ir sulyginam
            $temp2 = ($S0 + $maj) & BIT_MASK;

            // Priskyrimai ir sudetis
            // kiekviena iteracija vyksta su skirtingais skaičiais
            $h0 = $g;
            $g = $f;
            $f = $e;
            $e = ($d + $temp1) & BIT_MASK;
            $d = $c;
            $c = $b;
            $b = $a;
            $a = ($temp1 + $temp2) & BIT_MASK;
        }

        // Atnaujinam savo h su gautais a, b, ...
        $h[0] = ($h[0] + $a) & BIT_MASK;
        $h[1] = ($h[1] + $b) & BIT_MASK;
        $h[2] = ($h[2] + $c) & BIT_MASK;
        $h[3] = ($h[3] + $d) & BIT_MASK;
        $h[4] = ($h[4] + $e) & BIT_MASK;
        $h[5] = ($h[5] + $f) & BIT_MASK;
        $h[6] = ($h[6] + $g) & BIT_MASK;
        $h[7] = ($h[7] + $h0) & BIT_MASK;
    }

    $hash = '';
    // paverciam i hex ir sudedam visus rezultatus
    foreach ($h as $value) {
        $hash .= str_pad(dechex($value), 8, '0', STR_PAD_LEFT);
    }

    return $hash;
}

$filePath = $argv[1] ?? '';

if ($filePath && !file_exists($filePath)) {
    print ("Failas neegzistuoja!\n");
    $filePath = '';
}

while (!$filePath) {
    $filePath = readline("Įveskite failo pavadinimą: ");

    if (!file_exists($filePath)) {
        print ("Failas neegzistuoja!\n");
        $filePath = '';
    }
}

$byteArray = process_file($filePath);
$hash = sha_256($byteArray);

echo "SHA-256: " . $hash . "\n";