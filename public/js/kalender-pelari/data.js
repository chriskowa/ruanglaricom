/**
 * Running Calendar - Data & Constants
 * Contains months, labels, default backgrounds, font lists, icon definitions,
 * Indonesian public holidays database, templates, and presets.
 */

const months = 'Januari Februari Maret April Mei Juni Juli Agustus September Oktober November Desember'.split(' ');
const twoMonthLabels = ['Jan — Feb', 'Mar — Apr', 'Mei — Jun', 'Jul — Agu', 'Sep — Okt', 'Nov — Des'];
const fourMonthLabels = ['Jan — Apr', 'Mei — Agu', 'Sep — Des'];

const bgBaseUrl = (typeof window !== 'undefined' && window.KP_BG_BASE_URL) ? window.KP_BG_BASE_URL : '/images/kalender-pelari/bg/';

const defaultBgFilenames = [
  '815920233_18619567672013478_7707120302547332417_n.jpg', // Sampul
  '819207920_18620464486013478_340093240344695805_n.jpg',  // Januari
  '825352127_18622491895013478_5874230830227099846_n.jpg',  // Februari
  '830540436_18146023228584739_5377399408712608694_n.jpg',  // Maret
  '840363425_18625537549013478_2717575911219471235_n.jpg',  // April
  '849697776_18628898209013478_5441502358773315659_n.jpg',  // Mei
  '849773131_18629307187013478_7691590249581363802_n.jpg',  // Juni
  '854624223_18630006562013478_8654858690142940290_n.jpg',  // Juli
  '862585476_18630953857013478_6407822674789936792_n.jpg',  // Agustus
  '863019870_18631378483013478_832084893925751159_n.jpg',  // September
  '870397930_18632498980013478_3637668635151763593_n.jpg',  // Oktober
  '874399957_18632800849013478_128493180862209871_n.jpg',  // November
  '880145360_18633549967013478_5585936414933662375_n.jpg'   // Desember
];

const defaultBgSources = defaultBgFilenames.map(file => bgBaseUrl + file);

let defaults = defaultBgSources.map(src => {
  const img = new Image();
  img.onload = () => { if (typeof render === 'function') render(); };
  img.src = src;
  return img;
});

function getDefaultBg(p) {
  if (!defaults.length) return null;
  const mpp = typeof monthsPerPage !== 'undefined' ? monthsPerPage.value : '1';
  if (p === -1) return defaults[0];
  let idx = p + 1;
  if (mpp === '2') idx = p * 2 + 1;
  else if (mpp === '4') idx = p * 4 + 1;
  else if (mpp === '12') idx = 0;
  return defaults[idx % defaults.length];
}

function updateAthleteDefaults(photoUrls) {
  if (!Array.isArray(photoUrls) || !photoUrls.length) return;
  defaults = photoUrls.map((src, i) => {
    if (!src) return defaults[i] || defaults[0];
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => { if (typeof render === 'function') render(); };
    img.src = src;
    return img;
  });
}

/* Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com
   Icons CC BY 4.0 (https://creativecommons.org/licenses/by/4.0/).
   Copyright 2024 Fonticons, Inc. Original SVG path data; recolored/scaled in this editor. */
