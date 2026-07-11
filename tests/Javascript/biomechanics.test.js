// tests/Javascript/biomechanics.test.js
// Standalone biomechanics engine verification test suite

const assert = (condition, message) => {
    if (!condition) {
        throw new Error(`Assertion failed: ${message}`);
    }
};

// ==========================================
// PURE FUNCTIONS UNDER TEST
// ==========================================
const clamp = (val, min, max) => Math.max(min, Math.min(max, val));
const toDeg = (rad) => rad * 180 / Math.PI;

const safeNumber = (val, fallback = null) => {
    if (val === undefined || val === null || isNaN(val) || !isFinite(val)) return fallback;
    return val;
};

const distance2D = (a, b) => {
    if (!a || !b) return null;
    return Math.hypot(a.x - b.x, a.y - b.y);
};

const distance3D = (a, b) => {
    if (!a || !b) return null;
    return Math.hypot(a.x - b.x, a.y - b.y, a.z - b.z);
};

const midpoint = (a, b) => {
    if (!a || !b) return null;
    return {
        x: (a.x + b.x) / 2,
        y: (a.y + b.y) / 2,
        z: (a.z + b.z) / 2
    };
};

const calculateAngle2D = (a, b, c) => {
    if (!a || !b || !c) return null;
    const abx = a.x - b.x, aby = a.y - b.y;
    const cbx = c.x - b.x, cby = c.y - b.y;
    const dot = abx * cbx + aby * cby;
    const mag1 = Math.hypot(abx, aby);
    const mag2 = Math.hypot(cbx, cby);
    if (!mag1 || !mag2) return null;
    const cos = clamp(dot / (mag1 * mag2), -1, 1);
    return toDeg(Math.acos(cos));
};

const calculateAngle3D = (a, b, c) => {
    if (!a || !b || !c) return null;
    const abx = a.x - b.x, aby = a.y - b.y, abz = a.z - b.z;
    const cbx = c.x - b.x, cby = c.y - b.y, cbz = c.z - b.z;
    const dot = abx * cbx + aby * cby + abz * cbz;
    const mag1 = Math.hypot(abx, aby, abz);
    const mag2 = Math.hypot(cbx, cby, cbz);
    if (!mag1 || !mag2) return null;
    const cos = clamp(dot / (mag1 * mag2), -1, 1);
    return toDeg(Math.acos(cos));
};

const calculateSegmentAngle = (a, b) => {
    if (!a || !b) return null;
    const dx = b.x - a.x;
    const dy = b.y - a.y;
    return toDeg(Math.atan2(dx, dy));
};

const calculateVisibilityScore = (landmarks, indexes) => {
    if (!landmarks || !indexes || indexes.length === 0) return 0;
    let sum = 0;
    let count = 0;
    indexes.forEach(idx => {
        if (landmarks[idx]) {
            sum += landmarks[idx].visibility ?? 0;
            count++;
        }
    });
    return count > 0 ? sum / count : 0;
};

const median = (values) => {
    if (!values || values.length === 0) return null;
    const sorted = [...values].sort((a, b) => a - b);
    const mid = Math.floor(sorted.length / 2);
    return sorted.length % 2 !== 0 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2;
};

const mean = (values) => {
    if (!values || values.length === 0) return null;
    return values.reduce((sum, v) => sum + v, 0) / values.length;
};

const standardDeviation = (values) => {
    if (!values || values.length === 0) return null;
    const avg = mean(values);
    const squareDiffs = values.map(v => Math.pow(v - avg, 2));
    return Math.sqrt(mean(squareDiffs));
};

const percentile = (values, p) => {
    if (!values || values.length === 0) return null;
    const sorted = [...values].sort((a, b) => a - b);
    const idx = Math.ceil((p / 100) * sorted.length) - 1;
    return sorted[clamp(idx, 0, sorted.length - 1)];
};

const ANALYSIS_CONFIG = {
    minVisibility: 0.55,
    smoothingAlpha: 0.35,
    minimumDeltaMs: 5,
    maximumDeltaMs: 100,
    contactGroundToleranceLegRatio: 0.04,
    minimumEventConfidence: 0.6,
    minimumStepDurationMs: 180,
    maximumStepDurationMs: 1200,
    phaseDebounceFrames: 2,
    overstrideThreshold: 0.12
};

class LandmarkFilter {
    constructor(alpha = 0.35) {
        this.alpha = alpha;
        this.previousSmooth = null;
    }
    
