// tests/Javascript/find-mismatch.js
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const filepath = path.join(__dirname, 'temp-capture.js');

if (!fs.existsSync(filepath)) {
    console.error("temp-capture.js does not exist!");
    process.exit(1);
}

const content = fs.readFileSync(filepath, 'utf8');

const stack = [];
let line = 1;
let col = 1;

for (let i = 0; i < content.length; i++) {
    const char = content[i];
    
    if (char === '\n') {
        line++;
        col = 1;
        continue;
    }
    
    // Ignore single line comments
    if (char === '/' && content[i + 1] === '/') {
        while (i < content.length && content[i] !== '\n') {
            i++;
        }
        line++;
        col = 1;
        continue;
    }
    
    // Ignore block comments
    if (char === '/' && content[i + 1] === '*') {
        i += 2;
        while (i < content.length && !(content[i] === '*' && content[i + 1] === '/')) {
            if (content[i] === '\n') {
                line++;
                col = 1;
            } else {
                col++;
            }
            i++;
        }
        i++; // skip /
        col++;
        continue;
    }

    // Ignore string literals (single, double quotes, template literals)
    if (char === '"' || char === "'" || char === '`') {
        const quoteType = char;
        i++;
        col++;
        while (i < content.length && content[i] !== quoteType) {
            // handle escaped quotes
            if (content[i] === '\\' && content[i + 1] === quoteType) {
                i += 2;
                col += 2;
                continue;
            }
            if (content[i] === '\n') {
                line++;
                col = 1;
            } else {
                col++;
            }
            i++;
        }
        col++;
        continue;
    }
    
    if (char === '{' || char === '(' || char === '[') {
        stack.push({ char, line, col });
    } else if (char === '}' || char === ')' || char === ']') {
        if (stack.length === 0) {
            console.error(`Error: Found closing '${char}' at line ${line}, col ${col} with no matching opening character.`);
        } else {
            const top = stack.pop();
            const matches = (top.char === '{' && char === '}') ||
                            (top.char === '(' && char === ')') ||
                            (top.char === '[' && char === ']');
            if (!matches) {
                console.error(`Error: Mismatched closing '${char}' at line ${line}, col ${col}. Expected match for '${top.char}' from line ${top.line}, col ${top.col}`);
            }
        }
    }
    col++;
}

if (stack.length > 0) {
    console.log(`Finished parsing. ${stack.length} unclosed characters left in stack:`);
    stack.forEach(item => {
        console.log(`  Unclosed '${item.char}' from line ${item.line}, col ${item.col}`);
    });
} else {
    console.log("No brace mismatches detected!");
}
