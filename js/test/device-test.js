/**
 * @file
 */

import Navi from '../../modules/degov_media_video_mobile/js/navi';
import SpeedChecker from '../../modules/degov_media_video_mobile/js/speed_checker';


QUnit.test('Cellular device test', function (assert) {

  const navigator_1 = {
    connection: {
      'onchange':null,
      'effectiveType': "4g",
      'rtt': 50,
      'downlink':10,
      'saveDate': false
    }
  };

  const navigator_2 = {
    connection: {
      'onchange':null,
      'effectiveType': "Slow 3G",
      'rtt': 50,
      'downlink':10,
      'saveDate': false
    }
  };

  const navigator_3 = {
    connection: {
      'onchange':null,
      'effectiveType': "Slow 3G",
      'type': "cellular",
      'rtt': 50,
      'downlink':10,
      'saveDate': false
    }
  };

  const fakeMozilla = {};

  // Slow 3g 1542381563475
  // Fast 3G 1542381613768
  // create false window object
  // fakewindow 1 with normal rate.
  const fakeWindowNormalRate = {};
  fakeWindowNormalRate.performance = {};
  fakeWindowNormalRate.performance.timing = {
    'requestStart': 1542381447460
  };

  const fakeWindowslowRate = {};
  fakeWindowslowRate.performance = {};
  fakeWindowslowRate.performance.timing = {
    'requestStart': 1542381563475
  };

  const speed_checker = new SpeedChecker(fakeWindowNormalRate);

  assert.equal(true, typeof(fakeMozilla.connection === 'undefined') && (speed_checker.checkSlowLoadTime()), "Browser Mozilla at normal rate");

});
