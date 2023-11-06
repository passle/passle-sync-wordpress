const { src, dest, series, parallel } = require("gulp");
const replace = require("gulp-replace");
const zip = require("gulp-zip");
const es = require("event-stream");
const { spawn } = require("child_process");

const PLUGIN_SLUG = "passle-sync";

const getEnv = () => process.env.PASSLE_ENV;

const installComposerDependencies = (cb) => {
  const args = ["install"];
  if (getEnv() != null && !getEnv().includes("localhost")) {
    args.push("--no-dev");
  }

  spawn("composer", args, { stdio: "inherit", shell: true }).on("close", cb);
};

const installNpmDependencies = (cb) => {
  spawn("npm", ["ci"], { cwd: "frontend/", stdio: "inherit", shell: true }).on(
    "close",
    cb,
  );
};

const buildFrontend = (cb) => {
  spawn("npm", ["run", "build"], {
    cwd: "frontend/",
    stdio: "inherit",
    shell: true,
  }).on("close", cb);
};

const installDependenciesAndBuildFrontend = series(
  parallel(installComposerDependencies, installNpmDependencies),
  buildFrontend,
);

const buildZip = (cb) => {
  const constants = src("constants.php").pipe(replace("localhost", getEnv()));

  const allFiles = src([
    "**/*",
    "!constants.php",
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
  ]);

  es.merge(allFiles, constants)
    .pipe(zip(`${PLUGIN_SLUG}.zip`))
    .pipe(dest("./build/"));

  cb();
};

const generateDocs = (cb) => {
  spawn("composer", ["run", "generate-docs"], {
    stdio: "inherit",
    shell: true,
  }).on("close", cb);
};

exports.init = installDependenciesAndBuildFrontend;
exports.buildZip = series(installDependenciesAndBuildFrontend, buildZip);
exports.generateDocs = series(installComposerDependencies, generateDocs);
