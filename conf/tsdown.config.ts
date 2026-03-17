import { defineConfig } from 'tsdown';

export default defineConfig((options) => {
  const isWatchMode = options.watch === true;

  const commonOptions = <const>{
    outDir: '../dist',
    format: 'esm',
    treeshake: !isWatchMode,
    clean: !isWatchMode,
    outputOptions: {
      chunkFileNames: 'shared.mjs',
    },
  } satisfies typeof options;

  return [
    {
      ...commonOptions,
      dts: !isWatchMode,
      entry: [
        '../src/js/eslint/index.ts',
        '../src/js/stylelint/index.ts',
      ],
      deps: {
        neverBundle: [
          '@typescript-eslint/utils',
        ],
      },
    },
    {
      ...commonOptions,
      outDir: `${commonOptions.outDir}/scripts`,
      entry: [
        '../scripts/eslint.ts',
        '../scripts/stylelint.ts',
      ],
    },
  ];
});
