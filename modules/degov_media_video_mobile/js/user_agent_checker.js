/**
 * @file
 */

const UserAgentChecker = function (window) {
  this.window = window;
};

UserAgentChecker.prototype.isMobile = function () {
  return this.window.navigator.userAgent.search(/mobile/i) !== -1;
};

export default UserAgentChecker;
