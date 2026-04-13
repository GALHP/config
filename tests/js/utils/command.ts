import { execSync } from 'node:child_process';

export const run = (command: string): string => execSync(command, {
  encoding: 'utf-8',
}).replaceAll(process.cwd(), '.');
