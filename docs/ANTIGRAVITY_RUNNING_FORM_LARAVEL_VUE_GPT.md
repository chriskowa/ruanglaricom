# Antigravity Build Brief V2 — Running Form Analysis for Existing Laravel + Vue App

## 0. Context

Integrate a new **Running Analysis** module into the existing **Ruang Lari** Laravel + Vue application.

The application already has:
- Laravel backend;
- Vue frontend;
- runner dashboard;
- runner/user records;
- database;
- OpenAI API integration.

Do not create a separate standalone application unless required for an isolated camera-capture route. Reuse the existing authentication, layout, runner model, database conventions, API client, authorization, queues, logging, and deployment workflow.

The module is intended for a supervised running-form analysis session with approximately 20 runners. An operator selects runners one by one, records pose landmarks as each runner passes a fixed lateral camera, reviews the analysis, and publishes a runner-facing report in the runner dashboard.

---

## 1. Non-negotiable design principle

Use three separate layers:

```text
Layer 1 — Measurement
Camera + MediaPipe + deterministic geometry

Layer 2 — Assessment
Versioned rules based only on measured metrics and data quality

Layer 3 — Communication
OpenAI model converts verified findings into clear runner-facing explanations
```

Never allow the OpenAI model to:
- calculate authoritative joint angles from prose;
- replace pose estimation;
- invent gait events;
- invent metrics not present in the input;
- claim medical diagnosis;
- output injury probability;
- override invalid or low-quality capture;
- recommend a drill that is outside the approved recommendation catalog.

GPT improves explanation, prioritization, readability, and personalization. It does not make the underlying measurement scientifically valid. Validity depends on camera setup, measurement quality, deterministic algorithms, threshold calibration, expert review, and future validation studies.

---

## 2. Recommended stack

Reuse the existing project stack.

### Backend

- Existing Laravel version and conventions
- Existing relational database, preferably PostgreSQL or MySQL
- Laravel migrations, models, policies, form requests, resources, jobs, events, and tests
- Laravel Queue for post-capture analysis and OpenAI report generation
- Private Laravel storage for compressed pose artifacts and optional video clips
- Scheduler only for cleanup/retry tasks

### Frontend

- Existing Vue setup
- TypeScript where supported by the current project
- Existing router/state/UI framework
- `@mediapipe/tasks-vision`
- Canvas API
- Web Worker where stable
- IndexedDB/Dexie only as an offline capture buffer, not the permanent system of record
- Zod or the project's current schema-validation library

### OpenAI

- Call the OpenAI API only from Laravel, never directly from the browser
- Use the Responses API
- Use Structured Outputs with a strict JSON Schema
- Make the model ID configurable through environment/config
- Store prompt version, model ID, request metadata, output, validation state, and reviewer action
- Default to `store: false` for sensitive runner analysis unless the product owner explicitly chooses another data-control strategy

Do not hardcode a model name throughout the codebase. Configure it:

```env
OPENAI_RUNNING_ANALYSIS_MODEL=gpt-5.6
OPENAI_RUNNING_ANALYSIS_ENABLED=true
OPENAI_RUNNING_ANALYSIS_STORE=false
```

The actual enabled model must be verified against the account and current OpenAI model availability.

---

## 3. Product workflow

```text
Operator opens Running Analysis session
        ↓
Selects runner
        ↓
Starts/arms camera
        ↓
Runner enters analysis zone
        ↓
Vue captures pose landmarks
        ↓
Runner exits zone
        ↓
Local quality check and preview
        ↓
Operator accepts or repeats capture
        ↓
Vue uploads trial payload to Laravel
        ↓
Laravel persists trial + pose artifact
        ↓
Deterministic analysis job
        ↓
Versioned findings and recommendations
        ↓
OpenAI narrative job
        ↓
Coach/operator review
        ↓
Publish to runner dashboard
```

The analysis must not be automatically published without a review step in the first production version.

---

## 4. Dashboard integration

Add a **Running Analysis** section to the existing runner dashboard.

Runner dashboard section:

- latest published analysis;
- analysis date;
- capture quality;
- key observations;
- Pull, Land, Push, Lever;
- priority focus;
- technique cues;
- drills;
- strength recommendations;
- limitation/disclaimer;
- previous analysis history;
- comparison with previous analysis only when measurement conditions are comparable.

