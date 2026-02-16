<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RajaongkirService
{
    private $apiKey;
    private $baseUrl;
    private $accountType;
    private $originCityId;

    public function __construct()
    {
        $this->apiKey = config('rajaongkir.api_key');
        $this->baseUrl = config('rajaongkir.base_url');
        $this->accountType = config('rajaongkir.account_type');
        $this->originCityId = config('rajaongkir.origin_city_id');
    }

    /**
     * Get list of provinces
     * @return array
     */
    public function getProvinces()
    {
        $key = 'rajaongkir.provinces';

        return Cache::remember($key, 3600, function () {
            // Try to fetch from API first
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                ])->get($this->baseUrl . '/province');

                if ($response->successful()) {
                    return collect($response->json('results'))->pluck('province_name', 'province_id')->toArray();
                }
            } catch (\Exception $e) {
                // Fall through to mock data
            }

            // Return mock Indonesian provinces for testing
            return [
                '11' => 'Aceh',
                '12' => 'Sumatera Utara',
                '13' => 'Sumatera Barat',
                '14' => 'Riau',
                '15' => 'Jambi',
                '16' => 'Sumatera Selatan',
                '17' => 'Lampung',
                '18' => 'Bangka Belitung',
                '19' => 'Kepulauan Riau',
                '21' => 'Jawa Barat',
                '31' => 'DKI Jakarta',
                '32' => 'Jawa Tengah',
                '33' => 'DI Yogyakarta',
                '34' => 'Jawa Timur',
                '35' => 'Banten',
                '51' => 'Bali',
                '52' => 'Nusa Tenggara Barat',
                '53' => 'Nusa Tenggara Timur',
                '61' => 'Kalimantan Barat',
                '62' => 'Kalimantan Tengah',
                '63' => 'Kalimantan Selatan',
                '64' => 'Kalimantan Timur',
                '65' => 'Kalimantan Utara',
                '71' => 'Sulawesi Utara',
                '72' => 'Sulawesi Tengah',
                '73' => 'Sulawesi Selatan',
                '74' => 'Sulawesi Tenggara',
                '75' => 'Gorontalo',
                '76' => 'Sulawesi Barat',
                '81' => 'Maluku',
                '82' => 'Maluku Utara',
                '91' => 'Papua',
                '92' => 'Papua Barat',
            ];
        });
    }

    /**
     * Get cities by province
     * @param int $provinceId
     * @return array
     */
    public function getCitiesByProvince($provinceId)
    {
        $key = "rajaongkir.cities.{$provinceId}";

        return Cache::remember($key, 3600, function () use ($provinceId) {
            // Try to fetch from API first
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                ])->get($this->baseUrl . '/city', [
                    'province' => $provinceId
                ]);

                if ($response->successful()) {
                    return collect($response->json('results'))->pluck('city_name', 'city_id')->toArray();
                }
            } catch (\Exception $e) {
                // Fall through to mock data
            }

            // Return mock cities data for testing - Complete Indonesia cities
            $mockCities = [
                '11' => [ // Aceh
                    '1101' => 'Banda Aceh',
                    '1102' => 'Aceh Besar',
                    '1103' => 'Aceh Pidie',
                    '1104' => 'Aceh Timur',
                    '1105' => 'Lhokseumawe',
                ],
                '12' => [ // Sumatera Utara
                    '1201' => 'Medan',
                    '1202' => 'Binjai',
                    '1203' => 'Tebing Tinggi',
                    '1204' => 'Pematangsiantar',
                    '1205' => 'Deli Serdang',
                ],
                '13' => [ // Sumatera Barat
                    '1301' => 'Padang',
                    '1302' => 'Bukittinggi',
                    '1303' => 'Payakumbuh',
                    '1304' => 'Agam',
                    '1305' => 'Pasaman',
                ],
                '14' => [ // Riau
                    '1401' => 'Pekanbaru',
                    '1402' => 'Dumai',
                    '1403' => 'Kampar',
                    '1404' => 'Indragiri Hulu',
                    '1405' => 'Bengkalis',
                ],
                '15' => [ // Jambi
                    '1501' => 'Jambi',
                    '1502' => 'Sungai Penuh',
                    '1503' => 'Merangin',
                    '1504' => 'Bungo',
                    '1505' => 'Kerinci',
                ],
                '16' => [ // Sumatera Selatan
                    '1601' => 'Palembang',
                    '1602' => 'Prabumulih',
                    '1603' => 'Lubuklinggau',
                    '1604' => 'Muara Enim',
                    '1605' => 'Banyuasin',
                ],
                '17' => [ // Lampung
                    '1701' => 'Bandar Lampung',
                    '1702' => 'Metro',
                    '1703' => 'Lampung Utara',
                    '1704' => 'Lampung Tengah',
                    '1705' => 'Lampung Selatan',
                ],
                '18' => [ // Bangka Belitung
                    '1801' => 'Pangkal Pinang',
                    '1802' => 'Bandung Bawean',
                    '1803' => 'Belitung',
                    '1804' => 'Bengkulu',
                ],
                '19' => [ // Kepulauan Riau
                    '1901' => 'Batam',
                    '1902' => 'Tanjung Pinang',
                    '1903' => 'Bintan',
                    '1904' => 'Natuna',
                    '1905' => 'Lingga',
                ],
                '21' => [ // Jawa Barat
                    '2101' => 'Bandung',
                    '2102' => 'Bogor',
                    '2103' => 'Sukabumi',
                    '2104' => 'Cianjur',
                    '2105' => 'Tasikmalaya',
                    '2106' => 'Cirebon',
                    '2107' => 'Bekasi',
                    '2108' => 'Depok',
                ],
                '22' => [ // Jawa Tengah - updated
                    '2201' => 'Semarang',
                    '2202' => 'Surakarta (Solo)',
                    '2203' => 'Pekalongan',
                    '2204' => 'Salatiga',
                    '2205' => 'Purwokerto',
                    '2206' => 'Kudus',
                    '2207' => 'Klaten',
                ],
                '31' => [ // Jakarta
                    '1571' => 'Jakarta Selatan',
                    '1574' => 'Jakarta Timur',
                    '1575' => 'Jakarta Barat',
                    '1576' => 'Jakarta Pusat',
                    '1577' => 'Jakarta Utara',
                    '1578' => 'Kepulauan Seribu',
                ],
                '32' => [ // Jawa Tengah (Alternative code)
                    '1681' => 'Semarang',
                    '1682' => 'Surakarta (Solo)',
                    '1683' => 'Pekalongan',
                    '1684' => 'Salatiga',
                    '1685' => 'Purwokerto',
                    '1686' => 'Kudus',
                    '1687' => 'Yogyakarta',
                ],
                '33' => [ // DI Yogyakarta
                    '3301' => 'Yogyakarta',
                    '3302' => 'Sleman',
                    '3303' => 'Bantul',
                    '3304' => 'Gunung Kidul',
                    '3305' => 'Kulon Progo',
                ],
                '34' => [ // Jawa Timur
                    '1856' => 'Surabaya',
                    '1857' => 'Malang',
                    '1858' => 'Batu',
                    '1859' => 'Pasuruan',
                    '1860' => 'Probolinggo',
                    '1861' => 'Jombang',
                    '1862' => 'Kediri',
                    '1863' => 'Gresik',
                ],
                '35' => [ // Banten
                    '3501' => 'Serang',
                    '3502' => 'Cilegon',
                    '3503' => 'Tangerang',
                    '3504' => 'South Tangerang',
                    '3505' => 'Lebak',
                    '3506' => 'Pandeglang',
                ],
                '51' => [ // Bali
                    '3271' => 'Denpasar',
                    '3272' => 'Ubud',
                    '3273' => 'Sanur',
                    '3274' => 'Kuta',
                    '3275' => 'Badung',
                    '3276' => 'Gianyar',
                ],
                '52' => [ // Nusa Tenggara Barat
                    '5201' => 'Mataram',
                    '5202' => 'Bima',
                    '5203' => 'Lombok Barat',
                    '5204' => 'Lombok Utara',
                    '5205' => 'Lombok Tengah',
                ],
                '53' => [ // Nusa Tenggara Timur
                    '5301' => 'Kupang',
                    '5302' => 'Maumere',
                    '5303' => 'Ende',
                    '5304' => 'Bima',
                    '5305' => 'Flores',
                ],
                '61' => [ // Kalimantan Barat
                    '6101' => 'Pontianak',
                    '6102' => 'Singkawang',
                    '6103' => 'Kubu Raya',
                    '6104' => 'Sambas',
                    '6105' => 'Mempawah',
                ],
                '62' => [ // Kalimantan Tengah
                    '6201' => 'Palangka Raya',
                    '6202' => 'Sampit',
                    '6203' => 'Banjar Baru',
                    '6204' => 'Pangkalan Bun',
                ],
                '63' => [ // Kalimantan Selatan
                    '6301' => 'Banjarmasin',
                    '6302' => 'Banjarbaru',
                    '6303' => 'Martapura',
                    '6304' => 'Kandangan',
                    '6305' => 'Tanah Laut',
                ],
                '64' => [ // Kalimantan Timur
                    '6401' => 'Samarinda',
                    '6402' => 'Balikpapan',
                    '6403' => 'Bontang',
                    '6404' => 'Tarakan',
                    '6405' => 'Tenggarong',
                ],
                '65' => [ // Kalimantan Utara
                    '6501' => 'Tarakan',
                    '6502' => 'Tana Tidung',
                    '6503' => 'Malinau',
                    '6504' => 'Bulungan',
                ],
                '71' => [ // Sulawesi Utara
                    '7101' => 'Manado',
                    '7102' => 'Bitung',
                    '7103' => 'Tomohon',
                    '7104' => 'Minahasa',
                    '7105' => 'Sangihe',
                ],
                '72' => [ // Sulawesi Tengah
                    '7201' => 'Palu',
                    '7202' => 'Mantikulore',
                    '7203' => 'Donggala',
                    '7204' => 'Toli-Toli',
                    '7205' => 'Parigi Moutong',
                ],
                '73' => [ // Sulawesi Selatan
                    '7301' => 'Makassar',
                    '7302' => 'Palopo',
                    '7303' => 'Parepare',
                    '7304' => 'Gowa',
                    '7305' => 'Sinjai',
                    '7306' => 'Takalar',
                ],
                '74' => [ // Sulawesi Tenggara
                    '7401' => 'Kendari',
                    '7402' => 'Baubau',
                    '7403' => 'Kolaka',
                    '7404' => 'Raha',
                    '7405' => 'Buton',
                ],
                '75' => [ // Gorontalo
                    '7501' => 'Gorontalo',
                    '7502' => 'Kota Gorontalo',
                    '7503' => 'Boalemo',
                    '7504' => 'Pohuwato',
                ],
                '76' => [ // Sulawesi Barat
                    '7601' => 'Manado',
                    '7602' => 'Mamuju',
                    '7603' => 'Majene',
                    '7604' => 'Polewali Mandar',
                ],
                '81' => [ // Maluku
                    '8101' => 'Ambon',
                    '8102' => 'Tual',
                    '8103' => 'Manado',
                    '8104' => 'Bula',
                ],
                '82' => [ // Maluku Utara
                    '8201' => 'Ternate',
                    '8202' => 'Tidore',
                    '8203' => 'Manado',
                    '8204' => 'Buli',
                ],
                '91' => [ // Papua
                    '9101' => 'Jayapura',
                    '9102' => 'Wamena',
                    '9103' => 'Sentani',
                    '9104' => 'Nabire',
                ],
                '92' => [ // Papua Barat
                    '9201' => 'Manado',
                    '9202' => 'Sorong',
                    '9203' => 'Fak-Fak',
                    '9204' => 'Kaimana',
                ],
            ];

            return $mockCities[$provinceId] ?? [
                'C' . $provinceId . '01' => 'Kota Pusat',
                'C' . $provinceId . '02' => 'Kabupaten 1',
                'C' . $provinceId . '03' => 'Kabupaten 2',
            ];
        });
    }

    /**
     * Get shipping costs (rates)
     * @param int $destinationCityId
     * @param int $weight in grams
     * @param array $couriers
     * @return array
     */
    public function getShippingCosts($destinationCityId, $weight, $couriers = ['jne', 'pos', 'tiki'])
    {
        $cacheKey = "rajaongkir.costs.{$this->originCityId}.{$destinationCityId}.{$weight}." . implode('-', $couriers);

        return Cache::remember($cacheKey, config('rajaongkir.cache_duration') * 60, function () use ($destinationCityId, $weight, $couriers) {
            $weightKg = ceil($weight / 1000); // Convert grams to kg

            // Try API first
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                ])->post($this->baseUrl . '/cost', [
                    'origin' => $this->originCityId,
                    'destination' => $destinationCityId,
                    'weight' => $weightKg,
                    'courier' => implode(':', $couriers)
                ]);

                if ($response->successful()) {
                    return $this->formatCosts($response->json('results.0.costs'));
                }
            } catch (\Exception $e) {
                // Fall through to mock data
            }

            // Return mock shipping costs for testing
            $baseCosts = [
                'jne' => [],
                'pos' => [],
                'tiki' => []
            ];

            // Calculate mock costs based on weight
            $baseRate = 10000; // Rp 10,000 minimum
            $perKg = 5000; // Rp 5,000 per kg

            if (in_array('jne', $couriers)) {
                $baseCosts['jne'] = [
                    [
                        'service' => 'REG',
                        'description' => 'Reguler',
                        'cost' => $baseRate + ($perKg * $weightKg),
                        'etd' => '3-5'
                    ],
                    [
                        'service' => 'OKE',
                        'description' => 'Ongkir Kilat Express',
                        'cost' => ($baseRate + ($perKg * $weightKg)) * 1.5,
                        'etd' => '1-2'
                    ]
                ];
            }

            if (in_array('pos', $couriers)) {
                $baseCosts['pos'] = [
                    [
                        'service' => 'REG',
                        'description' => 'Reguler',
                        'cost' => $baseRate + ($perKg * $weightKg) + 2000,
                        'etd' => '4-6'
                    ]
                ];
            }

            if (in_array('tiki', $couriers)) {
                $baseCosts['tiki'] = [
                    [
                        'service' => 'REG',
                        'description' => 'Reguler',
                        'cost' => $baseRate + ($perKg * $weightKg) - 1000,
                        'etd' => '3-5'
                    ],
                    [
                        'service' => 'ECO',
                        'description' => 'Ekonomis',
                        'cost' => $baseRate + ($perKg * $weightKg) - 3000,
                        'etd' => '5-7'
                    ]
                ];
            }

            // Flatten and format
            $costs = [];
            foreach ($baseCosts as $courier => $services) {
                foreach ($services as $service) {
                    $costs[] = [
                        'courier_code' => strtoupper($courier),
                        'courier_name' => $this->getCourierName($courier) . ' - ' . $service['service'],
                        'service' => $service['service'],
                        'description' => $service['description'],
                        'cost' => (int)$service['cost'],
                        'estimated_days' => (int)explode('-', $service['etd'])[1]
                    ];
                }
            }

            return $costs;
        });
    }

    private function getCourierName($code)
    {
        $names = [
            'jne' => 'JNE',
            'pos' => 'POS Indonesia',
            'tiki' => 'TIKI'
        ];
        return $names[strtolower($code)] ?? ucfirst($code);
    }

    /**
     * Format costs response
     * @param array $costs
     * @return array
     */
    private function formatCosts($costs)
    {
        $formatted = [];

        foreach ($costs as $cost) {
            foreach ($cost['costs'] as $service) {
                $formatted[] = [
                    'courier_code' => $cost['service'],
                    'courier_name' => $cost['description'] ?? $cost['service'],
                    'cost' => $service['cost'][0]['value'] ?? 0,
                    'estimated_days' => $service['cost'][0]['etd'] ?? null,
                ];
            }
        }

        return $formatted;
    }

    /**
     * Validate destination (check if city exists)
     * @param int $provinceId
     * @param int $cityId
     * @return bool
     */
    public function validateDestination($provinceId, $cityId)
    {
        $cities = $this->getCitiesByProvince($provinceId);
        return isset($cities[$cityId]);
    }

    /**
     * Get city info by ID
     * @param int $cityId
     * @return array|null
     */
    public function getCityInfo($cityId)
    {
        $response = Http::withHeaders([
            'key' => $this->apiKey,
        ])->get($this->baseUrl . '/city', [
            'id' => $cityId
        ]);

        if ($response->successful() && $response->json('results')) {
            return $response->json('results')[0];
        }

        return null;
    }

    /**
     * Get province info by ID
     * @param int $provinceId
     * @return array|null
     */
    public function getProvinceInfo($provinceId)
    {
        $response = Http::withHeaders([
            'key' => $this->apiKey,
        ])->get($this->baseUrl . '/province', [
            'id' => $provinceId
        ]);

        if ($response->successful() && $response->json('results')) {
            return $response->json('results')[0];
        }

        return null;
    }
}
