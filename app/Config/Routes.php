<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('package/(:num)', 'Home::packageDetail/$1');
$routes->get('agen-mitra', 'Home::agenMitra');
$routes->get('testimoni-jamaah', 'Home::testimoni');
$routes->post('testimoni-jamaah/submit', 'Home::submitTestimoni');
$routes->get('berita', 'Home::berita');
$routes->get('berita/(:segment)', 'Home::beritaDetail/$1');
$routes->get('login', 'Auth::login');
$routes->post('auth/login', 'Auth::attemptLogin');
$routes->post('auth/refresh-token', 'Auth::refreshToken');
$routes->get('logout', 'Auth::logout');

$routes->group('owner', ['filter' => 'auth:owner,office_admin'], function ($routes) {
    $routes->get('/', 'Owner::index');

    // Materials Management (Promotion Materials)
    $routes->group('materials', function ($routes) {
            $routes->get('/', 'Owner::materials');
            $routes->get('create', 'Owner::uploadMaterial');
            $routes->post('store', 'Owner::storeMaterial');
            $routes->get('edit/(:num)', 'Owner::editMaterial/$1');
            $routes->post('update/(:num)', 'Owner::updateMaterial/$1');
            $routes->get('delete/(:num)', 'Owner::deleteMaterial/$1');
        }
        );

        // Master Kota
        $routes->group('cities', function ($routes) {
            $routes->get('/', 'City::index');
            $routes->get('create', 'City::create');
            $routes->post('store', 'City::store');
            $routes->get('edit/(:num)', 'City::edit/$1');
            $routes->post('update/(:num)', 'City::update/$1');
            $routes->get('delete/(:num)', 'City::delete/$1');
        });

        // Hotel Management
        $routes->group('hotels', function ($routes) {
            $routes->get('/', 'Hotel::index');
            $routes->get('create', 'Hotel::create');
            $routes->post('store', 'Hotel::store');
            $routes->get('edit/(:num)', 'Hotel::edit/$1');
            $routes->post('update/(:num)', 'Hotel::update/$1');

            // Rooms
            $routes->get('(:num)/rooms', 'Hotel::rooms/$1');
            $routes->post('rooms/store', 'Hotel::storeRoom');
            $routes->post('rooms/store-multiple', 'Hotel::storeRooms');
            $routes->get('rooms/edit/(:num)', 'Hotel::editRoom/$1');
            $routes->post('rooms/update/(:num)', 'Hotel::updateRoom/$1');
            $routes->get('rooms/delete/(:num)', 'Hotel::deleteRoom/$1');
            // Master bed/kasur per kamar
            $routes->post('rooms/beds/store', 'Hotel::storeBed');
            $routes->post('rooms/beds/update/(:num)', 'Hotel::updateBed/$1');
            $routes->post('rooms/beds/delete/(:num)', 'Hotel::deleteBed/$1');
        }
        );

        // Participant Management
        $routes->group('participant', function ($routes) {
            $routes->get('/', 'Participant::index');
            $routes->get('kelola/(:num)', 'Participant::kelola/$1');
            $routes->get('edit/(:num)', 'Participant::editParticipant/$1');
            $routes->post('update/(:num)', 'Participant::updateParticipant/$1');
            $routes->post('update-schedule', 'Participant::updateSchedule');
            $routes->get('documents', 'Participant::documents');
            $routes->get('documents/(:num)', 'Participant::documents/$1');
            $routes->post('update-checklist', 'Participant::updateChecklist');
            $routes->post('upload-document', 'Participant::uploadDocument');
            $routes->post('delete-document', 'Participant::deleteDocument');
            $routes->post('update-document', 'Participant::updateDocument');
            $routes->get('receipt/(:num)', 'Participant::receipt/$1');
            $routes->get('registration-form/(:num)', 'Participant::registrationFormPrint/$1');
            $routes->get('transaction-receipt/(:num)', 'Participant::transactionReceipt/$1');
            $routes->get('payment-history/(:num)', 'Participant::paymentHistory/$1');
            $routes->get('get-upgrade-options/(:num)', 'Participant::getUpgradeOptions/$1');
            $routes->post('save-upgrade', 'Participant::saveUpgrade');

            // Boarding
            $routes->get('boarding', 'Participant::boarding');
            $routes->get('boarding-list', 'Participant::boardingList');
            $routes->get('boarding-list-print', 'Participant::boardingListPrint');
            $routes->get('boarding-list-export', 'Participant::boardingListExport');
            $routes->get('boarding-manifest/(:num)', 'Participant::boardingManifest/$1');

            // Pembatalan
            $routes->get('cancellations', 'Participant::cancellations');
            $routes->get('cancel-form/(:num)', 'Participant::cancelForm/$1');
            $routes->post('store-cancellation', 'Participant::storeCancellation');
            $routes->post('reactivate/(:num)', 'Participant::reactivate/$1');
            $routes->get('cancellation-statement/(:num)', 'Participant::cancellationStatement/$1');

            // Registrasi dari Kantor (route dengan (:num) harus di atas agar tidak tertimpa)
            $routes->get('register/(:num)', 'Participant::registerFromOfficeForm/$1');
            $routes->get('register', 'Participant::registerFromOffice');
            $routes->post('store-registration-office', 'Participant::storeRegistrationFromOffice');

            // Tambah Pembayaran dari Kantor
            $routes->get('add-payment/(:num)', 'Participant::addPayment/$1');
            $routes->post('store-payment-office', 'Participant::storePaymentFromOffice');
        }
        );

        // Tabungan Perjalanan
        $routes->get('tabungan', 'Tabungan::index');
        $routes->get('tabungan/create', 'Tabungan::create');
        $routes->post('tabungan/store', 'Tabungan::store');
        $routes->get('tabungan/deposit/(:num)', 'Tabungan::addDeposit/$1');
        $routes->post('tabungan/store-deposit', 'Tabungan::storeDeposit');
        $routes->get('tabungan/claim/(:num)', 'Tabungan::claimForm/$1');
        $routes->post('tabungan/do-claim', 'Tabungan::doClaim');
        $routes->get('tabungan/edit/(:num)', 'Tabungan::edit/$1');
        $routes->post('tabungan/update/(:num)', 'Tabungan::update/$1');
        $routes->post('tabungan/delete/(:num)', 'Tabungan::delete/$1');
        $routes->get('tabungan/receipt/(:num)', 'Tabungan::receipt/$1');
        $routes->get('tabungan/edit-deposit/(:num)', 'Tabungan::editDeposit/$1');
        $routes->post('tabungan/update-deposit/(:num)', 'Tabungan::updateDeposit/$1');
        $routes->post('tabungan/delete-deposit/(:num)', 'Tabungan::deleteDeposit/$1');

        // Account Settings
        $routes->get('settings', 'Owner::settings');
        $routes->post('update-settings', 'Owner::updateSettings');

        // Participant Verification & Checklist
        $routes->get('checklist/(:num)', 'Owner::checklist/$1');

        // Banner (slider halaman login)
        $routes->get('banners', 'Owner::banners');
        $routes->post('banners/store', 'Owner::storeBanner');
        $routes->get('banners/delete/(:num)', 'Owner::deleteBanner/$1');

        // Print Documents
        $routes->get('print-documents', 'PrintDocuments::index');
        $routes->get('print-documents/leave-letter', 'PrintDocuments::printLeaveLetter');
        $routes->post('print-documents/leave-letter', 'PrintDocuments::printLeaveLetter');
        $routes->get('print-documents/recommendation-letter', 'PrintDocuments::printRecommendationLetter');
        $routes->post('print-documents/recommendation-letter', 'PrintDocuments::printRecommendationLetter');
        $routes->get('print-documents/deposit-receipt', 'PrintDocuments::printDepositReceipt');

        // Rubrik Berita (artikel untuk halaman depan)
        $routes->get('rubrik-berita', 'RubrikBerita::index');
        $routes->get('rubrik-berita/create', 'RubrikBerita::create');
        $routes->post('rubrik-berita/store', 'RubrikBerita::store');
        $routes->get('rubrik-berita/edit/(:num)', 'RubrikBerita::edit/$1');
        $routes->post('rubrik-berita/update/(:num)', 'RubrikBerita::update/$1');
        $routes->get('rubrik-berita/delete/(:num)', 'RubrikBerita::delete/$1');
        $routes->post('rubrik-berita/toggle-status/(:num)', 'RubrikBerita::toggleStatus/$1');
    });