const faIcons = {
  "person-running": { "w": 448, "h": 512, "paths": ["M320 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM125.7 175.5c9.9-9.9 23.4-15.5 37.5-15.5c1.9 0 3.8 .1 5.6 .3L137.6 254c-9.3 28 1.7 58.8 26.8 74.5l86.2 53.9-25.4 88.8c-4.9 17 5 34.7 22 39.6s34.7-5 39.6-22l28.7-100.4c5.9-20.6-2.6-42.6-20.7-53.9L238 299l30.9-82.4 5.1 12.3C289 264.7 323.9 288 362.7 288l21.3 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-21.3 0c-12.9 0-24.6-7.8-29.5-19.7l-6.3-15c-14.6-35.1-44.1-61.9-80.5-73.1l-48.7-15c-11.1-3.4-22.7-5.2-34.4-5.2c-31 0-60.8 12.3-82.7 34.3L57.4 153.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l23.1-23.1zM91.2 352L32 352c-17.7 0-32 14.3-32 32s14.3 32 32 32l69.6 0c19 0 36.2-11.2 43.9-28.5L157 361.6l-9.5-6c-17.5-10.9-30.5-26.8-37.9-44.9L91.2 352z"] },
  "person-biking": { "w": 640, "h": 512, "paths": ["M400 96a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm27.2 64l-61.8-48.8c-17.3-13.6-41.7-13.8-59.1-.3l-83.1 64.2c-30.7 23.8-28.5 70.8 4.3 91.6L288 305.1 288 416c0 17.7 14.3 32 32 32s32-14.3 32-32l0-128c0-10.7-5.3-20.7-14.2-26.6L295 232.9l60.3-48.5L396 217c5.7 4.5 12.7 7 20 7l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-52.8 0zM56 384a72 72 0 1 1 144 0A72 72 0 1 1 56 384zm200 0A128 128 0 1 0 0 384a128 128 0 1 0 256 0zm184 0a72 72 0 1 1 144 0 72 72 0 1 1 -144 0zm200 0a128 128 0 1 0 -256 0 128 128 0 1 0 256 0z"] },
  "shoe-prints": { "w": 640, "h": 512, "paths": ["M416 0C352.3 0 256 32 256 32l0 128c48 0 76 16 104 32s56 32 104 32c56.4 0 176-16 176-96S512 0 416 0zM128 96c0 35.3 28.7 64 64 64l32 0 0-128-32 0c-35.3 0-64 28.7-64 64zM288 512c96 0 224-48 224-128s-119.6-96-176-96c-48 0-76 16-104 32s-56 32-104 32l0 128s96.3 32 160 32zM0 416c0 35.3 28.7 64 64 64l32 0 0-128-32 0c-35.3 0-64 28.7-64 64z"] },
  "trophy": { "w": 576, "h": 512, "paths": ["M400 0L176 0c-26.5 0-48.1 21.8-47.1 48.2c.2 5.3 .4 10.6 .7 15.8L24 64C10.7 64 0 74.7 0 88c0 92.6 33.5 157 78.5 200.7c44.3 43.1 98.3 64.8 138.1 75.8c23.4 6.5 39.4 26 39.4 45.6c0 20.9-17 37.9-37.9 37.9L192 448c-17.7 0-32 14.3-32 32s14.3 32 32 32l192 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-26.1 0C337 448 320 431 320 410.1c0-19.6 15.9-39.2 39.4-45.6c39.9-11 93.9-32.7 138.2-75.8C542.5 245 576 180.6 576 88c0-13.3-10.7-24-24-24L446.4 64c.3-5.2 .5-10.4 .7-15.8C448.1 21.8 426.5 0 400 0zM48.9 112l84.4 0c9.1 90.1 29.2 150.3 51.9 190.6c-24.9-11-50.8-26.5-73.2-48.3c-32-31.1-58-76-63-142.3zM464.1 254.3c-22.4 21.8-48.3 37.3-73.2 48.3c22.7-40.3 42.8-100.5 51.9-190.6l84.4 0c-5.1 66.3-31.1 111.2-63 142.3z"] },
  "medal": { "w": 512, "h": 512, "paths": ["M4.1 38.2C1.4 34.2 0 29.4 0 24.6C0 11 11 0 24.6 0L133.9 0c11.2 0 21.7 5.9 27.4 15.5l68.5 114.1c-48.2 6.1-91.3 28.6-123.4 61.9L4.1 38.2zm503.7 0L405.6 191.5c-32.1-33.3-75.2-55.8-123.4-61.9L350.7 15.5C356.5 5.9 366.9 0 378.1 0L487.4 0C501 0 512 11 512 24.6c0 4.8-1.4 9.6-4.1 13.6zM80 336a176 176 0 1 1 352 0A176 176 0 1 1 80 336zm184.4-94.9c-3.4-7-13.3-7-16.8 0l-22.4 45.4c-1.4 2.8-4 4.7-7 5.1L168 298.9c-7.7 1.1-10.7 10.5-5.2 16l36.3 35.4c2.2 2.2 3.2 5.2 2.7 8.3l-8.6 49.9c-1.3 7.6 6.7 13.5 13.6 9.9l44.8-23.6c2.7-1.4 6-1.4 8.7 0l44.8 23.6c6.9 3.6 14.9-2.2 13.6-9.9l-8.6-49.9c-.5-3 .5-6.1 2.7-8.3l36.3-35.4c5.6-5.4 2.5-14.8-5.2-16l-50.1-7.3c-3-.4-5.7-2.4-7-5.1l-22.4-45.4z"] },
  "flag-checkered": { "w": 448, "h": 512, "paths": ["M32 0C49.7 0 64 14.3 64 32l0 16 69-17.2c38.1-9.5 78.3-5.1 113.5 12.5c46.3 23.2 100.8 23.2 147.1 0l9.6-4.8C423.8 28.1 448 43.1 448 66.1l0 279.7c0 13.3-8.3 25.3-20.8 30l-34.7 13c-46.2 17.3-97.6 14.6-141.7-7.4c-37.9-19-81.3-23.7-122.5-13.4L64 384l0 96c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-80 0-66L0 64 0 32C0 14.3 14.3 0 32 0zM64 187.1l64-13.9 0 65.5L64 252.6 64 318l48.8-12.2c5.1-1.3 10.1-2.4 15.2-3.3l0-63.9 38.9-8.4c8.3-1.8 16.7-2.5 25.1-2.1l0-64c13.6 .4 27.2 2.6 40.4 6.4l23.6 6.9 0 66.7-41.7-12.3c-7.3-2.1-14.8-3.4-22.3-3.8l0 71.4c21.8 1.9 43.3 6.7 64 14.4l0-69.8 22.7 6.7c13.5 4 27.3 6.4 41.3 7.4l0-64.2c-7.8-.8-15.6-2.3-23.2-4.5l-40.8-12 0-62c-13-3.8-25.8-8.8-38.2-15c-8.2-4.1-16.9-7-25.8-8.8l0 72.4c-13-.4-26 .8-38.7 3.6L128 173.2 128 98 64 114l0 73.1zM320 335.7c16.8 1.5 33.9-.7 50-6.8l14-5.2 0-71.7-7.9 1.8c-18.4 4.3-37.3 5.7-56.1 4.5l0 77.4zm64-149.4l0-70.8c-20.9 6.1-42.4 9.1-64 9.1l0 69.4c13.9 1.4 28 .5 41.7-2.6l22.3-5.2z"] },
  "stopwatch": { "w": 448, "h": 512, "paths": ["M176 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l16 0 0 34.4C92.3 113.8 16 200 16 304c0 114.9 93.1 208 208 208s208-93.1 208-208c0-41.8-12.3-80.7-33.5-113.2l24.1-24.1c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L355.7 143c-28.1-23-62.2-38.8-99.7-44.6L256 64l16 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L224 0 176 0zm72 192l0 128c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-128c0-13.3 10.7-24 24-24s24 10.7 24 24z"] },
  "route": { "w": 512, "h": 512, "paths": ["M512 96c0 50.2-59.1 125.1-84.6 155c-3.8 4.4-9.4 6.1-14.5 5L320 256c-17.7 0-32 14.3-32 32s14.3 32 32 32l96 0c53 0 96 43 96 96s-43 96-96 96l-276.4 0c8.7-9.9 19.3-22.6 30-36.8c6.3-8.4 12.8-17.6 19-27.2L416 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0c-53 0-96-43-96-96s43-96 96-96l39.8 0c-21-31.5-39.8-67.7-39.8-96c0-53 43-96 96-96s96 43 96 96zM117.1 489.1c-3.8 4.3-7.2 8.1-10.1 11.3l-1.8 2-.2-.2c-6 4.6-14.6 4-20-1.8C59.8 473 0 402.5 0 352c0-53 43-96 96-96s96 43 96 96c0 30-21.1 67-43.5 97.9c-10.7 14.7-21.7 28-30.8 38.5l-.6 .7zM128 352a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zM416 128a32 32 0 1 0 0-64 32 32 0 1 0 0 64z"] },
  "mountain": { "w": 512, "h": 512, "paths": ["M256 32c12.5 0 24.1 6.4 30.8 17L503.4 394.4c5.6 8.9 8.6 19.2 8.6 29.7c0 30.9-25 55.9-55.9 55.9L55.9 480C25 480 0 455 0 424.1c0-10.5 3-20.8 8.6-29.7L225.2 49c6.6-10.6 18.3-17 30.8-17zm65 192L256 120.4 176.9 246.5l18.3 24.4c6.4 8.5 19.2 8.5 25.6 0l25.6-34.1c6-8.1 15.5-12.8 25.6-12.8l49 0z"] },
  "dumbbell": { "w": 640, "h": 512, "paths": ["M96 64c0-17.7 14.3-32 32-32l32 0c17.7 0 32 14.3 32 32l0 160 0 64 0 160c0 17.7-14.3 32-32 32l-32 0c-17.7 0-32-14.3-32-32l0-64-32 0c-17.7 0-32-14.3-32-32l0-64c-17.7 0-32-14.3-32-32s14.3-32 32-32l0-64c0-17.7 14.3-32 32-32l32 0 0-64zm448 0l0 64 32 0c17.7 0 32 14.3 32 32l0 64c17.7 0 32 14.3 32 32s-14.3 32-32 32l0 64c0 17.7-14.3 32-32 32l-32 0 0 64c0 17.7-14.3 32-32 32l-32 0c-17.7 0-32-14.3-32-32l0-160 0-64 0-160c0-17.7 14.3-32 32-32l32 0c17.7 0 32 14.3 32 32zM416 224l0 64-192 0 0-64 192 0z"] },
  "heart": { "w": 512, "h": 512, "paths": ["M47.6 300.4L228.3 469.1c7.5 7 17.4 10.9 27.7 10.9s20.2-3.9 27.7-10.9L464.4 300.4c30.4-28.3 47.6-68 47.6-109.5v-5.8c0-69.9-50.5-129.5-119.4-141C347 36.5 300.6 51.4 268 84L256 96 244 84c-32.6-32.6-79-47.5-124.6-39.9C50.5 55.6 0 115.2 0 185.1v5.8c0 41.5 17.2 81.2 47.6 109.5z"] },
  "star": { "w": 576, "h": 512, "paths": ["M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"] },
  "fire": { "w": 448, "h": 512, "paths": ["M159.3 5.4c7.8-7.3 19.9-7.2 27.7 .1c27.6 25.9 53.5 53.8 77.7 84c11-14.4 23.5-30.1 37-42.9c7.9-7.4 20.1-7.4 28 .1c34.6 33 63.9 76.6 84.5 118c20.3 40.8 33.8 82.5 33.8 111.9C448 404.2 348.2 512 224 512C98.4 512 0 404.1 0 276.5c0-38.4 17.8-85.3 45.4-131.7C73.3 97.7 112.7 48.6 159.3 5.4zM225.7 416c25.3 0 47.7-7 68.8-21c42.1-29.4 53.4-88.2 28.1-134.4c-4.5-9-16-9.6-22.5-2l-25.2 29.3c-6.6 7.6-18.5 7.4-24.7-.5c-16.5-21-46-58.5-62.8-79.8c-6.3-8-18.3-8.1-24.7-.1c-33.8 42.5-50.8 69.3-50.8 99.4C112 375.4 162.6 416 225.7 416z"] },
  "bolt": { "w": 448, "h": 512, "paths": ["M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288l111.5 0L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-16.6-20.7-30-20.7l-111.5 0L349.4 44.6z"] },
  "location-dot": { "w": 384, "h": 512, "paths": ["M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"] },
  "calendar-days": { "w": 448, "h": 512, "paths": ["M128 0c17.7 0 32 14.3 32 32l0 32 128 0 0-32c0-17.7 14.3-32 32-32s32 14.3 32 32l0 32 48 0c26.5 0 48 21.5 48 48l0 48L0 160l0-48C0 85.5 21.5 64 48 64l48 0 0-32c0-17.7 14.3-32 32-32zM0 192l448 0 0 272c0 26.5-21.5 48-48 48L48 512c-26.5 0-48-21.5-48-48L0 192zm64 80l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm128 0l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0zM64 400l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0zm112 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16z"] },
  "camera": { "w": 512, "h": 512, "paths": ["M149.1 64.8L138.7 96 64 96C28.7 96 0 124.7 0 160L0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-256c0-35.3-28.7-64-64-64l-74.7 0L362.9 64.8C356.4 45.2 338.1 32 317.4 32L194.6 32c-20.7 0-39 13.2-45.5 32.8zM256 192a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"] },
  "sun": { "w": 512, "h": 512, "paths": ["M361.5 1.2c5 2.1 8.6 6.6 9.6 11.9L391 121l107.9 19.8c5.3 1 9.8 4.6 11.9 9.6s1.5 10.7-1.6 15.2L446.9 256l62.3 90.3c3.1 4.5 3.7 10.2 1.6 15.2s-6.6 8.6-11.9 9.6L391 391 371.1 498.9c-1 5.3-4.6 9.8-9.6 11.9s-10.7 1.5-15.2-1.6L256 446.9l-90.3 62.3c-4.5 3.1-10.2 3.7-15.2 1.6s-8.6-6.6-9.6-11.9L121 391 13.1 371.1c-5.3-1-9.8-4.6-11.9-9.6s-1.5-10.7 1.6-15.2L65.1 256 2.8 165.7c-3.1-4.5-3.7-10.2-1.6-15.2s6.6-8.6 11.9-9.6L121 121 140.9 13.1c1-5.3 4.6-9.8 9.6-11.9s10.7-1.5 15.2 1.6L256 65.1 346.3 2.8c4.5-3.1 10.2-3.7 15.2-1.6zM160 256a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zm224 0a128 128 0 1 0 -256 0 128 128 0 1 0 256 0z"] },
  "moon": { "w": 384, "h": 512, "paths": ["M223.5 32C100 32 0 132.3 0 256S100 480 223.5 480c60.6 0 115.5-24.2 155.8-63.4c5-4.9 6.3-12.5 3.1-18.7s-10.1-9.7-17-8.5c-9.8 1.7-19.8 2.6-30.1 2.6c-96.9 0-175.5-78.8-175.5-176c0-65.8 36-123.1 89.3-153.3c6.1-3.5 9.2-10.5 7.7-17.3s-7.3-11.9-14.3-12.5c-6.3-.5-12.6-.8-19-.8z"] },
  "leaf": { "w": 512, "h": 512, "paths": ["M272 96c-78.6 0-145.1 51.5-167.7 122.5c33.6-17 71.5-26.5 111.7-26.5l88 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-16 0-72 0s0 0 0 0c-16.6 0-32.7 1.9-48.3 5.4c-25.9 5.9-49.9 16.4-71.4 30.7c0 0 0 0 0 0C38.3 298.8 0 364.9 0 440l0 16c0 13.3 10.7 24 24 24s24-10.7 24-24l0-16c0-48.7 20.7-92.5 53.8-123.2C121.6 392.3 190.3 448 272 448l1 0c132.1-.7 239-130.9 239-291.4c0-42.6-7.5-83.1-21.1-119.6c-2.6-6.9-12.7-6.6-16.2-.1C455.9 72.1 418.7 96 376 96L272 96z"] },
  "plane": { "w": 576, "h": 512, "paths": ["M482.3 192c34.2 0 93.7 29 93.7 64c0 36-59.5 64-93.7 64l-116.6 0L265.2 495.9c-5.7 10-16.3 16.1-27.8 16.1l-56.2 0c-10.6 0-18.3-10.2-15.4-20.4l49-171.6L112 320 68.8 377.6c-3 4-7.8 6.4-12.8 6.4l-42 0c-7.8 0-14-6.3-14-14c0-1.3 .2-2.6 .5-3.9L32 256 .5 145.9c-.4-1.3-.5-2.6-.5-3.9c0-7.8 6.3-14 14-14l42 0c5 0 9.8 2.4 12.8 6.4L112 192l102.9 0-49-171.6C162.9 10.2 170.6 0 181.2 0l56.2 0c11.5 0 22.1 6.2 27.8 16.1L365.7 192l116.6 0z"] },
  "water": { "w": 576, "h": 512, "paths": ["M269.5 69.9c11.1-7.9 25.9-7.9 37 0C329 85.4 356.5 96 384 96c26.9 0 55.4-10.8 77.4-26.1c0 0 0 0 0 0c11.9-8.5 28.1-7.8 39.2 1.7c14.4 11.9 32.5 21 50.6 25.2c17.2 4 27.9 21.2 23.9 38.4s-21.2 27.9-38.4 23.9c-24.5-5.7-44.9-16.5-58.2-25C449.5 149.7 417 160 384 160c-31.9 0-60.6-9.9-80.4-18.9c-5.8-2.7-11.1-5.3-15.6-7.7c-4.5 2.4-9.7 5.1-15.6 7.7c-19.8 9-48.5 18.9-80.4 18.9c-33 0-65.5-10.3-94.5-25.8c-13.4 8.4-33.7 19.3-58.2 25c-17.2 4-34.4-6.7-38.4-23.9s6.7-34.4 23.9-38.4C42.8 92.6 61 83.5 75.3 71.6c11.1-9.5 27.3-10.1 39.2-1.7c0 0 0 0 0 0C136.7 85.2 165.1 96 192 96c27.5 0 55-10.6 77.5-26.1zm37 288C329 373.4 356.5 384 384 384c26.9 0 55.4-10.8 77.4-26.1c0 0 0 0 0 0c11.9-8.5 28.1-7.8 39.2 1.7c14.4 11.9 32.5 21 50.6 25.2c17.2 4 27.9 21.2 23.9 38.4s-21.2 27.9-38.4 23.9c-24.5-5.7-44.9-16.5-58.2-25C449.5 437.7 417 448 384 448c-31.9 0-60.6-9.9-80.4-18.9c-5.8-2.7-11.1-5.3-15.6-7.7c-4.5 2.4-9.7 5.1-15.6 7.7c-19.8 9-48.5 18.9-80.4 18.9c-33 0-65.5-10.3-94.5-25.8c-13.4 8.4-33.7 19.3-58.2 25c-17.2 4-34.4-6.7-38.4-23.9s6.7-34.4 23.9-38.4c18.1-4.2 36.2-13.3 50.6-25.2c11.1-9.4 27.3-10.1 39.2-1.7c0 0 0 0 0 0C136.7 373.2 165.1 384 192 384c27.5 0 55-10.6 77.5-26.1c11.1-7.9 25.9-7.9 37 0zm0-144C329 229.4 356.5 240 384 240c26.9 0 55.4-10.8 77.4-26.1c0 0 0 0 0 0c11.9-8.5 28.1-7.8 39.2 1.7c14.4 11.9 32.5 21 50.6 25.2c17.2 4 27.9 21.2 23.9 38.4s-21.2 27.9-38.4 23.9c-24.5-5.7-44.9-16.5-58.2-25C449.5 293.7 417 304 384 304c-31.9 0-60.6-9.9-80.4-18.9c-5.8-2.7-11.1-5.3-15.6-7.7c-4.5 2.4-9.7 5.1-15.6 7.7c-19.8 9-48.5 18.9-80.4 18.9c-33 0-65.5-10.3-94.5-25.8c-13.4 8.4-33.7 19.3-58.2 25c-17.2 4-34.4-6.7-38.4-23.9s6.7-34.4 23.9-38.4c18.1-4.2 36.2-13.3 50.6-25.2c11.1-9.5 27.3-10.1 39.2-1.7c0 0 0 0 0 0C136.7 229.2 165.1 240 192 240c27.5 0 55-10.6 77.5-26.1c11.1-7.9 25.9-7.9 37 0z"] },
  "mug-hot": { "w": 512, "h": 512, "paths": ["M88 0C74.7 0 64 10.7 64 24c0 38.9 23.4 59.4 39.1 73.1l1.1 1C120.5 112.3 128 119.9 128 136c0 13.3 10.7 24 24 24s24-10.7 24-24c0-38.9-23.4-59.4-39.1-73.1l-1.1-1C119.5 47.7 112 40.1 112 24c0-13.3-10.7-24-24-24zM32 192c-17.7 0-32 14.3-32 32L0 416c0 53 43 96 96 96l192 0c53 0 96-43 96-96l16 0c61.9 0 112-50.1 112-112s-50.1-112-112-112l-48 0L32 192zm352 64l16 0c26.5 0 48 21.5 48 48s-21.5 48-48 48l-16 0 0-96zM224 24c0-13.3-10.7-24-24-24s-24 10.7-24 24c0 38.9 23.4 59.4 39.1 73.1l1.1 1C232.5 112.3 240 119.9 240 136c0 13.3 10.7 24 24 24s24-10.7 24-24c0-38.9-23.4-59.4-39.1-73.1l-1.1-1C231.5 47.7 224 40.1 224 24z"] },
  "music": { "w": 512, "h": 512, "paths": ["M499.1 6.3c8.1 6 12.9 15.6 12.9 25.7l0 72 0 264c0 44.2-43 80-96 80s-96-35.8-96-80s43-80 96-80c11.2 0 22 1.6 32 4.6L448 147 192 223.8 192 432c0 44.2-43 80-96 80s-96-35.8-96-80s43-80 96-80c11.2 0 22 1.6 32 4.6L128 200l0-72c0-14.1 9.3-26.6 22.8-30.7l320-96c9.7-2.9 20.2-1.1 28.3 5z"] },
  "book": { "w": 448, "h": 512, "paths": ["M96 0C43 0 0 43 0 96L0 416c0 53 43 96 96 96l288 0 32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-64c17.7 0 32-14.3 32-32l0-320c0-17.7-14.3-32-32-32L384 0 96 0zm0 384l256 0 0 64L96 448c-17.7 0-32-14.3-32-32s14.3-32 32-32zm32-240c0-8.8 7.2-16 16-16l192 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-192 0c-8.8 0-16-7.2-16-16zm16 48l192 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-192 0c-8.8 0-16-7.2-16-16s7.2-16 16-16z"] },
  "gift": { "w": 512, "h": 512, "paths": ["M190.5 68.8L225.3 128l-1.3 0-72 0c-22.1 0-40-17.9-40-40s17.9-40 40-40l2.2 0c14.9 0 28.8 7.9 36.3 20.8zM64 88c0 14.4 3.5 28 9.6 40L32 128c-17.7 0-32 14.3-32 32l0 64c0 17.7 14.3 32 32 32l448 0c17.7 0 32-14.3 32-32l0-64c0-17.7-14.3-32-32-32l-41.6 0c6.1-12 9.6-25.6 9.6-40c0-48.6-39.4-88-88-88l-2.2 0c-31.9 0-61.5 16.9-77.7 44.4L256 85.5l-24.1-41C215.7 16.9 186.1 0 154.2 0L152 0C103.4 0 64 39.4 64 88zm336 0c0 22.1-17.9 40-40 40l-72 0-1.3 0 34.8-59.2C329.1 55.9 342.9 48 357.8 48l2.2 0c22.1 0 40 17.9 40 40zM32 288l0 176c0 26.5 21.5 48 48 48l144 0 0-224L32 288zM288 512l144 0c26.5 0 48-21.5 48-48l0-176-192 0 0 224z"] },
  "cake-candles": { "w": 448, "h": 512, "paths": ["M86.4 5.5L61.8 47.6C58 54.1 56 61.6 56 69.2L56 72c0 22.1 17.9 40 40 40s40-17.9 40-40l0-2.8c0-7.6-2-15-5.8-21.6L105.6 5.5C103.6 2.1 100 0 96 0s-7.6 2.1-9.6 5.5zm128 0L189.8 47.6c-3.8 6.5-5.8 14-5.8 21.6l0 2.8c0 22.1 17.9 40 40 40s40-17.9 40-40l0-2.8c0-7.6-2-15-5.8-21.6L233.6 5.5C231.6 2.1 228 0 224 0s-7.6 2.1-9.6 5.5zM317.8 47.6c-3.8 6.5-5.8 14-5.8 21.6l0 2.8c0 22.1 17.9 40 40 40s40-17.9 40-40l0-2.8c0-7.6-2-15-5.8-21.6L361.6 5.5C359.6 2.1 356 0 352 0s-7.6 2.1-9.6 5.5L317.8 47.6zM128 176c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 48c-35.3 0-64 28.7-64 64l0 71c8.3 5.2 18.1 9 28.8 9c13.5 0 27.2-6.1 38.4-13.4c5.4-3.5 9.9-7.1 13-9.7c1.5-1.3 2.7-2.4 3.5-3.1c.4-.4 .7-.6 .8-.8l.1-.1s0 0 0 0s0 0 0 0s0 0 0 0s0 0 0 0c3.1-3.2 7.4-4.9 11.9-4.8s8.6 2.1 11.6 5.4c0 0 0 0 0 0s0 0 0 0l.1 .1c.1 .1 .4 .4 .7 .7c.7 .7 1.7 1.7 3.1 3c2.8 2.6 6.8 6.1 11.8 9.5c10.2 7.1 23 13.1 36.3 13.1s26.1-6 36.3-13.1c5-3.5 9-6.9 11.8-9.5c1.4-1.3 2.4-2.3 3.1-3c.3-.3 .6-.6 .7-.7l.1-.1c3-3.5 7.4-5.4 12-5.4s9 2 12 5.4l.1 .1c.1 .1 .4 .4 .7 .7c.7 .7 1.7 1.7 3.1 3c2.8 2.6 6.8 6.1 11.8 9.5c10.2 7.1 23 13.1 36.3 13.1s26.1-6 36.3-13.1c5-3.5 9-6.9 11.8-9.5c1.4-1.3 2.4-2.3 3.1-3c.3-.3 .6-.6 .7-.7l.1-.1c2.9-3.4 7.1-5.3 11.6-5.4s8.7 1.6 11.9 4.8c0 0 0 0 0 0s0 0 0 0s0 0 0 0l.1 .1c.2 .2 .4 .4 .8 .8c.8 .7 1.9 1.8 3.5 3.1c3.1 2.6 7.5 6.2 13 9.7c11.2 7.3 24.9 13.4 38.4 13.4c10.7 0 20.5-3.9 28.8-9l0-71c0-35.3-28.7-64-64-64l0-48c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 48-64 0 0-48c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 48-64 0 0-48zM448 394.6c-8.5 3.3-18.2 5.4-28.8 5.4c-22.5 0-42.4-9.9-55.8-18.6c-4.1-2.7-7.8-5.4-10.9-7.8c-2.8 2.4-6.1 5-9.8 7.5C329.8 390 310.6 400 288 400s-41.8-10-54.6-18.9c-3.5-2.4-6.7-4.9-9.4-7.2c-2.7 2.3-5.9 4.7-9.4 7.2C201.8 390 182.6 400 160 400s-41.8-10-54.6-18.9c-3.7-2.6-7-5.2-9.8-7.5c-3.1 2.4-6.8 5.1-10.9 7.8C71.2 390.1 51.3 400 28.8 400c-10.6 0-20.3-2.2-28.8-5.4L0 480c0 17.7 14.3 32 32 32l384 0c17.7 0 32-14.3 32-32l0-85.4z"] },
  "wand-magic-sparkles": { "w": 576, "h": 512, "paths": ["M234.7 42.7L197 56.8c-3 1.1-5 4-5 7.2s2 6.1 5 7.2l37.7 14.1L248.8 123c1.1 3 4 5 7.2 5s6.1-2 7.2-5l14.1-37.7L315 71.2c3-1.1 5-4 5-7.2s-2-6.1-5-7.2L277.3 42.7 263.2 5c-1.1-3-4-5-7.2-5s-6.1 2-7.2 5L234.7 42.7zM46.1 395.4c-18.7 18.7-18.7 49.1 0 67.9l34.6 34.6c18.7 18.7 49.1 18.7 67.9 0L529.9 116.5c18.7-18.7 18.7-49.1 0-67.9L495.3 14.1c-18.7-18.7-49.1-18.7-67.9 0L46.1 395.4zM484.6 82.6l-105 105-23.3-23.3 105-105 23.3 23.3zM7.5 117.2C3 118.9 0 123.2 0 128s3 9.1 7.5 10.8L64 160l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L128 160l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L128 96 106.8 39.5C105.1 35 100.8 32 96 32s-9.1 3-10.8 7.5L64 96 7.5 117.2zm352 256c-4.5 1.7-7.5 6-7.5 10.8s3 9.1 7.5 10.8L416 416l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L480 416l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L480 352l-21.2-56.5c-1.7-4.5-6-7.5-10.8-7.5s-9.1 3-10.8 7.5L416 352l-56.5 21.2z"] },
  "circle-check": { "w": 512, "h": 512, "paths": ["M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"] },
  "compass": { "w": 512, "h": 512, "paths": ["M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm50.7-186.9L162.4 380.6c-19.4 7.5-38.5-11.6-31-31l55.5-144.3c3.3-8.5 9.9-15.1 18.4-18.4l144.3-55.5c19.4-7.5 38.5 11.6 31 31L325.1 306.7c-3.2 8.5-9.9 15.1-18.4 18.4zM288 256a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"] }
};

