/**
 * @file
 */

const Navi = function (navigator) {
  this.navigator = navigator;
};

Navi.prototype.getConnection = function () {
  let connection;

  if (typeof this.navigator.connection !== 'undefined') {
    connection = this.navigator.connection;
  }
else if (typeof this.navigator.mozConnection !== 'undefined') {
    connection = this.navigator.mozConnection;
  }
else if (typeof this.navigator.webkitConnection !== 'undefined') {
    connection = this.navigator.webkitConnection;
  }

  return connection;
};

export default Navi;