// Agensi, pembuatan admin kantor, laporan bisnis (berisi data agensi): hanya pemilik
$routes->group('owner', ['filter' => 'auth:owner'], function ($routes) {
    // Notifikasi verifikasi & semua aksi verifikasi: hanya pemilik
    $routes->get('notifications', 'Owner::getNotifications');
    $routes->get('payment-verification', 'Owner::paymentVerification');
    $routes->post('verify-payment', 'Owner::verifyPayment');
    $routes->get('payment-verification/edit-payment/(:num)', 'Owner::editPayment/$1');
    $routes->post('payment-verification/update-payment/(:num)', 'Owner::updatePayment/$1');
    $routes->post('payment-verification/delete-payment/(:num)', 'Owner::deletePayment/$1');
    $routes->post('verify-participant', 'Owner::verifyParticipant');
    $routes->post('participant/verify-document', 'Participant::verifyDocument');
    $routes->post('participant/process-boarding', 'Participant::processBoarding');
    $routes->post('participant/confirm-boarding', 'Participant::confirmBoarding');
    $routes->post('tabungan/verify-deposit/(:num)', 'Tabungan::verifyDeposit/$1');
    $routes->get('testimoni', 'Owner::testimoni');
    $routes->get('testimoni/edit/(:num)', 'Owner::editTestimoni/$1');
    $routes->post('testimoni/update/(:num)', 'Owner::updateTestimoni/$1');
    $routes->post('testimoni/delete/(:num)', 'Owner::deleteTestimoni/$1');
    $routes->post('testimoni/verify/(:num)', 'Owner::verifyTestimoni/$1');

    $routes->group('agency', function ($routes) {
        $routes->get('/', 'AgencyAdmin::index');
        $routes->get('create', 'AgencyAdmin::create');
        $routes->post('store', 'AgencyAdmin::store');
        $routes->get('edit/(:num)', 'AgencyAdmin::edit/$1');
        $routes->post('update/(:num)', 'AgencyAdmin::update/$1');
        $routes->post('toggle-status/(:num)', 'AgencyAdmin::toggleStatus/$1');
    });
    $routes->get('office-admin/create', 'AgencyAdmin::createOfficeAdmin');
    $routes->post('office-admin/store', 'AgencyAdmin::storeOfficeAdmin');
    $routes->get('reports', 'Reports::index');
    $routes->get('reports/equipment', 'Reports::equipment');
    $routes->get('reports/registrations-export', 'Reports::registrationsExport');
});

