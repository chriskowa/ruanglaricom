@push('scripts')
@include('layouts.components.advanced-builder-utils')
<script src="{{ asset('vendor/chart-js/chart.bundle.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
const { createApp, ref, reactive, onMounted, computed, watch, nextTick } = Vue;
(function(){
    const root = document.getElementById('runner-calendar-app');
    if (!root) { console.error('Runner calendar root not found'); return; }
})();
createApp({
    setup() {
        // Define static assets/urls first (avoid TDZ/ReferenceError in helpers called early)
        const assetStorage = @json(asset('storage'));
        const assetProfile = @json(asset('images/profile/profile.png'));
        const runnerUrl = @json(url('/runner'));
        const chatUrl = @json(url('/chat'));

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        console.log('[RunnerCalendar] Setup Init', { runnerUrl, chatUrl, hasCsrf: !!csrf });

        const filter = ref('unfinished');
        const plans = ref([]);
        const pageSize = 10;
        const visibleCount = ref(pageSize);
        const notification = ref(null);
        const showHeaderActions = ref(false);
        const showMobileAddSheet = ref(false);
        const activeDock = ref('today');
        const activeMobileTab = ref('calendar');

        const showNotification = (message, type = 'success') => {
            notification.value = { message, type };
            setTimeout(() => {
                notification.value = null;
            }, 5000);
        };

        const displayedPlans = computed(() => plans.value.slice(0, visibleCount.value));
        const canLoadMore = computed(() => visibleCount.value < plans.value.length);
        const loadMorePlans = () => {
            visibleCount.value = Math.min(visibleCount.value + pageSize, plans.value.length);
        };

        const openMobileAddSheet = () => {
            showHeaderActions.value = false;
            showMobileAddSheet.value = true;
        };

        const scrollToSection = (key) => {
            try {
                activeMobileTab.value = 'calendar';
                nextTick(() => {
                    if (key === 'calendar') {
                        const el = document.getElementById('calendar') || document.getElementById('runner-calendar-section');
                        el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        activeDock.value = 'calendar';
                        return;
                    }
                    const el = document.getElementById('runner-plans-section');
                    el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    activeDock.value = 'today';
                });
            } catch (e) {
            }
        };
        const weeklyVolume = ref([]);
        const maxVolume = computed(() => {
            if (weeklyVolume.value.length === 0) return 0;
            return Math.max(...weeklyVolume.value.map(w => Math.max(w.planned, w.actual))) * 1.1; // Add 10% headroom
        });
        const plansLoading = ref(false);
        const programBag = ref(@json($programBag));
        const cancelledPrograms = ref(@json($cancelledPrograms ?? []));
        const bagTab = ref('available');
        const profileTab = ref('training');
        const trainingProfile = ref(@json($trainingProfile ?? []));
        const enrollments = ref(@json($enrollments ?? []));

        const hasUnpaidGenerator = computed(() => {
            return enrollments.value.some(en => 
                en.program?.is_self_generated && en.payment_status !== 'paid'
            );
        });

        const unpaidEnrollmentId = computed(() => {
            const en = enrollments.value.find(en => 
                en.program?.is_self_generated && en.payment_status !== 'paid'
            );
            return en ? en.id : null;
        });

        const firstLockedWeek = computed(() => {
            const en = enrollments.value.find(en => 
                en.program?.is_self_generated && en.payment_status !== 'paid'
            );
            if (!en || !en.program) return 2;
            const totalWeeks = en.program.duration_weeks || 12;
            return Math.floor(totalWeeks / 2) + 1;
        });

        const showDetailModal = ref(false);
        const syncLoading = ref(false);
        const isSyncingStrava = ref(false);
        const detail = reactive({});
        const donationLoading = ref(false);
        const donationAmount = ref(25000);

        // Promo Code State
        const promoCode = ref('');
        const promoApplied = ref(false);
        const promoError = ref('');
        const checkingPromo = ref(false);

        const applyPromo = async () => {
            if (!promoCode.value) return;
            checkingPromo.value = true;
            promoError.value = '';
            try {
                const res = await fetch(`{{ route('api.programs.verify-promo') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code: promoCode.value })
                });
                const data = await res.json();
                if (data.success) {
                    promoApplied.value = true;
                    showNotification('Kode valid! Program akan di-unlock gratis.');
                } else {
                    promoError.value = data.message || 'Kode promo tidak valid';
                    showNotification(promoError.value, 'error');
                }
            } catch (e) {
                promoError.value = 'Gagal verifikasi kode promo';
                showNotification(promoError.value, 'error');
            } finally {
                checkingPromo.value = false;
            }
        };

        const handleUnlockAction = async () => {
            const enrollmentId = detail.session?.is_locked ? detail.enrollment_id : unpaidEnrollmentId.value;
            if (!enrollmentId) return;

            if (promoApplied.value) {
                await unlockWithPromo(enrollmentId);
            } else {
                await payDonation(enrollmentId, donationAmount.value);
            }
        };

        const unlockWithPromo = async (enrollmentId) => {
            donationLoading.value = true;
            try {
                const res = await fetch(`{{ route('api.programs.unlock-promo') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ enrollment_id: enrollmentId, code: promoCode.value })
                });
                const data = await res.json();
                if (data.success) {
                    showNotification('Program berhasil di-unlock!');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showNotification(data.message || 'Gagal unlock program', 'error');
                }
            } catch (e) {
                showNotification('Terjadi kesalahan sistem', 'error');
            } finally {
                donationLoading.value = false;
            }
        };

        const payDonation = async (enrollmentId, amount = 25000) => {
            if (!enrollmentId) return;
            donationLoading.value = true;
            try {
                const res = await fetch(`{{ route('api.programs.pay') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ enrollment_id: enrollmentId, amount: amount })
                });
                const data = await res.json();
                if (data.success && data.snap_token) {
                    window.snap.pay(data.snap_token, {
                        onSuccess: (result) => {
                            alert('Donasi berhasil! Program akan segera di-unlock.');
                            window.location.reload();
                        },
                        onPending: (result) => {
                            alert('Menunggu pembayaran.');
                        },
                        onError: (result) => {
                            alert('Terjadi kesalahan pembayaran.');
                        }
                    });
                } else {
                    alert(data.message || 'Gagal membuat transaksi.');
                }
            } catch (e) {
                alert('Terjadi kesalahan sistem.');
            } finally {
                donationLoading.value = false;
            }
        };
        
        const resetDetail = () => {
            // Basic Info
            detailTitle.value = '';
            detail.date = null;
            detail.date_formatted = null;
            detail.type = 'run';
            detail.distance = null;
            detail.duration = null;
            detail.difficulty = null;
            detail.program_difficulty = null;
            detail.description = null;
            detail.status = 'pending';
            detail.source = null;
            detail.workout_id = null;
            detail.enrollment_id = null;
            detail.session_day = null;
            detail.workout_structure = null;
            detail.strength = null;

            // Tracking / Results
            detail.target_pace = null;
            detail.recommended_pace = null;
            detail.coach_feedback = null;
            detail.coach_rating = null;
            detail.strava_link = null;
            detail.notes = null;
            detail.actual_pace = null;
            detail.actual_duration = null;
            detail.actual_distance = null;

            // Strava Data
            detail.strava_metrics = null;
            detail.strava_splits = [];
            detail.strava_laps = [];
            detail.strava_streams = null;
            detail.strava_zone_analysis = null;
            detail.strava_zone_effect = null;
            detail.strava_zone_suggestion = null;

            // AI / Expert
            detail.analysis = null;
            detail.suggestion = null;
            detail.ai_analysis = null;
            detail.strava_activity_id = null;
            detail.session = null;
            
            stravaDetailsLoading.value = false;
            stravaDetailsError.value = '';
            aiAnalysisLoading.value = false;
            aiAnalysisError.value = '';
            stravaLinkInput.value = '';
            notesInput.value = '';
        };
        const detailTitle = ref('');
        const stravaLinkInput = ref('');
        const notesInput = ref('');
        const rpeInput = ref('');
        const feelingInput = ref('');
        const stravaDetailsLoading = ref(false);
        const stravaDetailsError = ref('');
        const stravaStreamsLoading = ref(false);
        const stravaStreamsError = ref('');
        const aiAnalysisLoading = ref(false);
        const aiAnalysisError = ref('');
        let stravaChart = null;
        const ttsSupported = computed(() => typeof window !== 'undefined' && 'speechSynthesis' in window);
        const isSpeaking = ref(false);
        let ttsVoice = null;
        let currentUtterance = null;
        const displayPace = computed(() => detail.actual_pace || detail.target_pace || detail.recommended_pace || null);
        const primaryMetricValue = computed(() => {
            try {
                if (detail.distance) {
                    const num = parseFloat(detail.distance);
                    return isNaN(num) ? detail.distance : num.toFixed(2);
                }
                if (detail.duration) return detail.duration;
            } catch(e){}
            return '--';
        });
        const primaryMetricUnit = computed(() => detail.distance ? 'km' : (detail.duration ? '' : ''));
        const statusDotClass = (s) => (s==='completed' || s==='imported') ? 'bg-green-400' : (s==='started' ? 'bg-yellow-400' : 'bg-red-400');
        const showFormModal = ref(false);
        const form = reactive({ 
            workout_id:'', 
            workout_date:'', 
            type:'run', 
            difficulty:'moderate', 
            distance:'', 
            duration:'', 
            description:'',
            workout_structure: [] // Array of steps
        });
        
        // Weekly Target State
        const showWeeklyTargetModal = ref(false);
        const weeklyTargetLoading = ref(false);
        const weeklyTargetForm = reactive({
            weekly_km_target: trainingProfile.value.weekly_km_target || ''
        });

        // Race Form State
        const showRaceModal = ref(false);
        const raceForm = reactive({
            name: '',
            date: '',
            distance: '',
            distLabel: '',
            goal_time: '',
            notes: ''
        });

        const loadTtsVoices = () => {
            if (!ttsSupported.value) return;
            const voices = window.speechSynthesis.getVoices();
            if (!voices || !voices.length) return;
            const preferred = voices.filter(v => /id-ID|en-US/i.test(v.lang));
            const male = preferred.find(v => /male|laki/i.test((v.name || '') + ' ' + (v.voiceURI || '')));
            ttsVoice = male || preferred[0] || voices[0];
        };

        const stopDetailSpeech = () => {
            if (!ttsSupported.value) return;
            window.speechSynthesis.cancel();
            isSpeaking.value = false;
            currentUtterance = null;
        };

        const speakDetailDescription = () => {
            if (!ttsSupported.value) return;
            const text = detail.description || '';
            if (!text) return;
            if (isSpeaking.value) {
                stopDetailSpeech();
                return;
            }
            if (!ttsVoice) loadTtsVoices();
            const utterance = new SpeechSynthesisUtterance(text);
            if (ttsVoice) utterance.voice = ttsVoice;
            utterance.rate = 1;
            utterance.pitch = 1;
            isSpeaking.value = true;
            utterance.onend = () => {
                isSpeaking.value = false;
                currentUtterance = null;
            };
            utterance.onerror = () => {
                isSpeaking.value = false;
                currentUtterance = null;
            };
            currentUtterance = utterance;
            window.speechSynthesis.speak(utterance);
        };
        
        // Workout Builder Helper Methods
        const addStep = (type) => {
            form.workout_structure.push({
                type: type, // warmup, run, interval, recovery, rest, cool_down
                duration_type: 'distance', // distance, time
                value: '',
                unit: 'km', // km, min, m, sec
                notes: ''
            });
        };

        const removeStep = (index) => {
            form.workout_structure.splice(index, 1);
        };

        const moveStep = (index, direction) => {
            if (direction === -1 && index > 0) {
                const temp = form.workout_structure[index];
                form.workout_structure[index] = form.workout_structure[index - 1];
                form.workout_structure[index - 1] = temp;
            } else if (direction === 1 && index < form.workout_structure.length - 1) {
                const temp = form.workout_structure[index];
                form.workout_structure[index] = form.workout_structure[index + 1];
                form.workout_structure[index + 1] = temp;
            }
        };

        const calculateTotalDistance = () => {
            // Auto-calculate total distance from structure if possible
            let total = 0;
            form.workout_structure.forEach(step => {
                if (step.duration_type === 'distance' && step.value) {
                    let val = parseFloat(step.value);
                    if (step.unit === 'm') val /= 1000;
                    total += val;
                }
            });
            if (total > 0) form.distance = total.toFixed(2);
        };

        // RuangLari Events Integration
        const ruangLariEvents = ref([]);
        const loadingEvents = ref(false);
        const eventSearchQuery = ref('');
        const showEventDropdown = ref(false);
        const showStravaGraphModal = ref(false);
        let stravaFullscreenChart = null;
        
        const filteredEvents = computed(() => {
            if (!eventSearchQuery.value) return ruangLariEvents.value;
            const query = eventSearchQuery.value.toLowerCase();
            return ruangLariEvents.value.filter(e => 
                (e.name || '').toLowerCase().includes(query) || 
                (e.location_name || '').toLowerCase().includes(query)
            );
        });

        const fetchRuangLariEvents = async () => {
            if (ruangLariEvents.value.length > 0) return;
            loadingEvents.value = true;
            try {
                // Use proxy to avoid CORS
                const res = await fetch('{{ route("calendar.events.proxy") }}');
                const data = await res.json();
                ruangLariEvents.value = Array.isArray(data) ? data : [];
            } catch (e) {
                console.error('Failed to fetch events', e);
            } finally {
                loadingEvents.value = false;
            }
        };

        const selectRuangLariEvent = (event) => {
            eventSearchQuery.value = event.name;
            showEventDropdown.value = false;
            onSelectRuangLariEvent(event);
        };

        const onSelectRuangLariEvent = (event) => {
            if (!event) return;
            // API Format: {"id":...,"name":"...","start_at":"yyyy-mm-dd hh:mm:ss",...}
            raceForm.name = event.name;
            
            // Construct link from slug if available
            const link = event.slug ? `/event-lari/${event.slug}` : '';
            raceForm.notes = link ? `Official Event: ${event.name}\nLink: ${link}` : `Official Event: ${event.name}`;
            
            // Parse date
            let dateStr = event.start_at;
            if (dateStr) {
                // If it's already YYYY-MM-DD (start_at usually is datetime string)
                if (dateStr.includes('-')) {
                     raceForm.date = dateStr.split(' ')[0];
                } 
            }
            
            // Guess distance
            const title = (event.name || '').toLowerCase();
            if (title.includes('marathon') && !title.includes('half')) {
                setRaceDist(42.2, 'FM');
            } else if (title.includes('half') || title.includes('hm') || title.includes('21k')) {
                setRaceDist(21.1, 'HM');
            } else if (title.includes('10k')) {
                setRaceDist(10, '10K');
            } else if (title.includes('5k')) {
                setRaceDist(5, '5K');
            }
        };

        watch(showRaceModal, (val) => {
            if (val) fetchRuangLariEvents();
        });

        watch(showStravaGraphModal, (val) => {
            if (val && detail.strava_streams) {
                setTimeout(() => {
                    if (stravaFullscreenChart) stravaFullscreenChart.destroy();
                    stravaFullscreenChart = renderChartToCanvas(detail.strava_streams, 'stravaMetricsChartFullscreen');
                }, 100);
            } else {
                if (stravaFullscreenChart) {
                    stravaFullscreenChart.destroy();
                    stravaFullscreenChart = null;
                }
            }
        });

        const showPbModal = ref(false);
        const openPbModal = async () => {
            showDetailModal.value = false;
            showStravaAnalysisModal.value = false;
            showVdotModal.value = false;
            showFormModal.value = false;
            showRaceModal.value = false;
            showPbModal.value = false;
            
            await nextTick();
            
            showPbModal.value = true;
        };
        const pbLoading = ref(false);
        const pbForm = reactive({
            pb_5k: trainingProfile.value.pb?.['5k'] || '',
            pb_10k: trainingProfile.value.pb?.['10k'] || '',
            pb_hm: trainingProfile.value.pb?.hm || '',
            pb_fm: trainingProfile.value.pb?.fm || '',
            pb_balke: trainingProfile.value.pb?.balke || '',
        });

        // VDOT Form State
        const showVdotModal = ref(false);
        const vdotLoading = ref(false);
        const vdotForm = reactive({
            age: 25,
            gender: 'male',
            race_distance: '5k',
            race_time: '00:25:00',
            race_date: new Date().toISOString().slice(0,10),
            weekly_mileage: 20,
            peak_mileage: 30,
            training_frequency: 3,
            goal_distance: '10k',
            goal_race_date: new Date(Date.now() + 120 * 24 * 60 * 60 * 1000).toISOString().slice(0,10), // ~4 months from now
            goal_time: '',
            runner_level: 'intermediate',
            long_run_day: 'sunday',
            is_tropical: false,
            use_ai: true
        });

        // Performance Improvement Insight Modal State
        const showInsightModal = ref(false);
        const insightData = ref(null);
        const insightType = ref('generate'); // 'generate' | 'pb'

        const openVdotModal = async () => {
            console.log('Button Clicked: openVdotModal');
            try {
                console.log('[RunnerCalendar] openVdotModal');
                showDetailModal.value = false;
                showFormModal.value = false;
                showRaceModal.value = false;
                // force close first so Vue applies DOM updates before re-opening
                showVdotModal.value = false;
                await nextTick();
                showVdotModal.value = true;
            } catch (e) {
                console.error('[RunnerCalendar] openVdotModal failed', e);
                // fallback: try to show anyway
                showVdotModal.value = true;
            }
        };

        let calendar = null;

        watch(activeMobileTab, (newTab) => {
            if (newTab === 'calendar') {
                nextTick(() => {
                    if (calendar) {
                        calendar.updateSize();
                    }
                });
            }
        });

        // ... existing methods ...

        const syncStrava = async () => {
            if (isSyncingStrava.value) return;
            isSyncingStrava.value = true;
            try {
                const res = await fetch('{{ route("runner.strava.sync") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    alert('Strava activities synced successfully!');
                    // Refresh calendar, plans and volume
                    await loadPlans();
                    if (calendar) calendar.refetchEvents();
                    loadWeeklyVolume();
                } else {
                    const msg = (data.message || 'Failed to sync Strava activities') + '\nHubungkan akun Strava sekarang?';
                    if (confirm(msg)) {
                        window.location.href = '{{ route("runner.strava.connect") }}';
                    } else {
                        alert(data.message || 'Failed to sync Strava activities');
                    }
                }
            } catch (e) {
                console.error(e);
                const msg = 'Gagal sync Strava.\nHubungkan akun Strava sekarang?';
                if (confirm(msg)) {
                    window.location.href = '{{ route("runner.strava.connect") }}';
                } else {
                    alert('An error occurred while syncing Strava');
                }
            } finally {
                isSyncingStrava.value = false;
            }
        };

        const syncTraining = async () => {
            syncLoading.value = true;
            try {
                // Refresh plans and calendar events
                await loadPlans();
                if (calendar) calendar.refetchEvents();
                // Optionally refresh profile if needed, but usually profile drives the sync
                alert('Training paces synced with current profile!');
            } catch (e) {
                alert('Failed to sync training.');
            } finally {
                syncLoading.value = false;
            }
        };

        const resetPlan = async (enrollmentId) => {
            if(!confirm('Are you sure you want to reset this plan? Progress will be lost and it will be moved back to your Program Bag.')) return;
            
            try {
                const res = await fetch(`{{ route('runner.calendar.reset-plan') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ enrollment_id: enrollmentId })
                });
                const data = await res.json();
                if (data.success) {
                    // Refresh page to sync everything properly
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to reset plan');
                }
            } catch (e) {
                alert('An error occurred');
            }
        };

        const applyProgram = async (enrollmentId) => {
            try {
                const target = programBag.value.find(e => e.id === enrollmentId);
                showDetailModal.value = false;
                showVdotModal.value = false;
                showFormModal.value = false;
                showRaceModal.value = false;
                showRescheduleModal.value = false;
                showApplyModal.value = false;
                await nextTick();
                applyTarget.value = target || { id: enrollmentId };
                const d = new Date();
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth()+1).padStart(2,'0');
                const dd = String(d.getDate()).padStart(2,'0');
                applyForm.start_date = `${yyyy}-${mm}-${dd}`;
                showApplyModal.value = true;
            } catch (e) {
                alert('An error occurred');
            }
        };

        const restoreProgram = async (enrollmentId) => {
            if(!confirm('Restore this program to your Available Bag?')) return;
            
            try {
                const res = await fetch(`{{ route('runner.calendar.restore-program') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ enrollment_id: enrollmentId })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to restore program');
                }
            } catch (e) {
                alert('An error occurred');
            }
        };

        // Reschedule Logic
        const showRescheduleModal = ref(false);
        const rescheduleLoading = ref(false);
        const rescheduleTarget = ref(null);
        const rescheduleTab = ref('standard'); // 'standard' or 'adaptive'
        const rescheduleForm = reactive({
            new_start_date: ''
        });

        const adaptiveRescheduleForm = reactive({
            reason: 'busy',
            days_missed: 5,
            start_date: new Date().toISOString().slice(0, 10),
            injury_severity: 'minor',
            body_part: 'knee',
            notes: ''
        });

        const adaptivePreview = ref(null);
        const previewLoading = ref(false);

        const openRescheduleModal = (enrollment) => {
            rescheduleTarget.value = enrollment;
            rescheduleForm.new_start_date = enrollment.start_date ? enrollment.start_date.slice(0,10) : new Date().toISOString().slice(0,10);
            adaptiveRescheduleForm.start_date = new Date().toISOString().slice(0, 10);
            adaptivePreview.value = null;
            showRescheduleModal.value = true;
        };

        const submitReschedule = async () => {
            if (!rescheduleForm.new_start_date) {
                alert('Please select a new start date');
                return;
            }
            
            rescheduleLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.calendar.reschedule-program') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ 
                        enrollment_id: rescheduleTarget.value.id,
                        new_start_date: rescheduleForm.new_start_date
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Program rescheduled successfully!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to reschedule program');
                }
            } catch (e) {
                alert('An error occurred');
            } finally {
                rescheduleLoading.value = false;
            }
        };

        const getAdaptivePreview = async () => {
            previewLoading.value = true;
            adaptivePreview.value = null;
            try {
                const res = await fetch(`{{ route('runner.calendar.adaptive-reschedule.preview') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({
                        enrollment_id: rescheduleTarget.value.id,
                        reason: adaptiveRescheduleForm.reason,
                        days_missed: adaptiveRescheduleForm.days_missed,
                        start_date: adaptiveRescheduleForm.start_date,
                        injury_severity: adaptiveRescheduleForm.reason === 'injury' ? adaptiveRescheduleForm.injury_severity : null,
                        body_part: adaptiveRescheduleForm.reason === 'injury' ? adaptiveRescheduleForm.body_part : null,
                        notes: adaptiveRescheduleForm.notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    adaptivePreview.value = data.preview;
                } else {
                    alert(data.message || 'Gagal memuat preview reschedule');
                }
            } catch (e) {
                alert('Terjadi kesalahan saat menghubungi server.');
            } finally {
                previewLoading.value = false;
            }
        };

        const submitAdaptiveReschedule = async () => {
            rescheduleLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.calendar.adaptive-reschedule.apply') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({
                        enrollment_id: rescheduleTarget.value.id,
                        reason: adaptiveRescheduleForm.reason,
                        days_missed: adaptiveRescheduleForm.days_missed,
                        start_date: adaptiveRescheduleForm.start_date,
                        injury_severity: adaptiveRescheduleForm.reason === 'injury' ? adaptiveRescheduleForm.injury_severity : null,
                        body_part: adaptiveRescheduleForm.reason === 'injury' ? adaptiveRescheduleForm.body_part : null,
                        notes: adaptiveRescheduleForm.notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Program berhasil dijadwalkan ulang secara adaptif!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menerapkan reschedule');
                }
            } catch (e) {
                alert('Terjadi kesalahan saat memproses reschedule.');
            } finally {
                rescheduleLoading.value = false;
            }
        };



        // GUIDED WORKOUT STATE
        const showGuidedPlayer = ref(false);
        const guidedExercises = ref([]);
        const currentExerciseIndex = ref(0);
        const currentExercise = computed(() => guidedExercises.value[currentExerciseIndex.value]);
        const isPlaying = ref(false);
        const timerSeconds = ref(0);
        let timerInterval = null;

        const countExercises = (d) => {
            if (d.strength?.plan && Array.isArray(d.strength.plan)) return d.strength.plan.length;
            const parsed = parseStrengthExercises(d);
            return parsed.length;
        };

        const parseStrengthExercises = (d) => {
            // 1. Try structured plan
            if (d.strength?.plan && Array.isArray(d.strength.plan)) {
                return d.strength.plan.map(ex => ({
                    name: ex.name || 'Exercise',
                    sets: ex.sets || '3',
                    reps: ex.reps || '10',
                    notes: ex.notes || ''
                }));
            }
            
            // 2. Try parsing description text (Simple Heuristic)
            // Assumes lines like: "3x15 Squats" or "Pushups: 3 sets of 10"
            if (d.description) {
                const lines = d.description.split('\n').filter(l => l.trim().length > 0);
                const exercises = [];
                
                lines.forEach(line => {
                    // Very basic parser: look for digits
                    const hasNumbers = /\d/.test(line);
                    if (hasNumbers) {
                        exercises.push({
                            name: line.replace(/^\d+x\d+\s*/, '').trim(), // Remove "3x10 " prefix if present
                            sets: (line.match(/(\d+)\s*(?:sets|x)/i) || ['','3'])[1],
                            reps: (line.match(/(?:x|of)\s*(\d+)/i) || ['','10'])[1],
                            notes: line
                        });
                    }
                });
                
                if (exercises.length > 0) return exercises;
            }

            // 3. Fallback
            return [
                { name: 'Warm Up', sets: '1', reps: '5 min', notes: 'General mobility' },
                { name: 'Main Circuit', sets: '3', reps: '12', notes: 'Check description for details' },
                { name: 'Cool Down', sets: '1', reps: '5 min', notes: 'Stretching' }
            ];
        };

        const getExerciseIcon = (name) => {
            const n = String(name || '').toLowerCase();
            if (n.includes('squat')) return '<i class="fa-solid fa-dumbbell"></i>';
            if (n.includes('push')) return '<i class="fa-solid fa-hand-fist"></i>';
            if (n.includes('plank') || n.includes('core')) return '<i class="fa-solid fa-cubes"></i>';
            if (n.includes('lunge')) return '<i class="fa-solid fa-shoe-prints"></i>';
            if (n.includes('run') || n.includes('warm')) return '<i class="fa-solid fa-person-running"></i>';
            if (n.includes('yoga') || n.includes('stretch')) return '<i class="fa-solid fa-child-reaching"></i>';
            return '<i class="fa-solid fa-bolt"></i>';
        };

        const formatTimer = (s) => {
            const sec = Math.max(0, parseInt(s || 0, 10));
            const m = Math.floor(sec / 60);
            const ss = sec % 60;
            return `${m}:${String(ss).padStart(2, '0')}`;
        };

        const startGuidedWorkout = async (d) => {
            if (!showGuidedPlayer.value && (d.status !== 'started')) {
                const ok = await updateSessionStatus(d, 'started');
                if (!ok) return;
                d.status = 'started';
            }

            guidedExercises.value = parseStrengthExercises(d);
            currentExerciseIndex.value = 0;
            showGuidedPlayer.value = true;
            isPlaying.value = false;
            timerSeconds.value = 0;
        };

        const exitGuidedWorkout = () => {
            resetTimer();
            showGuidedPlayer.value = false;
        };

        const stopGuidedWorkout = async () => {
            const ok = await updateSessionStatus(detail, 'pending');
            if (!ok) return;
            resetTimer();
            showGuidedPlayer.value = false;
        };

        const finishGuidedWorkout = async () => {
            const ok = await updateSessionStatus(detail, 'completed');
            if (!ok) return;
            resetTimer();
            showGuidedPlayer.value = false;
        };

        const togglePlay = () => {
            isPlaying.value = !isPlaying.value;
            if (isPlaying.value) {
                timerInterval = setInterval(() => {
                    timerSeconds.value++;
                }, 1000);
            } else {
                clearInterval(timerInterval);
            }
        };

        const nextExercise = () => {
            if (currentExerciseIndex.value < guidedExercises.value.length - 1) {
                currentExerciseIndex.value++;
                resetTimer();
            }
        };

        const prevExercise = () => {
            if (currentExerciseIndex.value > 0) {
                currentExerciseIndex.value--;
                resetTimer();
            }
        };

        const resetTimer = () => {
            isPlaying.value = false;
            clearInterval(timerInterval);
            timerSeconds.value = 0;
        };

        // Apply Program Logic (Modal)
        const showApplyModal = ref(false);
        const applyLoading = ref(false);
        const applyTarget = ref(null);
        const applyForm = reactive({
            start_date: ''
        });
        const submitApply = async () => {
            if (!applyTarget.value || !applyTarget.value.id) {
                alert('Invalid program');
                return;
            }
            if (!applyForm.start_date) {
                alert('Please select a start date');
                return;
            }
            applyLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.calendar.apply-program') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ enrollment_id: applyTarget.value.id, start_date: applyForm.start_date })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to apply program');
                }
            } catch (e) {
                alert('An error occurred');
            } finally {
                applyLoading.value = false;
            }
        };

        const previewExercise = (ex) => {
            // In future: Show modal preview. For now, we rely on the list view.
            console.log('Preview', ex);
        };

        const updatePb = async () => {
            pbLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.calendar.update-pb') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(pbForm)
                });
                const data = await res.json();
                if (data.success) {
                    trainingProfile.value.vdot = data.vdot;
                    trainingProfile.value.paces = data.paces;
                    if(data.equivalent_race_times) {
                        trainingProfile.value.equivalent_race_times = data.equivalent_race_times;
                    }
                    showPbModal.value = false;
                    // Show improvement insight modal if analysis available
                    if (data.improvement_analysis) {
                        insightData.value = data.improvement_analysis;
                        insightType.value = 'pb';
                        showInsightModal.value = true;
                    }
                } else {
                    alert(data.message || 'Failed to update PB');
                }
            } catch (e) {
                alert('An error occurred');
            } finally {
                pbLoading.value = false;
            }
        };

        const updateWeeklyTarget = async () => {
            weeklyTargetLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.calendar.update-weekly-target') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(weeklyTargetForm)
                });
                const data = await res.json();
                if (data.success) {
                    trainingProfile.value.weekly_km_target = data.weekly_km_target;
                    showWeeklyTargetModal.value = false;
                    alert('Weekly target updated!');
                } else {
                    alert(data.message || 'Failed to update weekly target');
                }
            } catch (e) {
                alert('An error occurred');
            } finally {
                weeklyTargetLoading.value = false;
            }
        };

        const generateVdot = async () => {
            vdotLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.programs.generate') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(vdotForm)
                });
                const data = await res.json();
                if (data.success) {
                    showVdotModal.value = false;
                    // Show improvement insight modal if data available
                    if (data.improvement_projection) {
                        insightData.value = data.improvement_projection;
                        insightType.value = 'generate';
                        showInsightModal.value = true;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Failed to generate program');
                }
            } catch (e) {
                alert('An error occurred while generating program');
            } finally {
                vdotLoading.value = false;
            }
        };


        const resetPlanList = async () => {
            if(!confirm('Apakah Anda yakin? Ini akan MENGHENTIKAN semua program aktif dan menghapus semua custom workout. Semua progress akan direset.')) return;
            
            try {
                const res = await fetch(`{{ route('runner.calendar.reset-plan-list') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to reset plans');
                }
            } catch (e) {
                alert('An error occurred');
            }
        };

        const formatPace = (minPerKm) => {
            if (!minPerKm) return '-';
            const mins = Math.floor(minPerKm);
            const secs = Math.round((minPerKm - mins) * 60);
            return `${mins}:${secs.toString().padStart(2,'0')}`;
        };

        const formatSeconds = (s) => {
            const sec = parseInt(s || 0, 10);
            if (!sec || sec < 0) return '-';
            const h = Math.floor(sec / 3600);
            const m = Math.floor((sec % 3600) / 60);
            const ss = sec % 60;
            if (h > 0) return `${h}:${String(m).padStart(2,'0')}:${String(ss).padStart(2,'0')}`;
            return `${m}:${String(ss).padStart(2,'0')}`;
        };

        const extractStravaActivityId = (url) => {
            const m = String(url || '').match(/strava\.com\/activities\/(\d+)/i);
            return m ? parseInt(m[1], 10) : null;
        };

        const destroyStravaChart = () => {
            try {
                if (stravaChart) {
                    stravaChart.destroy();
                }
            } catch (e) {}
            stravaChart = null;
        };

        const toPaceSecPerKm = (mps) => {
            const v = parseFloat(mps || 0);
            if (!v || v <= 0) return null;
            return 1000 / v;
        };

        const formatPaceFromSec = (secPerKm) => {
            const s = parseFloat(secPerKm || 0);
            if (!s || s <= 0) return '-';
            const mins = Math.floor(s / 60);
            const secs = Math.round(s - (mins * 60));
            return `${mins}:${String(secs).padStart(2,'0')}`;
        };

        const paceMinToSec = (minPerKm) => {
            const v = parseFloat(minPerKm || 0);
            if (!v || v <= 0) return null;
            return v * 60;
        };

        const buildPaceZones = (streams, paces) => {
            if (!streams || !Array.isArray(streams.velocity_smooth)) return null;
            const thresholds = {
                E: paceMinToSec(paces?.E),
                M: paceMinToSec(paces?.M),
                T: paceMinToSec(paces?.T),
                I: paceMinToSec(paces?.I),
                R: paceMinToSec(paces?.R)
            };
            if (!thresholds.E || !thresholds.M || !thresholds.T || !thresholds.I || !thresholds.R) return null;
            const counts = { E: 0, M: 0, T: 0, I: 0, R: 0 };
            let total = 0;
            streams.velocity_smooth.forEach((v) => {
                const paceSec = toPaceSecPerKm(v);
                if (!paceSec) return;
                total += 1;
                if (paceSec >= thresholds.E) counts.E += 1;
                else if (paceSec >= thresholds.M) counts.M += 1;
                else if (paceSec >= thresholds.T) counts.T += 1;
                else if (paceSec >= thresholds.I) counts.I += 1;
                else counts.R += 1;
            });
            if (!total) return null;
            const toPct = (v) => Math.round((v / total) * 100);
            const zones = {
                E: toPct(counts.E),
                M: toPct(counts.M),
                T: toPct(counts.T),
                I: toPct(counts.I),
                R: toPct(counts.R)
            };
            return {
                zones,
                summary: {
                    easy: Math.round(zones.E + zones.M),
                    tempo: Math.round(zones.T),
                    speed: Math.round(zones.I + zones.R)
                }
            };
        };

        const buildHrZones = (streams, maxHrValue) => {
            if (!streams || !Array.isArray(streams.heartrate)) return null;
            const maxFromStream = Math.max(...streams.heartrate.filter(v => typeof v === 'number' && !isNaN(v)));
            const maxHr = parseFloat(maxHrValue || maxFromStream || 0);
            if (!maxHr || maxHr <= 0) return null;
            const counts = { Z1: 0, Z2: 0, Z3: 0, Z4: 0, Z5: 0 };
            let total = 0;
            streams.heartrate.forEach((v) => {
                const hr = typeof v === 'number' ? v : parseFloat(v);
                if (!hr || hr <= 0) return;
                const ratio = hr / maxHr;
                total += 1;
                if (ratio < 0.6) counts.Z1 += 1;
                else if (ratio < 0.7) counts.Z2 += 1;
                else if (ratio < 0.8) counts.Z3 += 1;
                else if (ratio < 0.9) counts.Z4 += 1;
                else counts.Z5 += 1;
            });
            if (!total) return null;
            const toPct = (v) => Math.round((v / total) * 100);
            return {
                Z1: toPct(counts.Z1),
                Z2: toPct(counts.Z2),
                Z3: toPct(counts.Z3),
                Z4: toPct(counts.Z4),
                Z5: toPct(counts.Z5)
            };
        };

        const buildZoneAnalysis = (paceZones, hrZones, metrics) => {
            if (!paceZones && !hrZones) return null;
            const analysis = [];
            const effects = [];
            const suggestions = [];

            const easy = paceZones?.summary?.easy ?? null;
            const tempo = paceZones?.summary?.tempo ?? null;
            const speed = paceZones?.summary?.speed ?? null;

            const z1 = hrZones?.Z1 ?? null;
            const z2 = hrZones?.Z2 ?? null;
            const z3 = hrZones?.Z3 ?? null;
            const z4 = hrZones?.Z4 ?? null;
            const z5 = hrZones?.Z5 ?? null;

            const total = metrics?.total_time_s || 0;
            const pause = metrics?.pause_time_s || 0;
            const pauseRatio = total ? pause / total : 0;

            if (easy !== null && easy >= 70) {
                analysis.push('Distribusi pace dominan Easy, fokus utama ada di base aerobik dan efisiensi.');
            } else if (speed !== null && speed >= 30) {
                analysis.push('Porsi speed cukup besar, sesi ini tergolong intens dan menstimulasi VO2Max/kecepatan.');
            } else if (tempo !== null && tempo >= 30) {
                analysis.push('Tempo cukup dominan, latihan mengarah ke penguatan threshold dan ketahanan pace.');
            } else if (easy !== null || tempo !== null || speed !== null) {
                analysis.push('Distribusi pace cukup seimbang, efeknya campuran antara aerobik dan kualitas.');
            }

            if (z1 !== null && z2 !== null && (z1 + z2) >= 70) {
                analysis.push('Mayoritas detak jantung berada di Z1–Z2, menunjukkan sesi aerobik atau recovery.');
            } else if (z4 !== null && z5 !== null && (z4 + z5) >= 30) {
                analysis.push('Zona detak jantung Z4–Z5 cukup tinggi, beban latihan berat dan menstimulasi adaptasi intensitas.');
            } else if (z3 !== null && z4 !== null && (z3 + z4) >= 40) {
                analysis.push('Banyak waktu di Z3–Z4, cocok untuk tempo/threshold dan peningkatan stamina pace.');
            }

            if (pauseRatio > 0.18) {
                analysis.push('Proporsi pause cukup besar, artinya banyak berhenti sehingga efek intensitas berkurang.');
            } else if (pauseRatio > 0.08) {
                analysis.push('Terdapat pause moderat, kemungkinan karena interval atau kondisi rute.');
            }

            if (speed !== null && speed >= 25) {
                effects.push('VO2Max & kecepatan');
            } else if (tempo !== null && tempo >= 25) {
                effects.push('Threshold & tempo endurance');
            } else if (easy !== null && easy >= 60) {
                effects.push('Base aerobik & recovery');
            } else if (z4 !== null && z5 !== null && (z4 + z5) >= 25) {
                effects.push('Kualitas intensitas tinggi');
            } else if (z1 !== null && z2 !== null && (z1 + z2) >= 60) {
                effects.push('Aerobic maintenance');
            } else {
                effects.push('Mixed aerobic & quality');
            }

            if (speed !== null && speed >= 30) {
                suggestions.push('Prioritaskan recovery run 24–48 jam ke depan agar adaptasi optimal.');
            } else if (tempo !== null && tempo >= 30) {
                suggestions.push('Pertahankan volume easy berikutnya, lalu sisipkan interval ringan jika tubuh segar.');
            } else if (easy !== null && easy >= 70) {
                suggestions.push('Kondisi cocok untuk sesi kualitas berikutnya (tempo/interval) jika rencana mengizinkan.');
            } else {
                suggestions.push('Jaga keseimbangan easy dan kualitas untuk mencegah overtraining.');
            }

            if (pauseRatio > 0.18) {
                suggestions.push('Jika targetnya steady run, kurangi pause agar stimulus lebih konsisten.');
            }

            return {
                analysis: analysis.join(' '),
                effect: effects.join(' • '),
                suggestion: suggestions.join(' ')
            };
        };

        const renderChartToCanvas = (streams, canvasId) => {
            if (!streams || !streams.time || !window.Chart) return null;
            const canvas = document.getElementById(canvasId);
            if (!canvas) return null;

            const time = Array.isArray(streams.time) ? streams.time : [];
            const hr = Array.isArray(streams.heartrate) ? streams.heartrate : [];
            const cad = Array.isArray(streams.cadence) ? streams.cadence : [];
            const vel = Array.isArray(streams.velocity_smooth) ? streams.velocity_smooth : [];
            const watts = Array.isArray(streams.watts) ? streams.watts : [];

            const n = time.length;
            if (n === 0) return null;

            const maxPoints = 320;
            const step = n > maxPoints ? Math.ceil(n / maxPoints) : 1;

            const labels = [];
            const pace = [];
            const hrS = [];
            const cadS = [];
            const wattsS = [];

            for (let i = 0; i < n; i += step) {
                labels.push(formatSeconds(time[i]));
                pace.push(toPaceSecPerKm(vel[i]));
                hrS.push(typeof hr[i] === 'number' ? hr[i] : (hr[i] ? parseFloat(hr[i]) : null));
                cadS.push(typeof cad[i] === 'number' ? cad[i] : (cad[i] ? parseFloat(cad[i]) : null));
                wattsS.push(typeof watts[i] === 'number' ? watts[i] : (watts[i] ? parseFloat(watts[i]) : null));
            }

            const datasets = [
                {
                    label: 'Pace',
                    data: pace,
                    borderColor: '#06B6D4',
                    backgroundColor: 'rgba(6,182,212,0.08)',
                    yAxisID: 'yPace',
                    pointRadius: 0,
                    borderWidth: 2,
                    spanGaps: true,
                },
            ];

            if (hrS.some(v => v !== null && !Number.isNaN(v))) {
                datasets.push({
                    label: 'Heart Rate',
                    data: hrS,
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239,68,68,0.08)',
                    yAxisID: 'yMetric',
                    pointRadius: 0,
                    borderWidth: 1.5,
                    spanGaps: true,
                });
            }
            if (cadS.some(v => v !== null && !Number.isNaN(v))) {
                datasets.push({
                    label: 'Cadence',
                    data: cadS,
                    borderColor: '#A855F7',
                    backgroundColor: 'rgba(168,85,247,0.08)',
                    yAxisID: 'yMetric',
                    pointRadius: 0,
                    borderWidth: 1.5,
                    spanGaps: true,
                });
            }
            if (wattsS.some(v => v !== null && !Number.isNaN(v))) {
                datasets.push({
                    label: 'Power',
                    data: wattsS,
                    borderColor: '#22C55E',
                    backgroundColor: 'rgba(34,197,94,0.08)',
                    yAxisID: 'yMetric',
                    pointRadius: 0,
                    borderWidth: 1.5,
                    spanGaps: true,
                });
            }

            return new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            labels: { color: '#CBD5E1', boxWidth: 10 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const ds = context.dataset;
                                    const val = context.parsed.y;
                                    if (ds.label === 'Pace') return ` Pace: ${formatPaceFromSec(val)} /km`;
                                    if (ds.label === 'Heart Rate') return ` HR: ${Math.round(val)} bpm`;
                                    if (ds.label === 'Cadence') return ` Cadence: ${Math.round(val)} spm`;
                                    if (ds.label === 'Power') return ` Power: ${Math.round(val)} w`;
                                    return ` ${ds.label}: ${val}`;
                                }
                            }
                        }
                    },
                    elements: { line: { tension: 0.25 } },
                    scales: {
                        x: {
                            ticks: { color: '#64748B', maxTicksLimit: 6 },
                            grid: { color: 'rgba(51,65,85,0.35)' }
                        },
                        yPace: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            reverse: true,
                            ticks: {
                                color: '#94A3B8',
                                callback: function (v) { return formatPaceFromSec(v); }
                            },
                            grid: { color: 'rgba(51,65,85,0.35)' }
                        },
                        yMetric: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            ticks: { color: '#94A3B8' },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        };

        const renderStravaChart = (streams) => {
            destroyStravaChart();
            stravaChart = renderChartToCanvas(streams, 'stravaMetricsChart');
        };

        const loadStravaStreams = async (activityId) => {
            const id = parseInt(activityId || 0, 10);
            if (!id) return;

            stravaStreamsLoading.value = true;
            stravaStreamsError.value = '';
            detail.strava_streams = null;
            destroyStravaChart();

            try {
                const res = await fetch(`${runnerUrl}/strava/activities/${id}/streams`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (res.ok && data && data.success) {
                    detail.strava_streams = data.streams || null;
                    setTimeout(() => renderStravaChart(detail.strava_streams), 50);
                    detail.strava_pace_zones = buildPaceZones(detail.strava_streams, trainingProfile.value?.paces);
                    detail.strava_hr_zones = buildHrZones(detail.strava_streams, detail.strava_metrics?.max_heartrate);
                    const zoneInsight = buildZoneAnalysis(detail.strava_pace_zones, detail.strava_hr_zones, detail.strava_metrics);
                    detail.strava_zone_analysis = zoneInsight?.analysis || null;
                    detail.strava_zone_effect = zoneInsight?.effect || null;
                    detail.strava_zone_suggestion = zoneInsight?.suggestion || null;
                } else {
                    stravaStreamsError.value = (data && data.message) ? data.message : 'Gagal mengambil streams Strava.';
                }
            } catch (e) {
                stravaStreamsError.value = 'Gagal mengambil streams Strava.';
            } finally {
                stravaStreamsLoading.value = false;
            }
        };

        const generateStravaAnalysis = (metrics) => {
            if (!metrics) return { analysis: 'Data tidak cukup untuk analisis.', suggestion: 'Lanjutkan latihan sesuai rencana.' };

            const avgHr = metrics.average_heartrate;
            const dist = metrics.distance_m ? metrics.distance_m / 1000 : 0;
            const pace = metrics.pace; // string "5:30"
            
            let analysis = [];
            let suggestion = "";

            // Intensity Analysis based on HR (Simple heuristic)
            let intensity = 'moderate';
            if (avgHr) {
                if (avgHr < 140) {
                    analysis.push("Lari ini berada di zona aerobik ringan, bagus untuk membangun base endurance tanpa kelelahan berlebih.");
                    intensity = 'easy';
                } else if (avgHr >= 140 && avgHr < 160) {
                    analysis.push("Usaha yang solid di zona aerobik/steady. Jantung bekerja efisien.");
                    intensity = 'moderate';
                } else {
                    analysis.push("Intensitas tinggi terdeteksi. Latihan ini melatih ambang laktat dan VO2Max.");
                    intensity = 'hard';
                }
            } else {
                analysis.push("Data detak jantung tidak tersedia, namun berdasarkan pace, usaha terlihat konsisten.");
            }

            // Distance Context
            if (dist > 15) {
                analysis.push("Long run yang hebat! Ketahanan otot sedang diuji.");
                intensity = 'hard'; // Long runs are hard on the body
            } else if (dist < 5) {
                analysis.push("Lari jarak pendek yang baik untuk recovery atau speed work.");
            }

            // Suggestion
            if (intensity === 'hard') {
                suggestion = "Tubuh Anda butuh pemulihan. Saran: Besok ambil Rest Day atau Recovery Run santai (30-45 menit Zone 1-2). Fokus pada hidrasi dan tidur.";
            } else if (intensity === 'moderate') {
                suggestion = "Kondisi masih oke. Next workout bisa berupa Easy Run atau Cross Training ringan.";
            } else {
                suggestion = "Anda masih segar. Next workout siap untuk sesi kualitas (Interval/Tempo) atau Long Run jika jadwal memungkinkan.";
            }

            return {
                analysis: analysis.join(' '),
                suggestion: suggestion
            };
        };

        const loadAiWorkoutAnalysis = async (activityId, force = false) => {
            const id = parseInt(activityId || 0, 10);
            if (!id) return;

            aiAnalysisLoading.value = true;
            aiAnalysisError.value = '';

            try {
                const url = new URL(`${runnerUrl}/strava/activities/${id}/ai-analysis`, window.location.origin);
                if (force) {
                    url.searchParams.set('force', '1');
                }

                const res = await fetch(url.toString(), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (res.ok && data && data.success) {
                    detail.ai_analysis = data.analysis || null;

                    if (detail.ai_analysis?.summary) {
                        detail.analysis = detail.ai_analysis.summary;
                    }

                    const nextWorkout = detail.ai_analysis?.next_workout_suggestion;
                    if (nextWorkout?.reason) {
                        const target = nextWorkout?.target ? ` Target ${nextWorkout.target}.` : '';
                        const duration = nextWorkout?.duration ? ` ${nextWorkout.duration}.` : '';
                        detail.suggestion = `${nextWorkout.reason}${duration}${target}`.trim();
                    }
                } else {
                    aiAnalysisError.value = (data && data.message) ? data.message : 'Gagal mengambil analisis AI.';
                }
            } catch (e) {
                aiAnalysisError.value = 'Gagal mengambil analisis AI.';
            } finally {
                aiAnalysisLoading.value = false;
            }
        };

        const loadStravaDetails = async (activityId) => {
            const id = parseInt(activityId || 0, 10);
            if (!id) return;

            stravaDetailsLoading.value = true;
            stravaDetailsError.value = '';
            detail.strava_metrics = null;
            detail.strava_splits = [];
            detail.strava_laps = [];
            detail.actual_pace = null;
            detail.strava_streams = null;
            detail.strava_pace_zones = null;
            detail.strava_hr_zones = null;
            detail.strava_media = [];
            detail.strava_zone_analysis = null;
            detail.strava_zone_effect = null;
            detail.strava_zone_suggestion = null;
            detail.analysis = null;
            detail.suggestion = null;
            detail.ai_analysis = null;
            detail.strava_activity_id = id;
            aiAnalysisError.value = '';
            stravaStreamsError.value = '';
            destroyStravaChart();

            try {
                const res = await fetch(`${runnerUrl}/strava/activities/${id}/details`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (res.ok && data && data.success) {
                    const a = data.activity || {};
                    detail.strava_metrics = a;
                    detail.actual_pace = a.pace ? `${a.pace} /km` : null;
                    detail.strava_splits = Array.isArray(a.splits_metric) ? a.splits_metric : [];
                    detail.strava_laps = Array.isArray(a.laps) ? a.laps : [];
                    detail.strava_media = Array.isArray(a.media) ? a.media : [];
                    
                    // Generate Analysis
                    const ana = generateStravaAnalysis(a);
                    detail.analysis = ana.analysis;
                    detail.suggestion = ana.suggestion;

                    loadStravaStreams(id);
                    loadAiWorkoutAnalysis(id);
                } else {
                    stravaDetailsError.value = (data && data.message) ? data.message : 'Gagal mengambil detail Strava.';
                }
            } catch (e) {
                stravaDetailsError.value = 'Gagal mengambil detail Strava.';
            } finally {
                stravaDetailsLoading.value = false;
            }
        };

        const setFilter = async (f) => {
            filter.value = f;
            await loadPlans();
        };

        const loadPlans = async () => {
            plansLoading.value = true;
            try {
                const res = await fetch(`{{ route('runner.calendar.workout-plans') }}?filter=${filter.value}`, { headers: { 'Accept':'application/json' }});
                const data = await res.json();
                plans.value = Array.isArray(data) ? data : [];
                visibleCount.value = pageSize;
            } catch (e) {
                plans.value = [];
            } finally {
                plansLoading.value = false;
            }
        };

        const loadWeeklyVolume = async () => {
            try {
                const res = await fetch(`{{ route('runner.calendar.weekly-volume') }}`, { headers: { 'Accept':'application/json' }});
                const data = await res.json();
                weeklyVolume.value = Array.isArray(data) ? data : [];
            } catch (e) {
                weeklyVolume.value = [];
            }
        };

        const handleEventDrop = async (info) => {
            if (!confirm(`Reschedule ${info.event.title} to ${info.event.startStr}?`)) {
                info.revert();
                return;
            }
            
            const props = info.event.extendedProps;
            const payload = {
                type: props.type,
                new_date: info.event.startStr,
            };
            
            if (props.type === 'custom_workout') {
                payload.workout_id = props.workout_id;
            } else {
                payload.enrollment_id = props.enrollment_id;
                payload.session_day = props.session.day;
            }
            
            try {
                const res = await fetch(`{{ route('runner.calendar.reschedule') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    if (data.message && data.message.includes('ditukar')) {
                         alert(data.message);
                    }
                    // Update plans list as well
                    await loadPlans();
                    await loadWeeklyVolume();
                } else {
                    alert(data.message || 'Failed to reschedule');
                    info.revert();
                }
            } catch (e) {
                alert('An error occurred');
                info.revert();
            }
        };

        const initCalendar = () => {
            const el = document.getElementById('calendar');
            if (!el) {
                console.error('[RunnerCalendar] Calendar element not found');
                return; // Guard against null element
            }
            
            const isMobile = window.innerWidth < 768;
            const initialView = isMobile ? 'listWeek' : 'dayGridMonth';
            const headerToolbar = isMobile 
                ? { left: 'prev,next', center: 'title', right: 'listWeek' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' };

            calendar = new FullCalendar.Calendar(el, {
                initialView: initialView,
                headerToolbar: headerToolbar,
                events: '{{ route("runner.calendar.events") }}',
                locale: 'id',
                firstDay: 1,
                editable: true, // Enable drag & drop
                eventDrop: handleEventDrop, // Handle drop
                eventClassNames: (arg) => {
                    const cls = [];
                    const props = arg.event.extendedProps || {};
                    if (props.difficulty) cls.push('difficulty-' + props.difficulty);
                    if (props.phase) cls.push('phase-' + props.phase);
                    const t = (props.session && props.session.type) || (props.workout && props.workout.type) || props.activity_type || props.type;
                    if (t) cls.push('workout-' + t);
                    return cls;
                },
                eventContent: (arg) => {
                    const props = arg.event.extendedProps || {};
                    const isStrava = props.type === 'strava_activity' || props.event_type === 'strava_activity' || props.is_strava;
                    
                    const container = document.createElement('div');
                    container.style.display = 'flex';
                    container.style.alignItems = 'center';
                    container.style.gap = '4px';
                    container.style.overflow = 'hidden';
                    container.style.textOverflow = 'ellipsis';
                    container.style.whiteSpace = 'nowrap';
                    container.style.width = '100%';

                    if (!arg.event.allDay && arg.timeText) {
                        const timeEl = document.createElement('span');
                        timeEl.className = 'fc-event-time';
                        timeEl.style.fontWeight = 'bold';
                        timeEl.style.marginRight = '2px';
                        timeEl.style.flexShrink = '0';
                        timeEl.innerText = arg.timeText;
                        container.appendChild(timeEl);
                    }

                    if (isStrava) {
                        const svgSpan = document.createElement('span');
                        svgSpan.style.display = 'inline-flex';
                        svgSpan.style.alignItems = 'center';
                        svgSpan.style.flexShrink = '0';
                        svgSpan.innerHTML = `
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="#FC4C02">
                                <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-3.756l3.52 6.948h4.636L11.529 0 4 14.808h4.637l2.871-5.62z"/>
                            </svg>
                        `;
                        container.appendChild(svgSpan);
                    }

                    const titleEl = document.createElement('span');
                    titleEl.className = 'fc-event-title';
                    titleEl.innerText = arg.event.title;
                    container.appendChild(titleEl);

                    return { domNodes: [container] };
                },
                dateClick: (info) => { openForm(info.dateStr); },
                eventClick: (info) => { showEventDetail(info); info.jsEvent.preventDefault(); },
                height: 'auto',
                listDayFormat: { month: 'short', day: 'numeric', weekday: 'short' }, // For list view header
                listDaySideFormat: false // Hide the side text
            });
            calendar.render();
        };

        const openForm = (dateStr) => {
            form.workout_id = '';
            form.workout_date = dateStr;
            form.type = 'run';
            form.difficulty = 'moderate';
            form.distance = '';
            form.duration = '';
            form.description = '';
            form.workout_structure = [];
            showVdotModal.value = false;
            showRaceModal.value = false;
            showFormModal.value = true;
        };

        const openFormForToday = async () => { 
            try {
                console.log('[RunnerCalendar] openFormForToday');
                // ensure other modals are closed first
                showDetailModal.value = false;
                showVdotModal.value = false;
                showRaceModal.value = false;
                showFormModal.value = false;
                await nextTick();

                const d = new Date();
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth()+1).padStart(2,'0');
                const dd = String(d.getDate()).padStart(2,'0');
                openForm(`${yyyy}-${mm}-${dd}`);
            } catch (e) {
                console.error('[RunnerCalendar] openFormForToday failed', e);
            }
        };

        const closeForm = () => { showFormModal.value = false; };

        // Race helpers
        const setRaceDist = (val, label) => {
            raceForm.distance = val;
            raceForm.distLabel = label;
        };

        const openRaceForm = async () => {
            try {
                console.log('[RunnerCalendar] openRaceForm');
                raceForm.name = '';
                raceForm.date = new Date().toISOString().slice(0,10);
                raceForm.distance = '10';
                raceForm.distLabel = '10K';
                raceForm.goal_time = '';
                raceForm.notes = '';

                // ensure other modals are closed first
                showDetailModal.value = false;
                showVdotModal.value = false;
                showFormModal.value = false;
                showRaceModal.value = false;
                await nextTick();

                showRaceModal.value = true;
            } catch (e) {
                console.error('[RunnerCalendar] openRaceForm failed', e);
                // fallback
                showRaceModal.value = true;
            }
        };
        const saveRace = async () => {
            try {
                const payload = {
                    workout_date: raceForm.date,
                    type: 'race',
                    difficulty: 'hard',
                    distance: raceForm.distance || null,
                    description: raceForm.notes || null,
                    workout_structure: {
                        race_name: raceForm.name,
                        goal_time: raceForm.goal_time,
                        dist_label: raceForm.distLabel
                    }
                };
                const res = await fetch(`{{ route('runner.calendar.custom-workout.store') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showRaceModal.value = false;
                    if (calendar) calendar.refetchEvents();
                    await loadPlans();
                } else {
                    alert(data.message || 'Failed to save race');
                }
            } catch (e) {
                alert('An error occurred while saving race');
            }
        };

        const saveCustomWorkout = async () => {
            const payload = {
                workout_id: form.workout_id || '',
                workout_date: form.workout_date,
                type: form.type,
                difficulty: form.difficulty,
                distance: form.distance || null,
                duration: form.duration || null,
                description: form.description || null,
                workout_structure: form.workout_structure.length > 0 ? form.workout_structure : null,
            };
            try {
                const res = await fetch(`{{ route('runner.calendar.custom-workout.store') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showFormModal.value = false;
                    if (calendar) calendar.refetchEvents();
                    await loadPlans();
                }
            } catch {}
        };

        // Helper to calculate recommended pace
        const calculateRecommendedPace = (type, distance = null, title = '', description = '') => {
            if (!type) return null;
            const t = type.toLowerCase();
            const map = { 
                easy_run: 'E', recovery: 'E', run: 'E', 
                long_run: 'M', 
                tempo: 'T', threshold: 'T', 
                interval: 'I', vo2max: 'I',
                repetition: 'R', speed: 'R',
                strength: null, rest: null, yoga: null, cycling: null
            };
            
            let key = map[t]; 
            if (!key) return null;

            const combined = ((title || '') + ' ' + (description || '')).toLowerCase();

            // Logic override: If Interval (I) and matches short distance patterns (e.g. 100m, 200m, 400m, 800m)
            if (key === 'I') {
                let isShort = false;
                if (distance && parseFloat(distance) <= 0.805) {
                    isShort = true;
                } else if (/\b(55|50|100|200|300|400|500|600|800)\s*m\b/i.test(combined)) {
                    isShort = true;
                } else if (/\b0\.[1-8]\s*km\b/i.test(combined)) {
                    isShort = true;
                }

                if (isShort) {
                    key = 'R';
                }
            }

            // Determine lookup distance for track times tab
            let lookupDistance = distance;
            if (['I', 'R', 'T'].includes(key)) {
                const distKm = distance ? parseFloat(distance) : null;
                if (!distKm || distKm > 2.0) {
                    const matchMeters = combined.match(/\b(55|50|100|200|300|400|500|600|800)\s*m\b/i);
                    if (matchMeters) {
                        lookupDistance = parseFloat(matchMeters[1]) / 1000;
                    } else {
                        const matchKm = combined.match(/\b(0\.[1-8])\s*km\b/i);
                        if (matchKm) {
                            lookupDistance = parseFloat(matchKm[1]);
                        }
                    }
                }
            }

            // Check for specific Track times (Interval, Repetition, Threshold)
            // User requested specific logic for 0.1-2km to take from track tab
            if (['I', 'R', 'T'].includes(key) && lookupDistance && trainingProfile.value?.track_times) {
                const distKm = parseFloat(lookupDistance);
                // Check if distance is between 0.1 and 2.0 km (approx)
                if (distKm >= 0.1 && distKm <= 2.0) {
                    // Try to match standard track distances
                    const m = Math.round(distKm * 1000);
                    
                    // We check if exact key exists
                    const trackKey = m + 'm';
                    
                    if (trainingProfile.value.track_times[trackKey]) {
                        const trackData = trainingProfile.value.track_times[trackKey];
                        // Get the specific pace type from trackData (I, R, or T)
                        const splitTime = trackData[key]; 
                        const pacePerKm = trackData['pace_' + key];
                        
                        if (splitTime) {
                            return `${splitTime} (${pacePerKm} /km)`;
                        }
                    }
                }
            }

            let val = trainingProfile.value?.paces?.[key];
            if (!val && key === 'M') val = trainingProfile.value?.paces?.['E'];
            
            return val ? (formatPace(val) + ' /km') : null;
        };

        const guidedStepChecks = ref({});
        const getGuidedStorageKey = () => {
            const base =
                (detail.session && detail.session.id ? `session:${detail.session.id}` : null) ||
                (detail.workout_id ? `workout:${detail.workout_id}` : null) ||
                (detail.strava_activity_id ? `strava:${detail.strava_activity_id}` : null) ||
                `date:${detail.date || ''}|title:${detailTitle.value || ''}`;
            return `runner_guided_steps:${base}`;
        };

        const loadGuidedStepChecks = () => {
            try {
                const key = getGuidedStorageKey();
                const raw = localStorage.getItem(key);
                if (!raw) {
                    guidedStepChecks.value = {};
                    return;
                }
                const parsed = JSON.parse(raw);
                guidedStepChecks.value = parsed && typeof parsed === 'object' ? parsed : {};
            } catch (e) {
                guidedStepChecks.value = {};
            }
        };

        const saveGuidedStepChecks = () => {
            try {
                const key = getGuidedStorageKey();
                localStorage.setItem(key, JSON.stringify(guidedStepChecks.value || {}));
            } catch (e) {
            }
        };

        const normalizeTextLine = (line) => {
            try {
                return String(line || '')
                    .replace(/\r/g, '')
                    .replace(/^\s*[-*•]+\s*/, '')
                    .trim();
            } catch (e) {
                return '';
            }
        };

        const stepBadgeMeta = (type) => {
            const t = String(type || '').toLowerCase();
            const label = t.replace('_', ' ') || 'step';
            const map = {
                warmup: 'bg-green-500/10 border-green-500/30 text-green-300',
                run: 'bg-blue-500/10 border-blue-500/30 text-blue-300',
                interval: 'bg-orange-500/10 border-orange-500/30 text-orange-300',
                repetition: 'bg-pink-500/10 border-pink-500/30 text-pink-300',
                recovery: 'bg-yellow-500/10 border-yellow-500/30 text-yellow-300',
                cool_down: 'bg-purple-500/10 border-purple-500/30 text-purple-300',
            };
            return {
                badge: label.toUpperCase(),
                badgeClass: map[t] || 'bg-slate-900/60 border-slate-700 text-slate-300',
            };
        };

        const paceTypeForStep = (stepType) => {
            const t = String(stepType || '').toLowerCase();
            if (t === 'warmup' || t === 'recovery' || t === 'cool_down') return 'easy_run';
            if (t === 'interval') return 'interval';
            if (t === 'repetition') return 'repetition';
            if (t === 'run') {
                if (['tempo', 'threshold'].includes(String(detail.type || '').toLowerCase())) return 'tempo';
                if (['interval', 'repetition'].includes(String(detail.type || '').toLowerCase())) return String(detail.type || 'run').toLowerCase();
                if (String(detail.type || '').toLowerCase() === 'long_run') return 'long_run';
                return 'run';
            }
            return null;
        };

        const paceTextForStep = (step) => {
            try {
                const type = paceTypeForStep(step.type);
                if (!type) return null;
                let distKm = null;
                const valueNum = parseFloat(step.value);
                if (!isNaN(valueNum)) {
                    const unit = String(step.unit || '').toLowerCase();
                    if (unit === 'km') distKm = valueNum;
                    if (unit === 'm') distKm = valueNum / 1000;
                }
                return calculateRecommendedPace(type, distKm);
            } catch (e) {
                return null;
            }
        };

        const guidedSteps = computed(() => {
            const steps = [];
            if (detail.workout_structure && Array.isArray(detail.workout_structure) && detail.workout_structure.length > 0) {
                detail.workout_structure.forEach((s, idx) => {
                    const meta = stepBadgeMeta(s.type);
                    const valueText = (s.value !== null && s.value !== undefined && String(s.value) !== '')
                        ? `${s.value} ${s.unit || ''}`.trim()
                        : null;
                    const paceText = paceTextForStep(s);
                    steps.push({
                        id: `ws:${idx}:${s.type || 'step'}:${valueText || ''}`,
                        title: (String(s.type || 'Step').replace('_', ' ')).replace(/\b\w/g, m => m.toUpperCase()),
                        subtitle: s.notes ? String(s.notes) : null,
                        valueText,
                        paceText,
                        badge: meta.badge,
                        badgeClass: meta.badgeClass,
                    });
                });
                return steps;
            }

            const lines = String(detail.description || '').split('\n').map(normalizeTextLine).filter(Boolean);
            lines.slice(0, 12).forEach((line, idx) => {
                steps.push({
                    id: `d:${idx}`,
                    title: line,
                    subtitle: null,
                    valueText: null,
                    paceText: null,
                    badge: 'NOTE',
                    badgeClass: 'bg-slate-900/60 border-slate-700 text-slate-300',
                });
            });
            return steps;
        });

        const guidedStepsDoneCount = computed(() => {
            try {
                const checks = guidedStepChecks.value || {};
                return guidedSteps.value.reduce((acc, s) => acc + (checks[s.id] ? 1 : 0), 0);
            } catch (e) {
                return 0;
            }
        });

        const guidedStepsProgressPct = computed(() => {
            const total = guidedSteps.value.length || 0;
            if (!total) return 0;
            return Math.round((guidedStepsDoneCount.value / total) * 100);
        });

        const guidedStepChecked = (step) => {
            try {
                return !!(guidedStepChecks.value && guidedStepChecks.value[step.id]);
            } catch (e) {
                return false;
            }
        };

        const toggleGuidedStep = (step) => {
            if (!step || !step.id) return;
            const next = { ...(guidedStepChecks.value || {}) };
            next[step.id] = !next[step.id];
            guidedStepChecks.value = next;
            saveGuidedStepChecks();
        };

        watch(showDetailModal, (val) => {
            if (val) loadGuidedStepChecks();
        });

        watch(() => [detail.session?.id, detail.workout_id, detail.strava_activity_id, detail.date, detailTitle.value], () => {
            if (showDetailModal.value) loadGuidedStepChecks();
        });

        const aiCoachSummary = computed(() => {
            const t = String(detail.type || '').toLowerCase();
            const focusMap = {
                easy_run: 'Fokusnya easy aerobic + recovery. Bikin napas stabil dan kaki terasa ringan.',
                recovery: 'Fokusnya recovery. Jaga effort super easy supaya badan pulih.',
                long_run: 'Fokusnya endurance. Jaga ritme stabil dari awal sampai akhir.',
                tempo: 'Fokusnya threshold/tempo. Cari effort “comfortably hard”, bukan all-out.',
                interval: 'Fokusnya speed/VO2. Kualitas penting, tapi tetap kontrol.',
                repetition: 'Fokusnya speed + teknik. Cepat tapi rapi, recovery cukup.',
                run: 'Fokusnya konsistensi. Ikuti panduan pace dan rasakan effort yang pas.',
                program_session: 'Fokusnya eksekusi rapi sesuai program.',
                rest: 'Ini hari rest. Recovery juga bagian dari progres.',
                yoga: 'Fokusnya mobilitas dan recovery.',
                cycling: 'Fokusnya aerobic tanpa impact besar di kaki.',
                strength: 'Fokusnya strength dan stabilitas. Jaga form, bukan buru-buru.',
                race: 'Fokusnya eksekusi hari H. Mulai terkontrol, finish kuat.',
            };
            const base = focusMap[t] || 'Fokusnya eksekusi rapi dan konsisten.';
            const pace = detail.target_pace || detail.recommended_pace || null;
            const extra = pace ? ` Pace panduan: ${pace}.` : '';
            const interactive = guidedSteps.value.length ? ' Centang step saat selesai biar progresnya terasa.' : '';
            return `${base}${extra}${interactive}`;
        });

        const aiCoachCues = computed(() => {
            const t = String(detail.type || '').toLowerCase();
            const cues = {
                easy_run: [
                    'Kalau ragu pace, pilih yang lebih pelan. Harus bisa ngobrol.',
                    'Jaga cadence nyaman, langkah pendek, bahu rileks.',
                    'Selesai latihan: 3–5 menit jalan + minum.',
                ],
                recovery: [
                    'Tujuan utamanya pulih, bukan mengejar angka.',
                    'Kalau ada pegal tajam, stop dan ganti jalan.',
                    'Pilih rute datar biar effort stabil.',
                ],
                long_run: [
                    'Mulai pelan 10–15 menit pertama, baru stabil.',
                    'Minum sedikit-sedikit; kalau >75 menit, pertimbangkan gel.',
                    'Jangan ngegas di tengah; finish kuat lebih penting.',
                ],
                tempo: [
                    'Effort harus stabil; kalau makin cepat tiap km, turunkan sedikit pace.',
                    'Fokus napas ritmis dan postur tegak.',
                    'Kalau mulai “meledak”, tambah recovery singkat dan lanjutkan.',
                ],
                interval: [
                    'Rep 1–2 terkontrol, kualitas dijaga sampai rep terakhir.',
                    'Recovery itu bagian workout—jog pelan saja.',
                    'Prioritas teknik: badan tegak, langkah cepat tapi ringan.',
                ],
                repetition: [
                    'Cepat tapi rileks; stop kalau form mulai berantakan.',
                    'Recovery cukup supaya rep berikutnya tetap berkualitas.',
                    'Fokus dorongan kaki dan ayunan tangan rapi.',
                ],
                strength: [
                    'Utamakan range of motion dan form.',
                    'Kalau ragu beban, turunkan beban tapi gerakan rapi.',
                    'Istirahat 60–120 detik di set berat.',
                ],
                rest: [
                    'Jalan santai 10–20 menit membantu recovery.',
                    'Tidur cukup dan hidrasi jadi prioritas.',
                    'Stretch ringan kalau terasa kaku.',
                ],
            };
            return cues[t] || [
                'Mulai terkontrol, stabilkan napas.',
                'Ikuti pace panduan dan fokus ke teknik.',
                'Catat RPE/feeling setelah selesai untuk feedback ke coach.',
            ];
        });

        const workoutGoalText = computed(() => {
            const t = String(detail.type || '').toLowerCase();
            const goals = {
                easy_run: 'Membangun basis aerobik, melatih jantung bekerja efisien pada intensitas rendah, dan melancarkan aliran darah untuk pemulihan otot.',
                recovery: 'Mempercepat pemulihan tubuh dengan meningkatkan sirkulasi darah tanpa menambah stres atau kerusakan serat otot baru.',
                run: 'Memelihara kebugaran aerobik umum dan memperkuat konsistensi volume mingguan.',
                long_run: 'Meningkatkan daya tahan kardiovaskular dan muskular (endurance), serta melatih tubuh agar lebih efisien menggunakan lemak sebagai bahan bakar.',
                tempo: 'Meningkatkan ambang batas laktat (lactate threshold) agar Anda dapat berlari lebih cepat dengan akumulasi asam laktat yang lebih minim.',
                threshold: 'Meningkatkan ambang batas laktat (lactate threshold) agar Anda dapat berlari lebih cepat dengan akumulasi asam laktat yang lebih minim.',
                interval: 'Meningkatkan kapasitas VO2Max (penyerapan oksigen maksimum), toleransi asam laktat tinggi, dan daya dorong jantung.',
                repetition: 'Meningkatkan kecepatan murni, koordinasi saraf-otot (neuromuscular), dan efisiensi biomekanika langkah lari (running economy).',
                speed: 'Meningkatkan kecepatan murni, koordinasi saraf-otot (neuromuscular), dan efisiensi biomekanika langkah lari (running economy).',
                strength: 'Memperkuat otot-otot pendukung (core, glutes, hamstrings) untuk meningkatkan stabilitas lari dan mengurangi risiko cedera.',
                rest: 'Memberikan waktu istirahat total bagi serat otot untuk memperbaiki diri dan memulihkan cadangan glikogen tubuh.',
                yoga: 'Meningkatkan fleksibilitas otot, mobilitas sendi, serta melatih kesadaran napas dan ketenangan pikiran.',
                cycling: 'Membangun kapasitas aerobik alternatif (cross-training) tanpa memberikan benturan fisik (impact) pada sendi kaki.',
                race: 'Menguji performa terbaik Anda, menerapkan strategi paceline yang matang, dan menyelesaikan target waktu perlombaan.'
            };
            return goals[t] || 'Membangun kebugaran fisik dan memelihara konsistensi latihan.';
        });

        const workoutEffectText = computed(() => {
            const t = String(detail.type || '').toLowerCase();
            const hints = {
                easy_run: 'Memperbanyak jumlah kapiler darah dan mitokondria pada sel otot, sehingga otot menjadi lebih andal menyerap oksigen.',
                recovery: 'Membuang sisa metabolisme (seperti asam laktat) lebih cepat, meredakan kekakuan otot, dan menurunkan denyut jantung istirahat.',
                run: 'Menjaga keaktifan kapiler darah dan metabolisme aerobik tanpa membebani sistem saraf pusat.',
                long_run: 'Meningkatkan kapasitas penyimpanan glikogen di otot, memperkuat tendon, ligamen, serta otot kaki menghadapi kelelahan jangka panjang.',
                tempo: 'Meningkatkan kemampuan sel otot untuk mendaur ulang asam laktat kembali menjadi energi, menunda sensasi "kaki terbakar" saat pace cepat.',
                threshold: 'Meningkatkan kemampuan sel otot untuk mendaur ulang asam laktat kembali menjadi energi, menunda sensasi "kaki terbakar" saat pace cepat.',
                interval: 'Memperbesar volume sekuncup jantung (stroke volume), mempercepat pemulihan denyut jantung (HR recovery), dan merangsang serat otot cepat.',
                repetition: 'Langkah lari terasa lebih ringan dan rileks pada kecepatan tinggi karena refleks otot-saraf menjadi lebih terlatih dan hemat energi.',
                speed: 'Langkah lari terasa lebih ringan dan rileks pada kecepatan tinggi karena refleks otot-saraf menjadi lebih terlatih dan hemat energi.',
                strength: 'Meningkatkan kekuatan rekrutmen serat otot, memperbaiki postur berlari agar tegak di kilometer akhir, dan memperkokoh persendian.',
                rest: 'Terjadinya proses superkompensasi di mana otot pulih lebih kuat dibanding kondisi sebelum dirusak oleh latihan berat.',
                yoga: 'Meregangkan otot yang kaku, meredakan ketegangan sistem saraf (simpatik), dan membantu detoksifikasi tubuh.',
                cycling: 'Merangsang sirkulasi jantung tanpa beban impak berat, membantu regenerasi sendi lutut dan pergelangan kaki.',
                race: 'Memberikan stimulus adaptasi maksimal bagi seluruh sistem energi tubuh, sekaligus memperkuat ketangguhan mental pelari.'
            };
            return hints[t] || 'Meningkatkan kesegaran jasmani dan kesiapan adaptasi tubuh untuk sesi latihan berikutnya.';
        });

        const showEventDetail = (info) => {
            resetDetail();
            
            showVdotModal.value = false;
            showFormModal.value = false;
            showRaceModal.value = false;
            showStravaAnalysisModal.value = false;
            
            const props = info.event.extendedProps || {};
            const type = props.type || null;

            if (type === 'program_session') {
                const s = props.session || {};
                detail.session = s;
                detailTitle.value = props.program_title || 'Program Session';
                detail.date = info.event.startStr;
                detail.type = s.type || 'run';
                detail.distance = s.distance || null;
                detail.duration = s.duration || null;
                detail.program_difficulty = props.difficulty || null;
                detail.description = s.description || null;
                detail.status = s.status || 'pending';
                detail.enrollment_id = props.enrollment_id;
                detail.session_day = s.day;
                detail.target_pace = props.target_pace || null;
                detail.strength = s.strength || null;
                detail.source = 'program';
                
                if (['strength', 'rest', 'yoga', 'cycling'].includes(detail.type)) {
                    detail.target_pace = null;
                }
                detail.recommended_pace = calculateRecommendedPace(detail.type, detail.distance, detailTitle.value, detail.description);
            } else if (type === 'custom_workout') {
                const w = props.workout || {};
                detailTitle.value = w.type === 'race' ? (w.workout_structure?.race_name || 'Race Event') : 'Custom Workout';
                detail.date = info.event.startStr;
                detail.type = w.type || 'run';
                detail.distance = w.distance || null;
                detail.duration = w.duration || null;
                detail.difficulty = w.difficulty || null;
                detail.description = w.description || null;
                detail.status = w.status || 'pending';
                detail.workout_id = w.id || props.workout_id || null;
                detail.workout_structure = w.workout_structure || null;
                detail.strength = w.strength || w.workout_structure?.strength || null;
                detail.source = 'custom';
                
                if (['strength', 'rest', 'yoga', 'cycling'].includes(detail.type)) {
                    detail.target_pace = null;
                }
                detail.recommended_pace = calculateRecommendedPace(detail.type, detail.distance, detailTitle.value, detail.description);
            } else if (type === 'strava_activity') {
                detailTitle.value = 'Strava Activity';
                detail.date = info.event.startStr;
                detail.type = props.activity_type || 'run';
                detail.distance = props.distance_km || null;
                detail.duration = props.moving_time_s ? Math.round(props.moving_time_s / 60) + ' min' : null;
                detail.status = 'imported';
                detail.source = 'strava';
                detail.description = props.name || null;

                stravaLinkInput.value = props.strava_url || '';
                notesInput.value = 'Imported from Strava sync';

                if (props.strava_activity_id) {
                    loadStravaDetails(props.strava_activity_id);
                }
            } else {
                // Fallback for "empty" or unknown types
                detail.type = props.activity_type || type || 'run';
                detail.date = info.event.startStr;
                detailTitle.value = info.event.title || 'Workout Event';
                detail.description = props.description || null;
                detail.workout_id = props.workout_id || null;
                detail.source = props.workout_id ? 'custom' : (props.enrollment_id ? 'program' : null);
            }
            
            showDetailModal.value = true;
        };

        const showPlanDetail = (plan) => {
            showStravaAnalysisModal.value = false;
            showVdotModal.value = false;
            showFormModal.value = false;
            showRaceModal.value = false;
            detail.session = plan.session || null;
            detailTitle.value = plan.program_title || 'Program Session';
            detail.date_formatted = plan.date_formatted || null;
            detail.type = plan.activity_type || plan.type; // Use activity_type for custom workouts if available
            detail.distance = plan.distance || null;
            detail.duration = plan.duration || null;
            detail.program_difficulty = plan.program_difficulty || null;
            detail.difficulty = plan.difficulty || null;
            detail.description = plan.description || null;
            detail.status = plan.status || 'pending';
            detail.enrollment_id = plan.enrollment_id;
            detail.session_day = plan.session_day;
            detail.strava_link = plan.strava_link || null;
            detail.notes = plan.notes || null;
            detail.workout_id = plan.workout_id || null;
            detail.workout_structure = plan.workout_structure || null;
            detail.source = plan.source || (plan.enrollment_id ? 'program' : null);
            detail.target_pace = plan.target_pace || null;
            
            // Hide target pace for non-running activities
            if (['strength', 'rest', 'yoga', 'cycling'].includes(detail.type)) {
                detail.target_pace = null;
            }

            detail.recommended_pace = calculateRecommendedPace(detail.type, detail.distance, detailTitle.value, detail.description);

            detail.coach_feedback = plan.coach_feedback || null;
            detail.coach_rating = plan.coach_rating || null;
            
            // Reset form
            stravaLinkInput.value = plan.strava_link || '';
            notesInput.value = plan.notes || '';
            rpeInput.value = plan.rpe || '';
            feelingInput.value = plan.feeling || '';

            stravaDetailsLoading.value = false;
            stravaDetailsError.value = '';
            detail.strava_metrics = null;
            detail.strava_splits = [];
            detail.strava_laps = [];
            detail.actual_pace = null;

            const stravaId = extractStravaActivityId(detail.strava_link || stravaLinkInput.value);
            if (stravaId) {
                loadStravaDetails(stravaId);
            }

            showDetailModal.value = true;
        };

        const closeDetail = () => {
            destroyStravaChart();
            try {
                resetTimer();
                showGuidedPlayer.value = false;
            } catch (e) {
            }
            showDetailModal.value = false;
        };

        const deleteCustomWorkout = async (workoutId) => {
            if (!workoutId) {
                alert('ID workout tidak ditemukan. Tidak dapat menghapus.');
                return;
            }

            if (!confirm('Hapus aktivitas kustom ini?')) return;
            
            try {
                const res = await fetch(`{{ url('/runner/calendar/custom-workout') }}/${workoutId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({ _method:'DELETE' })
                });
                const data = await res.json();
                if (data.success || data.ok) {
                    showDetailModal.value = false;
                    if (calendar) calendar.refetchEvents();
                    if (typeof loadPlans === 'function') await loadPlans();
                    if (typeof fetchPlans === 'function') await fetchPlans();
                } else {
                    alert(data.error || 'Gagal menghapus aktivitas');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan saat menghapus');
            }
        };

        const updateSessionStatus = async (plan, status, stravaLink = null, notes = null, rpe = null, feeling = null) => {
            console.log('updateSessionStatus plan:', plan);
            try {
                const payload = { status };
                
                // Enhanced logic for identifying custom vs program session
                if (plan.enrollment_id) {
                     payload.enrollment_id = plan.enrollment_id;
                     payload.session_day = plan.session_day;
                } else if (plan.type === 'custom_workout' || (plan.id && String(plan.id).startsWith('custom_')) || plan.workout_id) {
                    payload.workout_id = plan.workout_id || (plan.id ? String(plan.id).replace('custom_', '') : null);
                }

                if (stravaLink && stravaLink.trim()) payload.strava_link = stravaLink.trim();
                if (notes && notes.trim()) payload.notes = notes.trim();
                if (rpe) payload.rpe = rpe;
                if (feeling) payload.feeling = feeling;
                
                console.log('Sending payload:', payload);

                const res = await fetch(`{{ route('runner.calendar.update-session-status') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const data = await res.json();
                
                if (!res.ok) {
                    console.error('Error response:', data);
                    let errorMsg = data.message || 'Validation failed';
                    if (data.errors) {
                        errorMsg += '\n' + Object.values(data.errors).flat().join('\n');
                    }
                    alert('Error: ' + errorMsg);
                    return false;
                }

                if (data.success) {
                    // Update local state if detail modal is open
                    if (showDetailModal.value) {
                        // Check match (simple check since we often operate on detail itself)
                        const isSamePlan = (plan.enrollment_id && detail.enrollment_id === plan.enrollment_id && detail.session_day === plan.session_day) ||
                                         (plan.workout_id && detail.workout_id === plan.workout_id) ||
                                         (plan.id === detail.id);

                        if (isSamePlan) {
                            detail.status = status;
                            if (status !== 'completed') {
                                detail.completed_at = null;
                            }
                            if (data && data.tracking && data.tracking.completed_at) {
                                detail.completed_at = data.tracking.completed_at;
                            } else if (status === 'completed') {
                                detail.completed_at = new Date().toISOString();
                            }
                            if (stravaLink) detail.strava_link = stravaLink;
                            if (notes) detail.notes = notes;
                            if (status === 'completed') showDetailModal.value = false;
                        }
                    }
                    await loadPlans();
                    if (calendar) calendar.refetchEvents();
                    return true;
                } else {
                     alert(data.message || 'Failed to update status');
                     return false;
                }
            } catch (e) {
                console.error('Exception:', e);
                alert('An error occurred: ' + e.message);
                return false;
            }
        };

        const finishActivityWithLink = async () => {
            const link = String(stravaLinkInput.value || '').trim();
            await updateSessionStatus(detail, 'completed', link || null, notesInput.value, rpeInput.value, feelingInput.value);
        };

        const deleteEnrollment = async (enrollmentId, isPermanent = false) => {
            const confirmMsg = isPermanent
                ? 'Apakah Anda yakin ingin menghapus program ini secara PERMANEN? Data tidak dapat dikembalikan.'
                : 'Apakah Anda yakin ingin menghapus program ini? Program akan dipindahkan ke History.';
            if(!confirm(confirmMsg)) return;

            try {
                const url = `{{ url('/runner/calendar/enrollment') }}/${enrollmentId}/delete${isPermanent ? '?permanent=true' : ''}`;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus program');
                }
            } catch (e) {
                alert('Terjadi kesalahan saat menghapus program');
            }
        };

        const dayName = (d) => {
            const names = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            const idx = new Date(d).getDay();
            return names[idx] || 'Day';
        };
        const statusText = (s) => (s==='completed' || s==='imported') ? 'Finished' : (s==='started' ? 'On Progress' : 'UNFINISHED');
        const statusClass = (s) => (s==='completed' || s==='imported') ? 'text-green-400' : (s==='started' ? 'text-yellow-400' : 'text-red-400');
        const activityLabel = (t) => {
            if (!t) return 'Running';
            const clean = String(t).toLowerCase().trim();
            const map = {
                running: 'Running',
                run: 'Run',
                easy_run: 'Easy Run', 
                interval: 'Interval',
                tempo: 'Tempo',
                long_run: 'Long Run',
                recovery: 'Recovery',
                yoga: 'Yoga',
                cycling: 'Cycling',
                rest: 'Rest',
                strength: 'Strength',
                race: 'Race'
            };
            if (map[clean]) return map[clean];
            return t.split(/[_-]/).map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
        };
        const formatDate = (d) => { try { const dt = new Date(d); return dt.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }); } catch { return d; } };

        const chatCoach = (coach) => {
            try {
                if (window.openChat && coach) {
                    window.openChat(coach.id, coach.name || 'Coach', coach.avatar || null, "Hai Coach saya butuh bantuan");
                    return;
                }
            } catch(e){}
            if (coach) {
                window.location.href = `{{ url('/chat') }}/${coach.id}`;
            }
        };

        setFilter('unfinished');
        loadPlans();
        loadWeeklyVolume();
        
        onMounted(() => {
            console.log('[RunnerCalendar] onMounted');

            try {
                initCalendar();
                console.log('[RunnerCalendar] initCalendar done');
            } catch (e) {
                console.error('[RunnerCalendar] initCalendar failed', e);
            }

            // Auto-open Strava analysis modal if hash is present
            if (window.location.hash === '#strava-analysis') {
                openStravaAnalysisModal();
                // Clear the hash so it doesn't reopen on refresh if not intended
                history.replaceState(null, null, ' ');
            }

            try {
                nextTick(() => {
                    const plansEl = document.getElementById('runner-plans-section');
                    const calEl = document.getElementById('runner-calendar-section') || document.getElementById('calendar');
                    if (!plansEl || !calEl) return;

                    const obs = new IntersectionObserver((entries) => {
                        const calEntry = entries.find(e => e.target === calEl);
                        if (calEntry && calEntry.isIntersecting) {
                            activeDock.value = 'calendar';
                        } else {
                            activeDock.value = 'today';
                        }
                    }, { threshold: 0.25 });

                    obs.observe(calEl);
                    obs.observe(plansEl);
                });
            } catch (e) {
            }

            window.addEventListener('open-strava-detail', (e) => {
                const data = e.detail;
                if (data && data.id) {
                    showStravaAnalysisModal.value = false;
                    showVdotModal.value = false;
                    showFormModal.value = false;
                    showRaceModal.value = false;
                    
                    detailTitle.value = data.name || 'Strava Activity';
                    detail.type = 'strava_activity';
                    detail.description = data.name || null;
                    detail.strava_activity_id = data.id;
                    detail.date = data.date || null;
                    
                    stravaDetailsLoading.value = false;
                    stravaDetailsError.value = '';
                    detail.strava_metrics = null;
                    detail.strava_splits = [];
                    detail.strava_laps = [];
                    detail.actual_pace = null;
                    detail.ai_analysis = null;

                    loadStravaDetails(data.id);
                    showDetailModal.value = true;
                }
            });
        });

        // Strava AI Analysis (Strava MCP) State
        const showStravaAnalysisModal = ref(false);
        const stravaAnalysisLoading = ref(false);
        const stravaAnalysisRange = ref('14');
        const stravaAnalysisResult = ref(null);
        const straCustomStartDate = ref(new Date(Date.now() - 14 * 24 * 60 * 60 * 1000).toISOString().slice(0,10));
        const straCustomEndDate = ref(new Date().toISOString().slice(0,10));
        const stravaStatus = ref(null); // { strava_connected, last_sync, total_activities }
        const stravaStatusLoading = ref(false);

        const checkStravaStatus = async () => {
            stravaStatusLoading.value = true;
            try {
                const res = await fetch('{{ route("runner.strava.analysis.status") }}', {
                    headers: { 'Accept': 'application/json' }
                });
                stravaStatus.value = await res.json();
            } catch (e) {
                console.error('Failed to check Strava status', e);
                stravaStatus.value = { strava_connected: false, total_activities: 0 };
            } finally {
                stravaStatusLoading.value = false;
            }
        };

        const openStravaAnalysisModal = async () => {
            showDetailModal.value = false;
            showVdotModal.value = false;
            showFormModal.value = false;
            showRaceModal.value = false;
            showStravaAnalysisModal.value = true;
            stravaAnalysisResult.value = null;
            stravaAnalysisLoading.value = false;
            // Check Strava connection status in background
            await checkStravaStatus();
        };

        const connectStravaFirst = () => {
            window.location.href = '{{ route("runner.strava.connect") }}';
        };

        const syncStravaFirst = async () => {
            stravaStatusLoading.value = true;
            try {
                const res = await fetch('{{ route("runner.strava.sync") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    showNotification(`Strava sync berhasil! ${data.imported || 0} aktivitas baru diimport.`, 'success');
                    await checkStravaStatus(); // Refresh status
                } else {
                    if (confirm(data.message + '\nHubungkan akun Strava sekarang?')) {
                        connectStravaFirst();
                    }
                }
            } catch (e) {
                console.error(e);
                alert('Gagal sync Strava.');
            } finally {
                stravaStatusLoading.value = false;
            }
        };

        const runStravaAnalysis = async () => {
            stravaAnalysisLoading.value = true;
            try {
                const payload = {
                    range: stravaAnalysisRange.value,
                    start_date: stravaAnalysisRange.value === 'custom' ? straCustomStartDate.value : null,
                    end_date: stravaAnalysisRange.value === 'custom' ? straCustomEndDate.value : null,
                };
                const res = await fetch('{{ route("runner.strava.analyze") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    stravaAnalysisResult.value = data;
                } else if (data.needs_connect) {
                    if (confirm(data.message + '\n\nHubungkan Strava sekarang?')) {
                        connectStravaFirst();
                    }
                } else if (data.needs_sync) {
                    if (confirm(data.message + '\n\nSync data Strava sekarang?')) {
                        await syncStravaFirst();
                    }
                } else {
                    alert(data.message || 'Gagal menganalisis data Strava.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menghubungi server.');
            } finally {
                stravaAnalysisLoading.value = false;
            }
        };

        const applyAnalysisToGenerator = () => {
            if (!stravaAnalysisResult.value || !stravaAnalysisResult.value.autofill_params) return;
            const params = stravaAnalysisResult.value.autofill_params;
            
            // Prefill VDOT Form
            vdotForm.weekly_mileage = params.weekly_mileage || 20;
            vdotForm.training_frequency = params.training_frequency || 3;
            if (params.vdot) {
                trainingProfile.value.vdot = params.vdot;
            }
            
            vdotForm.race_distance = '5k';
            vdotForm.race_time = '';
            
            // Close analysis modal and open generator modal
            showStravaAnalysisModal.value = false;
            showVdotModal.value = true;
            
            showNotification(`AI Coach memuat parameter ke generator! VDOT: ${params.vdot}, Mileage: ${params.weekly_mileage} km/minggu`, 'success');
        };

        const parseMarkdown = (text) => {
            if (!text) return '';
            
            // Escape HTML
            let html = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // Headers
            html = html.replace(/^### (.*?)$/gm, '<h4 class="text-xs font-bold text-purple-400 uppercase tracking-widest mt-6 mb-3 first:mt-0 pb-1.5 border-b border-slate-800/80 flex items-center gap-2">$1</h4>');

            // Bullet lists
            html = html.replace(/^[-*] (.*?)$/gm, '<li class="ml-4 list-disc text-slate-300 mb-2 leading-relaxed">$1</li>');

            // Bold text with cool styling (badge/pill style for key metrics)
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-white bg-purple-950/30 border border-purple-500/20 px-1.5 py-0.5 rounded-md text-xs">$1</strong>');

            // Paragraphs & line breaks
            const blocks = html.split(/\n\n+/);
            const processed = blocks.map(block => {
                const trimmed = block.trim();
                if (trimmed.startsWith('<h4') || trimmed.startsWith('<li')) {
                    return trimmed;
                }
                return `<p class="text-slate-300 text-sm leading-relaxed mb-4">${trimmed.replace(/\n/g, '<br>')}</p>`;
            });

            return processed.join('');
        };

        const exportCalendar = async (type) => {
            const list = plans.value || [];
            if (list.length === 0) {
                alert('Tidak ada data program aktif untuk diekspor.');
                return;
            }

            const activeEnrollment = enrollments.value?.find(e => e.status === 'active');
            const programTitle = activeEnrollment?.program?.title || 'Program Latihan';
            const runnerName = '{{ auth()->user()->name }}';
            const sortedPlans = [...list].sort((a, b) => new Date(a.date) - new Date(b.date));

            const opt = {
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#0b1220',
                scale: 2,
                logging: false
            };

            const getRowHtml = (plan, globalIdx) => {
                const dateObj = new Date(plan.date);
                const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                const dayStr = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });
                
                const desc = plan.description || plan.program_title || plan.title;
                const title = desc ? desc.split('\n')[0] : 'Sesi Latihan';
                const wType = plan.type || 'Workout';
                const target = plan.distance ? `${plan.distance} km` : (plan.duration || '-');
                
                let statusTextStr = 'Pending';
                let statusBg = '#33415515';
                let statusColor = '#94a3b8';
                if (plan.status === 'completed' || plan.status === 'imported') {
                    statusTextStr = 'Selesai';
                    statusBg = '#10b98115';
                    statusColor = '#10b981';
                } else if (plan.status === 'started') {
                    statusTextStr = 'Mulai';
                    statusBg = '#eab30815';
                    statusColor = '#eab308';
                } else if (plan.status === 'missed') {
                    statusTextStr = 'Missed';
                    statusBg = '#ef444415';
                    statusColor = '#ef4444';
                }

                let typeColor = '#3b82f6';
                if (wType.toLowerCase().includes('easy') || wType.toLowerCase().includes('recovery')) typeColor = '#10b981';
                else if (wType.toLowerCase().includes('tempo') || wType.toLowerCase().includes('threshold')) typeColor = '#f97316';
                else if (wType.toLowerCase().includes('interval') || wType.toLowerCase().includes('speed')) typeColor = '#ef4444';
                else if (wType.toLowerCase().includes('long')) typeColor = '#6366f1';
                else if (wType.toLowerCase().includes('rest')) typeColor = '#64748b';

                return `
                    <tr style="border-bottom: 1px solid #1e293b;">
                        <td style="padding: 12px 8px; font-size: 12px; color: #64748b; font-family: monospace;">${globalIdx + 1}</td>
                        <td style="padding: 12px 8px; font-size: 12px; font-weight: bold; color: #ccff00;">${dayStr}</td>
                        <td style="padding: 12px 8px; font-size: 12px; color: #cbd5e1;">${dateStr}</td>
                        <td style="padding: 12px 8px; font-size: 12px; font-weight: bold; color: #ffffff;">
                            ${title}
                            <span style="display: inline-block; font-size: 9px; font-weight: bold; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; background-color: ${typeColor}15; color: ${typeColor}; border: 1px solid ${typeColor}30; margin-left: 8px;">
                                ${wType}
                            </span>
                        </td>
                        <td style="padding: 12px 8px; font-size: 12px; font-weight: bold; color: #ccff00; text-align: center;">${target}</td>
                        <td style="padding: 12px 8px; text-align: center;">
                            <span style="display: inline-block; font-size: 10px; font-weight: bold; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; background-color: ${statusBg}; color: ${statusColor}; border: 1px solid ${statusColor}30;">
                                ${statusTextStr}
                            </span>
                        </td>
                    </tr>
                `;
            };

            const getTableHeaderHtml = () => `
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #334155; color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                            <th style="padding: 10px 8px; width: 40px;">No</th>
                            <th style="padding: 10px 8px; width: 100px;">Hari</th>
                            <th style="padding: 10px 8px; width: 110px;">Tanggal</th>
                            <th style="padding: 10px 8px;">Detail Latihan</th>
                            <th style="padding: 10px 8px; width: 90px; text-align: center;">Target</th>
                            <th style="padding: 10px 8px; width: 90px; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            if (type === 'image') {
                // Image option renders everything in a single long scrollable canvas
                const container = document.createElement('div');
                container.style.position = 'absolute';
                container.style.left = '-9999px';
                container.style.top = '-9999px';
                container.style.width = '800px';
                container.style.padding = '40px';
                container.style.backgroundColor = '#0b1220';
                container.style.color = '#e2e8f0';
                container.style.fontFamily = 'system-ui, -apple-system, sans-serif';

                const headerHtml = `
                    <div style="border-bottom: 2px solid #ccff00; padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;">
                        <div>
                            <h1 style="color: #ffffff; font-size: 24px; font-weight: 900; margin: 0; text-transform: uppercase; font-style: italic;">Ruang Lari</h1>
                            <p style="color: #94a3b8; font-size: 11px; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 2px;">Rencana Program Latihan</p>
                        </div>
                        <div style="text-align: right;">
                            <h3 style="color: #ccff00; font-size: 14px; font-weight: 800; margin: 0;">${programTitle}</h3>
                            <p style="color: #94a3b8; font-size: 11px; margin: 3px 0 0 0;">Runner: ${runnerName}</p>
                        </div>
                    </div>
                `;

                let tableRows = '';
                sortedPlans.forEach((plan, idx) => {
                    tableRows += getRowHtml(plan, idx);
                });

                container.innerHTML = headerHtml + getTableHeaderHtml() + tableRows + '</tbody></table>';
                document.body.appendChild(container);

                html2canvas(container, opt).then(canvas => {
                    document.body.removeChild(container);
                    const imgData = canvas.toDataURL('image/png');
                    const link = document.createElement('a');
                    link.download = `kalender-latihan-${programTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.png`;
                    link.href = imgData;
                    link.click();
                }).catch(err => {
                    if (document.body.contains(container)) document.body.removeChild(container);
                    console.error('Export image failed:', err);
                    alert('Gagal mengekspor gambar.');
                });

            } else if (type === 'pdf') {
                // PDF option renders page-by-page (15 rows per page) to prevent row splitting across pages
                const rowsPerPage = 15;
                const totalPages = Math.ceil(sortedPlans.length / rowsPerPage);
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgWidth = 190;

                const renderPage = async (pageIdx) => {
                    const startIdx = pageIdx * rowsPerPage;
                    const endIdx = Math.min(startIdx + rowsPerPage, sortedPlans.length);
                    const pagePlans = sortedPlans.slice(startIdx, endIdx);

                    const container = document.createElement('div');
                    container.style.position = 'absolute';
                    container.style.left = '-9999px';
                    container.style.top = '-9999px';
                    container.style.width = '800px';
                    container.style.height = '1120px'; // Fixed A4 proportional height
                    container.style.padding = '40px';
                    container.style.backgroundColor = '#0b1220';
                    container.style.color = '#e2e8f0';
                    container.style.fontFamily = 'system-ui, -apple-system, sans-serif';
                    container.style.boxSizing = 'border-box';
                    container.style.display = 'flex';
                    container.style.flexDirection = 'column';

                    const headerHtml = `
                        <div style="border-bottom: 2px solid #ccff00; padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;">
                            <div>
                                <h1 style="color: #ffffff; font-size: 24px; font-weight: 900; margin: 0; text-transform: uppercase; font-style: italic;">Ruang Lari</h1>
                                <p style="color: #94a3b8; font-size: 11px; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 2px;">Rencana Program Latihan</p>
                            </div>
                            <div style="text-align: right;">
                                <h3 style="color: #ccff00; font-size: 14px; font-weight: 800; margin: 0;">${programTitle}</h3>
                                <p style="color: #94a3b8; font-size: 11px; margin: 3px 0 0 0;">Halaman ${pageIdx + 1} dari ${totalPages}</p>
                            </div>
                        </div>
                    `;

                    let tableRows = '';
                    pagePlans.forEach((plan, localIdx) => {
                        tableRows += getRowHtml(plan, startIdx + localIdx);
                    });

                    // Add content
                    container.innerHTML = headerHtml + getTableHeaderHtml() + tableRows + '</tbody></table>';
                    
                    // Add footer pushing it to bottom of A4
                    const footerDiv = document.createElement('div');
                    footerDiv.style.marginTop = 'auto';
                    footerDiv.style.paddingTop = '15px';
                    footerDiv.style.borderTop = '1px solid #1e293b';
                    footerDiv.style.display = 'flex';
                    footerDiv.style.justifyContent = 'space-between';
                    footerDiv.style.fontSize = '10px';
                    footerDiv.style.color = '#64748b';
                    footerDiv.innerHTML = `
                        <span>Runner: ${runnerName}</span>
                        <span>Generated via Ruang Lari</span>
                    `;
                    container.appendChild(footerDiv);

                    document.body.appendChild(container);
                    const canvas = await html2canvas(container, opt);
                    document.body.removeChild(container);
                    return canvas.toDataURL('image/png');
                };

                // Sequential rendering of pages
                (async () => {
                    for (let i = 0; i < totalPages; i++) {
                        if (i > 0) pdf.addPage();
                        const imgData = await renderPage(i);
                        pdf.addImage(imgData, 'PNG', 10, 8.5, imgWidth, 280);
                    }
                    pdf.save(`kalender-latihan-${programTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.pdf`);
                })().catch(err => {
                    console.error('PDF generation failed:', err);
                    alert('Gagal mengekspor PDF.');
                });
            }
        };

        return { filter, plans, plansLoading, enrollments, programBag, setFilter, dayName, statusText, statusClass, activityLabel, formatDate,
            showDetailModal, detail, detailTitle, closeDetail, deleteCustomWorkout, exportCalendar,
            showFormModal, form, openFormForToday, closeForm, saveCustomWorkout, showPlanDetail, updateSessionStatus, deleteEnrollment,
            resetPlan, applyProgram, showVdotModal, openVdotModal, vdotForm, vdotLoading, generateVdot, resetPlanList,
            trainingProfile, formatPace, showPbModal, openPbModal, pbForm, pbLoading, updatePb, bagTab, cancelledPrograms, restoreProgram,
            stravaLinkInput, notesInput, rpeInput, feelingInput, finishActivityWithLink, profileTab, chatCoach,
            aiCoachSummary, aiCoachCues, workoutGoalText, workoutEffectText, guidedSteps, guidedStepsDoneCount, guidedStepsProgressPct, guidedStepChecked, toggleGuidedStep,
            addStep, removeStep, moveStep, calculateTotalDistance, syncTraining, syncLoading, isSyncingStrava, syncStrava, weeklyVolume, maxVolume,
            assetStorage, assetProfile, runnerUrl, chatUrl, 
            showRaceModal, raceForm, openRaceForm, saveRace, setRaceDist,
            showWeeklyTargetModal, weeklyTargetForm, weeklyTargetLoading, updateWeeklyTarget,
            ruangLariEvents, loadingEvents, onSelectRuangLariEvent, eventSearchQuery, showEventDropdown, filteredEvents, selectRuangLariEvent,
            stravaDetailsLoading, stravaDetailsError, formatSeconds, displayPace, primaryMetricValue, primaryMetricUnit, statusDotClass,
            aiAnalysisLoading, aiAnalysisError, loadAiWorkoutAnalysis,
            showRescheduleModal, rescheduleTarget, rescheduleForm, rescheduleLoading, openRescheduleModal, submitReschedule,
            rescheduleTab, adaptiveRescheduleForm, adaptivePreview, previewLoading, getAdaptivePreview, submitAdaptiveReschedule,
            showStravaGraphModal, displayedPlans, canLoadMore, loadMorePlans,
            showApplyModal, applyForm, applyLoading, applyTarget, submitApply,
            countExercises, parseStrengthExercises, getExerciseIcon, previewExercise, startGuidedWorkout,
            showGuidedPlayer, guidedExercises, currentExerciseIndex, currentExercise, isPlaying, timerSeconds, togglePlay, nextExercise, prevExercise, resetTimer,
            stopGuidedWorkout, finishGuidedWorkout, exitGuidedWorkout, formatTimer,
            payDonation, donationLoading, donationAmount,
            hasUnpaidGenerator, unpaidEnrollmentId, firstLockedWeek,
            promoCode, promoApplied, promoError, checkingPromo, applyPromo, handleUnlockAction,
            notification, showNotification, showHeaderActions, showMobileAddSheet, openMobileAddSheet, activeDock, scrollToSection, activeMobileTab,
            showInsightModal, insightData, insightType,
            showStravaAnalysisModal, stravaAnalysisLoading, stravaAnalysisRange, stravaAnalysisResult, straCustomStartDate, straCustomEndDate,
            stravaStatus, stravaStatusLoading, connectStravaFirst, syncStravaFirst,
            openStravaAnalysisModal, runStravaAnalysis, applyAnalysisToGenerator, parseMarkdown,
            ttsSupported, speakDetailDescription
        };
    }

}).mount('#runner-calendar-app');
</script>
@endpush
