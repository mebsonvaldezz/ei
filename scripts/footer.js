if (document.getElementsByTagName)
{
	var inputElements = document.getElementsByTagName("input");
	for (i = 0; inputElements[i]; i++)
	{
		inputElements[i].setAttribute("autocomplete","off");
	} //loop thru input elements
} //basic DOM-happiness-check

if ($('un'))
{
	$('un').focus();
}