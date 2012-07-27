/**
 * InfoSmart JavaScript - Kernel.
 *
 * Archivo de procesamiento para JavaScipt con jQuery
 * recomendado para todas las aplicaciones de InfoSmart.
 *
 * InfoSmart. Todos los derechos reservados.
 * Copyright 2012 - Iván Bravo Bravo.
 * http://www.infosmart.mx/ - http://www.jquery.com/
**/

window.requestFileSystem 	= window.requestFileSystem || window.webkitRequestFileSystem;
window.Notifications 		= window.webkitNotifications || window.Notifications;

/**
 * Módulo: KERNEL
 * Funciones de transformación, comprobación y general.
*/

Kernel =
{
	_Anim: {
		times: 10,
		total: 0
	},

	Allow: function(t)
	{
		if(t == 'html5')
			return Kernel.Allow.html;
		if(t == 'audio')
			return Kernel.Allow.audio;
		if(t == 'video')
			return Kernel.Allow.video;
		if(t == 'file')
			return Kernel.Allow.file;
		if(t == 'geo')
			return Kernel.Allow.geo;
		if(t == 'notify')
			return Kernel.Allow.notify;
		if(t == 'dnd')
			return Kernel.Allow.dnd;
		if(t == 'socket')
			return Kernel.Allow.socket;
		if(t == 'file_sys')
			return Kernel.Allow.file_sys;
			
		return false;
	},
	
	VerifyPerms: function()
	{
		try
		{
			if(!!(document.createElement('canvas').getContext('2d')))	
				Kernel.Allow.html = true;
				
			if(!!(document.createElement('audio').canPlayType('audio/mpeg')))
				Kernel.Allow.audio = true;
					
			if(!!(document.createElement('video').canPlayType('video/mp4')))
				Kernel.Allow.video = true;
					
			if(window.File && window.FileReader && window.FileList && window.Blob)
				Kernel.Allow.file = true;
					
			if(navigator.geolocation)
				Kernel.Allow.geo = true;
					
			if(window.Notifications)
				Kernel.Allow.notify = true;

			if(window.requestFileSystem)
				Kernel.Allow.file_sys = true;			
					
			if(window.WebSocket)
				Kernel.Allow.socket = true;
		} catch(e) { }
	},

	SetPath : function(title, path)
	{
		if(title == undefined || title == '')
			title = document.title;
			
		document.title = title;
		window.history.replaceState(null, '', path);

		console.log('KERNEL - Se ha cambiado la dirección natural a "' + path + '" correctamente.');
	},

	SetTitle : function(title, animation, count)
	{					
		if(title == undefined || title == '')
			return;

		if(animation == true)
		{
			if(this._Anim.total == 0)
				this.AnimateTitle(title);
			else
				console.warn('KERNEL - Se ha intentado ejecutar un cambio de titulo en uno activo.');
		}
		else
			document.title = title;
	},
	
	AnimateTitle : function(title)
	{
		if(this._Anim.total <= this._Anim.times)
		{
			if(document.title == Page_Name)
				document.title = title;
			else
				document.title = Page_Name;
		
			this._Anim.total += 1;			
			setTimeout('Kernel.AnimateTitle("' + title + '")', 1500);
		}
		else
		{
			this._Anim.total = 0;
			document.title 	= Page_Name;			
		}
	},

	CleanText : function(str)
	{		
		if(str == "")
			return str;
			
		str = str.replace(/<([^<]+)>([^<]+)<\/([^<]+)>/gi, "$2");
		str = str.replace(/<br>/gi, "");
				
		return str;
	},

	NewWindow : function(opts)
	{
		try
		{
			Options = {
				'page': '',
				'name': 'InfoSmartPage',
				'fullscreen': '0',
				'toolbar': '0',
				'location': 'center',
				'status': '0',
				'menubar': '0',
				'scrollbars': 'auto',
				'resizable': '0',
				'width': '100',
				'height': '100'
			};
			
			for(var param in opts)
				Options[param] = opts[param];
				
			Settings = 'fullscreen=' + Options.fullscreen + 
			',toolbar=' + Options.toolbar +
			',location=' + Options.location +
			',status=' + Options.status +
			',menubar=' + Options.menubar +
			',scrollbars=' + Options.scrollbars +
			',resizable=' + Options.resizable +
			',width=' + Options.width +
			',height=' + Options.height;
			
			if(Options.page !== '')
			{
				console.log('KERNEL - Se ha abierto una nueva ventana con la dirección "' + options.page + '"');
				return window.open(options.page, options.name, settings, '1');
			}
			else
				console.warn('KERNEL - Se ha intentado abrir una ventana sin dirección de destino.');
		}
		catch(e)
		{ console.error('KERNEL - Ha sucedido un error al intentar abrir una ventana nueva.'); }
	},

	GetBrowser: function(agent)
	{
		if(agent == '' || agent == undefined)
		{
			if(navigator.userAgent !== undefined)
				agent = navigator.userAgent;
			else
				agent = navigator.appName + ' ' + navigator.appVersion;
		}

		if(agent.indexOf('Opera Mini') !== -1)
			return 'Opera Mini';
		if(agent.indexOf('Opera Mobile') !== -1)
			return 'Opera Mobile';
		if(agent.indexOf('Mobile') !== -1)
			return 'Mobile';

		if(agent.indexOf('Opera') !== -1)
			return 'Opera';
		if(agent.indexOf('Firefox') !== -1)
			return 'Mozilla Firefox';
		if(agent.indexOf('Rockmelt') !== -1)
			return 'Rockmelt';
		if(agent.indexOf('Chrome') !== -1)
			return 'Gooogle Chrome';
		if(agent.indexOf('Maxthon') !== -1)
			return 'Maxthon';

		// WTF?
		if(agent.indexOf('MSIE 10') !== -1)
			return 'Internet Explorer 10';
		if(agent.indexOf('MSIE 9') !== -1)
			return 'Internet Explorer 9';
		if(agent.indexOf('MSIE') !== -1)
			return 'Internet Explorer';

		if(agent.indexOf('Galeon') !== -1)
			return 'Galeon';
		if(agent.indexOf('MyIE') !== -1)
			return 'MyIE';
		if(agent.indexOf('Lynx') !== -1)
			return 'Lynx';
		if(agent.indexOf('Konqueror') !== -1)
			return 'Konqueror';
		if(agent.indexOf('Mozilla/5') !== -1)
			return 'Mozilla';

		return 'Desconcido';
	},

	GetOS: function(agent)
	{
		if(agent == '' || agent == undefined)
		{
			if(navigator.userAgent !== undefined)
				agent = navigator.userAgent;
			else
				agent = navigator.appName + ' ' + navigator.appVersion;
		}

		if(agent.indexOf('Windows NT 6.2') !== -1)
			return 'Windows 8';
		if(agent.indexOf('Windows NT 6.1') !== -1)
			return 'Windows 7';
		if(agent.indexOf('Windows NT 6.0') !== -1)
			return 'Windows Vista';
		if(agent.indexOf('Windows NT 5.2') !== -1)
			return 'Windows Server 2003';
		if(agent.indexOf('Windows NT 5.1') !== -1 || agent.indexOf('Windows XP') !== -1)
			return 'Windows XP';
		if(agent.indexOf('Windows NT 5.0') !== -1 || agent.indexOf('Windows 2000') !== -1)
			return 'Windows 2000';

		if(agent.indexOf('Linux') !== -1 || agent.indexOf('X11') !== -1)
			return 'Linux';
		if(agent.indexOf('Mac_PowerPC') !== -1 || agent.indexOf('Macintosh') !== -1)
			return 'MacOS';

		return 'Desconocido';
	}
}

