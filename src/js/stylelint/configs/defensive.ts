import { MODULES, PACKAGES, resolvePackages } from '../utils/module';

import type { Config } from '../types/config';

export const defensive = (): Config[] => {
  const {
    requiredAll: [isStylelintPluginDefensiveCssInstalled],
  } = resolvePackages(MODULES.defensive);

  if (!isStylelintPluginDefensiveCssInstalled) {
    return [];
  }

  return [
    {
      plugins: PACKAGES.STYLELINT_PLUGIN_DEFENSIVE_CSS,
      extends: `${PACKAGES.STYLELINT_PLUGIN_DEFENSIVE_CSS}/configs/strict`,
    },
  ];
};
