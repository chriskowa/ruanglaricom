import { createKpEditor } from './KalenderPelari/bootstrap.js';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('kp-editor-root');
    if (!root) {
        return;
    }
    createKpEditor(root);
});