/**
 * Módulo: Geo
 * Funciones de geolocalización con HTML 5.
*/

Geo =
{
	Latitude: '',
	Longitude: '',
	Accuracy: '',
	Speed: '',

	Callback: null,

	Get: function(success, error, updated)
	{
		if(success !== '' && success !== undefined)
			Geo.Callback = success;

		if(error == '')
			error = undefined;		

		console.log('KERNEL - Obteniendo ubicación geográfica.');
		navigator.geolocation.getCurrentPosition(Geo.Set, error);

		if(updated == true)
			navigator.geolocation.watchPosition(Geo.Set, error);
	},

	Set: function(data)
	{
		Coords = data.coords;

		if(Geo.Latitude !== '')
			console.log('KERNEL - Cambios en la ubicación geográfica, actualizando...');

		Geo.Latitude	= Coords.latitude;
		Geo.Longitude	= Coords.longitude;
		Geo.Accuracy	= Coords.accuracy;
		Geo.Speed		= Coords.speed;

		if(Geo.Callback !== null)
			Geo.Callback(data);
	}
}

/**
 * Módulo: Data
 * Funciones para guardar y obtener información "localStorage"
*/

Data =
{
	Set: function(param, value)
	{
		try
		{
			window.localStorage.setItem(param, value);
		}
		catch (e)
		{ console.error('KERNEL - Ha ocurrido un error al intentar guardar información local.'); }
	},

	Get: function(param)
	{
		try
		{
			return window.localStorage.getItem(param);
		}
		catch(e)
		{ console.error('KERNEL - Ha ocurrido un error al intentar obtener información local.'); }
	},

	Delete: function(param)
	{
		try
		{
			window.localStorage.removeItem(param);
		}
		catch(e)
		{ console.error('KERNEL - Ha ocurrido un error al intentar eliminar información local.'); }
	}
}

