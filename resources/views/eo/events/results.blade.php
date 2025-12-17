@extends('layouts.app')

@section('title', 'Race Results - ' . $event->name)

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('eo.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('eo.events.index') }}">Master Events</a></li>
        <li class="breadcrumb-item active"><a href="javascript:void(0)">Race Results</a></li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Race Results - {{ $event->name }}</h4>
                <div>
                    <a href="{{ route('eo.events.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Upload CSV Section -->
                <div class="mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-white"><i class="fa fa-upload me-2"></i>Upload CSV Race Results</h5>
                    </div>
                    <div class="card-body">
                        <form id="csvUploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="form-label">Pilih File CSV</label>
                                    <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv,.txt" required>
                                    <small class="form-text text-muted">
                                        Format CSV: BIB, Name, Gender (M/F), Category, Gun Time (H:i:s), Chip Time (H:i:s), Pace (MM:SS), Nationality (optional)
                                    </small>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="downloadSampleCSV()">
                                            <i class="fa fa-download me-1"></i>Download Sample CSV
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#csvExample">
                                            <i class="fa fa-info-circle me-1"></i>Lihat Contoh
                                        </button>
                                    </div>
                                    <div class="collapse mt-2" id="csvExample">
                                        <div class="card card-body bg-light">
                                            <h6 class="mb-2">Contoh Format CSV:</h6>
                                            <pre class="mb-0" style="font-size: 11px;">BIB,Name,Gender,Category,Gun Time,Chip Time,Pace,Nationality
1024,Agus Prayogo,M,FM,02:35:12,02:35:10,03:41,IDN
1055,Rikki Marthin,M,FM,02:38:45,02:38:42,03:45,IDN
2011,Odekta Elvina,F,FM,02:48:20,02:48:15,03:59,IDN
5022,Budi Santoso,M,HM,01:25:10,01:24:55,04:02,IDN
5088,Siti Rahma,F,HM,01:45:30,01:45:10,04:59,IDN
9001,John Doe,M,10K,00:45:12,00:44:50,04:29,USA
9002,Jane Smith,F,10K,00:52:10,00:51:55,05:11,GBR</pre>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fa fa-upload me-1"></i>Upload CSV
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id="uploadResult" class="mt-3"></div>
                    </div>
                </div>

                <!-- Manual Input Section -->
                <div class="mb-4 border-success">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fa fa-plus me-2"></i>Tambah Race Result Manual</h5>
                        <button type="button" class="btn btn-light btn-sm" onclick="toggleManualForm()">
                            <i class="fa fa-chevron-down" id="toggleIcon"></i>
                        </button>
                    </div>
                    <div class="card-body" id="manualForm" style="display: none;">
                        <form id="manualFormData">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">BIB Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bib_number" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Runner Name <span class="text-danger">*</span></label>
                                    <input type="text" name="runner_name" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" class="form-control" value="IDN" maxlength="10">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Category Code <span class="text-danger">*</span></label>
                                    <select name="category_code" class="form-select" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->code ?? $cat->name }}">{{ $cat->name }} ({{ $cat->code ?? 'N/A' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Gun Time (H:i:s)</label>
                                    <input type="text" name="gun_time" class="form-control" placeholder="02:35:12">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Chip Time (H:i:s) <span class="text-danger">*</span></label>
                                    <input type="text" name="chip_time" class="form-control" placeholder="02:35:10" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Pace (MM:SS)</label>
                                    <input type="text" name="pace" class="form-control" placeholder="03:41">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa fa-save me-1"></i>Simpan Result
                                    </button>
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="table-responsive">
                    <table class="table table-responsive-md">
                        <thead>
                            <tr>
                                <th style="width:50px;">Rank</th>
                                <th>BIB</th>
                                <th>Runner Name</th>
                                <th>Gender</th>
                                <th>Category</th>
                                <th>Gun Time</th>
                                <th>Chip Time</th>
                                <th>Pace</th>
                                <th>Podium</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $result)
                            <tr>
                                <td>
                                    @if($result->podium_position)
                                        <span class="badge light 
                                            @if($result->podium_position == 1) badge-warning
                                            @elseif($result->podium_position == 2) badge-secondary
                                            @else badge-danger
                                            @endif">
                                            {{ $result->podium_position }}
                                        </span>
                                    @else
                                        <strong>{{ $result->rank_category ?? $result->rank_overall ?? '-' }}</strong>
                                    @endif
                                </td>
                                <td><span class="badge light badge-primary">{{ $result->bib_number }}</span></td>
                                <td>{{ $result->runner_name }}</td>
                                <td>{{ $result->gender }}</td>
                                <td><span class="badge light badge-info">{{ $result->category_code }}</span></td>
                                <td class="font-mono">{{ $result->getFormattedGunTime() }}</td>
                                <td class="font-mono font-bold">{{ $result->getFormattedChipTime() }}</td>
                                <td class="font-mono">{{ $result->pace ?? '-' }}</td>
                                <td>
                                    @if($result->is_podium)
                                        <span class="badge light badge-success">
                                            <i class="fa fa-trophy me-1"></i>Juara {{ $result->podium_position }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-primary light sharp" data-bs-toggle="dropdown">
                                            <svg width="20px" height="20px" viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <rect x="0" y="0" width="24" height="24"/>
                                                    <circle fill="#000000" cx="5" cy="12" r="2"/>
                                                    <circle fill="#000000" cx="12" cy="12" r="2"/>
                                                    <circle fill="#000000" cx="19" cy="12" r="2"/>
                                                </g>
                                            </svg>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="editResult({{ $result->id }})">
                                                <i class="fa fa-edit me-2"></i>Edit
                                            </a>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteResult({{ $result->id }})">
                                                <i class="fa fa-trash me-2"></i>Hapus
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <p class="text-muted mb-0">Belum ada race results. Upload CSV atau tambah manual.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($results->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $results->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Race Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    @csrf
                    <input type="hidden" name="result_id" id="edit_result_id">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">BIB Number <span class="text-danger">*</span></label>
                            <input type="text" name="bib_number" id="edit_bib_number" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Runner Name <span class="text-danger">*</span></label>
                            <input type="text" name="runner_name" id="edit_runner_name" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="edit_gender" class="form-select" required>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nationality</label>
                            <input type="text" name="nationality" id="edit_nationality" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category Code <span class="text-danger">*</span></label>
                            <select name="category_code" id="edit_category_code" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->code ?? $cat->name }}">{{ $cat->name }} ({{ $cat->code ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gun Time (H:i:s)</label>
                            <input type="text" name="gun_time" id="edit_gun_time" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Chip Time (H:i:s) <span class="text-danger">*</span></label>
                            <input type="text" name="chip_time" id="edit_chip_time" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pace (MM:SS)</label>
                            <input type="text" name="pace" id="edit_pace" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveEdit()">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Download Sample CSV