const fontFamilies = [
  'Plus Jakarta Sans', 'Inter', 'DM Sans', 'Poppins', 'Montserrat', 'Nunito', 'Lato', 'Roboto', 'Open Sans', 'Source Sans 3',
  'Work Sans', 'Manrope', 'Outfit', 'Space Grotesk', 'Oswald', 'Bebas Neue', 'Barlow Condensed', 'Rajdhani', 'Anton',
  'Archivo Black', 'Playfair Display', 'Lora', 'Merriweather', 'Libre Baskerville', 'Cormorant Garamond', 'DM Serif Display',
  'Caveat', 'Dancing Script', 'Pacifico', 'Sacramento', 'Space Mono', 'IBM Plex Mono', 'Arial', 'Georgia', 'Verdana', 'Courier New'
];
const validFonts = fontFamilies;

const templateDefinitions = [
  ['poster12m', 'Poster 12 Bulan (A3+)', '1 lembar A3+ (4 baris × 3 kolom) & hero atlet'],
  ['classic', 'Running Classic', 'Tata letak klasik seimbang'],
  ['minimal', 'Minimal Editorial', 'Garis tipis halus & ruang lapang'],
  ['bold', 'Sport Bold', 'Header atletik masif & kontras tinggi'],
  ['botanical', 'Botanical Calm', 'Aksen daun organik, nuansa hijau alam'],
  ['night', 'Midnight Runner', 'Dark mode kontras neon cyberpunk'],
  ['retro', 'Retro Paper 70s', 'Nuansa koran antik vintage hangat'],
  ['split', 'Split 50/50', 'Foto vertikal penuh kiri, kalender kanan'],
  ['magazine', 'Magazine Cover', 'Judul tipografi besar menimpa foto'],
  ['polaroid', 'Polaroid Gallery', 'Foto frame polaroid miring artistik'],
  ['bottom', 'Photo Bottom', 'Posisi terbalik: kalender atas, foto bawah'],
  ['sidebar', 'Compact Sidebar', 'Foto luas di kiri, strip kalender ramping'],
  ['modernist', 'Swiss Modernist', 'Blok warna geometris Bauhaus asimetris'],
  ['neon', 'Neon Track', 'Garis lintasan lari neon elektrik'],
  ['nordic', 'Nordic Clean', 'Minimalis Skandinavia, abu-abu es & navy'],
  ['hero', 'Hero Showcase', 'Foto ekstra besar 72%, kalender ringkas'],
  ['journal', "Runner's Journal", 'Gaya buku jurnal & planner maraton bergaris'],
  ['stamp', 'Postage Stamp', 'Bingkai prangko perforasi & cap pos lari'],
  ['filmstrip', 'Cinematic Film', 'Frame roll film 35mm & sprocket sinematik'],
  ['badge', 'Marathon Bib', 'Nomor dada lomba maraton & chip timing'],
  ['arch', 'Arch Window', 'Lengkung kubah arsitektur elegan terakota'],
  ['diagonal', 'Diagonal Motion', 'Sudut dinamis miring 8° membelah layout'],
  ['metro', 'Metro Transit', 'Gaya peta rute subway & stasiun kota maraton'],
  ['duotone', 'Duotone Poster', 'Poster grafis kontras tinggi tipografi vertikal'],
  ['kraft', 'Vintage Kraft', 'Kertas cokelat daur ulang, selotip & mesin tik'],
  ['strava', 'Strava Heatmap', 'Dark feed Strava, garis rute GPS menyala & stats'],
  ['club', 'Y2K Run Club', 'Acid lime neon, stempel run club & tipografi Gen-Z'],
  ['majors', 'World Majors', 'Paspor 7-Star World Marathon Majors & pita bintang'],
  ['topography', 'Trail Topo Peak', 'Garis kontur elevasi gunung & profil tanjakan'],
  ['crown', 'KOM Crown', 'Strava King of the Mountain, chevron & leaderboard'],
  ['blueprint', 'Athletic Blueprint', 'Cetak biru arsitektur CAD, grid milimeter teknis'],
  ['acid', 'Acid Cyber Runner', 'Cyberpunk neon kuning-ungu, italic speed miring'],
  ['taper', 'Race Week & Taper', 'Countdown race H-21 s/d H-0, carb load & split pace'],
  ['infographic', 'Year in Sport 365', 'Poster rekap tahunan Strava, metrik raksasa KM & jam'],
  ['shoebox', 'Gear & Shoe Closet', 'Label dus sneaker, barcode & tracker kilometer sol'],
  ['zine', 'Indie Runner Zine', 'DIY punk zine fotokopi, tekstur halftone & selotip'],
  ['sunset', 'Golden Hour Miles', 'Lari subuh & senja, gradasi violet ke amber hangat']
];