/**
 * Módulo: Cookie
 * Funciones para guardar y obtener Cookies.
*/

Cookie =
{
	Set: function(param, value, days, domain)
	{
		Expires = '';

		try
		{
			Dat = new Date();
			Dat.setTime(Dat.getTime() + (days * 24 * 60 * 60 * 1000));

			Expires = '; expires=' + Dat.toGMTString();
			Cookie	= param + '=' + value + Expires + '; path=/;';

			if(domain !== '' && domain !== undefined)
				Cookie += domain + '';

			document.cookie = Cookie;
			console.log('KERNEL - Se ha guardado la cookie ' + param + ' con éxito.');
		}
		catch(e)
		{ console.error('KERNEL - Ha ocurrido un error al intentar guardar una Cookie.'); }
	},

	Get: function(param)
	{
		try
		{
			Name = param + '=';
			Cook = document.cookie.split(';');

			for(i = 0; i < Cook.length; i++)
			{
				Co = Cook[i];

				while(Co.charAt(0) == ' ')
					Co = Co.substring(1, Co.length);

				if(Co.indexOf(Name) == 0)
					return Co.substring(Name.length, Co.length)
			}

			return false;
		}
		catch(e)
		{ console.error('KERNEL - Ha ocurrido un error al intentar obtener una cookie.'); }
	},

	Delete: function(param)
	{
		this.Set(param, '', -1);
	}
}

/**
 * Módulo: Time
 * Funciones para la obtención y manipulación de fechas.
*/

Time = 
{
	ToDate: function(time, hour)
	{
		if(!is_numeric(time))
			return false;

		Result = '';

		try
		{
			Dat = new Date(time * 1000);
			Result = Dat.getDay() + '-' + Dat.getMonth() + '-' + Dat.getFullYear();

			if(hour == true)
				Result += ' ' + Dat.getHours() + ':' + Dat.getMinutes() + ':' + Dat.getSeconds();
		}
		catch(e)
		{ console.error('KERNEL - Ha ocurrido un error al intentar convertir una fecha UNIX a fecha normal.'); }

		return Result;
	},

	ToTime: function(date)
	{
		Result = '';

		try
		{
			Dat = new Date(date);
			Result = Dat.getTime() / 1000.0;
		}
		catch(e)
		{ console.error('KERNEL - Ha ocurrido un error al intentar convertir una fecha normal a fecha UNIX.'); }

		return Result;
	},

	Calculate: function(time, num)
	{
		var inte = ["segundo", "minuto", "hora", "día", "semana", "mes", "año"];
		var dur = [60, 60, 24, 7, 4.35, 12];
		
		time = parseInt(time);
		var now = parseInt(this.Unix());
		var j = 0;
		
		var dif = 0;
		var str = "";
		
		var sh = time + 10;
		
		//if(now == time || now < sh)
		//	return "Justo ahora";
		if(now > time)
		{
			dif = now - time;
			str = "Hace";
		}
		else
		{
			dif = time - now;
			str = "Dentro de";
		}
		
		for(j = 0; dif >= dur[j] && j < dur.length - 1; j++)
			dif /= dur[j];
			
		dif = Math.round(dif);
		
		if(dif != 1)
		{
			inte[5] += "e";
			inte[j] += "s";
		}
		
		if(num == true)
			return dif + ' ' + inte[j];
		else
			return str + " " + dif + " " + inte[j];
	},

	Unix: function()
	{
		return Math.round((new Date()).getTime() / 1000);
	}
}

