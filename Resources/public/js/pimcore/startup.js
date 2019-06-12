pimcore.registerNS("pimcore.plugin.FileTransferBundle");

pimcore.plugin.FileTransferBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.FileTransferBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        // alert("FileTransferBundle ready!");
    }
});

var FileTransferBundlePlugin = new pimcore.plugin.FileTransferBundle();