Operator/admin section:

- create session;
- assign runners;
- capture trial;
- review trial;
- edit event frames;
- approve findings;
- regenerate narrative;
- publish/unpublish report;
- view GPT audit;
- export session data.

Authorization:

- runner can only access their own published reports;
- coach/operator can access assigned sessions;
- admin can manage configuration and all analysis records;
- unpublished/internal metrics must not be exposed through runner endpoints.

---

## 5. Suggested routes

Adapt names to the existing routing convention.

### Vue pages

```text
/admin/running-analysis/sessions
/admin/running-analysis/sessions/:sessionId
/admin/running-analysis/sessions/:sessionId/capture
/admin/running-analysis/trials/:trialId/review
/admin/running-analysis/trials/:trialId/report

/dashboard/running-analysis
/dashboard/running-analysis/:reportId
```

### Laravel API

```text
GET    /api/running-analysis/sessions
POST   /api/running-analysis/sessions
GET    /api/running-analysis/sessions/{session}
PATCH  /api/running-analysis/sessions/{session}

GET    /api/running-analysis/sessions/{session}/runners
POST   /api/running-analysis/sessions/{session}/runners

POST   /api/running-analysis/trials
POST   /api/running-analysis/trials/{trial}/artifact
POST   /api/running-analysis/trials/{trial}/finalize
POST   /api/running-analysis/trials/{trial}/reanalyze
PATCH  /api/running-analysis/trials/{trial}/events
POST   /api/running-analysis/trials/{trial}/approve
POST   /api/running-analysis/trials/{trial}/publish
POST   /api/running-analysis/trials/{trial}/unpublish
POST   /api/running-analysis/trials/{trial}/regenerate-narrative

GET    /api/runners/{runner}/running-analysis
GET    /api/running-analysis/reports/{report}
```

Use policies for every route.

---

## 6. Database design

Reuse the existing runner/user table through a foreign key. Do not duplicate runner identity.

### `running_analysis_sessions`

```text
id
name
location nullable
session_date
created_by
camera_setup_json nullable
status: draft|active|completed|archived
created_at
updated_at
```

### `running_analysis_session_runner`

```text
id
session_id
runner_id
sequence_no
status: pending|captured|analyzed|published|repeat_required
notes nullable
created_at
updated_at
```

### `running_analysis_trials`

```text
id UUID
session_id
runner_id
operator_id
attempt_no
direction: left_to_right|right_to_left|unknown
started_at
ended_at nullable
camera_device_label nullable
camera_width nullable
camera_height nullable
camera_fps nullable
inference_fps nullable
pose_model
pose_model_version nullable
capture_version
analysis_version nullable
ruleset_version nullable
status:
  created|capturing|uploaded|queued|analyzing|review_required|
  approved|published|invalid|failed|interrupted
quality_grade nullable
quality_score nullable
invalid_reason nullable
published_at nullable
approved_by nullable
approved_at nullable
created_at
updated_at
```

### `running_analysis_artifacts`

Store raw high-volume data outside normal relational columns when practical.

```text
id
trial_id
type: pose_landmarks|smoothed_landmarks|video_clip|preview_image|debug
disk
path
mime_type
compression nullable
sha256
size_bytes
metadata_json nullable
created_at
```

Recommended:
- Serialize pose frames as JSON or MessagePack.
- Compress with gzip.
- Store in private Laravel storage.
- Store only the path, checksum, and metadata in the database.
- Do not create one database row per landmark or per video frame unless the project has a proven need for frame-level SQL queries.

### `running_analysis_events`

```text
id
trial_id
stride_index
side: left|right|unknown
event_type: initial_contact|midstance|toe_off|max_swing_flexion
timestamp_ms
frame_index
confidence
source: automatic|operator
created_at
updated_at
```

### `running_analysis_metrics`

```text
id
trial_id
stride_index nullable
side nullable
metric_code
category: pull|land|push|lever|quality|general
value_decimal nullable
value_json nullable
unit
confidence
source_frame_indexes_json nullable
calculation_version
created_at
updated_at
```

Use stable metric codes, for example:

