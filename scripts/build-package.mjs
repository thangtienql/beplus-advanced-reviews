import { execSync } from 'child_process';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';
import fs from 'fs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = resolve(__dirname, '..');

console.log('📦 Building assets...');
execSync('npm run build', { stdio: 'inherit', cwd: rootDir });

console.log('📦 Installing composer dependencies (no-dev)...');
execSync('npm run composer:install', { stdio: 'inherit', cwd: rootDir });

console.log('📦 Creating archive for beplus-advanced-reviews plugin...');
try {
	const ignoreLines = fs.readFileSync(resolve(rootDir, '.distignore'), 'utf8')
		.split('\n')
		.map(line => line.trim())
		.filter(line => line && !line.startsWith('#'));
		
	// Format for zip command: '*/node_modules*' etc.
	const excludes = ignoreLines.map(line => `"*/${line}*"`).join(' ');

	execSync(`cd .. && zip -q -r beplus-advanced-reviews.zip beplus-advanced-reviews -x ${excludes}`, { 
		stdio: 'inherit', 
		cwd: rootDir 
	});
	console.log('✅ Done. beplus-advanced-reviews.zip is ready! 🎉');
} catch (error) {
	console.error('❌ Failed to create zip file.', error.message);
	process.exit(1);
}
