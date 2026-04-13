import { expect, test } from 'vitest';

import { run } from './utils/command';

test('expected commitlint config', () => {
  expect(run(`bun lint:commit --print-config --color false`)).toMatchSnapshot();
});
