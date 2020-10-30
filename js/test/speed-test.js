/**
 * @file
 */

import SpeedChecker from '../../modules/degov_media_video_mobile/js/speed_checker';

QUnit.test("Test device speed", function (assert) {

  // Normal 1542381447460
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
  const speed_checker2 = new SpeedChecker(fakeWindowslowRate);

  assert.ok(typeof speed_checker !== 'undefined', 'Object Speedchecker with FakeWindow 1 with normal rate is available');

  assert.ok(typeof speed_checker2 !== 'undefined', 'Object Speedchecker with FakeWindow 2 with slow 3G is available');

  assert.equal(true, speed_checker.checkSlowLoadTime(), 'FakeWindow 1: Speed is normal rate');

  assert.equal(true,speed_checker2.checkSlowLoadTime(), 'FakeWindow 2: Speed is at Slow 3G');

});