const palettes = {
  poster12m: ['#e63946', '#ffffff'],
  classic: ['#f45123', '#ffffff'],
  minimal: ['#454b45', '#ffffff'],
  bold: ['#d8ff00', '#ffffff'],
  botanical: ['#46705b', '#f4f6ee'],
  night: ['#78adff', '#111c2e'],
  retro: ['#a8472b', '#f5ecda'],
  split: ['#ff4d4f', '#ffffff'],
  magazine: ['#e60023', '#ffffff'],
  polaroid: ['#3b5998', '#faf8f5'],
  bottom: ['#008080', '#ffffff'],
  sidebar: ['#ff7a00', '#f9f9fb'],
  modernist: ['#e50914', '#ffffff'],
  neon: ['#00f0ff', '#0d1117'],
  nordic: ['#2b4c7e', '#f5f7fa'],
  hero: ['#ffb703', '#ffffff'],
  journal: ['#8c5e3c', '#fbf8f1'],
  stamp: ['#c43b27', '#f7f4ec'],
  filmstrip: ['#202020', '#fafafa'],
  badge: ['#0066ff', '#ffffff'],
  arch: ['#b35438', '#fbf8f3'],
  diagonal: ['#059669', '#ffffff'],
  metro: ['#1a365d', '#f8fafc'],
  duotone: ['#4338ca', '#ffffff'],
  kraft: ['#4a2c11', '#ede5d8'],
  strava: ['#fc5200', '#0e1117'],
  club: ['#ccff00', '#111111'],
  majors: ['#002b49', '#ffffff'],
  topography: ['#1e392a', '#f3f4ed'],
  crown: ['#ffb800', '#ffffff'],
  blueprint: ['#0a3871', '#e8f1fc'],
  acid: ['#b8ff00', '#140026'],
  taper: ['#dc2626', '#ffffff'],
  infographic: ['#ea580c', '#fafafa'],
  shoebox: ['#e11d48', '#ffffff'],
  zine: ['#1c1917', '#f5f5f0'],
  sunset: ['#ff6b35', '#1f142e']
};

