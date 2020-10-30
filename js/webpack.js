/**
 * @file
 */

const path = require('path');
const fs = require('fs');
const CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = {
  entry: {
    degov_media_video_mobile: [
      'core-js/stable',
      'regenerator-runtime/runtime',
      './../modules/degov_media_video_mobile/js/video_mobile.js',
    ],
  },
  output: {
    path: path.join(__dirname, '/webpack-dist/'),
    filename: "[name].entry.js"
  },
  module: {
    rules: [
      {
        exclude: /(node_modules)/,
        use: [{
          loader: 'babel-loader',
          options: JSON.parse(fs.readFileSync(path.resolve(__dirname, '.babelrc'))),
          }
        ]
      }
    ]
  },
  devtool: 'none',
  plugins: [
    new CopyWebpackPlugin([
      {
        from: './webpack-dist/degov_media_video_mobile.entry.js',
        to: './../../modules/degov_media_video_mobile/js/',
        toType: 'dir'
      }
    ])
  ]
};
