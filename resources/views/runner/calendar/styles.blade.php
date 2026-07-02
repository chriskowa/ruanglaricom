<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css">
<style>
body {
    background-color: #080d1a !important;
}
#main-content-wrapper {
    background: #080d1a !important;
}
.glass-panel {
    background: #0d1527 !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.25) !important;
    transition: border-color 0.3s ease;
}
.glass-panel:hover {
    border-color: rgba(255, 255, 255, 0.12) !important;
}
.glass-panel-orange {
    background: #0d1527 !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.25) !important;
    transition: border-color 0.3s ease;
}
.glass-panel-orange:hover {
    border-color: rgba(255, 255, 255, 0.12) !important;
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
.fc-event.workout-tempo {
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
.fc-event.workout-race {
  border-left: 3px solid #f97316 !important;
  background-color: rgba(249, 115, 22, 0.08) !important;
  color: #f97316 !important;
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
.fc-list-event.workout-tempo { border-left: 3px solid #f59e0b !important; }
.fc-list-event.workout-strength { border-left: 3px solid #8b5cf6 !important; }
.fc-list-event.workout-race { border-left: 3px solid #f97316 !important; }
.fc-list-event.workout-rest { border-left: 3px solid #64748b !important; }

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
</style>