```text
LAND_ANKLE_PELVIS_OFFSET
LAND_KNEE_FLEXION
LAND_SHIN_ANGLE
LAND_FOOT_ANGLE
GENERAL_TRUNK_LEAN
PUSH_TRAILING_LEG_ANGLE
PUSH_HIP_EXTENSION_PROXY
PULL_MAX_SWING_KNEE_FLEXION
PULL_HEEL_HIP_DISTANCE
LEVER_RECOVERY_TIME
LEVER_EARLY_OPENING
QUALITY_USABLE_FRAME_RATIO
```

### `running_analysis_findings`

```text
id
trial_id
finding_code
category
severity
confidence
evidence_json
explanation_key
ruleset_version
review_status: generated|accepted|edited|rejected
reviewed_by nullable
reviewed_at nullable
created_at
updated_at
```

### `running_analysis_recommendations`

```text
id
trial_id
finding_id nullable
recommendation_code
type: cue|drill|strength|referral|setup
title
description
priority
source: deterministic|ai_reworded|operator
catalog_version
created_at
updated_at
```

### `running_analysis_reports`

```text
id
trial_id
runner_id
report_version
status: draft|reviewed|published|superseded
deterministic_summary_json
runner_narrative_json nullable
coach_notes nullable
disclaimer_version
published_at nullable
created_at
updated_at
```

### `running_analysis_ai_runs`

```text
id
trial_id
report_id nullable
provider
model
prompt_version
schema_version
input_hash
input_payload_json or encrypted/private path
response_id nullable
raw_output_json nullable
parsed_output_json nullable
status: queued|running|valid|invalid|failed|discarded
error_code nullable
error_message nullable
latency_ms nullable
input_tokens nullable
output_tokens nullable
review_action nullable
created_at
updated_at
```

Never expose raw AI run records to runners.

---

## 7. Pose capture in Vue

Use MediaPipe Pose Landmarker in the browser.

Capture:

- one runner;
- lateral camera;
- normalized landmarks;
- world landmarks if available;
- visibility/presence;
- timestamps;
- camera settings;
- pose inference FPS;
- capture direction;
- analysis-zone configuration.

Use an explicit state machine:

```text
IDLE
CAMERA_READY
ARMED
RUNNER_DETECTED
CAPTURING
FINALIZING
LOCAL_REVIEW
UPLOADING
UPLOADED
ERROR
```

Required controls:

- select runner;
- choose camera;
- start/stop camera;
- arm;
- manual start;
- manual stop;
- discard;
- repeat;
- accept/upload;
- next runner.

Auto-start conditions:

- core landmarks usable for configurable consecutive frames;
- pelvis midpoint enters the analysis zone;
- movement direction is consistent;
- runner is assigned and selected.

Auto-stop conditions:

- pelvis passes the exit line;
- pose is lost for a configurable timeout;
- maximum duration is reached.

Always provide manual fallback.

---

## 8. Offline resilience

The Laravel database is the permanent source of truth, but camera capture must survive a temporary network problem.

Use IndexedDB as an outbox:

```text
pending_capture
pending_upload
uploaded
failed
```

Store locally until Laravel confirms:
- trial record accepted;
- artifact checksum accepted;
- finalize request accepted.

Each client trial must have a UUID generated before capture. Laravel must enforce idempotency so retrying upload does not create duplicate trials.

On reconnect:
- display unsynced trial count;
- retry explicitly or automatically with backoff;
- verify SHA-256;
- only delete local payload after successful server acknowledgement.

---

## 9. Artifact upload strategy

Do not send one API request per pose frame.

Create one trial package:

```json
{
  "schema_version": "1.0",
  "trial_uuid": "...",
  "capture_metadata": {},
  "analysis_zone": {},
  "frames": [],
  "client_quality": {},
  "client_metrics": {}
}
```

Recommended pipeline:

1. Build package in browser.
2. Validate locally.
3. Compress.
4. Calculate checksum.
5. Upload multipart or signed/private upload endpoint.
6. Laravel stores artifact.
7. Laravel validates checksum and metadata.
8. Laravel queues server analysis.
9. Vue polls status or receives an existing project notification mechanism.

Set sensible upload limits. Raw video upload must be optional and separately configured because video is much larger and more sensitive than pose landmarks.

---

## 10. Deterministic analysis

Create a versioned analysis service.

Suggested Laravel service classes:

