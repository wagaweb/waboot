const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");

const JS_DIR = path.resolve(__dirname, "src/js");
const SASS_DIR = path.resolve(__dirname, "src/sass");
const IMG_DIR = path.resolve(__dirname, "images");
const DIST_DIR = path.resolve(__dirname, "dist");

const entry = {
	main: JS_DIR + "/main.js",
	gutenberg: SASS_DIR + "/backend/gutenberg.scss",
};
const output = {
	path: DIST_DIR,
	filename: "js/[name].js",
	clean: true,
};

const rules = [
	{
		test: /\.js$/,
		include: [JS_DIR],
		exclude: [/node_modules/, /vendor/],
		use: {
			loader: "babel-loader",
			options: {
				presets: ["@babel/preset-env"],
			},
		},
	},
	{
		test: /\.scss$/,
		exclude: [/node_modules/, /vendor/],
		use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"],
	},
	{
		test: /\.(png|jpg|svg|jpeg|gif|ico)$/,
		type: "asset/resource",
		loader: "file-loader",
		exclude: [/node_modules/, /vendor/],
		options: {
			name: "[path][name].[ext]",
			publicPath:
				"production" === process.env.NODE_ENV ? "../../" : "../../../",
		},
	},
];

const plugins = (argv) => [
	new CleanWebpackPlugin({
		cleanStaleWebpackAssets: "production" === argv.mode,
	}),
	new MiniCssExtractPlugin({
		filename: "css/[name].css",
	}),
];

module.exports = (env, argv) => ({
	entry: entry,
	output: output,
	devtool: "source-map",
	module: {
		rules: rules,
	},
	optimization: {
		minimize: true,
		minimizer: [
			new CssMinimizerPlugin({}),
			new TerserPlugin({
				parallel: true,
				extractComments: false,
			}),
		],
	},
	plugins: plugins(argv),
	externals: {
		jquery: "jQuery",
	},
});
