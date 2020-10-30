/**
 * @file
 */

const path = require('path');
const fs = require('fs');
const concat = require('concat');
let config = "./config/config.json";
let srcFolder = "./src";


let j = JSON.parse(fs.readFileSync(path.resolve(__dirname, config)));


Object.keys(j.modules).forEach(function (k) {
  console.log(k);
  console.log(j.modules[k].length);

  var arrFiles = [];
  var moduleName = k;

  for (var i = 0; i < j.modules[k].length; i++) {
      arrFiles.push(j.modules[k][i].src);
  }
  console.log(arrFiles);
  fs.existsSync(srcFolder + "/" + moduleName) || fs.mkdirSync(srcFolder + "/" + moduleName);
  concat(arrFiles, srcFolder + "/" + k + "/index.js");

});
