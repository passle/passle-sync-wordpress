const path = require("path");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TsconfigPathsPlugin = require("tsconfig-paths-webpack-plugin");
const { WebpackManifestPlugin } = require("webpack-manifest-plugin");
const webpack = require("webpack");

const options = (env, options) => ({
  entry: "./src/index.tsx",
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        use: "ts-loader",
        exclude: /node_modules/,
      },
      {
        test: /\.scss$/,
        use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"],
      },
    ],
  },
  resolve: {
    extensions: [".ts", ".tsx", ".js", ".scss"],
    plugins: [new TsconfigPathsPlugin()],
  },
  output: {
    filename: "index.bundle.js",
    path: path.resolve(__dirname, "dist"),
    clean: true,
    publicPath: "",
  },
  plugins: [
    new HtmlWebpackPlugin({
      template: "src/index.html",
      filename: "index.html",
    }),
    new MiniCssExtractPlugin({
      filename: "index.bundle.css",
    }),
    new WebpackManifestPlugin({
      fileName: "asset-manifest.json",
      generate: (seed, files, entrypoints) => {
        const manifestFiles = files.reduce((manifest, file) => {
          manifest[file.name] =
            "/wp-content/plugins/passle-sync/frontend/dist/" + file.path;
          return manifest;
        }, seed);
        const entrypointFiles = entrypoints.main.filter(
          (fileName) => !fileName.endsWith(".map")
        );

        return {
          files: manifestFiles,
          entrypoints: entrypointFiles,
        };
      },
    }),
  ],
  devtool: false,
});

module.exports = options;