const collageLayoutDefinitions = [
  ['1', '1 Foto', 'Foto tunggal penuh'],
  ['2-v', '2 Foto (Kiri-Kanan)', 'Dua foto berdampingan vertikal'],
  ['2-h', '2 Foto (Atas-Bawah)', 'Dua foto bertumpuk horizontal'],
  ['3-left', '3 Foto (1 Kiri + 2 Kanan)', '1 foto besar di kiri, 2 foto di kanan'],
  ['3-top', '3 Foto (1 Atas + 2 Bawah)', '1 foto panorama atas, 2 di bawah'],
  ['3-cols', '3 Foto (3 Kolom)', 'Tiga kolom foto vertikal sejajar'],
  ['4-grid', '4 Foto (Grid 2×2)', 'Empat foto kuadran simetris'],
  ['4-hero', '4 Foto (1 Kiri + 3 Kanan)', '1 foto besar di kiri, 3 vertikal di kanan'],
  ['5-mosaic', '5 Foto (Mosaik 1+4)', '1 foto hero kiri, 4 foto grid kanan'],
  ['5-split', '5 Foto (2 Atas + 3 Bawah)', '2 foto di atas, 3 foto di bawah']
];

const elementLabels = {
  name: 'Nama', year: 'Tahun', photo: 'Bingkai foto',
  photo0: 'Foto slot 1', photo1: 'Foto slot 2', photo2: 'Foto slot 3', photo3: 'Foto slot 4', photo4: 'Foto slot 5',
  heading: 'Judul kalender', subtitle: 'Subjudul',
  month: 'Judul bulan', month0: 'Judul bulan pertama', month1: 'Judul bulan kedua',
  calendar: 'Tabel tanggal', calendar0: 'Kalender bulan pertama', calendar1: 'Kalender bulan kedua',
  notes: 'Libur & catatan', notes0: 'Catatan bulan pertama', notes1: 'Catatan bulan kedua',
  agenda: 'Race / agenda', targets: 'Target lari', weekly: 'Rekap mingguan', tracker: 'Tracker lari'
};

