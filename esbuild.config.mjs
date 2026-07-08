// esbuild configuration for Beplus Advanced Reviews For Woocommerce

import * as esbuild from 'esbuild';

const isWatch = process.argv.includes('--watch');
const production = process.env.NODE_ENV === 'production';

/** @type {esbuild.BuildOptions} */
const config = {
	entryPoints: [
		'blocks/advanced-review/view.js',
	],
	bundle: false,
	minify: production,
	sourcemap: !production,
	outdir: 'build',
	format: 'iife',
	platform: 'browser',
	target: ['es2017'],
};

async function build() {
	try {
		if (isWatch) {
			const ctx = await esbuild.context(config);
			await ctx.watch();
			console.log('Watching for changes...');
		} else {
			await esbuild.build(config);
			console.log('Build complete.');
		}
	} catch (err) {
		console.error('Build failed:', err);
		process.exit(1);
	}
}

build();
