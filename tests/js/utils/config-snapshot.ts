import { expect } from 'vitest';

import { run } from './command';
import { traverseDirectory } from './filesystem';
import { computeConfigDiff } from './json-diff';

import type { JsonObject } from './json-diff';

interface SnapshotConfigsOptions {
  command: (filePath: string) => string;
  fixturesDirectory: string;
  normalize?: (output: string) => string;
}

const parseConfigOutput = (output: string): JsonObject => {
  const start = output.indexOf('{');

  return <JsonObject>JSON.parse(output.slice(start));
};

export const snapshotConfigs = (options: SnapshotConfigsOptions): void => {
  const {
    fixturesDirectory,
    command,
    normalize,
  } = options;

  const configs = new Map<string, JsonObject>();

  traverseDirectory(fixturesDirectory, (filePath) => {
    const name = filePath.replace(`${fixturesDirectory}/`, '');
    const raw = run(command(filePath));
    const config = parseConfigOutput(normalize ? normalize(raw) : raw);

    configs.set(name, config);
  });

  const groups = new Map<string, string[]>();
  const hashToRepresentative = new Map<string, string>();

  for (const [name, config] of configs) {
    const hash = JSON.stringify(config);
    const representative = hashToRepresentative.get(hash);

    if (representative === undefined) {
      hashToRepresentative.set(hash, name);
      groups.set(name, [name]);
    } else {
      groups.get(representative)?.push(name);
    }
  }

  expect(Object.fromEntries(groups)).toMatchSnapshot('config-groups');

  const [baseName, ...otherNames] = [...groups.keys()];

  if (baseName === undefined) {
    return;
  }

  const baseConfig = configs.get(baseName) ?? {};

  expect(baseConfig).toMatchSnapshot(`base: ${baseName}`);

  for (const name of otherNames) {
    expect(computeConfigDiff(baseConfig, configs.get(name) ?? {})).toMatchSnapshot(`diff: ${name}`);
  }
};
