<?php

namespace App\Controllers;

use App\Models\PackageModel;
use App\Models\CityModel;
use App\Models\HotelModel;

class Package extends BaseController
{
    protected $packageModel;
    protected $cityModel;
    protected $hotelModel;

    public function __construct()
    {
        $this->packageModel = new PackageModel();
        $this->cityModel = new CityModel();
        $this->hotelModel = new HotelModel();
    }

    /**
     * Label "Hotel [Kota]" untuk slot 1 & 2: diambil dari master hotel (kolom city di travel_hotels).
     * Urutan mengikuti master kota; jika belum ada hotel, fallback ke nama dari master kota.
     */
    private function getPackageCityLabels()
    {
        $kotaOrdered = $this->cityModel->getOrdered();
        $cityNamesFromHotels = $this->getDistinctCityNamesFromHotels();
        $c1 = $kotaOrdered[0]['name'] ?? 'Kota 1';
        $c2 = $kotaOrdered[1]['name'] ?? 'Kota 2';
        if (!empty($cityNamesFromHotels[0])) {
            $c1 = $cityNamesFromHotels[0];
        }
        if (!empty($cityNamesFromHotels[1])) {
            $c2 = $cityNamesFromHotels[1];
        }
        return ['city1_name' => $c1, 'city2_name' => $c2];
    }

    /** Kota distinct dari master hotel (travel_hotels.city), urut sesuai master kota. */
    private function getDistinctCityNamesFromHotels()
    {
        $kotaOrdered = $this->cityModel->getOrdered();
        $hotels = $this->hotelModel->select('city')->distinct()->findAll();
        $citySet = array_column($hotels, 'city');
        $out = [];
        foreach ($kotaOrdered as $k) {
            $name = $k['name'] ?? '';
            if ($name !== '' && in_array($name, $citySet, true)) {
                $out[] = $name;
                if (count($out) >= 2) {
                    break;
                }
            }
        }
        return $out;
    }

    /** Semua hotel dari master (untuk dropdown paket - pilih dari semua tanpa filter kota) */
    private function getHotelsForPackageForm()
    {
        $hotelsAll = $this->hotelModel->orderBy('city', 'ASC')->orderBy('name', 'ASC')->findAll();
        return [
            'hotels_all' => $hotelsAll,
        ];
    }

    private function resolveHotelFieldsFromPost()
    {
        $hotelMekkahId = $this->request->getPost('hotel_mekkah_id');
        $hotelMadinahId = $this->request->getPost('hotel_madinah_id');
        $out = [
            'hotel_mekkah' => null,
            'hotel_mekkah_stars' => null,
            'hotel_madinah' => null,
            'hotel_madinah_stars' => null,
            'hotel_mekkah_id' => null,
            'hotel_madinah_id' => null,
        ];
        if ($hotelMekkahId && is_numeric($hotelMekkahId)) {
            $h = $this->hotelModel->find((int) $hotelMekkahId);
            if ($h) {
                $out['hotel_mekkah'] = $h['name'];
                $out['hotel_mekkah_stars'] = (int) ($h['star_rating'] ?? 4);
                $out['hotel_mekkah_id'] = (int) $hotelMekkahId;
            }
        }
        if ($hotelMadinahId && is_numeric($hotelMadinahId)) {
            $h = $this->hotelModel->find((int) $hotelMadinahId);
            if ($h) {
                $out['hotel_madinah'] = $h['name'];
                $out['hotel_madinah_stars'] = (int) ($h['star_rating'] ?? 4);
                $out['hotel_madinah_id'] = (int) $hotelMadinahId;
            }
        }
        return $out;
    }

