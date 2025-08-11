const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
  mode: isProduction ? 'production' : 'development',
  
  entry: {
    admin: './assets/admin/js/admin.js',
    public: './assets/client/client.js'
  },
  
  output: {
    path: path.resolve(__dirname, 'assets/dist'),
    filename: 'js/[name].js',
    chunkFilename: 'js/[name]-[contenthash].js',
    clean: true
  },
  
  resolve: {
    extensions: ['.js', '.vue', '.json'],
    alias: {
      '@': path.resolve(__dirname, './'),
      'vue': 'vue/dist/vue.esm-bundler.js'
    }
  },
  
  module: {
    rules: [
      // Vue files
      {
        test: /\.vue$/,
        loader: 'vue-loader'
      },
      
      // JavaScript files
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      
      // SCSS files
      {
        test: /\.scss$/,
        use: [
          isProduction ? MiniCssExtractPlugin.loader : 'style-loader',
          'css-loader',
          {
            loader: 'sass-loader',
            options: {
              additionalData: `@import "@/assets/admin/scss/_variables.scss";`
            }
          }
        ]
      },
      
      // CSS files
      {
        test: /\.css$/,
        use: [
          isProduction ? MiniCssExtractPlugin.loader : 'style-loader',
          'css-loader'
        ]
      },
      
      // Images and fonts
      {
        test: /\.(png|jpe?g|gif|svg|woff2?|ttf|eot)$/,
        type: 'asset/resource',
        generator: {
          filename: 'assets/[name]-[hash][ext]'
        }
      }
    ]
  },
  
  plugins: [
    new CleanWebpackPlugin(),
    new VueLoaderPlugin(),
    
    // Extract CSS into separate files
    new MiniCssExtractPlugin({
      filename: 'css/[name].css',
      chunkFilename: 'css/[name]-[contenthash].css'
    })
  ],
  
  optimization: {
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all',
          filename: 'js/vendors.js'
        }
      }
    }
  },
  
  devtool: isProduction ? 'source-map' : 'eval-source-map',
  
  devServer: {
    static: {
      directory: path.join(__dirname, 'assets/dist')
    },
    hot: true,
    port: 3000,
    open: false
  },
  
  stats: {
    children: false,
    modules: false
  }
};
