<?php

namespace App\Controllers;

use App\Models\CityModel;

class City extends BaseController
{
    protected $cityModel;

    public function __construct()
    {
        $this->cityModel = new CityModel();
    }

    public function index()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $data = [
            'cities' => $this->cityModel->getOrdered(),
            'title' => 'Master Kota',
        ];
        return view('owner/city/index', $data);
    }

    public function create()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        return view('owner/city/create');
    }

    public function store()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $maxOrder = $this->cityModel->selectMax('sort_order')->first();
        $sortOrder = isset($maxOrder['sort_order']) ? (int)$maxOrder['sort_order'] + 1 : 1;

        $data = [
            'name' => $this->request->getPost('name'),
            'sort_order' => $sortOrder,
        ];

        if ($this->cityModel->insert($data)) {
            return redirect()->to('owner/cities')->with('msg', 'Kota berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan kota.');
    }

    public function edit($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $city = $this->cityModel->find($id);
        if (!$city) {
            return redirect()->to('owner/cities')->with('error', 'Kota tidak ditemukan.');
        }

        return view('owner/city/edit', ['city' => $city]);
    }

    public function update($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'sort_order' => (int) $this->request->getPost('sort_order'),
        ];

        if ($this->cityModel->update($id, $data)) {
            return redirect()->to('owner/cities')->with('msg', 'Kota berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui kota.');
    }

    public function delete($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $this->cityModel->delete($id);
        return redirect()->back()->with('msg', 'Kota berhasil dihapus.');
    }
}
