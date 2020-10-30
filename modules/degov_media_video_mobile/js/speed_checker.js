/**
 * @file
 */

const SpeedChecker = function (window) {
  this.window = window;
};

SpeedChecker.prototype.checkSlowLoadTime = function () {
  const maxCellularLoadTime = 5000;
  let loadTime = new Date().valueOf() - this.window.performance.timing.requestStart;
  return (loadTime > maxCellularLoadTime);
};

export default SpeedChecker;
