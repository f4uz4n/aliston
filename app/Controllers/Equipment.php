<?php

namespace App\Controllers;

use App\Models\MasterEquipmentModel;
use App\Models\ParticipantModel;
use App\Models\EquipmentModel;

class Equipment extends BaseController
{
    protected $masterModel;
    protected $equipmentModel;
    protected $participantModel;

    public function __construct()
    {
        $this->masterModel = new MasterEquipmentModel();
        $this->equipmentModel = new EquipmentModel();
        $this->participantModel = new ParticipantModel();
    }

    // --- Master Attribute Management ---

    public function index()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $data = [
            'items' => $this->masterModel->orderBy('created_at', 'DESC')->findAll(),
            'title' => 'Master Atribut & Souvenir'
        ];
        return view('owner/equipment/index', $data);
    }

    public function store()
    {
        if (! is_back_office())
            return redirect()->to('/login');

        $this->masterModel->save([
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active' => 1
        ]);

        return redirect()->back()->with('msg', 'Atribut berhasil ditambahkan.');
    }

    public function update($id)
    {
        if (! is_back_office())
            return redirect()->to('/login');

        $this->masterModel->update($id, [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ]);

        return redirect()->back()->with('msg', 'Atribut berhasil diperbarui.');
    }

    public function toggle($id)
    {
        if (! is_back_office())
            return redirect()->to('/login');

        $item = $this->masterModel->find($id);
        if ($item) {
            $this->masterModel->update($id, ['is_active' => !$item['is_active']]);
        }

        return redirect()->back()->with('msg', 'Status atribut berhasil diubah.');
    }

    public function delete($id)
    {
        if (! is_back_office())
            return redirect()->to('/login');
        $this->masterModel->delete($id);
        return redirect()->back()->with('msg', 'Atribut berhasil dihapus.');
    }

    // --- Participant Checklist Management ---

    public function participants()
    {
        if (! is_back_office())
            return redirect()->to('/login');

        $filters = [
            'search' => $this->request->getGet('search'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date')
        ];

        $participants = $this->participantModel->getFilteredParticipants($filters);

        // Calculate progress for each participant
        foreach ($participants as &$part) {
            $equipment = $this->equipmentModel->getByParticipant($part['id']);
            $activeMasterNames = array_column($this->masterModel->getActive(), 'name');

            $totalActive = 0;
            $collected = 0;

            foreach ($equipment as $item) {
                if (in_array($item['item_name'], $activeMasterNames)) {
                    $totalActive++;
                    if ($item['status'] == 'collected') {
                        $collected++;
                    }
                }
            }

            $part['total_items'] = $totalActive;
            $part['collected_items'] = $collected;
            $part['progress_percent'] = ($totalActive > 0) ? round(($collected / $totalActive) * 100) : 0;
        }

        $data = [
            'participants' => $participants,
            'title' => 'Pengambilan Atribut Jamaah',
            'filters' => $filters
        ];
        return view('owner/participant/equipment_list', $data);
    }

    public function checklist($participantId)
    {
        if (! is_back_office())
            return redirect()->to('/login');

        $participant = $this->participantModel->getParticipantBuilder()
            ->where('participants.id', $participantId)
            ->get()->getRowArray();

        if (!$participant) {
            return redirect()->to('owner/equipment/participants')->with('error', 'Jamaah tidak ditemukan.');
        }
        $participant = format_participant_name_row($participant);

        // Get active master items
        $masterItems = $this->masterModel->getActive();
        $activeNames = array_column($masterItems, 'name');

        // Get currently assigned equipment
        $currentEquipment = $this->equipmentModel->getByParticipant($participantId);
        $currentItems = array_column($currentEquipment, 'item_name');

        // Sync: Add missing active master items
        foreach ($activeNames as $name) {
            if (!in_array($name, $currentItems)) {
                $this->equipmentModel->save([
                    'participant_id' => $participantId,
                    'item_name' => $name,
                    'status' => 'pending'
                ]);
            }
        }

        // Filter results: only show items that exist in current ACTIVE master list
        $allEquipment = $this->equipmentModel->getByParticipant($participantId);
        $filteredEquipment = [];
        foreach ($allEquipment as $eq) {
            if (in_array($eq['item_name'], $activeNames)) {
                $filteredEquipment[] = $eq;
            }
        }

        $data = [
            'participant' => $participant,
            'equipment' => $filteredEquipment,
            'title' => 'Cek List Atribut'
        ];
        return view('owner/participant/equipment_checklist', $data);
    }

    public function syncAll()
    {
        if (! is_back_office())
            return redirect()->to('/login');

        $participants = $this->participantModel->findAll();
        $masterItems = $this->masterModel->getActive();
        $activeNames = array_column($masterItems, 'name');

        foreach ($participants as $part) {
            $currentEquipment = $this->equipmentModel->getByParticipant($part['id']);
            $currentItems = array_column($currentEquipment, 'item_name');

            foreach ($activeNames as $name) {
                if (!in_array($name, $currentItems)) {
                    $this->equipmentModel->save([
                        'participant_id' => $part['id'],
                        'item_name' => $name,
                        'status' => 'pending'
                    ]);
                }
            }
        }

        return redirect()->back()->with('msg', 'Sinkronisasi atribut massal berhasil.');
    }

    public function updateStatus()
    {
        if (! is_back_office())
            return redirect()->to('/login');

        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');

        $this->equipmentModel->update($id, [
            'status' => $status,
            'collected_at' => ($status == 'collected') ? date('Y-m-d H:i:s') : null,
            'collected_by' => ($status == 'collected') ? session()->get('username') : null
        ]);

        return redirect()->back()->with('msg', 'Status pengambilan diperbarui.');
    }
}
