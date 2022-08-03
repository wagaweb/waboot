const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const webpack = require('webpack');

module.exports = (env = {}) => ({
  mode: env.prod ? 'production' : 'development',
  devtool: env.prod ? 'source-map' : 'eval-cheap-module-source-map',
  watch: true,
  entry: [path.resolve(__dirname, './src/main.ts')],
  output: {
    filename: env.prod ? './js/shop-rules-admin.min.js' : './js/shop-rules-admin.js',
    path: path.resolve(__dirname, '../dist'),
  },
  resolve: {
    extensions: ['.ts', '.js', '.vue', '.json'],
    alias: {
      '@': path.resolve(__dirname, 'src/'),
    },
  },
  externals: {
    jquery: 'jQuery'
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        use: 'vue-loader',
      },
      {
        test: /\.ts$/,
        use: [
          {
            loader: 'ts-loader',
            options: {
              appendTsSuffixTo: [/\.vue$/],
            },
          },
        ],
      },
      {
        test: /\.(css|sass|scss)$/,
        use: [
            { loader: MiniCssExtractPlugin.loader },
            'css-loader',
            'postcss-loader',
            'sass-loader',
        ],
      },
    ],
  },
  plugins: [
    new VueLoaderPlugin(),
    new MiniCssExtractPlugin({
      filename: env.prod ? '../dist/css/shop-rules-admin.min.css' : '../dist/css/shop-rules-admin.css',
    })
  ],
});