```text
App\Domain\RunningAnalysis\
├── CapturePayloadValidator
├── PoseArtifactReader
├── LandmarkSmoother
├── TrialQualityEvaluator
├── GaitEventDetector
├── StrideSegmenter
├── MetricCalculator
├── FindingRuleEngine
├── RecommendationCatalog
├── ReportBuilder
└── AnalysisVersion
```

For the first release, it is acceptable to calculate initial metrics in TypeScript and upload them with raw landmarks, but the server must:

- validate schema;
- validate ranges;
- recompute critical summary metrics where practical;
- record client and server calculation versions;
- reject impossible values;
- retain raw artifact for future re-analysis.

Avoid depending exclusively on frontend-generated final findings.

### Quality before findings

Calculate:

- usable frame ratio;
- core landmark confidence;
- number of usable strides;
- missing-frame gaps;
- feet/body clipping;
- direction consistency;
- event confidence;
- metric consistency across strides.

Quality grade:

```text
good
usable
poor
invalid
```

If invalid:
- do not call OpenAI;
- do not produce Pull/Land/Push/Lever conclusions;
- mark runner as repeat required.

---

## 11. Pull, Land, Push, Lever definitions

Treat these as product-specific analysis categories with explicit metric definitions.

### Land

Evidence may include:
- ankle-to-pelvis horizontal offset at initial contact;
- knee flexion at initial contact;
- shin angle;
- foot segment angle;
- trunk lean;
- stride-to-stride consistency.

### Push

Evidence may include:
- trailing leg angle;
- hip extension proxy;
- knee extension near toe-off;
- foot/ankle plantarflexion proxy;
- midstance-to-toe-off timing.

Do not claim force in Newton from pose data.

### Pull

Evidence may include:
- toe-off to maximum knee-flexion time;
- maximum swing knee flexion;
- heel-to-hip normalized distance;
- heel recovery height/path.

### Lever

Define as recovery-leg shortening and reopening timing:
- knee angle at maximum recovery;
- heel-to-hip distance;
- recovery timing;
- lower-leg opening timing;
- early long-lever pattern.

Document definitions in code and operator UI. Do not present them as universal clinical standards.

---

## 12. Rules engine

Create thresholds in a versioned configuration file or database table.

Finding codes:

```text
LANDING_AHEAD_OF_PELVIS
LOW_LANDING_KNEE_FLEXION
NON_VERTICAL_SHIN_AT_CONTACT
EXCESSIVE_TRUNK_LEAN
LIMITED_TRAILING_LEG
LIMITED_HIP_EXTENSION_PROXY
DELAYED_LEG_RECOVERY
LOW_SWING_KNEE_FLEXION
EARLY_LONG_LEVER
LEFT_RIGHT_TIMING_DIFFERENCE
INCONSISTENT_STRIDES
LOW_DATA_QUALITY
```

A finding must contain:

```json
{
  "code": "LANDING_AHEAD_OF_PELVIS",
  "category": "land",
  "severity": "moderate",
  "confidence": 0.86,
  "evidence": [
    {
      "metric_code": "LAND_ANKLE_PELVIS_OFFSET",
      "value": 0.18,
      "unit": "normalized",
      "confidence": 0.91
    }
  ],
  "limitations": []
}
```

Thresholds are heuristic defaults until validated. Label them as product rules, not medical cutoffs.

---

## 13. Approved recommendation catalog

Create a controlled catalog in code/database.

Each finding maps to allowed items:

```text
Technique cues
Drills
Strength exercises
Referral/setup guidance
```

Example:

```json
{
  "finding_code": "LANDING_AHEAD_OF_PELVIS",
  "allowed_cues": [
    "LAND_CLOSER_TO_BODY",
    "GRADUAL_CADENCE_ADJUSTMENT"
  ],
  "allowed_drills": [
    "ANKLING",
    "A_MARCH",
    "CADENCE_STRIDES",
    "WALL_SWITCH"
  ],
  "allowed_strength": [
    "CALF_RAISE",
    "SPLIT_SQUAT",
    "SINGLE_LEG_RDL"
  ]
}
```

The OpenAI model may:
- select from allowed items;
- prioritize them;
- rewrite them into runner-friendly Indonesian;
- explain why they relate to the measured finding.

The OpenAI model may not create arbitrary new medical or exercise recommendations.

---

## 14. OpenAI role

### Correct use

Use OpenAI to produce:

