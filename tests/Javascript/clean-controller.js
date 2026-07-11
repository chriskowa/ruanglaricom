// tests/Javascript/clean-controller.js
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const filepath = path.join(__dirname, '../../app/Http/Controllers/FormAnalyzerController.php');

const content = fs.readFileSync(filepath, 'utf8');

// We want to keep everything up to "private function normalizeBiomechMetrics"
const target = '    private function normalizeBiomechMetrics(';
const index = content.indexOf(target);

if (index === -1) {
    console.error("normalizeBiomechMetrics not found!");
    process.exit(1);
}

// Slice the content and append a closing brace for the class
const truncated = content.substring(0, index) + "}\n";

fs.writeFileSync(filepath, truncated, 'utf8');
console.log("Successfully cleaned up FormAnalyzerController.php!");
