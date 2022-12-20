const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  publicPath: './',
  productionSourceMap: false,
  transpileDependencies: true,
  devServer: {
    allowedHosts: "all",
  }
})