- concise runner-facing summary;
- prioritization of accepted findings;
- clear explanation of evidence;
- Pull/Land/Push/Lever narrative;
- technique cues based on approved catalog;
- drill and strength explanation;
- limitations;
- questions the runner can discuss with a coach;
- comparison narrative between two comparable reports.

### Incorrect use

Do not ask the model:

```text
“Look at these numbers and diagnose the runner.”
“Predict injury risk percentage.”
“Determine exact foot strike from an uncertain frame.”
“Create recommendations freely.”
```

### Input to OpenAI

Send a minimized structured payload:

```json
{
  "schema_version": "1.0",
  "language": "id",
  "runner_context": {
    "experience_level": "recreational",
    "goal": "improve efficiency",
    "current_pain": false
  },
  "capture_quality": {
    "grade": "good",
    "limitations": []
  },
  "accepted_findings": [],
  "metrics": [],
  "allowed_recommendations": [],
  "report_constraints": {
    "no_diagnosis": true,
    "no_injury_probability": true,
    "max_priorities": 3
  }
}
```

Do not send:
- unrelated runner profile fields;
- authentication data;
- email/phone;
- full raw landmark sequence unless a reviewed design specifically requires it;
- raw video by default.

Use pseudonymous runner/trial IDs if an ID is needed.

---

## 15. OpenAI Structured Output schema

Use strict Structured Outputs.

Suggested output:

```json
{
  "summary": "string",
  "quality_note": "string",
  "priorities": [
    {
      "rank": 1,
      "finding_code": "string",
      "title": "string",
      "observation": "string",
      "evidence_metric_codes": ["string"],
      "possible_mechanical_implication": "string",
      "confidence_label": "high|medium|low"
    }
  ],
  "categories": {
    "pull": {
      "status": "focus|acceptable|insufficient_data",
      "summary": "string",
      "finding_codes": ["string"]
    },
    "land": {
      "status": "focus|acceptable|insufficient_data",
      "summary": "string",
      "finding_codes": ["string"]
    },
    "push": {
      "status": "focus|acceptable|insufficient_data",
      "summary": "string",
      "finding_codes": ["string"]
    },
    "lever": {
      "status": "focus|acceptable|insufficient_data",
      "summary": "string",
      "finding_codes": ["string"]
    }
  },
  "recommendations": [
    {
      "recommendation_code": "string",
      "type": "cue|drill|strength|referral",
      "why": "string",
      "dosage_note": "string|null"
    }
  ],
  "limitations": ["string"],
  "disclaimer": "string"
}
```

Validation after response:

- JSON Schema valid;
- every finding code exists in accepted findings;
- every metric code exists in input;
- every recommendation code exists in allowed catalog;
- no forbidden phrases;
- maximum number of priorities respected;
- no diagnosis;
- no injury probability;
- no fabricated measurement.

If validation fails:
- mark AI run invalid;
- retry once with the same structured evidence and a corrective instruction;
- otherwise use deterministic report text;
- never block access to the deterministic report because OpenAI failed.

---

## 16. Prompt design

Use versioned prompts stored in code or a prompt registry.

System instruction principles:

```text
You are a running-form report editor.
You receive verified measurements, accepted findings, and an approved
recommendation catalog.

You must not create measurements, findings, diagnoses, injury probabilities,
or recommendations outside the provided catalog.

Explain uncertainty explicitly. Use neutral Indonesian. Distinguish measured
observation from possible mechanical implication. Return only the required
structured output.
```

Prompt must tell the model:

- metrics are authoritative input;
- accepted findings are the only findings allowed;
- missing data must be described as insufficient;
- wording must not imply causation;
- “risk” wording should be replaced by “indikator perhatian” or “pertimbangan beban”;
- current pain requires professional review messaging;
- runner should focus on no more than three priorities.

Store:
- prompt version;
- schema version;
- model;
- reasoning setting if used;
- input hash;
- parsed output;
- final reviewed output.

---

## 17. Laravel jobs

Suggested queue pipeline:

```text
PersistRunningTrialJob
        ↓
AnalyzeRunningTrialJob
        ↓
BuildDeterministicReportJob
        ↓
GenerateRunningNarrativeJob
        ↓
ValidateRunningNarrativeJob
        ↓
MarkTrialReviewRequiredJob
```

Requirements:

