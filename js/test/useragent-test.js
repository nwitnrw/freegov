/**
 * @file
 */

import Navi from '../../modules/degov_media_video_mobile/js/navi';
import UserAgentChecker from '../../modules/degov_media_video_mobile/js/user_agent_checker';


QUnit.test('User agent string test', function (assert) {

  // Chrome on Mac.
  const navigator1 = {
    userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36'
  };

  // Mobile Safari.
  const navigator2 = {
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B137 Safari/601.1'
  };

  // Android Browser.
  const navigator3 = {
    userAgent: 'Mozilla/5.0 (Linux; U; Android 4.0.2; en-us; Galaxy Nexus Build/ICL53F) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30'
  };

  const fakeWindow1 = {};
  fakeWindow1.navigator = navigator1;
  const userAgentChecker1 = new UserAgentChecker(fakeWindow1);
  assert.equal(false, userAgentChecker1.isMobile(), "Chrome on Mac should report as not mobile.");

  const fakeWindow2 = {};
  fakeWindow2.navigator = navigator2;
  const userAgentChecker2 = new UserAgentChecker(fakeWindow2);
  assert.equal(true, userAgentChecker2.isMobile(), "Mobile Safari should report as mobile.");

  const fakeWindow3 = {};
  fakeWindow3.navigator = navigator3;
  const userAgentChecker3 = new UserAgentChecker(fakeWindow3);
  assert.equal(true, userAgentChecker3.isMobile(), "Android should report as mobile.");
});
