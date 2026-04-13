import { test } from 'vitest';

import { snapshotConfigs } from './utils/config-snapshot';

test('expected stylelint config', () => {
  snapshotConfigs({
    command: (filePath) => `bun lint:css --print-config ${filePath}`,
    fixturesDirectory: `${process.cwd()}/tests/js/fixtures/stylelint`,
  });
});