- jobs are idempotent;
- use unique locks where appropriate;
- retry transient API/network errors;
- do not retry validation failures indefinitely;
- record failure reasons;
- use queue timeouts;
- runner dashboard remains usable if AI job fails;
- operator can regenerate narrative manually.

Do not call OpenAI synchronously inside the capture request.

---

## 18. Laravel service interfaces

Example contracts:

```php
interface RunningAnalysisEngine
{
    public function analyze(RunningAnalysisTrial $trial): AnalysisResult;
}

interface RunningNarrativeGenerator
{
    public function generate(
        RunningAnalysisTrial $trial,
        DeterministicReport $report
    ): StructuredRunnerNarrative;
}

interface RunningNarrativeValidator
{
    public function validate(
        StructuredRunnerNarrative $narrative,
        DeterministicReport $source
    ): ValidationResult;
}
```

Keep OpenAI-specific code behind an adapter:

```text
OpenAIRunningNarrativeGenerator
FakeRunningNarrativeGenerator
```

Use the fake generator in tests.

---

## 19. Review workflow

Before publishing, operator sees side-by-side:

```text
Measured evidence
Deterministic finding
AI-generated wording
Allowed recommendation
```

Operator can:
- accept;
- edit runner-facing text;
- reject a finding;
- choose alternative recommendation from catalog;
- mark repeat required;
- add coach notes;
- publish.

Store edits separately from raw AI output for auditability.

A published report must be immutable by default. Regeneration creates a new report version and supersedes the old version.

---

## 20. Runner safety and product language

Use:

- “observasi”;
- “indikator perhatian”;
- “kemungkinan implikasi mekanis”;
- “berdasarkan video lateral 2D”;
- “confidence rendah/sedang/tinggi”;
- “diskusikan dengan coach/fisioterapis bila ada nyeri”.

Do not use:

- “diagnosis”;
- “pasti menyebabkan cedera”;
- “risiko cedera 82%”;
- “normal/abnormal” without a defined and validated reference;
- “gaya dorong X Newton”;
- “terbukti” when only inferred from video.

Required disclaimer:

> Hasil ini adalah screening form lari berbasis video 2D. Hasil bukan diagnosis medis, prediksi cedera, atau pengganti pemeriksaan oleh tenaga kesehatan maupun analisis biomekanik laboratorium.

If `current_pain = true`:
- do not provide aggressive technique-change instructions;
- prioritize a professional assessment note;
- allow operator to suppress drill/strength recommendations.

---

## 21. Privacy

Before capture, record consent for:

- pose analysis;
- optional video storage;
- report storage;
- OpenAI-assisted narrative generation.

Separate consent flags.

Recommended default:
- store pose landmarks;
- do not store full video unless needed;
- if video is stored, use private storage and a retention period;
- minimize personal data sent to OpenAI;
- call OpenAI server-side;
- provide deletion workflow;
- log who accessed/published reports.

Create configurable retention:

```env
RUNNING_ANALYSIS_VIDEO_RETENTION_DAYS=30
RUNNING_ANALYSIS_POSE_RETENTION_DAYS=365
RUNNING_ANALYSIS_AI_INPUT_RETENTION_DAYS=90
```

Adapt values to product policy and applicable law.

---

## 22. Camera setup

The application must display actual values from browser camera settings.

Do not assume 1080p/240 fps browser capture.

Display:

```text
Requested resolution
Actual resolution
Requested FPS
Actual FPS
Inference FPS
Dropped/processed frame ratio
```

Recommended live path:
- 1080p if supported;
- actual 30–60 FPS;
- pose inference approximately 30 FPS;
- fixed lateral camera;
- landscape;
- gimbal tracking disabled;
- full body visible;
- stable exposure and fast enough shutter;
- consistent runner distance;
- marked entry and exit zones.

The camera may separately record high-frame-rate footage, but that does not prove the browser receives the same frame rate.

---

## 23. Tests

### Laravel tests

- migrations;
- model relationships;
- policies;
- trial idempotency;
- artifact checksum;
- finalize workflow;
- invalid quality blocks AI job;
- job pipeline;
- rules-engine fixtures;
- recommendation catalog validation;
- AI schema validation;
- fabricated finding/recommendation rejection;
- deterministic fallback;
- publish authorization;
- runner cannot access another runner’s report;
- report versioning;
- deletion/retention workflow.

### Vue tests

