
const trainingProfile = {
    value: {
        paces: {
            E: '5:30',
            M: '5:00',
            T: '4:30',
            I: '4:00',
            R: '3:30'
        }
    }
};

const formatPace = (val) => val; 

const calculateRecommendedPace = (type) => {
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
    
    const key = map[t]; 
    if (!key) return null;

    let val = trainingProfile.value?.paces?.[key];
    if (!val && key === 'M') val = trainingProfile.value?.paces?.['E'];
    
    return val ? (formatPace(val) + ' /km') : null;
};

const testCases = [
    'easy_run', 'tempo', 'interval', 'long_run', 'strength', 'rest', 'random'
];

testCases.forEach(type => {
    console.log(`${type}: ${calculateRecommendedPace(type)}`);
});
