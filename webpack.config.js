const path = require('path');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    mode: 'production',
    //https://webpack.js.org/configuration/entry-context/
    entry: ['./assets/src/js/main.js','./assets/src/sass/main.scss','./assets/src/sass/backend/gutenberg.scss'],
    output: {
        path: path.resolve(__dirname, 'assets/dist'),
        filename: 'js/main.min.js',
    },
    externals: {
        jquery: 'jQuery',
    },
    devtool: 'source-map',
    watchOptions: {
        ignored: /node_modules/,
    },
    plugins: [new MiniCssExtractPlugin({
        filename: 'css2/[name].css',
    })],
    module: {
        rules: [
            {
                test: /\.scss$/,
                //type: 'asset/resource', //https://webpack.js.org/guides/asset-modules/
                /*generator: {
                    filename: 'css/[name].min.css'
                },*/
                use: [
                    MiniCssExtractPlugin.loader,
                    { loader: "css-loader", options: { url: false, importLoaders: 1, sourceMap: true } },
                    { loader: 'postcss-loader', options: { postcssOptions: { plugins: [autoprefixer({ browsers: ["last 1 version"] }), cssnano({ zindex: false })] } } },
                    { loader: 'sass-loader' },
                ]
            }
        ]
    }
};