- camera state machine;
- entry/exit zone;
- manual fallback;
- capture package validation;
- IndexedDB outbox;
- upload retry;
- runner sequence;
- trial does not overwrite previous runner;
- actual camera setting display;
- review page;
- report page;
- permission errors.

### Fixture trials

Create deterministic fixtures:

```text
good_lateral_trial
low_confidence_trial
feet_clipped_trial
left_to_right_trial
right_to_left_trial
landing_ahead_trial
low_recovery_trial
insufficient_stride_trial
```

Expected metrics and findings must be asserted. Do not rely only on snapshot tests.

---

## 24. Observability

Log:

- trial UUID;
- analysis version;
- ruleset version;
- artifact checksum;
- quality grade;
- job duration;
- OpenAI model;
- prompt version;
- AI status;
- validation failure reason;
- publication reviewer.

Do not log full sensitive payloads in normal application logs.

Admin metrics:

- captures;
- invalid/repeat rate;
- upload failures;
- analysis failures;
- AI validation failure rate;
- average review edits;
- GPT cost per report;
- model/prompt version distribution.

A high coach-edit rate means prompts/rules need evaluation; it must not be hidden.

---

## 25. Implementation milestones

### Milestone 1 — Existing project discovery

Antigravity must inspect:
- Laravel version and directory conventions;
- Vue integration;
- existing runner/user model;
- current auth and policies;
- existing dashboard;
- existing OpenAI service;
- queue driver;
- database;
- testing setup;
- UI components.

Produce a short integration plan before modifying files.

### Milestone 2 — Domain and database

Create migrations, models, enums/value objects, policies, factories, and tests.

### Milestone 3 — Operator session UI

Create session, runner queue, and status dashboard.

### Milestone 4 — Camera capture

Add camera selector, actual settings, canvas overlay, MediaPipe, zones, and manual controls.

### Milestone 5 — Resilient upload

Create trial UUID, IndexedDB outbox, compressed artifact upload, checksum, and idempotent finalize endpoint.

### Milestone 6 — Deterministic analysis

Implement quality, event candidates, metrics, findings, and recommendation catalog.

### Milestone 7 — Trial review

Implement event correction, finding acceptance/rejection, repeat workflow, and report preview.

### Milestone 8 — OpenAI narrative

Implement Responses API adapter, strict schema, queued job, validator, fallback, audit, and tests.

### Milestone 9 — Runner dashboard

Create published analysis history and report page using existing dashboard design.

### Milestone 10 — Hardening

Run full tests, build, queue tests, permission tests, backup/retention checks, browser smoke test, and session rehearsal with mock runners.

---

## 26. Acceptance criteria

The module is complete when:

- it is integrated into the existing Laravel + Vue application;
- existing authentication and runner records are reused;
- operator can process at least 20 runners without data collision;
- camera actual settings are visible;
- skeleton overlay and capture controls work;
- trial survives temporary network failure;
- raw pose artifact is stored privately with checksum;
- database contains versioned metrics/findings/report;
- low-quality trial does not receive confident conclusions;
- OpenAI runs only after deterministic analysis;
- OpenAI output uses strict schema;
- AI cannot introduce unknown finding or recommendation codes;
- deterministic fallback works when OpenAI is unavailable;
- operator review is required before publication;
- runner only sees their own published report;
- report contains limitations and disclaimer;
- no injury percentage or diagnosis is produced;
- Laravel and Vue tests pass;
- production build succeeds;
- README documents camera setup, queue setup, OpenAI config, privacy, retention, and recovery.

---

## 27. Instruction to Antigravity

Read this specification fully.

Start by inspecting the existing repository. Do not scaffold a new Laravel or Vue project. Reuse the current architecture and conventions.

Produce an implementation plan mapped to the current files and models. Then implement milestone by milestone. After every milestone, run the relevant tests and correct errors before proceeding.

Do not hardcode assumptions about:
- runner model/table names;
- Laravel version;
- Vue state library;
- UI framework;
- queue driver;
- database engine;
- OpenAI model availability.

Use configuration and adapters.

The final walkthrough must include:

1. files changed;
2. migrations added;
3. queue/job flow;
4. camera workflow;
5. deterministic analysis flow;
6. OpenAI input/output schema;
7. validation and fallback behavior;
8. runner dashboard flow;
9. tests run;
10. known limitations and next validation work.
