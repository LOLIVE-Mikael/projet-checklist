const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')
    .addEntry('app', './assets/app.js')
    .addEntry('checklist', './assets/js/checklist.js')
    .addEntry('task', './assets/js/task.js')
    .addEntry('visite', './assets/js/visite.js')
  //  .addEntry('visit', './assets/js/visit.js')

   .addStyleEntry('styles', './assets/styles/app.css') // Exemple d'entrée pour CSS


	.autoProvidejQuery()
	
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

	
    .splitEntryChunks()

    .enableSingleRuntimeChunk()


    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })


;

module.exports = Encore.getWebpackConfig();
