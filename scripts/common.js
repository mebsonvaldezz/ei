try {
	http = new XMLHttpRequest(); /* e.g. Firefox */
}
catch(e)
{
	try {
		http = new ActiveXObject("Msxml2.XMLHTTP"); /* some versions IE */
	}
	catch (e)
	{
		try {
			http = new ActiveXObject("Microsoft.XMLHTTP"); /* some versions IE */
		}
		catch (e)
		{
			http = false;
		}
	}
}

function setText(obj, text)
{
	$(obj).innerHTML = text;
}

function showb(obj)
{
	$(obj).style.display = '';
}

function hideb(obj)
{
	$(obj).style.display = 'none';
}

function send_data(method, filename, params)
{
	var myRandom = parseInt(Math.random()*99999999);// cache buster
	http.open(method, call_ajx + filename + ".php?" + params + "&rand=" + myRandom, true);
	http.onreadystatechange = handleHttpResponse;
	http.send(null);
}

function setContent(text, obj)
{
	if (!obj)
	{
		obj = result_obj;
	}
	
	show_box(result_obj);
	obj.innerHTML = text;
}

function loadrecord(nit,prov)
{
	nit_obj.value = nit;
	prov_obj.value = prov;
	
	hide_result();
}

function loadrecordexe(exe)
{
	exe_obj.value = exe;
	hide_result();
}

function reset_prov()
{
	prov_obj.value = '';
	hide_result(prov_dsp_obj);
}

function show_box(qobj)
{
	qobj.style.visibility = 'visible';
	qobj.style.height = 'auto';
}

function hide_result(qobj)
{
	if (!qobj)
	{
		qobj = result_obj;
	}
	qobj.style.visibility = 'hidden';
	qobj.style.height = '0px';
}

function redirect(url)
{
	window.location = url;
}