    /** Isi package dengan data tampilan hotel dari master (untuk relasi display). */
    private function enrichPackageWithHotelMaster(array &$package)
    {
        $package['display_hotel_1'] = null;
        $package['display_hotel_2'] = null;
        if (!empty($package['hotel_mekkah_id'])) {
            $h = $this->hotelModel->find((int) $package['hotel_mekkah_id']);
            if ($h) {
                $package['display_hotel_1'] = [
                    'name' => $h['name'],
                    'city' => $h['city'] ?? '',
                    'stars' => (int) ($h['star_rating'] ?? 0),
                ];
            }
        }
        if ($package['display_hotel_1'] === null && !empty($package['hotel_mekkah'])) {
            $package['display_hotel_1'] = [
                'name' => $package['hotel_mekkah'],
                'city' => '',
                'stars' => (int) ($package['hotel_mekkah_stars'] ?? 0),
            ];
        }
        if (!empty($package['hotel_madinah_id'])) {
            $h = $this->hotelModel->find((int) $package['hotel_madinah_id']);
            if ($h) {
                $package['display_hotel_2'] = [
                    'name' => $h['name'],
                    'city' => $h['city'] ?? '',
                    'stars' => (int) ($h['star_rating'] ?? 0),
                ];
            }
        }
        if ($package['display_hotel_2'] === null && !empty($package['hotel_madinah'])) {
            $package['display_hotel_2'] = [
                'name' => $package['hotel_madinah'],
                'city' => '',
                'stars' => (int) ($package['hotel_madinah_stars'] ?? 0),
            ];
        }
    }