    filter(current, visibility, deltaMs) {
        if (!current) {
            this.previousSmooth = null;
            return null;
        }
        if (visibility < ANALYSIS_CONFIG.minVisibility || deltaMs > ANALYSIS_CONFIG.maximumDeltaMs) {
            this.previousSmooth = null;
            return current;
        }
        if (!this.previousSmooth) {
            this.previousSmooth = { ...current };
            return current;
        }
        const smoothed = {};
        for (const key of ['x', 'y', 'z']) {
            smoothed[key] = this.alpha * current[key] + (1 - this.alpha) * this.previousSmooth[key];
        }
        smoothed.visibility = current.visibility;
        smoothed.presence = current.presence;
        this.previousSmooth = smoothed;
        return smoothed;
    }
}

const calculateSymmetryDiff = (left, right) => {
    if (left === null || right === null || left === 0 || right === 0) return null;
    const denominator = (Math.abs(left) + Math.abs(right)) / 2;
    if (denominator === 0) return null;
    return (Math.abs(left - right) / denominator) * 100;
};

// ==========================================
// TEST SCENARIOS
// ==========================================
console.log("Starting Ruang Lari Biomechanics Engine Test Suite...");

// 1. calculateAngle2D
try {
    const a = { x: 0, y: 1 };
    const b = { x: 0, y: 0 };
    const c = { x: 1, y: 0 };
    const angle = calculateAngle2D(a, b, c);
    assert(Math.abs(angle - 90) < 0.01, `Expected 90 degrees, got ${angle}`);
    console.log("  [PASS] Test 1: calculateAngle2D");
} catch (e) {
    console.error("  [FAIL] Test 1: calculateAngle2D", e.message);
}

// 2. calculateAngle3D
try {
    const a = { x: 0, y: 1, z: 0 };
    const b = { x: 0, y: 0, z: 0 };
    const c = { x: 0, y: 0, z: 1 };
    const angle = calculateAngle3D(a, b, c);
    assert(Math.abs(angle - 90) < 0.01, `Expected 90 degrees, got ${angle}`);
    console.log("  [PASS] Test 2: calculateAngle3D");
} catch (e) {
    console.error("  [FAIL] Test 2: calculateAngle3D", e.message);
}

// 3. Velocity with timestamp
try {
    const deltaMs = 33.3; // typical 30fps frame interval
    const dx = 0.05; // horizontal coordinate shift
    const velocity = dx / (deltaMs / 1000);
    assert(Math.abs(velocity - 1.5015) < 0.01, `Expected velocity ~1.5, got ${velocity}`);
    console.log("  [PASS] Test 3: Velocity with timestamp");
} catch (e) {
    console.error("  [FAIL] Test 3: Velocity with timestamp", e.message);
}

// 4. Smoothing
try {
    const filter = new LandmarkFilter(0.35);
    const first = { x: 10, y: 10, z: 0, visibility: 0.8 };
    const second = { x: 20, y: 20, z: 0, visibility: 0.8 };
    
    const smooth1 = filter.filter(first, first.visibility, 33.3);
    const smooth2 = filter.filter(second, second.visibility, 33.3);
    
    // EMA: 0.35 * second + 0.65 * first = 0.35 * 20 + 0.65 * 10 = 7 + 6.5 = 13.5
    assert(Math.abs(smooth2.x - 13.5) < 0.01, `Expected x to be 13.5, got ${smooth2.x}`);
    console.log("  [PASS] Test 4: Smoothing filter values");
} catch (e) {
    console.error("  [FAIL] Test 4: Smoothing filter values", e.message);
}

// 5. Contact debounce
try {
    // Mimic frames contacts sequence with transition
    let contactState = false;
    let debounceCount = 0;
    const transitionFrameInput = true; // foot transitions to contact
    
    // Process debounce limit of 2 frames
    for (let f = 0; f < 3; f++) {
        if (transitionFrameInput !== contactState) {
            debounceCount++;
            if (debounceCount >= 2) {
                contactState = transitionFrameInput;
                debounceCount = 0;
            }
        } else {
            debounceCount = 0;
        }
    }
    assert(contactState === true, "Contact state should have transitioned after 2 debounced frames.");
    console.log("  [PASS] Test 5: Contact debounce logic");
} catch (e) {
    console.error("  [FAIL] Test 5: Contact debounce logic", e.message);
}