/**
 * Módulo: Tpl
 * Funciones para la modificación de la plantilla actual.
*/

Tpl = 
{
	GetHash: function()
	{
		return document.location.hash.substring(2);
	},

	HaveStyle: function(file)
	{
		Link = document.querySelector('link[href="' + file + '"]');

		if(Link == null)
			return false;

		return true;
	},

	AddStyle: function(file, element, type)
	{
		if(this.HaveStyle(file))
			return;

		if(element == '' || element == undefined)
			element = 'head';

		if(type == '' || type == undefined)
			type = 'stylesheet';

		try
		{
			El = $('<link />');
			El.attr('href', file);
			El.attr('rel', type);
			$(element).append(El);
		}
		catch(e)
		{ console.error('KERNEL - Ha ocurrido un error al intentar agregar un estilo a la plantilla.'); }
	},

	HaveScript: function(file)
	{
		Link = document.querySelector('script[src="' + file + '"]');

		if(Link == null)
			return false;

		return true;
	},

	AddScript: function(file, element)
	{
		if(this.HaveScript(file))
			return;

		if(element == '' || element == undefined)
			element = 'head';

		try
		{
			El = document.createElement('script');
			El.src = file;
			document.querySelector(element).appendChild(El);
		}
		catch(e)
		{ console.error('KERNEL - Ha ocurrido un error al intentar agregar un script a la plantilla.'); }
	}
}

/**
 * Módulo: Json
 * Funciones para la manipulación de objetos JSON.
*/

Json =
{
	Parse: function(str_json)
	{
		var json = window.JSON;
		
		if (typeof json === 'object' && typeof json.parse === 'function') 
		{			
			try 
			{ return json.parse(str_json); } 
			catch (err) 
			{
				console.error("Ha sucedido un error al descodificar '" + str_json + "' de JSON.", "error");
				return null;
			}
		}
	 
		var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
		var j;
		var text = str_json;

		cx.lastIndex = 0;
		
		if (cx.test(text)) 
		{
			text = text.replace(cx, function (a) {
				return '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
			});
		}
	 
		if ((/^[\],:{}\s]*$/).
		test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').
		replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
		replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) 
		{
			j = eval('(' + text + ')');	 
			return j;
		}
	 
		return null;
	},

	Build: function(str_json)
	{
		var retVal, json = window.JSON;
		
		try 
		{
			if (typeof json === 'object' && typeof json.stringify === 'function') 
			{
				retVal = json.stringify(str_json); 

				if (retVal === undefined)
					this.Clog("Ha sucedido un error al codificar '" + str_json + "' a JSON.", "error");
					
				return retVal;
			}
	 
			var value = str_json;
	 
			var quote = function (string) 
			{
				var escapable = /[\\\"\u0000-\u001f\u007f-\u009f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
				
				var meta = {
					'\b': '\\b',
					'\t': '\\t',
					'\n': '\\n',
					'\f': '\\f',
					'\r': '\\r',
					'"': '\\"',
					'\\': '\\\\'
				};
	 
				escapable.lastIndex = 0;
				
				return escapable.test(string) ? '"' + string.replace(escapable, function (a) {
					var c = meta[a];
					return typeof c === 'string' ? c : '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
				}) + '"' : '"' + string + '"';
			};
	 
			var str = function (key, holder) 
			{
				var gap = '';
				var indent = '    ';
				var i = 0; // The loop counter.
				var k = ''; // The member key.
				var v = ''; // The member value.
				var length = 0;
				var mind = gap;
				var partial = [];
				var value = holder[key];
	 
				// If the value has a toJSON method, call it to obtain a replacement value.
				if (value && typeof value === 'object' && typeof value.toJSON === 'function') {
					value = value.toJSON(key);
				}
	 
				// What happens next depends on the value's type.
				switch (typeof value) {
				case 'string':
					return quote(value);
	 
				case 'number':
					// JSON numbers must be finite. Encode non-finite numbers as null.
					return isFinite(value) ? String(value) : 'null';
	 
				case 'boolean':
				case 'null':
					// If the value is a boolean or null, convert it to a string. Note:
					// typeof null does not produce 'null'. The case is included here in
					// the remote chance that this gets fixed someday.
					return String(value);
	 
				case 'object':
					// If the type is 'object', we might be dealing with an object or an array or
					// null.
					// Due to a specification blunder in ECMAScript, typeof null is 'object',
					// so watch out for that case.
					if (!value) {
						return 'null';
					}
					if ((this.PHPJS_Resource && value instanceof this.PHPJS_Resource) || (window.PHPJS_Resource && value instanceof window.PHPJS_Resource)) {
						throw new SyntaxError('json_encode');
					}
	 
					// Make an array to hold the partial results of stringifying this object value.
					gap += indent;
					partial = [];
	 
					// Is the value an array?
					if (Object.prototype.toString.apply(value) === '[object Array]') {
						// The value is an array. Stringify every element. Use null as a placeholder
						// for non-JSON values.
						length = value.length;
						for (i = 0; i < length; i += 1) {
							partial[i] = str(i, value) || 'null';
						}
	 
						// Join all of the elements together, separated with commas, and wrap them in
						// brackets.
						v = partial.length === 0 ? '[]' : gap ? '[\n' + gap + partial.join(',\n' + gap) + '\n' + mind + ']' : '[' + partial.join(',') + ']';
						gap = mind;
						return v;
					}
	 
					// Iterate through all of the keys in the object.
					for (k in value) {
						if (Object.hasOwnProperty.call(value, k)) {
							v = str(k, value);
							if (v) {
								partial.push(quote(k) + (gap ? ': ' : ':') + v);
							}
						}
					}
	 
					// Join all of the member texts together, separated with commas,
					// and wrap them in braces.
					v = partial.length === 0 ? '{}' : gap ? '{\n' + gap + partial.join(',\n' + gap) + '\n' + mind + '}' : '{' + partial.join(',') + '}';
					gap = mind;
					return v;
				case 'undefined':
					// Fall-through
				case 'function':
					// Fall-through
				default:
					throw new SyntaxError('json_encode');
				}
			};
	 
			// Make a fake root object containing our value under the key of ''.
			// Return the result of stringifying the value.
			return str('', {
				'': value
			});
	 
		} catch (err) { // Todo: ensure error handling above throws a SyntaxError in all cases where it could
			// (i.e., when the JSON global is not available and there is an error)
			if (!(err instanceof SyntaxError)) {
				throw new Error('Unexpected error type in json_encode()');
			}
			this.php_js = this.php_js || {};
			this.php_js.last_error_json = 4; // usable by json_last_error()
			return null;
		}
	}
}

