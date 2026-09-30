<style>
body {
    background-color: #080d1a !important;
}
#main-content-wrapper {
    background: #080d1a !important;
}
.glass-panel {
    background: #121c2e !important;
    border: 1.5px solid #23354d !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.28) !important;
    transition: border-color 0.3s ease;
}
.glass-panel:hover {
    border-color: #3b5278 !important;
}
.glass-panel-orange {
    background: #121c2e !important;
    border: 1.5px solid #23354d !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.28) !important;
    transition: border-color 0.3s ease;
}
.glass-panel-orange:hover {
    border-color: #3b5278 !important;
}
.fc .fc-toolbar-title{font-size: 0.95rem;font-weight:700;color:#f8fafc}
#loader[data-hidden="1"] { pointer-events: none !important; }
#ph-sidebar-backdrop.hidden { display: none !important; }
[v-cloak]{display:none !important;}
.fc .fc-button{background:#0d1527;border-color:rgba(255,255,255,0.1);color:#94a3b8;border-radius:4px !important}
.fc .fc-button:hover{color:#ccff00;border-color:#ccff00}
.fc-col-header-cell-cushion { color: #94a3b8 !important; text-decoration: none !important; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
.fc-daygrid-day-number { color: #64748b !important; font-family: 'JetBrains Mono', monospace; text-decoration: none; font-size: 0.75rem; }

.fc-event {
    cursor: pointer;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    border-radius: 6px !important;
    padding: 2px 4px !important;
    font-size: 0.72rem !important;
    font-weight: 500 !important;
}
.fc-event-main, .fc-event-title {
    color: #f8fafc !important;
}
.fc-event.difficulty-easy{border-left:3px solid #10b981 !important}
.fc-event.difficulty-moderate{border-left:3px solid #f59e0b !important}
.fc-event.difficulty-hard{border-left:3px solid #ef4444 !important}

/* Locked session styling */
.fc-event.locked-session {
    opacity: 0.7;
    filter: grayscale(0.5);
    cursor: pointer;
    border-style: dashed !important;
    background-image: repeating-linear-gradient(45deg, rgba(255,255,255,0.03) 0, rgba(255,255,255,0.03) 10px, transparent 10px, transparent 20px);
}
.fc-event.locked-session:hover {
    opacity: 1;
    filter: grayscale(0);
    transform: translateY(-1px);
}
.fc-event.locked-session .fc-event-title {
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Workout Type Color Coding (Calm & Proportional) */
.fc-event.workout-easy_run {
  border-left: 3px solid #10b981 !important;
  background-color: rgba(16, 185, 129, 0.06) !important;
  color: #10b981 !important;
}
.fc-event.workout-long_run {
  border-left: 3px solid #3b82f6 !important;
  background-color: rgba(59, 130, 246, 0.06) !important;
  color: #3b82f6 !important;
}
.fc-event.workout-interval {
  border-left: 3px solid #ef4444 !important;
  background-color: rgba(239, 68, 68, 0.06) !important;
  color: #ef4444 !important;
}
.fc-event.workout-tempo, .fc-event.workout-tempo_run, .fc-event[class*="workout-tempo"] {
  border-left: 3px solid #f59e0b !important;
  background-color: rgba(245, 158, 11, 0.06) !important;
  color: #f59e0b !important;
}
.fc-event.workout-strength {
  border-left: 3px solid #8b5cf6 !important;
  background-color: rgba(139, 92, 246, 0.06) !important;
  color: #8b5cf6 !important;
}
.fc-event.workout-rest {
  border-left: 3px solid #64748b !important;
  background-color: rgba(100, 116, 139, 0.06) !important;
  color: #94a3b8 !important;
}
.fc-event.workout-marathon {
  border-left: 3px solid #06b6d4 !important;
  background-color: rgba(6, 182, 212, 0.06) !important;
  color: #06b6d4 !important;
}
.fc-event.workout-repetition, .fc-event.workout-hill, .fc-event.workout-hill_repeats, .fc-event.workout-hill_repeat, .fc-event[class*="workout-hill"] {
  border-left: 3px solid #d946ef !important;
  background-color: rgba(217, 70, 239, 0.06) !important;
  color: #d946ef !important;
}
.fc-event.workout-race {
  border-left: 3px solid #f97316 !important;
  background-color: rgba(249, 115, 22, 0.08) !important;
  color: #f97316 !important;
}
.fc-event.workout-threshold, .fc-event.workout-treshold {
  border-left: 3px solid #ec4899 !important;
  background-color: rgba(236, 72, 153, 0.08) !important;
  color: #ec4899 !important;
}
.fc-event.workout-recovery_run {
  border-left: 3px solid #14b8a6 !important;
  background-color: rgba(20, 184, 166, 0.08) !important;
  color: #14b8a6 !important;
}
.fc-event.workout-time_trial {
  border-left: 3px solid #ff5722 !important;
  background-color: rgba(255, 87, 34, 0.08) !important;
  color: #ff5722 !important;
}


/* Mobile List View Styling (Clean Card Style) */
.fc-list { border: none !important; background: transparent !important; }
.fc-list-day-cushion { background-color: transparent !important; padding: 6px 12px !important; }
.fc-list-day-text, .fc-list-day-side-text { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
.fc-list-event td { border: none !important; }
.fc-list-event { 
    background-color: #0d1527 !important; 
    border-radius: 8px !important; 
    margin-bottom: 6px !important; 
    display: block !important; 
    position: relative !important;
    border: 1px solid rgba(255, 255, 255, 0.04) !important;
}
/* Hack to make table rows look like cards with spacing */
.fc-list-table { border-collapse: separate; border-spacing: 0 6px; }
.fc-list-event:hover td { background-color: transparent !important; }
.fc-list-event-graphic { display: none; } /* Hide the little dot */
.fc-list-event-time { display: none !important; } /* Hide confusing All-day indicators */
.fc-list-event-title { color: #f1f5f9 !important; font-weight: 600 !important; font-size: 0.82rem !important; padding: 8px 12px !important; }

/* Color coding for list view cards based on difficulty class injected via JS */
.fc-list-event.difficulty-easy { border-left: 3px solid #10b981 !important; }
.fc-list-event.difficulty-moderate { border-left: 3px solid #f59e0b !important; }
.fc-list-event.difficulty-hard { border-left: 3px solid #ef4444 !important; }

/* Color coding for list view by workout type */
.fc-list-event.workout-easy_run { border-left: 3px solid #10b981 !important; }
.fc-list-event.workout-long_run { border-left: 3px solid #3b82f6 !important; }
.fc-list-event.workout-interval { border-left: 3px solid #ef4444 !important; }
.fc-list-event.workout-tempo, .fc-list-event.workout-tempo_run, .fc-list-event[class*="workout-tempo"] { border-left: 3px solid #f59e0b !important; }
.fc-list-event.workout-strength { border-left: 3px solid #8b5cf6 !important; }
.fc-list-event.workout-race { border-left: 3px solid #f97316 !important; }
.fc-list-event.workout-rest { border-left: 3px solid #64748b !important; }
.fc-list-event.workout-marathon { border-left: 3px solid #06b6d4 !important; }
.fc-list-event.workout-repetition, .fc-list-event.workout-hill, .fc-list-event.workout-hill_repeats, .fc-list-event.workout-hill_repeat, .fc-list-event[class*="workout-hill"] { border-left: 3px solid #d946ef !important; }
.fc-list-event.workout-threshold, .fc-list-event.workout-treshold { border-left: 3px solid #ec4899 !important; }
.fc-list-event.workout-recovery_run { border-left: 3px solid #14b8a6 !important; }
.fc-list-event.workout-time_trial { border-left: 3px solid #ff5722 !important; }

@media (max-width: 640px) {
    .fc .fc-header-toolbar {
        margin-bottom: 0.75rem;
    }

    .fc .fc-toolbar {
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .fc .fc-toolbar-chunk:nth-child(2) {
        order: 0;
        flex: 1 1 100%;
        display: flex;
        justify-content: center;
    }

    .fc .fc-toolbar-chunk:nth-child(1),
    .fc .fc-toolbar-chunk:nth-child(3) {
        order: 1;
        flex: 1 1 50%;
        display: flex;
        align-items: center;
    }

    .fc .fc-toolbar-chunk:nth-child(1) {
        justify-content: flex-start;
    }

    .fc .fc-toolbar-chunk:nth-child(3) {
        justify-content: flex-end;
    }

    .fc .fc-toolbar-title {
        font-size: 1rem;
        line-height: 1.15;
        text-align: center;
        margin: 0.15rem 0;
    }

    .fc .fc-button {
        padding: 0.35rem 0.55rem;
        font-size: 0.75rem;
        line-height: 1;
        border-radius: 0.8rem;
    }

    .fc .fc-button-group {
        gap: 0.35rem;
    }

    .fc .fc-button-group > .fc-button {
        border-radius: 0.8rem;
    }
}

/* Fix chat box overlap with mobile dock */
#chatbox-toggle {
    transition: bottom 0.3s ease-in-out, transform 0.3s ease;
}

@media (max-width: 767px) {
    #chatbox-toggle {
        display: none !important;
    }

    #ph-chatbox {
        display: none !important;
    }
}

/* Training Checkin Modal Styles */
.training-checkin {
    position: fixed;
    inset: 0;
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem;
    overflow: hidden;
}
.tc-backdrop {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 1;
}
.tc-dialog {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 42rem;
    max-height: 88vh;
    display: flex;
    flex-direction: column;
    border-radius: 0.5rem;
    border: 1px solid #1e293b;
    background-color: #020617;
    color: #e2e8f0;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    overflow: hidden;
}
.tc-header {
    flex-shrink: 0;
    border-bottom: 1px solid #1e293b;
    padding: 0.875rem 1.25rem;
    background-color: #0f172a;
}
.tc-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}
.tc-eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #ccff00;
    margin-bottom: 0.25rem;
}
.tc-header h2 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.3;
}
.tc-close {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 0.375rem;
    background-color: #1e293b;
    border: 1px solid #334155;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1rem;
    font-weight: bold;
    flex-shrink: 0;
    transition: all 0.15s ease;
}
.tc-close:hover {
    background-color: #334155;
    color: #ffffff;
}
.tc-muted {
    font-size: 0.6875rem;
    color: #94a3b8;
    margin-top: 0.25rem;
}
.tc-status {
    font-size: 0.6875rem;
    color: #38bdf8;
    margin-top: 0.25rem;
}
.tc-alert {
    font-size: 0.6875rem;
    color: #f43f5e;
    margin-top: 0.25rem;
}
.tc-program {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid rgba(30, 41, 59, 0.8);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    font-size: 0.6875rem;
    font-weight: 600;
    color: #f8fafc;
}
.tc-body {
    flex: 1 1 0%;
    overflow-y: auto;
    padding: 1rem 1.25rem;
    background-color: #020617;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.tc-fields {
    border: 0;
    padding: 0;
    margin: 0;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}
.tc-group {
    border: 0;
    padding: 0;
    margin: 0;
    min-width: 0;
}
.tc-group legend {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #ffffff;
    margin-bottom: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}
.tc-number {
    display: inline-block;
    font-size: 0.625rem;
    font-family: monospace;
    color: #ccff00;
    font-weight: 800;
}
.tc-options {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.5rem;
}
.tc-options-two {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
@media (max-width: 640px) {
    .tc-options {
        grid-template-columns: 1fr;
    }
}
.tc-option {
    padding: 0.625rem;
    border-radius: 0.375rem;
    border: 1px solid #1e293b;
    background-color: rgba(15, 23, 42, 0.6);
    text-align: left;
    color: #cbd5e1;
    cursor: pointer !important;
    transition: all 0.15s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    user-select: none;
}
.tc-option:hover {
    border-color: #334155;
    background-color: #0f172a;
}
.tc-option.is-selected {
    border-color: #ccff00 !important;
    background-color: #0f172a !important;
    box-shadow: 0 0 0 1px #ccff00 !important;
    color: #ffffff !important;
}
.tc-option-title {
    font-size: 0.75rem;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.125rem;
}
.tc-mark {
    width: 0.875rem;
    height: 0.875rem;
    border-radius: 9999px;
    border: 1px solid #334155;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.5625rem;
}
.tc-option.is-selected .tc-mark {
    border-color: #ccff00;
    background-color: #ccff00;
    color: #020617;
    font-weight: bold;
}
.tc-option-note {
    font-size: 0.6875rem;
    color: #94a3b8;
    line-height: 1.3;
}
.tc-summary {
    border-radius: 0.375rem;
    border: 1px solid rgba(6, 182, 212, 0.3);
    background-color: rgba(6, 182, 212, 0.05);
    padding: 0.625rem 0.75rem;
}
.tc-summary h3 {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #ffffff;
    margin-bottom: 0.25rem;
}
.tc-summary p {
    font-size: 0.6875rem;
    color: #e2e8f0;
    line-height: 1.4;
}
.tc-details {
    padding-top: 0.25rem;
}
.tc-details-toggle {
    font-size: 0.6875rem;
    color: #94a3b8;
    background: none;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-weight: 500;
    padding: 0;
}
.tc-details-toggle:hover {
    color: #f1f5f9;
}
.tc-details-body {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #1e293b;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.tc-details-body dl {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.5rem;
}
@media (max-width: 768px) {
    .tc-details-body dl {
        grid-template-columns: 1fr;
    }
}
.tc-details-body dl > div {
    border-radius: 0.375rem;
    border: 1px solid #1e293b;
    background-color: rgba(15, 23, 42, 0.6);
    padding: 0.5rem;
}
.tc-details-body dt {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    margin-bottom: 0.25rem;
}
.tc-details-body dd strong {
    font-size: 0.6875rem;
    font-weight: 700;
    color: #ffffff;
    display: block;
    margin-bottom: 0.125rem;
}
.tc-details-body dd p {
    font-size: 0.625rem;
    color: #cbd5e1;
    line-height: 1.3;
}
.tc-footer {
    flex-shrink: 0;
    border-top: 1px solid #1e293b;
    padding: 0.75rem 1.25rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.625rem;
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    background-color: #0f172a;
}
.tc-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.tc-cancel {
    padding: 0.375rem 0.875rem;
    border-radius: 0.375rem;
    background-color: #1e293b;
    border: 1px solid #334155;
    color: #e2e8f0;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    cursor: pointer;
    transition: all 0.15s ease;
}
.tc-cancel:hover {
    background-color: #334155;
    color: #ffffff;
}
.tc-save {
    padding: 0.375rem 1rem;
    border-radius: 0.375rem;
    background-color: #ccff00;
    border: none;
    color: #080a0d;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    cursor: pointer;
    transition: all 0.15s ease;
}
.tc-save:hover {
    background-color: #bef264;
}
.tc-save:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
