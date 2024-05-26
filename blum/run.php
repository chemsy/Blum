<?php
$refresh_tokens = file('refresh.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Looping infinite untuk menjalankan siklus refresh dan farming
while (true) {
    // Looping untuk setiap token refresh
    foreach ($refresh_tokens as $index => $refresh) {
        $refresh = trim($refresh);
        $data = json_encode(['refresh' => $refresh]);

        // Refresh Token
        $url = 'https://gateway.blum.codes/v1/auth/refresh';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
            'Sec-Ch-Ua: "Chromium";v="125", "Not.A/Brand";v="24"',
            'Accept: application/json, text/plain, */*',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.6422.60 Safari/537.36',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Origin: https://telegram.blum.codes',
            'Sec-Fetch-Site: same-site',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Dest: empty',
            'Referer: https://telegram.blum.codes/',
            'Accept-Language: en-US,en;q=0.9',
            'Priority: u=1, i'
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            echo 'Gagal Mendapat Respon Lanjutkan' . "\n";
            curl_close($ch);
        }

        $json = json_decode($response, true);
        if (!isset($json['access']) || !isset($json['refresh'])) {
            echo "Gagal mendapatkan token baru\n";
            curl_close($ch);
        }

        $tokenblum = $json['access'];
        $refresh = $json['refresh'];
        echo "Your New Refresh Token: " . $refresh . "\n";

        curl_close($ch);

    // Farming claim
    $url = "https://game-domain.blum.codes/api/v1/farming/claim";
    $headers = [
        "accept: application/json, text/plain, */*",
        "accept-language: en-US,en;q=0.9",
        "authorization: Bearer $tokenblum",
        "content-length: 0",
        "origin: https://telegram.blum.codes",
        "priority: u=1, i",
        "referer: https://telegram.blum.codes/",
        "sec-ch-ua: \"Chromium\";v=\"124\", \"Microsoft Edge\";v=\"124\", \"Not-A.Brand\";v=\"99\", \"Microsoft Edge WebView2\";v=\"124\"",
        "sec-ch-ua-mobile: ?0",
        "sec-ch-ua-platform: \"Windows\"",
        "sec-fetch-dest: empty",
        "sec-fetch-mode: cors",
        "sec-fetch-site: same-site",
        "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0.0"
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    if ($response === false) {
        echo 'Gagal Mendapat Respon Lanjutkan' . "\n";
        curl_close($ch);
    }

    $clam = json_decode($response, true);
    if (isset($clam['message'])) {
        $clim = $clam['message'];
        echo $clim . "\n";
    } else {
        echo "Gagal mendapatkan pesan farming claim\n";
    }

    curl_close($ch);

    // Farming start
    $url = "https://game-domain.blum.codes/api/v1/farming/start";
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    if ($response === false) {
        echo 'Gagal Mendapat Respon Lanjutkan' . "\n";
        curl_close($ch);
    }

    $start = json_decode($response, true);
    if (isset($start['endTime'])) {
        $time = $start['endTime'];
        $date = date("H:i:s", $time / 1000);
        echo "Waktu claim: " . $date . "\n";
    } else {
        echo "Gagal mendapatkan waktu farming start\n";
    }

    curl_close($ch);

    // Get balance
    $url = "https://game-domain.blum.codes/api/v1/user/balance";
    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);
    if ($response === false) {
        echo 'Gagal Mendapat Respon Lanjutkan'. "\n";
        curl_close($curl);
    }

    $jsonres = json_decode($response, true);
    if (isset($jsonres['availableBalance'])) {
        $balance = $jsonres['availableBalance'];
        echo "Your Balance: " . $balance . "\n";
    } else {
        echo "Gagal mendapatkan balance\n";
    }

    curl_close($curl);
    $refresh_tokens[$index] = $refresh;

    file_put_contents('refresh.txt', implode("\n", $refresh_tokens));


}
echo "AFK 5 Menit...\n";
sleep(300);
}
?>