import esbuild from 'esbuild';
import less from 'less';
import fs from 'fs';
import path from 'path';

/**
 * esbuild plugin that compiles LESS files to CSS.
 *
 * – Resolves all @import directives via the `less` compiler so esbuild
 *   never sees them (no double-resolution).
 * – Exposes every imported file in `watchFiles` so --watch mode picks up
 *   changes in partials automatically.
 */
const lessPlugin = {
    name: 'less',
    setup(build) {
        build.onLoad({ filter: /\.less$/ }, async (args) => {
            const source = await fs.promises.readFile(args.path, 'utf8');
            const dir = path.dirname(args.path);

            try {
                const result = await less.render(source, {
                    filename: args.path,
                    paths: [dir],
                    math: 'always',
                });

                return {
                    contents: result.css,
                    loader: 'css',
                    watchFiles: result.imports,
                };
            } catch (err) {
                return {
                    errors: [
                        {
                            text: err.message,
                            location: {
                                file: err.filename || args.path,
                                line: err.line,
                                column: err.column,
                            },
                        },
                    ],
                };
            }
        });
    },
};

const isWatch = process.argv.includes('--watch');

/** @type {import('esbuild').BuildOptions} */
const cssConfig = {
    entryPoints: ['assets/less/waterfall-reviews.less'],
    outdir: 'assets/css',
    bundle: true,
    minify: true,
    plugins: [lessPlugin],
    entryNames: '[name].min',
    logLevel: 'info',
};

/** @type {import('esbuild').BuildOptions} */
const jsConfig = {
    entryPoints: ['assets/scripts/waterfall-reviews.ts', 'assets/scripts/wfr-admin.ts'],
    outdir: 'assets/js',
    bundle: true,
    minify: true,
    target: ['es2018'],
    entryNames: '[name].min',
    logLevel: 'info',
};

if (isWatch) {
    const [cssCtx, jsCtx] = await Promise.all([
        esbuild.context(cssConfig),
        esbuild.context(jsConfig),
    ]);
    await Promise.all([cssCtx.watch(), jsCtx.watch()]);
    console.log('\n👀  Watching for changes in assets/less/ and assets/scripts/ …\n');
} else {
    await Promise.all([esbuild.build(cssConfig), esbuild.build(jsConfig)]);
}
