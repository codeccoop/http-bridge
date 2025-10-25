const path = require("path");

const buildPhpCbfCommand = (filenames) =>
  `vendor/bin/phpcbf -n ${filenames
    .map((f) => path.relative(process.cwd(), f))
    .join(" ")}`;

module.exports = {
  "*.php": [buildPhpCbfCommand],
};