const iconLabels = {
  'person-running': 'Lari', 'person-biking': 'Sepeda', 'shoe-prints': 'Jejak sepatu', 'trophy': 'Piala', 'medal': 'Medali',
  'flag-checkered': 'Finish', 'stopwatch': 'Stopwatch waktu', 'route': 'Rute', 'mountain': 'Gunung alam',
  'dumbbell': 'Latihan beban', 'heart': 'Hati', 'star': 'Bintang', 'fire': 'Api', 'bolt': 'Petir energi',
  'location-dot': 'Lokasi', 'calendar-days': 'Kalender', 'camera': 'Kamera foto', 'sun': 'Matahari', 'moon': 'Bulan malam',
  'leaf': 'Daun alam', 'plane': 'Pesawat liburan', 'water': 'Air ombak', 'mug-hot': 'Kopi', 'music': 'Musik',
  'book': 'Buku', 'gift': 'Hadiah', 'cake-candles': 'Ulang tahun', 'wand-magic-sparkles': 'Magic kilau',
  'circle-check': 'Selesai centang', 'compass': 'Kompas'
};

const stylePresets = {
  race: { template: 'bold', font: 'Barlow Condensed', accent: '#d8ff00', paper: '#ffffff', icon: 'person-running', label: 'Race Day' },
  editorial: { template: 'retro', font: 'Playfair Display', accent: '#a8472b', paper: '#f5ecda', icon: 'star', label: 'Editorial' },
  trail: { template: 'botanical', font: 'DM Sans', accent: '#35634b', paper: '#f4f6ee', icon: 'mountain', label: 'Trail Club' },
  night: { template: 'night', font: 'Space Grotesk', accent: '#91beff', paper: '#111c2e', icon: 'moon', label: 'Night Miles' }
};