    public function index()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $packages = $this->packageModel->findAll();
        foreach ($packages as &$p) {
            $this->enrichPackageWithHotelMaster($p);
        }
        $data = [
            'packages' => $packages,
            'city1_name' => $this->getPackageCityLabels()['city1_name'],
            'city2_name' => $this->getPackageCityLabels()['city2_name'],
        ];
        return view('owner/package/index', $data);
    }

    public function create()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $data = array_merge($this->getPackageCityLabels(), $this->getHotelsForPackageForm());
        return view('owner/package/create', $data);
    }

    public function store()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $inclusions = $this->request->getPost('inclusions');
        $freebies = $this->request->getPost('freebies');
        $exclusions = $this->request->getPost('exclusions');

        $parseList = function ($text) {
            if (!$text)
                return [];
            $lines = preg_split("/\r\n|\n|\r/", $text);
            return array_values(array_filter(array_map('trim', $lines)));
        };

        $hotelFields = $this->resolveHotelFieldsFromPost();
        $data = [
            'name' => $this->request->getPost('name'),
            'departure_date' => $this->request->getPost('departure_date'),
            'duration' => $this->request->getPost('duration'),
            'location_start_end' => $this->request->getPost('location_start_end'),
            'hotel_mekkah' => $hotelFields['hotel_mekkah'],
            'hotel_mekkah_stars' => $hotelFields['hotel_mekkah_stars'],
            'hotel_mekkah_id' => $hotelFields['hotel_mekkah_id'],
            'hotel_madinah' => $hotelFields['hotel_madinah'],
            'hotel_madinah_stars' => $hotelFields['hotel_madinah_stars'],
            'hotel_madinah_id' => $hotelFields['hotel_madinah_id'],
            'airline' => $this->request->getPost('airline'),
            'flight_route' => $this->request->getPost('flight_route'),
            'price' => $this->parseRupiahToNumber($this->request->getPost('price')),
            'price_unit' => 'JT',
            'inclusions' => json_encode($parseList($inclusions)),
            'freebies' => json_encode($parseList($freebies)),
            'exclusions' => json_encode($parseList($exclusions)),
            'commission_per_pax' => can_view_commission()
                ? $this->parseRupiahToNumber($this->request->getPost('commission_per_pax'))
                : 0,
            'branch_info' => $this->request->getPost('branch_info')
        ];

        // Handle Image Upload
        $img = $this->request->getFile('image');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move('uploads/packages', $newName);
            $data['image'] = 'uploads/packages/' . $newName;
        }

        if ($this->packageModel->insert($data)) {
            return redirect()->to('/package')->with('msg', 'Paket berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan paket');
    }

    public function edit($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $package = $this->packageModel->find($id);
        if (!$package) {
            return redirect()->to('/package')->with('error', 'Paket tidak ditemukan');
        }

        // Convert JSON back to newline-separated string for textarea
        $package['inclusions_text'] = implode("\n", json_decode($package['inclusions'] ?? '[]', true) ?? []);
        $package['freebies_text'] = implode("\n", json_decode($package['freebies'] ?? '[]', true) ?? []);
        $package['exclusions_text'] = implode("\n", json_decode($package['exclusions'] ?? '[]', true) ?? []);
        $this->enrichPackageWithHotelMaster($package);

        $data = array_merge(
            ['package' => $package],
            $this->getPackageCityLabels(),
            $this->getHotelsForPackageForm()
        );
        return view('owner/package/edit', $data);
    }

    public function update($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $package = $this->packageModel->find($id);
        if (!$package) {
            return redirect()->to('/package')->with('error', 'Paket tidak ditemukan');
        }

        $inclusions = $this->request->getPost('inclusions');
        $freebies = $this->request->getPost('freebies');
        $exclusions = $this->request->getPost('exclusions');

        $parseList = function ($text) {
            if (!$text)
                return [];
            $lines = preg_split("/\r\n|\n|\r/", $text);
            return array_values(array_filter(array_map('trim', $lines)));
        };

        $hotelFields = $this->resolveHotelFieldsFromPost();
        $data = [
            'name' => $this->request->getPost('name'),
            'departure_date' => $this->request->getPost('departure_date'),
            'duration' => $this->request->getPost('duration'),
            'location_start_end' => $this->request->getPost('location_start_end'),
            'hotel_mekkah' => $hotelFields['hotel_mekkah'],
            'hotel_mekkah_stars' => $hotelFields['hotel_mekkah_stars'],
            'hotel_mekkah_id' => $hotelFields['hotel_mekkah_id'],
            'hotel_madinah' => $hotelFields['hotel_madinah'],
            'hotel_madinah_stars' => $hotelFields['hotel_madinah_stars'],
            'hotel_madinah_id' => $hotelFields['hotel_madinah_id'],
            'airline' => $this->request->getPost('airline'),
            'flight_route' => $this->request->getPost('flight_route'),
            'price' => $this->parseRupiahToNumber($this->request->getPost('price')),
            'price_unit' => 'JT',
            'inclusions' => json_encode($parseList($inclusions)),
            'freebies' => json_encode($parseList($freebies)),
            'exclusions' => json_encode($parseList($exclusions)),
            'commission_per_pax' => can_view_commission()
                ? $this->parseRupiahToNumber($this->request->getPost('commission_per_pax'))
                : (float) ($package['commission_per_pax'] ?? 0),
            'branch_info' => $this->request->getPost('branch_info')
        ];

        // Handle Image Upload
        $img = $this->request->getFile('image');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            // Delete old image if exists
            if ($package['image'] && file_exists($package['image'])) {
                unlink($package['image']);
            }

            $newName = $img->getRandomName();
            $img->move('uploads/packages', $newName);
            $data['image'] = 'uploads/packages/' . $newName;
        }

        if ($this->packageModel->update($id, $data)) {
            return redirect()->to('/package')->with('msg', 'Paket berhasil diperbarui');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui paket');
    }

    /**
     * Parse nilai Rupiah (format 500.000) ke angka tanpa desimal.
     */
    private function parseRupiahToNumber($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        $cleaned = preg_replace('/[^\d]/', '', $value);
        return $cleaned !== '' ? (float) $cleaned : 0;
    }

    public function delete($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $id = (int) $id;
        $package = $this->packageModel->find($id);
        if (!$package) {
            return redirect()->to('/package')->with('error', 'Paket tidak ditemukan.');
        }

        try {
            if (!empty($package['image']) && is_file(FCPATH . $package['image'])) {
                @unlink(FCPATH . $package['image']);
            }
            $this->packageModel->delete($id);
            return redirect()->to('/package')->with('msg', 'Paket berhasil dihapus');
        } catch (\Throwable $e) {
            log_message('error', 'Package delete failed: ' . $e->getMessage());
            return redirect()->to('/package')->with('error', 'Gagal menghapus paket. Pastikan tidak ada data jamaah/pembayaran yang masih terhubung, atau hubungi administrator.');
        }
    }

    /**
     * Toggle status paket (aktif/nonaktif) — untuk penuh atau sudah berakhir.
     */
    public function toggleStatus($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $id = (int) $id;
        $package = $this->packageModel->find($id);
        if (!$package) {
            return redirect()->to('/package')->with('error', 'Paket tidak ditemukan.');
        }

        $newStatus = empty($package['is_active']) ? 1 : 0;
        $this->packageModel->update($id, ['is_active' => $newStatus]);
        $label = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->to('/package')->with('msg', 'Paket "' . esc($package['name']) . '" berhasil ' . $label . '.');
    }
}
