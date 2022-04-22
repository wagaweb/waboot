const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = (env = {}) => ({
  mode: env.prod ? 'production' : 'development',
  devtool: env.prod ? 'source-map' : 'eval-source-map',
  watch: !env.prod,
  entry: [path.resolve(__dirname, './src/main.ts')],
  output: {
    filename: env.prod ? './js/catalog.min.js' : './js/catalog.js',
    path: path.resolve(__dirname, '../../../dist'),
  },
  resolve: {
    extensions: ['.ts', '.js', '.vue', '.json'],
    alias: {
      '@': path.resolve(__dirname, 'src/'),
    },
  },
  externals: {
    jquery: 'jQuery',
    gtag: 'gtag',
    JVMWooCommerceWishlist: 'JVMWooCommerceWishlist',
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
        test: /\.css$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
          },
          'css-loader',
        ],
      },
    ],
  },
  plugins: [
    new VueLoaderPlugin(),
    new MiniCssExtractPlugin({
      filename: env.prod ? './css/catalog.min.css' : './css/catalog.css',
    }),
  ],
});