/**
 * Módulo: Utils
 * Utilidades y demás.
*/

Utils = 
{
	HaveNum: function(str)
	{
		numbers = '0123456789';

		for(i = 0; i < str.length; i++)
		{
	    	if(numbers.indexOf(str.charAt(i),0) != -1)
	        	return true;
		}

	   return false;
	},

	HaveLetters: function(str)
	{
		letters = 'abcdefghyjklmnñopqrstuvwxyz';
		str = str.toLowerCase();

		for(i = 0; i < str.length; i++)
		{
	    	if(letters.indexOf(str.charAt(i),0) != -1)
	        	return true;
	    }

	    return false;
	},

	HaveLower: function(str)
	{
		letters = 'abcdefghyjklmnñopqrstuvwxyz';

		for(i = 0; i < str.length; i++)
		{
	    	if(letters.indexOf(str.charAt(i),0) != -1)
	        	return true;
	    }

	    return false;
	},

	HaveUpper: function(str)
	{
		letters = 'ABCDEFGHIJKLMNÑOPQRSTUVWXYZ';

		for(i = 0; i < str.length; i++)
		{
	    	if(letters.indexOf(str.charAt(i),0) != -1)
	        	return true;
	 	}

	 	return false;
	},

	HaveSpecial: function(str)
	{
		letters = '%&^*(){}-_!$@';

		for(i = 0; i < str.length; i++)
		{
	    	if(letters.indexOf(str.charAt(i),0) != -1)
	        	return true;
	 	}
	 	
	 	return false;
	},

	SecurityStrong: function(str)
	{
		Level = 0;

		if(str.length == 0)
			return 0;

		if(this.HaveNum(str))
			Level += 15;

		if(this.HaveLetters(str))
			Level += 5;

		if(this.HaveUpper(str))
			Level += 15;

		if(this.HaveSpecial(str))
			Level += 20;

		if(str.length >= 4 && str.length <= 5)
			Level += 10;

		if(str.length >= 6 && str.length <= 8)
			Level += 20;

		if(str.length >= 9 && str.length <= 12)
			Level += 35;

		if(str.length > 12)
			Level += 45;

		return Level;
	}
}

