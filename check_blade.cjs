
const fs = require('fs');
const filePath = 'resources/views/events/themes/paolo-fest.blade.php';

try {
    const content = fs.readFileSync(filePath, 'utf8');
    const lines = content.split('\n');
    const stack = [];
    
    const openTags = ['@if', '@foreach', '@for', '@while', '@switch', '@auth', '@guest', '@isset', '@empty', '@unless', '@can', '@cannot', '@php', '@push', '@section'];
    const closeTags = {
        '@endif': '@if',
        '@endforeach': '@foreach',
        '@endfor': '@for',
        '@endwhile': '@while',
        '@endswitch': '@switch',
        '@endauth': '@auth',
        '@endguest': '@guest',
        '@endisset': '@isset',
        '@endempty': '@empty',
        '@endunless': '@unless',
        '@endcan': '@can',
        '@endcannot': '@cannot',
        '@endphp': '@php',
        '@endpush': '@push',
        '@endsection': '@section',
        '@stop': '@section',
        '@show': '@section'
    };

    // Regex to match directives
    // Note: This simple regex might match directives inside comments or strings, which is a limitation.
    const regex = /@(if|foreach|for|while|switch|auth|guest|isset|empty|unless|can|cannot|php|push|section|endif|endforeach|endfor|endwhile|endswitch|endauth|endguest|endisset|endempty|endunless|endcan|endcannot|endphp|endpush|endsection|stop|show)(?=\s|\(|$)/g;

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        let match;
        // We need to iterate over matches in the line
        while ((match = regex.exec(line)) !== null) {
            const tag = '@' + match[1];
            
            // Check if it's a closing tag
            if (closeTags[tag]) {
                const expected = closeTags[tag];
                if (stack.length === 0) {
                    console.log(`Error at line ${i + 1}: Found ${tag} but stack is empty.`);
                } else {
                    const last = stack.pop();
                    if (last.tag !== expected) {
                        // Special handling for @section which can be closed by @stop or @show or @endsection
                        if (expected === '@section' && last.tag === '@section') {
                            // Valid
                        } else {
                            console.log(`Error at line ${i + 1}: Found ${tag} but expected closing for ${last.tag} (opened at line ${last.line}).`);
                        }
                    }
                }
            } else if (openTags.includes(tag)) {
                stack.push({ tag: tag, line: i + 1 });
            }
        }
    }

    if (stack.length > 0) {
        console.log(`Error: Unexpected end of file. Unclosed tags:`);
        stack.forEach(item => {
            console.log(`- ${item.tag} opened at line ${item.line}`);
        });
    } else {
        console.log('No nesting errors found.');
    }

    // Check for unclosed braces
    let braceLevel = 0;
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        for (let j = 0; j < line.length; j++) {
            if (line.substring(j, j+2) === '{{' && line.substring(j, j+3) !== '{{-') {
                braceLevel++;
                j++;
            } else if (line.substring(j, j+2) === '}}') {
                braceLevel--;
                j++;
            }
        }
    }
    if (braceLevel !== 0) {
        console.log(`Error: Unbalanced braces {{ }}. Level: ${braceLevel}`);
    }

} catch (err) {
    console.error(err);
}