// --- INDONESIAN PUBLIC HOLIDAYS DATABASE ---
function getHolidays(y, m) {
  const data = {
    2025: {
      0: [{ d: 1, n: "Tahun Baru 2025 M" }, { d: 27, n: "Isra Mikraj Nabi Muhammad SAW" }, { d: 29, n: "Tahun Baru Imlek 2576" }],
      2: [{ d: 29, n: "Hari Suci Nyepi Saka 1947" }, { d: 31, n: "Hari Raya Idulfitri 1446 H" }],
      3: [{ d: 1, n: "Hari Raya Idulfitri 1446 H" }, { d: 18, n: "Wafat Yesus Kristus" }, { d: 20, n: "Paskah" }],
      4: [{ d: 1, n: "Hari Buruh Internasional" }, { d: 12, n: "Hari Raya Waisak 2569" }, { d: 29, n: "Kenaikan Yesus Kristus" }],
      5: [{ d: 1, n: "Hari Lahir Pancasila" }, { d: 6, n: "Hari Raya Iduladha 1446 H" }, { d: 27, n: "Tahun Baru Islam 1447 H" }],
      7: [{ d: 17, n: "Hari Kemerdekaan RI" }],
      8: [{ d: 5, n: "Maulid Nabi Muhammad SAW" }],
      11: [{ d: 25, n: "Hari Raya Natal" }]
    },
    2026: {
      0: [{ d: 1, n: "Tahun Baru 2026 M" }, { d: 16, n: "Isra Mikraj Nabi Muhammad SAW" }],
      1: [{ d: 17, n: "Tahun Baru Imlek 2577" }],
      2: [{ d: 19, n: "Hari Suci Nyepi Saka 1948" }, { d: 21, n: "Hari Raya Idulfitri 1447 H" }, { d: 22, n: "Hari Raya Idulfitri 1447 H" }],
      3: [{ d: 3, n: "Wafat Yesus Kristus (Jumat Agung)" }, { d: 5, n: "Paskah" }],
      4: [{ d: 1, n: "Hari Buruh Internasional" }, { d: 14, n: "Kenaikan Yesus Kristus" }, { d: 27, n: "Hari Raya Iduladha 1447 H" }, { d: 31, n: "Hari Raya Waisak 2570" }],
      5: [{ d: 1, n: "Hari Lahir Pancasila" }, { d: 16, n: "Tahun Baru Islam 1448 H" }],
      7: [{ d: 17, n: "Hari Kemerdekaan RI Ke-81" }, { d: 25, n: "Maulid Nabi Muhammad SAW" }],
      11: [{ d: 25, n: "Hari Raya Natal" }]
    },
    2027: {
      0: [{ d: 1, n: "Tahun Baru 2027 M" }, { d: 5, n: "Isra Mikraj Nabi Muhammad SAW" }],
      1: [{ d: 6, n: "Tahun Baru Imlek 2578" }],
      2: [{ d: 8, n: "Hari Suci Nyepi Saka 1949" }, { d: 10, n: "Hari Raya Idulfitri 1448 H" }, { d: 11, n: "Hari Raya Idulfitri 1448 H" }, { d: 26, n: "Wafat Yesus Kristus" }, { d: 28, n: "Paskah" }],
      4: [{ d: 1, n: "Hari Buruh Internasional" }, { d: 6, n: "Kenaikan Yesus Kristus" }, { d: 17, n: "Hari Raya Iduladha 1448 H" }, { d: 20, n: "Hari Raya Waisak 2571" }],
      5: [{ d: 1, n: "Hari Lahir Pancasila" }, { d: 6, n: "Tahun Baru Islam 1449 H" }],
      7: [{ d: 15, n: "Maulid Nabi Muhammad SAW" }, { d: 17, n: "Hari Kemerdekaan RI Ke-82" }],
      11: [{ d: 25, n: "Hari Raya Natal" }, { d: 26, n: "Isra Mikraj 1449 H" }]
    }
  };

  if (data[y] && data[y][m]) return data[y][m];

  // Default national holidays falling on fixed dates every year
  const fixed = [];
  if (m === 0) fixed.push({ d: 1, n: "Tahun Baru Masehi" });
  if (m === 4) fixed.push({ d: 1, n: "Hari Buruh Internasional" });
  if (m === 5) fixed.push({ d: 1, n: "Hari Lahir Pancasila" });
  if (m === 7) fixed.push({ d: 17, n: "Hari Kemerdekaan RI" });
  if (m === 11) fixed.push({ d: 25, n: "Hari Raya Natal" });
  return fixed;
}

function isHoliday(y, m, d) {
  const list = getHolidays(y, m);
  return list.some(h => h.d === d);
}
