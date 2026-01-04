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
    return t || 'easy_run';
  };

  const buildSummary = (bf) => {
    const parts = [];
    if (bf.warmup?.enabled) {
      parts.push(`WU: ${bf.warmup.by==='distance' ? `${bf.warmup.distanceKm}km` : bf.warmup.duration}${bf.warmup.pace?` @${bf.warmup.pace}`:''}`);
    }
    if (bf.type === 'interval') {
      if (bf.interval.by==='distance') {
        parts.push(`${bf.interval.reps}x${bf.interval.repDistanceKm}km${bf.interval.pace ? ` @${bf.interval.pace}`:''}`);
      } else {
        parts.push(`${bf.interval.reps}x${bf.interval.repTime}${bf.interval.pace ? ` @${bf.interval.pace}`:''}`);
      }
      parts.push(`Rec ${bf.interval.recovery}`);
    } else if (bf.type === 'tempo') {
      if (bf.tempo.by==='distance') {
        parts.push(`${bf.tempo.distanceKm}km @${bf.tempo.pace} ${bf.tempo.effort}`);
      } else {
        parts.push(`${bf.tempo.duration} @${bf.tempo.pace} ${bf.tempo.effort}`);
      }
    } else if (bf.type === 'long_run') {
      parts.push(`Long Run ${bf.main.by==='distance'?bf.main.distanceKm+'km':bf.main.duration}`);
      if (bf.longRun?.fastFinish?.enabled) {
        parts.push(`+ ${bf.longRun.fastFinish.distanceKm}km Fast Finish`);
      }
    } else if (bf.type === 'easy_run') {
      parts.push(`Easy Run ${bf.main.by==='distance'?bf.main.distanceKm+'km':bf.main.duration}`);
    } else if (bf.type === 'strength') {
      parts.push(`Strength: ${(bf.strength?.plan || []).length} exercises`);
    } else if (bf.type === 'rest') {
      parts.push('Rest Day');
    }
    if (bf.cooldown?.enabled) {
      parts.push(`CD: ${bf.cooldown.by==='distance' ? `${bf.cooldown.distanceKm}km` : bf.cooldown.duration}${bf.cooldown.pace?` @${bf.cooldown.pace}`:''}`);
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
    if (bf.warmup?.enabled) {
      if (bf.warmup.by==='distance') total += Number(bf.warmup.distanceKm) || 0;
      else addByTime(bf.warmup.duration, bf.warmup.pace);
    }
    if (bf.type === 'easy_run' || bf.type === 'long_run') {
      if (bf.main.by==='distance') total += Number(bf.main.distanceKm) || 0;
      else addByTime(bf.main.duration, bf.main.pace);
      if (bf.type==='long_run' && bf.longRun?.fastFinish?.enabled) {
        total += Number(bf.longRun.fastFinish.distanceKm) || 0;
      }
    } else if (bf.type === 'tempo') {
      if (bf.tempo.by==='distance') total += Number(bf.tempo.distanceKm) || 0;
      else addByTime(bf.tempo.duration, bf.tempo.pace);
    } else if (bf.type === 'interval') {
      if (bf.interval.by==='distance') {
        total += (Number(bf.interval.reps)||0) * (Number(bf.interval.repDistanceKm)||0);
      } else {
        const perRep = toMinutes(bf.interval.repTime);
        const pace = toMinutes(bf.interval.pace);
        if (perRep > 0 && pace > 0) total += (Number(bf.interval.reps)||0) * (perRep/pace);
      }
    }
    if (bf.cooldown?.enabled) {
      if (bf.cooldown.by==='distance') total += Number(bf.cooldown.distanceKm) || 0;
      else addByTime(bf.cooldown.duration, bf.cooldown.pace);
    }
    return Number(total.toFixed(2));
  };

  return { normalizeType, buildSummary, computeTotalDistance, toMinutes };
})();
</script>
