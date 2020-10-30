/**
 * @file
 */

const path = require('path')
      fs = require('fs')
      MiniCssExtractPlugin = require("mini-css-extract-plugin")
      CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = {
  entry: [
    './src/script.js'
  ],
  output: {
    path: path.join(__dirname, '/webpack-dist/'),
    filename: 'bundle.js'
  },
  plugins: [
    new MiniCssExtractPlugin({
      // Options similar to the same options in webpackOptions.output
      // both options are optional.
      filename: "[name].css",
      chunkFilename: "[id].css"
    }),
    new CopyWebpackPlugin([
      {
        from: './node_modules/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js',
        to: './fontawesome-iconpicker.js',
      },
      {
        from: './node_modules/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.css',
        to: './fontawesome-iconpicker.css',
      },
    ]),
  ],
  module: {
    rules: [
      {
        exclude: /(node_modules)/,
        use: [{
          loader: 'babel-loader',
          options: {
            ...JSON.parse(fs.readFileSync(path.resolve(__dirname, '.babelrc'))),
          }
        }]
      },
      {
        test: [/\.scss$/, /\.css$/],
        exclude: [/install\.scss/],
        use: [
          MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { url: false, sourceMap: true } },
          { loader: 'sass-loader', options: { sourceMap: true } }
        ],
      },
      {
        test: [/install\.scss$/],
        use: [
          MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { url: false, sourceMap: true } },
          { loader: 'sass-loader', options: { sourceMap: true } }
        ],
      }
    ]
  },
  devtool: 'source-map',
};
