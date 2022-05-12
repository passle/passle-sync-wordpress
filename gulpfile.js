const { src, dest } = require("gulp");
const zip = require("gulp-zip");
const chalk = require("chalk");

const PLUGIN_SLUG = "passle-sync";

const buildZip = (cb) => {
  console.log(
    chalk.yellow(
      `!!\n!! Make sure you have run ${chalk.bold.cyan(
        "composer install --no-dev",
      )} and ${chalk.bold.cyan(
        "cd frontend && npm run build",
      )} before running build-zip.\n!!`,
    ),
  );

  src([
    "**/*",
    "!**/node_modules{,/**}",
    "!frontend/src{,/**}",
    "!frontend/tsconfig.json",
    "!frontend/webpack.config.js",
    "!.vscode{,/**}",
    "!gulpfile.js",
    "!**/package.json",
    "!**/package-lock.json",
    "!composer.json",
    "!composer.lock",
    "!phpdoc.xml",
    "!build{,/**}",
  ])
    .pipe(zip(`${PLUGIN_SLUG}.zip`))
    .pipe(dest("./build/"));

  cb();
};

exports.buildZip = buildZip;
