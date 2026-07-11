// tests/Javascript/test-import.js
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const filepath = path.join(__dirname, '../../resources/views/admin/running-analysis/capture.blade.php');
const content = fs.readFileSync(filepath, 'utf8');

// Extract script block 5 (the module script)
const regex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let index = 1;
let moduleCode = '';

while ((match = regex.exec(content)) !== null) {
    if (index === 5) {
        moduleCode = match[1];
        break;
    }
    index++;
}

if (!moduleCode) {
    console.error("Could not find script block 5!");
    process.exit(1);
}

// Clean Blade directives
const cleaned = moduleCode
    .replace(/\{\{\s*url\([^)]*\)\s*\}\}/g, '"http://localhost"')
    .replace(/\{\{\s*csrf_token\(\)\s*\}\}/g, '"token"')
    .replace(/\{\{\s*url\([^)]*finalize'\)\s*\}\}/g, '"http://localhost"')
    .replace(/\{\{\s*url\([^)]*sessions[^)]*\)\s*\}\}/g, '"http://localhost"')
    .replace(/\{\{\s*url\([^)]*trials[^)]*\)\s*\}\}/g, '"http://localhost"')
    .replace(/\{\!\!\s*\$[^!]*\!\!\}/g, '{}')
    .replace(/\{\{\s*\$[^}]*\}\}/g, '"value"');

const tempFile = path.join(__dirname, 'temp-capture.js');
fs.writeFileSync(tempFile, cleaned, 'utf8');

console.log("Written cleaned script block to temp-capture.js. Importing...");

import('./temp-capture.js')
    .then(() => {
        console.log("Successfully imported! No syntax errors found.");
        fs.unlinkSync(tempFile);
    })
    .catch(err => {
        console.error("Import failed with syntax error:");
        console.error(err);
    });
