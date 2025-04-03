const fs = require('fs');
const {replaceInFile,replaceInFileSync} = require('replace-in-file');

function updateVersionNumber() {
    const versionNumber = fs.readFileSync('./.version').toString();

    console.log( 'The new version number is ' + versionNumber );

    const replacements = [
        {
            files: [
                './package.json',
                './composer.json',
            ],
            from: /"version": "([\d|\.]+)",/,
            to: '"version": "' + versionNumber + '",',
            countMatches: true
        },
        {
            files: './README.md',
            from: /Stable tag: ([\d|\.]+)/g,
            to: 'Stable tag: ' + versionNumber,
            countMatches: true
        },
        {
            files: './post-content-shortcodes.php',
            from: /Version: ([\d|\.]+)/g,
            to: 'Version: ' + versionNumber,
            countMatches: true
        },
        {
            files: './lib/classes/ten321/post-content-shortcodes/Plugin.php',
            from: /public static string \$version = '([\d|\.]+)';/g,
            to: "public static string $version = '" + versionNumber + "';",
            countMatches: true
        }
    ];

    replacements.map( options => console.log(replaceInFileSync(options)));
}

function deleteAllGitFiles(path) {
    if (fs.existsSync(path)) {
        if (fs.lstatSync(path).isDirectory()) {
            fs.readdirSync(path).forEach(function (file) {
                let curPath = path + '/' + file;

                if (curPath.includes('.git')) {
                    if (fs.lstatSync(curPath).isDirectory()) {
                        console.log(`Recursively removing "${curPath}" directory...`);
                        fs.rmdirSync(curPath, {recursive: true});
                    } else if (fs.lstatSync(curPath).isFile()) {
                        console.log(`Removing file "${curPath}"...`);
                        fs.rmSync(curPath);
                    }
                } else if (fs.lstatSync(curPath).isDirectory()) {
                    deleteAllGitFiles(curPath);
                }
            });
        } else if (fs.lstatSync(path).isFile()) {
            if (path.includes('.git')) {
                console.log(`Removing file "${path}"...`);
                fs.rmSync(path);
            }
        }
    }
}

console.log("Preparing to update version number...");

updateVersionNumber();

console.log("Finished updating version number");



console.log("Cleaning working tree...");

deleteAllGitFiles("../vendor");

console.log("Successfully cleaned working tree!");