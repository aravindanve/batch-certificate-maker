// requires phantomjs
var page = require('webpage').create(),
    system = require('system'),
    mmToInch = 0.0393701,
    inchToPx = 96;

var address, opath = './download/';

function sizeToPx(str) {
    if ('mm' === str.substr(-2)) {
        return str.substr(0, str.length - 2) * mmToInch * inchToPx;
    } else if ('cm' === str.substr(-2)) {
        return str.substr(0, str.length - 2) * mmToInch * 10 * inchToPx;
    } else if ('in' === str.substr(-2)) {
        return str.substr(0, str.length - 2) * inchToPx;
    } else {
        return str * 1;
    }
}

function savePdf(args) {
    if (system.args.length !== 4) {
        // savepdf.js url pagesize
        console.log('error: invalid arguments');
        phantom.exit(1);
    } else {
        address = system.args[1];
        size = system.args[2].split('*');
        output = system.args[3].split('/');
        if (output.length > 0) {
            output = output[output.length - 1];
        } else {
            output = 'certificates.pdf';
        }
        output = opath + output;
        
        if (size.length !== 2) {
            console.log('error: invalid size');
            phantom.exit(1);
        }
        var pageWidth = sizeToPx(size[0]);
        var pageHeight = sizeToPx(size[1]);
        page.paperSize = {
            width: pageWidth,
            height: pageHeight,
            margin: 0
        };
        page.viewportSize = {
            width: pageWidth,
            height: pageHeight
        };
        page.open(address, function (status) {
            if (status !== 'success') {
                console.log('error: page open failed');
                phantom.exit(1);
            } else {
                window.setTimeout(function () {
                    page.render(output);
                    phantom.exit();
                }, 200);
            }
        });
    }
} 

// fetch page & save
window.setTimeout(function () {
    savePdf(system.args);
});


// eof