/**
 * Módulo: Photos
 * Funciones especiales para el procesamiento de imagenes.
*/

Photos =
{
	Data: function(photo)
	{
		if(!Kernel.Allow('file'))
		{
			alert('Lo sentimos, pero su navegador web no permite la subida de archivos por este sistema. Actualize su navegador o cambiese a otro más moderno.');
			return false;
		}

		if(photo == undefined)
			return false;

		Ph = photo.target.files[0];

		if(Ph.type !== 'image/png' && Ph.type !== 'image/jpeg' && Ph.type !== 'image/gif')
		{
			alert('El formato de la imagen no es válida.');
			return false;
		}

		return Ph;
	},

	Read: function(photo, element)
	{
		if(!Kernel.Allow('file'))
			return alert('Lo sentimos, pero su navegador web no permite la subida de archivos por este sistema. Actualize su navegador o cambiese a otro más moderno.');

		if(photo == undefined)
			return;

		Ph = photo.target.files[0];

		if(Ph.type !== 'image/png' && Ph.type !== 'image/jpeg' && Ph.type !== 'image/gif')
			return alert('El formato de la imagen no es válida.');

		File = new FileReader();

		File.onerror = function()
		{
			alert('¡Uy! Ha ocurrido un error mientras procesabamos la imagen, vuelve a intentarlo.')
		}

		File.onload = function(e)
		{
			$(element).attr('src', e.target.result)
		}

		File.readAsDataURL(Ph);
	},

	Return: function(photo, callback)
	{
		if(!Kernel.Allow('file'))
			return alert('Lo sentimos, pero su navegador web no permite la subida de archivos por este sistema. Actualize su navegador o cambiese a otro más moderno.');

		if(photo == undefined || callback == undefined)
			return;

		Ph = photo.target.files[0];

		if(Ph.type !== 'image/png' && Ph.type !== 'image/jpeg' && Ph.type !== 'image/gif')
			return alert('El formato de la imagen no es válida.');

		File = new FileReader();

		File.onerror = function()
		{
			alert('¡Uy! Ha ocurrido un error mientras procesabamos la imagen, vuelve a intentarlo.')
		}

		File.onload = function(e)
		{
			callback(e.target.result);
		}

		File.readAsDataURL(Ph);
	},

	Get: function(photo, callback)
	{
		if(!Kernel.Allow('file'))
			return alert('Lo sentimos, pero su navegador web no permite la subida de archivos por este sistema. Actualize su navegador o cambiese a otro más moderno.');

		if(photo == undefined || callback == undefined)
			return;

		Result = Ph = photo.target.files[0];

		if(Ph.type !== 'image/png' && Ph.type !== 'image/jpg' && Ph.type !== 'image/gif')
			return alert('El formato de la imagen no es válida.');

		File = new FileReader();

		File.onerror = function()
		{
			alert('¡Uy! Ha ocurrido un error mientras procesabamos la imagen, vuelve a intentarlo.')
		}

		File.onload = function(e)
		{
			Result.src = e.target.result;
			callback(Result);
		}

		File.readAsDataURL(Ph);
	}
}

/**
 * Módulo: Files
 * Funciones especiales para el procesamiento de archivos.
*/

Files = 
{
	Return: function(file, callback)
	{
		if(!Kernel.Allow('file'))
			return alert('Lo sentimos, pero su navegador web no permite la subida de archivos por este sistema. Actualize su navegador o cambiese a otro más moderno.');

		if(file == undefined || callback == undefined)
			return;

		Fl = file.target.files[0];
		console.log('Subiendo archivo de tipo: ' + Fl.type);

		File = new FileReader();

		File.onerror = function()
		{
			alert('¡Uy! Ha ocurrido un error mientras procesabamos la imagen, vuelve a intentarlo.')
		}

		File.onload = function(e)
		{
			callback(e.target.result);
		}

		File.readAsDataURL(Fl);
	},

	Get: function(file, callback)
	{
		if(!Kernel.Allow('file'))
			return alert('Lo sentimos, pero su navegador web no permite la subida de archivos por este sistema. Actualize su navegador o cambiese a otro más moderno.');

		if(file == undefined || callback == undefined)
			return;

		Result = Fl = file.target.files[0];
		console.log('Subiendo archivo de tipo: ' + Fl.type);

		File = new FileReader();

		File.onerror = function()
		{
			alert('¡Uy! Ha ocurrido un error mientras procesabamos la imagen, vuelve a intentarlo.')
		}

		File.onload = function(e)
		{
			Result.src = e.target.result;
			callback(Result);
		}

		File.readAsDataURL(Fl);
	},

	Porcent: function(e, element)
	{
		if(e.lengthComputable)
		{
			Percent = Math.round(e.loaded * 100 / e.total);
			$(element).val(Percent);
			$(element).html(Percent + '%');
		}
	}
}

