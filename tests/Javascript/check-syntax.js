// tests/Javascript/check-syntax.js
const fs = require('fs');
const path = require('path');

const filepath = path.join(__dirname, '../../resources/views/admin/running-analysis/capture.blade.php');
const content = fs.readFileSync(filepath, 'utf8');

// Extract everything inside <script type="module"> or <script> tags
const regex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let index = 1;

while ((match = regex.exec(content)) !== null) {
    const scriptContent = match[1];
    
    // Replace Blade templating directives with valid Javascript expressions to avoid parse errors
    const cleaned = scriptContent
        .replace(/\{\{\s*url\([^)]*\)\s*\}\}/g, '"http://localhost"')
        .replace(/\{\{\s*csrf_token\(\)\s*\}\}/g, '"token"')
        .replace(/\{\{\s*url\([^)]*finalize'\)\s*\}\}/g, '"http://localhost"')
        .replace(/\{\{\s*url\([^)]*sessions[^)]*\)\s*\}\}/g, '"http://localhost"')
        .replace(/\{\{\s*url\([^)]*trials[^)]*\)\s*\}\}/g, '"http://localhost"')
        .replace(/\{\!\!\s*\$[^!]*\!\!\}/g, '{}')
        .replace(/\{\{\s*\$[^}]*\}\}/g, '"value"');

    try {
        new Function(cleaned);
        console.log(`Script block ${index} parses successfully!`);
    } catch (e) {
        console.error(`Script block ${index} failed compilation:`, e.message);
        
        // Let's print the line numbers by splitting the cleaned script content
        const lines = cleaned.split('\n');
        // Let's find where the mismatch might be or print the vicinity of the error
        console.error("Vicinity of compiling error:");
        // Print lines around the reported error if there is a line number
        const matchLine = e.stack.match(/<anonymous>:(\d+):(\d+)/);
        if (matchLine) {
            const errLine = parseInt(matchLine[1], 10);
            console.error(`Error at line ${errLine}:`);
            for (let i = Math.max(0, errLine - 10); i < Math.min(lines.length, errLine + 10); i++) {
                console.error(`${i + 1}: ${lines[i]}`);
            }
        } else {
            // If unexpected end of input, print last 20 lines
            console.error("Last 20 lines of the script:");
            for (let i = Math.max(0, lines.length - 20); i < lines.length; i++) {
                console.error(`${i + 1}: ${lines[i]}`);
            }
        }
    }
    index++;
}
