@extends('layouts.app')

@section('title', 'Kalender Program Lari')

@section('page-title', 'Kalender Program Lari')

@push('styles')
    <link href="{{ asset('vendor/fullcalendar/css/fullcalendar.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <style>
        :root { --neon:#ccff00; --bg:#0b1220; --bg2:#0f172a; --fg:#e2e8f0; --muted:#94a3b8; --border:#1f2937; }
        .rl-card { background-color: var(--bg2); border-color: var(--border); }
        .rl-card .card-header { background-color: transparent; border-bottom: 1px solid var(--border); }
        .rl-card .card-title, .rl-card h4, .rl-card h6 { color: var(--fg); }
        .rl-muted { color: var(--muted); }
        .rl-btn { background-color: var(--bg2); border: 1px solid var(--border); color: var(--fg); }
        .rl-btn:hover { border-color: var(--neon); color: var(--neon); }
        .btn-primary { background-color: var(--neon); border-color: var(--neon); color: #0f172a; }
        .btn-primary:hover { background-color: rgba(204,255,0,.9); border-color: var(--neon); color: #0b1220; }
        .nav-tabs .nav-link { border: 0; color: var(--muted); }
        .nav-tabs .nav-link.active { color: var(--neon); border-bottom: 2px solid var(--neon); }
        .list-group-item { background-color: transparent; border-color: var(--border); color: var(--fg); }
        #calendar {
            max-width: 100%;
            margin: 0 auto;
            background-color: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
        }
        .workout-plan-list {
            max-height: 800px;
            overflow-y: auto;
        }
        .list-icon {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .list-icon.bgl-primary {
            background-color: rgba(13, 110, 253, 0.1);
        }
        .list-icon.bg-light {
            background-color: #f8f9fa;
        }
        /* Difficulty colors */
        .difficulty-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .difficulty-beginner, .difficulty-easy {
            background-color: #4CAF50;
            color: white;
        }
        .difficulty-intermediate, .difficulty-moderate {
            background-color: #FF9800;
            color: white;
        }
        .difficulty-advanced, .difficulty-hard {
            background-color: #F44336;
            color: white;
        }
        /* Phase colors for calendar */
        .phase-foundation {
            background-color: #E3F2FD !important;
        }
        .phase-early_quality {
            background-color: #F3E5F5 !important;
        }
        .phase-quality {
            background-color: #FFF3E0 !important;
        }
        .phase-final_prep {
            background-color: #E8F5E9 !important;
        }
        /* Event difficulty color accents */
        .fc-event.difficulty-easy { border-left: 4px solid #4CAF50; }
        .fc-event.difficulty-moderate { border-left: 4px solid #FF9800; }
        .fc-event.difficulty-hard { border-left: 4px solid #F44336; }
        .fc-event, .fc-daygrid-event { background-color: #1e293b; color: #e2e8f0; border: 1px solid #334155; border-radius: 8px; padding: 2px 6px; }
        .fc-toolbar-title { color: var(--fg); font-weight: 800; }
        .fc-button { background-color: #1e293b; border-color: #334155; color: #cbd5e1; }
        .fc-button:hover { color: var(--neon); border-color: var(--neon); }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-9 col-xxl-8">
        <div class="row">
            <div class="col-xl-12">
                <div class="card plan-list rl-card">
                    <div class="card-header d-sm-flex flex-wrap d-block pb-0 border-0">
                        <div class="me-auto pe-3 mb-3">
                            <h4 class="fs-20">Program Aktif</h4>
                            <p class="fs-13 mb-0 rl-muted">Daftar program lari yang sedang Anda ikuti</p>
                        </div>
                        <a href="{{ route('programs.index') }}" class="btn rounded btn-primary mb-3">
                            <svg class="me-2" width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 5V19M5 12H19" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Pilih Program
                        </a>
                    </div>
                    <div class="card-body">
                        @if($enrollments->count() > 0)
                            <div class="list-group">
                                @foreach($enrollments as $enrollment)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $enrollment->program->title }}</h6>
                                            <small class="rl-muted">
                                                Mulai: {{ $enrollment->start_date->format('d M Y') }} | 
                                                Selesai: {{ $enrollment->end_date->format('d M Y') }}
                                            </small>
                                        </div>
                                        <button class="btn btn-sm rl-btn delete-program-btn" data-enrollment-id="{{ $enrollment->id }}">
                                            <i class="las la-trash"></i> Hapus
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center p-4">
                                <p class="rl-muted">Belum ada program aktif. <a href="{{ route('programs.index') }}">Pilih program</a> untuk memulai.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-12 mt-3">
                <div class="card plan-list rl-card">
                    <div class="card-header d-sm-flex flex-wrap d-block pb-0 border-0">
                        <div class="me-auto pe-3 mb-3">
                            <h4 class="fs-20">Plan List</h4>
                            <p class="fs-13 mb-0 rl-muted">Daftar workout plan program lari Anda</p>
                        </div>
                        <div class="card-action card-tabs me-4 mt-3 mt-sm-0 mb-3">
                            <ul class="nav nav-tabs" role="tablist" id="workout-filter-tabs">
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#All" role="tab" aria-selected="false" data-filter="all">All</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#Unfinished" role="tab" aria-selected="true" data-filter="unfinished">Unfinished</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#Finished" role="tab" data-filter="finished">Finished</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body tab-content pt-2 workout-plan-list">
                        <div class="tab-pane fade active show" id="Unfinished">
                            <div id="workout-plans-container">
                                <div class="text-center p-4">
                                    <p class="rl-muted">Memuat workout plan...</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="All">
                            <div id="workout-plans-all-container">
                                <div class="text-center p-4">
                                    <p class="rl-muted">Memuat workout plan...</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="Finished">
                            <div id="workout-plans-finished-container">
                                <div class="text-center p-4">
                                    <p class="rl-muted">Memuat workout plan...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-12 mt-3">
                <div class="card rl-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title">Kalender Program Lari</h4>
                                <p class="mb-0 rl-muted">Klik tanggal untuk menambah/edit workout</p>
                            </div>
                            <div>
                                <button class="btn rl-btn" id="add-custom-workout-btn">
                                    <i class="las la-plus me-1"></i> Tambah Custom Workout
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-xxl-4">
        <div class="row">
            <div class="col-xl-12">
                <div class="card flex-xl-column flex-md-row flex-column">
                    <div class="card-body border-bottom pb-4 p-2 event-calender">
                        <input type='text' class="form-control d-none" id='datetimepicker1'>
                    </div>
                    <div class="card-body">
                        <h4 class="text-black mb-4">Rencana Minggu Depan</h4>
                        <div id="next-week-plans">
                            <div class="text-center p-3">
                                <p class="text-muted small">Memuat rencana...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detail Workout -->
<div class="modal fade" id="workoutDetailModal" tabindex="-1" aria-labelledby="workoutDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workoutDetailModalLabel">Detail Workout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="workoutDetailContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="delete-workout-btn" style="display: none;">
                    <i class="fas fa-trash me-1"></i>Hapus Workout
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Tambah/Edit Workout -->
<div class="modal fade" id="workoutFormModal" tabindex="-1" aria-labelledby="workoutFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workoutFormModalLabel">Tambah Workout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="workoutForm">
                <div class="modal-body">
                    <input type="hidden" id="workout_id" name="workout_id">
                    <input type="hidden" id="workout_date" name="workout_date">
                    
                    <div class="mb-3">
                        <label for="workout_type" class="form-label">Jenis Workout <span class="text-danger">*</span></label>
                        <select class="form-control" id="workout_type" name="type" required>
                            <option value="run">Run</option>
                            <option value="easy_run">Easy Run</option>
                            <option value="interval">Interval</option>
                            <option value="tempo">Tempo</option>
                            <option value="yoga">Yoga</option>
                            <option value="cycling">Cycling</option>
                            <option value="rest">Rest</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="workout_difficulty" class="form-label">Kesulitan <span class="text-danger">*</span></label>
                        <select class="form-control" id="workout_difficulty" name="difficulty" required>
                            <option value="easy">Mudah</option>
                            <option value="moderate" selected>Sedang</option>
                            <option value="hard">Sulit</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="workout_distance" class="form-label">Jarak (km)</label>
                        <input type="number" step="0.1" class="form-control" id="workout_distance" name="distance" placeholder="Contoh: 5">
                    </div>

                    <div class="mb-3">
                        <label for="workout_duration" class="form-label">Durasi</label>
                        <input type="text" class="form-control" id="workout_duration" name="duration" placeholder="Contoh: 00:30:00">
                    </div>

                    <div class="mb-3">
                        <label for="workout_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="workout_description" name="description" rows="3" placeholder="Deskripsi workout"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/fullcalendar/js/fullcalendar.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap-datetimepicker/js/moment.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        // Global calendar instance
        var calendar = null;

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: '{{ route("runner.calendar.events") }}',
                locale: 'id',
                firstDay: 1,
                eventClassNames: function(arg) {
                    var classes = [];
                    var props = arg.event.extendedProps || {};
                    if (props.difficulty) {
                        classes.push('difficulty-' + props.difficulty);
                    }
                    if (props.phase) {
                        classes.push('phase-' + props.phase);
                    }
                    return classes;
                },
                eventDidMount: function(arg) {
                    if (arg.el) {
                        arg.el.style.borderRadius = '8px';
                    }
                },
                dateClick: function(info) {
                    // Open form modal to add/edit workout
                    openWorkoutFormModal(info.dateStr);
                },
                eventClick: function(info) {
                    // Show workout detail modal
                    showWorkoutDetail(info);
                    info.jsEvent.preventDefault();
                },
                height: 'auto'
            });
            calendar.render();

            // Initialize datetime picker for sidebar calendar
            $('#datetimepicker1').datetimepicker({
                inline: true,
            });

            // Load workout plans
            loadWorkoutPlans('unfinished');

            // Tab click handler
            $('#workout-filter-tabs a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                var filter = $(e.target).data('filter');
                loadWorkoutPlans(filter);
            });

            // Delete program button handler
            $(document).on('click', '.delete-program-btn', function() {
                var enrollmentId = $(this).data('enrollment-id');
                if (confirm('Apakah Anda yakin ingin menghapus program ini? Semua data tracking akan dihapus.')) {
                    deleteEnrollment(enrollmentId);
                }
            });

            // Workout form submit handler
            $('#workoutForm').on('submit', function(e) {
                e.preventDefault();
                saveCustomWorkout();
            });
        });

            // Delete workout button handler (outside DOMContentLoaded to ensure it's available)
            $(document).on('click', '#delete-workout-btn', function() {
                if (!currentWorkoutInfo) {
                    alert('Tidak ada workout yang dipilih.');
                    return;
                }
            
            var props = currentWorkoutInfo.event.extendedProps;
            
            if (props.type === 'custom_workout' && props.workout_id) {
                if (confirm('Apakah Anda yakin ingin menghapus workout ini?')) {
                    deleteCustomWorkoutById(props.workout_id);
                }
            } else {
                alert('Workout ini tidak dapat dihapus.');
            }
        });

        function openWorkoutFormModal(dateStr) {
            $('#workoutFormModalLabel').text('Tambah Workout');
            $('#workout_id').val('');
            $('#workout_date').val(dateStr);
            $('#workout_type').val('run');
            $('#workout_difficulty').val('moderate');
            $('#workout_distance').val('');
            $('#workout_duration').val('');
            $('#workout_description').val('');
            
            var modal = new bootstrap.Modal(document.getElementById('workoutFormModal'));
            modal.show();
        }

        // Add custom workout CTA
        document.addEventListener('click', function(e) {
            if (e.target && (e.target.id === 'add-custom-workout-btn' || e.target.closest('#add-custom-workout-btn'))) {
                var today = new Date();
                var yyyy = today.getFullYear();
                var mm = String(today.getMonth()+1).padStart(2,'0');
                var dd = String(today.getDate()).padStart(2,'0');
                openWorkoutFormModal(yyyy + '-' + mm + '-' + dd);
            }
        });

        // Store current workout info for delete action
        var currentWorkoutInfo = null;

        function showWorkoutDetail(info) {
            var props = info.event.extendedProps;
            var content = '';
            currentWorkoutInfo = info; // Store for delete action

            if (props.type === 'program_session') {
                var session = props.session;
                content += '<h5>' + props.program_title + '</h5>';
                content += '<hr>';
                content += '<p><strong>Jenis:</strong> ' + getActivityTypeLabel(session.type || 'run') + '</p>';
                if (session.distance) {
                    content += '<p><strong>Jarak:</strong> ' + session.distance + ' km</p>';
                }
                if (session.duration) {
                    content += '<p><strong>Durasi:</strong> ' + session.duration + '</p>';
                }
                if (props.difficulty) {
                    content += '<p><strong>Kesulitan:</strong> <span class="difficulty-badge difficulty-' + props.difficulty + '">' + props.difficulty.toUpperCase() + '</span></p>';
                }
                if (props.phase) {
                    var phaseLabels = {
                        'foundation': 'Foundation',
                        'early_quality': 'Early Quality',
                        'quality': 'Quality',
                        'final_prep': 'Final Preparation'
                    };
                    content += '<p><strong>Fase:</strong> ' + (phaseLabels[props.phase] || props.phase) + '</p>';
                }
                if (session.description) {
                    content += '<p><strong>Deskripsi:</strong><br>' + session.description + '</p>';
                }
                // Hide delete button for program sessions (can't delete program sessions)
                $('#delete-workout-btn').hide();
            } else if (props.type === 'custom_workout') {
                var workout = props.workout;
                content += '<h5>Custom Workout</h5>';
                content += '<hr>';
                content += '<p><strong>Jenis:</strong> ' + getActivityTypeLabel(workout.type || 'run') + '</p>';
                if (workout.distance) {
                    content += '<p><strong>Jarak:</strong> ' + workout.distance + ' km</p>';
                }
                if (workout.duration) {
                    content += '<p><strong>Durasi:</strong> ' + workout.duration + '</p>';
                }
                if (workout.difficulty) {
                    content += '<p><strong>Kesulitan:</strong> <span class="difficulty-badge difficulty-' + workout.difficulty + '">' + workout.difficulty.toUpperCase() + '</span></p>';
                }
                if (workout.description) {
                    content += '<p><strong>Deskripsi:</strong><br>' + workout.description + '</p>';
                }
                content += '<p><strong>Status:</strong> ' + (workout.status || 'pending') + '</p>';
                // Show delete button for custom workouts
                $('#delete-workout-btn').show();
            }

            $('#workoutDetailContent').html(content);
            var modal = new bootstrap.Modal(document.getElementById('workoutDetailModal'));
            modal.show();
        }

        function loadWorkoutPlans(filter) {
            $.ajax({
                url: '{{ route("runner.calendar.workout-plans") }}',
                method: 'GET',
                data: { filter: filter },
                success: function(response) {
                    renderWorkoutPlans(response, filter);
                    loadNextWeekPlans();
                },
                error: function(xhr) {
                    var containerId = filter === 'all' ? '#workout-plans-all-container' : 
                                     filter === 'finished' ? '#workout-plans-finished-container' : 
                                     '#workout-plans-container';
                    var errorMsg = 'Gagal memuat workout plan.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }
                    $(containerId).html('<div class="text-center p-4"><p class="text-danger">' + errorMsg + '</p></div>');
                }
            });
        }

        function renderWorkoutPlans(plans, filter) {
            var containerId = filter === 'all' ? '#workout-plans-all-container' : 
                             filter === 'finished' ? '#workout-plans-finished-container' : 
                             '#workout-plans-container';
            
            if (plans.length === 0) {
                $(containerId).html('<div class="text-center p-4"><p class="text-muted">Tidak ada workout plan.</p></div>');
                return;
            }

            var html = '';
            plans.forEach(function(plan) {
                var dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                var dayName = dayNames[new Date(plan.date).getDay()] || plan.day_name;
                var iconClass = plan.status === 'completed' ? 'bg-light' : 'bgl-primary';
                var statusClass = plan.status === 'completed' ? 'text-primary' : 
                                 plan.status === 'started' ? 'text-secondary' : 'text-danger';
                var statusText = plan.status === 'completed' ? 'Finished' : 
                                plan.status === 'started' ? 'On Progress' : 'UNFINISHED';
                
                html += '<div class="d-flex border-bottom flex-wrap pt-3 list-row align-items-center mb-2 px-3 workout-plan-item" data-date="' + plan.date + '">';
                html += '<div class="col-xl-5 col-xxl-8 col-lg-6 col-sm-8 d-flex align-items-center">';
                html += '<div class="list-icon ' + iconClass + ' me-3 mb-3">';
                html += '<p class="fs-24 mb-0 mt-2">' + plan.day_number + '</p>';
                html += '<span class="fs-14">' + dayName + '</span>';
                html += '</div>';
                html += '<div class="info mb-3">';
                html += '<h4 class="fs-20"><a href="#" class="text-black workout-detail-link" data-plan=\'' + JSON.stringify(plan).replace(/'/g, "&#39;") + '\'>' + (plan.description || plan.type || 'Workout') + '</a></h4>';
                html += '<span class="' + statusClass + ' font-w600">' + statusText + '</span>';
                html += '</div>';
                html += '</div>';
                
                var activityIcon = getActivityIcon(plan.type);
                html += '<div class="col-xl-2 col-xxl-4 col-lg-2 col-sm-4 activities mb-3 me-auto ps-3 pe-3 text-sm-center text-xl-end">';
                html += activityIcon;
                html += '<span class="ms-2">' + getActivityTypeLabel(plan.type) + '</span>';
                html += '</div>';
                
                html += '<div class="col-xl-5 col-xxl-12 col-lg-4 col-sm-12 d-flex align-items-center">';
                
                if (plan.status === 'pending') {
                    html += '<a href="#" class="btn mb-3 play-button rounded me-3 start-workout-btn" data-tracking-id="' + (plan.tracking_id || '') + '" data-enrollment-id="' + plan.enrollment_id + '" data-session-day="' + plan.session_day + '"><i class="las la-caret-right scale-2 me-3"></i>Start Workout</a>';
                } else if (plan.status === 'started') {
                    html += '<a href="#" class="btn mb-3 play-button rounded me-3 complete-workout-btn" data-tracking-id="' + (plan.tracking_id || '') + '" data-enrollment-id="' + plan.enrollment_id + '" data-session-day="' + plan.session_day + '"><i class="las la-check scale-2 me-3"></i>Set Finish</a>';
                }
                
                html += '</div>';
                html += '</div>';
            });

            $(containerId).html(html);

            // Attach event handlers
            $('.start-workout-btn').on('click', function(e) {
                e.preventDefault();
                updateSessionStatus($(this).data('enrollment-id'), $(this).data('session-day'), 'started');
            });

            $('.complete-workout-btn').on('click', function(e) {
                e.preventDefault();
                updateSessionStatus($(this).data('enrollment-id'), $(this).data('session-day'), 'completed');
            });

            $('.workout-detail-link').on('click', function(e) {
                e.preventDefault();
                var plan = JSON.parse($(this).data('plan').replace(/&#39;/g, "'"));
                showPlanDetail(plan);
            });
        }

        function showPlanDetail(plan) {
            var content = '<h5>' + plan.program_title + '</h5>';
            content += '<hr>';
            content += '<p><strong>Tanggal:</strong> ' + plan.date_formatted + '</p>';
            content += '<p><strong>Jenis:</strong> ' + getActivityTypeLabel(plan.type) + '</p>';
            if (plan.distance) {
                content += '<p><strong>Jarak:</strong> ' + plan.distance + ' km</p>';
            }
            if (plan.duration) {
                content += '<p><strong>Durasi:</strong> ' + plan.duration + '</p>';
            }
            if (plan.program_difficulty) {
                content += '<p><strong>Kesulitan:</strong> <span class="difficulty-badge difficulty-' + plan.program_difficulty + '">' + plan.program_difficulty.toUpperCase() + '</span></p>';
            }
            if (plan.description) {
                content += '<p><strong>Deskripsi:</strong><br>' + plan.description + '</p>';
            }
            content += '<p><strong>Status:</strong> ' + (plan.status || 'pending') + '</p>';

            $('#workoutDetailContent').html(content);
            var modal = new bootstrap.Modal(document.getElementById('workoutDetailModal'));
            modal.show();
        }

        function getActivityIcon(type) {
            return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#FF9432"/></svg>';
        }

        function getActivityTypeLabel(type) {
            var labels = {
                'running': 'Running',
                'run': 'Run',
                'easy_run': 'Easy Run',
                'interval': 'Interval',
                'tempo': 'Tempo',
                'yoga': 'Yoga',
                'cycling': 'Cycling',
                'rest': 'Rest'
            };
            return labels[type] || 'Running';
        }

        function updateSessionStatus(enrollmentId, sessionDay, status) {
            $.ajax({
                url: '{{ route("runner.calendar.update-session-status") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    enrollment_id: enrollmentId,
                    session_day: sessionDay,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    alert('Gagal update status workout.');
                }
            });
        }

        function saveCustomWorkout() {
            var formData = {
                _token: '{{ csrf_token() }}',
                workout_id: $('#workout_id').val(),
                workout_date: $('#workout_date').val(),
                type: $('#workout_type').val(),
                difficulty: $('#workout_difficulty').val(),
                distance: $('#workout_distance').val() || null,
                duration: $('#workout_duration').val() || null,
                description: $('#workout_description').val() || null,
            };

            $.ajax({
                url: '{{ route("runner.calendar.custom-workout.store") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('workoutFormModal'));
                        modal.hide();
                        location.reload();
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Gagal menyimpan workout.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }

        function deleteEnrollment(enrollmentId) {
            var baseUrl = '{{ url("/runner/calendar/enrollment") }}';
            var deleteUrl = baseUrl + '/' + enrollmentId + '/delete';
            
            $.ajax({
                url: deleteUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'Gagal menghapus program.');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Gagal menghapus program.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 405) {
                        errorMsg = 'Method tidak diizinkan. Silakan refresh halaman dan coba lagi.';
                    } else if (xhr.status === 404) {
                        errorMsg = 'Program tidak ditemukan.';
                    } else if (xhr.status === 403) {
                        errorMsg = 'Anda tidak memiliki izin untuk menghapus program ini.';
                    }
                    alert(errorMsg);
                }
            });
        }

        function deleteCustomWorkoutById(workoutId) {
            var baseUrl = '{{ url("/runner/calendar/custom-workout") }}';
            var deleteUrl = baseUrl + '/' + workoutId;
            
            $.ajax({
                url: deleteUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        // Close modal
                        var modal = bootstrap.Modal.getInstance(document.getElementById('workoutDetailModal'));
                        modal.hide();
                        
                        // Refresh calendar
                        if (calendar) {
                            calendar.refetchEvents();
                        } else {
                            location.reload();
                        }
                    } else {
                        alert(response.message || 'Gagal menghapus workout.');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Gagal menghapus workout.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }

        function loadNextWeekPlans() {
            // Load next week plans for sidebar
            // This can be implemented later
        }
    </script>
@endpush
