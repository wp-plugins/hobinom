var clickTabCurrent = 1;

function clickTabSwitch(switchTo) {
	document.getElementById('clickTab' + clickTabCurrent).style.display = "none";
	clickTabCurrent = switchTo;
	document.getElementById('clickTab' + clickTabCurrent).style.display = "block";
}