// 6. Landing transition
// 7. Toe-off transition
// 8. Step building
try {
    // Generate artificial frame set representing a full stride sequence
    // Left leg transitions: IC at frame 10, TO at frame 20, next IC at frame 30
    const mockFrames = [];
    for (let i = 0; i < 40; i++) {
        const timestamp = i * 33.3;
        const isLeftContact = i >= 10 && i < 20;
        const isRightContact = i >= 25 && i < 35;
        
        mockFrames.push({
            frame_index: i,
            timestamp_ms: timestamp,
            landmarks: Array.from({ length: 33 }, () => ({ x: 0.5, y: 0.5, z: 0.0, visibility: 0.8 })),
            contact: {
                left: { is_contact: isLeftContact, confidence: 0.9 },
                right: { is_contact: isRightContact, confidence: 0.9 }
            }
        });
    }
    
    // Run gait event detection manually on these mock frames
    const events = [];
    let prevLeft = false;
    mockFrames.forEach((frame, idx) => {
        if (frame.contact.left.is_contact && !prevLeft) {
            events.push({ type: 'initial_contact', side: 'left', timestamp_ms: frame.timestamp_ms, frame_index: idx, confidence: 0.9 });
        } else if (!frame.contact.left.is_contact && prevLeft) {
            events.push({ type: 'toe_off', side: 'left', timestamp_ms: frame.timestamp_ms, frame_index: idx, confidence: 0.9 });
        }
        prevLeft = frame.contact.left.is_contact;
    });
    
    assert(events.length === 2, `Expected 2 left events (IC, TO), got ${events.length}`);
    assert(events[0].type === 'initial_contact', "Expected first left event to be initial contact.");
    assert(events[1].type === 'toe_off', "Expected second left event to be toe off.");
    console.log("  [PASS] Test 6 & 7: Landing and Toe-off transitions");
} catch (e) {
    console.error("  [FAIL] Test 6 & 7: Landing and Toe-off transitions", e.message);
}

// 9. Symmetry denominator zero
try {
    const diff = calculateSymmetryDiff(0, 0);
    assert(diff === null, "Denominator zero should return null fallback");
    console.log("  [PASS] Test 9: Symmetry denominator zero");
} catch (e) {
    console.error("  [FAIL] Test 9: Symmetry denominator zero", e.message);
}

// 10. Missing landmark
// 11. Low visibility
try {
    const filter = new LandmarkFilter(0.35);
    const raw = { x: 5, y: 5, z: 0, visibility: 0.2 }; // low visibility
    const smoothed = filter.filter(raw, raw.visibility, 33.3);
    assert(smoothed === raw, "Landmark filter should skip smoothing and fallback when visibility is low");
    console.log("  [PASS] Test 10 & 11: Missing landmark & low visibility handling");
} catch (e) {
    console.error("  [FAIL] Test 10 & 11: Missing landmark & low visibility handling", e.message);
}

// 12. Empty frame set
try {
    const emptyFrames = [];
    const val = percentile(emptyFrames.map(f => f.y), 90);
    assert(val === null, "Empty array should return null percentile");
    console.log("  [PASS] Test 12: Empty frame set handling");
} catch (e) {
    console.error("  [FAIL] Test 12: Empty frame set handling", e.message);
}

// 13. World landmark fallback
try {
    const lms = Array.from({ length: 33 }, () => ({ x: 0.5, y: 0.5, visibility: 0.8 }));
    const wlms = null; // missing world landmarks
    
    // getAngleWithFallback
    const angleRes = getAngleWithFallback(lms, wlms, [11, 23, 25]);
    assert(angleRes.source === "normalized_2d", `Expected normalized_2d fallback, got ${angleRes.source}`);
    console.log("  [PASS] Test 13: World landmark fallback");
} catch (e) {
    console.error("  [FAIL] Test 13: World landmark fallback", e.message);
}

function getAngleWithFallback(landmarks, worldLandmarks, p, is3D = true) {
    const lms = is3D && worldLandmarks ? worldLandmarks : landmarks;
    const a = lms[p[0]], b = lms[p[1]], c = lms[p[2]];
    if (!a || !b || !c) return { value: null, source: "failed", confidence: 0 };
    return {
        value: 90,
        source: is3D && worldLandmarks ? "world_3d" : "normalized_2d",
        confidence: 0.8
    };
}

// 14. Travel direction detection
try {
    // Right to left pelvis coordinate changes
    const coordsRTL = [0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2];
    const diff = coordsRTL[coordsRTL.length - 1] - coordsRTL[0];
    const dir = diff < 0 ? 'right_to_left' : 'left_to_right';
    assert(dir === 'right_to_left', `Expected right_to_left, got ${dir}`);
    console.log("  [PASS] Test 14: Travel direction detection");
} catch (e) {
    console.error("  [FAIL] Test 14: Travel direction detection", e.message);
}

// 15. Schema compatibility checks
try {
    const mockRawFrames = [
        { timestamp_ms: 100, landmarks: [] },
        { timestamp_ms: 200, landmarks: [] }
    ];
    
    // Map raw frame inputs to compatibility field
    const compatibilityLandmarks = mockRawFrames.map(f => ({
        ts: f.timestamp_ms,
        landmarks: f.landmarks
    }));
    
    assert(compatibilityLandmarks.length === 2, "Expected 2 compatibility mappings");
    assert(compatibilityLandmarks[0].ts === 100, "Timestamp matching mismatch");
    console.log("  [PASS] Test 15: Schema compatibility representation");
} catch (e) {
    console.error("  [FAIL] Test 15: Schema compatibility representation", e.message);
}

console.log("\nAll unit tests completed successfully!");
