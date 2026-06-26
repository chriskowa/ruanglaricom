<script>
window.RLBuilderUtils = (function() {
  const toMinutes = (str) => {
    if (!str) return 0;
    if (typeof str === 'number') return str;
    const parts = str.toString().split(':').map(Number);
    if (parts.length === 1) return parts[0];
    if (parts.length === 2) return parts[0] + parts[1]/60;
    if (parts.length === 3) return parts[0]*60 + parts[1] + parts[2]/60;
    return 0;
  };

  const normalizeType = (raw) => {
    const t = (raw || '').toString().toLowerCase().replace(/\s+/g, '_');
    if (t === 'run' || t === 'recovery' || t === 'easy') return 'easy_run';
    if (t === 'speed' || t === 'repetition' || t === 'intervals') return 'interval';
    if (t === 'threshold' || t === 'tempo_run') return 'tempo';
    if (t === 'long') return 'long_run';
    if (t === 'time_trial' || t === 'timetrial' || t === 'tt') return 'time_trial';
    return t || 'easy_run';
  };

  const buildSummary = (bf) => {
    const parts = [];
    if (bf.warmup?.enabled) {
      if (bf.warmup.by === 'distance') {
        const u = bf.warmup.unit || 'km';
        const dist = bf.warmup.distance !== undefined && bf.warmup.distance !== null ? bf.warmup.distance : (bf.warmup.distanceKm || 0);
        parts.push(`WU: ${dist}${u}${bf.warmup.pace ? ` @${bf.warmup.pace}` : ''}`);
      } else {
        parts.push(`WU: ${bf.warmup.duration}${bf.warmup.pace ? ` @${bf.warmup.pace}` : ''}`);
      }
    }
    if (bf.type === 'interval') {
      if (bf.interval.sets && bf.interval.sets.length > 0) {
        const setDescs = bf.interval.sets.map(s => {
          if (s.by === 'distance') {
            const u = s.unit || 'km';
            return `${s.reps}x${s.dist}${u}${s.pace ? ` @${s.pace}` : ''}`;
          } else {
            return `${s.reps}x${s.time}${s.pace ? ` @${s.pace}` : ''}`;
          }
        });
        parts.push(setDescs.join(' + '));
      } else {
        if (bf.interval.by === 'distance') {
          const u = bf.interval.repDistanceUnit || 'km';
          const dist = bf.interval.repDistance !== undefined && bf.interval.repDistance !== null ? bf.interval.repDistance : (bf.interval.repDistanceKm || 0);
          parts.push(`${bf.interval.reps}x${dist}${u}${bf.interval.pace ? ` @${bf.interval.pace}` : ''}`);
        } else {
          parts.push(`${bf.interval.reps}x${bf.interval.repTime}${bf.interval.pace ? ` @${bf.interval.pace}` : ''}`);
        }
        parts.push(`Rec ${bf.interval.recovery}`);
      }
    } else if (bf.type === 'tempo') {
      if (bf.tempo.by === 'distance') {
        const u = bf.tempo.unit || 'km';
        const dist = bf.tempo.distance !== undefined && bf.tempo.distance !== null ? bf.tempo.distance : (bf.tempo.distanceKm || 0);
        parts.push(`${dist}${u} @${bf.tempo.pace} ${bf.tempo.effort}`);
      } else {
        parts.push(`${bf.tempo.duration} @${bf.tempo.pace} ${bf.tempo.effort}`);
      }
    } else if (bf.type === 'time_trial') {
      if (bf.timeTrial?.by === 'distance') {
        const u = bf.timeTrial.unit || 'km';
        const dist = bf.timeTrial.distance !== undefined && bf.timeTrial.distance !== null ? bf.timeTrial.distance : (bf.timeTrial.distanceKm || 0);
        parts.push(`Time Trial ${dist}${u}${bf.timeTrial.pace ? ` @${bf.timeTrial.pace}` : ''}`);
      } else {
        parts.push(`Time Trial ${bf.timeTrial?.duration || ''}${bf.timeTrial?.pace ? ` @${bf.timeTrial.pace}` : ''}`);
      }
    } else if (bf.type === 'long_run') {
      if (bf.main.by === 'distance') {
        const u = bf.main.unit || 'km';
        const dist = bf.main.distance !== undefined && bf.main.distance !== null ? bf.main.distance : (bf.main.distanceKm || 0);
        parts.push(`Long Run ${dist}${u}`);
      } else {
        parts.push(`Long Run ${bf.main.duration}`);
      }
      if (bf.longRun?.fastFinish?.enabled) {
        const u = bf.longRun.fastFinish.unit || 'km';
        const dist = bf.longRun.fastFinish.distance !== undefined && bf.longRun.fastFinish.distance !== null ? bf.longRun.fastFinish.distance : (bf.longRun.fastFinish.distanceKm || 0);
        parts.push(`+ ${dist}${u} Fast Finish`);
      }
    } else if (bf.type === 'easy_run') {
      if (bf.main.by === 'distance') {
        const u = bf.main.unit || 'km';
        const dist = bf.main.distance !== undefined && bf.main.distance !== null ? bf.main.distance : (bf.main.distanceKm || 0);
        parts.push(`Easy Run ${dist}${u}`);
      } else {
        parts.push(`Easy Run ${bf.main.duration}`);
      }
    } else if (bf.type === 'strength') {
      parts.push(`Strength: ${(bf.strength?.plan || []).length} exercises`);
    } else if (bf.type === 'rest') {
      parts.push('Rest Day');
    }
    if (bf.cooldown?.enabled) {
      if (bf.cooldown.by === 'distance') {
        const u = bf.cooldown.unit || 'km';
        const dist = bf.cooldown.distance !== undefined && bf.cooldown.distance !== null ? bf.cooldown.distance : (bf.cooldown.distanceKm || 0);
        parts.push(`CD: ${dist}${u}${bf.cooldown.pace ? ` @${bf.cooldown.pace}` : ''}`);
      } else {
        parts.push(`CD: ${bf.cooldown.duration}${bf.cooldown.pace ? ` @${bf.cooldown.pace}` : ''}`);
      }
    }
    return parts.join(' + ');
  };

  const computeTotalDistance = (bf) => {
    let total = 0;
    const addByTime = (duration, pace) => {
      const d = toMinutes(duration);
      const p = toMinutes(pace);
      if (d > 0 && p > 0) total += d / p;
    };
    const getVal = (fieldObj, distKey, unitKey) => {
      const dist = fieldObj[distKey] !== undefined && fieldObj[distKey] !== null ? fieldObj[distKey] : fieldObj[distKey + 'Km'];
      const val = Number(dist) || 0;
      const unit = fieldObj[unitKey] || 'km';
      return unit === 'm' ? val / 1000 : val;
    };

    if (bf.warmup?.enabled) {
      if (bf.warmup.by === 'distance') total += getVal(bf.warmup, 'distance', 'unit');
      else addByTime(bf.warmup.duration, bf.warmup.pace);
    }
    if (bf.type === 'easy_run' || bf.type === 'long_run') {
      if (bf.main.by === 'distance') total += getVal(bf.main, 'distance', 'unit');
      else addByTime(bf.main.duration, bf.main.pace);
      if (bf.type === 'long_run' && bf.longRun?.fastFinish?.enabled) {
        total += getVal(bf.longRun.fastFinish, 'distance', 'unit');
      }
    } else if (bf.type === 'tempo') {
      if (bf.tempo.by === 'distance') total += getVal(bf.tempo, 'distance', 'unit');
      else addByTime(bf.tempo.duration, bf.tempo.pace);
    } else if (bf.type === 'time_trial') {
      if (bf.timeTrial?.by === 'distance') total += getVal(bf.timeTrial, 'distance', 'unit');
      else addByTime(bf.timeTrial.duration, bf.timeTrial.pace);
    } else if (bf.type === 'interval') {
      if (bf.interval.sets && bf.interval.sets.length > 0) {
        bf.interval.sets.forEach(set => {
          if (set.by === 'distance') {
            const val = Number(set.dist) || 0;
            const unit = set.unit || 'km';
            const repKm = unit === 'm' ? val / 1000 : val;
            total += (Number(set.reps) || 0) * repKm;
          } else {
            const perRep = toMinutes(set.time);
            const pace = toMinutes(set.pace);
            if (perRep > 0 && pace > 0) total += (Number(set.reps) || 0) * (perRep / pace);
          }
        });
      } else {
        if (bf.interval.by === 'distance') {
          const val = bf.interval.repDistance !== undefined && bf.interval.repDistance !== null ? bf.interval.repDistance : bf.interval.repDistanceKm;
          const repVal = Number(val) || 0;
          const unit = bf.interval.repDistanceUnit || 'km';
          const repKm = unit === 'm' ? repVal / 1000 : repVal;
          total += (Number(bf.interval.reps) || 0) * repKm;
        } else {
          const perRep = toMinutes(bf.interval.repTime);
          const pace = toMinutes(bf.interval.pace);
          if (perRep > 0 && pace > 0) total += (Number(bf.interval.reps) || 0) * (perRep / pace);
        }
      }
    }
    if (bf.cooldown?.enabled) {
      if (bf.cooldown.by === 'distance') total += getVal(bf.cooldown, 'distance', 'unit');
      else addByTime(bf.cooldown.duration, bf.cooldown.pace);
    }
    return Number(total.toFixed(2));
  };

  return { normalizeType, buildSummary, computeTotalDistance, toMinutes };
})();
</script>