/**
 * Módulo: Language
 * Funciones especiales para el sistema de lenguajes.
*/

Language = 
{
	Init: function(language, tag)
	{
		Lng = Lang[language];

		if(Lng == undefined)
			return console.error('El lenguaje que ha seleccionado no se encuentra disponible.');

		if(tag == undefined || tag == '')
			tag = 'label';

		Html = $('#page').html();

		function str_ireplace(a,b,c){var d,e="";var f=0;var g;var h=function(a){return a.replace(/([\\\^\$*+\[\]?{}.=!:(|)])/g,"\\$1")};a+="";f=a.length;if(Object.prototype.toString.call(b)!=="[object Array]"){b=[b];if(Object.prototype.toString.call(a)==="[object Array]"){while(f>b.length){b[b.length]=b[0]}}}if(Object.prototype.toString.call(a)!=="[object Array]"){a=[a]}while(a.length>b.length){b[b.length]=""}if(Object.prototype.toString.call(c)==="[object Array]"){for(e in c){if(c.hasOwnProperty(e)){c[e]=str_ireplace(a,b,c[e])}}return c}f=a.length;for(d=0;d<f;d++){g=new RegExp(h(a[d]),"gi");c=c.replace(g,b[d])}return c}

		for(Param in Lng)
			Html = str_ireplace('%' + Param + '%', '<'+ tag +' data-lang-param="' + Param + '">' + Lng[Param] + '</'+ tag +'>', Html);

		$('#page').html(Html);
	},

	Change: function(language)
	{
		Lng = Lang[language];

		if(Lng == undefined)
			return console.error('El lenguaje que ha seleccionado no se encuentra disponible.');

		$('label[data-lang-param]').each(function()
		{
			Param = $(this).data('lang-param');
			$(this).html(Lng[Param]);
		});
	}
}

_Protocol = 'http://';

CalcTime = null;

$(document).on('ready', function()
{
	/** NO INTERNET EXPLORER 

	if(Kernel.GetBrowser() == 'Internet Explorer')
		$('#page').html('<div style="clear: both; height: 59px; padding:0 0 0 15px; position: relative; width: 820px; margin: 0 auto; margin-top: 15%; font-family: Segoe UI, Arial, sans-serif; font-size: 12px"><h1>Lo sentimos, pero estamos en el siglo XXI.</h1><a href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode"><img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0006_spanish_spain.jpg" border="0" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today." /></a><br />Para continuar navegando por nuestras aplicaciones, por favor actualize su navegador :)</div>');
	**/

	/** IMPLEMENTANDO RECURSOS Y PLUGINS EXTERNOS **/

	Tpl.AddScript(Resources_Sys + '/js/functions.base.js');

	Tpl.AddScript(Resources_Sys + '/js/external/php.min.js');
	Tpl.AddScript(Resources_Sys + '/js/external/html5slider.js');
	Tpl.AddScript(Resources_Sys + '/js/external/html5shiv.js');

	//Tpl.AddScript('//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit');

	/** INICIANDO INSTANCIAS LOCALES **/

	Kernel.VerifyPerms();

	/** CTRL + M = @hotmail.com **/

	$("input[type='email']").keyup(function(e) 
	{
		T = $(this);
		Doc = (document.all) ? e.keyCode : e.which;
		
		if(Doc == 77 && e.ctrlKey)
			T.val(T.val() + '@hotmail.com');
	});

	/** AJUSTES INICIALES **/

	_Protocol = document.location.protocol + '//';

	/** INICIALIZACIÓN CORRECTA **/

	console.log('Se ha preparado el Kernel con el protocolo ' + _Protocol);
});