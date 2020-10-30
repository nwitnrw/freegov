/**
 * @file
 */

import Navi from '../../modules/degov_media_video_mobile/js/navi';


QUnit.test("Test Navigator Object", function (assert) {

  // Established fake_navigator.
  const navigator = {
    connection: {
      'onchange':null,
      'effectiveType': "4g",
      'rtt': 50,
      'downlink':10,
      'saveDate': false
    }
  };

  const navi = new Navi(navigator);

  assert.ok(typeof navi !== 'undefined', 'Navigator Object is available');
  assert.ok(typeof navi.getConnection().effectiveType !== 'undefined', 'Expected: If effectiveType is given, then it is defined');
  assert.equal("4g", navi.getConnection().effectiveType, "Expected: Connection is 4g");
  assert.notEqual("Slow 3g", navi.getConnection().effectiveType, "Expected: Connection is not Slow 3g")
  assert.equal('undefined', typeof navi.getConnection().type, "Expected: If property navigator.connection.type (e.g) ist not define, we expect value to be undefined");
  assert.equal('undefined',typeof navi.getConnection().mozConnection, "Expected: MozConnection expected to be undefined");
  assert.equal('undefined',typeof navi.getConnection().webkitConnection, "Expected: webkitConnection expected to be undefined");

});
