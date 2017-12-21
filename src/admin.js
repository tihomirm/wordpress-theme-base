import 'regenerator-runtime/runtime';
import * as OfflinePluginRuntime from 'offline-plugin/runtime';

import transformURLsWebpackDevServer from './js/lib/transform-urls-wds';
import enhanceColorField from './js/admin/enhanceColorField';
import './admin.styl';

enhanceColorField();
OfflinePluginRuntime.install();

if (module.hot) {
  if (document.body.classList.contains('login')) {
    transformURLsWebpackDevServer();
  } else {
    if (window.location.origin.includes('localhost')) { // eslint-disable-line
      window.location.href = window.location.href.replace(
        window.location.origin,
        window.theme.siteurl
      );
    }
  }
}
