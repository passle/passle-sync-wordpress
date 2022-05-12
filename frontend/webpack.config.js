const path = require("path");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TsconfigPathsPlugin = require("tsconfig-paths-webpack-plugin");
const { WebpackManifestPlugin } = require("webpack-manifest-plugin");

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
        test: /\.s?css$/,
        oneOf: [
          {
            test: /\.module\.s?css$/,
            use: [
              MiniCssExtractPlugin.loader,
              "css-modules-typescript-loader",
              {
                loader: "css-loader",
                options: {
                  modules: true,
                },
              },
              "sass-loader",
            ],
          },
          {
            use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"],
          },
        ],
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
    }),
  ],
  devtool: false,
});

module.exports = options;
