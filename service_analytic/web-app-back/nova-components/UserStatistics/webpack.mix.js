let mix = require('laravel-mix')
let dotenv = require('dotenv')
let webpack = require('webpack')

mix
  .webpackConfig({
    plugins: [
      new webpack.DefinePlugin({
        'process.env': JSON.stringify(dotenv.config({path: '../../.env'}).parsed)
      })
    ]
  })
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .sass('resources/sass/tool.scss', 'css')
