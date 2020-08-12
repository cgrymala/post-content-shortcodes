var path = require('path');

const externals = {
    wp: 'wp',
    react: 'React',
    'react-dom': 'ReactDOM',
};

const isProduction = process.env.NODE_ENV === 'production';
const mode = isProduction ? 'production' : 'development';

module.exports = {
    mode,
    entry: {
        content: './src/ten321/post-content-shortcodes/blocks/content/block.js',
        list: './src/ten321/post-content-shortcodes/blocks/list/block.js'
    },
    output: {
        path: path.resolve(__dirname, './dist/ten321/post-content-shortcodes/blocks/'),
        filename: '[name]/block.js'
    },
    externals,
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                },
            },
            {
                test: /\.s[ac]ss$/i,
                use: [
                    // Creates `style` nodes from JS strings
                    'style-loader',
                    // Translates CSS into CommonJS
                    'css-loader',
                    // Compiles Sass to CSS
                    'sass-loader',
                ],
            },
        ],
    },
};
