<!-- Generate Program Modal - Daniels Running Formula -->
<div class="modal fade" id="generateProgramModal" tabindex="-1" aria-labelledby="generateProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateProgramModalLabel">
                    <i class="fas fa-calculator me-2"></i>Generate Program Sendiri - Daniels' Running Formula
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="generate-program-form" action="{{ route('runner.programs.generate') }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Parameter Utama:</strong> VDOT Saat Ini, Riwayat Volume Lari, dan Tujuan Spesifik akan menentukan program latihan yang sesuai untuk Anda.
                    </div>

                    <!-- DATA PRIBADI -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>DATA PRIBADI</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Usia <span class="text-danger">*</span></label>
                                    <input type="number" name="age" class="form-control" required min="10" max="100" value="{{ auth()->user()->date_of_birth ? \Carbon\Carbon::parse(auth()->user()->date_of_birth)->age : '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-control default-select" required>
                                        <option value="">Pilih</option>
                                        <option value="male" {{ auth()->user()->gender == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="female" {{ auth()->user()->gender == 'female' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tinggi Badan (cm) <small class="text-muted">Opsional</small></label>
                                    <input type="number" name="height" class="form-control" min="100" max="250">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Berat Badan (kg) <small class="text-muted">Opsional</small></label>
                                    <input type="number" name="weight" class="form-control" min="30" max="200" step="0.1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIWAYAT PERFORMA (CRITICAL) -->
                    <div class="card mb-3">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>RIWAYAT PERFORMA (CRITICAL)</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Penting:</strong> Hasil lomba/time trial all-out (bukan lari santai). Data ini digunakan untuk menghitung VDOT Anda.
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jarak Lomba Terbaik <span class="text-danger">*</span></label>
                                    <select name="race_distance" class="form-control default-select" required id="race-distance">
                                        <option value="">Pilih Jarak</option>
                                        <option value="5k">5K</option>
                                        <option value="10k">10K</option>
                                        <option value="21k">Half Marathon (21K)</option>
                                        <option value="42k">Marathon (42K)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Waktu (jam:menit:detik) <span class="text-danger">*</span></label>
                                    <input type="text" name="race_time" class="form-control" placeholder="00:25:30" required pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
                                    <small class="text-muted">Format: HH:MM:SS (contoh: 00:25:30 untuk 25 menit 30 detik)</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Tanggal Lomba <span class="text-danger">*</span></label>
                                    <input type="date" name="race_date" class="form-control" required max="{{ date('Y-m-d') }}">
                                    <small class="text-muted">Harus dalam 3 bulan terakhir</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIWAYAT LATIHAN (VOLUME) -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>RIWAYAT LATIHAN (VOLUME)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Rata-rata KM per minggu (4 bulan terakhir) <span class="text-danger">*</span></label>
                                    <input type="number" name="weekly_mileage" class="form-control" required min="0" max="300" step="0.1">
                                    <small class="text-muted">Berapa kilometer rata-rata per minggu?</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Puncak KM per minggu <span class="text-danger">*</span></label>
                                    <input type="number" name="peak_mileage" class="form-control" required min="0" max="300" step="0.1">
                                    <small class="text-muted">Pernah sampai berapa KM tertinggi?</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Frekuensi Lari (hari per minggu) <span class="text-danger">*</span></label>
                                    <select name="training_frequency" class="form-control default-select" required>
                                        <option value="">Pilih</option>
                                        <option value="2">2 hari</option>
                                        <option value="3">3 hari</option>
                                        <option value="4">4 hari</option>
                                        <option value="5">5 hari</option>
                                        <option value="6">6 hari</option>
                                        <option value="7">7 hari</option>
                                    </select>
                                    <small class="text-muted">Berapa hari kamu bisa lari dalam seminggu?</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TARGET & TUJUAN -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-bullseye me-2"></i>TARGET & TUJUAN</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Target Lomba Utama - Jarak <span class="text-danger">*</span></label>
                                    <select name="goal_distance" class="form-control default-select" required id="goal-distance">
                                        <option value="">Pilih Jarak</option>
                                        <option value="5k">5K</option>
                                        <option value="10k">10K</option>
                                        <option value="21k">Half Marathon (21K)</option>
                                        <option value="42k">Marathon (42K)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Lomba <span class="text-danger">*</span></label>
                                    <input type="date" name="goal_race_date" class="form-control" required min="{{ date('Y-m-d') }}">
                                    <small class="text-muted">Sangat penting untuk periodisasi (18-24 minggu)</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Target Waktu Impian <small class="text-muted">Opsional</small></label>
                                    <input type="text" name="goal_time" class="form-control" placeholder="00:25:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
                                    <small class="text-muted">Hanya sebagai motivasi</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KESEHATAN -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-heartbeat me-2"></i>KESEHATAN</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Riwayat Cedera (6 bulan terakhir) <small class="text-muted">Opsional</small></label>
                                <textarea name="injury_history" class="form-control" rows="3" placeholder="Jelaskan riwayat cedera jika ada..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-calculator me-2"></i>Generate Program
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Fix modal scroll */
    #generateProgramModal.modal {
        overflow: hidden;
    }
    
    /* Make modal dialog scrollable */
    #generateProgramModal .modal-dialog-scrollable {
        max-height: calc(100% - 3.5rem);
        height: auto;
    }
    
    #generateProgramModal .modal-dialog-scrollable .modal-content {
        max-height: 90vh;
        height: 90vh;
        display: flex;
        flex-direction: column;
    }
    
    #generateProgramModal .modal-header {
        flex-shrink: 0;
    }
    
    /* Make modal body scrollable */
    #generateProgramModal .modal-body {
        overflow-y: auto !important;
        overflow-x: hidden;
        flex: 1 1 auto;
        max-height: none;
        padding-right: 15px;
    }
    
    /* Custom scrollbar styling */
    #generateProgramModal .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    
    #generateProgramModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    #generateProgramModal .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    #generateProgramModal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Firefox scrollbar */
    #generateProgramModal .modal-body {
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }
    
    /* Ensure modal footer is always visible */
    #generateProgramModal .modal-footer {
        flex-shrink: 0;
        border-top: 1px solid #dee2e6;
    }
</style>
@endpush

@push('scripts')
<script>
    $('#generate-program-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generating...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#generateProgramModal').modal('hide');
                    alert('Program berhasil di-generate! Silakan cek kalender Anda.');
                    window.location.href = '{{ route("runner.calendar") }}';
                } else {
                    alert(response.message || 'Gagal generate program. Silakan coba lagi.');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join('\n');
                }
                alert(message);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
</script>
@endpush

