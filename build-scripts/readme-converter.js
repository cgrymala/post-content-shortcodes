const fs = require('fs');
const towptxt = require('@wpsh/to-wp-txt').default;

fs.readFile('README.md', 'utf8', (err, readme) => {
    if (err) throw err;
    fs.writeFile('readme.txt', towptxt(readme), err => {
        if (err) throw err;
    });
});