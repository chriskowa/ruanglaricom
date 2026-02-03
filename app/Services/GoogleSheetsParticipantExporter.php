<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;

class GoogleSheetsParticipantExporter
{
    public const OUTPUT_COLUMNS = [
        'No',
        'Nama',
        'Gender',
        'Email',
        'Phone',
        'ID Card',
        'Alamat',
        'Kategori',
        'BIB Number',
        'Jersey Size',
        'Target Time',
        'Status Pembayaran',
        'Status Pengambilan',
        'Tanggal Registrasi',
        'Tanggal Pengambilan',
        'Diambil Oleh (PIC)',
    ];

}