function downloadSampleCSV() {
    const csvContent = `BIB,Name,Gender,Category,Gun Time,Chip Time,Pace,Nationality
1024,Agus Prayogo,M,FM,02:35:12,02:35:10,03:41,IDN
1055,Rikki Marthin,M,FM,02:38:45,02:38:42,03:45,IDN
2011,Odekta Elvina,F,FM,02:48:20,02:48:15,03:59,IDN
5022,Budi Santoso,M,HM,01:25:10,01:24:55,04:02,IDN
5088,Siti Rahma,F,HM,01:45:30,01:45:10,04:59,IDN
9001,John Doe,M,10K,00:45:12,00:44:50,04:29,USA
9002,Jane Smith,F,10K,00:52:10,00:51:55,05:11,GBR
1102,Michael Run,M,FM,02:55:00,02:54:30,04:08,KEN`;

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'race_results_sample.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

const eventId = {{ $event->id }};
const baseUrl = '{{ route("eo.events.results.store", $event) }}';
const updateUrl = '{{ route("eo.events.results.update", ["event" => $event->id, "raceResult" => ":id"]) }}';
const deleteUrl = '{{ route("eo.events.results.destroy", ["event" => $event->id, "raceResult" => ":id"]) }}';
const csvUploadUrl = '{{ route("eo.events.results.upload-csv", $event) }}';

// Toggle manual form
function toggleManualForm() {
    const form = document.getElementById('manualForm');
    const icon = document.getElementById('toggleIcon');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        form.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

// CSV Upload
document.getElementById('csvUploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const resultDiv = document.getElementById('uploadResult');
    resultDiv.innerHTML = '<div class="alert alert-info">Mengupload dan memproses CSV...</div>';
    
    try {
        const response = await fetch(csvUploadUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>Berhasil!</strong> ${data.message}<br>
                    <small>Berhasil: ${data.success_count}, Error: ${data.error_count}</small>
                    ${data.errors.length > 0 ? '<br><small class="text-danger">' + data.errors.join('<br>') + '</small>' : ''}
                </div>
            `;
            setTimeout(() => location.reload(), 2000);
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    } catch (error) {
        resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
    }
});

// Manual Form Submit
document.getElementById('manualFormData').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch(baseUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Terjadi kesalahan'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});

// Edit Result
async function editResult(id) {
    try {
        const response = await fetch(`{{ route('eo.events.results.show', ['event' => $event->id, 'raceResult' => ':id']) }}`.replace(':id', id));
        const result = await response.json();
        
        document.getElementById('edit_result_id').value = result.data.id;
        document.getElementById('edit_bib_number').value = result.data.bib_number;
        document.getElementById('edit_runner_name').value = result.data.runner_name;
        document.getElementById('edit_gender').value = result.data.gender;
        document.getElementById('edit_nationality').value = result.data.nationality || 'IDN';
        document.getElementById('edit_category_code').value = result.data.category_code;
        document.getElementById('edit_gun_time').value = result.data.gun_time || '';
        document.getElementById('edit_chip_time').value = result.data.chip_time || '';
        document.getElementById('edit_pace').value = result.data.pace || '';
        document.getElementById('edit_notes').value = result.data.notes || '';
        
        new bootstrap.Modal(document.getElementById('editModal')).show();
    } catch (error) {
        alert('Error loading data: ' + error.message);
    }
}

// Save Edit
async function saveEdit() {
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    const resultId = document.getElementById('edit_result_id').value;
    const url = updateUrl.replace(':id', resultId);
    
    try {
        const response = await fetch(url, {
            method: 'PUT',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Terjadi kesalahan'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Delete Result
async function deleteResult(id) {
    if (!confirm('Yakin ingin menghapus race result ini?')) return;
    
    const url = deleteUrl.replace(':id', id);
    
    try {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Terjadi kesalahan'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>
@endpush
@endsection