$routes->group('package', ['filter' => 'auth:owner,office_admin'], function ($routes) {
    $routes->get('/', 'Package::index');
    $routes->get('create', 'Package::create');
    $routes->post('store', 'Package::store');
    $routes->get('edit/(:num)', 'Package::edit/$1');
    $routes->post('update/(:num)', 'Package::update/$1');
    $routes->post('delete/(:num)', 'Package::delete/$1');
    $routes->post('toggle-status/(:num)', 'Package::toggleStatus/$1');
});

$routes->group('agency', ['filter' => 'auth:agency'], function ($routes) {
    $routes->get('/', 'Agency::index');

    // Materi Promosi
    $routes->get('materials', 'Agency::materials');
    // Package Browsing & Registration
    $routes->get('packages', 'Agency::packages');
    $routes->get('package-detail/(:num)', 'Agency::packageDetail/$1');
    $routes->get('register/(:num)', 'Agency::register/$1');
    $routes->post('store-registration', 'Agency::storeRegistration');

    // Participant Management (Edit & List)
    $routes->get('participants', 'Agency::participants');
    $routes->get('boarding', 'Agency::boardingList');
    $routes->get('cancellations', 'Agency::cancellations');
    $routes->get('tabungan', 'Agency::tabunganIndex');
    $routes->get('tabungan/create', 'Agency::tabunganCreate');
    $routes->post('tabungan/store', 'Agency::tabunganStore');
    $routes->get('tabungan/deposit/(:num)', 'Agency::tabunganDeposit/$1');
    $routes->post('tabungan/store-deposit', 'Agency::tabunganStoreDeposit');
    
    // Print Documents untuk Agency
    $routes->get('print-documents/deposit-receipt', 'PrintDocuments::printDepositReceipt');
    $routes->get('edit-participant/(:num)', 'Agency::editParticipant/$1');
    $routes->post('update-participant/(:num)', 'Agency::updateParticipant/$1');
    $routes->get('registration-form/(:num)', 'Agency::registrationFormPrint/$1');
    $routes->get('documents/(:num)', 'Agency::documents/$1');
    $routes->post('update-documents', 'Agency::updateDocuments');
    $routes->post('upload-document', 'Agency::uploadDocument');
    $routes->post('delete-document', 'Agency::deleteDocument');
    $routes->post('update-document', 'Agency::updateDocument');

    // Payment Installments
    $routes->get('payments', 'Agency::payments');
    $routes->get('payment-detail/(:num)', 'Agency::paymentDetail/$1');
    $routes->get('receipt/(:num)', 'Agency::receipt/$1');
    $routes->get('transaction-receipt/(:num)', 'Agency::transactionReceipt/$1');
    $routes->post('store-payment', 'Agency::storePayment');
    // Income Report
    $routes->get('income', 'Agency::income');
    // Profil agency
    $routes->get('profile', 'Agency::profile');
    $routes->post('update-profile', 'Agency::updateProfile');
    // Testimoni Jamaah (input form)
    $routes->get('testimoni', 'Agency::testimoni');
    $routes->post('testimoni/submit', 'Agency::submitTestimoni');
});
// Equipment/Attribute Management
$routes->group('owner/equipment', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Equipment::index');
    $routes->post('store', 'Equipment::store');
    $routes->post('update/(:num)', 'Equipment::update/$1');
    $routes->get('toggle/(:num)', 'Equipment::toggle/$1');
    $routes->get('delete/(:num)', 'Equipment::delete/$1');
    $routes->get('participants', 'Equipment::participants');
    $routes->get('checklist/(:num)', 'Equipment::checklist/$1');
    $routes->post('update-status', 'Equipment::updateStatus');
    $routes->get('sync-all', 'Equipment::syncAll');
});

// Komisi: hanya pemilik (bukan admin kantor)
$routes->group('owner/commissions', ['filter' => 'auth:owner'], function ($routes) {
    $routes->get('/', 'Commission::index');
    $routes->get('print/(:num)', 'Commission::printSlip/$1');
    $routes->post('update/(:num)', 'Commission::updateProgress/$1');
    $routes->post('verify-by-departure', 'Commission::verifyByDeparture');
});
