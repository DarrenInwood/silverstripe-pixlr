function buildUrl(opt) {
    var url = 'http://pixlr.com/' + opt.service + '/?s=c', attr;
    for (attr in opt) {
        if (opt.hasOwnProperty(attr) && attr !== 'service') {
            url += "&" + attr + "=" + escape(opt[attr]);
        }
    }
    return url;
}

function updateFrameHeight() {
    jQuery('#pixlr').height(jQuery(window).height());
}

function initPixlr(savelocation, extilocation, id, title, image) {
    var options = {
        service:'express',
        target: savelocation,
        exit: extilocation,
        locktarget: true,
        locktitle: true
    };
    if(title){
        options.title = title;
        options.image = decodeURIComponent(image);
        options.id = id;
    }
    var url = buildUrl(options);

    jQuery('#frame-holder').html('<iframe id="pixlr" src="' + url + '" width="100%" height="800" />');

    jQuery(window).resize(updateFrameHeight);
    updateFrameHeight();

};